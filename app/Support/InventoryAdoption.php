<?php

namespace App\Support;

use App\AdOu;
use App\InvItems;
use App\InvRoom;
use App\Location;

/**
 * Brings the old inventory's hallways and printer rooms into
 * Einstellungen > Standorte & Adressen (2026-10-09).
 *
 * Old rooms that have no AD room (inv_rooms.ad_ou_id empty) never showed up
 * in the AD pickers, so neither did their printers. This takes every such
 * room that is a hallway ("Flur", "Korridor") or holds a printer and:
 *  1. finds its AD address: from its old OU path (inv_rooms.ad_ou), else the
 *     AD address most linked rooms of the same old address belong to, else
 *     the only (visible) address of the city's AD Standort, else the one
 *     whose name has the same street and house number;
 *  2. finds the floor: from the OU path, else "Flur 3.OG" in the name, else
 *     the old etage column ("3", "3.OG", "EG", "0" = EG);
 *  3. links it to an existing room of the same name there (AD or app), or
 *     creates an app room (AppOus) - nothing is written to AD;
 *  4. sets inv_rooms.ad_ou_id (ad_link 'manual', so the linker keeps it).
 * Printers stay in inv_items: the pickers list the devices of the old rooms
 * linked to an AD room, so they appear there from now on.
 *
 * run(true) = plan only. Safe to repeat: linked rooms are skipped, and an
 * existing room of the same name is reused instead of creating a second one.
 * `php artisan ad:adopt-inventory-rooms [--dry-run]`, or the button on the page.
 */
class InventoryAdoption
{
    public const HALLWAY = '/(flur|korridor)/iu'; // also "Hausflur"

    public const PRINTER_GART = 5;

