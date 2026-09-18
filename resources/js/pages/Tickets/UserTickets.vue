<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { computed, h, reactive, ref } from 'vue';
import { Inbox, CheckCircle2, FileDown, Trash2 } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import TicketFolderNav from '@/components/tickets/TicketFolderNav.vue';
import RowActions from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import type { Auth, BreadcrumbItem } from '@/types';
import { ticketStatusMeta, ticketPriorityLabel, ticketPriorityBadgeClass, korsoStatusBadgeClass } from '@/lib/ticketStatus';

/**
 * Converted from resources/views/tickets/usertickets.blade.php
 * (TicketController@usertickets, route /usertickets/{city?}) - the "Offene"
 * half of the unified "my tickets" dashboard. Not a Tickets-only page: it
 * assembles open tickets from all three ticket systems (Ticket/Handwerk/
 * Korso) into up to 3 stacked sections, each gated by role exactly like the
 * old Blade page:
 * - Ticket section: always shown.
 * - Handwerk section: Super_Admin/handwerk/handwerk_admin/Verwaltung only.
 * - Korso section: Verwaltung only.
 *
 * This is the page every "Offene"/"Meine Tickets" link in the app already
 * points to (AppSidebar.vue's persistent "Meine Tickets" item, Handwerk's
 * History.vue mini-nav) - those links were previously falling back to a full
 * Blade page load since this route didn't return an Inertia response yet.
 * Converting it is what makes them real SPA transitions.
 *
 * DataTables search/paging dropped, matching the plain-table approach used
 * everywhere else in this migration.
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts) - each of the 3 sections is its own
 * independent table/search/pagination instance.
 */

type NamedUser = { username: string } | null;
type InvItem = { gname: string | null } | null;

type TicketRow = {
    id: number;
    ticket_status_id: number | null;
    priority_id: number;
    problem_type: string;
    notizen: string | null;
    created_at: string;
    subUser: NamedUser;
    user: NamedUser;
    invitem: InvItem;
    [key: string]: unknown;
};

type HandwerkLocation = { address: string | null } | null;
type HandwerkRoom = { rname: string | null; altrname: string | null } | null;

type HandwerkRow = {
    id: number;
    problem_type: string;
    submitter_name: string | null;
    submitter_standort: string | null;
    priority: number;
    created_at: string;
    notizen: string | null;
    location: HandwerkLocation;
    room: HandwerkRoom;
};

type KorsoTicketStatus = { id: number; name: string } | null;

type KorsoTicketRow = {
    id: number;
    submitter_name: string | null;
    // Fixed while converting: the old Blade printed the raw `assignedTo`
    // foreign-key integer here instead of resolving it through the already
    // eager-loaded `assignedUser` relation (KorsoController@usertickets
    // loads it, but the view never read `->assignedUser->username`) - same
    // class of bug as the "Zugewiesen an" fix on Handwerk/Show.vue earlier
    // in this migration. Using the relation now.
    assignedUser: { username: string } | null;
    problem_type: string;
    priority: number;
    ticket_status: KorsoTicketStatus;
    created_at: string;
};

