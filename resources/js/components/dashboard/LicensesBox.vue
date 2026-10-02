<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, X } from '@lucide/vue';
import { computed, reactive, ref } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import { Button } from '@/components/ui/button';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';

/**
 * Lizenzen (Super_Admin only) - same look and behaviour as the Kündigungen
 * box (TerminationsBox.vue):
 * - Dashboard: only licences expiring within 30 days (or already expired) are
 *   sent by DashboardController; the box is hidden when there are none.
 * - Own page /licenses (sidebar "Lizenzen"): all licences, `fullPage`.
 * Neu / Bearbeiten in a dialog (POST/PUT /licenses), Löschen with confirm.
 *
 * "Gültig" colours: ≤ 7 days (or expired) red, ≤ 30 days orange, later green.
 */

export type LicenseRow = {
    id: number;
    name: string;
    where: string | null;
    comment: string | null;
    valid: string | null;
    version: string | null;
};

const props = defineProps<{
    licenses: LicenseRow[];
    /** Own page: all licences, compact rows, no height limit. */
    fullPage?: boolean;
}>();

const DAY_MS = 24 * 60 * 60 * 1000;

function dayOf(value: string): Date | null {
    const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(value);

    return m ? new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3])) : null;
}

function formatDate(value: string | null): string {
    const d = value ? dayOf(value) : null;

    return d ? d.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '–';
}

function daysLeft(l: LicenseRow): number | null {
    const d = l.valid ? dayOf(l.valid) : null;
    if (!d) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return Math.round((d.getTime() - today.getTime()) / DAY_MS);
}

function validClass(l: LicenseRow): string {
    const days = daysLeft(l);
    if (days === null) return 'text-muted-foreground';
    if (days <= 7) return 'font-semibold text-red-600 dark:text-red-400';
    if (days <= 30) return 'font-semibold text-orange-500 dark:text-orange-400';

    return 'font-semibold text-green-600 dark:text-green-400';
}

function validHint(l: LicenseRow): string {
    const days = daysLeft(l);
    if (days === null) return '';
    if (days < 0) return `abgelaufen seit ${-days} ${-days === 1 ? 'Tag' : 'Tagen'}`;
    if (days === 0) return 'läuft heute ab';

    return `noch ${days} ${days === 1 ? 'Tag' : 'Tage'}`;
}

const cellPad = computed(() => (props.fullPage ? 'py-1.5' : 'py-3'));

// --- table (search + sort, no paging) ---
const COLUMNS: DataTableColumn<LicenseRow>[] = [
    { key: 'name' },
    { key: 'where' },
    { key: 'comment' },
    { key: 'valid', searchable: false },
    { key: 'version' },
    { key: 'actions', sortable: false, searchable: false },
];
const rows = computed(() => props.licenses);
const table = reactive(useDataTable(rows, COLUMNS, { pageSize: 0 }));

// --- Neu / Bearbeiten dialog ---
const editing = ref<LicenseRow | null>(null);
const formOpen = ref(false);
const form = useForm({ name: '', where: '', version: '', valid: '', comment: '' });

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(l: LicenseRow) {
    editing.value = l;
    form.clearErrors();
    form.name = l.name ?? '';
    form.where = l.where ?? '';
    form.version = l.version ?? '';
    form.valid = l.valid ? l.valid.slice(0, 10) : '';
    form.comment = l.comment ?? '';
    formOpen.value = true;
}

function submitForm() {
    const options = { preserveScroll: true, onSuccess: () => (formOpen.value = false) };
    if (editing.value) form.put(`/licenses/${editing.value.id}`, options);
    else form.post('/licenses', options);
}

function deleteLicense(l: LicenseRow) {
    if (!confirm(`"${l.name}" wirklich löschen?`)) return;
    router.delete(`/licenses/${l.id}`, { preserveScroll: true });
}

function actions(l: LicenseRow): RowAction[] {
    return [
        { icon: Pencil, label: 'Bearbeiten', onClick: () => openEdit(l) },
        { icon: Trash2, label: 'Löschen', variant: 'destructive', onClick: () => deleteLicense(l) },
    ];
}
</script>

