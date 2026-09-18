// Shared "checkbox item -> qty" definitions for Handwerk furniture/electro
// tickets. Mirrors the boolean columns on the Handwerk model exactly (see
// HandwerkController@form_store_handwerk for the full field list). Used by
// both the ticket detail page (Handwerk/Show.vue, read-only display) and the
// "Mobiliar - Einrichtung" / "Mobiliar - Elektro" creation forms
// (Handwerk/New/Einrichtungsgegenstaende.vue, Elektro.vue), so the item list
// only has to be maintained in one place.

export type ItemDef = { key: string; label: string };
export type ItemGroup = { title: string; items: ItemDef[] };

export const TAFEL: ItemGroup = {
    title: 'Tafel',
    items: [
        { key: 'schiebetafel', label: 'Schiebetafel' },
        { key: 'whiteboard', label: 'Whiteboard' },
        { key: 'kreidetafel', label: 'Kreidetafel' },
        { key: 'pinnwand', label: 'Pinnwand' },
    ],
};
export const STUHL: ItemGroup = {
    title: 'Stuhl',
    items: [
        { key: 'schreibtischstuhl', label: 'Schreibtischstuhl' },
        { key: 'bürostuhl', label: 'Bürostuhl' },
        { key: 'stapelstühl', label: 'Stapelstühl' },
    ],
};
export const SCHRANK: ItemGroup = {
    title: 'Schrank',
    items: [
        { key: 'rollcontainer', label: 'Rollcontainer' },
        { key: 'standcontainer', label: 'Standcontainer' },
        { key: 'hochschrank', label: 'Hochschrank' },
        { key: 'ordnerhöhen_2', label: 'Ordnerhöhen 2' },
        { key: 'ordnerhöhen_3', label: 'Ordnerhöhen 3' },
        { key: 'hängeschrank', label: 'Hängeschrank' },
    ],
};
export const SONNENSCHUTZ: ItemGroup = {
    title: 'Sonnenschutz',
    items: [
        { key: 'lamellenvorhang', label: 'Lamellenvorhang' },
        { key: 'rollo', label: 'Rollo' },
    ],
};
export const DECORATION: ItemGroup = { title: 'Dekoration', items: [{ key: 'bilder', label: 'Bilder' }] };
export const WC: ItemGroup = {
    title: 'WC',
    items: [
        { key: 'handtuchspender', label: 'Handtuchspender' },
        { key: 'toilettenpapierhalter', label: 'Toilettenpapierhalter' },
        { key: 'desinfektionsmittelspender', label: 'Desinfektionsmittelspender' },
    ],
};
export const KUECHE: ItemGroup = {
    title: 'Küche',
    items: [
        { key: 'barzeile', label: 'Barzeile' },
        { key: 'bar_Hochstühle', label: 'Bar Hochstühle' },
        { key: 'küchenzeile', label: 'küchenzeile' },
    ],
};
export const ELEKTRO: ItemGroup = {
    title: 'Elektro',
    items: [
        { key: 'kühlschrank', label: 'Kühlschrank' },
        { key: 'geschirrspüler', label: 'Geschirrspüler' },
        { key: 'kaffeemaschine', label: 'Kaffeemaschine' },
        { key: 'ventilator', label: 'Ventilator' },
    ],
};

// "Tisch" (table) items are special-cased: the desk families (Schreibtisch
// TN/DOZ/MA) are a parent label with 3 selectable size variants each, while
// the rest are plain checkbox+qty items. DESK_FAMILIES/OTHER_TISCH_ITEMS are
// only needed by the creation form (Einrichtungsgegenstaende.vue) - the show
// page just lists every dimension as its own flat item in TISCH below.
export type DeskFamily = { key: string; label: string; dims: ItemDef[] };

export const DESK_FAMILIES: DeskFamily[] = [
    {
        key: 'schreibtisch_TN',
        label: 'Schreibtisch TN',
        dims: [
            { key: 'schreibtisch_TN_70x70', label: '70 x 70' },
            { key: 'schreibtisch_TN_80x80', label: '80 x 80' },
            { key: 'schreibtisch_TN_80x160', label: '80 x 160' },
        ],
    },
    {
        key: 'schreibtisch_DOZ',
        label: 'Schreibtisch DOZ',
        dims: [
            { key: 'schreibtisch_DOZ_80x140', label: '80 x 140' },
            { key: 'schreibtisch_DOZ_80x160', label: '80 x 160' },
            { key: 'schreibtisch_DOZ_80x180', label: '80 x 180' },
        ],
    },
    {
        key: 'schreibtisch_MA',
        label: 'Schreibtisch MA',
        dims: [
            { key: 'schreibtisch_MA_80x140', label: '80 x 140' },
            { key: 'schreibtisch_MA_80x160', label: '80 x 160' },
            { key: 'schreibtisch_MA_80x180', label: '80 x 180' },
        ],
    },
];
export const OTHER_TISCH_ITEMS: ItemDef[] = [
    { key: 'stehtisch', label: 'Stehtisch' },
    { key: 'gesprächstisch_rund', label: 'Gesprächstisch rund' },
    { key: 'konferenztisch', label: 'Konferenztisch' },
    { key: 'couchtisch', label: 'Couchtisch' },
    { key: 'beistelltisch', label: 'Beistelltisch' },
];

export const TISCH: ItemGroup = {
    title: 'Tisch',
    items: [...DESK_FAMILIES.flatMap((family) => family.dims), ...OTHER_TISCH_ITEMS],
};

export const ITEM_GROUPS_BY_TYPE: Record<string, ItemGroup[]> = {
    'Mobiliar - Einrichtung': [TAFEL, TISCH, STUHL, SCHRANK, SONNENSCHUTZ, DECORATION, WC, KUECHE],
    'Mobiliar - Elektro': [ELEKTRO],
};

// Types whose ticket-detail partial shows a free-text "Betreff" field instead
// of item groups.
export const SUBJECT_TYPES = ['Modifikation', 'Reparatur - Elektro', 'Reparatur - Mobiliar'];

// Toggling an item's checkbox on any creation form should default its
// quantity to 1, and clear it back out when unchecked - rather than leaving
// a stale or empty-but-visible number behind. Shared so every checkbox in
// ItemCheckboxGroup.vue and the Tisch desk-family section behaves the same.
export function setItemChecked(form: Record<string, unknown>, key: string, checked: boolean) {
    form[key] = checked;
    form[`${key}_qty`] = checked ? '1' : '';
}