const props = defineProps<{
    myTickets: TicketRow[];
    myTicketsCount: number;
    ticketsdone: number;
    myHandwerkTickets: HandwerkRow[];
    handwerkticketsdone: number;
    myhandwerkTicketsCount: number;
    myhandwerkTicketsCountCity: number;
    userCities: Record<number, string[]>;
    cityHandwerkCounts: Record<string, number>;
    korsoTicket: KorsoTicketRow[];
    assignedCount: number;
    myDoneCount: number;
    city: string | null;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Meine Tickets', href: '/usertickets' }];
        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const currentUser = computed(() => page.props.auth.user);
const roles = computed(() => currentUser.value?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('Super_Admin'));
const canSeeHandwerk = computed(
    () =>
        isSuperAdmin.value ||
        roles.value.includes('handwerk') ||
        roles.value.includes('handwerk_admin') ||
        roles.value.includes('Verwaltung'),
);
const canSeeKorso = computed(() => roles.value.includes('Verwaltung'));
const canDownloadHandwerkPdf = computed(
    () => isSuperAdmin.value || roles.value.includes('handwerk_admin') || roles.value.includes('Sekretariat'),
);

// --- Ticket folder nav ---

const ticketFolderLinks = computed(() => [
    { label: 'Offene', href: '/usertickets', count: props.myTicketsCount, icon: Inbox, active: true },
    { label: 'Erledigte', href: '/userticketshistory', count: props.ticketsdone, icon: CheckCircle2 },
]);

function formatDate(value: string): string {
    const diffMin = Math.round((Date.now() - new Date(value).getTime()) / 60000);
    if (diffMin < 60) return `vor ${Math.max(diffMin, 0)} Min.`;
    const diffHour = Math.round(diffMin / 60);
    if (diffHour < 24) return `vor ${diffHour} Std.`;
    return `vor ${Math.round(diffHour / 24)} Tagen`;
}

// --- Ticket (IT-Helpdesk) table ---

const myTickets = ref<TicketRow[]>([...props.myTickets]);

async function deleteTicket(ticket: TicketRow) {
    if (!confirm('Sind Sie sicher? Sie können dies nicht rückgängig machen!')) return;

    try {
        const { data } = await axios.post(`/ticket.force_delete/${ticket.id}`);
        if (data === 'true') {
            myTickets.value = myTickets.value.filter((t) => t.id !== ticket.id);
        } else {
            toast.error('Das Ticket konnte nicht gelöscht werden.');
        }
    } catch {
        toast.error('Fehler beim Löschen des Tickets.');
    }
}

function ticketRowActions(ticket: TicketRow) {
    return [{ icon: Trash2, label: 'Löschen', variant: 'destructive' as const, onClick: () => deleteTicket(ticket) }];
}

const TICKET_COLUMNS: DataTableColumn<TicketRow>[] = [
    { key: 'status', value: (t) => ticketStatusMeta(t.ticket_status_id).label },
    { key: 'assignee', value: (t) => (isSuperAdmin.value ? t.subUser?.username : t.user?.username) ?? 'Unbekannt' },
    { key: 'problem_type' },
    { key: 'device', value: (t) => t.invitem?.gname ?? '—' },
    { key: 'priority', value: (t) => ticketPriorityLabel(t.priority_id) },
    { key: 'created_at', searchable: false },
    { key: 'actions', sortable: false, searchable: false },
];
const ticketTable = reactive(useDataTable(myTickets, TICKET_COLUMNS));

// --- Handwerk folder nav (own "Offene"/"Erledigte" pair + per-city links) ---

const myCities = computed(() => props.userCities[currentUser.value?.id ?? -1] ?? []);

const handwerkFolderLinks = computed(() => [
    { label: 'Offene', href: '/usertickets', count: props.myhandwerkTicketsCount, icon: Inbox, active: true },
    { label: 'Erledigte', href: '/usertHandwerkicketshistory', count: props.handwerkticketsdone, icon: CheckCircle2 },
    ...myCities.value.map((city) => ({
        label: city.charAt(0).toUpperCase() + city.slice(1),
        href: `/usertickets/${city}`,
        count: props.cityHandwerkCounts[city] ?? 0,
        active: props.city === city,
    })),
]);

const handwerkCardHeading = computed(() => (props.city ? props.city.charAt(0).toUpperCase() + props.city.slice(1) : (currentUser.value?.ort ?? '')));

const HANDWERK_COLUMNS: DataTableColumn<HandwerkRow>[] = [
    { key: 'problem_type' },
    { key: 'submitter_name' },
    { key: 'submitter_standort' },
    { key: 'location.address' },
    { key: 'room', value: (t) => `${t.room?.rname ?? ''} ${t.room?.altrname ?? ''}`.trim() },
    { key: 'priority', value: (t) => ticketPriorityLabel(t.priority) },
    { key: 'created_at', searchable: false },
];
const myHandwerkTickets = computed(() => props.myHandwerkTickets);
const handwerkTable = reactive(useDataTable(myHandwerkTickets, HANDWERK_COLUMNS));

// --- Korso (Verwaltung) table ---

const korsoTickets = ref<KorsoTicketRow[]>([...props.korsoTicket]);

async function deleteKorsoTicket(ticket: KorsoTicketRow) {
    if (!confirm('Das Löschen eines Korso-Tickets kann nicht rückgängig gemacht werden!')) return;

    try {
        const { data } = await axios.post(`/korso/force_delete/${ticket.id}`);
        if (data === 'true') {
            korsoTickets.value = korsoTickets.value.filter((t) => t.id !== ticket.id);
            toast.success('Das Ticket wurde dauerhaft gelöscht.');
        } else {
            toast.error('Das Ticket konnte nicht gelöscht werden.');
        }
    } catch {
        toast.error('Es ist ein Fehler aufgetreten.');
    }
}

function korsoRowActions(ticket: KorsoTicketRow) {
    return [{ icon: Trash2, label: 'Löschen', variant: 'destructive' as const, onClick: () => deleteKorsoTicket(ticket) }];
}

function korsoPriorityLabel(priority: number): string {
    return priority === 3 ? 'Hoch' : priority === 2 ? 'Normal' : 'Niedrig';
}

const KORSO_COLUMNS: DataTableColumn<KorsoTicketRow>[] = [
    { key: 'submitter_name', value: (t) => t.submitter_name ?? 'nicht zugewiesen' },
    { key: 'status', value: (t) => t.ticket_status?.name ?? 'nicht zugewiesen' },
    { key: 'assignedUser', value: (t) => t.assignedUser?.username ?? 'nicht zugewiesen' },
    { key: 'problem_type' },
    { key: 'priority', value: (t) => korsoPriorityLabel(t.priority) },
    { key: 'created_at', searchable: false },
    { key: 'actions', sortable: false, searchable: false },
];
const korsoTable = reactive(useDataTable(korsoTickets, KORSO_COLUMNS));

const korsoFolderLinks = computed(() => [
    { label: 'Offene', href: '/usertickets', count: props.assignedCount, icon: Inbox, active: true },
    { label: 'Erledigte', href: '/userticketshistory', count: props.myDoneCount, icon: CheckCircle2 },
]);
</script>

<template>
    <Head title="Meine Tickets" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <!-- Ticket section -->
        <div class="flex flex-col gap-4 lg:flex-row">
            <TicketFolderNav title="Ordner" :links="ticketFolderLinks" />

            <div class="bg-card text-card-foreground flex-1 rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h3 class="font-semibold">Anzahl offener Tickets: {{ myTicketsCount }}</h3>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="ticketTable.search" v-model:page-size="ticketTable.pageSize" search-placeholder="Suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Status" sort-key="status" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('status')" />
                                <TableHeadCell :label="isSuperAdmin ? 'Erstellt von' : 'Zugewiesen an'" sort-key="assignee" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('assignee')" />
                                <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('problem_type')" />
                                <TableHeadCell label="Das Gerät" sort-key="device" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('device')" />
                                <TableHeadCell label="Priorität" sort-key="priority" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" @sort="ticketTable.toggleSort('priority')" />
                                <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="ticketTable.sortKey" :direction="ticketTable.sortDir" align="right" @sort="ticketTable.toggleSort('created_at')" />
                                <TableHeadCell label="" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in ticketTable.pagedRows" :key="ticket.id">
                                <td class="p-3">
                                    <component
                                        :is="ticketStatusMeta(ticket.ticket_status_id).icon"
                                        class="h-4 w-4"
                                        :style="{ color: ticketStatusMeta(ticket.ticket_status_id).color }"
                                        :title="ticketStatusMeta(ticket.ticket_status_id).label"
                                    />
                                </td>
                                <td class="p-3">
                                    <Link :href="`/ticket/${ticket.id}`" class="text-primary font-semibold hover:underline">
                                        {{ (isSuperAdmin ? ticket.subUser?.username : ticket.user?.username) ?? 'Unbekannt' }}
                                    </Link>
                                </td>
                                <td class="p-3">
                                    <Link :href="`/ticket/${ticket.id}`" class="hover:underline">{{ ticket.problem_type }}</Link>
                                </td>
                                <td class="p-3 font-semibold">{{ ticket.invitem?.gname ?? '—' }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="ticketPriorityBadgeClass(ticket.priority_id)"
                                    >
                                        {{ ticketPriorityLabel(ticket.priority_id) }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">{{ formatDate(ticket.created_at) }}</td>
                                <td class="p-3">
                                    <RowActions :actions="ticketRowActions(ticket)" />
                                </td>
                            </tr>
                            <tr v-if="!ticketTable.pagedRows.length">
                                <td colspan="7" class="text-muted-foreground p-6 text-center">
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

        <!-- Handwerk section -->
        <div v-if="canSeeHandwerk" class="flex flex-col gap-4 lg:flex-row">
            <TicketFolderNav title="Handwerk Ordner" :links="handwerkFolderLinks" />

            <div class="bg-card text-card-foreground flex-1 rounded-xl border shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b p-4">
                    <h3 class="font-semibold">
                        Anzahl eigene offener Tickets: <span style="color: #004873">{{ myhandwerkTicketsCount }}</span>
                        <span class="text-muted-foreground ml-2 font-normal capitalize">
                            {{ handwerkCardHeading }}: <span class="font-semibold" style="color: #004873">{{ myhandwerkTicketsCountCity }}</span>
                        </span>
                    </h3>
                    <a
                        v-if="canDownloadHandwerkPdf"
                        :href="`/handwerk/${currentUser?.ort}/open-tickets-pdf`"
                        class="bg-destructive text-destructive-foreground hover:bg-destructive/90 inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm"
                    >
                        <FileDown class="h-4 w-4" />
                        PDF Herunterladen
                    </a>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="handwerkTable.search" v-model:page-size="handwerkTable.pageSize" search-placeholder="Suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" @sort="handwerkTable.toggleSort('problem_type')" />
                                <TableHeadCell label="Ersteller" sort-key="submitter_name" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" @sort="handwerkTable.toggleSort('submitter_name')" />
                                <TableHeadCell label="Standort" sort-key="submitter_standort" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" @sort="handwerkTable.toggleSort('submitter_standort')" />
                                <TableHeadCell label="Adresse" sort-key="location.address" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" @sort="handwerkTable.toggleSort('location.address')" />
                                <TableHeadCell label="Raum" sort-key="room" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" @sort="handwerkTable.toggleSort('room')" />
                                <TableHeadCell label="Priorität" sort-key="priority" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" @sort="handwerkTable.toggleSort('priority')" />
                                <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="handwerkTable.sortKey" :direction="handwerkTable.sortDir" align="right" @sort="handwerkTable.toggleSort('created_at')" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in handwerkTable.pagedRows" :key="ticket.id">
                                <td class="p-3">
                                    <Link :href="`/handwerk/${ticket.id}`" class="text-primary font-semibold hover:underline">
                                        {{ ticket.problem_type }}
                                    </Link>
                                </td>
                                <td class="p-3">{{ ticket.submitter_name }}</td>
                                <td class="p-3">{{ ticket.submitter_standort }}</td>
                                <td class="p-3">{{ ticket.location?.address }}</td>
                                <td class="p-3">{{ ticket.room?.rname }} {{ ticket.room?.altrname }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="ticketPriorityBadgeClass(ticket.priority)"
                                    >
                                        {{ ticketPriorityLabel(ticket.priority) }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">{{ formatDate(ticket.created_at) }}</td>
                            </tr>
                            <tr v-if="!handwerkTable.pagedRows.length">
                                <td colspan="7" class="text-muted-foreground p-6 text-center">
                                    {{ handwerkTable.search ? 'Nichts gefunden.' : 'Keine Tickets vorhanden.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <TablePagination
                    :page="handwerkTable.page"
                    :page-count="handwerkTable.pageCount"
                    :range-from="handwerkTable.rangeFrom"
                    :range-to="handwerkTable.rangeTo"
                    :total="handwerkTable.total"
                    item-label="Tickets"
                    @update:page="handwerkTable.page = $event"
                />
            </div>
        </div>

        <!-- Korso section -->
        <div v-if="canSeeKorso" class="flex flex-col gap-4 lg:flex-row">
            <TicketFolderNav title="Ordner" :links="korsoFolderLinks" />

            <div class="bg-card text-card-foreground flex-1 rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h3 class="font-semibold">Anzahl offener Tickets: {{ myTicketsCount }}</h3>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="korsoTable.search" v-model:page-size="korsoTable.pageSize" search-placeholder="Suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Ersteller" sort-key="submitter_name" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('submitter_name')" />
                                <TableHeadCell label="Status" sort-key="status" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('status')" />
                                <TableHeadCell label="Zugewiesen an" sort-key="assignedUser" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('assignedUser')" />
                                <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('problem_type')" />
                                <TableHeadCell label="Priorität" sort-key="priority" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" @sort="korsoTable.toggleSort('priority')" />
                                <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="korsoTable.sortKey" :direction="korsoTable.sortDir" align="right" @sort="korsoTable.toggleSort('created_at')" />
                                <TableHeadCell label="" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in korsoTable.pagedRows" :key="ticket.id">
                                <td class="p-3">{{ ticket.submitter_name ?? 'nicht zugewiesen' }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="korsoStatusBadgeClass(ticket.ticket_status?.id)"
                                    >
                                        {{ ticket.ticket_status?.name ?? 'nicht zugewiesen' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <Link :href="`/korso/${ticket.id}`" class="text-primary font-semibold hover:underline">
                                        {{ ticket.assignedUser?.username ?? 'nicht zugewiesen' }}
                                    </Link>
                                </td>
                                <td class="p-3">{{ ticket.problem_type }}</td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            ticket.priority === 3
                                                ? 'bg-red-100 text-red-800'
                                                : ticket.priority === 2
                                                  ? 'bg-amber-100 text-amber-800'
                                                  : 'bg-gray-100 text-gray-700'
                                        "
                                    >
                                        {{ korsoPriorityLabel(ticket.priority) }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">{{ formatDate(ticket.created_at) }}</td>
                                <td class="p-3">
                                    <RowActions :actions="korsoRowActions(ticket)" />
                                </td>
                            </tr>
                            <tr v-if="!korsoTable.pagedRows.length">
                                <td colspan="7" class="text-muted-foreground p-6 text-center">
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
