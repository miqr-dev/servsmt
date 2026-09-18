// Item catalog for the Printmarketing creation form, transcribed from the
// *actually rendered* markup in resources/views/korso/partials/
// {flyer_info,give_aways,stationery,signage,fair_and_misc}.blade.php.
//
// flyer_info.blade.php also declares a `$flyerItems` PHP array (~100 lines)
// that is never referenced anywhere in that file's rendered HTML - the real
// markup below it builds every checkbox from separate, hand-written
// `@foreach` loops with entirely different field names. That array is dead
// code and is not reproduced here; every key below comes from the live
// `name="..."` attributes instead. This was cross-checked against
// KorsoController@printmarketingManagement's `$flyerKeys` list (the report
// page these same item names feed into), which matches - including two
// quirks worth flagging rather than "fixing": the "MISO" section's third
// field is genuinely named `MISO_MISO-K mit JobBSK` (a literal space in the
// HTML name attribute, from `$name = "MISO_{$lang}"` with
// `$lang = 'MISO-K mit JobBSK'`), and several categories the management
// report lists 4 language variants for (BAMF_Alpha_*, BAMF_Zweit_*,
// BAMF_Gering_*) only ever have a single `DE_EN` checkbox on this form
// (`@foreach(['DE_EN'] as $lang)`) - the other 3 report rows can never
// actually be populated by this form. Both are preserved as-is.

export type PMItem = { key: string; label: string; qty: number };
export type PMGrayItem = PMItem & { grayOut?: { from: string; to: string } };
export type PMSection = { heading: string; items: PMItem[] };

// --- Flyer / Infomaterial tab ---

export const flyerKompakt: PMItem[] = [
    { key: 'AfA_Kompakt', label: 'AfA Kompaktflyer', qty: 250 },
    { key: 'DRV_Kompakt', label: 'DRV Kompaktflyer', qty: 250 },
];

export const flyerArbeitgeber: PMItem[] = [
    { key: 'Arbeitgeberflyer_Weiterbildung', label: 'Weiterbildung', qty: 100 },
    { key: 'Arbeitgeberflyer_Umschulung', label: 'Umschulung', qty: 100 },
];

