<script setup lang="ts">
import { Info } from '@lucide/vue';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import type { AdUserDevice } from '@/composables/useAdLocations';

/**
 * "Gerät hinzufügen" (2026-10-09): when the PC / printer / phone / projector
 * a ticket is about is not in the room's list, the user adds it here. It is
 * saved for that room (POST /ad-locations/devices -> ad_devices), so it is in
 * the list next time, and IT reviews new entries on Einstellungen >
 * Standorte & Adressen. Nothing is written to AD.
 * Sits inside the ticket form: no required fields, Enter does not submit.
 */

const KIND_LABEL: Record<string, string> = {
    rechner: 'Rechner',
    drucker: 'Drucker',
    scanner: 'Scanner',
    telefon: 'Telefon',
    beamer: 'Beamer',
    sonstiges: 'Gerät',
};

const props = withDefaults(
    defineProps<{
        /** AD / app room (ad_ous.id); adding needs a room */
        room: number | string | null;
        /** device kinds that fit this field; the first is the default */
        kinds: string[];
        /** open the form right away (the room has no devices) */
        open?: boolean;
        /** allow adding without a room (software forms: just the PC name) */
        allowNoRoom?: boolean;
        /** the room's name, for the projector example ("Schulungsraum FOSI" -> "B-Fosi") */
        roomLabel?: string | null;
    }>(),
    { open: false, allowNoRoom: false, roomLabel: null },
);

const emit = defineEmits<{ added: [device: AdUserDevice] }>();

const show = ref(props.open);
const kind = ref(props.kinds[0]);
const name = ref('');
const saving = ref(false);
const error = ref('');

watch(
    () => props.open,
    (v) => {
        if (v) show.value = true;
    },
);
watch(
    () => props.room,
    () => {
        error.value = '';
        if (!props.open) show.value = false;
    },
);

const noun = () => KIND_LABEL[props.kinds[0]] ?? 'Gerät';
// projectors are named "B-<room>": "Schulungsraum FOSI" -> "B-Fosi"
function projectorName(label: string | null | undefined): string {
    const words = (label ?? '').trim().split(/\s+/).filter(Boolean);
    const rest = words.filter((w) => !/^(schulungsraum|seminarraum|unterrichtsraum|klassenraum|raum|büro|buero)$/i.test(w));
    const name = (rest.length ? rest : words).join(' ');
    if (!name) return 'B-Raumname';
    return `B-${/^[A-ZÄÖÜ]{2,}$/.test(name) ? name[0] + name.slice(1).toLowerCase() : name}`;
}

// printers / scanners have no sticker name: ask for the model; projectors: B-<room>;
// phones: their number (2026-10-09)
const placeholder = computed(() => {
    if (kind.value === 'drucker' || kind.value === 'scanner') return 'Modell und Nummer, z. B. Brother DCP-L3550CDW';
    if (kind.value === 'beamer') return `z. B. ${projectorName(props.roomLabel)}`;
    if (kind.value === 'telefon') return 'Telefonnummer, z. B. 0361 123456-12';
    return 'Name vom Aufkleber';
});

async function save() {
    if ((!props.room && !props.allowNoRoom) || !name.value.trim() || saving.value) return;
    saving.value = true;
    error.value = '';
    try {
        const { data } = await axios.post<AdUserDevice>('/ad-locations/devices', {
            room: props.room || null,
            kind: kind.value,
            name: name.value,
        });
        emit('added', data);
        toast.success(`${KIND_LABEL[data.kind] ?? 'Gerät'} "${data.name}" hinzugefügt.`);
        name.value = '';
        show.value = false;
    } catch (e: unknown) {
        const errors = (e as { response?: { data?: { errors?: Record<string, string[]> } } }).response?.data?.errors;
        error.value = errors ? Object.values(errors)[0][0] : 'Konnte nicht gespeichert werden.';
    } finally {
        saving.value = false;
    }
}

const input = 'border-input bg-background h-9 rounded-md border px-3 text-sm';
</script>

<template>
    <div class="flex flex-col gap-2">
        <button
            v-if="!show"
            type="button"
            class="text-primary self-start text-xs hover:underline disabled:cursor-not-allowed disabled:no-underline disabled:opacity-60"
            :disabled="!room && !allowNoRoom"
            :title="room || allowNoRoom ? '' : 'Erst Adresse und Raum wählen'"
            @click="show = true"
        >
            + {{ noun() }} nicht in der Liste? Selbst hinzufügen
        </button>

        <div v-else class="bg-muted/30 flex flex-col gap-2 rounded-md border p-3">
            <p class="text-muted-foreground flex gap-1.5 text-xs">
                <Info class="mt-0.5 size-3.5 shrink-0" />
                <span>
                    <strong class="text-foreground">Hinweis:</strong> {{ noun() }} nicht in der Liste? Tragen Sie es hier ein. Es wird
                    {{ room ? 'für diesen Raum ' : '' }}gespeichert und steht beim nächsten Ticket in der Liste. Die IT-Abteilung prüft neue Einträge.
                </span>
            </p>
            <p v-if="!room && !allowNoRoom" class="text-xs">Bitte zuerst Adresse und Raum wählen.</p>
            <template v-else>
                <select v-if="kinds.length > 1" v-model="kind" :class="input">
                    <option v-for="k in kinds" :key="k" :value="k">{{ KIND_LABEL[k] ?? k }}</option>
                </select>
                <input
                    v-model="name"
                    type="text"
                    maxlength="100"
                    :placeholder="placeholder"
                    :inputmode="kind === 'telefon' ? 'tel' : undefined"
                    :class="input"
                    @keydown.enter.prevent="save"
                />
                <p v-if="error" class="text-destructive text-xs">{{ error }}</p>
                <div class="flex justify-end gap-2">
                    <button type="button" class="hover:bg-accent h-8 rounded-md border px-3 text-xs" @click="show = false">Abbrechen</button>
                    <button
                        type="button"
                        :disabled="saving || !name.trim()"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 h-8 rounded-md px-3 text-xs disabled:opacity-60"
                        @click="save"
                    >
                        Hinzufügen
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>
