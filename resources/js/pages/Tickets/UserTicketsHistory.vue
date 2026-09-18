<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, h, reactive } from 'vue';
import { Inbox, CheckCircle2 } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import TicketFolderNav from '@/components/tickets/TicketFolderNav.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/userticketsdone.blade.php
 * (TicketController@userticketshistory, route /userticketshistory) - the
 * "Erledigte" half of the unified "my tickets" dashboard, paired with
 * Tickets/UserTickets.vue. Two sections only here (Ticket + Korso) - unlike
 * the "Offene" page, Handwerk history is its own separate, already-converted
 * page (Handwerk/History.vue, route /usertHandwerkicketshistory) and was
 * never part of this Blade file - not an omission, matches the old page
 * exactly (confirmed by reading userticketsdone.blade.php in full - no
 * Handwerk section exists in it at all).
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts) - each of the 2 sections is its own
 * independent table/search/pagination instance.
 */

type NamedUser = { username: string } | null;

type TicketRow = {
    id: number;
    problem_type: string;
    subUser: NamedUser;
    done_by: string | null;
    deleted_at: string | null;
    updated_at: string;
    user: NamedUser;
    notizen: string | null;
    [key: string]: unknown;
};

type KorsoTicketRow = {
    id: number;
    problem_type: string;
    // Fixed while converting: the old Blade read `$myTicket->submitter`
    // here, but Korso has no `submitter()` relation (only `subUser()`,
    // belongsTo User via the `submitter` foreign key column) - `->username`
    // on the raw integer column silently resolved to nothing. Using the
    // relation that's actually eager-loaded (and actually named "creator")
    // instead, same class of fix as UserTickets.vue's assignedUser one.
    subUser: NamedUser;
    doneByUser: NamedUser;
    assignedUser: NamedUser;
    deleted_at: string | null;
    updated_at: string;
    notizen: string | null;
};

const props = defineProps<{
    oldTickets: TicketRow[];
    myTicketsCount: number;
    ticketsdone: number;
    oldKorsoTickets: KorsoTicketRow[];
    myDoneCount: number;
    assignedCount: number;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Meine Tickets', href: '/usertickets' },
            { title: 'Erledigte', href: '#' },
        ];
        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('Super_Admin'));
const canSeeKorso = computed(() => roles.value.includes('Verwaltung'));

const ticketFolderLinks = computed(() => [
    { label: 'Offene', href: '/usertickets', count: props.myTicketsCount, icon: Inbox },
    { label: 'Erledigte', href: '/userticketshistory', count: props.ticketsdone, icon: CheckCircle2, active: true },
]);

const korsoFolderLinks = computed(() => [
    { label: 'Offene', href: '/usertickets', count: props.assignedCount, icon: Inbox },
    { label: 'Erledigte', href: '/userticketshistory', count: props.myDoneCount, icon: CheckCircle2, active: true },
]);

function formatDate(value: string | null): string {
    return value ? new Date(value).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';
}

function timeAgo(value: string): string {
    const diffMin = Math.round((Date.now() - new Date(value).getTime()) / 60000);
    if (diffMin < 60) return `vor ${Math.max(diffMin, 0)} Min.`;
    const diffHour = Math.round(diffMin / 60);
    if (diffHour < 24) return `vor ${diffHour} Std.`;
    return `vor ${Math.round(diffHour / 24)} Tagen`;
}

const TICKET_COLUMNS: DataTableColumn<TicketRow>[] = [
    { key: 'problem_type' },
    { key: 'doneBy', value: (t) => (isSuperAdmin.value ? t.subUser?.username : t.done_by) ?? '—' },
    { key: 'deleted_at', searchable: false },
    { key: 'updated_at', searchable: false },
    { key: 'assignee', value: (t) => t.user?.username ?? '—' },
];
const oldTickets = computed(() => props.oldTickets);
const ticketTable = reactive(useDataTable(oldTickets, TICKET_COLUMNS));

const KORSO_COLUMNS: DataTableColumn<KorsoTicketRow>[] = [
    { key: 'problem_type' },
    { key: 'doneBy', value: (t) => (isSuperAdmin.value ? t.subUser?.username : t.doneByUser?.username) ?? '—' },
    { key: 'deleted_at', searchable: false },
    { key: 'updated_at', searchable: false },
    { key: 'assignedUser', value: (t) => t.assignedUser?.username ?? 'nicht zugewiesen' },
];
const oldKorsoTickets = computed(() => props.oldKorsoTickets);
const korsoTable = reactive(useDataTable(oldKorsoTickets, KORSO_COLUMNS));
</script>

