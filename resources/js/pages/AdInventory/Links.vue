<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Ban, Check, Pencil, RotateCcw, X } from '@lucide/vue';
import { computed, h, reactive, ref, watch } from 'vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Zuordnung alt ↔ AD (AdInventoryController@links, 2026-10-05) - step 1 of
 * switching tickets to the AD rooms / computers. Old inv_rooms / inv_items
 * (Server, PC, Laptop) and their AD room / computer:
 *   auto    = found by the import (room: its ad_ou / sub-room OU, computer: same name)
 *   manuell = set here (never overwritten by the import)
 *   "Nicht im AD" = manuell, without AD entry
 */
type LinkKind = 'auto' | 'manual' | null;
type Room = {
    id: number;
    place: string | null;
    address: string | null;
    etage: string | null;
    name: string;
    old_ou: string | null;
    items: number;
    ad_id: number | null;
    link: LinkKind;
};
type Item = {
    id: number;
    invnr: string;
    name: string | null;
    gart: string | null;
    typ: string | null;
    room: string | null;
    ad_id: number | null;
    link: LinkKind;
};
type AdRoom = { id: number; label: string };
type AdComputer = { id: number; name: string; label: string; deleted: boolean };

const props = defineProps<{ rooms: Room[]; items: Item[]; adRooms: AdRoom[]; adComputers: AdComputer[] }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'AD Räume & Computer', href: '/ad-inventory' },
            { title: 'Zuordnung', href: '/ad-inventory/zuordnung' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const tab = ref<'rooms' | 'items'>((new URLSearchParams(window.location.search).get('tab') as 'items') === 'items' ? 'items' : 'rooms');
const filter = ref<'all' | 'open' | 'auto' | 'manual'>('open');

const adRoomById = computed(() => new Map(props.adRooms.map((r) => [r.id, r])));
const adPcById = computed(() => new Map(props.adComputers.map((c) => [c.id, c])));

const isOpen = (r: { ad_id: number | null; link: LinkKind }) => !r.ad_id && r.link !== 'manual';
const matchFilter = (r: { ad_id: number | null; link: LinkKind }) =>
    filter.value === 'all' ||
    (filter.value === 'open' && isOpen(r)) ||
    (filter.value === 'auto' && r.link === 'auto') ||
    (filter.value === 'manual' && r.link === 'manual');

const counts = computed(() => {
    const list = tab.value === 'rooms' ? props.rooms : props.items;

    return {
        all: list.length,
        open: list.filter(isOpen).length,
        auto: list.filter((r) => r.link === 'auto').length,
        manual: list.filter((r) => r.link === 'manual').length,
    };
});

// --- tables ---
const roomRows = computed(() => props.rooms.filter(matchFilter));
const itemRows = computed(() => props.items.filter(matchFilter));
const ROOM_COLUMNS: DataTableColumn<Room>[] = [
    { key: 'id' },
    { key: 'place', value: (r) => r.place ?? '' },
    { key: 'address', value: (r) => r.address ?? '' },
    { key: 'name' },
    { key: 'items', searchable: false },
    { key: 'ad', value: (r) => (r.ad_id ? (adRoomById.value.get(r.ad_id)?.label ?? '') : '') },
];
const ITEM_COLUMNS: DataTableColumn<Item>[] = [
    { key: 'invnr' },
    { key: 'name', value: (i) => i.name ?? '' },
    { key: 'gart', value: (i) => i.gart ?? '' },
    { key: 'room', value: (i) => i.room ?? '' },
    { key: 'ad', value: (i) => (i.ad_id ? `${adPcById.value.get(i.ad_id)?.name ?? ''} ${adPcById.value.get(i.ad_id)?.label ?? ''}` : '') },
];
const roomTable = reactive(useDataTable(roomRows, ROOM_COLUMNS, { pageSize: 50 }));
const itemTable = reactive(useDataTable(itemRows, ITEM_COLUMNS, { pageSize: 50 }));
watch(filter, () => {
    roomTable.page = 1;
    itemTable.page = 1;
});

// --- edit (one row at a time) ---
const editing = ref<{ kind: 'room' | 'item'; id: number } | null>(null);
const query = ref('');
const saving = ref(false);

function startEdit(kind: 'room' | 'item', row: Room | Item) {
    editing.value = { kind, id: row.id };
    query.value = kind === 'item' ? ((row as Item).name ?? '') : (row as Room).name;
}

const options = computed(() => {
    if (!editing.value) return [];
    const q = query.value.trim().toLowerCase();
    if (q.length < 2) return [];
    if (editing.value.kind === 'room') {
        return props.adRooms
            .filter((r) => r.label.toLowerCase().includes(q))
            .slice(0, 10)
            .map((r) => ({ id: r.id, title: r.label, sub: '', deleted: false }));
    }

    return props.adComputers
        .filter((c) => c.name.toLowerCase().includes(q) || c.label.toLowerCase().includes(q))
        .slice(0, 10)
        .map((c) => ({ id: c.id, title: c.name, sub: c.label, deleted: c.deleted }));
});

function save(mode: 'ad' | 'none' | 'auto', adId: number | null = null) {
    if (!editing.value) return;
    saving.value = true;
    router.put(
        `/ad-inventory/zuordnung/${editing.value.kind}/${editing.value.id}`,
        { mode, ad_id: adId },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => (editing.value = null),
            onFinish: () => (saving.value = false),
        },
    );
}

