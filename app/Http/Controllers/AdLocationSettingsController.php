<?php

namespace App\Http\Controllers;

use App\AdComputer;
use App\AdDevice;
use App\AdOu;
use App\InvItems;
use App\Support\AdDevices;
use App\Support\AdLocation;
use App\Support\AdStates;
use App\Support\AppOus;
use App\Support\InventoryAdoption;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * Einstellungen > Standorte & Adressen (2026-10-09, Super_Admin via
 * route_access 'settings/*'). Shapes what the ticket-form pickers offer,
 * without ever writing to AD:
 *  - Bundesland per Standort (groups the Standort select);
 *  - hide AD OUs that are not real addresses / floors / rooms
 *    ("NB-no-Energie", "LAPTOPs", ...) - the import keeps the flag;
 *  - add / rename / delete app-only addresses and rooms, e.g. a "Flur" per
 *    floor for hallway printers (App\Support\AppOus);
 *  - take over the old inventory's hallways and printer rooms
 *    (App\Support\InventoryAdoption, preview first).
 * Rooms show the old-inventory devices (printers, phones, projectors) of the
 * old rooms linked to them, plus the devices added in the app (ad_devices) -
 * the same list the ticket forms offer. Devices users added in the forms
 * ("Gerät hinzufügen") are listed for IT to confirm or delete; IT can add
 * devices to a room here too.
 */
class AdLocationSettingsController extends Controller
{
    /** inv_items.gart_id of the devices that are not in AD */
    private const DEVICE_KINDS = [5 => 'Drucker', 15 => 'Telefon', 13 => 'Beamer'];

