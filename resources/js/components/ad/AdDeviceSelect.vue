<script setup lang="ts">
import axios from 'axios';
import { computed, nextTick, ref, watch } from 'vue';
import AdDeviceAdd from '@/components/ad/AdDeviceAdd.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import type { AdUserDevice } from '@/composables/useAdLocations';

/**
 * Printer / phone / projector of an AD room (AD switch step 4, 2026-10-09).
 * These devices are not in AD. The list (GET /ad-locations/items) has the
 * old inventory items of the old rooms linked to the AD room, plus devices
 * added in the app (ad_devices). Not in the list -> the user adds it right
 * here (AdDeviceAdd), with a hint that IT reviews new entries.
 *
 * Models: v-model = the old item (its id for gname_id, or its Inventarnummer
 * for printer_name); v-model:device = a device added in the app
 * (ad_device_id). Only one of the two is set.
 */

type Device = { id: number; gname: string | null; invnr: string | null; source: 'inventar' | 'app'; checked: boolean; kind?: string };

const KINDS: Record<'printer' | 'phone' | 'projector', string[]> = {
    printer: ['drucker', 'scanner'],
    phone: ['telefon'],
    projector: ['beamer'],
};

const props = withDefaults(
    defineProps<{
        /** AD room (ad_ous.id), '' = none picked yet */
        room: number | string | null;
        type: 'printer' | 'phone' | 'projector';
        label: string;
        /** which field is posted for an old item: its id (gname_id) or its Inventarnummer (printer_name) */
        valueKey?: 'id' | 'invnr';
        /** kinds offered by "hinzufügen", first = default (default: by type; scanner forms: scanner first) */
        addKinds?: string[];
        /** the room's name, for the "hinzufügen" example (projectors: "B-Fosi") */
        roomLabel?: string | null;
    }>(),
    { valueKey: 'id', addKinds: undefined, roomLabel: null },
);

const model = defineModel<number | string | null>({ default: '' });
const device = defineModel<number | string | null>('device', { default: '' });

const devices = ref<Device[]>([]);
const loading = ref(false);
let request = 0;

watch(
    () => props.room,
    async (room) => {
        model.value = '';
        device.value = '';
        devices.value = [];
        if (!room) return;
        const mine = ++request;
        loading.value = true;
        try {
            const { data } = await axios.get<Device[]>('/ad-locations/items', { params: { room, type: props.type } });
            if (mine === request) {
                devices.value = data;
                // only one device in the room -> take it (2026-10-09)
                await nextTick();
                if (options.value.length === 1 && !selected.value) selected.value = options.value[0].value;
            }
        } finally {
            if (mine === request) loading.value = false;
        }
    },
    { immediate: true },
);

// one Combobox for both sources: app devices as "dev:<id>"
const selected = computed<string | number | null>({
    get: () => (device.value ? `dev:${device.value}` : (model.value ?? '')),
    set: (v) => {
        const s = String(v ?? '');
        if (s.startsWith('dev:')) {
            device.value = Number(s.slice(4));
            model.value = '';
        } else {
            device.value = '';
            model.value = v;
        }
    },
});

const options = computed(() =>
    devices.value
        .filter((d) => d.source === 'app' || (d[props.valueKey] !== null && d[props.valueKey] !== ''))
        .map((d) =>
            d.source === 'app'
                ? { value: `dev:${d.id}`, label: `${d.gname ?? ''}${d.checked ? '' : ' (neu)'}` }
                : { value: d[props.valueKey] as string | number, label: d.gname || d.invnr || `#${d.id}` },
        ),
);

const noDevices = computed(() => !!props.room && !loading.value && devices.value.length === 0);

function onAdded(d: AdUserDevice) {
    if (!devices.value.some((x) => x.source === 'app' && x.id === d.id)) {
        devices.value.push({ id: d.id, gname: d.name, invnr: d.invnr, source: 'app', checked: d.checked, kind: d.kind });
    }
    selected.value = `dev:${d.id}`;
}
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-sm font-medium">{{ label }} <span v-if="room" class="text-muted-foreground">*</span></label>
        <Combobox
            v-model="selected"
            :options="options"
            :required="!!room"
            :disabled="!room"
            :placeholder="loading ? 'Lädt…' : room ? 'Bitte Wählen' : 'Erst den Raum wählen'"
            :empty-text="`Kein ${label} in diesem Raum - unten hinzufügen`"
        />
        <AdDeviceAdd v-if="room" :room="room" :kinds="addKinds ?? KINDS[type]" :room-label="roomLabel" :open="noDevices" @added="onAdded" />
    </div>
</template>
