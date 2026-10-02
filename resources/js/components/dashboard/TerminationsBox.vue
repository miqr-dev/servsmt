<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, History, Pencil, Plus, Power, PowerOff, Trash2, UserMinus, X } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import { Button } from '@/components/ui/button';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';

/**
 * HR "Kündigungen" box on the unified Dashboard (pages/Home.vue), next to the
 * email forwardings, under the news box. Same look as the former /dashboard
 * table (search + sortable columns + row actions), but no paging: all rows
 * are in the table and the body scrolls.
 * Visible height: at least 5 rows, or as many rows as there are red entries
 * (exit today / passed) if that's more. "10 weitere anzeigen" at the bottom
 * grows the visible area by 10 rows each click; the rest keeps scrolling.
 *
 * Exit date colours (your rule, 2026-09-29):
 * - exit day today (or already passed) -> red
 * - within the next 7 days             -> orange
 * - more than a week away              -> green
 * - inactive entries                   -> grey
 *
 * Actions: Neu / Bearbeiten (dialog, POST/PUT /terminations), Aktiv/Inaktiv,
 * Entfernen (dialog with reason: Kündigung zurückgezogen / Vertrag verlängert /
 * Sonstiges -> POST /terminations/{id}/remove, shown in the Verlauf) and
 * Löschen (entered by mistake). Entfernen and Löschen both mail the fixed
 * HR recipient list and can be undone from the Verlauf.
 */

export type TerminationRow = {
    id: number;
    name: string;
    exit: string | null;
    is_active: boolean;
    location: string | null;
    occupation: string | null;
};

const props = defineProps<{
    terminations: TerminationRow[];
    /** Own page (/terminations): no row limit, no "weitere anzeigen", + Beschäftigung column. */
    fullPage?: boolean;
    /** Super_Admin Dashboard: only the due (red) entries are sent - say so and link the full list. */
    dueOnly?: boolean;
}>();

const MIN_VISIBLE_ROWS = 5;
// Own page: tighter rows so more fit on screen.
const cellPad = computed(() => (props.fullPage ? 'py-1.5' : 'py-3'));
const STEP = 10;
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

/** Days from today to the exit day (negative = passed), null without a date. */
function daysUntilExit(t: TerminationRow): number | null {
    const d = t.exit ? dayOf(t.exit) : null;
    if (!d) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return Math.round((d.getTime() - today.getTime()) / DAY_MS);
}

function isRed(t: TerminationRow): boolean {
    const days = daysUntilExit(t);

    return t.is_active && days !== null && days <= 0;
}

function exitColorClass(t: TerminationRow): string {
    if (!t.is_active) return 'font-semibold text-neutral-500';
    const days = daysUntilExit(t);
    if (days === null) return '';

    if (days <= 0) return 'font-semibold text-red-600 dark:text-red-400';
    if (days <= 7) return 'font-semibold text-orange-500 dark:text-orange-400';

    return 'font-semibold text-green-600 dark:text-green-400';
}

function deleteTermination(t: TerminationRow) {
    if (
        !confirm(
            `"${t.name}" wirklich löschen?\n\nNur für falsch erfasste Einträge. Wenn die Kündigung nicht mehr nötig ist, bitte "Entfernen" verwenden.`,
        )
    )
        return;

    // TerminationController has no resource destroy(); the old app used this
    // POST route (it also mails the fixed recipient list).
    router.post(`/terminations.delete/${t.id}`, {}, { preserveScroll: true });
}

function toggleTermination(t: TerminationRow) {
    if (t.is_active && !confirm(`"${t.name}" als inaktiv markieren? Die HR-Verteiler bekommen eine E-Mail.`)) return;
    router.post(`/terminations/${t.id}/toggle`, {}, { preserveScroll: true });
}

// --- Neu / Bearbeiten dialog ---
const editing = ref<TerminationRow | null>(null);
const formOpen = ref(false);
const form = useForm({
    name: '',
    location: '',
    occupation: '',
    exit: '',
    is_active: true,
});

const locationOptions = computed(() =>
    [...new Set(props.terminations.map((t) => t.location).filter((l): l is string => !!l))].sort((a, b) => a.localeCompare(b, 'de')),
);