// The 4 columns under "A4-Infomaterial", each a stack of labeled sections.
export const flyerColumns: PMSection[][] = [
    [
        {
            heading: 'AfA',
            items: [
                { key: 'Aktualisierung_APO', label: 'APO', qty: 50 },
                { key: 'Aktualisierung_PAA', label: 'PAA', qty: 50 },
                { key: 'Aktualisierung_BUS', label: 'BUS', qty: 50 },
                { key: 'Aktualisierung_ISO', label: 'ISO', qty: 50 },
                { key: 'Aktualisierung_ISO_Kompakt', label: 'ISO Kompakt', qty: 50 },
                { key: 'Aktualisierung_IBO', label: 'IBO', qty: 50 },
                { key: 'Aktualisierung_MWe_Kompakt', label: 'MWe Kompakt', qty: 50 },
                { key: 'Aktualisierung_Bewerbungstraining', label: 'Bewerbungstraining', qty: 50 },
                { key: 'Aktualisierung_kbQ', label: 'kbQ', qty: 50 },
                { key: 'Aktualisierung_Umschulung_Kompakt', label: 'Umschulung Kompakt', qty: 50 },
                { key: 'Aktualisierung_KBM', label: 'KBM', qty: 50 },
                { key: 'Aktualisierung_IK', label: 'IK', qty: 50 },
                { key: 'Aktualisierung_KiG', label: 'KiG', qty: 50 },
                { key: 'Aktualisierung_Weiterfuehrendes_AG_Migranten', label: 'Weiterführendes AG Migranten', qty: 50 },
            ],
        },
    ],
    [
        {
            heading: 'DRV',
            items: [
                { key: 'DRV_FOSI', label: 'FOSI', qty: 50 },
                { key: 'DRV_OSI', label: 'OSI', qty: 50 },
                { key: 'DRV_BT_S', label: 'BT S', qty: 50 },
                { key: 'DRV_VL_bbU', label: 'VL bbU', qty: 50 },
                { key: 'DRV_bbU', label: 'bbU', qty: 50 },
                { key: 'DRV_RVL', label: 'RVL', qty: 50 },
                { key: 'DRV_RVL_intensiv', label: 'RVL intensiv', qty: 50 },
                { key: 'DRV_Umschulung_Kompakt', label: 'Umschulung Kompakt', qty: 50 },
                { key: 'DRV_KBM', label: 'KBM', qty: 50 },
                { key: 'DRV_IK', label: 'IK', qty: 50 },
                { key: 'DRV_KIG', label: 'KIG', qty: 50 },
            ],
        },
        {
            heading: 'Berufssprachkurse',
            items: [
                { key: 'Berufssprachkurse_BAMF-DE_EN', label: 'BAMF-DE/EN', qty: 50 },
                { key: 'Berufssprachkurse_BAMF-DE_UA', label: 'BAMF-DE/UA', qty: 50 },
                { key: 'Berufssprachkurse_BAMF-DE_AR', label: 'BAMF-DE/AR', qty: 50 },
                { key: 'Berufssprachkurse_BAMF-DE_ES', label: 'BAMF-DE/ES', qty: 50 },
            ],
        },
    ],
    [
        {
            heading: 'BAMF – Integrationskurse',
            items: [
                { key: 'BAMF_Deutsch_DE_EN', label: 'DE/EN', qty: 50 },
                { key: 'BAMF_Deutsch_DE_UA', label: 'DE/UA', qty: 50 },
                { key: 'BAMF_Deutsch_DE_AR', label: 'DE/AR', qty: 50 },
                { key: 'BAMF_Deutsch_DE_ES', label: 'DE/ES', qty: 50 },
            ],
        },
        { heading: 'BAMF-Alphakurse', items: [{ key: 'BAMF_Alpha_DE_EN', label: 'DE/EN', qty: 50 }] },
        { heading: 'BAMF-Zweitschriftlernerkurse', items: [{ key: 'BAMF_Zweit_DE_EN', label: 'DE/EN', qty: 50 }] },
        {
            heading: 'BAMF – Integrationskurs für gering Literalisierte',
            items: [{ key: 'BAMF_Gering_DE_EN', label: 'DE/EN', qty: 50 }],
        },
        { heading: 'BAMF Kompakt', items: [{ key: 'BAMF_Kompakt', label: 'DE', qty: 50 }] },
    ],
    [
        {
            heading: 'MAPO',
            items: [
                { key: 'MAPO_DE_EN', label: 'DE/EN', qty: 50 },
                { key: 'MAPO_DE_UA', label: 'DE/UA', qty: 50 },
            ],
        },
        {
            heading: 'MISO',
            items: [
                { key: 'MISO_DE_EN', label: 'DE/EN', qty: 50 },
                { key: 'MISO_DE_UA', label: 'DE/UA', qty: 50 },
                { key: 'MISO_MISO-K mit JobBSK', label: 'MISO-K mit JobBSK', qty: 50 },
            ],
        },
        { heading: 'MIBO', items: [{ key: 'MIBO_DE_EN', label: 'DE/EN', qty: 50 }] },
        {
            heading: 'Sonstiges',
            items: [
                { key: 'Willkommensmappe', label: 'Willkommensmappe', qty: 50 },
                { key: 'Willkommensmappe_freie_MA', label: 'Willkommensmappe freie MA', qty: 50 },
                { key: 'Organigramm', label: 'Organigramm', qty: 50 },
                { key: 'Erstellung_Anzeige', label: 'Erstellung Anzeige', qty: 50 },
                { key: 'Vorlage_Schuelerausweise', label: 'Vorlage Schülerausweise', qty: 50 },
                { key: 'Vorlage_Namensschilder', label: 'Vorlage Namensschilder', qty: 50 },
                { key: 'Vorlage_Ansprechpartner', label: 'Vorlage Ansprechpartner', qty: 50 },
            ],
        },
    ],
];

// --- Give Aways tab ---
// hasWarning === false items render first, then (if any hasWarning === true
// items exist, which they always do) a quarterly-order-only warning banner,
// then the hasWarning === true items - some of which are additionally
// grayed out/disabled outside a fixed date window.

export const giveawaysBeforeWarning: PMItem[] = [
    { key: 'City_Cards_Do_you_speak_German', label: 'City Cards – „Do you speak German“', qty: 50 },
    { key: 'City_Cards_Salad', label: 'City Cards – Salad', qty: 50 },
    { key: 'City_Cards_Sauerkraut', label: 'City Cards – Sauerkraut', qty: 50 },
    { key: 'City_Cards_Smiley', label: 'City Cards – Smiley', qty: 50 },
    { key: 'City_Cards_Egg', label: 'City Cards – Egg', qty: 50 },
];

export const giveawaysAfterWarning: PMGrayItem[] = [
    { key: 'Tragetaschen', label: 'Tragetaschen Baumwolle', qty: 50 },
    { key: 'Einkaufswagenloeser', label: 'Einkaufswagenlöser', qty: 50, grayOut: { from: '2025-11-01', to: '2025-12-01' } },
    { key: 'Pflastermaeppchen', label: 'Pflastermäppchen', qty: 1, grayOut: { from: '2025-11-01', to: '2025-12-01' } },
    { key: 'Fruchtgummi', label: 'Fruchtgummi', qty: 250, grayOut: { from: '2025-11-01', to: '2025-12-01' } },
    { key: 'Kugelschreiber', label: 'Kugelschreiber Rot', qty: 100 },
];

// --- Geschäftsausstattung (Stationery) tab ---

