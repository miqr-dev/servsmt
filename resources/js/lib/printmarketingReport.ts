// Category layout of the Printmarketing Verwaltung report
// (Korso/PrintmarketingManagement.vue). Key lists copied verbatim from the old
// korso/Printmarketing/management.blade.php and KorsoController@exportPrintmarketingPdf
// (which keeps its own copy for the PDF - keep the two in sync).

export type PMReportCategory = { key: 'flyer' | 'giveaways' | 'stationery' | 'signage' | 'messe'; label: string; items: string[] };

export const PM_REPORT_CATEGORIES: PMReportCategory[] = [
    {
        key: 'flyer',
        label: 'Flyer / Infomaterial',
        items: [
            'AfA_Kompakt', 'Arbeitgeberflyer_Umschulung', 'Arbeitgeberflyer_Weiterbildung', 'DRV_Kompakt',
            'Aktualisierung_APO', 'Aktualisierung_PAA', 'Aktualisierung_BUS', 'Aktualisierung_ISO', 'Aktualisierung_ISO_Kompakt',
            'Aktualisierung_IBO', 'Aktualisierung_MWe_Kompakt', 'Aktualisierung_Bewerbungstraining', 'Aktualisierung_kbQ',
            'Aktualisierung_Umschulung_Kompakt', 'Aktualisierung_KBM', 'Aktualisierung_IK', 'Aktualisierung_KiG',
            'Aktualisierung_Weiterfuehrendes_AG_Migranten',
            'DRV_FOSI', 'DRV_OSI', 'DRV_BT_S', 'DRV_VL_bbU', 'DRV_bbU', 'DRV_RVL', 'DRV_RVL_intensiv', 'DRV_Umschulung_Kompakt',
            'DRV_KBM', 'DRV_IK', 'DRV_KIG',
            'Berufssprachkurse_BAMF-DE_EN', 'Berufssprachkurse_BAMF-DE_UA', 'Berufssprachkurse_BAMF-DE_AR', 'Berufssprachkurse_BAMF-DE_ES',
            'BAMF_Deutsch_DE_EN', 'BAMF_Deutsch_DE_UA', 'BAMF_Deutsch_DE_AR', 'BAMF_Deutsch_DE_ES',
            'BAMF_Alpha_DE_EN', 'BAMF_Alpha_DE_UA', 'BAMF_Alpha_DE_AR', 'BAMF_Alpha_DE_ES',
            'BAMF_Zweit_DE_EN', 'BAMF_Zweit_DE_UA', 'BAMF_Zweit_DE_AR', 'BAMF_Zweit_DE_ES',
            'BAMF_Gering_DE_EN', 'BAMF_Gering_DE_UA', 'BAMF_Gering_DE_AR', 'BAMF_Gering_DE_ES', 'BAMF_Gering_BAMF_Kompakt',
            'MAPO_DE_EN', 'MAPO_DE_UA', 'MISO_DE_EN', 'MISO_DE_UA', 'MISO_MISO-K mit JobBSK', 'MIBO_DE_EN', 'MIBO_DE_UA',
            'Willkommensmappe', 'Willkommensmappe_freie_MA', 'Organigramm', 'Erstellung_Anzeige',
            'Vorlage_Schuelerausweise', 'Vorlage_Namensschilder', 'Vorlage_Ansprechpartner',
        ],
    },
    {
        key: 'giveaways',
        label: 'Give Aways',
        items: [
            'City_Cards_Do_you_speak_German', 'City_Cards_Salad', 'City_Cards_Sauerkraut', 'City_Cards_Smiley', 'City_Cards_Egg',
            'Tragetaschen', 'Einkaufswagenloeser', 'Pflastermaeppchen', 'Fruchtgummi', 'Kugelschreiber',
        ],
    },
    {
        key: 'stationery',
        label: 'Geschäftsausstattung',
        items: [
            'Visitenkarten', 'Glueckwunschkarte_Alles_Gute', 'Glueckwunschkarte_blanco', 'GA_Mappen', 'Zeugnismappe',
            'Zeugnismappe_Deutschkurse', 'Zeugnispapier', 'A5_Ringblock', 'A4_Schreibblock_Streifen', 'USB_Stick',
            'Notizblock_PostIt_Stift',
            'Versandtasche_Fenster_C4', 'Versandtasche_Fenster_C5', 'Versandtasche_Fenster_DL',
            'Versandtasche_ohne_Fenster_C4', 'Versandtasche_ohne_Fenster_C5', 'Versandtasche_ohne_Fenster_DL',
            'Block_A6',
        ],
    },
    { key: 'signage', label: 'Beschilderung / Gestaltung', items: ['Beklebung', 'Beschilderung', 'Plakate'] },
    {
        key: 'messe',
        label: 'Messe & Sonstiges',
        items: ['Anmeldung_Messe', 'Ausstattung_Messe', 'Rollup_Anzahl', 'Plakate_Anzahl', 'Messestand', 'Beach_Flag', 'Sonstiges'],
    },
];

// Geschäftsausstattung shows the six Versandtasche keys as two small groups
// (mit / ohne Fenster, each C4 / C5 / DL) instead of six loose rows - same as
// the old page.
export const VERSANDTASCHE_GROUPS: { label: string; items: Record<string, string> }[] = [
    {
        label: 'Versandtasche mit Fenster',
        items: { Versandtasche_Fenster_C4: 'C4', Versandtasche_Fenster_C5: 'C5', Versandtasche_Fenster_DL: 'DL' },
    },
    {
        label: 'Versandtasche ohne Fenster',
        items: { Versandtasche_ohne_Fenster_C4: 'C4', Versandtasche_ohne_Fenster_C5: 'C5', Versandtasche_ohne_Fenster_DL: 'DL' },
    },
];

/** "Aktualisierung_APO" -> "Aktualisierung APO" (the old page printed the raw key). */
export function pmItemLabel(key: string): string {
    return key.replace(/_/g, ' ');
}
