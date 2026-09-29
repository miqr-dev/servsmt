<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, RotateCcw } from '@lucide/vue';
import { computed, h, reactive, ref } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Kündigungen - Verlauf (/terminations/history, TerminationController@history).
 * Replaces termination/history.blade.php. Lists every entry that left the
 * Dashboard list: "Entfernt" (not needed anymore - with reason, who, when) or
 * "Gelöscht" (plain delete). Both can be restored back to the list.
 */

type HistoryRow = {
    id: number;
    name: string;
    location: string | null;
    occupation: string | null;
    exit: string | null;
    deleted_at: string | null;
    kind: 'removed' | 'deleted';
    reason: string | null;
    note: string | null;
    removed_by: string | null;
};

const props = defineProps<{ terminations: HistoryRow[] }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Dashboard', href: '/' },
            { title: 'Kündigungen - Verlauf', href: '/terminations/history' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const filter = ref<'all' | 'removed' | 'deleted'>('all');

function fmt(value: string | null, withTime = false): string {
    if (!value) return '';
    const d = new Date(value.length === 10 ? `${value}T00:00:00` : value);

    return withTime
        ? d.toLocaleString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        : d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function restore(row: HistoryRow) {
    if (!confirm(`"${row.name}" wieder in die Kündigungsliste aufnehmen?`)) return;
    router.post(`/terminations/restore/${row.id}`, {}, { preserveScroll: true });
}

function actions(row: HistoryRow): RowAction[] {
    return [{ icon: RotateCcw, label: 'Wiederherstellen', onClick: () => restore(row) }];
}

const COLUMNS: DataTableColumn<HistoryRow>[] = [
    { key: 'name' },
    { key: 'location' },
    { key: 'occupation' },
    { key: 'exit', searchable: false },
    { key: 'kind', value: (r) => (r.kind === 'removed' ? `Entfernt ${r.reason ?? ''} ${r.note ?? ''}` : 'Gelöscht') },
    { key: 'deleted_at', searchable: false },
    { key: 'removed_by' },
    { key: 'actions', sortable: false, searchable: false },
];

const rows = computed(() => (filter.value === 'all' ? props.terminations : props.terminations.filter((r) => r.kind === filter.value)));
const table = reactive(useDataTable(rows, COLUMNS));

const counts = computed(() => ({
    all: props.terminations.length,
    removed: props.terminations.filter((r) => r.kind === 'removed').length,
    deleted: props.terminations.filter((r) => r.kind === 'deleted').length,
}));
</script>

<template>
    <Head title="Kündigungen - Verlauf" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Kündigungen - Verlauf</h1>
                <p class="text-muted-foreground text-sm">Entfernte und gelöschte Einträge. Wiederherstellen setzt den Eintrag zurück in die Liste.</p>
            </div>
            <Link href="/" class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-sm">
                <ArrowLeft class="size-4" />
                Zurück zum Dashboard
            </Link>
        </div>

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b p-3">
                <button
                    v-for="f in [
                        { key: 'all', label: 'Alle' },
                        { key: 'removed', label: 'Entfernt' },
                        { key: 'deleted', label: 'Gelöscht' },
                    ] as const"
                    :key="f.key"
                    type="button"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md border px-3 text-sm transition-colors"
                    :class="filter === f.key ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent'"
                    @click="filter = f.key"
                >
                    {{ f.label }}
                    <span class="text-xs opacity-80">{{ counts[f.key] }}</span>
                </button>
            </div>
            <div class="p-3">
                <TableToolbar v-model:search="table.search" v-model:page-size="table.pageSize" search-placeholder="Verlauf durchsuchen..." />
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
                                label="Standort"
                                sort-key="location"
                                :active-key="table.sortKey"
                                :direction="table.sortDir"
                                @sort="table.toggleSort('location')"
                            />
                            <TableHeadCell
                                label="Beschäftigung"
                                sort-key="occupation"
                                :active-key="table.sortKey"
                                :direction="table.sortDir"
                                @sort="table.toggleSort('occupation')"
                            />
                            <TableHeadCell
                                label="Austritt"
                                sort-key="exit"
                                :active-key="table.sortKey"
                                :direction="table.sortDir"
                                @sort="table.toggleSort('exit')"
                            />
                            <TableHeadCell
                                label="Status / Grund"
                                sort-key="kind"
                                :active-key="table.sortKey"
                                :direction="table.sortDir"
                                @sort="table.toggleSort('kind')"
                            />
                            <TableHeadCell
                                label="Am"
                                sort-key="deleted_at"
                                :active-key="table.sortKey"
                                :direction="table.sortDir"
                                @sort="table.toggleSort('deleted_at')"
                            />
                            <TableHeadCell
                                label="Von"
                                sort-key="removed_by"
                                :active-key="table.sortKey"
                                :direction="table.sortDir"
                                @sort="table.toggleSort('removed_by')"
                            />
                            <TableHeadCell label="Aktion" align="right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="r in table.pagedRows" :key="r.id">
                            <td class="p-3 font-medium">{{ r.name }}</td>
                            <td class="p-3">{{ r.location }}</td>
                            <td class="p-3">{{ r.occupation }}</td>
                            <td class="p-3 whitespace-nowrap">{{ fmt(r.exit) }}</td>
                            <td class="p-3">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        r.kind === 'removed'
                                            ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200'
                                            : 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300'
                                    "
                                >
                                    {{ r.kind === 'removed' ? 'Entfernt' : 'Gelöscht' }}
                                </span>
                                <span v-if="r.reason" class="mt-1 block">{{ r.reason }}</span>
                                <span v-if="r.note" class="text-muted-foreground block text-xs">{{ r.note }}</span>
                            </td>
                            <td class="text-muted-foreground p-3 whitespace-nowrap">{{ fmt(r.deleted_at, true) }}</td>
                            <td class="p-3">{{ r.removed_by ?? '–' }}</td>
                            <td class="p-3"><RowActions :actions="actions(r)" /></td>
                        </tr>
                        <tr v-if="!table.pagedRows.length">
                            <td colspan="8" class="text-muted-foreground p-6 text-center">
                                {{ table.search ? 'Keine Einträge gefunden.' : 'Keine Einträge im Verlauf.' }}
                            </td>
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
                item-label="Einträge"
                @update:page="table.page = $event"
            />
        </div>
    </div>
</template>
