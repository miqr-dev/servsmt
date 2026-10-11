// Address / room of a Handwerk ticket for lists and the detail page (AD switch
// step 3, 2026-10-09). New and backfilled tickets carry `ad_room` (appended by
// App\Handwerk, built by App\Support\AdLocation::room); old tickets without an
// AD link fall back to the old inv_rooms room / location, then to custom_room.

export type HandwerkAdRoom = {
    standort: string | null;
    adresse: string | null;
    label: string;
    path: string;
    deleted: boolean;
} | null;

type RowLike = {
    ad_room?: HandwerkAdRoom;
    room?: { rname: string | null; altrname: string | null } | null;
    location?: { address: string | null } | null;
    custom_room?: string | null;
};

export function handwerkAddress(row: RowLike): string {
    const ad = row.ad_room;
    if (ad?.adresse) return [ad.adresse, ad.standort].filter(Boolean).join(', ');
    return row.location?.address ?? '';
}

export function handwerkRoom(row: RowLike): string {
    const ad = row.ad_room;
    // label is '' when only an Adresse was picked (step 4) -> old room / free text
    if (ad?.label) return ad.label + (ad.deleted ? ' (nicht mehr im AD)' : '');
    const old = [row.room?.rname, row.room?.altrname].filter(Boolean).join(' ');
    return old || (row.custom_room ?? '');
}
