import axios from 'axios';
import { ref } from 'vue';

// AD rooms / PCs for the ticket forms (AD switch step 4, 2026-10-09), from
// GET /ad-locations (AdLocationController@index). Replaces the old
// inventory cascades (item.listen / room_list) in the IT and Handwerk forms.
// One request per page and kind (rooms only / rooms + PCs), cached for a
// few minutes so two pickers on one form share it.

/** state = Bundesland (Einstellungen > Standorte & Adressen), groups the Standort select */
export type AdStandort = { id: number; label: string; state: string | null };
export type AdAdresse = { id: number; standort_id: number; label: string; app?: boolean };
export type AdEtage = { id: number; adresse_id: number; label: string };
export type AdRaum = { id: number; adresse_id: number; etage_id: number | null; label: string; app: boolean };
export type AdPc = {
    id: number;
    name: string;
    group: string | null;
    standort_id: number | null;
    adresse_id: number | null;
    etage_id: number | null;
    room_id: number | null;
};
/** device added in the app ("Gerät hinzufügen", ad_devices); checked = confirmed by IT */
export type AdUserDevice = {
    id: number;
    name: string;
    kind: string;
    invnr: string | null;
    room_id: number | null;
    standort_id?: number | null;
    adresse_id?: number | null;
    etage_id?: number | null;
    checked: boolean;
};
export type AdLocationData = {
    standorte: AdStandort[];
    adressen: AdAdresse[];
    etagen: AdEtage[];
    raeume: AdRaum[];
    computers: AdPc[];
    /** PCs added by users (not in AD), only with { computers: true } */
    pc_devices: AdUserDevice[];
};

/** What a picker has selected. etage is the floor of the picked room (set with it). */
export type AdSelection = { standort: number | ''; adresse: number | ''; etage: number | ''; raum: number | '' };

export const emptySelection = (): AdSelection => ({ standort: '', adresse: '', etage: '', raum: '' });

/** Most specific OU picked (Raum, else Etage, else Adresse) - posted as ad_room_id. */
export function selectionOu(s: AdSelection): number | '' {
    return s.raum || s.etage || s.adresse || '';
}

/** Selection that points at a room (used when a PC is picked first). */
export function selectionForRoom(data: AdLocationData, roomId: number): AdSelection | null {
    const room = data.raeume.find((r) => r.id === roomId);
    if (!room) return null;
    const adresse = data.adressen.find((a) => a.id === room.adresse_id);
    return { standort: adresse?.standort_id ?? '', adresse: room.adresse_id, etage: '', raum: room.id };
}

/** "Döbeln" ~ "doebeln" ~ "DÖBELN" */
export function sameCity(a: string | null | undefined, b: string | null | undefined): boolean {
    const norm = (v: string | null | undefined) =>
        (v ?? '').trim().toLowerCase().replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss');
    return norm(a) !== '' && norm(a) === norm(b);
}

/** "Löscherstraße 16" / "Löscherstr. -16 (DD1)" -> "loescherstr16" (the "(DD1)" part is dropped) */
function addressNorm(v: string | null | undefined): string {
    return (v ?? '')
        .toLowerCase()
        .replace(/\([^)]*\)/g, '')
        .replace(/ä/g, 'ae')
        .replace(/ö/g, 'oe')
        .replace(/ü/g, 'ue')
        .replace(/ß/g, 'ss')
        .replace(/strasse/g, 'str')
        .replace(/[^a-z0-9]/g, '');
}

/**
 * The AD address of a user's street ("Heinrichstraße 92"): same street and
 * house number, else the only address in that street ("Heinrichstr. -89").
 */
export function matchAddress<T extends { label: string }>(list: T[], street: string | null | undefined): T | null {
    const m = addressNorm(street).match(/^([a-z]+?)(\d+)/) ?? addressNorm(street).match(/^([a-z]+)()/);
    if (!m || m[1].length < 4) return null;
    const [, stem, no] = m;
    const sameStreet = list.filter((a) => addressNorm(a.label).includes(stem));
    if (no) {
        const exact = sameStreet.filter((a) => new RegExp(`(^|\\D)${no}(\\D|$)`).test(addressNorm(a.label)));
        if (exact.length === 1) return exact[0];
    }
    return sameStreet.length === 1 ? sameStreet[0] : null;
}

const emptyData = (): AdLocationData => ({ standorte: [], adressen: [], etagen: [], raeume: [], computers: [], pc_devices: [] });
const TTL_MS = 5 * 60 * 1000;
const cache = new Map<string, { at: number; promise: Promise<AdLocationData> }>();

function fetchData(withComputers: boolean): Promise<AdLocationData> {
    const key = withComputers ? 'pcs' : 'rooms';
    const hit = cache.get(key);
    if (hit && Date.now() - hit.at < TTL_MS) return hit.promise;

    const promise = axios
        .get<Partial<AdLocationData>>('/ad-locations', { params: withComputers ? { computers: 1 } : {} })
        .then(({ data }) => ({ ...emptyData(), ...data }))
        .catch((e) => {
            cache.delete(key);
            throw e;
        });
    cache.set(key, { at: Date.now(), promise });
    return promise;
}

export function useAdLocations(options: { computers?: boolean } = {}) {
    const data = ref<AdLocationData>(emptyData());
    const loading = ref(false);
    const failed = ref(false);

    async function load() {
        loading.value = true;
        failed.value = false;
        try {
            data.value = await fetchData(!!options.computers);
        } catch {
            failed.value = true;
        } finally {
            loading.value = false;
        }
    }

    return { data, loading, failed, load };
}
