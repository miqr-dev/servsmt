<script setup lang="ts">
import { computed, watch } from 'vue';
import { emptySelection, matchAddress, sameCity, selectionOu, type AdLocationData, type AdSelection } from '@/composables/useAdLocations';

/**
 * Standort › Adresse › Etage › Raum from AD (AD switch step 4, 2026-10-09),
 * shared by the IT and Handwerk ticket forms. The selects render as sibling
 * blocks without a wrapper, so they flow into the parent's grid like the old
 * LocationRoomFields did.
 *
 * Models:
 *  - v-model (optional): the whole selection, for parents that filter by it
 *    (AdComputerField) or set it (a PC picked first).
 *  - v-model:ou: most specific OU picked (Raum, else Etage, else Adresse),
 *    posted as ad_room_id. Setting it to '' from outside resets the picker.
 *  - v-model:room: the Raum only ('' until one is picked), for device lists.
 *
 * The Standort starts at `city` (the user's Ort). With `lockCity` and a
 * matching AD Standort, the Standort select is hidden (Handwerk: the city
 * comes from the submitter card / "Standort ändern").
 * Standort and Adresse are ONE select (addresses grouped by Standort, the
 * Standorte ordered by Bundesland); Etage and Raum are one select (rooms
 * grouped by floor), 2026-10-09. Both start at the user's city and address
 * (`city` + `address`, e.g. "Heinrichstraße 92" ~ AD "Heinrichstr. -89").
 * Hidden OUs and app-only addresses / rooms come from Einstellungen >
 * Standorte & Adressen (already applied by the API).
 */

const props = withDefaults(
    defineProps<{
        data: AdLocationData;
        city?: string | null;
        /** the user's street ("Heinrichstraße 92"): preselects the matching AD address */
        address?: string | null;
        lockCity?: boolean;
        /** 'adresse' = only Standort › Adresse (no Etage / Raum) */
        level?: 'raum' | 'adresse';
        requireAddress?: boolean;
        requireRoom?: boolean;
        loading?: boolean;
        standortLabel?: string;
        adresseLabel?: string;
        raumLabel?: string;
    }>(),
    {
        city: null,
        address: null,
        lockCity: false,
        level: 'raum',
        requireAddress: true,
        requireRoom: false,
        loading: false,
        standortLabel: 'Standort',
        adresseLabel: 'Adresse',
        raumLabel: 'Raum',
    },
);

const selection = defineModel<AdSelection>({ default: () => emptySelection() });
const ou = defineModel<number | string | null>('ou', { default: '' });
const room = defineModel<number | string | null>('room', { default: '' });

const cityStandort = computed(() => props.data.standorte.find((s) => sameCity(s.label, props.city)) ?? null);

const collator = new Intl.Collator('de', { numeric: true, sensitivity: 'base' });
const standortGroups = computed(() => {
    const byState = new Map<string, typeof props.data.standorte>();
    for (const s of props.data.standorte) {
        const key = s.state || '';
        byState.set(key, [...(byState.get(key) ?? []), s]);
    }
    // states A-Z, Standorte without a state last
    return [...byState.entries()]
        .sort(([a], [b]) => (a === '' ? 1 : b === '' ? -1 : collator.compare(a, b)))
        .map(([state, items]) => ({ state: state || 'Weitere', items }));
});
const showStandort = computed(() => !(props.lockCity && cityStandort.value));

// Standort + Adresse in one select: one optgroup per Standort ("Erfurt (Thüringen)")
const addressGroups = computed(() =>
    standortGroups.value.flatMap((g) =>
        g.items
            .map((s) => ({
                id: s.id,
                label: s.state ? `${s.label} (${s.state})` : s.label,
                items: props.data.adressen.filter((a) => a.standort_id === s.id),
            }))
            .filter((x) => x.items.length),
    ),
);

function defaults(): AdSelection {
    const standort = cityStandort.value;
    const own = standort
        ? matchAddress(
              props.data.adressen.filter((a) => a.standort_id === standort.id),
              props.address,
          )
        : null;
    return { ...emptySelection(), standort: standort?.id ?? '', adresse: own?.id ?? '' };
}

function set(patch: Partial<AdSelection>) {
    selection.value = { ...selection.value, ...patch };
}

const num = (v: unknown): number | '' => (v === '' || v === null || v === undefined ? '' : Number(v));

// Start at the user's city once the data is there, and follow a changed city.
watch(
    cityStandort,
    (s) => {
        if (s && selection.value.standort !== s.id) selection.value = defaults();
    },
    { immediate: true },
);

