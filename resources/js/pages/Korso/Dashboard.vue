<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { computed, h, ref } from 'vue';
import { watchDebounced } from '@vueuse/core';
import { ArrowDown, ArrowUp, ArrowUpDown, CheckCircle2, ChevronLeft, ChevronRight, Eye, PaintRoller, RefreshCw, Settings, X } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/korso/dashboard.blade.php
 * (KorsoController@dashboard, route /korso-dashboard) - the Korso_ma/
 * Korso_Admin ticket queue. The old page's DataTables search/paging was
 * dropped, matching the plain-table approach used everywhere else in this
 * app; filtering (sidebar filters + per-admin buttons) still works the same
 * way, just via axios instead of jQuery/AJAX re-rendering a Blade partial.
 *
 * Two endpoints changed shape to support this (see KorsoController):
 * - filterTickets() now returns JSON instead of a rendered
 *   korso.partials.tickets_table view.
 * - getTicketDetails() now returns JSON instead of a rendered
 *   korso.partials.ticket_details view - the "sideslide" panel below reads
 *   it directly instead of injecting raw HTML.
 *
 * The old page also had a `.delete-ticket` jQuery handler wired up, but no
 * button anywhere in korso.partials.tickets_table actually triggers it (dead
 * code - confirmed by reading that partial) - not reproduced here for the
 * same reason: there was never a working "permanently delete" action to
 * carry over. Separately, KorsoController@destroy (the endpoint that dead
 * handler pointed at) starts with an unconditional `return $korso;` before
 * any of its own delete logic, so even if something did call it today it
 * wouldn't delete anything - flagging this in case it's meant to work.
 */

type KorsoMaUser = { id: number; name: string; vorname: string | null; assigned_tickets_count: number };

type TicketStatus = { id: number; name: string } | null;
type NamedUser = { id: number; name: string } | null;
type SubUser = { vorname: string | null; name: string | null; ort: string | null; position: string | null; abteilung: string | null } | null;

type TicketRow = {
    id: number;
    priority: number;
    problem_type: string;
    assignedTo: number | null;
    subUser: SubUser;
    // Name stored on the ticket at creation time - always present, unlike
    // subUser (the App\User relation via "submitter"), which can't resolve
    // for every ticket. Used as the Ersteller fallback below.
    submitter_name: string | null;
    ticket_status: TicketStatus;
    created_at: string;
};