    /** @return array{rows: list<array>, created: int, linked: int, open: int} */
    public static function run(bool $dryRun = true): array
    {
        $printers = InvItems::where('gart_id', self::PRINTER_GART)->whereNotNull('room_id')
            ->orderBy('gname')->get(['id', 'gname', 'invnr', 'room_id'])->groupBy('room_id');

        $candidates = InvRoom::whereNull('ad_ou_id')->orderBy('location_id')->orderBy('rname')
            ->get(['id', 'location_id', 'etage', 'rname', 'altrname', 'ad_ou'])
            ->filter(fn ($r) => preg_match(self::HALLWAY, $r->rname.' '.$r->altrname) || $printers->has($r->id));

        // AD address of each old address: where most of its linked rooms are
        $votes = [];
        foreach (InvRoom::whereNotNull('ad_ou_id')->whereNotNull('location_id')->get(['location_id', 'ad_ou_id']) as $r) {
            if ($a = AdLocation::ancestry($r->ad_ou_id)['adresse']) {
                $votes[$r->location_id][$a] = ($votes[$r->location_id][$a] ?? 0) + 1;
            }
        }
        $addressOfLocation = array_map(fn ($v) => array_search(max($v), $v), $votes);

        $ous = AdOu::whereIn('type', ['adresse', 'etage', 'raum'])->get(['id', 'parent_id', 'type', 'label', 'name', 'dn', 'hidden']);
        $ouByDn = $ous->keyBy(fn ($o) => AdInventoryLinker::norm($o->dn));
        // floors and rooms per AD address; rooms created in this run are added as we go
        $floors = [];
        $rooms = [];
        foreach ($ous as $o) {
            $up = AdLocation::ancestry($o->id);
            if ($o->type === 'etage' && $up['adresse']) {
                $floors[$up['adresse']][] = ['id' => $o->id, 'key' => self::floorKey($o->label ?: $o->name)];
            } elseif ($o->type === 'raum' && $up['adresse']) {
                $rooms[$up['adresse']][] = ['id' => $o->id, 'parent' => $o->parent_id, 'name' => mb_strtolower($o->label ?: $o->name)];
            }
        }
        $locations = Location::with('place:id,pnname')->get(['id', 'place_id', 'address'])->keyBy('id');

        // fallback by city + street / house number (visible AD addresses only)
        $standortOfCity = AdOu::where('type', 'standort')->get(['id', 'label', 'name'])
            ->mapWithKeys(fn ($o) => [AdStates::norm($o->label ?: $o->name) => $o->id]);
        $addressesOfStandort = [];
        foreach ($ous->where('type', 'adresse') as $o) {
            $st = AdLocation::ancestry($o->id)['standort'];
            if ($st && ! $o->hidden) {
                $addressesOfStandort[$st][] = ['id' => $o->id, 'norm' => self::addressNorm($o->label ?: $o->name)];
            }
        }
        $guessAddress = function ($loc) use ($standortOfCity, $addressesOfStandort): ?int {
            $st = $loc ? ($standortOfCity[AdStates::norm($loc->place->pnname ?? '')] ?? null) : null;
            $list = $st ? ($addressesOfStandort[$st] ?? []) : [];
            if (count($list) === 1) {
                return $list[0]['id'];
            }
            if (! preg_match('/^([a-z]+?)(\d+)/', self::addressNorm($loc?->address), $m)) {
                return null;
            }
            $hits = array_values(array_filter($list, fn ($a) => str_contains($a['norm'], $m[1])
                && preg_match('/(?<!\d)'.$m[2].'(?!\d)/', $a['norm'])));

            return count($hits) === 1 ? $hits[0]['id'] : null;
        };

        $out = ['rows' => [], 'created' => 0, 'linked' => 0, 'open' => 0];
        $newRooms = []; // dry run: "parentId|name" of rooms that would be created

        foreach ($candidates as $r) {
            $loc = $locations->get($r->location_id);
            $row = [
                'old_id' => $r->id,
                'old' => trim($r->rname.($r->altrname ? ' | '.$r->altrname : '')) ?: "#{$r->id}",
                'old_place' => $loc ? trim($loc->address.', '.($loc->place->pnname ?? ''), ', ') : '–',
                'old_floor' => $r->etage,
                'printers' => $printers->get($r->id, collect())->map(fn ($p) => $p->gname ?: $p->invnr)->values()->all(),
                'status' => 'offen',
                'target' => null,
                'reason' => null,
            ];

            // 1. AD address (and maybe floor) from the old OU path, else from the old address
            $up = ['adresse' => null, 'etage' => null];
            if ($r->ad_ou && ($ou = $ouByDn->get(AdInventoryLinker::norm($r->ad_ou)))) {
                $up = array_intersect_key(AdLocation::ancestry($ou->id), $up);
            }
            $addressId = $up['adresse'] ?: ($addressOfLocation[$r->location_id] ?? null) ?: $guessAddress($loc);
            if (! $addressId) {
                $row['reason'] = $loc
                    ? "Keine passende AD-Adresse zu \"{$row['old_place']}\" gefunden."
                    : 'Der alte Raum hat keine Adresse.';
                $out['open']++;
                $out['rows'][] = $row;
                continue;
            }

            // 2. floor
            $floorId = $up['etage'];
            $floorKey = null;
            if (! $floorId) {
                $floorKey = self::floorInName($r->rname.' '.$r->altrname) ?? self::floorKey($r->etage);
                foreach ($floors[$addressId] ?? [] as $f) {
                    if ($floorKey && $f['key'] === $floorKey) {
                        $floorId = $f['id'];
                        break;
                    }
                }
            }
            $parentId = $floorId ?: $addressId;

            // 3. an existing room of that name: same floor, else the only one at the address
            $name = self::roomName($r, $floorId ? null : $floorKey);
            $key = mb_strtolower(trim($r->rname ?: $r->altrname ?: ''));
            $same = array_values(array_filter($rooms[$addressId] ?? [], fn ($x) => $x['name'] === $key));
            $match = collect($same)->firstWhere('parent', $parentId)
                ?? (! $floorId && count($same) === 1 ? $same[0] : null)
                // e.g. "Flur (Drucker)" from an earlier run
                ?? collect($rooms[$addressId] ?? [])->first(fn ($x) => $x['parent'] == $parentId && $x['name'] === mb_strtolower($name));

            if ($match) {
                $targetId = $match['id'];
                $row['status'] = 'verknüpft';
                $row['target'] = AdLocation::room($targetId)['path'] ?? '';
                $out['linked']++;
            } else {
                $row['status'] = 'neu';
                $row['target'] = trim((AdLocation::room($parentId)['path'] ?? '').' › '.$name, ' ›');
                $out['created']++;
                $dupKey = $parentId.'|'.mb_strtolower($name);
                if ($dryRun) {
                    if (isset($newRooms[$dupKey])) {
                        $row['status'] = 'verknüpft'; // a room created for an earlier old room
                        $out['created']--;
                        $out['linked']++;
                    }
                    $newRooms[$dupKey] = true;
                    $targetId = null;
                } else {
                    $targetId = AppOus::create(AdOu::findOrFail($parentId), 'raum', $name)->id;
                    $rooms[$addressId][] = ['id' => $targetId, 'parent' => $parentId, 'name' => mb_strtolower($name)];
                    if ($key !== mb_strtolower($name)) {
                        // also findable by its plain name ("Flur") for the next old room
                        $rooms[$addressId][] = ['id' => $targetId, 'parent' => $parentId, 'name' => $key];
                    }
                }
            }

            // 4. link the old room
            if (! $dryRun && $targetId) {
                InvRoom::whereKey($r->id)->update(['ad_ou_id' => $targetId, 'ad_link' => 'manual']);
            }
            $out['rows'][] = $row;
        }

        if (! $dryRun && ($out['created'] || $out['linked'])) {
            AdInventoryLinker::backfillTickets(); // old tickets of these rooms get the AD room too
        }

        return $out;
    }