export const stationeryBeforeWarning: PMItem[] = [
    { key: 'Visitenkarten', label: 'Visitenkarten', qty: 100 },
    { key: 'Glueckwunschkarte_Alles_Gute', label: 'Glückwunschkarte A6 – „Alles Gute“', qty: 50 },
    { key: 'Glueckwunschkarte_blanco', label: 'Glückwunschkarte A6 – blanco', qty: 50 },
];

export const stationeryAfterWarning: PMGrayItem[] = [
    { key: 'GA_Mappen', label: 'GA – Mappen', qty: 100 },
    { key: 'Zeugnismappe', label: 'Zeugnismappe', qty: 100 },
    { key: 'Zeugnismappe_Deutschkurse', label: 'Zeugnismappe Deutschkurse', qty: 100 },
    { key: 'Zeugnispapier', label: 'Zeugnispapier', qty: 200 },
    { key: 'A5_Ringblock', label: 'A5 Ringblock', qty: 50, grayOut: { from: '2025-11-01', to: '2025-12-31' } },
    { key: 'A4_Schreibblock_Streifen', label: 'A4 Schreibblock Streifen', qty: 100 },
    { key: 'USB_Stick', label: 'USB-Stick', qty: 20, grayOut: { from: '2025-11-01', to: '2025-12-31' } },
    { key: 'Notizblock_PostIt_Stift', label: 'Notizblock mit Post it + Stift', qty: 50, grayOut: { from: '2025-11-01', to: '2025-12-01' } },
    { key: 'Block_A6', label: 'Block A6', qty: 100 },
];

// Rendered after stationeryAfterWarning's flat items, in this order.
export const stationeryVersandtaschen: { label: string; items: PMItem[] }[] = [
    {
        label: 'Versandtasche mit Fenster',
        items: [
            { key: 'Versandtasche_Fenster_C4', label: 'C4', qty: 100 },
            { key: 'Versandtasche_Fenster_C5', label: 'C5', qty: 100 },
            { key: 'Versandtasche_Fenster_DL', label: 'DL', qty: 100 },
        ],
    },
    {
        label: 'Versandtasche ohne Fenster',
        items: [
            { key: 'Versandtasche_ohne_Fenster_C4', label: 'C4', qty: 100 },
            { key: 'Versandtasche_ohne_Fenster_C5', label: 'C5', qty: 100 },
            { key: 'Versandtasche_ohne_Fenster_DL', label: 'DL', qty: 100 },
        ],
    },
];

// Fields for the Visitenkarten "extra" block, revealed when that checkbox is
// checked. Field names match KorsoController@form_store_korso's
// `visitenkarte_*` inputs exactly (this is the "position"+"adresse", no
// "mobile" variant - see Korso/Show.vue's doc comment on the same
// inconsistency in the two old ticket-detail partials).
export const visitenkarteFields: { key: string; label: string; type: string }[] = [
    { key: 'visitenkarte_name', label: 'Name, Vorname', type: 'text' },
    { key: 'visitenkarte_adresse', label: 'Adresse', type: 'text' },
    { key: 'visitenkarte_email', label: 'E-Mail', type: 'email' },
    { key: 'visitenkarte_telephone', label: 'Telefon', type: 'text' },
    { key: 'visitenkarte_position', label: 'Fachbereich / Position', type: 'text' },
    { key: 'visitenkarte_fax', label: 'Fax', type: 'text' },
];

// --- Beschilderung / Gestaltung tab ---

export const signageItems: PMItem[] = [
    { key: 'Beklebung', label: 'Beklebung', qty: 1 },
    { key: 'Beschilderung', label: 'Beschilderung', qty: 1 },
    { key: 'Plakate', label: 'Plakate', qty: 1 },
];

// --- Messe & Sonstiges tab ---

export const messeItems: PMItem[] = [
    { key: 'Anmeldung_Messe', label: 'Anmeldung Messe', qty: 1 },
    { key: 'Ausstattung_Messe', label: 'Ausstattung Messe', qty: 1 },
    { key: 'Rollup_Anzahl', label: 'Rollup Anzahl', qty: 1 },
    { key: 'Plakate_Anzahl', label: 'Plakate Anzahl', qty: 1 },
    { key: 'Messestand', label: 'Messestand', qty: 1 },
    { key: 'Beach_Flag', label: 'Beach Flag', qty: 1 },
    { key: 'Sonstiges', label: 'Sonstiges', qty: 1 },
];

// Date-window gray-out check, mirroring the old page's server-rendered
// `!$now->between($from, $to)` (computed once per page load there; computed
// live here since there's nothing server-trust-sensitive about it).
export function isGrayedOut(item: PMGrayItem): boolean {
    if (!item.grayOut) return false;
    const now = Date.now();
    return !(now >= new Date(item.grayOut.from).getTime() && now <= new Date(item.grayOut.to).getTime());
}

export function grayOutTooltip(item: PMGrayItem): string {
    if (!item.grayOut) return '';
    const fmt = (d: string) =>
        new Date(d).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
    return `Verfügbar vom ${fmt(item.grayOut.from)} bis ${fmt(item.grayOut.to)}`;
}