type PaginatedTickets = {
    data: TicketRow[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    from: number | null;
    to: number | null;
};

type NamedItem = { name: string } | null;
type KorsoItem = { item_name: string; quantity: number; details: string | null };
type Attachment = { file_path: string; file_type: string; context: string | null };
type Kcourse = { name: string; payer: { name: string } | null; pivot: { quantity: number } };
type LocationInfo = { address: string | null } | null;

type TicketDetail = {
    id: number;
    priority: number;
    problem_type: string;
    subUser: SubUser;
    submitter_name: string | null;
    // Also recorded on the ticket at creation time - fallback for Standort
    // below when subUser can't resolve. Position/Abteilung have no such
    // fallback column on korsos, so they stay "—" for those tickets.
    submitter_standort: string | null;
    assignedUser: NamedUser;
    ticket_status: TicketStatus;
    onlinemarketingItem: NamedItem;
    zertifizierungItem: NamedItem;
    massnahme: NamedItem;
    created_at: string;
    notizen: string | null;
    is_chatgpt_project: boolean;
    location: LocationInfo;
    kcourses: Kcourse[];
    korsoItems: KorsoItem[];
    korsoAttachments: Attachment[];
};

const props = defineProps<{
    korso_ma_users: KorsoMaUser[];
    tickets: PaginatedTickets;
    assignedCount: number;
    unassignedCount: number;
    openCount: number;
    myDoneCount: number;
    allDoneCount: number;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Dashboard', href: '/korso-dashboard' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const currentUser = computed(() => page.props.auth.user);
const roles = computed(() => currentUser.value?.roles ?? []);
const isKorsoAdmin = computed(() => roles.value.includes('Korso_Admin'));
// The old page hardcoded these two ids ("Id 312 is Frau Dreyße") - carried
// over as-is rather than guessing at a role that isn't actually checked.
const canSeePrintmarketingManagement = computed(() => currentUser.value?.id === 1 || currentUser.value?.id === 312);

// Local, mutable copies of the sidebar counts and per-admin badge counts.
// These come from props (computed server-side on the initial page load), but
// markDone() below needs to adjust them in place after a ticket is marked
// done - otherwise they'd only ever reflect reality again after a full page
// reload, which is exactly the bug this was fixed for.
const assignedCount = ref(props.assignedCount);
const unassignedCount = ref(props.unassignedCount);
const openCount = ref(props.openCount);
const myDoneCount = ref(props.myDoneCount);
const allDoneCount = ref(props.allDoneCount);
const korsoMaUsers = ref<KorsoMaUser[]>(props.korso_ma_users.map((u) => ({ ...u })));

const FILTERS: { key: string; label: string; count: () => number }[] = [
    { key: 'assigned', label: 'Eigene Tickets', count: () => assignedCount.value },
    { key: 'unassigned', label: 'Nicht zugewiesene Tickets', count: () => unassignedCount.value },
    { key: 'open', label: 'Offene Tickets', count: () => openCount.value },
    { key: 'myDone', label: 'Erledigte', count: () => myDoneCount.value },
    { key: 'allDone', label: 'Alle erledigten Tickets', count: () => allDoneCount.value },
];

// The dashboard's default (initial-load) view is already the "unassigned"
// filter (see KorsoController@dashboard) - starting activeFilter here
// instead of null keeps the sidebar highlight and the search/sort controls
// in sync with what's actually on screen before any tab is clicked.
const heading = ref('Dashboard Overview');
const activeFilter = ref<string>('unassigned');
const activeUserId = ref<number | null>(null);
const ticketsPage = ref<PaginatedTickets>(props.tickets);
const tickets = computed(() => ticketsPage.value.data);
const loading = ref(false);

// Search + date-sort - both are server-side (see KorsoController@filterTickets)
// since "Alle erledigten Tickets" in particular can hold far more rows than
// makes sense to filter/sort in the browser.
const search = ref('');
const dateSort = ref<'asc' | 'desc' | null>(null);

// Restore the active filter/admin selection when arriving back from a
// ticket's Show.vue page via its "Dashboard" back button, which forwards
// ?filter=/&user_id=&page= (see ticketHref() below and Show.vue's
// dashboardHref) - without this, going into a ticket from e.g. the
// "S. Ersfeld" admin tab and clicking back landed on the default
// (unassigned) view instead of back on that same tab. Only search/dateSort
// are intentionally left out of this round trip - restoring exactly what
// the user's report described (the active filter/admin tab) rather than
// every bit of table state.
{
    const initialParams = new URLSearchParams(window.location.search);
    const initialUserId = initialParams.get('user_id');
    const initialFilter = initialParams.get('filter');
    const initialPage = Number(initialParams.get('page')) || 1;

    if (initialUserId !== null) {
        const uid = Number(initialUserId);
        const admin = korsoMaUsers.value.find((u) => u.id === uid);
        if (admin) {
            activeUserId.value = uid;
            heading.value = `Zugewiesene Tickets an ${(admin.vorname ?? '').charAt(0).toUpperCase()}. ${admin.name}`;
            reload(initialPage);
        }
    } else if (initialFilter && initialFilter !== activeFilter.value) {
        activeFilter.value = initialFilter;
        heading.value = FILTERS.find((f) => f.key === initialFilter)?.label ?? 'Dashboard Overview';
        reload(initialPage);
    } else if (initialPage > 1) {
        reload(initialPage);
    }
}

// Builds the query string a ticket row's link carries into Show.vue, so its
// "Dashboard" back button can restore this same filter/admin selection - see
// the restoration block above.
function ticketHref(ticket: TicketRow): string {
    const params = new URLSearchParams();
    if (activeUserId.value !== null) {
        params.set('user_id', String(activeUserId.value));
    } else {
        params.set('filter', activeFilter.value);
    }
    if (ticketsPage.value.current_page > 1) {
        params.set('page', String(ticketsPage.value.current_page));
    }
    return `/korso/${ticket.id}?${params.toString()}`;
}

function buildFetchParams(pageNum: number): Record<string, string | number> {
    const params: Record<string, string | number> =
        activeUserId.value !== null ? { user_id: activeUserId.value } : { filter: activeFilter.value };

    if (search.value.trim()) {
        params.search = search.value.trim();
    }
    if (dateSort.value) {
        params.sort = 'created_at';
        params.direction = dateSort.value;
    }
    params.page = pageNum;
    return params;
}

async function reload(pageNum = 1) {
    await fetchTickets(buildFetchParams(pageNum));
}

async function selectFilter(filter: string) {
    activeUserId.value = null;
    activeFilter.value = filter;
    heading.value = FILTERS.find((f) => f.key === filter)?.label ?? 'Dashboard Overview';
    await reload(1);
}

async function selectAdminUser(user: KorsoMaUser) {
    activeUserId.value = user.id;
    heading.value = `Zugewiesene Tickets an ${(user.vorname ?? '').charAt(0).toUpperCase()}. ${user.name}`;
    await reload(1);
}

function toggleDateSort() {
    // cycle: default (priority) -> newest first -> oldest first -> default
    if (dateSort.value === null) dateSort.value = 'desc';
    else if (dateSort.value === 'desc') dateSort.value = 'asc';
    else dateSort.value = null;
    reload(1);
}

function goToPage(pageNum: number) {
    if (pageNum < 1 || pageNum > ticketsPage.value.last_page || pageNum === ticketsPage.value.current_page) return;
    reload(pageNum);
}

watchDebounced(search, () => reload(1), { debounce: 350 });

async function fetchTickets(params: Record<string, string | number>) {
    loading.value = true;
    try {
        const { data } = await axios.get('/dashboard/filter-tickets', { params });
        ticketsPage.value = data;
    } catch {
        toast.error('Error loading tickets.');
    } finally {
        loading.value = false;
    }
}

function refresh() {
    window.location.reload();
}

// --- per-row actions ---

async function onAssignChange(ticket: TicketRow) {
    try {
        await axios.post('/korso/assign', { ticket_id: ticket.id, user_id: ticket.assignedTo });
        toast.success(ticket.assignedTo ? 'Zugewiesen!' : 'Nicht zugewiesen!');
    } catch {
        toast.error('Something went wrong.');
    }
}

async function markDone(ticket: TicketRow) {
    if (!confirm('Dieses Ticket wird als erledigt markiert!')) return;

    try {
        await axios.post(`/korso/${ticket.id}/done`);
        ticketsPage.value = {
            ...ticketsPage.value,
            data: ticketsPage.value.data.filter((t) => t.id !== ticket.id),
            total: Math.max(ticketsPage.value.total - 1, 0),
        };

        // Sidebar counts and the per-admin badges are otherwise only computed
        // server-side on page load, so without this they'd silently go stale
        // until the next full refresh - update them the same way the server
        // would recompute them for this one ticket's state change.
        openCount.value = Math.max(openCount.value - 1, 0);
        if (ticket.assignedTo === null) {
            unassignedCount.value = Math.max(unassignedCount.value - 1, 0);
        } else {
            const admin = korsoMaUsers.value.find((u) => u.id === ticket.assignedTo);
            if (admin) admin.assigned_tickets_count = Math.max(admin.assigned_tickets_count - 1, 0);
            if (ticket.assignedTo === currentUser.value?.id) {
                assignedCount.value = Math.max(assignedCount.value - 1, 0);
                myDoneCount.value += 1;
            }
        }
        allDoneCount.value += 1;

        toast.success('Ticket wurde als erledigt markiert.');
    } catch {
        toast.error('Etwas ist schief gelaufen.');
    }
}

// --- priority / status badges ---

function priorityBadgeClass(priority: number): string {
    if (priority === 3) return 'bg-red-100 text-red-800';
    if (priority === 2) return 'bg-amber-100 text-amber-800';
    return 'bg-gray-100 text-gray-700';
}
function priorityLabel(priority: number): string {
    if (priority === 3) return 'Hoch';
    if (priority === 2) return 'Normal';
    return 'Niedrig';
}

const STATUS_BADGE_CLASSES: Record<number, string> = {
    1: 'bg-gray-100 text-gray-700', // Nicht begonnen
    2: 'bg-amber-100 text-amber-800', // In Bearbeitung
    3: 'bg-green-100 text-green-800', // Erledigt
    4: 'bg-blue-100 text-blue-800', // Wartet auf jemand anderen
    5: 'bg-slate-800 text-white', // Zurückgestellt
    6: 'bg-red-100 text-red-800', // Duplikat
    7: 'bg-indigo-100 text-indigo-800', // Warten auf Antwort
    8: 'bg-amber-100 text-amber-800', // Wiederhergestellt
};
function statusBadgeClass(status: TicketStatus): string {
    if (!status) return 'bg-gray-100 text-gray-700';
    return STATUS_BADGE_CLASSES[status.id] ?? 'bg-gray-100 text-gray-700';
}

function timeAgo(value: string): string {
    const diffMin = Math.round((Date.now() - new Date(value).getTime()) / 60000);
    if (diffMin < 60) return `vor ${Math.max(diffMin, 0)} Min.`;
    const diffHour = Math.round(diffMin / 60);
    if (diffHour < 24) return `vor ${diffHour} Std.`;
    return `vor ${Math.round(diffHour / 24)} Tagen`;
}

// subUser (the App\User relation) doesn't resolve for every ticket, so the
// table and the sideslide both fall back to submitter_name - the plain
// string recorded on the ticket itself at creation time.
function creatorLabel(ticket: { subUser: SubUser; submitter_name: string | null }): string {
    if (ticket.subUser?.name) {
        return `${(ticket.subUser.vorname ?? '').charAt(0).toUpperCase()}. ${ticket.subUser.name}`;
    }
    return ticket.submitter_name?.trim() || 'Unbekannt';
}

// --- ticket detail sideslide panel ---

const detailTicket = ref<TicketDetail | null>(null);
const detailOpen = ref(false);
const detailLoading = ref(false);

async function openDetails(ticket: TicketRow) {
    detailOpen.value = true;
    detailLoading.value = true;
    detailTicket.value = null;
    try {
        const { data } = await axios.get(`/korso/${ticket.id}/details`);
        detailTicket.value = data;
    } catch (error) {
        // Logged (not just toasted) so a real failure here - wrong status
        // code, unexpected payload shape - shows up in devtools instead of
        // just "something went wrong".
        console.error('Failed to load Korso ticket details for #' + ticket.id, error);
        toast.error('Fehler beim Laden der Ticketdetails.');
        detailOpen.value = false;
    } finally {
        detailLoading.value = false;
    }
}

function closeDetails() {
    detailOpen.value = false;
}

const detailCleanNotes = computed(() => {
    const t = detailTicket.value;
    if (!t) return null;
    let notes = t.notizen;
    if (t.is_chatgpt_project && notes?.includes('<h5>ChatGPT-Projektvorschläge</h5>')) {
        const idx = notes.indexOf('<hr>');
        notes = idx !== -1 ? notes.slice(0, idx) : null;
    }
    return notes;
});
const detailHasCleanNotes = computed(() => !!detailCleanNotes.value?.replace(/<[^>]*>/g, '').trim());

// Same (narrower) field set as the old sideslide partial - it shows
// name/email/telephone/mobile/fax, not the same list Korso/Show.vue's
// Visitenkarten block shows (position/adresse instead of mobile) - the two
// old Blade partials already disagreed on this, reproduced as-is per page
// rather than unified.
const SIDESLIDE_VISITENKARTE_FIELDS: { key: string; label: string }[] = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'telephone', label: 'Telefon' },
    { key: 'mobile', label: 'Mobil' },
    { key: 'fax', label: 'Fax' },
];
function visitenkarteDetails(details: string): { label: string; value: string }[] {
    // Guard the JSON.parse: a render-time throw here would abort the whole
    // sideslide's re-render, leaving it stuck showing whatever it displayed
    // before (e.g. still "Loading...") instead of surfacing the real error.
    let parsed: Record<string, string | undefined>;
    try {
        parsed = JSON.parse(details) as Record<string, string | undefined>;
    } catch {
        return [];
    }
    return SIDESLIDE_VISITENKARTE_FIELDS.filter((f) => parsed[f.key]).map((f) => ({ label: f.label, value: parsed[f.key]! }));
}

