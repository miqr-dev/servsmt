<?php

namespace App\Http\Controllers;

use App\AdComputer;
use App\AdOu;
use App\InvItems;
use App\InvRoom;
use App\Support\AdInventoryLinker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

/**
 * "AD Räume & Computer" (/ad-inventory, IT admin): the OUs and computers
 * imported from Active Directory by `php artisan ad:import-inventory`
 * (ad_ous / ad_computers). Read-only view, 2026-10-05.
 * import(): "Jetzt aktualisieren" runs the same import right away (it also
 * runs hourly via the scheduler).
 */
class AdInventoryController extends Controller
{
    public function index()
    {
        $ous = AdOu::query()
            ->get(['id', 'parent_id', 'type', 'label', 'name', 'depth', 'description', 'source'])
            ->map(fn ($o) => [
                'id' => $o->id,
                'source' => $o->source,
                'parent_id' => $o->parent_id,
                'type' => $o->type ?? 'other',
                'label' => $o->label ?: $o->name,
                'name' => $o->name,
                'description' => $o->description,
            ]);

        $computers = AdComputer::query()
            ->get(['id', 'name', 'ad_ou_id', 'room_ou_id', 'group', 'os', 'os_version', 'description',
                'enabled', 'last_logon_at', 'dns_hostname'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'ou' => $c->ad_ou_id,
                'room' => $c->room_ou_id,
                'group' => $c->group,
                'os' => trim($c->os.' '.$c->os_version) ?: null,
                'description' => $c->description,
                'enabled' => $c->enabled,
                'last_logon' => $c->last_logon_at?->toIso8601String(),
                'dns' => $c->dns_hostname,
            ]);

        return Inertia::render('AdInventory/Index', [
            'ous' => $ous,
            'computers' => $computers,
            'lastSync' => AdComputer::max('synced_at'),
            'base' => config('ad_inventory.base'),
            // OUs imported before the type/room migration have no type yet ->
            // no rooms until the import runs again.
            'needsImport' => AdOu::whereNull('type')->exists(),
        ]);
    }

    /** "Jetzt aktualisieren": run ad:import-inventory now (AD is only read). */
    public function import()
    {
        $lock = Cache::lock('ad-import-inventory', 600);
        if (! $lock->get()) {
            return back()->with('error', 'Der Import läuft bereits - bitte kurz warten.');
        }

        @set_time_limit(600);
        $start = now();

        try {
            $code = Artisan::call('ad:import-inventory');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Import fehlgeschlagen: '.$e->getMessage());
        } finally {
            $lock->release();
        }

        if ($code !== 0) {
            return back()->with('error', 'Import fehlgeschlagen: '.trim(strtok(Artisan::output(), "\n")));
        }

        $new = AdComputer::where('created_at', '>=', $start)->count();
        $gone = AdComputer::onlyTrashed()->where('deleted_at', '>=', $start)->count();
        $total = AdComputer::count();

        return back()->with('success', "AD aktualisiert: {$total} Computer ({$new} neu, {$gone} entfernt, Räume/Namen aktualisiert).");
    }

