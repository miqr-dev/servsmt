<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, ChevronRight, Eye, EyeOff, History, Package, Pencil, Plus, Trash2, X } from '@lucide/vue';
import axios from 'axios';
import { computed, h, ref } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Einstellungen > Standorte & Adressen (AdLocationSettingsController,
 * 2026-10-09). Shapes what the ticket forms offer under Standort › Adresse ›
 * Raum - only in the app, Active Directory is never changed:
 *  - Bundesland per Standort (groups the Standort select);
 *  - hide AD OUs that are no real address / floor / room ("NB-no-Energie",
 *    "LAPTOPs", ...). The import keeps the flag; old tickets still show them;
 *  - add addresses and rooms that are not in AD, e.g. a "Flur" on each floor
 *    for hallway printers ("Flur je Etage"). These can be renamed / deleted;
 *  - "Flure & Drucker aus altem Inventar": the old hallways and printer rooms
 *    without an AD room become app rooms (preview first, InventoryAdoption).
 * Rooms show their devices (old inventory + added in the app); "+" on a room
 * adds one. Devices users added in the ticket forms ("Gerät hinzufügen") are
 * listed on top for IT to confirm or delete.
 */

type Standort = { id: number; label: string; state: string | null; default_state: string | null };
type OuNode = {
    id: number;
    type: 'adresse' | 'etage' | 'raum';
    label: string;
    standort_id: number;
    adresse_id: number | null;
    etage_id: number | null;
    hidden: boolean;
    app: boolean;
    description: string | null;
    pcs: number;
    /** old-inventory devices of the old rooms linked to this room */
    devices: { name: string; kind: string; app: boolean }[];
};
type AdoptRow = {
    old_id: number;
    old: string;
    old_place: string;
    old_floor: string | null;
    printers: string[];
    status: 'neu' | 'verknüpft' | 'offen';
    target: string | null;
    reason: string | null;
};
type AdoptPlan = { rows: AdoptRow[]; created: number; linked: number; open: number };

type UserDevice = {
    id: number;
    name: string;
    kind: string;
    invnr: string | null;
    path: string;
    by: string | null;
    at: string | null;
    checked: boolean;
};

