<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import AdDeviceAdd from '@/components/ad/AdDeviceAdd.vue';
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { emptySelection, selectionForRoom, type AdLocationData, type AdSelection, type AdUserDevice } from '@/composables/useAdLocations';

/**
 * "Welcher Rechner" from AD (AD switch step 4, 2026-10-09), replacing
 * ComputerSelect (old inventory list). The Standort › Adresse › Etage / Raum
 * selects only narrow the PC list (all optional, Standort starts at the
 * user's city); the searchable list can also be used directly. "Alle Rechner
 * anzeigen" lifts the filter, e.g. for loan laptops that sit in no room.
 * Picking a PC fills in its room.
 * Not in the list -> the user adds it to the room (AdDeviceAdd, a PC that is
 * not in AD; IT reviews it). Such PCs are in the list next time.
 *
 * Models: v-model = ad_computers.id (ad_computer_id); v-model:device = a PC
 * added in the app (ad_pc_device_id). Only one of the two is set.
 * :cascade="false" (software forms): no location selects, just every PC in
 * one searchable list (with its location); a missing PC is added by name.
 */

const props = withDefaults(
    defineProps<{
        data: AdLocationData;
        city?: string | null;
        /** the user's street: preselects the matching AD address */
        address?: string | null;
        loading?: boolean;
        /** false = no Standort / Raum selects, all PCs in one list */
        cascade?: boolean;
        required?: boolean;
        label?: string;
        legend?: string;
    }>(),
    { city: null, address: null, loading: false, cascade: true, required: true, label: 'Welcher Rechner', legend: 'Rechner' },
);

const model = defineModel<number | string | null>({ default: '' });
const device = defineModel<number | string | null>('device', { default: '' });

const selection = ref<AdSelection>(emptySelection());
const showAll = ref(!props.cascade);
const added = ref<AdUserDevice[]>([]);

type Pc = {
    value: string;
    name: string;
    group: string | null;
    standort_id: number | null;
    adresse_id: number | null;
    etage_id: number | null;
    room_id: number | null;
    isNew: boolean;
};

// AD computers ("ad:<id>") and PCs added in the app ("dev:<id>") in one list
const pcs = computed<Pc[]>(() => {
    const userPcs = [...props.data.pc_devices, ...added.value.filter((a) => !props.data.pc_devices.some((d) => d.id === a.id))];
    return [
        ...props.data.computers.map((c) => ({ ...c, value: `ad:${c.id}`, isNew: false })),
        ...userPcs.map((d) => ({
            value: `dev:${d.id}`,
            name: d.name,
            group: null,
            standort_id: d.standort_id ?? null,
            adresse_id: d.adresse_id ?? null,
            etage_id: d.etage_id ?? null,
            room_id: d.room_id ?? null,
            isNew: !d.checked,
        })),
    ];
});

const selected = computed<string>({
    get: () => (device.value ? `dev:${device.value}` : model.value ? `ad:${model.value}` : ''),
    set: (v) => {
        const s = String(v ?? '');
        model.value = s.startsWith('ad:') ? Number(s.slice(3)) : '';
        device.value = s.startsWith('dev:') ? Number(s.slice(4)) : '';
    },
});

const standortLabel = computed(() => new Map(props.data.standorte.map((s) => [s.id, s.label])));
const roomLabel = computed(() => {
    const etage = new Map(props.data.etagen.map((e) => [e.id, e.label]));
    return new Map(props.data.raeume.map((r) => [r.id, r.etage_id && etage.get(r.etage_id) ? `${r.label} (${etage.get(r.etage_id)})` : r.label]));
});

function inSelection(pc: Pc, s: AdSelection): boolean {
    if (s.raum !== '') return pc.room_id === s.raum;
    if (s.etage === 0) return pc.adresse_id === s.adresse && !pc.etage_id;
    if (s.etage !== '') return pc.etage_id === s.etage;
    if (s.adresse !== '') return pc.adresse_id === s.adresse;
    if (s.standort !== '') return pc.standort_id === s.standort;
    return true;
}

const visible = computed(() => (showAll.value ? pcs.value : pcs.value.filter((pc) => inSelection(pc, selection.value))));

function describe(pc: Pc): string {
    const parts: string[] = [];
    if (showAll.value) parts.push(pc.standort_id ? (standortLabel.value.get(pc.standort_id) ?? '') : 'ohne Standort');
    if (pc.room_id) parts.push(roomLabel.value.get(pc.room_id) ?? '');
    if (pc.group) parts.push(pc.group);
    if (pc.isNew) parts.push('neu');
    const where = parts.filter(Boolean).join(' · ');
    return where ? `${pc.name} — ${where}` : pc.name;
}

const options = computed(() => visible.value.map((pc) => ({ value: pc.value, label: describe(pc) })));

// A PC picked straight from the list -> show its room in the selects.
watch(selected, (value) => {
    if (!value) return;
    const pc = pcs.value.find((c) => c.value === value);
    if (!pc?.room_id || selection.value.raum === pc.room_id) return;
    const sel = selectionForRoom(props.data, pc.room_id);
    if (sel) {
        selection.value = sel;
        showAll.value = false;
    }
});

// The filter changed and the picked PC is no longer in the list -> clear it.
watch([selection, showAll], () => {
    if (!selected.value || showAll.value) return;
    if (!visible.value.some((pc) => pc.value === selected.value)) selected.value = '';
});

const roomEmpty = computed(() => selection.value.raum !== '' && !props.loading && visible.value.length === 0);

function onAdded(d: AdUserDevice) {
    if (!added.value.some((a) => a.id === d.id)) added.value.push(d);
    selected.value = `dev:${d.id}`;
}
</script>

<template>
    <fieldset class="grid gap-3 rounded-md border p-3">
        <legend class="px-1 text-sm font-medium">{{ legend }}</legend>
        <AdRoomPicker v-if="cascade" v-model="selection" :data="data" :city="city" :address="address" :loading="loading" :require-address="false" />
        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium">{{ label }} <span v-if="required" class="text-muted-foreground">*</span></label>
            <Combobox
                v-model="selected"
                :options="options"
                :required="required"
                :placeholder="loading ? 'Lädt…' : 'Bitte Wählen'"
                :empty-text="
                    !cascade
                        ? 'Kein Rechner gefunden - unten hinzufügen'
                        : selection.raum !== ''
                          ? 'Kein Rechner in diesem Raum - unten hinzufügen'
                          : 'Kein Rechner gefunden'
                "
            />
            <button v-if="cascade" type="button" class="text-primary self-start text-xs hover:underline" @click="showAll = !showAll">
                {{ showAll ? 'Nur Rechner der Auswahl anzeigen' : 'Rechner nicht dabei? Alle Rechner anzeigen' }}
            </button>
            <AdDeviceAdd v-if="!cascade" :room="null" allow-no-room :kinds="['rechner']" @added="onAdded" />
            <AdDeviceAdd v-else-if="selection.raum !== ''" :room="selection.raum" :kinds="['rechner']" :open="roomEmpty" @added="onAdded" />
        </div>
    </fieldset>
</template>