const adressen = computed(() => props.data.adressen.filter((a) => a.standort_id === selection.value.standort));
const etagen = computed(() => props.data.etagen.filter((e) => e.adresse_id === selection.value.adresse));
const roomsOfAddress = computed(() => props.data.raeume.filter((r) => r.adresse_id === selection.value.adresse));
const looseRooms = computed(() => roomsOfAddress.value.filter((r) => !r.etage_id));

// Etage + Raum in one select: rooms grouped by floor (optgroup per Etage)
const roomGroups = computed(() => {
    const groups: { key: number; label: string; rooms: typeof roomsOfAddress.value }[] = [];
    for (const etage of etagen.value) {
        const rooms = roomsOfAddress.value.filter((r) => r.etage_id === etage.id);
        if (rooms.length) groups.push({ key: etage.id, label: etage.label, rooms });
    }
    if (looseRooms.value.length) {
        groups.push({ key: 0, label: groups.length ? 'ohne Etage' : '', rooms: looseRooms.value });
    }
    return groups;
});

// Only one address at this Standort -> take it (not in the optional PC filter,
// where it would hide PCs that sit outside any address).
watch(adressen, (list) => {
    if (props.requireAddress && list.length === 1 && selection.value.adresse === '') set({ adresse: list[0].id });
});

const adresseModel = computed({
    get: () => selection.value.adresse,
    set: (v) => {
        const adresse = num(v);
        const picked = adresse === '' ? null : props.data.adressen.find((a) => a.id === adresse);
        // '' in the optional PC filter = all Standorte
        const standort = picked ? picked.standort_id : props.requireAddress ? selection.value.standort : '';
        set({ standort, adresse, etage: '', raum: '' });
    },
});
const emptyAddressLabel = computed(() => {
    if (props.loading) return 'Lädt…';
    if (props.requireAddress) return showStandort.value ? 'Standort / Adresse...' : 'Adresse...';
    const st = props.data.standorte.find((s) => s.id === selection.value.standort);
    return st ? `Alle Adressen in ${st.label}` : 'Alle Standorte';
});
const raumModel = computed({
    get: () => selection.value.raum,
    set: (v) => {
        const raum = num(v);
        const picked = raum === '' ? null : props.data.raeume.find((r) => r.id === raum);
        set({ raum, etage: picked?.etage_id ?? '' });
    },
});

// Outputs, and a reset when the parent clears ou.
watch(
    selection,
    (s) => {
        const picked = selectionOu(s);
        if (ou.value !== picked) ou.value = picked;
        if (room.value !== s.raum) room.value = s.raum;
    },
    { immediate: true },
);
watch(ou, (v) => {
    if ((v === '' || v === null) && selectionOu(selection.value) !== '') selection.value = defaults();
});

const selectClass = 'border-input bg-background h-9 rounded-md border px-3 text-sm disabled:opacity-60';
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-sm font-medium">
            {{ showStandort ? standortLabel : adresseLabel }} <span v-if="requireAddress" class="text-muted-foreground">*</span>
        </label>
        <select v-model="adresseModel" :required="requireAddress" :class="selectClass">
            <option value="">{{ emptyAddressLabel }}</option>
            <template v-if="showStandort">
                <optgroup v-for="g in addressGroups" :key="g.id" :label="g.label">
                    <option v-for="a in g.items" :key="a.id" :value="a.id">{{ a.label }}</option>
                </optgroup>
            </template>
            <template v-else>
                <option v-for="a in adressen" :key="a.id" :value="a.id">{{ a.label }}</option>
            </template>
        </select>
        <p v-if="!loading && !showStandort && !adressen.length" class="text-muted-foreground text-xs">
            Für diesen Standort sind keine Adressen hinterlegt. Bitte die IT-Abteilung informieren.
        </p>
    </div>

    <div v-if="level === 'raum'" class="flex flex-col gap-1">
        <label class="text-sm font-medium"
            >{{ etagen.length ? `Etage / ${raumLabel}` : raumLabel }} <span v-if="requireRoom" class="text-muted-foreground">*</span></label
        >
        <select v-model="raumModel" :required="requireRoom" :disabled="selection.adresse === ''" :class="selectClass">
            <option value="">Raum...</option>
            <template v-for="g in roomGroups" :key="g.key">
                <optgroup v-if="g.label" :label="g.label">
                    <option v-for="r in g.rooms" :key="r.id" :value="r.id">{{ r.label }}</option>
                </optgroup>
                <template v-else>
                    <option v-for="r in g.rooms" :key="r.id" :value="r.id">{{ r.label }}</option>
                </template>
            </template>
        </select>
        <p v-if="selection.adresse !== '' && !roomsOfAddress.length" class="text-muted-foreground text-xs">
            Für diese Adresse sind keine Räume hinterlegt.
        </p>
    </div>
</template>
