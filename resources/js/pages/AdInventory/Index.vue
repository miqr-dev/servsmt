<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronsDownUp, Monitor, Pencil, Plus, RefreshCw, Trash2, X } from '@lucide/vue';
import { computed, h, provide, reactive, ref, watch } from 'vue';
import AdOuTreeNode from '@/components/inventory/AdOuTreeNode.vue';
import { AD_TREE_KEY, type AdTreeNode } from '@/components/inventory/adTree';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * AD Räume & Computer (AdInventoryController, 2026-10-05): the OUs and
 * computers imported from AD by `php artisan ad:import-inventory`.
 * Left: OU tree (Standort > Adresse > Etage > Raum > Gruppe; the "Computer"
 * container OU is skipped). Right: computers of the selected OU incl.
 * everything below it, or all computers when nothing is selected.
 */
type Ou = { id: number; parent_id: number | null; type: string; label: string; name: string; description: string | null; source?: string };
type Computer = {
    id: number;
    name: string;
    ou: number | null;
    room: number | null;
    group: string | null;
    os: string | null;
    description: string | null;
    enabled: boolean;
    last_logon: string | null;
    dns: string | null;
};

const props = defineProps<{ ous: Ou[]; computers: Computer[]; lastSync: string | null; base: string; needsImport?: boolean }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'AD Räume & Computer', href: '/ad-inventory' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

// --- tree ---
const ouById = computed(() => new Map(props.ous.map((o) => [o.id, o])));
const collator = new Intl.Collator('de', { numeric: true, sensitivity: 'base' });
const TYPE_ORDER: Record<string, number> = { standort: 0, adresse: 1, etage: 2, raum: 3, gruppe: 4, other: 5 };

const computersByOu = computed(() => {
    const m = new Map<number, number>();
    for (const c of props.computers) if (c.ou) m.set(c.ou, (m.get(c.ou) ?? 0) + 1);

    return m;
});

/** Tree without "container" OUs (their children move up one level). */
const roots = computed<AdTreeNode[]>(() => {
    const kids = new Map<number | null, Ou[]>();
    for (const o of props.ous) {
        const list = kids.get(o.parent_id) ?? [];
        list.push(o);
        kids.set(o.parent_id, list);
    }
    const build = (parentId: number | null): AdTreeNode[] => {
        const out: AdTreeNode[] = [];
        for (const o of kids.get(parentId) ?? []) {
            if (o.type === 'container') {
                out.push(...build(o.id));
                continue;
            }
            const children = build(o.id);
            out.push({
                id: o.id,
                label: o.label,
                name: o.name,
                type: o.type,
                source: o.source,
                children,
                count: (computersByOu.value.get(o.id) ?? 0) + children.reduce((s, c) => s + c.count, 0) + containerCount(o.id),
            });
        }

        return out.sort((a, b) => (TYPE_ORDER[a.type] ?? 9) - (TYPE_ORDER[b.type] ?? 9) || collator.compare(a.label, b.label));
    };
    // computers sitting directly in a skipped container still count for its parent
    const containerCount = (id: number) =>
        (kids.get(id) ?? []).filter((k) => k.type === 'container').reduce((s, k) => s + (computersByOu.value.get(k.id) ?? 0), 0);

    return build(null);
});

const expanded = reactive(new Set<number>());
const selected = ref<number | null>(null);
provide(AD_TREE_KEY, {
    expanded,
    selected,
    toggle: (id) => (expanded.has(id) ? expanded.delete(id) : expanded.add(id)),
    select: (id) => {
        selected.value = selected.value === id ? null : id;
        expanded.add(id);
    },
});

// --- path labels ---
function pathOf(ouId: number | null, stopAt: number | null = null): string[] {
    const parts: string[] = [];
    let cur = ouId ? ouById.value.get(ouId) : undefined;
    while (cur && cur.id !== stopAt) {
        if (cur.type !== 'container' && cur.type !== 'gruppe') parts.unshift(cur.label);
        cur = cur.parent_id ? ouById.value.get(cur.parent_id) : undefined;
    }

    return parts;
}
const selectedPath = computed(() =>
    selected.value ? [...pathOf(ouById.value.get(selected.value)?.parent_id ?? null), ouById.value.get(selected.value)?.label] : [],
);

/** all OU ids in the selected subtree */
const selectedIds = computed(() => {
    if (!selected.value) return null;
    const ids = new Set<number>([selected.value]);
    let added = true;
    while (added) {
        added = false;
        for (const o of props.ous) {
            if (o.parent_id && ids.has(o.parent_id) && !ids.has(o.id)) {
                ids.add(o.id);
                added = true;
            }
        }
    }

    return ids;
});