function openCreate() {
    editing.value = null;
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(t: TerminationRow) {
    editing.value = t;
    form.clearErrors();
    form.name = t.name ?? '';
    form.location = t.location ?? '';
    form.occupation = t.occupation ?? '';
    form.exit = t.exit ? t.exit.slice(0, 10) : '';
    form.is_active = t.is_active;
    formOpen.value = true;
}

function submitForm() {
    const options = { preserveScroll: true, onSuccess: () => (formOpen.value = false) };
    if (editing.value) form.put(`/terminations/${editing.value.id}`, options);
    else form.post('/terminations', options);
}

// --- Entfernen dialog ---
const REMOVAL_REASONS = [
    { value: 'withdrawn', label: 'Kündigung zurückgezogen' },
    { value: 'renewed', label: 'Vertrag verlängert' },
    { value: 'other', label: 'Sonstiges' },
];
const removing = ref<TerminationRow | null>(null);
const removeForm = useForm({ reason: 'withdrawn', note: '' });

function openRemove(t: TerminationRow) {
    removeForm.reset();
    removeForm.clearErrors();
    removing.value = t;
}

function submitRemove() {
    if (!removing.value) return;
    removeForm.post(`/terminations/${removing.value.id}/remove`, {
        preserveScroll: true,
        onSuccess: () => (removing.value = null),
    });
}

function actions(t: TerminationRow): RowAction[] {
    return [
        { icon: Pencil, label: 'Bearbeiten', onClick: () => openEdit(t) },
        {
            icon: t.is_active ? PowerOff : Power,
            label: t.is_active ? 'Als inaktiv markieren' : 'Als aktiv markieren',
            onClick: () => toggleTermination(t),
        },
        { icon: UserMinus, label: 'Entfernen (nicht mehr nötig)', onClick: () => openRemove(t) },
        { icon: Trash2, label: 'Löschen', variant: 'destructive', onClick: () => deleteTermination(t) },
    ];
}

const COLUMNS: DataTableColumn<TerminationRow>[] = [
    { key: 'name' },
    { key: 'exit', searchable: false },
    { key: 'location' },
    { key: 'occupation' },
    { key: 'actions', sortable: false, searchable: false },
];
const rows = computed(() => props.terminations);
// pageSize 0 = all rows (the scroll area replaces paging).
const table = reactive(useDataTable(rows, COLUMNS, { pageSize: 0 }));

// Rows that fit in the scroll area: max(5, number of red entries), plus 10
// per "weitere anzeigen" click.
const redCount = computed(() => props.terminations.filter(isRed).length);
const baseRows = computed(() => Math.max(MIN_VISIBLE_ROWS, redCount.value));
const extraRows = ref(0);
const visibleRows = computed(() => baseRows.value + extraRows.value);
const hasMore = computed(() => !props.fullPage && table.pagedRows.length > visibleRows.value);

function showMore() {
    extraRows.value += STEP;
}

function showLess() {
    extraRows.value = 0;
    nextTick(() => scrollEl.value?.scrollTo({ top: 0 }));
}

// Scroll area height = header + the first `visibleRows` rows, measured, so it
// fits exactly that many rows whether names wrap to two lines or not.
const scrollEl = ref<HTMLElement | null>(null);
const maxHeight = ref<string | undefined>(undefined);

function measure() {
    const el = scrollEl.value;
    if (!el) return;
    if (props.fullPage) {
        maxHeight.value = undefined;

        return;
    }
    const bodyRows = el.querySelectorAll<HTMLElement>('tbody tr');
    if (bodyRows.length <= visibleRows.value) {
        maxHeight.value = undefined;

        return;
    }
    let h = el.querySelector<HTMLElement>('thead')?.offsetHeight ?? 0;
    for (let i = 0; i < visibleRows.value; i++) h += bodyRows[i].offsetHeight;
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
watch([() => table.pagedRows, visibleRows], () => nextTick(measure));
</script>

<template>
    <section class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
        <div class="flex items-center justify-between border-b p-4">
            <div class="min-w-0">
                <h3 class="font-semibold">Kündigungen</h3>
                <p v-if="dueOnly" class="text-muted-foreground text-xs">
                    Nur fällige ·
                    <Link href="/terminations" class="text-primary hover:underline">alle anzeigen</Link>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button as-child size="sm" variant="outline">
                    <Link href="/terminations/history">
                        <History class="size-4" />
                        Verlauf
                    </Link>
                </Button>
                <Button size="sm" @click="openCreate">
                    <Plus class="size-4" />
                    Neue Kündigung
                </Button>
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
                        <TableHeadCell
                            v-if="fullPage"
                            label="Beschäftigung"
                            sort-key="occupation"
                            :active-key="table.sortKey"
                            :direction="table.sortDir"
                            @sort="table.toggleSort('occupation')"
                        />
                        <TableHeadCell label="Aktion" align="right" />
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="t in table.pagedRows" :key="t.id">
                        <td class="px-3" :class="cellPad">{{ t.name }}</td>
                        <td class="px-3 whitespace-nowrap" :class="[exitColorClass(t), cellPad]">{{ formatDate(t.exit) }}</td>
                        <td class="px-3" :class="cellPad">{{ t.location }}</td>
                        <td v-if="fullPage" class="px-3" :class="cellPad">{{ t.occupation }}</td>
                        <td class="px-3" :class="cellPad">
                            <RowActions :actions="actions(t)" />
                        </td>
                    </tr>
                    <tr v-if="!table.pagedRows.length">
                        <td :colspan="fullPage ? 5 : 4" class="text-muted-foreground p-3 text-center">
                            {{ table.search ? 'Keine Kündigungen gefunden.' : 'Keine Kündigungen vorhanden.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="hasMore || extraRows > 0" class="flex items-center justify-center gap-1 border-t">
            <button
                v-if="hasMore"
                type="button"
                class="text-muted-foreground hover:text-foreground hover:bg-accent/60 flex flex-1 items-center justify-center gap-1 px-4 py-2 text-xs font-medium transition-colors"
                @click="showMore"
            >
                <ChevronDown class="size-4" />
                {{ Math.min(STEP, table.pagedRows.length - visibleRows) }} weitere anzeigen
            </button>
            <button
                v-if="extraRows > 0"
                type="button"
                class="text-muted-foreground hover:text-foreground hover:bg-accent/60 flex items-center justify-center gap-1 px-4 py-2 text-xs font-medium transition-colors"
                :class="hasMore ? 'border-l' : 'flex-1'"
                @click="showLess"
            >
                <ChevronUp class="size-4" />
                Weniger
            </button>
        </div>
        <!-- Neu / Bearbeiten -->
        <Teleport to="body">
            <div v-if="formOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="formOpen = false">
                <form class="bg-card text-card-foreground w-full max-w-md rounded-xl border shadow-lg" @submit.prevent="submitForm">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">{{ editing ? 'Kündigung bearbeiten' : 'Neue Kündigung' }}</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="formOpen = false">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 px-5 py-4">
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Name *</span>
                            <input v-model="form.name" type="text" class="border-input bg-background h-9 rounded-md border px-3" autocomplete="off" />
                            <span v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</span>
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium">Standort *</span>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    list="termination-locations"
                                    class="border-input bg-background h-9 rounded-md border px-3"
                                    autocomplete="off"
                                />
                                <datalist id="termination-locations">
                                    <option v-for="l in locationOptions" :key="l" :value="l" />
                                </datalist>
                                <span v-if="form.errors.location" class="text-destructive text-xs">{{ form.errors.location }}</span>
                            </label>
                            <label class="flex flex-col gap-1 text-sm">
                                <span class="font-medium">Austritt zum *</span>
                                <input v-model="form.exit" type="date" class="border-input bg-background h-9 rounded-md border px-3" />
                                <span v-if="form.errors.exit" class="text-destructive text-xs">{{ form.errors.exit }}</span>
                            </label>
                        </div>
                        <label class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Beschäftigung</span>
                            <input
                                v-model="form.occupation"
                                type="text"
                                class="border-input bg-background h-9 rounded-md border px-3"
                                autocomplete="off"
                            />
                            <span v-if="form.errors.occupation" class="text-destructive text-xs">{{ form.errors.occupation }}</span>
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            <input v-model="form.is_active" type="checkbox" class="size-4" />
                            Aktiv (erscheint farbig in der Liste)
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

            <!-- Entfernen -->
            <div v-if="removing" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="removing = null">
                <form class="bg-card text-card-foreground w-full max-w-md rounded-xl border shadow-lg" @submit.prevent="submitRemove">
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">Aus der Liste entfernen</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="removing = null">
                            <X class="size-4" />
                        </button>
                    </div>
                    <div class="flex flex-col gap-4 px-5 py-4 text-sm">
                        <p>
                            <span class="font-medium">{{ removing.name }}</span>
                            <span class="text-muted-foreground"> · {{ removing.location }} · Austritt {{ formatDate(removing.exit) }}</span>
                        </p>
                        <fieldset class="flex flex-col gap-2">
                            <legend class="mb-1 font-medium">Grund</legend>
                            <label v-for="r in REMOVAL_REASONS" :key="r.value" class="flex items-center gap-2">
                                <input v-model="removeForm.reason" type="radio" :value="r.value" class="size-4" />
                                {{ r.label }}
                            </label>
                            <span v-if="removeForm.errors.reason" class="text-destructive text-xs">{{ removeForm.errors.reason }}</span>
                        </fieldset>
                        <label class="flex flex-col gap-1">
                            <span class="font-medium">Bemerkung{{ removeForm.reason === 'other' ? ' *' : ' (optional)' }}</span>
                            <textarea
                                v-model="removeForm.note"
                                rows="3"
                                class="border-input bg-background rounded-md border px-3 py-2"
                                :placeholder="removeForm.reason === 'renewed' ? 'z. B. verlängert bis 31.12.2027' : ''"
                            />
                            <span v-if="removeForm.errors.note" class="text-destructive text-xs">{{ removeForm.errors.note }}</span>
                        </label>
                        <p class="text-muted-foreground text-xs">
                            Der Eintrag verschwindet aus der Liste, bleibt mit Grund im Verlauf und kann dort wiederhergestellt werden. Die
                            HR-Verteiler bekommen eine E-Mail.
                        </p>
                    </div>
                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="removing = null">Abbrechen</button>
                        <button
                            type="submit"
                            :disabled="removeForm.processing"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-4 text-sm disabled:opacity-60"
                        >
                            Entfernen
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>
    </section>
</template>