const badge = (link: LinkKind, hasAd: boolean) =>
    link === 'manual'
        ? hasAd
            ? { text: 'manuell', cls: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }
            : { text: 'nicht im AD', cls: 'bg-muted text-muted-foreground' }
        : hasAd
          ? { text: 'auto', cls: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' }
          : { text: 'offen', cls: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' };

const FILTERS = [
    { key: 'open', label: 'Offen' },
    { key: 'auto', label: 'Automatisch' },
    { key: 'manual', label: 'Manuell' },
    { key: 'all', label: 'Alle' },
] as const;
</script>

<template>
    <Head title="Zuordnung alt ↔ AD" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div>
                <h1 class="text-xl font-semibold">Zuordnung: altes Inventar ↔ AD</h1>
                <p class="text-muted-foreground max-w-3xl text-xs">
                    Jeder alte Raum / Computer bekommt seinen AD-Raum / AD-Computer. Automatisch beim Import (Raum über die eingetragene OU, Computer
                    über den Namen); hier lässt sich das korrigieren. Manuelle Zuordnungen überschreibt der Import nie. Alte Tickets bleiben
                    unverändert.
                </p>
            </div>
            <Link href="/ad-inventory" class="hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm">Zur AD-Übersicht</Link>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="bg-muted inline-flex rounded-lg p-1 text-sm">
                <button
                    v-for="t in [
                        { key: 'rooms', label: `Räume (${rooms.length})` },
                        { key: 'items', label: `Computer (${items.length})` },
                    ]"
                    :key="t.key"
                    type="button"
                    class="rounded-md px-3 py-1.5"
                    :class="tab === t.key ? 'bg-background font-medium shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                    @click="
                        tab = t.key as 'rooms' | 'items';
                        editing = null;
                    "
                >
                    {{ t.label }}
                </button>
            </div>
            <div class="flex flex-wrap gap-1 text-sm">
                <button
                    v-for="f in FILTERS"
                    :key="f.key"
                    type="button"
                    class="rounded-full border px-3 py-1"
                    :class="filter === f.key ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent'"
                    @click="filter = f.key"
                >
                    {{ f.label }} ({{ counts[f.key] }})
                </button>
            </div>
        </div>

        <!-- Räume -->
        <section v-if="tab === 'rooms'" class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="p-3">
                <TableToolbar
                    v-model:search="roomTable.search"
                    v-model:page-size="roomTable.pageSize"
                    search-placeholder="Raum, Standort, AD-Raum suchen..."
                />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell
                                label="Alter Raum"
                                sort-key="name"
                                :active-key="roomTable.sortKey"
                                :direction="roomTable.sortDir"
                                @sort="roomTable.toggleSort('name')"
                            />
                            <TableHeadCell
                                label="Standort / Adresse"
                                sort-key="place"
                                :active-key="roomTable.sortKey"
                                :direction="roomTable.sortDir"
                                @sort="roomTable.toggleSort('place')"
                            />
                            <TableHeadCell
                                label="Geräte"
                                sort-key="items"
                                :active-key="roomTable.sortKey"
                                :direction="roomTable.sortDir"
                                @sort="roomTable.toggleSort('items')"
                            />
                            <TableHeadCell
                                label="AD-Raum"
                                sort-key="ad"
                                :active-key="roomTable.sortKey"
                                :direction="roomTable.sortDir"
                                @sort="roomTable.toggleSort('ad')"
                            />
                            <TableHeadCell label="" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template v-for="r in roomTable.pagedRows" :key="r.id">
                            <tr class="align-top">
                                <td class="p-3">
                                    <span class="font-medium">{{ r.name || '–' }}</span>
                                    <span class="text-muted-foreground block text-xs"
                                        >#{{ r.id }}<span v-if="r.etage"> · Etage {{ r.etage }}</span></span
                                    >
                                </td>
                                <td class="p-3">
                                    {{ r.place ?? '–' }}
                                    <span class="text-muted-foreground block text-xs">{{ r.address }}</span>
                                </td>
                                <td class="p-3 tabular-nums">{{ r.items }}</td>
                                <td class="p-3">
                                    <span class="mr-2 rounded px-1.5 py-px text-[10px] font-medium uppercase" :class="badge(r.link, !!r.ad_id).cls">{{
                                        badge(r.link, !!r.ad_id).text
                                    }}</span>
                                    <span v-if="r.ad_id">{{ adRoomById.get(r.ad_id)?.label ?? `#${r.ad_id}` }}</span>
                                    <span v-if="r.old_ou && !r.ad_id" class="text-muted-foreground block text-xs break-all"
                                        >alte OU: {{ r.old_ou }}</span
                                    >
                                </td>
                                <td class="p-3 text-right">
                                    <button
                                        type="button"
                                        class="hover:bg-accent rounded-md p-1.5"
                                        title="Zuordnung ändern"
                                        @click="startEdit('room', r)"
                                    >
                                        <Pencil class="size-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="editing?.kind === 'room' && editing.id === r.id">
                                <td colspan="5" class="bg-muted/40 p-3">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-wrap gap-2">
                                            <input
                                                v-model="query"
                                                type="search"
                                                placeholder="AD-Raum suchen, z. B. 3.05 oder Dresden"
                                                class="border-input bg-background h-9 min-w-72 flex-1 rounded-md border px-3 text-sm"
                                                autofocus
                                            />
                                            <button
                                                type="button"
                                                :disabled="saving"
                                                class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
                                                @click="save('none')"
                                            >
                                                <Ban class="size-4" /> Nicht im AD
                                            </button>
                                            <button
                                                type="button"
                                                :disabled="saving"
                                                class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
                                                @click="save('auto')"
                                            >
                                                <RotateCcw class="size-4" /> Automatisch
                                            </button>
                                            <button
                                                type="button"
                                                class="text-muted-foreground hover:text-foreground inline-flex h-9 items-center px-2"
                                                @click="editing = null"
                                            >
                                                <X class="size-4" />
                                            </button>
                                        </div>
                                        <ul v-if="options.length" class="bg-background divide-y rounded-md border text-sm">
                                            <li v-for="o in options" :key="o.id">
                                                <button
                                                    type="button"
                                                    :disabled="saving"
                                                    class="hover:bg-accent flex w-full items-center gap-2 px-3 py-2 text-left"
                                                    @click="save('ad', o.id)"
                                                >
                                                    <Check class="text-primary size-4 shrink-0" /> {{ o.title }}
                                                </button>
                                            </li>
                                        </ul>
                                        <p v-else-if="query.trim().length >= 2" class="text-muted-foreground text-xs">Kein AD-Raum gefunden.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="!roomTable.pagedRows.length">
                            <td colspan="5" class="text-muted-foreground p-4 text-center">Keine Räume in dieser Ansicht.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <TablePagination
                :page="roomTable.page"
                :page-count="roomTable.pageCount"
                :range-from="roomTable.rangeFrom"
                :range-to="roomTable.rangeTo"
                :total="roomTable.total"
                item-label="Räumen"
                @update:page="roomTable.page = $event"
            />
        </section>

        <!-- Computer -->
        <section v-else class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="p-3">
                <TableToolbar
                    v-model:search="itemTable.search"
                    v-model:page-size="itemTable.pageSize"
                    search-placeholder="Inventarnr., Name, Raum suchen..."
                />
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell
                                label="Inventarnr."
                                sort-key="invnr"
                                :active-key="itemTable.sortKey"
                                :direction="itemTable.sortDir"
                                @sort="itemTable.toggleSort('invnr')"
                            />
                            <TableHeadCell
                                label="Name"
                                sort-key="name"
                                :active-key="itemTable.sortKey"
                                :direction="itemTable.sortDir"
                                @sort="itemTable.toggleSort('name')"
                            />
                            <TableHeadCell
                                label="Art"
                                sort-key="gart"
                                :active-key="itemTable.sortKey"
                                :direction="itemTable.sortDir"
                                @sort="itemTable.toggleSort('gart')"
                            />
                            <TableHeadCell
                                label="Alter Raum"
                                sort-key="room"
                                :active-key="itemTable.sortKey"
                                :direction="itemTable.sortDir"
                                @sort="itemTable.toggleSort('room')"
                            />
                            <TableHeadCell
                                label="AD-Computer"
                                sort-key="ad"
                                :active-key="itemTable.sortKey"
                                :direction="itemTable.sortDir"
                                @sort="itemTable.toggleSort('ad')"
                            />
                            <TableHeadCell label="" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template v-for="i in itemTable.pagedRows" :key="i.id">
                            <tr class="align-top">
                                <td class="p-3 tabular-nums">{{ i.invnr }}</td>
                                <td class="p-3 font-medium">{{ i.name ?? '–' }}</td>
                                <td class="p-3">
                                    {{ i.gart ?? '–' }}
                                    <span v-if="i.typ" class="text-muted-foreground block text-xs">{{ i.typ }}</span>
                                </td>
                                <td class="p-3">{{ i.room ?? '–' }}</td>
                                <td class="p-3">
                                    <span class="mr-2 rounded px-1.5 py-px text-[10px] font-medium uppercase" :class="badge(i.link, !!i.ad_id).cls">{{
                                        badge(i.link, !!i.ad_id).text
                                    }}</span>
                                    <template v-if="i.ad_id">
                                        <span :class="{ 'line-through opacity-60': adPcById.get(i.ad_id)?.deleted }">{{
                                            adPcById.get(i.ad_id)?.name ?? `#${i.ad_id}`
                                        }}</span>
                                        <span class="text-muted-foreground block text-xs"
                                            >{{ adPcById.get(i.ad_id)?.label
                                            }}<span v-if="adPcById.get(i.ad_id)?.deleted"> · nicht mehr im AD</span></span
                                        >
                                    </template>
                                </td>
                                <td class="p-3 text-right">
                                    <button
                                        type="button"
                                        class="hover:bg-accent rounded-md p-1.5"
                                        title="Zuordnung ändern"
                                        @click="startEdit('item', i)"
                                    >
                                        <Pencil class="size-4" />
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="editing?.kind === 'item' && editing.id === i.id">
                                <td colspan="6" class="bg-muted/40 p-3">
                                    <div class="flex flex-col gap-2">
                                        <div class="flex flex-wrap gap-2">
                                            <input
                                                v-model="query"
                                                type="search"
                                                placeholder="AD-Computer suchen (Name oder Raum)"
                                                class="border-input bg-background h-9 min-w-72 flex-1 rounded-md border px-3 text-sm"
                                                autofocus
                                            />
                                            <button
                                                type="button"
                                                :disabled="saving"
                                                class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
                                                @click="save('none')"
                                            >
                                                <Ban class="size-4" /> Nicht im AD
                                            </button>
                                            <button
                                                type="button"
                                                :disabled="saving"
                                                class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
                                                @click="save('auto')"
                                            >
                                                <RotateCcw class="size-4" /> Automatisch
                                            </button>
                                            <button
                                                type="button"
                                                class="text-muted-foreground hover:text-foreground inline-flex h-9 items-center px-2"
                                                @click="editing = null"
                                            >
                                                <X class="size-4" />
                                            </button>
                                        </div>
                                        <ul v-if="options.length" class="bg-background divide-y rounded-md border text-sm">
                                            <li v-for="o in options" :key="o.id">
                                                <button
                                                    type="button"
                                                    :disabled="saving"
                                                    class="hover:bg-accent flex w-full items-center gap-2 px-3 py-2 text-left"
                                                    @click="save('ad', o.id)"
                                                >
                                                    <Check class="text-primary size-4 shrink-0" />
                                                    <span :class="{ 'line-through opacity-60': o.deleted }">{{ o.title }}</span>
                                                    <span class="text-muted-foreground text-xs">{{ o.sub }}</span>
                                                </button>
                                            </li>
                                        </ul>
                                        <p v-else-if="query.trim().length >= 2" class="text-muted-foreground text-xs">Kein AD-Computer gefunden.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="!itemTable.pagedRows.length">
                            <td colspan="6" class="text-muted-foreground p-4 text-center">Keine Computer in dieser Ansicht.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <TablePagination
                :page="itemTable.page"
                :page-count="itemTable.pageCount"
                :range-from="itemTable.rangeFrom"
                :range-to="itemTable.rangeTo"
                :total="itemTable.total"
                item-label="Computern"
                @update:page="itemTable.page = $event"
            />
        </section>
    </div>
</template>