// --- computers table ---
const onlyEnabled = ref(false);
const rows = computed(() => {
    const ids = selectedIds.value;

    return props.computers.filter((c) => (!ids || (c.ou !== null && ids.has(c.ou))) && (!onlyEnabled.value || c.enabled));
});
const locationOf = (c: Computer) => pathOf(c.ou).join(' › ');

const COLUMNS: DataTableColumn<Computer>[] = [
    { key: 'name' },
    { key: 'location', value: (c) => locationOf(c) },
    { key: 'group', value: (c) => c.group ?? '' },
    { key: 'os', value: (c) => c.os ?? '' },
    { key: 'enabled', value: (c) => (c.enabled ? 1 : 0), searchable: false },
    { key: 'last_logon', value: (c) => c.last_logon ?? '', searchable: false },
    { key: 'description', value: (c) => c.description ?? '' },
];
const table = reactive(useDataTable(rows, COLUMNS, { pageSize: 50 }));
watch(selected, () => (table.page = 1));

function formatDate(v: string | null) {
    return v ? new Date(v).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '–';
}
function daysAgo(v: string | null) {
    return v ? Math.floor((Date.now() - new Date(v).getTime()) / 86400000) : null;
}

// --- "Jetzt aktualisieren" (POST /ad-inventory/import, runs ad:import-inventory) ---
const importing = ref(false);
function runImport() {
    importing.value = true;
    router.post('/ad-inventory/import', {}, { preserveScroll: true, preserveState: true, onFinish: () => (importing.value = false) });
}

// --- rooms that are not in AD (source 'app'): add under an Adresse / Etage, rename, delete ---
const selectedOu = computed(() => (selected.value ? ouById.value.get(selected.value) : undefined));
const canAddRoom = computed(() => selectedOu.value && ['adresse', 'etage'].includes(selectedOu.value.type));
const isAppRoom = computed(() => selectedOu.value?.source === 'app');
const roomDialog = ref<'create' | 'edit' | null>(null);
const roomForm = useForm({ parent_id: 0, name: '', description: '' });
// possible places for a room: every Adresse / Etage, with its path
const roomParents = computed(() =>
    props.ous
        .filter((o) => o.type === 'adresse' || o.type === 'etage')
        .map((o) => ({ id: o.id, label: pathOf(o.id).join(' › ') }))
        .sort((a, b) => collator.compare(a.label, b.label)),
);

function openCreateRoom() {
    roomForm.reset();
    roomForm.clearErrors();
    roomForm.parent_id = selectedOu.value!.id;
    roomDialog.value = 'create';
}
function openEditRoom() {
    const o = selectedOu.value!;
    roomForm.clearErrors();
    roomForm.parent_id = o.parent_id ?? 0;
    roomForm.name = o.name;
    roomForm.description = o.description ?? '';
    roomDialog.value = 'edit';
}
function submitRoom() {
    const opts = { preserveScroll: true, preserveState: true, onSuccess: () => (roomDialog.value = null) };
    if (roomDialog.value === 'edit') roomForm.put(`/ad-inventory/rooms/${selected.value}`, opts);
    else roomForm.post('/ad-inventory/rooms', opts);
}
function deleteRoom() {
    const o = selectedOu.value!;
    if (!confirm(`Raum "${o.name}" löschen? Alte Tickets behalten den Namen.`)) return;
    router.delete(`/ad-inventory/rooms/${o.id}`, { preserveScroll: true, preserveState: true, onSuccess: () => (selected.value = null) });
}

// --- stats ---
const stats = computed(() => ({
    standorte: props.ous.filter((o) => o.type === 'standort').length,
    raeume: props.ous.filter((o) => o.type === 'raum').length,
    computer: props.computers.length,
    ohneRaum: props.computers.filter((c) => !c.room).length,
    deaktiviert: props.computers.filter((c) => !c.enabled).length,
}));
</script>

