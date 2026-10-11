<?php

namespace App\Support;

use App\InvItems;
use App\InvRoom;

/**
 * Old inventory ids for an AD room / PC (AD switch step 4, 2026-10-09).
 *
 * The new forms post AD ids (ad_room_id, ad_computer_id, ...). During the
 * transition the ticket also gets the old id where a link exists (step 1:
 * inv_rooms.ad_ou_id, inv_items.ad_computer_id), so anything that still
 * reads room_id / location_id / gname_id keeps working. No link -> null.
 */
class AdLegacy
{
    /** first old room linked to this AD room; null when no Raum was picked (only Adresse / Etage) */
    public static function roomId(?int $ouId): ?int
    {
        $raum = $ouId ? AdLocation::ancestry($ouId)['raum'] : null;
        if (! $raum) {
            return null;
        }
        $id = InvRoom::whereIn('ad_ou_id', AdLocation::descendantIds($raum))->orderBy('id')->value('id');

        return $id ? (int) $id : null;
    }

    /** old address (locations.id): the one most old rooms below this OU belong to */
    public static function locationId(?int $ouId): ?int
    {
        if (! $ouId) {
            return null;
        }
        $id = InvRoom::whereIn('ad_ou_id', AdLocation::descendantIds($ouId))
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->orderByRaw('COUNT(*) DESC')
            ->value('location_id');

        return $id ? (int) $id : null;
    }

    /** old inventory item (inv_items.id) linked to this AD computer */
    public static function itemId(?int $computerId): ?int
    {
        if (! $computerId) {
            return null;
        }
        $id = InvItems::where('ad_computer_id', $computerId)->orderBy('id')->value('id');

        return $id ? (int) $id : null;
    }
}
