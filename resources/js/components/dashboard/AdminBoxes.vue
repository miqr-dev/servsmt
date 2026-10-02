<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';

/**
 * Super_Admin boxes of the unified Dashboard (pages/Home.vue), moved here
 * 2026-09-29 from the former /dashboard page (pages/Dashboard.vue, removed).
 * (HR Kündigungen: components/dashboard/TerminationsBox.vue.)
 * Every box renders only when its prop is sent - DashboardController decides:
 * (Lizenzen: components/dashboard/LicensesBox.vue, 2026-10-02.)
 * - active/history email forwardings: Super_Admin only (as in the old app)
 * Tables use the shared useDataTable standard (sort, search, paging).
 */

type ForwardingUser = { name: string; vorname: string | null } | null;

type ForwardingTicketRow = {
    id: number;
    forward_from: string | null;
    forward_on: string | null;
    forward_required_at: string | null;
    forward_to_at: string | null;
    forward_removed_at: string | null;
    done_by: string | null;
    created_at: string | null;
    forwardFromUser: ForwardingUser;
    forwardOnUser: ForwardingUser;
    forwardRemovedByUser: ForwardingUser;
    subUser: ForwardingUser;
    user: ForwardingUser;
};

const props = defineProps<{
    activeEmailForwardingTickets?: ForwardingTicketRow[];
    historyEmailForwardingTickets?: ForwardingTicketRow[];
}>();

const showForwardingHistory = ref(false);