<template>
    <section class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
        <div class="flex items-center justify-between border-b p-4">
            <div class="min-w-0">
                <h3 class="font-semibold">Lizenzen</h3>
                <p v-if="!fullPage" class="text-muted-foreground text-xs">
                    Laufen in 30 Tagen ab ·
                    <Link href="/licenses" class="text-primary hover:underline">alle anzeigen</Link>
                </p>
            </div>
            <Button size="sm" @click="openCreate">
                <Plus class="size-4" />
                Neue Lizenz
            </Button>
        </div>
        <div class="flex items-center justify-between gap-2 p-3">
            <input
                v-model="table.search"
                type="search"
                placeholder="Lizenz suchen..."
                class="border-input bg-background h-9 w-full max-w-64 rounded-md border px-3 text-sm"
            />
            <span class="text-muted-foreground shrink-0 text-xs">{{ table.total }} Einträge</span>
        </div>
        <div class="overflow-auto" :class="fullPage ? '' : 'max-h-[26rem]'">
            <table class="w-full text-sm">
                <thead class="text-muted-foreground bg-card sticky top-0 z-10 text-left">
                    <tr>
                        <TableHeadCell label="Lizenzname" sort-key="name" :active-key="table.sortKey" :direction="table.sortDir" @sort="table.toggleSort('name')" />
                        <TableHeadCell label="Wo" sort-key="where" :active-key="table.sortKey" :direction="table.sortDir" @sort="table.toggleSort('where')" />
                        <TableHeadCell
                            label="Bemerkung"
                            sort-key="comment"
                            :active-key="table.sortKey"
                            :direction="table.sortDir"
                            @sort="table.toggleSort('comment')"
                        />
                        <TableHeadCell label="Gültig" sort-key="valid" :active-key="table.sortKey" :direction="table.sortDir" @sort="table.toggleSort('valid')" />
                        <TableHeadCell
                            label="Version"
                            sort-key="version"
                            :active-key="table.sortKey"
                            :direction="table.sortDir"
                            @sort="table.toggleSort('version')"
                        />
                        <TableHeadCell label="Aktion" align="right" />
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="l in table.pagedRows" :key="l.id">
                        <td class="px-3" :class="cellPad">{{ l.name }}</td>
                        <td class="px-3" :class="cellPad">{{ l.where }}</td>
                        <td class="px-3 break-words whitespace-pre-line" :class="cellPad">{{ l.comment }}</td>
                        <td class="px-3 whitespace-nowrap" :class="cellPad">
                            <span :class="validClass(l)">{{ formatDate(l.valid) }}</span>
                            <span v-if="validHint(l)" class="text-muted-foreground block text-xs">{{ validHint(l) }}</span>
                        </td>
                        <td class="px-3" :class="cellPad">{{ l.version }}</td>
                        <td class="px-3" :class="cellPad">
                            <RowActions :actions="actions(l)" />
                        </td>
                    </tr>
                    <tr v-if="!table.pagedRows.length">
                        <td colspan="6" class="text-muted-foreground p-3 text-center">
                            {{ table.search ? 'Keine Lizenzen gefunden.' : 'Keine Lizenzen vorhanden.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Neu / Bearbeiten -->
        <Teleport to="body">
            <div v-if="formOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="formOpen = false">
                <form class="bg-card text-card-foreground w-full max-w-md rounded-xl border shadow-lg" @submit.prevent="submitForm">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">{{ editing ? 'Lizenz bearbeiten' : 'Neue Lizenz' }}</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="formOpen = false">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 px-5 py-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Lizenzname *</span>
                            <input v-model="form.name" type="text" class="border-input bg-background h-9 rounded-md border px-3" autocomplete="off" />
                            <span v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</span>
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium">Wo</span>
                                <input v-model="form.where" type="text" class="border-input bg-background h-9 rounded-md border px-3" autocomplete="off" />
                                <span v-if="form.errors.where" class="text-destructive text-xs">{{ form.errors.where }}</span>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium">Gültig bis</span>
                                <input v-model="form.valid" type="date" class="border-input bg-background h-9 rounded-md border px-3" />
                                <span v-if="form.errors.valid" class="text-destructive text-xs">{{ form.errors.valid }}</span>
                            </label>
                        </div>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Version</span>
                            <input v-model="form.version" type="text" class="border-input bg-background h-9 rounded-md border px-3" autocomplete="off" />
                            <span v-if="form.errors.version" class="text-destructive text-xs">{{ form.errors.version }}</span>
                        </label>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Bemerkung</span>
                            <textarea v-model="form.comment" rows="4" class="border-input bg-background rounded-md border px-3 py-2" />
                            <span v-if="form.errors.comment" class="text-destructive text-xs">{{ form.errors.comment }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="formOpen = false">Abbrechen</button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-4 text-sm disabled:opacity-60"
                        >
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>
    </section>
</template>