    /**
     * Zuordnung (step 1 of switching to AD): old inv_rooms / inv_items and
     * their AD room / computer, set automatically (AdInventoryLinker) or by hand.
     */
    public function links()
    {
        $paths = $this->ouPaths();

        $rooms = InvRoom::query()
            ->leftJoin('places', 'places.id', '=', 'inv_rooms.place_id')
            ->leftJoin('locations', 'locations.id', '=', 'inv_rooms.location_id')
            ->select(['inv_rooms.id', 'inv_rooms.etage', 'inv_rooms.rname', 'inv_rooms.altrname', 'inv_rooms.ad_ou',
                'inv_rooms.ad_ou_id', 'inv_rooms.ad_link', 'places.pnname as place', 'locations.address as address'])
            ->withCount('invitems')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'place' => $r->place,
                'address' => $r->address,
                'etage' => $r->etage,
                'name' => trim($r->rname.($r->altrname ? ' ('.$r->altrname.')' : '')),
                'old_ou' => $r->ad_ou,
                'items' => $r->invitems_count,
                'ad_id' => $r->ad_ou_id,
                'link' => $r->ad_link,
            ]);

        $items = InvItems::query()
            ->whereIn('inv_items.gart_id', AdInventoryLinker::COMPUTER_GARTS)
            ->leftJoin('inv_rooms', 'inv_rooms.id', '=', 'inv_items.room_id')
            ->leftJoin('garts', 'garts.id', '=', 'inv_items.gart_id')
            ->get(['inv_items.id', 'inv_items.invnr', 'inv_items.gname', 'inv_items.gtyp', 'inv_items.ad_computer_id',
                'inv_items.ad_link', 'garts.name as gart', 'inv_rooms.rname as room'])
            ->map(fn ($i) => [
                'id' => $i->id,
                'invnr' => $i->invnr,
                'name' => $i->gname,
                'gart' => $i->gart,
                'typ' => $i->gtyp,
                'room' => $i->room,
                'ad_id' => $i->ad_computer_id,
                'link' => $i->ad_link,
            ]);

        $adRooms = AdOu::where('type', 'raum')->get(['id'])
            ->map(fn ($o) => ['id' => $o->id, 'label' => $paths[$o->id] ?? ''])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values();

        $adComputers = AdComputer::withTrashed()->get(['id', 'name', 'ad_ou_id', 'deleted_at'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'label' => $paths[$c->ad_ou_id] ?? '',
                'deleted' => $c->deleted_at !== null,
            ])->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return Inertia::render('AdInventory/Links', [
            'rooms' => $rooms,
            'items' => $items,
            'adRooms' => $adRooms,
            'adComputers' => $adComputers,
        ]);
    }

    /** Set / clear the AD room of an old room. mode: ad (with ad_id) | none (not in AD) | auto (let the import decide) */
    public function linkRoom(Request $request, $id)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['ad', 'none', 'auto'])],
            'ad_id' => ['nullable', 'required_if:mode,ad', Rule::exists('ad_ous', 'id')->where('type', 'raum')],
        ]);
        $room = InvRoom::findOrFail($id);
        InvRoom::whereKey($room->id)->update($this->linkValues($data, 'ad_ou_id'));
        if ($data['mode'] === 'auto') {
            AdInventoryLinker::run();
        }
        AdInventoryLinker::backfillTickets(); // tickets follow the corrected link

        return back()->with('success', 'Zuordnung gespeichert.');
    }

    public function linkItem(Request $request, $id)
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['ad', 'none', 'auto'])],
            'ad_id' => ['nullable', 'required_if:mode,ad', Rule::exists('ad_computers', 'id')],
        ]);
        $item = InvItems::findOrFail($id);
        InvItems::whereKey($item->id)->update($this->linkValues($data, 'ad_computer_id'));
        if ($data['mode'] === 'auto') {
            AdInventoryLinker::run();
        }
        AdInventoryLinker::backfillTickets(); // tickets follow the corrected link

        return back()->with('success', 'Zuordnung gespeichert.');
    }

    private function linkValues(array $data, string $column): array
    {
        return match ($data['mode']) {
            'ad' => [$column => (int) $data['ad_id'], 'ad_link' => 'manual'],
            'none' => [$column => null, 'ad_link' => 'manual'],   // "nicht im AD" - auto never relinks
            default => [$column => null, 'ad_link' => null],      // back to automatic
        };
    }

    /** "Dresden › Löscherstr. -16 (DD1) › 3.OG › 3.05" for every OU (containers / groups left out). */
    private function ouPaths(): array
    {
        $ous = AdOu::withTrashed()->get(['id', 'parent_id', 'type', 'label', 'name'])->keyBy('id');
        $paths = [];
        foreach ($ous as $id => $ou) {
            $parts = [];
            $cur = $ou;
            $guard = 0;
            while ($cur && $guard++ < 30) {
                if (! in_array($cur->type, ['container', 'gruppe'], true)) {
                    array_unshift($parts, $cur->label ?: $cur->name);
                }
                $cur = $cur->parent_id ? $ous->get($cur->parent_id) : null;
            }
            $paths[$id] = implode(' › ', $parts);
        }

        return $paths;
    }

    // ---------------------------------------------------------------------
    // Rooms that are not in AD (2026-10-05): kitchen, corridor, WC, ... for
    // Handwerk. Stored in ad_ous with source 'app' under an AD address or
    // floor, so every room picker has one list. The import never touches them.
    // ---------------------------------------------------------------------

    public function storeRoom(Request $request)
    {
        $data = $this->validateRoom($request);
        $parent = AdOu::findOrFail($data['parent_id']);

        $room = new AdOu;
        $room->forceFill([
            'guid' => 'app-'.\Illuminate\Support\Str::uuid(),
            'source' => 'app',
            'parent_id' => $parent->id,
            'standort_ou_id' => $parent->standort_ou_id,
            'name' => $data['name'],
            'label' => $data['name'],
            'type' => 'raum',
            'dn' => 'app:'.$data['name'].'|'.$parent->dn,
            'depth' => $parent->depth + 1,
            'description' => $data['description'] ?? null,
            'created_by' => auth()->id(),
        ])->save();

        return back()->with('success', "Raum \"{$room->name}\" angelegt.");
    }

    public function updateRoom(Request $request, $id)
    {
        $room = AdOu::where('source', 'app')->findOrFail($id);
        $data = $this->validateRoom($request, $room->id);
        $parent = AdOu::findOrFail($data['parent_id']);

        $room->forceFill([
            'parent_id' => $parent->id,
            'standort_ou_id' => $parent->standort_ou_id,
            'name' => $data['name'],
            'label' => $data['name'],
            'dn' => 'app:'.$data['name'].'|'.$parent->dn,
            'depth' => $parent->depth + 1,
            'description' => $data['description'] ?? null,
        ])->save();

        return back()->with('success', 'Raum gespeichert.');
    }

    /** Soft delete: tickets that used the room keep showing its name. */
    public function destroyRoom($id)
    {
        $room = AdOu::where('source', 'app')->findOrFail($id);
        $room->delete();

        return back()->with('success', "Raum \"{$room->name}\" gelöscht.");
    }

    private function validateRoom(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            // only under an address or a floor
            'parent_id' => ['required', Rule::exists('ad_ous', 'id')->whereIn('type', ['adresse', 'etage'])->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $data['name'] = trim($data['name']);

        // no two rooms with the same name in the same place
        $exists = AdOu::where('parent_id', $data['parent_id'])->where('type', 'raum')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get(['label', 'name'])
            ->contains(fn ($o) => mb_strtolower($o->label ?: $o->name) === mb_strtolower($data['name']));
        if ($exists) {
            throw \Illuminate\Validation\ValidationException::withMessages(['name' => 'Diesen Raum gibt es hier schon.']);
        }

        return $data;
    }
}