    /** "Flur" / "3.10 (Sekretariat)"; a floor that has no AD Etage goes into the name: "Flur (2.OG)" */
    private static function roomName(InvRoom $r, ?string $unmatchedFloor): string
    {
        $rname = trim((string) $r->rname);
        $alt = trim((string) $r->altrname);
        $name = $rname !== '' ? $rname : $alt;
        if ($rname !== '' && $alt !== '' && mb_strtolower($alt) !== mb_strtolower($rname)) {
            $name .= " ({$alt})";
        }
        if ($unmatchedFloor && self::floorInName($name) === null) {
            $name .= ' ('.self::floorLabel($unmatchedFloor).')';
        }

        return mb_substr($name !== '' ? $name : "Raum {$r->id}", 0, 100);
    }

    /** "3.OG" / "3" / "3. Etage" -> "3og", "EG" / "0" -> "eg", "UG" / "KG" -> "ug", "DG" -> "dg" */
    public static function floorKey(?string $text): ?string
    {
        $c = str_replace([' ', '.'], '', mb_strtolower(trim((string) $text)));
        if ($c === '') {
            return null;
        }
        if (preg_match('/^(eg|erdgeschoss|parterre)$/u', $c)) {
            return 'eg';
        }
        if (preg_match('/^(ug|kg|keller|untergeschoss|souterrain|-1)$/u', $c)) {
            return 'ug';
        }
        if (preg_match('/^(dg|dachgeschoss)$/u', $c)) {
            return 'dg';
        }
        if (preg_match('/^(\d+)(og|etage|stock|obergeschoss)?$/u', $c, $m)) {
            return (int) $m[1] === 0 ? 'eg' : ((int) $m[1]).'og';
        }

        return null;
    }

    /** a floor mentioned in a room name: "Flur 3.OG", "Flur 2. Etage", "Flur EG" */
    public static function floorInName(?string $name): ?string
    {
        $t = mb_strtolower((string) $name);
        if (preg_match('/(\d+)\s*\.?\s*(og|etage|stock|obergeschoss)\b/u', $t, $m)) {
            return (int) $m[1] === 0 ? 'eg' : ((int) $m[1]).'og';
        }
        foreach (['eg' => 'eg|erdgeschoss|parterre', 'ug' => 'ug|kg|keller|untergeschoss', 'dg' => 'dg|dachgeschoss'] as $key => $words) {
            if (preg_match("/\\b({$words})\\b/u", $t)) {
                return $key;
            }
        }

        return null;
    }

    /** "Löscherstraße 16" / "Löscherstr. -16 (DD1)" -> "loescherstr16" (the "(DD1)" part is dropped) */
    private static function addressNorm(?string $text): string
    {
        $text = preg_replace('/\([^)]*\)/u', '', (string) $text);

        return preg_replace('/[^a-z0-9]/', '', str_replace('strasse', 'str', AdStates::norm($text)));
    }

    private static function floorLabel(string $key): string
    {
        return preg_match('/^(\d+)og$/', $key, $m) ? "{$m[1]}.OG" : mb_strtoupper($key);
    }
}
