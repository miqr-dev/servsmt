<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';
import { computed, h } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/korso/zertifizierung_items/index.blade.php
 * (ZertifizierungItemController@index) - the "Zert & Quali. Optionen" item-
 * management sub-page linked from Korso/Dashboard.vue's Korso_Admin nav.
 * ZertifizierungItem::all() with no pagination, matching the old page's
 * plain @forelse loop.
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts).
 */

type ZertifizierungItem = { id: number; name: string };

const props = defineProps<{
    items: ZertifizierungItem[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Dashboard', href: '/korso-dashboard' },
            { title: 'Zert & Quali. Optionen', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

function destroy(item: ZertifizierungItem) {
    if (!confirm('Möchten Sie diese Option wirklich löschen?')) return;

    router.delete(`/zertifizierung_items/${item.id}`, { preserveScroll: true });
}

function rowActions(item: ZertifizierungItem): RowAction[] {
    return [
        { icon: Pencil, label: 'Bearbeiten', href: `/zertifizierung_items/${item.id}/edit` },
        { icon: Trash2, label: 'Löschen', variant: 'destructive', onClick: () => destroy(item) },
    ];
}

const ITEM_COLUMNS: DataTableColumn<ZertifizierungItem>[] = [
    { key: 'name' },
    { key: 'actions', sortable: false, searchable: false },
];
const items = computed(() => props.items);
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(
    items,
    ITEM_COLUMNS,
);
</script>

<template>
    <Head title="Zertifizierung & Qualitätsmanagement verwalten" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Zertifizierung & Qualitätsmanagement verwalten</h1>
            <Link
                href="/zertifizierung_items/create"
                class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm"
            >
                + Neue Option hinzufügen
            </Link>
        </div>

        <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Suchen..." />

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <table class="w-full text-sm">
                <thead class="text-muted-foreground text-left">
                    <tr>
                        <TableHeadCell label="Bezeichnung" sort-key="name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('name')" />
                        <TableHeadCell label="Aktionen" align="right" />
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="item in pagedRows" :key="item.id">
                        <td class="p-3">{{ item.name }}</td>
                        <td class="p-3">
                            <RowActions :actions="rowActions(item)" />
                        </td>
                    </tr>
                    <tr v-if="!pagedRows.length">
                        <td colspan="2" class="text-muted-foreground p-6 text-center">
                            {{ search ? 'Nichts gefunden.' : 'Keine Optionen gefunden.' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <TablePagination
                :page="page"
                :page-count="pageCount"
                :range-from="rangeFrom"
                :range-to="rangeTo"
                :total="total"
                item-label="Optionen"
                @update:page="page = $event"
            />
        </div>
    </div>
</template>