const props = defineProps<{
    standorte: Standort[];
    nodes: OuNode[];
    states: string[];
    /** devices added in the app, not yet confirmed first */
    userDevices: UserDevice[];
    deviceKinds: Record<string, string>;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Standorte & Adressen', href: '/settings/ad-standorte' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const OTHER = 'Ohne Bundesland';
const collator = new Intl.Collator('de', { numeric: true, sensitivity: 'base' });
const stateOf = (s: Standort) => s.state || s.default_state || '';

const groups = computed(() => {
    const byState = new Map<string, Standort[]>();
    for (const s of props.standorte) {
        const key = stateOf(s) || OTHER;
        byState.set(key, [...(byState.get(key) ?? []), s]);
    }
    return [...byState.entries()]
        .sort(([a], [b]) => (a === OTHER ? 1 : b === OTHER ? -1 : collator.compare(a, b)))
        .map(([state, items]) => ({ state, items }));
});

const selectedId = ref<number | null>(groups.value[0]?.items[0]?.id ?? null);
const selected = computed(() => props.standorte.find((s) => s.id === selectedId.value) ?? null);

const addresses = computed(() => props.nodes.filter((n) => n.type === 'adresse' && n.standort_id === selectedId.value));
const addressCount = (standortId: number) => props.nodes.filter((n) => n.type === 'adresse' && n.standort_id === standortId && !n.hidden).length;

/** floors of an address with their rooms, plus the rooms without a floor */
function sectionsOf(addressId: number) {
    const floors = props.nodes.filter((n) => n.type === 'etage' && n.adresse_id === addressId);
    const rooms = props.nodes.filter((n) => n.type === 'raum' && n.adresse_id === addressId);
    const out: { key: number; floor: OuNode | null; rooms: OuNode[] }[] = floors.map((f) => ({
        key: f.id,
        floor: f,
        rooms: rooms.filter((r) => r.etage_id === f.id),
    }));
    const loose = rooms.filter((r) => !r.etage_id);
    if (loose.length || !floors.length) out.push({ key: 0, floor: null, rooms: loose });
    return out;
}

const open = ref<Record<number, boolean>>({});

const printersOf = (n: OuNode) => n.devices.filter((d) => d.kind === 'Drucker');
const addressPrinters = (addressId: number) =>
    props.nodes.filter((n) => n.type === 'raum' && n.adresse_id === addressId).reduce((sum, r) => sum + printersOf(r).length, 0);
const deviceTitle = (n: OuNode) => n.devices.map((d) => `${d.kind}: ${d.name}${d.app ? ' (App)' : ''}`).join('\n');

// --- devices added in the app: review + add ---
const unchecked = computed(() => props.userDevices.filter((d) => !d.checked));
const showAllDevices = ref(false);
const reviewList = computed(() => (showAllDevices.value ? props.userDevices : unchecked.value));

function checkDevice(d: UserDevice) {
    router.put(`/settings/ad-standorte/devices/${d.id}/check`, {}, opts);
}
function deleteDevice(d: UserDevice) {
    if (!confirm(`${d.kind} "${d.name}" löschen? Tickets, die es nennen, behalten den Namen.`)) return;
    router.delete(`/settings/ad-standorte/devices/${d.id}`, opts);
}

const deviceDialog = ref<{ room: number; title: string } | null>(null);
const deviceForm = useForm({ room: 0, kind: 'drucker', name: '', invnr: '' });
function openDeviceDialog(r: OuNode) {
    deviceForm.reset();
    deviceForm.clearErrors();
    deviceForm.room = r.id;
    deviceDialog.value = { room: r.id, title: `Gerät hinzufügen: ${r.label}` };
}
function submitDevice() {
    deviceForm.post('/settings/ad-standorte/devices', { ...opts, onSuccess: () => (deviceDialog.value = null) });
}

const opts = { preserveScroll: true, preserveState: true };

function setState(s: Standort, value: string) {
    router.put(`/settings/ad-standorte/${s.id}/state`, { state: value }, opts);
}
function toggleHidden(n: OuNode) {
    router.put(`/settings/ad-standorte/${n.id}/hidden`, { hidden: !n.hidden }, opts);
}
function addHallways(a: OuNode) {
    router.post(`/settings/ad-standorte/${a.id}/flure`, {}, opts);
}
function remove(n: OuNode) {
    const what = n.type === 'adresse' ? 'Adresse (mit ihren App-Räumen)' : 'Raum';
    if (!confirm(`${what} "${n.label}" löschen? Alte Tickets behalten den Namen.`)) return;
    router.delete(`/settings/ad-standorte/app/${n.id}`, opts);
}

// --- old inventory: hallways + printer rooms (preview, then run) ---
const adoptOpen = ref(false);
const adoptPlan = ref<AdoptPlan | null>(null);
const adoptLoading = ref(false);
const adoptRunning = ref(false);

async function openAdopt() {
    adoptOpen.value = true;
    adoptPlan.value = null;
    adoptLoading.value = true;
    try {
        const { data } = await axios.get<AdoptPlan>('/settings/ad-standorte/alt-inventar');
        adoptPlan.value = data;
    } catch {
        toast.error('Vorschau konnte nicht geladen werden.');
        adoptOpen.value = false;
    } finally {
        adoptLoading.value = false;
    }
}
function runAdopt() {
    adoptRunning.value = true;
    router.post(
        '/settings/ad-standorte/alt-inventar',
        {},
        { ...opts, onSuccess: () => (adoptOpen.value = false), onFinish: () => (adoptRunning.value = false) },
    );
}
const STATUS_STYLE: Record<AdoptRow['status'], string> = {
    neu: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    verknüpft: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    offen: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
};

// --- name dialog: new address / new room / rename ---
type Dialog = { mode: 'address' | 'room' | 'rename'; target: number; title: string; placeholder: string };
const dialog = ref<Dialog | null>(null);
const nameForm = useForm({ name: '', description: '' });

function openDialog(mode: Dialog['mode'], target: number, title: string, current?: OuNode) {
    nameForm.reset();
    nameForm.clearErrors();
    if (current) {
        nameForm.name = current.label;
        nameForm.description = current.description ?? '';
    }
    const placeholder = mode === 'address' || current?.type === 'adresse' ? 'z. B. Musterstraße 1 (Lager)' : 'z. B. Flur, Küche, Empfang';
    dialog.value = { mode, target, title, placeholder };
}

function submitDialog() {
    const d = dialog.value!;
    const o = { ...opts, onSuccess: () => (dialog.value = null) };
    if (d.mode === 'address') nameForm.transform((data) => ({ ...data, standort_id: d.target })).post('/settings/ad-standorte/addresses', o);
    else if (d.mode === 'room') nameForm.transform((data) => ({ ...data, parent_id: d.target })).post('/settings/ad-standorte/rooms', o);
    else nameForm.transform((data) => data).put(`/settings/ad-standorte/app/${d.target}`, o);
}

const badge = 'shrink-0 rounded px-1.5 py-px text-[10px] font-medium';
const iconBtn = 'text-muted-foreground hover:text-foreground hover:bg-accent rounded p-1';
const smallBtn = 'hover:bg-accent inline-flex h-8 items-center gap-1 rounded-md border px-2.5 text-xs';
</script>

<template>
    <Head title="Standorte & Adressen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Standorte & Adressen</h1>
                <p class="text-muted-foreground mt-1 max-w-3xl text-sm">
                    Legt fest, was die Ticket-Formulare unter Standort › Adresse › Raum anbieten. Alles hier gilt nur in der App, im Active Directory
                    wird nichts geändert.
                </p>
            </div>
            <button type="button" :class="smallBtn" @click="openAdopt"><History class="size-3.5" /> Flure & Drucker aus altem Inventar</button>
        </div>

        <!-- devices users added in the ticket forms -->
        <section v-if="userDevices.length" class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
                <h2 class="font-semibold">
                    Von Benutzern hinzugefügte Geräte
                    <span v-if="unchecked.length" :class="[badge, 'ml-1 bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300']"
                        >{{ unchecked.length }} ungeprüft</span
                    >
                </h2>
                <label class="text-muted-foreground flex items-center gap-2 text-xs">
                    <input v-model="showAllDevices" type="checkbox" class="border-input rounded" /> alle in der App angelegten Geräte zeigen
                </label>
            </div>
            <p v-if="!reviewList.length" class="text-muted-foreground px-4 py-3 text-sm">Alles geprüft.</p>
            <ul v-else class="divide-y text-sm">
                <li v-for="d in reviewList" :key="d.id" class="flex flex-wrap items-center gap-x-3 gap-y-1 px-4 py-2">
                    <span :class="[badge, 'bg-muted text-muted-foreground']">{{ d.kind }}</span>
                    <span class="font-medium"
                        >{{ d.name }}<template v-if="d.invnr"> ({{ d.invnr }})</template></span
                    >
                    <span class="text-muted-foreground text-xs">{{ d.path }}</span>
                    <span class="text-muted-foreground ml-auto text-xs">{{ [d.by, d.at].filter(Boolean).join(' · ') }}</span>
                    <span v-if="d.checked" :class="[badge, 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300']">geprüft</span>
                    <button v-else type="button" :class="iconBtn" title="Bestätigen" @click="checkDevice(d)">
                        <Check class="size-4" />
                    </button>
                    <button type="button" :class="iconBtn" title="Löschen" @click="deleteDevice(d)">
                        <Trash2 class="size-4" />
                    </button>
                </li>
            </ul>
        </section>

        <div class="grid items-start gap-4 lg:grid-cols-3">
            <!-- Standorte, grouped by Bundesland -->
            <section class="bg-card text-card-foreground overflow-hidden rounded-xl border shadow-sm">
                <div class="border-b px-4 py-3">
                    <h2 class="font-semibold">Standorte</h2>
                </div>
                <p v-if="!standorte.length" class="text-muted-foreground p-4 text-sm">
                    Noch keine Standorte. Zuerst den AD-Import ausführen (AD Räume & Computer › Jetzt aktualisieren).
                </p>
                <div v-for="g in groups" :key="g.state">
                    <p class="text-muted-foreground bg-muted/40 px-4 py-1.5 text-xs font-medium">{{ g.state }}</p>
                    <button
                        v-for="s in g.items"
                        :key="s.id"
                        type="button"
                        class="flex w-full items-center justify-between gap-2 px-4 py-2 text-left text-sm"
                        :class="selectedId === s.id ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-accent'"
                        @click="selectedId = s.id"
                    >
                        <span>{{ s.label }}</span>
                        <span class="text-muted-foreground text-xs tabular-nums">{{ addressCount(s.id) }} Adressen</span>
                    </button>
                </div>
            </section>

            <!-- the selected Standort -->
            <section v-if="selected" class="bg-card text-card-foreground rounded-xl border shadow-sm lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
                    <h2 class="font-semibold">{{ selected.label }}</h2>
                    <label class="flex items-center gap-2 text-sm">
                        <span class="text-muted-foreground">Bundesland</span>
                        <select
                            :value="selected.state ?? ''"
                            class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                            @change="setState(selected, ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">{{ selected.default_state ? `Standard (${selected.default_state})` : '– keins –' }}</option>
                            <option v-for="st in states" :key="st" :value="st">{{ st }}</option>
                        </select>
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 px-4 pt-4">
                    <h3 class="text-sm font-medium">Adressen</h3>
                    <button type="button" :class="smallBtn" @click="openDialog('address', selected.id, `Neue Adresse in ${selected.label}`)">
                        <Plus class="size-3.5" /> Adresse hinzufügen
                    </button>
                </div>

                <p v-if="!addresses.length" class="text-muted-foreground px-4 py-6 text-sm">Für diesen Standort gibt es keine Adressen.</p>

                <ul class="flex flex-col gap-2 p-4">
                    <li v-for="a in addresses" :key="a.id" class="rounded-lg border">
                        <div class="flex flex-wrap items-center gap-2 px-3 py-2">
                            <button
                                type="button"
                                class="flex min-w-0 flex-1 items-center gap-1.5 text-left text-sm font-medium"
                                :class="a.hidden && 'text-muted-foreground line-through'"
                                @click="open[a.id] = !open[a.id]"
                            >
                                <ChevronRight class="size-4 shrink-0 transition-transform" :class="{ 'rotate-90': open[a.id] }" />
                                <span class="truncate">{{ a.label }}</span>
                            </button>
                            <span v-if="a.app" :class="[badge, 'border border-dashed']" title="Nur in der App angelegt, nicht im AD">App</span>
                            <span v-if="a.hidden" :class="[badge, 'bg-muted text-muted-foreground']">ausgeblendet</span>
                            <span v-if="a.pcs" :class="[badge, 'bg-muted text-muted-foreground']">{{ a.pcs }} PCs</span>
                            <span v-if="addressPrinters(a.id)" :class="[badge, 'bg-muted text-muted-foreground']"
                                >{{ addressPrinters(a.id) }} Drucker</span
                            >
                            <button
                                type="button"
                                :class="iconBtn"
                                :title="a.hidden ? 'Wieder anzeigen' : 'In den Ticket-Formularen ausblenden'"
                                @click="toggleHidden(a)"
                            >
                                <EyeOff v-if="a.hidden" class="size-4" />
                                <Eye v-else class="size-4" />
                            </button>
                            <template v-if="a.app">
                                <button
                                    type="button"
                                    :class="iconBtn"
                                    title="Umbenennen"
                                    @click="openDialog('rename', a.id, 'Adresse umbenennen', a)"
                                >
                                    <Pencil class="size-4" />
                                </button>
                                <button type="button" :class="iconBtn" title="Löschen" @click="remove(a)">
                                    <Trash2 class="size-4" />
                                </button>
                            </template>
                        </div>

                        <div v-if="open[a.id]" class="border-t px-3 py-3 text-sm">
                            <p v-if="a.hidden" class="text-muted-foreground mb-3 text-xs">
                                Ausgeblendet, mit allen Etagen und Räumen. Rechner darin bleiben über den Standort auswählbar.
                            </p>
                            <div class="mb-3 flex flex-wrap gap-2">
                                <button type="button" :class="smallBtn" @click="addHallways(a)">
                                    <Plus class="size-3.5" /> Flur je Etage anlegen
                                </button>
                            </div>

                            <div v-for="sec in sectionsOf(a.id)" :key="sec.key" class="border-b py-2 last:border-b-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-medium" :class="sec.floor?.hidden && 'text-muted-foreground line-through'">
                                        {{ sec.floor ? sec.floor.label : sectionsOf(a.id).length > 1 ? 'ohne Etage' : 'Räume' }}
                                    </span>
                                    <span v-if="sec.floor?.hidden" :class="[badge, 'bg-muted text-muted-foreground']">ausgeblendet</span>
                                    <button
                                        v-if="sec.floor"
                                        type="button"
                                        :class="iconBtn"
                                        :title="sec.floor.hidden ? 'Wieder anzeigen' : 'Etage ausblenden'"
                                        @click="toggleHidden(sec.floor)"
                                    >
                                        <EyeOff v-if="sec.floor.hidden" class="size-3.5" />
                                        <Eye v-else class="size-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        class="text-primary ml-auto inline-flex items-center gap-1 text-xs hover:underline"
                                        @click="
                                            openDialog(
                                                'room',
                                                sec.floor ? sec.floor.id : a.id,
                                                `Neuer Raum: ${a.label}${sec.floor ? ' › ' + sec.floor.label : ''}`,
                                            )
                                        "
                                    >
                                        <Plus class="size-3.5" /> Raum
                                    </button>
                                </div>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    <span
                                        v-for="r in sec.rooms"
                                        :key="r.id"
                                        class="inline-flex items-center gap-1 rounded-md border py-0.5 pr-0.5 pl-2 text-xs"
                                        :class="[r.app && 'border-dashed', r.hidden && 'text-muted-foreground line-through']"
                                        :title="
                                            [r.app ? 'Nur in der App angelegt' : r.pcs ? `${r.pcs} PCs` : 'AD-Raum', deviceTitle(r)]
                                                .filter(Boolean)
                                                .join('\n')
                                        "
                                    >
                                        {{ r.label }}
                                        <span v-if="r.devices.length" class="text-muted-foreground inline-flex items-center gap-0.5">
                                            <Package class="size-3" />{{ r.devices.length }}
                                        </span>
                                        <button type="button" :class="iconBtn" title="Gerät hinzufügen" @click="openDeviceDialog(r)">
                                            <Plus class="size-3" />
                                        </button>
                                        <button
                                            type="button"
                                            :class="iconBtn"
                                            :title="r.hidden ? 'Wieder anzeigen' : 'Ausblenden'"
                                            @click="toggleHidden(r)"
                                        >
                                            <EyeOff v-if="r.hidden" class="size-3" />
                                            <Eye v-else class="size-3" />
                                        </button>
                                        <template v-if="r.app">
                                            <button
                                                type="button"
                                                :class="iconBtn"
                                                title="Umbenennen"
                                                @click="openDialog('rename', r.id, 'Raum umbenennen', r)"
                                            >
                                                <Pencil class="size-3" />
                                            </button>
                                            <button type="button" :class="iconBtn" title="Löschen" @click="remove(r)">
                                                <Trash2 class="size-3" />
                                            </button>
                                        </template>
                                    </span>
                                    <span v-if="!sec.rooms.length" class="text-muted-foreground text-xs">keine Räume</span>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <!-- add a device to a room -->
        <Teleport to="body">
            <div v-if="deviceDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="deviceDialog = null">
                <form class="bg-card text-card-foreground w-full max-w-lg rounded-xl border shadow-lg" @submit.prevent="submitDevice">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">{{ deviceDialog.title }}</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="deviceDialog = null">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 px-5 py-4 text-sm">
                        <p class="text-muted-foreground text-xs">
                            Erscheint in den Ticket-Formularen in der Geräteliste dieses Raums. Nur in der App, nicht im AD.
                        </p>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Art *</span>
                            <select v-model="deviceForm.kind" class="border-input bg-background h-9 rounded-md border px-2">
                                <option v-for="(label, key) in deviceKinds" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Bezeichnung *</span>
                            <input
                                v-model="deviceForm.name"
                                type="text"
                                maxlength="100"
                                class="border-input bg-background h-9 rounded-md border px-3"
                                autofocus
                            />
                            <span v-if="deviceForm.errors.name" class="text-destructive text-xs">{{ deviceForm.errors.name }}</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Inventar-Nr. / Aufkleber</span>
                            <input
                                v-model="deviceForm.invnr"
                                type="text"
                                maxlength="50"
                                class="border-input bg-background h-9 rounded-md border px-3"
                            />
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="deviceDialog = null">
                            Abbrechen
                        </button>
                        <button
                            type="submit"
                            :disabled="deviceForm.processing || !deviceForm.name.trim()"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-4 text-sm disabled:opacity-60"
                        >
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>

        <!-- old inventory: hallways + printer rooms -->
        <Teleport to="body">
            <div v-if="adoptOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="adoptOpen = false">
                <div class="bg-card text-card-foreground flex max-h-[90vh] w-full max-w-5xl flex-col rounded-xl border shadow-lg">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">Flure & Drucker aus dem alten Inventar</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="adoptOpen = false">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-3 overflow-y-auto px-5 py-4 text-sm">
                        <p class="text-muted-foreground text-xs">
                            Alte Räume ohne AD-Raum, die ein Flur sind oder einen Drucker haben, kommen an ihre AD-Adresse und Etage: zu einem
                            vorhandenen Raum gleichen Namens, sonst als neuer App-Raum. Die Drucker erscheinen danach dort in den Ticket-Formularen.
                            Im AD wird nichts geändert; erneut ausführen legt nichts doppelt an.
                        </p>
                        <p v-if="adoptLoading" class="text-muted-foreground py-6 text-center">Vorschau wird erstellt…</p>
                        <template v-else-if="adoptPlan">
                            <p class="font-medium">
                                {{ adoptPlan.created }} neue Räume · {{ adoptPlan.linked }} mit vorhandenen Räumen verknüpft ·
                                {{ adoptPlan.open }} offen
                            </p>
                            <p v-if="!adoptPlan.rows.length" class="text-muted-foreground py-6 text-center">
                                Nichts zu übernehmen, alles ist schon zugeordnet.
                            </p>
                            <div v-else class="overflow-x-auto rounded-md border">
                                <table class="w-full text-xs">
                                    <thead class="bg-muted/40 text-muted-foreground text-left">
                                        <tr>
                                            <th class="p-2">Status</th>
                                            <th class="p-2">Alter Raum</th>
                                            <th class="p-2">Drucker</th>
                                            <th class="p-2">Ziel / Grund</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr v-for="row in adoptPlan.rows" :key="row.old_id" class="align-top">
                                            <td class="p-2">
                                                <span :class="[badge, STATUS_STYLE[row.status]]">{{ row.status }}</span>
                                            </td>
                                            <td class="p-2">
                                                <p class="font-medium">{{ row.old }}</p>
                                                <p class="text-muted-foreground">
                                                    {{ row.old_place }}<template v-if="row.old_floor"> · Etage {{ row.old_floor }}</template>
                                                </p>
                                            </td>
                                            <td class="p-2">{{ row.printers.join(', ') || '–' }}</td>
                                            <td class="p-2" :class="row.status === 'offen' && 'text-muted-foreground'">
                                                {{ row.target ?? row.reason }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-if="adoptPlan.open" class="text-muted-foreground text-xs">
                                Offene Räume danach auf AD Räume & Computer › Zuordnung von Hand einem Raum zuordnen.
                            </p>
                        </template>
                    </div>
                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="adoptOpen = false">Abbrechen</button>
                        <button
                            type="button"
                            :disabled="adoptLoading || adoptRunning || !adoptPlan || adoptPlan.created + adoptPlan.linked === 0"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-4 text-sm disabled:opacity-60"
                            @click="runAdopt"
                        >
                            Übernehmen
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- new address / new room / rename -->
        <Teleport to="body">
            <div v-if="dialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="dialog = null">
                <form class="bg-card text-card-foreground w-full max-w-lg rounded-xl border shadow-lg" @submit.prevent="submitDialog">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">{{ dialog.title }}</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="dialog = null">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 px-5 py-4 text-sm">
                        <p class="text-muted-foreground text-xs">
                            Existiert nur in der App, nicht im AD, und erscheint in den Ticket-Formularen wie ein AD-Eintrag.
                        </p>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Name *</span>
                            <input
                                v-model="nameForm.name"
                                type="text"
                                :placeholder="dialog.placeholder"
                                class="border-input bg-background h-9 rounded-md border px-3"
                                autofocus
                            />
                            <span v-if="nameForm.errors.name" class="text-destructive text-xs">{{ nameForm.errors.name }}</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Beschreibung</span>
                            <input v-model="nameForm.description" type="text" class="border-input bg-background h-9 rounded-md border px-3" />
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="dialog = null">Abbrechen</button>
                        <button
                            type="submit"
                            :disabled="nameForm.processing || !nameForm.name.trim()"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-4 text-sm disabled:opacity-60"
                        >
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>
    </div>
</template>