<template>
    <Head title="AD Räume & Computer" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h1 class="text-xl font-semibold">AD Räume &amp; Computer</h1>
                <p class="text-muted-foreground text-xs">
                    Aus dem Active Directory ({{ base }}) · zuletzt importiert:
                    {{ lastSync ? new Date(lastSync).toLocaleString('de-DE', { dateStyle: 'short', timeStyle: 'short' }) : 'noch nie' }}
                    · automatisch jede Stunde
                </p>
            </div>
            <Link href="/ad-inventory/zuordnung" class="hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm">
                Zuordnung alt ↔ AD
            </Link>
            <button
                type="button"
                :disabled="importing"
                class="hover:bg-accent inline-flex h-9 items-center gap-2 rounded-md border px-3 text-sm disabled:opacity-60"
                @click="runImport"
            >
                <RefreshCw class="size-4" :class="{ 'animate-spin': importing }" />
                {{ importing ? 'Wird aktualisiert…' : 'Jetzt aktualisieren' }}
            </button>
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="bg-muted rounded-full px-2.5 py-1">{{ stats.standorte }} Standorte</span>
                <span class="bg-muted rounded-full px-2.5 py-1">{{ stats.raeume }} Räume</span>
                <span class="bg-muted rounded-full px-2.5 py-1">{{ stats.computer }} Computer</span>
                <span class="bg-muted rounded-full px-2.5 py-1">{{ stats.ohneRaum }} ohne Raum</span>
                <span class="bg-muted rounded-full px-2.5 py-1">{{ stats.deaktiviert }} deaktiviert</span>
            </div>
        </div>

        <div
            v-if="needsImport"
            class="rounded-xl border border-orange-300 bg-orange-50 px-4 py-3 text-sm text-orange-800 dark:border-orange-800 dark:bg-orange-950/40 dark:text-orange-200"
        >
            Die Räume sind noch nicht erkannt (die OUs wurden vor der Raum-Erkennung importiert). Bitte einmal
            <strong>Jetzt aktualisieren</strong> klicken.
        </div>

        <div class="grid min-h-0 flex-1 gap-4 lg:grid-cols-[22rem_1fr]">
            <!-- OU tree -->
            <section class="bg-card text-card-foreground flex min-h-0 flex-col rounded-xl border shadow-sm">
                <header class="flex items-center justify-between border-b px-3 py-2">
                    <h2 class="text-sm font-semibold">Struktur</h2>
                    <button
                        type="button"
                        class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs"
                        title="Alle zuklappen"
                        @click="expanded.clear()"
                    >
                        <ChevronsDownUp class="size-3.5" /> zuklappen
                    </button>
                </header>
                <ul class="max-h-[calc(100vh-14rem)] overflow-y-auto p-2">
                    <AdOuTreeNode v-for="n in roots" :key="n.id" :node="n" :level="0" />
                    <li v-if="!roots.length" class="text-muted-foreground p-3 text-sm">
                        Noch nichts importiert - <code>php artisan ad:import-inventory</code> ausführen.
                    </li>
                </ul>
            </section>

            <!-- computers -->
            <section class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
                <header class="flex flex-wrap items-center justify-between gap-2 border-b px-4 py-3">
                    <h2 class="flex min-w-0 items-center gap-2 font-semibold">
                        <Monitor class="text-primary size-4 shrink-0" />
                        <span v-if="selected" class="truncate">{{ selectedPath.join(' › ') }}</span>
                        <span v-else>Alle Computer</span>
                    </h2>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            v-if="canAddRoom"
                            type="button"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-8 items-center gap-1.5 rounded-md px-3 text-sm"
                            title="Raum ohne Computer (z. B. Küche, Flur, WC) - nicht im AD"
                            @click="openCreateRoom"
                        >
                            <Plus class="size-4" /> Raum hinzufügen
                        </button>
                        <template v-if="isAppRoom">
                            <button
                                type="button"
                                class="hover:bg-accent inline-flex h-8 items-center gap-1.5 rounded-md border px-3 text-sm"
                                @click="openEditRoom"
                            >
                                <Pencil class="size-4" /> Bearbeiten
                            </button>
                            <button
                                type="button"
                                class="text-destructive hover:bg-destructive/10 inline-flex h-8 items-center gap-1.5 rounded-md border px-3 text-sm"
                                @click="deleteRoom"
                            >
                                <Trash2 class="size-4" /> Löschen
                            </button>
                        </template>
                        <label class="flex items-center gap-1.5 text-sm">
                            <input v-model="onlyEnabled" type="checkbox" class="size-4" /> nur aktive
                        </label>
                        <button
                            v-if="selected"
                            type="button"
                            class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm"
                            @click="selected = null"
                        >
                            <X class="size-4" /> Auswahl aufheben
                        </button>
                    </div>
                </header>

                <div class="p-3">
                    <TableToolbar
                        v-model:search="table.search"
                        v-model:page-size="table.pageSize"
                        search-placeholder="Computer, Raum, OS suchen..."
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell
                                    label="Name"
                                    sort-key="name"
                                    :active-key="table.sortKey"
                                    :direction="table.sortDir"
                                    @sort="table.toggleSort('name')"
                                />
                                <TableHeadCell
                                    label="Ort"
                                    sort-key="location"
                                    :active-key="table.sortKey"
                                    :direction="table.sortDir"
                                    @sort="table.toggleSort('location')"
                                />
                                <TableHeadCell
                                    label="Gruppe"
                                    sort-key="group"
                                    :active-key="table.sortKey"
                                    :direction="table.sortDir"
                                    @sort="table.toggleSort('group')"
                                />
                                <TableHeadCell
                                    label="OS"
                                    sort-key="os"
                                    :active-key="table.sortKey"
                                    :direction="table.sortDir"
                                    @sort="table.toggleSort('os')"
                                />
                                <TableHeadCell
                                    label="Aktiv"
                                    sort-key="enabled"
                                    :active-key="table.sortKey"
                                    :direction="table.sortDir"
                                    @sort="table.toggleSort('enabled')"
                                />
                                <TableHeadCell
                                    label="Letzte Anmeldung"
                                    sort-key="last_logon"
                                    :active-key="table.sortKey"
                                    :direction="table.sortDir"
                                    @sort="table.toggleSort('last_logon')"
                                />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="c in table.pagedRows" :key="c.id" :class="{ 'opacity-60': !c.enabled }">
                                <td class="p-3 font-medium whitespace-nowrap">
                                    {{ c.name }}
                                    <span v-if="c.description" class="text-muted-foreground block text-xs font-normal">{{ c.description }}</span>
                                </td>
                                <td class="p-3">
                                    <span v-if="c.room">{{ locationOf(c) }}</span>
                                    <span v-else class="text-muted-foreground">{{ locationOf(c) || '–' }} <em class="text-xs">(kein Raum)</em></span>
                                </td>
                                <td class="p-3">{{ c.group ?? '–' }}</td>
                                <td class="p-3 text-xs">{{ c.os ?? '–' }}</td>
                                <td class="p-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-xs font-medium"
                                        :class="c.enabled ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                    >
                                        <span class="size-2 rounded-full" :class="c.enabled ? 'bg-green-500' : 'bg-red-500'" />
                                        {{ c.enabled ? 'ja' : 'nein' }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    {{ formatDate(c.last_logon) }}
                                    <span
                                        v-if="daysAgo(c.last_logon) !== null && daysAgo(c.last_logon)! > 90"
                                        class="block text-xs text-orange-600 dark:text-orange-400"
                                        >vor {{ daysAgo(c.last_logon) }} Tagen</span
                                    >
                                </td>
                            </tr>
                            <tr v-if="!table.pagedRows.length">
                                <td colspan="6" class="text-muted-foreground p-4 text-center">Keine Computer.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <TablePagination
                    :page="table.page"
                    :page-count="table.pageCount"
                    :range-from="table.rangeFrom"
                    :range-to="table.rangeTo"
                    :total="table.total"
                    item-label="Computern"
                    @update:page="table.page = $event"
                />
            </section>
        </div>

        <!-- Raum (nicht im AD) anlegen / bearbeiten -->
        <Teleport to="body">
            <div v-if="roomDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="roomDialog = null">
                <form class="bg-card text-card-foreground w-full max-w-lg rounded-xl border shadow-lg" @submit.prevent="submitRoom">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">{{ roomDialog === 'edit' ? 'Raum bearbeiten' : 'Raum hinzufügen' }}</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="roomDialog = null">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 px-5 py-4 text-sm">
                        <p class="text-muted-foreground text-xs">
                            Für Räume ohne Computer (Küche, Flur, WC, Büro …). Der Raum existiert nur in der App, nicht im AD, und erscheint in den
                            Ticket-Formularen wie ein AD-Raum.
                        </p>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Wo (Adresse / Etage) *</span>
                            <select v-model="roomForm.parent_id" class="border-input bg-background h-9 rounded-md border px-2">
                                <option v-for="p in roomParents" :key="p.id" :value="p.id">{{ p.label }}</option>
                            </select>
                            <span v-if="roomForm.errors.parent_id" class="text-destructive text-xs">{{ roomForm.errors.parent_id }}</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Raumname *</span>
                            <input
                                v-model="roomForm.name"
                                type="text"
                                placeholder="z. B. Küche, WC Damen, 3.01"
                                class="border-input bg-background h-9 rounded-md border px-3"
                                autofocus
                            />
                            <span v-if="roomForm.errors.name" class="text-destructive text-xs">{{ roomForm.errors.name }}</span>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Beschreibung</span>
                            <input v-model="roomForm.description" type="text" class="border-input bg-background h-9 rounded-md border px-3" />
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="roomDialog = null">Abbrechen</button>
                        <button
                            type="submit"
                            :disabled="roomForm.processing || !roomForm.name.trim()"
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
