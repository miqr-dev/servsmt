<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Pencil, Power, PowerOff, Trash2 } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';

/**
 * HR "Kündigungen" box on the unified Dashboard (pages/Home.vue), next to the
 * email forwardings, under the news box. Same look as the former /dashboard
 * table (search + sortable columns + row actions), but no paging: all rows
 * are in the table and the body scrolls once there are more than 5 rows.
 *
 * Exit date colours (your rule, 2026-09-29):
 * - exit day today (or already passed) -> red
 * - within the next 7 days             -> orange
 * - more than a week away              -> green
 * - inactive entries                   -> grey
 */

export type TerminationRow = {
    id: number;
    name: string;
    exit: string | null;
    is_active: boolean;
    location: string | null;
    occupation: string | null;
};

const props = defineProps<{ terminations: TerminationRow[] }>();

const MAX_VISIBLE_ROWS = 5;
const DAY_MS = 24 * 60 * 60 * 1000;

/** "2026-09-11..." -> local midnight of that day (ignores the time/zone part). */
function dayOf(value: string): Date | null {
    const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(value);

    return m ? new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3])) : null;
}

function formatDate(value: string | null): string {
    const d = value ? dayOf(value) : null;

    return d ? d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '';
}

function exitColorClass(t: TerminationRow): string {
    if (!t.is_active) return 'font-semibold text-neutral-500';
    const d = t.exit ? dayOf(t.exit) : null;
    if (!d) return '';

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const days = Math.round((d.getTime() - today.getTime()) / DAY_MS);

    if (days <= 0) return 'font-semibold text-red-600 dark:text-red-400';
    if (days <= 7) return 'font-semibold text-orange-500 dark:text-orange-400';

    return 'font-semibold text-green-600 dark:text-green-400';
}

function deleteTermination(t: TerminationRow) {
    if (!confirm(`"${t.name}" wirklich löschen?`)) return;

    // TerminationController has no resource destroy(); the old app used this
    // POST route (it also mails the fixed recipient list).
    router.post(`/terminations.delete/${t.id}`, {}, { preserveScroll: true });
}

function toggleTermination(t: TerminationRow) {
    router.post(`/terminations/${t.id}/toggle`, {}, { preserveScroll: true });
}

function actions(t: TerminationRow): RowAction[] {
    return [
        { icon: Pencil, label: 'Bearbeiten', href: `/terminations/${t.id}/edit` },
        {
            icon: t.is_active ? PowerOff : Power,
            label: t.is_active ? 'Deaktivieren' : 'Aktivieren',
            onClick: () => toggleTermination(t),
        },
        { icon: Trash2, label: 'Löschen', variant: 'destructive', onClick: () => deleteTermination(t) },
    ];
}

const COLUMNS: DataTableColumn<TerminationRow>[] = [
    { key: 'name' },
    { key: 'exit', searchable: false },
    { key: 'location' },
    { key: 'actions', sortable: false, searchable: false },
];
const rows = computed(() => props.terminations);
// pageSize 0 = all rows (the scroll area replaces paging).
const table = reactive(useDataTable(rows, COLUMNS, { pageSize: 0 }));

// Scroll area height = header + the first 5 rows, measured, so it fits exactly
// 5 rows whether names wrap to two lines or not.
const scrollEl = ref<HTMLElement | null>(null);
const maxHeight = ref<string | undefined>(undefined);

function measure() {
    const el = scrollEl.value;
    if (!el) return;
    const bodyRows = el.querySelectorAll<HTMLElement>('tbody tr');
    if (bodyRows.length <= MAX_VISIBLE_ROWS) {
        maxHeight.value = undefined;

        return;
    }
    let h = el.querySelector<HTMLElement>('thead')?.offsetHeight ?? 0;
    for (let i = 0; i < MAX_VISIBLE_ROWS; i++) h += bodyRows[i].offsetHeight;
    maxHeight.value = `${h}px`;
}

let observer: ResizeObserver | null = null;
onMounted(() => {
    measure();
    if (scrollEl.value && typeof ResizeObserver !== 'undefined') {
        observer = new ResizeObserver(() => measure());
        observer.observe(scrollEl.value);
    }
});
onBeforeUnmount(() => observer?.disconnect());
watch(
    () => table.pagedRows,
    () => nextTick(measure),
);
</script>

<template>
    <section class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
        <div class="flex items-center justify-between border-b p-4">
            <h3 class="font-semibold">Kündigungen</h3>
            <div class="flex items-center gap-2">
                <Link href="/terminations/history" class="text-muted-foreground hover:text-foreground text-sm">Verlauf</Link>
                <Link
                    href="/terminations/create"
                    class="border-primary text-primary hover:bg-primary hover:text-primary-foreground inline-flex h-8 items-center rounded-md border px-3 text-sm"
                    >+ Neu</Link
                >
            </div>
        </div>
        <div class="flex items-center justify-between gap-2 p-3">
            <input
                v-model="table.search"
                type="search"
                placeholder="Kündigung suchen..."
                class="border-input bg-background h-9 w-full max-w-64 rounded-md border px-3 text-sm"
            />
            <span class="text-muted-foreground shrink-0 text-xs">{{ table.total }} Einträge</span>
        </div>
        <div ref="scrollEl" class="overflow-auto" :style="maxHeight ? { maxHeight } : undefined">
            <table class="w-full text-sm">
                <thead class="text-muted-foreground bg-card sticky top-0 z-10 text-left">
                    <tr>
                        <TableHeadCell
                            label="Name"
                            sort-key="name"
                            :active-key="table.sortKey"
                            :direction="table.sortDir"
                            @sort="table.toggleSort('name')"
                        />
                        <TableHeadCell
                            label="Austritt"
                            sort-key="exit"
                            :active-key="table.sortKey"
                            :direction="table.sortDir"
                            @sort="table.toggleSort('exit')"
                        />
                        <TableHeadCell
                            label="Standort"
                            sort-key="location"
                            :active-key="table.sortKey"
                            :direction="table.sortDir"
                            @sort="table.toggleSort('location')"
                        />
                        <TableHeadCell label="Aktion" align="right" />
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="t in table.pagedRows" :key="t.id">
                        <td class="p-3">{{ t.name }}</td>
                        <td class="p-3 whitespace-nowrap" :class="exitColorClass(t)">{{ formatDate(t.exit) }}</td>
                        <td class="p-3">{{ t.location }}</td>
                        <td class="p-3">
                            <RowActions :actions="actions(t)" />
                        </td>
                    </tr>
                    <tr v-if="!table.pagedRows.length">
                        <td colspan="4" class="text-muted-foreground p-3 text-center">
                            {{ table.search ? 'Keine Kündigungen gefunden.' : 'Keine Kündigungen vorhanden.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>