    public function index()
    {
        $ous = AdOu::query()
            ->whereIn('type', ['standort', 'adresse', 'etage', 'raum'])
            ->get(['id', 'type', 'label', 'name', 'source', 'hidden', 'state', 'description']);

        // computers per Adresse / Etage / Raum (to see what an OU holds before hiding it)
        $pcs = [];
        foreach (AdComputer::query()->get(['ad_ou_id', 'room_ou_id']) as $pc) {
            foreach (array_filter(AdLocation::ancestry($pc->room_ou_id ?: $pc->ad_ou_id)) as $level => $id) {
                if ($level !== 'standort') {
                    $pcs[$id] = ($pcs[$id] ?? 0) + 1;
                }
            }
        }

        // old-inventory devices per AD room (via the linked old rooms)
        $devices = [];
        $rows = InvItems::query()
            ->join('inv_rooms', 'inv_rooms.id', '=', 'inv_items.room_id')
            ->whereNotNull('inv_rooms.ad_ou_id')
            ->whereIn('inv_items.gart_id', array_keys(self::DEVICE_KINDS))
            ->orderBy('inv_items.gname')
            ->get(['inv_rooms.ad_ou_id', 'inv_items.gname', 'inv_items.invnr', 'inv_items.gart_id']);
        foreach ($rows as $d) {
            $devices[$d->ad_ou_id][] = ['name' => $d->gname ?: $d->invnr, 'kind' => self::DEVICE_KINDS[$d->gart_id] ?? 'Gerät', 'app' => false];
        }
        // devices added in the app (forms / this page)
        $appDevices = AdDevice::with('creator:id,username,vorname,name')->orderBy('name')->get();
        foreach ($appDevices as $d) {
            $devices[$d->ad_ou_id][] = ['name' => $d->name, 'kind' => AdDevices::KINDS[$d->kind] ?? 'Gerät', 'app' => true];
        }

        $standorte = [];
        $nodes = [];
        foreach ($ous as $ou) {
            $label = $ou->label ?: $ou->name;
            if ($ou->type === 'standort') {
                $standorte[] = [
                    'id' => $ou->id,
                    'label' => $label,
                    'state' => $ou->state,
                    'default_state' => AdStates::default($label),
                ];
                continue;
            }
            $up = AdLocation::ancestry($ou->id);
            if (! $up['standort'] || ($ou->type !== 'adresse' && ! $up['adresse'])) {
                continue;
            }
            $nodes[] = [
                'id' => $ou->id,
                'type' => $ou->type,
                'label' => $label,
                'standort_id' => $up['standort'],
                'adresse_id' => $ou->type === 'adresse' ? null : $up['adresse'],
                'etage_id' => $ou->type === 'raum' ? $up['etage'] : null,
                'hidden' => (bool) $ou->hidden,
                'app' => $ou->source === 'app',
                'description' => $ou->description,
                'pcs' => $pcs[$ou->id] ?? 0,
                'devices' => $devices[$ou->id] ?? [],
            ];
        }
        usort($standorte, fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));
        usort($nodes, fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));

        // for review: not yet confirmed first
        $userDevices = $appDevices
            ->sortBy(fn ($d) => [$d->checked_at ? 1 : 0, -$d->created_at?->timestamp])
            ->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'kind' => AdDevices::KINDS[$d->kind] ?? 'Gerät',
                'invnr' => $d->invnr,
                'path' => AdLocation::room($d->ad_ou_id)['path'] ?? '–',
                'by' => $d->creator ? trim(($d->creator->vorname ?? '').' '.($d->creator->name ?? '')) ?: $d->creator->username : null,
                'at' => $d->created_at?->format('d.m.Y H:i'),
                'checked' => $d->checked_at !== null,
            ])->values();

        return Inertia::render('Settings/AdLocations', [
            'standorte' => $standorte,
            'nodes' => $nodes,
            'states' => AdStates::OPTIONS,
            'userDevices' => $userDevices,
            'deviceKinds' => AdDevices::KINDS,
        ]);
    }

    /** PUT settings/ad-standorte/{id}/state - '' = back to the default */
    public function updateState(Request $request, $id)
    {
        $standort = AdOu::where('type', 'standort')->findOrFail($id);
        $data = $request->validate(['state' => ['nullable', Rule::in(AdStates::OPTIONS)]]);
        $standort->forceFill(['state' => $data['state'] ?: null])->save();

        return back()->with('success', 'Bundesland gespeichert.');
    }

    /** PUT settings/ad-standorte/{id}/hidden - only in the app, AD is not changed */
    public function updateHidden(Request $request, $id)
    {
        $ou = AdOu::whereIn('type', ['adresse', 'etage', 'raum'])->findOrFail($id);
        $data = $request->validate(['hidden' => 'required|boolean']);
        $ou->forceFill(['hidden' => $data['hidden']])->save();
        $name = $ou->label ?: $ou->name;

        return back()->with('success', $data['hidden'] ? "\"{$name}\" ausgeblendet." : "\"{$name}\" wird wieder angezeigt.");
    }

    /** POST settings/ad-standorte/addresses */
    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'standort_id' => ['required', Rule::exists('ad_ous', 'id')->where('type', 'standort')->whereNull('deleted_at')],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        $parent = AppOus::addressParent(AdOu::findOrFail($data['standort_id']));
        $ou = AppOus::create($parent, 'adresse', $data['name'], $data['description'] ?? null);

        return back()->with('success', "Adresse \"{$ou->name}\" angelegt.");
    }

    /** POST settings/ad-standorte/rooms - under an Adresse or Etage */
    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'parent_id' => ['required', Rule::exists('ad_ous', 'id')->whereIn('type', ['adresse', 'etage'])->whereNull('deleted_at')],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
        $ou = AppOus::create(AdOu::findOrFail($data['parent_id']), 'raum', $data['name'], $data['description'] ?? null);

        return back()->with('success', "Raum \"{$ou->name}\" angelegt.");
    }

    /** POST settings/ad-standorte/{id}/flure - a "Flur" on every floor of the address that has none */
    public function storeHallways($id)
    {
        $address = AdOu::where('type', 'adresse')->findOrFail($id);
        $floors = AdOu::where('parent_id', $address->id)->where('type', 'etage')->get();
        $places = $floors->isNotEmpty() ? $floors : collect([$address]);

        $created = 0;
        foreach ($places as $place) {
            $has = AdOu::where('parent_id', $place->id)->where('type', 'raum')->get(['label', 'name'])
                ->contains(fn ($o) => mb_strtolower($o->label ?: $o->name) === 'flur');
            if (! $has) {
                AppOus::create($place, 'raum', 'Flur');
                $created++;
            }
        }

        return back()->with('success', $created ? "{$created} × \"Flur\" angelegt." : 'Alle Etagen haben schon einen Flur.');
    }

    /** POST settings/ad-standorte/devices - IT adds a device to a room (counts as confirmed) */
    public function storeDevice(Request $request)
    {
        $data = $request->validate([
            'room' => ['required', Rule::exists('ad_ous', 'id')->where('type', 'raum')->whereNull('deleted_at')],
            'kind' => ['required', Rule::in(array_keys(AdDevices::KINDS))],
            'name' => 'required|string|max:100',
            'invnr' => 'nullable|string|max:50',
        ]);
        $d = AdDevices::create(AdOu::findOrFail($data['room']), $data['kind'], $data['name'], $data['invnr'] ?? null);
        if (! $d->checked_at) {
            $d->forceFill(['checked_at' => now(), 'checked_by' => auth()->id()])->save();
        }

        return back()->with('success', (AdDevices::KINDS[$d->kind] ?? 'Gerät')." \"{$d->name}\" hinzugefügt.");
    }

    /** PUT settings/ad-standorte/devices/{id}/check - confirm a device a user added */
    public function checkDevice($id)
    {
        $d = AdDevice::findOrFail($id);
        $d->forceFill(['checked_at' => now(), 'checked_by' => auth()->id()])->save();

        return back()->with('success', "\"{$d->name}\" bestätigt.");
    }

    /** DELETE settings/ad-standorte/devices/{id} - soft delete, tickets keep the name */
    public function destroyDevice($id)
    {
        $d = AdDevice::findOrFail($id);
        $d->delete();

        return back()->with('success', "\"{$d->name}\" gelöscht.");
    }

    /** GET settings/ad-standorte/alt-inventar - plan only, for the preview (JSON) */
    public function adoptPreview()
    {
        return response()->json(InventoryAdoption::run(true));
    }

    /** POST settings/ad-standorte/alt-inventar - old hallways / printer rooms -> app rooms + links */
    public function adopt()
    {
        $r = InventoryAdoption::run(false);

        return back()->with('success', "Altes Inventar: {$r['created']} Räume angelegt, {$r['linked']} mit vorhandenen Räumen verknüpft"
            .($r['open'] ? ", {$r['open']} offen" : '').'.');
    }

    /** PUT settings/ad-standorte/app/{id} - rename an app address / room */
    public function updateApp(Request $request, $id)
    {
        $ou = AdOu::where('source', 'app')->findOrFail($id);
        $data = $request->validate(['name' => 'required|string|max:100', 'description' => 'nullable|string|max:255']);
        AppOus::rename($ou, $data['name'], $data['description'] ?? null);

        return back()->with('success', 'Gespeichert.');
    }

    /** DELETE settings/ad-standorte/app/{id} - soft delete, old tickets keep the name */
    public function destroyApp($id)
    {
        $ou = AdOu::where('source', 'app')->findOrFail($id);
        AppOus::delete($ou);

        return back()->with('success', "\"{$ou->name}\" gelöscht.");
    }
}
