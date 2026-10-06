<?php

namespace App\Support;

use App\AdComputer;
use App\AdOu;
use App\InvItems;
use App\InvRoom;
use App\InvSubRooms;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Links the old inventory to the AD import (step 1, 2026-10-05):
 *   inv_rooms.ad_ou_id       <- inv_rooms.ad_ou (DN), else the OU of one of its
 *                               inv_sub_rooms (Teilnehmer/Dozent) -> its Raum
 *   inv_items.ad_computer_id <- inv_items.gname = ad_computers.name
 * Only fills links that are empty or were set automatically; manual links
 * (ad_link = 'manual', set on /ad-inventory/zuordnung) are never touched.
 * Runs after every ad:import-inventory, or `php artisan ad:link-inventory`.
 *
 * backfillTickets() (step 2): copies those links onto the tickets that have
 * an old room / computer (tickets.ad_room_id / ad_computer_id,
 * ticket_pcs.ad_computer_id, handwerks.ad_room_id). Always derived from the
 * old id, so correcting a link on /ad-inventory/zuordnung also corrects the
 * tickets. Tickets without an old id (new AD-only tickets) are not touched.
 */
class AdInventoryLinker
{
    /** inventory garts that are computers in AD: 1 Server, 2 PC, 3 Laptop */
    public const COMPUTER_GARTS = [1, 2, 3];

    public static function run(bool $dryRun = false): array
    {
        $stats = ['rooms_linked' => 0, 'rooms_unlinked' => 0, 'items_linked' => 0, 'items_unlinked' => 0];

        // --- rooms ---
        $ous = AdOu::withTrashed()->get(['id', 'parent_id', 'type', 'dn', 'deleted_at'])->keyBy('id');
        $ouByDn = $ous->filter(fn ($o) => ! $o->deleted_at)->keyBy(fn ($o) => self::norm($o->dn));
        $roomOf = function (?AdOu $ou) use ($ous) {
            // the OU itself if it is a Raum, else the nearest Raum above (e.g. Teilnehmer-TCs -> 3.05)
            while ($ou) {
                if ($ou->type === 'raum') {
                    return $ou->id;
                }
                $ou = $ou->parent_id ? $ous->get($ou->parent_id) : null;
            }

            return null;
        };
        $subOus = InvSubRooms::all()->groupBy('room_id');

        foreach (InvRoom::all() as $room) {
            if ($room->ad_link === 'manual') {
                continue;
            }
            $adId = $roomOf($ouByDn->get(self::norm($room->ad_ou)));
            foreach ($subOus->get($room->id, []) as $sub) {
                $adId ??= $roomOf($ouByDn->get(self::norm($sub->subRoom_OU)));
            }
            $adId ? $stats['rooms_linked']++ : $stats['rooms_unlinked']++;
            if (! $dryRun && (int) $room->ad_ou_id !== (int) $adId) {
                InvRoom::whereKey($room->id)->update(['ad_ou_id' => $adId, 'ad_link' => $adId ? 'auto' : null]);
            }
        }

        // --- computers (by name; active AD computers first) ---
        $byName = AdComputer::withTrashed()->orderByRaw('deleted_at is null desc')->get(['id', 'name', 'deleted_at'])
            ->groupBy(fn ($c) => mb_strtolower(trim($c->name)));

        foreach (InvItems::whereIn('gart_id', self::COMPUTER_GARTS)->get(['id', 'gname', 'ad_computer_id', 'ad_link']) as $item) {
            if ($item->ad_link === 'manual') {
                continue;
            }
            $matches = $byName->get(mb_strtolower(trim((string) $item->gname)));
            // ambiguous (two active AD computers with the same name) -> leave for manual
            $active = $matches?->whereNull('deleted_at');
            $adId = ($active && $active->count() > 1) ? null : $matches?->first()?->id;
            $adId ? $stats['items_linked']++ : $stats['items_unlinked']++;
            if (! $dryRun && (int) $item->ad_computer_id !== (int) $adId) {
                InvItems::whereKey($item->id)->update(['ad_computer_id' => $adId, 'ad_link' => $adId ? 'auto' : null]);
            }
        }

        return $stats;
    }

    public static function norm(?string $dn): string
    {
        $parts = preg_split('/(?<!\\\\),/', (string) $dn);

        return implode(',', array_values(array_filter(array_map(fn ($p) => mb_strtolower(trim($p)), $parts))));
    }

    /** @return array<string,int> updated rows per column */
    public static function backfillTickets(): array
    {
        $done = [];
        if (! Schema::hasColumn('tickets', 'ad_room_id')) {
            return $done; // step 2 migration not run yet
        }

        $done['tickets.ad_room_id'] = DB::table('tickets')
            ->join('inv_rooms', 'inv_rooms.id', '=', 'tickets.room_id')
            ->whereRaw('NOT (tickets.ad_room_id <=> inv_rooms.ad_ou_id)')
            ->update(['tickets.ad_room_id' => DB::raw('inv_rooms.ad_ou_id')]);

        $done['tickets.ad_computer_id'] = DB::table('tickets')
            ->join('inv_items', 'inv_items.id', '=', 'tickets.gname_id')
            ->whereRaw('NOT (tickets.ad_computer_id <=> inv_items.ad_computer_id)')
            ->update(['tickets.ad_computer_id' => DB::raw('inv_items.ad_computer_id')]);

        $done['ticket_pcs.ad_computer_id'] = DB::table('ticket_pcs')
            ->join('inv_items', 'inv_items.id', '=', 'ticket_pcs.inv_item_id')
            ->whereRaw('NOT (ticket_pcs.ad_computer_id <=> inv_items.ad_computer_id)')
            ->update(['ticket_pcs.ad_computer_id' => DB::raw('inv_items.ad_computer_id')]);

        $done['handwerks.ad_room_id'] = DB::table('handwerks')
            ->join('inv_rooms', 'inv_rooms.id', '=', 'handwerks.room_id')
            ->whereRaw('NOT (handwerks.ad_room_id <=> inv_rooms.ad_ou_id)')
            ->update(['handwerks.ad_room_id' => DB::raw('inv_rooms.ad_ou_id')]);

        return $done;
    }
}