function formatDate(value: string | null): string {
    if (!value) return '';

    return new Date(value).toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function personLabel(user: ForwardingUser, fallback: string | null): string {
    if (user) {
        return [user.name, user.vorname].filter(Boolean).join(', ');
    }

    return fallback ?? '-';
}

function isOverdue(ticket: ForwardingTicketRow): boolean {
    return !!ticket.forward_to_at && new Date(ticket.forward_to_at) < new Date();
}

function markForwardingRemoved(ticket: ForwardingTicketRow) {
    router.post(`/ticket/${ticket.id}/forwarding-removed`, {}, { preserveScroll: true });
}

// --- Active email forwarding table ---
const ACTIVE_FORWARDING_COLUMNS: DataTableColumn<ForwardingTicketRow>[] = [
    { key: 'from', value: (t) => personLabel(t.forwardFromUser, t.forward_from) },
    { key: 'on', value: (t) => personLabel(t.forwardOnUser, t.forward_on) },
    { key: 'forward_required_at', searchable: false },
    { key: 'forward_to_at', searchable: false },
    { key: 'status', value: (t) => (isOverdue(t) ? 'Überfällig' : 'Aktiv') },
    { key: 'submitter', value: (t) => personLabel(t.subUser, null) },
    { key: 'actions', sortable: false, searchable: false },
];
const activeForwarding = computed(() => props.activeEmailForwardingTickets ?? []);
const activeForwardingTable = reactive(useDataTable(activeForwarding, ACTIVE_FORWARDING_COLUMNS));

// --- Forwarding history table ---
const HISTORY_FORWARDING_COLUMNS: DataTableColumn<ForwardingTicketRow>[] = [
    { key: 'from', value: (t) => personLabel(t.forwardFromUser, t.forward_from) },
    { key: 'on', value: (t) => personLabel(t.forwardOnUser, t.forward_on) },
    { key: 'forward_required_at', searchable: false },
    { key: 'forward_to_at', searchable: false },
    { key: 'submitter', value: (t) => personLabel(t.subUser, null) },
    { key: 'forward_removed_at', searchable: false },
    { key: 'removedBy', value: (t) => personLabel(t.forwardRemovedByUser, null) },
];
const historyForwarding = computed(() => props.historyEmailForwardingTickets ?? []);
const historyForwardingTable = reactive(useDataTable(historyForwarding, HISTORY_FORWARDING_COLUMNS));
</script>

<template>
    <div class="flex flex-col gap-6">
        <!-- Email forwarding - Super_Admin only, matches the old @if(hasRole('Super_Admin')) -->
        <template v-if="props.activeEmailForwardingTickets">
            <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="flex items-center justify-between border-b p-4">
                    <h3 class="font-semibold">E-Mail-Weiterleitungen (Aktiv)</h3>
                    <button
                        type="button"
                        class="border-border hover:bg-accent inline-flex h-8 items-center rounded-md border px-3 text-sm"
                        @click="showForwardingHistory = !showForwardingHistory"
                    >
                        {{ showForwardingHistory ? 'Verlauf ausblenden' : 'Verlauf anzeigen' }}
                    </button>
                </div>
                <div class="p-3">
                    <TableToolbar
                        v-model:search="activeForwardingTable.search"
                        v-model:page-size="activeForwardingTable.pageSize"
                        search-placeholder="Weiterleitung suchen..."
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell
                                    label="Von"
                                    sort-key="from"
                                    :active-key="activeForwardingTable.sortKey"
                                    :direction="activeForwardingTable.sortDir"
                                    @sort="activeForwardingTable.toggleSort('from')"
                                />
                                <TableHeadCell
                                    label="An"
                                    sort-key="on"
                                    :active-key="activeForwardingTable.sortKey"
                                    :direction="activeForwardingTable.sortDir"
                                    @sort="activeForwardingTable.toggleSort('on')"
                                />
                                <TableHeadCell
                                    label="Von Datum"
                                    sort-key="forward_required_at"
                                    :active-key="activeForwardingTable.sortKey"
                                    :direction="activeForwardingTable.sortDir"
                                    @sort="activeForwardingTable.toggleSort('forward_required_at')"
                                />
                                <TableHeadCell
                                    label="Bis Datum"
                                    sort-key="forward_to_at"
                                    :active-key="activeForwardingTable.sortKey"
                                    :direction="activeForwardingTable.sortDir"
                                    @sort="activeForwardingTable.toggleSort('forward_to_at')"
                                />
                                <TableHeadCell
                                    label="Status"
                                    sort-key="status"
                                    :active-key="activeForwardingTable.sortKey"
                                    :direction="activeForwardingTable.sortDir"
                                    @sort="activeForwardingTable.toggleSort('status')"
                                />
                                <TableHeadCell
                                    label="Erstellt von"
                                    sort-key="submitter"
                                    :active-key="activeForwardingTable.sortKey"
                                    :direction="activeForwardingTable.sortDir"
                                    @sort="activeForwardingTable.toggleSort('submitter')"
                                />
                                <TableHeadCell label="Aktion" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in activeForwardingTable.pagedRows" :key="ticket.id">
                                <td class="p-3">
                                    {{ personLabel(ticket.forwardFromUser, ticket.forward_from) }}
                                </td>
                                <td class="p-3">
                                    {{ personLabel(ticket.forwardOnUser, ticket.forward_on) }}
                                </td>
                                <td class="p-3">
                                    {{ formatDate(ticket.forward_required_at) }}
                                </td>
                                <td class="p-3">
                                    {{ formatDate(ticket.forward_to_at) }}
                                </td>
                                <td class="p-3">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            isOverdue(ticket)
                                                ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                        "
                                    >
                                        {{ isOverdue(ticket) ? 'Überfällig' : 'Aktiv' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    {{ personLabel(ticket.subUser, null) }}
                                </td>
                                <td class="p-3">
                                    <button
                                        type="button"
                                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-8 items-center rounded-md px-3 text-sm"
                                        @click="markForwardingRemoved(ticket)"
                                    >
                                        Als entfernt markieren
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!activeForwardingTable.pagedRows.length">
                                <td colspan="7" class="text-muted-foreground p-3 text-center">
                                    {{ activeForwardingTable.search ? 'Keine Weiterleitungen gefunden.' : 'Keine aktiven Weiterleitungen.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :page="activeForwardingTable.page"
                    :page-count="activeForwardingTable.pageCount"
                    :range-from="activeForwardingTable.rangeFrom"
                    :range-to="activeForwardingTable.rangeTo"
                    :total="activeForwardingTable.total"
                    item-label="Weiterleitungen"
                    @update:page="activeForwardingTable.page = $event"
                />
            </div>

            <div v-if="showForwardingHistory" class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h3 class="font-semibold">E-Mail-Weiterleitungen (Verlauf)</h3>
                </div>
                <div class="p-3">
                    <TableToolbar
                        v-model:search="historyForwardingTable.search"
                        v-model:page-size="historyForwardingTable.pageSize"
                        search-placeholder="Verlauf suchen..."
                    />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell
                                    label="Von"
                                    sort-key="from"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('from')"
                                />
                                <TableHeadCell
                                    label="An"
                                    sort-key="on"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('on')"
                                />
                                <TableHeadCell
                                    label="Von Datum"
                                    sort-key="forward_required_at"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('forward_required_at')"
                                />
                                <TableHeadCell
                                    label="Bis Datum"
                                    sort-key="forward_to_at"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('forward_to_at')"
                                />
                                <TableHeadCell
                                    label="Erstellt von"
                                    sort-key="submitter"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('submitter')"
                                />
                                <TableHeadCell
                                    label="Entfernt am"
                                    sort-key="forward_removed_at"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('forward_removed_at')"
                                />
                                <TableHeadCell
                                    label="Entfernt von"
                                    sort-key="removedBy"
                                    :active-key="historyForwardingTable.sortKey"
                                    :direction="historyForwardingTable.sortDir"
                                    @sort="historyForwardingTable.toggleSort('removedBy')"
                                />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in historyForwardingTable.pagedRows" :key="ticket.id">
                                <td class="p-3">
                                    {{ personLabel(ticket.forwardFromUser, ticket.forward_from) }}
                                </td>
                                <td class="p-3">
                                    {{ personLabel(ticket.forwardOnUser, ticket.forward_on) }}
                                </td>
                                <td class="p-3">
                                    {{ formatDate(ticket.forward_required_at) }}
                                </td>
                                <td class="p-3">
                                    {{ formatDate(ticket.forward_to_at) }}
                                </td>
                                <td class="p-3">
                                    {{ personLabel(ticket.subUser, null) }}
                                </td>
                                <td class="p-3">
                                    {{ formatDate(ticket.forward_removed_at) }}
                                </td>
                                <td class="p-3">
                                    {{ personLabel(ticket.forwardRemovedByUser, null) }}
                                </td>
                            </tr>
                            <tr v-if="!historyForwardingTable.pagedRows.length">
                                <td colspan="7" class="text-muted-foreground p-3 text-center">
                                    {{ historyForwardingTable.search ? 'Nichts gefunden.' : 'Kein Verlauf vorhanden.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :page="historyForwardingTable.page"
                    :page-count="historyForwardingTable.pageCount"
                    :range-from="historyForwardingTable.rangeFrom"
                    :range-to="historyForwardingTable.rangeTo"
                    :total="historyForwardingTable.total"
                    item-label="Einträgen"
                    @update:page="historyForwardingTable.page = $event"
                />
            </div>
        </template>
    </div>
</template>