<template>
    <Head title="Erledigte Tickets" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <!-- Ticket section -->
        <div class="flex flex-col gap-4 lg:flex-row">
            <TicketFolderNav title="Ordner" :links="ticketFolderLinks" />

            <div class="bg-card text-card-foreground flex-1 rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h3 class="font-semibold">Anzahl erledigte Tickets: {{ ticketsdone }}</h3>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="ticketTable.search" v-model:page-size="ticketTable.pageSize" search-placeholder="Suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('problem_type')" />
                                <TableHeadCell :label="isSuperAdmin ? 'Erstellt von' : 'Erledigt von'" sort-key="doneBy" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('doneBy')" />
                                <TableHeadCell label="Erledigt am" sort-key="deleted_at" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('deleted_at')" />
                                <TableHeadCell label="Seit" sort-key="updated_at" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('updated_at')" />
                                <TableHeadCell label="Zugewiesen an" sort-key="assignee" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('assignee')" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in ticketTable.pagedRows" :key="ticket.id">
                                <td class="p-3">
                                    <Link :href="`/ticket/${ticket.id}`" class="text-primary font-semibold hover:underline">
                                        {{ ticket.problem_type }}
                                    </Link>
                                </td>
                                <td class="p-3 font-semibold">{{ (isSuperAdmin ? ticket.subUser?.username : ticket.done_by) ?? '—' }}</td>
                                <td class="p-3 font-semibold">{{ formatDate(ticket.deleted_at) }}</td>
                                <td class="p-3">{{ timeAgo(ticket.updated_at) }}</td>
                                <td class="p-3 font-semibold">{{ ticket.user?.username ?? '—' }}</td>
                            </tr>
                            <tr v-if="!ticketTable.pagedRows.length">
                                <td colspan="5" class="text-muted-foreground p-6 text-center">
                                    {{ ticketTable.search ? 'Nichts gefunden.' : 'Keine Tickets vorhanden.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <TablePagination
                    :page="ticketTable.page"
                    :page-count="ticketTable.pageCount"
                    :range-from="ticketTable.rangeFrom"
                    :range-to="ticketTable.rangeTo"
                    :total="ticketTable.total"
                    item-label="Tickets"
                    @update:page="ticketTable.page = $event"
                />
            </div>
        </div>

        <!-- Korso section -->
        <div v-if="canSeeKorso" class="flex flex-col gap-4 lg:flex-row">
            <TicketFolderNav title="Ordner" :links="korsoFolderLinks" />

            <div class="bg-card text-card-foreground flex-1 rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h3 class="font-semibold">Anzahl erledigte Tickets: {{ myDoneCount }}</h3>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="korsoTable.search" v-model:page-size="korsoTable.pageSize" search-placeholder="Suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('problem_type')" />
                                <TableHeadCell :label="isSuperAdmin ? 'Erstellt von' : 'Erledigt von'" sort-key="doneBy" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('doneBy')" />
                                <TableHeadCell label="Erledigt am" sort-key="deleted_at" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('deleted_at')" />
                                <TableHeadCell label="Seit" sort-key="updated_at" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('updated_at')" />
                                <TableHeadCell label="Zugewiesen an" sort-key="assignedUser" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('assignedUser')" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in korsoTable.pagedRows" :key="ticket.id">
                                <td class="p-3">
                                    <Link :href="`/korso/${ticket.id}`" class="text-primary font-semibold hover:underline">
                                        {{ ticket.problem_type }}
                                    </Link>
                                </td>
                                <td class="p-3 font-semibold">{{ (isSuperAdmin ? ticket.subUser?.username : ticket.doneByUser?.username) ?? '—' }}</td>
                                <td class="p-3 font-semibold">{{ formatDate(ticket.deleted_at) }}</td>
                                <td class="p-3">{{ timeAgo(ticket.updated_at) }}</td>
                                <td class="p-3 font-semibold">{{ ticket.assignedUser?.username ?? 'nicht zugewiesen' }}</td>
                            </tr>
                            <tr v-if="!korsoTable.pagedRows.length">
                                <td colspan="5" class="text-muted-foreground p-6 text-center">
                                    {{ korsoTable.search ? 'Nichts gefunden.' : 'Keine Tickets vorhanden.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <TablePagination
                    :page="korsoTable.page"
                    :page-count="korsoTable.pageCount"
                    :range-from="korsoTable.rangeFrom"
                    :range-to="korsoTable.rangeTo"
                    :total="korsoTable.total"
                    item-label="Tickets"
                    @update:page="korsoTable.page = $event"
                />
            </div>
        </div>
    </div>
</template>
