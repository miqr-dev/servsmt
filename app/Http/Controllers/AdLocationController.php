<?php

namespace App\Http\Controllers;

use App\AdComputer;
use App\AdDevice;
use App\AdOu;
use App\InvItems;
use App\InvRoom;
use App\Support\AdDevices;
use App\Support\AdLocation;
use App\Support\AdStates;
use Illuminate\Http\Request;

/**
 * Data for the AD location pickers of the new ticket forms (AD switch step 4,
 * 2026-10-09): Standort › Adresse › Etage › Raum (+ PCs) from ad_ous /
 * ad_computers, and the old-inventory devices (printers, phones, projectors)
 * of an AD room. Open to every logged-in user, like the old item/listen.
 * OUs hidden on Einstellungen > Standorte & Adressen are left out with
 * everything below them; Standorte carry their Bundesland (AdStates).
 * Devices that are not in a room's list can be added by any user
 * (storeDevice, App\Support\AdDevices); IT reviews them in Einstellungen.
 */
class AdLocationController extends Controller
{
    /** GET /ad-locations[?computers=1] */
    public function index(Request $request)
    {
        $data = ['standorte' => [], 'adressen' => [], 'etagen' => [], 'raeume' => []];

        $nodes = AdOu::query()
            ->whereIn('type', ['standort', 'adresse', 'etage', 'raum'])
            ->get(['id', 'type', 'label', 'name', 'source', 'hidden', 'state']);

        $hidden = [];
        foreach ($nodes->where('hidden', true) as $ou) {
            foreach (AdLocation::descendantIds($ou->id) as $id) {
                $hidden[$id] = true;
            }
        }

        foreach ($nodes as $ou) {
            if (isset($hidden[$ou->id])) {
                continue;
            }
            $up = AdLocation::ancestry($ou->id);
            $label = $ou->label ?: $ou->name;
            if ($ou->type === 'standort') {
                $data['standorte'][] = ['id' => $ou->id, 'label' => $label, 'state' => AdStates::of($ou)];
            } elseif ($ou->type === 'adresse' && $up['standort']) {
                $data['adressen'][] = ['id' => $ou->id, 'standort_id' => $up['standort'], 'label' => $label, 'app' => $ou->source === 'app'];
            } elseif ($ou->type === 'etage' && $up['adresse']) {
                $data['etagen'][] = ['id' => $ou->id, 'adresse_id' => $up['adresse'], 'label' => $label];
            } elseif ($ou->type === 'raum' && $up['adresse']) {
                $data['raeume'][] = [
                    'id' => $ou->id,
                    'adresse_id' => $up['adresse'],
                    'etage_id' => $up['etage'],
                    'label' => $label,
                    'app' => $ou->source === 'app', // room added in the app (no computers)
                ];
            }
        }
        foreach ($data as $key => $rows) {
            usort($rows, fn ($a, $b) => strnatcasecmp($a['label'], $b['label']));
            $data[$key] = $rows;
        }

        if ($request->boolean('computers')) {
            $data['computers'] = AdComputer::query()
                ->where('enabled', true)
                ->orderBy('name')
                ->get(['id', 'name', 'ad_ou_id', 'room_ou_id', 'group'])
                ->map(function ($pc) use ($hidden) {
                    $up = AdLocation::ancestry($pc->room_ou_id ?: $pc->ad_ou_id);
                    // in a hidden OU (e.g. "LAPTOPs"): only the Standort counts
                    foreach (['adresse', 'etage', 'raum'] as $level) {
                        if ($up[$level] && isset($hidden[$up[$level]])) {
                            $up = array_merge($up, $level === 'adresse' ? ['adresse' => null, 'etage' => null, 'raum' => null]
                                : ($level === 'etage' ? ['etage' => null, 'raum' => null] : ['raum' => null]));
                        }
                    }

                    return [
                        'id' => $pc->id,
                        'name' => $pc->name,
                        'group' => $pc->group,
                        'standort_id' => $up['standort'],
                        'adresse_id' => $up['adresse'],
                        'etage_id' => $up['etage'],
                        'room_id' => $up['raum'],
                    ];
                })
                ->values();

            // PCs added by users (not in AD), placed like the AD ones
            $data['pc_devices'] = AdDevice::where('kind', 'rechner')->orderBy('name')->get()
                ->filter(fn ($d) => ! isset($hidden[$d->ad_ou_id]))
                ->map(function ($d) {
                    $up = AdLocation::ancestry($d->ad_ou_id);

                    return AdDevices::payload($d) + [
                        'standort_id' => $up['standort'],
                        'adresse_id' => $up['adresse'],
                        'etage_id' => $up['etage'],
                        'room_id' => $up['raum'],
                    ];
                })
                ->values();
        }

        return response()->json($data);
    }

    /**
     * GET /ad-locations/items?room=&type=printer|phone|projector
     * Devices of this room that are not in AD: the old inventory items of the
     * old rooms linked to it (source 'inventar') and the devices added in the
     * app (source 'app', checked = confirmed by IT).
     */
    public function items(Request $request)
    {
        $data = $request->validate([
            'room' => 'required|integer',
            'type' => 'required|in:printer,phone,projector',
        ]);
        $type = AdDevices::TYPES[$data['type']];
        $roomIds = AdLocation::descendantIds((int) $data['room']);
        $oldRooms = InvRoom::whereIn('ad_ou_id', $roomIds)->pluck('id');

        $old = InvItems::whereIn('room_id', $oldRooms)
            ->whereIn('gart_id', $type['garts'])
            ->orderBy('gname')
            ->get(['id', 'gname', 'invnr'])
            ->map(fn ($i) => ['id' => $i->id, 'gname' => $i->gname, 'invnr' => $i->invnr, 'source' => 'inventar', 'checked' => true]);
        $app = AdDevice::whereIn('ad_ou_id', $roomIds)
            ->whereIn('kind', $type['kinds'])
            ->orderBy('name')
            ->get()
            ->map(fn ($d) => ['id' => $d->id, 'gname' => $d->name, 'invnr' => $d->invnr, 'source' => 'app',
                'checked' => $d->checked_at !== null, 'kind' => $d->kind]);

        return $old->concat($app)->values();
    }

    /**
     * POST /ad-locations/devices {room, kind, name, invnr?} - "Gerät hinzufügen"
     * in the ticket forms when the device is not in the room's list. Returns it.
     */
    public function storeDevice(Request $request)
    {
        $data = $request->validate([
            // without a room only for PCs (software forms list every PC, no room asked)
            'room' => ['nullable', 'required_unless:kind,rechner', 'integer', \Illuminate\Validation\Rule::exists('ad_ous', 'id')->where('type', 'raum')->whereNull('deleted_at')],
            'kind' => ['required', \Illuminate\Validation\Rule::in(array_keys(AdDevices::KINDS))],
            'name' => 'required|string|max:100',
            'invnr' => 'nullable|string|max:50',
        ]);
        $room = ! empty($data['room']) ? AdOu::findOrFail($data['room']) : null;
        $device = AdDevices::create($room, $data['kind'], $data['name'], $data['invnr'] ?? null);
        $up = AdLocation::ancestry($device->ad_ou_id);

        return response()->json(AdDevices::payload($device) + [
            'standort_id' => $up['standort'],
            'adresse_id' => $up['adresse'],
            'etage_id' => $up['etage'],
            'room_id' => $up['raum'],
        ]);
    }
}