function attachmentUrl(path: string): string {
    return `/storage/${path}`;
}
</script>

<template>
    <Head title="Korso Dashboard" />

    <div class="flex flex-1 gap-4 p-4">
        <!-- Sidebar -->
        <div class="bg-card text-card-foreground w-64 shrink-0 rounded-xl border p-4 shadow-sm">
            <h3 class="mb-3 text-center font-semibold">{{ currentUser?.username ?? currentUser?.name }}</h3>

            <nav class="flex flex-col gap-1">
                <button
                    v-for="filter in FILTERS"
                    :key="filter.key"
                    type="button"
                    class="hover:bg-accent flex items-center justify-between rounded-md px-3 py-2 text-left text-sm"
                    :class="activeFilter === filter.key ? 'text-primary font-semibold' : ''"
                    @click="selectFilter(filter.key)"
                >
                    {{ filter.label }}
                    <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs">{{ filter.count() }}</span>
                </button>

                <a
                    v-if="canSeePrintmarketingManagement"
                    href="/printmarketing-management"
                    class="hover:bg-accent mt-1 flex items-center gap-2 rounded-md px-3 py-2 text-sm"
                >
                    <PaintRoller class="h-4 w-4" />
                    Printmarketing Verwaltung
                </a>
            </nav>

            <button
                type="button"
                class="border-border hover:bg-accent mt-4 inline-flex h-9 w-full items-center justify-center gap-1.5 rounded-md border text-sm"
                @click="refresh"
            >
                <RefreshCw class="h-4 w-4" />
                Refresh
            </button>

            <nav v-if="isKorsoAdmin" class="mt-4 flex flex-col gap-1 border-t pt-4">
                <a href="/onlinemarketing_items" class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm">
                    <Settings class="h-4 w-4" />
                    Onlinemarketing Optionen
                </a>
                <a href="/zertifizierung_items" class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm">
                    <Settings class="h-4 w-4" />
                    Zert & Quali. Optionen
                </a>
                <Link href="/user-management" class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm">
                    <Settings class="h-4 w-4" />
                    Rechtevergabe
                </Link>
                <a href="/sek-groups" class="hover:bg-accent flex items-center gap-2 rounded-md px-3 py-2 text-sm">
                    <Settings class="h-4 w-4" />
                    Sek Groupen
                </a>
            </nav>
        </div>

        <!-- Main content -->
        <div class="flex min-w-0 flex-1 flex-col gap-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-semibold">{{ heading }}</h2>

                <div class="flex flex-wrap items-center gap-2">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Suchen (Ersteller, Bereich, ID)..."
                        class="border-input bg-background h-9 w-56 rounded-md border px-3 text-sm"
                    />

                    <div v-if="isKorsoAdmin" class="flex flex-wrap gap-2">
                        <button
                            v-for="u in korsoMaUsers"
                            :key="u.id"
                            type="button"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-xs"
                            :class="
                                activeUserId === u.id
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'border-border hover:bg-accent'
                            "
                            @click="selectAdminUser(u)"
                        >
                            {{ (u.vorname ?? '').charAt(0).toUpperCase() }}. {{ u.name }}
                            <span class="rounded-full bg-black/10 px-1.5">{{ u.assigned_tickets_count }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <th class="p-3 font-medium">ID</th>
                                <th class="p-3 font-medium">Ersteller</th>
                                <th class="p-3 font-medium">Priorität</th>
                                <th class="p-3 font-medium">Bereich</th>
                                <th class="p-3 font-medium">Status</th>
                                <th class="p-3 font-medium">Zugewiesen an</th>
                                <th class="p-3 font-medium text-right">
                                    <button
                                        type="button"
                                        class="hover:text-foreground inline-flex items-center gap-1"
                                        title="Nach Datum sortieren"
                                        @click="toggleDateSort"
                                    >
                                        Datum
                                        <ArrowUpDown v-if="dateSort === null" class="h-3.5 w-3.5" />
                                        <ArrowDown v-else-if="dateSort === 'desc'" class="h-3.5 w-3.5" />
                                        <ArrowUp v-else class="h-3.5 w-3.5" />
                                    </button>
                                </th>
                                <th class="p-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="ticket in tickets" :key="ticket.id" :class="ticket.priority === 3 ? 'bg-red-50' : ''">
                                <td class="p-3">{{ ticket.id }}</td>
                                <td class="p-3">
                                    <Link :href="ticketHref(ticket)" class="text-primary font-semibold hover:underline">
                                        {{ creatorLabel(ticket) }}
                                    </Link>
                                </td>
                                <td class="p-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="priorityBadgeClass(ticket.priority)">
                                        {{ priorityLabel(ticket.priority) }}
                                    </span>
                                </td>
                                <td class="p-3">{{ ticket.problem_type }}</td>
                                <td class="p-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusBadgeClass(ticket.ticket_status)">
                                        {{ ticket.ticket_status?.name ?? 'nicht zugewiesen' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <select
                                        v-model="ticket.assignedTo"
                                        class="border-input bg-background h-8 rounded-md border px-2 text-xs"
                                        @change="onAssignChange(ticket)"
                                    >
                                        <option :value="null">nicht zugewiesen</option>
                                        <option v-for="u in korsoMaUsers" :key="u.id" :value="u.id">
                                            {{ (u.vorname ?? '').charAt(0).toUpperCase() }}. {{ u.name }}
                                        </option>
                                    </select>
                                </td>
                                <td class="p-3 text-right">{{ timeAgo(ticket.created_at) }}</td>
                                <td class="p-3">
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-if="activeFilter !== 'myDone' && activeFilter !== 'allDone'"
                                            type="button"
                                            title="Erledigt"
                                            class="text-muted-foreground hover:bg-accent hover:text-primary inline-flex h-8 w-8 items-center justify-center rounded-md"
                                            @click="markDone(ticket)"
                                        >
                                            <CheckCircle2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            title="Details"
                                            class="text-muted-foreground hover:bg-accent hover:text-primary inline-flex h-8 w-8 items-center justify-center rounded-md"
                                            @click="openDetails(ticket)"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="loading">
                                <td colspan="8" class="text-muted-foreground p-6 text-center">Lädt...</td>
                            </tr>
                            <tr v-else-if="!tickets.length">
                                <td colspan="8" class="text-muted-foreground p-6 text-center">Keine Tickets vorhanden.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="ticketsPage.last_page > 1" class="flex flex-wrap items-center justify-between gap-2 border-t p-3 text-sm">
                    <span class="text-muted-foreground">
                        {{ ticketsPage.from ?? 0 }}–{{ ticketsPage.to ?? 0 }} von {{ ticketsPage.total }} Tickets
                    </span>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            :disabled="ticketsPage.current_page <= 1 || loading"
                            class="border-border hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border disabled:cursor-not-allowed disabled:opacity-40"
                            @click="goToPage(ticketsPage.current_page - 1)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                        <span class="text-muted-foreground px-2">Seite {{ ticketsPage.current_page }} von {{ ticketsPage.last_page }}</span>
                        <button
                            type="button"
                            :disabled="ticketsPage.current_page >= ticketsPage.last_page || loading"
                            class="border-border hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border disabled:cursor-not-allowed disabled:opacity-40"
                            @click="goToPage(ticketsPage.current_page + 1)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket details sideslide -->
    <Transition enter-active-class="transition-opacity duration-200" leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div v-if="detailOpen" class="fixed inset-0 z-50 flex justify-end">
            <div class="absolute inset-0 bg-black/30" @click="closeDetails"></div>
            <Transition
                enter-active-class="transition-transform duration-300"
                leave-active-class="transition-transform duration-300"
                enter-from-class="translate-x-full"
                leave-to-class="translate-x-full"
            >
                <div v-if="detailOpen" class="relative flex h-full w-full max-w-md flex-col bg-white shadow-xl dark:bg-neutral-900">
                    <div class="flex items-center justify-between p-4 text-white" style="background-color: #65a30d">
                        <span class="font-semibold">Ticket Details</span>
                        <button type="button" @click="closeDetails"><X class="h-5 w-5" /></button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 text-sm">
                        <p v-if="detailLoading" class="text-muted-foreground">Loading...</p>

                        <div v-else-if="detailTicket" class="flex flex-col gap-2">
                            <p>
                                <strong>Ersteller:</strong>
                                {{ `${detailTicket.subUser?.vorname ?? ''} ${detailTicket.subUser?.name ?? ''}`.trim() || detailTicket.submitter_name?.trim() || 'Unbekannt' }}
                            </p>
                            <p><strong>Standort:</strong> {{ detailTicket.subUser?.ort || detailTicket.submitter_standort || '—' }}</p>
                            <p><strong>Position:</strong> {{ detailTicket.subUser?.position ?? '—' }}</p>
                            <p><strong>Abteilung:</strong> {{ detailTicket.subUser?.abteilung ?? '—' }}</p>
                            <p>
                                <strong>Priorität:</strong>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="priorityBadgeClass(detailTicket.priority)">
                                    {{ priorityLabel(detailTicket.priority) }}
                                </span>
                            </p>
                            <p><strong>Typ:</strong> {{ detailTicket.problem_type }}</p>
                            <p v-if="detailTicket.problem_type === 'Onlinemarketing' && detailTicket.onlinemarketingItem">
                                <strong>Online Marketing Item:</strong> {{ detailTicket.onlinemarketingItem.name }}
                            </p>
                            <p v-else-if="detailTicket.problem_type === 'Zertifizierung & Qualitätsmanagement' && detailTicket.zertifizierungItem">
                                <strong>Zertifizierung:</strong> {{ detailTicket.zertifizierungItem.name }}
                            </p>
                            <p v-if="detailTicket.massnahme"><strong>Maßnahme:</strong> {{ detailTicket.massnahme.name }}</p>
                            <p><strong>Status:</strong> {{ detailTicket.ticket_status?.name ?? 'Nicht begonnen' }}</p>
                            <p><strong>Zugewiesen an:</strong> {{ detailTicket.assignedUser?.name ?? 'Nicht zugewiesen' }}</p>
                            <p>
                                <strong>Erstellt am:</strong>
                                {{ new Date(detailTicket.created_at).toLocaleString('de-DE') }}
                            </p>

                            <template v-if="detailHasCleanNotes">
                                <hr />
                                <h4 class="font-semibold" style="color: #65a30d">Beschreibung</h4>
                                <!-- eslint-disable-next-line vue/no-v-html -->
                                <p v-html="detailCleanNotes"></p>
                            </template>

                            <hr />
                            <h4 class="font-semibold" style="color: #65a30d">Standort</h4>
                            <p>{{ detailTicket.location?.address ?? '-' }}</p>

                            <template v-if="detailTicket.kcourses?.length">
                                <hr />
                                <h4 class="font-semibold" style="color: #65a30d">Bestellung Flyer</h4>
                                <ul class="flex flex-col gap-1 pl-3">
                                    <li v-for="(kc, idx) in detailTicket.kcourses" :key="idx">
                                        <strong>{{ kc.payer?.name }}</strong> → {{ kc.name }}
                                        <span class="bg-green-100 text-green-800 ml-1 rounded-full px-1.5 py-0.5 text-xs">{{ kc.pivot.quantity }}</span>
                                    </li>
                                </ul>
                            </template>

                            <template v-if="detailTicket.korsoItems?.length">
                                <hr />
                                <h4 class="font-semibold" style="color: #65a30d">Bestellte Artikel</h4>
                                <ul class="flex flex-col gap-2 pl-3">
                                    <li v-for="(item, idx) in detailTicket.korsoItems" :key="idx">
                                        {{ item.item_name }}
                                        <span class="bg-green-100 text-green-800 ml-1 rounded-full px-1.5 py-0.5 text-xs">{{ item.quantity }}</span>
                                        <ul
                                            v-if="item.item_name === 'Visitenkarten' && item.details"
                                            class="text-muted-foreground mt-1 ml-3 flex flex-col gap-0.5 text-xs"
                                        >
                                            <li v-for="detail in visitenkarteDetails(item.details)" :key="detail.label">
                                                <strong>{{ detail.label }}:</strong> {{ detail.value }}
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </template>

                            <template v-if="(detailTicket.korsoAttachments ?? []).some((a) => a.file_type?.includes('image') || a.file_type === 'application/pdf')">
                                <hr />
                                <h4 class="font-semibold" style="color: #65a30d">Anhänge</h4>
                                <div class="flex flex-wrap gap-2">
                                    <template v-for="(att, idx) in detailTicket.korsoAttachments" :key="idx">
                                        <a v-if="att.file_type?.includes('image')" :href="attachmentUrl(att.file_path)" target="_blank" rel="noopener">
                                            <img :src="attachmentUrl(att.file_path)" class="h-20 w-20 rounded border object-cover" />
                                        </a>
                                        <a
                                            v-else-if="att.file_type === 'application/pdf'"
                                            :href="attachmentUrl(att.file_path)"
                                            target="_blank"
                                            rel="noopener"
                                            class="flex h-20 w-20 items-center justify-center rounded border text-red-600"
                                        >
                                            PDF
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
