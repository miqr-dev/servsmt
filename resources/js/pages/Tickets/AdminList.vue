<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { computed, h, ref } from 'vue';
import { X } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import type { Auth, BreadcrumbItem } from '@/types';
import { ticketStatusMeta, ticketPriorityLabel, ticketPriorityBadgeClass } from '@/lib/ticketStatus';

/**
 * Converted from resources/views/tickets/admins/tables/tickettable.blade.php,
 * the shared admin ticket LIST partial. In the old app this one Blade
 * partial is @extends'd by five near-empty (45-byte) wrapper views, one per
 * route/controller method:
 *   - open.blade.php          <- TicketController@opentickets        (mode "open")
 *   - unassigned.blade.php    <- TicketController@unassignedtickets  (mode "unassigned")
 *   - tickethistory.blade.php <- TicketController@tickethistory      (mode "history")
 *   - city.blade.php          <- TicketController@cityTickets        (mode "city")
 *   - admins.blade.php        <- TicketController@userTicketsAdmins  (mode "admin")
 * (confirmed by staging and reading all five - each is a single
 * `@extends('tickets.admins.tables.tickettable')` line with no @section
 * overrides). Rather than porting five near-duplicate Vue pages, all five
 * controller methods now render this one component with an explicit `mode`
 * prop - the old page told these apart at render time via
 * `URL::current() == route(...)` string comparisons, which is fragile and
 * doesn't work outside a full page load; `mode` replaces that everywhere
 * it mattered (the card heading text, and which of the header alert
 * buttons/badges show).
 *
 * Also NOT ported: resources/views/tickets/admins/cities/{berlin,chemnitz,
 * döbeln,dresden,leipzig,suhl}.blade.php - six more trivial `@extends`
 * wrappers, but confirmed dead (nothing in routes/web.php or any other view
 * references `tickets.admins.cities.*`; the one real per-city route,
 * `city.tickets` / TicketController@cityTickets, already renders the
 * parameterized `tickets.admins.city` wrapper handled here as mode "city").
 *
 * Eager loading: the old Blade table accesses `$myTicket->subUser` and
 * `$myTicket->invitem` per row. Three of the five controller methods
 * (opentickets/unassignedtickets/userTicketsAdmins) already eager-load
 * `subUser` but not `invitem`; cityTickets/tickethistory eager-loaded
 * neither. Under Blade this only meant an N+1 lazy query per row; under
 * Inertia the ticket collection is JSON-encoded as a prop, so a relation
 * that isn't eager-loaded is simply missing from the payload instead of
 * being lazily resolved - both relations are now eager-loaded in every one
 * of the five controller methods, a required part of this conversion, not
 * an optional perf tweak.
 *
 * DataTables search/paging dropped, matching every other converted list
 * page in this migration - the underlying queries already return the full
 * result set unpaginated (tickethistory caps itself at the latest 200 via
 * `->take(200)`, same as before).
 *
 * The old page's "assign ticket" <select> AJAX-posted straight to
 * TicketController@assignedTo without any visible feedback; a toast was
 * added here (same pattern as Korso/Dashboard.vue's onAssignChange) so a
 * failed assignment doesn't silently do nothing.
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts) - `myTickets` switched from a plain
 * reactive() array to ref() so it can be handed to useDataTable directly;
 * item mutation (the per-row assign <select>) still works the same way
 * since Vue unwraps nested object properties through a ref array exactly
 * like it does through reactive().
 *
 * Beschreibung (notizen) is HTML from the old rich-text field and can be
 * arbitrarily long, so the cell now shows a single-line plain-text preview
 * (tags stripped, truncated) with an "Anzeigen" button that opens a small
 * modal rendering the full ticket.notizen (still via v-html, same as the
 * old inline rendering - no sanitization was done here before either).
 *
 * 2026-09-17: removed the per-city hover-preview sidebar (fetchCityPreview /
 * onCityEnter / onCityLeave and the fixed right-side panel it rendered) -
 * not needed anymore. The per-city badge row itself is kept (still a real
 * link to the mode "city" page for that city), just without the hover
 * handlers. The "Nicht zugewiesen" navbar shortcut stays here too - only
 * removed from AppSidebar.vue's persistent nav, not from this page's own
 * top bar (a quick correction the same day: it was first dropped from both,
 * then restored here since only the sidebar entry was meant to go). Nothing
 * on the backend (routes, controller methods) was touched.
 */

type NamedUser = { username: string; ort: string | null } | null;
type InvItem = { gname: string | null } | null;

type TicketRow = {
    id: number;
    ticket_status_id: number | null;
    priority_id: number;
    assignedTo: number | null;
    problem_type: string;
    tel_number: string | null;
    custom_tel_number: string | null;
    notizen: string | null;
    created_at: string;
    subUser: NamedUser;
    invitem: InvItem;
};

type Admin = { id: number; name: string; username: string };
type CityNote = { id: number; content: string };
type City = { id: number; pnname: string; notes: CityNote[] } | null;

const props = defineProps<{
    mode: 'open' | 'unassigned' | 'history' | 'city' | 'admin';
    myTickets: TicketRow[];
    admins: Admin[];
    AllTicketsCount?: number;
    UnassignedTicketsCount?: number;
    myTicketsCount?: number;
    ticketCounts?: Record<number, number>;
    cityTicketCounts?: Record<string, number>;
    activeForwardingCount?: number;
    dueForwardingCount?: number;
    dueTerminationCount?: number;
    city?: City;
    userId?: number | null;
    done?: number;
}>();

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('Super_Admin'));

defineOptions({
    layout: (h_: typeof h, pageVNode: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tickets', href: '#' }];
        return h_(AppLayout, { breadcrumbs }, () => pageVNode);
    },
});

const pageTitle = computed(() => {
    switch (props.mode) {
        case 'open':
            return 'Offene Tickets';
        case 'unassigned':
            return 'Nicht zugewiesene Tickets';
        case 'history':
            return 'Ticket-Verlauf';
        case 'city':
            return props.city ? `Tickets - ${props.city.pnname}` : 'Tickets nach Stadt';
        case 'admin':
            return 'Zugewiesene Tickets';
        default:
            return 'Tickets';
    }
});

// Replaces the old Blade's `URL::current() == route(...)` chain, which only
// ever matched three of the five modes (city/admin always rendered a blank
// heading there) - now every mode gets a real heading since it's known
// explicitly instead of guessed from the current URL.
const heading = computed(() => {
    switch (props.mode) {
        case 'open':
            return `Anzahl offener Tickets: ${props.AllTicketsCount ?? 0}`;
        case 'unassigned':
            return `Anzahl Nicht zugewiesener Tickets: ${props.UnassignedTicketsCount ?? 0}`;
        case 'history':
            return `Anzahl Erledigter Tickets: ${props.done ?? 0}`;
        case 'city':
            return props.city ? `Tickets in ${props.city.pnname}` : 'Tickets nach Stadt';
        case 'admin': {
            const admin = props.admins.find((a) => a.id === props.userId);
            return admin ? `Zugewiesene Tickets an ${admin.name}` : 'Zugewiesene Tickets';
        }
        default:
            return '';
    }
});

const showForwardingAlerts = computed(() => props.mode === 'open' || props.mode === 'unassigned');

const cityEntries = computed(() => Object.entries(props.cityTicketCounts ?? {}));

// --- city PDF dropdown (left nav, Super_Admin only) ---

const pdfCity = ref('');
function onPdfCityChange() {
    if (pdfCity.value) {
        window.location.href = pdfCity.value;
    }
}

// --- per-row ticket assignment ---

const myTickets = ref<TicketRow[]>(props.myTickets.map((t) => ({ ...t })));

async function onAssignChange(ticket: TicketRow) {
    try {
        await axios.post('/ticket/assignTo', { assignedTo: ticket.assignedTo, ticket_id: ticket.id });
        toast.success(ticket.assignedTo ? 'Zugewiesen!' : 'Nicht zugewiesen!');
    } catch {
        toast.error('Zuweisung fehlgeschlagen.');
    }
}

function timeAgo(value: string): string {
    const diffMin = Math.round((Date.now() - new Date(value).getTime()) / 60000);
    if (diffMin < 60) return `vor ${Math.max(diffMin, 0)} Min.`;
    const diffHour = Math.round(diffMin / 60);
    if (diffHour < 24) return `vor ${diffHour} Std.`;
    return `vor ${Math.round(diffHour / 24)} Tagen`;
}
function formatDateShort(value: string): string {
    return new Date(value).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// --- Beschreibung preview + "view full" modal ---

function stripHtml(html: string | null): string {
    if (!html) return '';
    return html
        .replace(/<[^>]*>/g, ' ')
        .replace(/&nbsp;/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}
function notesPreview(html: string | null): string {
    const text = stripHtml(html);
    if (!text) return '—';
    return text.length > 50 ? `${text.slice(0, 50)}…` : text;
}

const notesModalOpen = ref(false);
const notesModalTicket = ref<TicketRow | null>(null);
function openNotesModal(ticket: TicketRow) {
    notesModalTicket.value = ticket;
    notesModalOpen.value = true;
}
function closeNotesModal() {
    notesModalOpen.value = false;
}

const TICKET_COLUMNS: DataTableColumn<TicketRow>[] = [
    { key: 'status', value: (t) => ticketStatusMeta(t.ticket_status_id).label },
    { key: 'assignedTo', sortable: false, searchable: false },
    { key: 'creator', value: (t) => t.subUser?.username ?? '—' },
    { key: 'problem_type' },
    { key: 'device', value: (t) => t.invitem?.gname ?? '' },
    { key: 'tel', value: (t) => (t.custom_tel_number ? `${t.tel_number ?? ''} | ${t.custom_tel_number}` : (t.tel_number ?? '')) },
    { key: 'standort', value: (t) => t.subUser?.ort ?? '' },
    { key: 'priority', value: (t) => ticketPriorityLabel(t.priority_id) },
    { key: 'created_at', searchable: false },
    { key: 'notizen', value: (t) => stripHtml(t.notizen), sortable: false },
];
const {
    search,
    sortKey,
    sortDir,
    toggleSort,
    pageSize,
    page: tablePage,
    pagedRows,
    total,
    pageCount,
    rangeFrom,
    rangeTo,
} = useDataTable(myTickets, TICKET_COLUMNS);

// --- city-mode notes footer (add / inline edit / delete) ---
// The old delete button only ever worked on a *double*-click (no confirm
// dialog either) - kept exactly as-is rather than adding a confirmation
// that was never there.

const cityNotes = ref<CityNote[]>(props.city?.notes ? [...props.city.notes] : []);
const newNoteContent = ref('');
const editingNoteId = ref<number | null>(null);
const editingContent = ref('');

async function addNote() {
    const content = newNoteContent.value.trim();
    if (!content || !props.city) return;
    try {
        const { data } = await axios.post('/notes', { content, place_id: props.city.id });
        cityNotes.value.push({ id: data.note.id, content: data.note.content });
        newNoteContent.value = '';
    } catch {
        toast.error('Hinweis konnte nicht hinzugefügt werden.');
    }
}

function startEditNote(note: CityNote) {
    editingNoteId.value = note.id;
    editingContent.value = note.content;
}
function cancelEditNote() {
    editingNoteId.value = null;
}
async function saveNote(note: CityNote) {
    try {
        await axios.patch(`/notes/${note.id}`, { content: editingContent.value });
        note.content = editingContent.value;
        editingNoteId.value = null;
    } catch {
        toast.error('Hinweis konnte nicht gespeichert werden.');
    }
}
async function deleteNote(note: CityNote) {
    try {
        await axios.delete(`/notes/${note.id}`);
        cityNotes.value = cityNotes.value.filter((n) => n.id !== note.id);
    } catch {
        toast.error('Hinweis konnte nicht gelöscht werden.');
    }
}
</script>

<template>
    <Head :title="pageTitle" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <!-- Top navbar -->
        <div class="bg-card text-card-foreground flex flex-col gap-3 rounded-xl border p-3 shadow-sm lg:flex-row lg:flex-wrap lg:items-center lg:justify-between">
            <div v-if="isSuperAdmin" class="flex flex-wrap items-center gap-2">
                <Link href="/opentickets" class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm" title="Offen">
                    Offen
                    <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs">{{ AllTicketsCount ?? 0 }}</span>
                </Link>
                <Link
                    href="/unassignedtickets"
                    class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
                    title="Nicht zugewiesen"
                >
                    Nicht zugewiesen
                    <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs">{{ UnassignedTicketsCount ?? 0 }}</span>
                </Link>
                <Link href="/usertickets" class="hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm" title="Eigene Tickets">
                    Eigene Tickets
                    <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs">{{ myTicketsCount ?? 0 }}</span>
                </Link>
                <select
                    v-if="cityEntries.length"
                    v-model="pdfCity"
                    class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                    @change="onPdfCityChange"
                >
                    <option value="" disabled>Stadt wählen (PDF)</option>
                    <option v-for="[cityName, count] in cityEntries" :key="cityName" :value="`/city/${cityName}/tickets/pdf`">
                        {{ cityName }} ({{ count }})
                    </option>
                </select>
            </div>

            <nav v-if="cityEntries.length" class="flex flex-wrap items-center gap-2">
                <Link
                    v-for="[cityName, count] in cityEntries"
                    :key="cityName"
                    :href="`/tickets/city/${cityName}`"
                    class="hover:bg-accent inline-flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-xs"
                >
                    {{ cityName }}
                    <span class="rounded-full bg-black/10 px-1.5">{{ count }}</span>
                </Link>
            </nav>

            <nav class="flex flex-wrap items-center gap-2">
                <Link
                    v-for="admin in admins"
                    :key="admin.id"
                    :href="`/tickets/${admin.id}`"
                    class="hover:bg-accent inline-flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-xs"
                >
                    {{ admin.name }}
                    <span class="rounded-full bg-black/10 px-1.5">{{ ticketCounts?.[admin.id] ?? 0 }}</span>
                </Link>
            </nav>
        </div>

        <!-- Main card -->
        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b p-4">
                <h3 class="font-semibold">{{ heading }}</h3>

                <div v-if="showForwardingAlerts" class="flex flex-wrap items-center gap-2">
                    <Link href="/dashboard" class="border-border hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm">
                        Aktiv
                        <span
                            class="font-bold"
                            :class="(activeForwardingCount ?? 0) > 0 ? 'animate-pulse text-amber-600' : ''"
                        >
                            {{ activeForwardingCount ?? 0 }}
                        </span>
                        Weiterleitungen
                    </Link>
                    <Link
                        v-if="(dueForwardingCount ?? 0) > 0"
                        href="/dashboard"
                        class="bg-destructive text-destructive-foreground inline-flex h-9 animate-pulse items-center gap-1.5 rounded-md px-3 text-sm"
                    >
                        Endet/Überfällig
                        <span class="font-bold">{{ dueForwardingCount }}</span>
                    </Link>
                    <Link
                        v-if="(dueTerminationCount ?? 0) > 0"
                        href="/dashboard"
                        class="bg-destructive text-destructive-foreground inline-flex h-9 animate-pulse items-center gap-1.5 rounded-md px-3 text-sm"
                    >
                        überfällige Kündigungen
                        <span class="font-bold">{{ dueTerminationCount }}</span>
                    </Link>
                </div>
            </div>

            <div class="p-3">
                <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Suchen..." />
            </div>

            <div class="overflow-x-auto p-1">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="" sort-key="status" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('status')" />
                            <TableHeadCell label="Zuweisen" />
                            <TableHeadCell label="Erstellt von" sort-key="creator" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('creator')" />
                            <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('problem_type')" />
                            <TableHeadCell label="Das Gerät" sort-key="device" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('device')" />
                            <TableHeadCell label="Tel" sort-key="tel" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('tel')" />
                            <TableHeadCell label="Standort" sort-key="standort" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('standort')" />
                            <TableHeadCell label="Priorität" sort-key="priority" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('priority')" />
                            <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('created_at')" />
                            <TableHeadCell label="Beschreibung" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="ticket in pagedRows" :key="ticket.id">
                            <td class="p-3">
                                <component
                                    :is="ticketStatusMeta(ticket.ticket_status_id).icon"
                                    class="h-4 w-4"
                                    :style="{ color: ticketStatusMeta(ticket.ticket_status_id).color }"
                                    :title="ticketStatusMeta(ticket.ticket_status_id).label"
                                />
                            </td>
                            <td class="p-3">
                                <select
                                    v-model="ticket.assignedTo"
                                    class="border-input bg-background h-8 rounded-md border px-2 text-xs"
                                    @change="onAssignChange(ticket)"
                                >
                                    <option :value="null">Zuweisen</option>
                                    <option v-for="admin in admins" :key="admin.id" :value="admin.id">{{ admin.username }}</option>
                                </select>
                            </td>
                            <td class="p-3">
                                <Link :href="`/ticket/${ticket.id}`" class="text-primary hover:underline">{{ ticket.subUser?.username ?? '—' }}</Link>
                            </td>
                            <td class="p-3">
                                <Link :href="`/ticket/${ticket.id}`" class="hover:underline">{{ ticket.problem_type }}</Link>
                            </td>
                            <td class="p-3 font-semibold">{{ ticket.invitem?.gname ?? '' }}</td>
                            <td class="p-3">
                                {{ ticket.tel_number }}
                                <template v-if="ticket.custom_tel_number"> | {{ ticket.custom_tel_number }}</template>
                            </td>
                            <td class="p-3">{{ ticket.subUser?.ort }}</td>
                            <td class="p-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="ticketPriorityBadgeClass(ticket.priority_id)">
                                    {{ ticketPriorityLabel(ticket.priority_id) }}
                                </span>
                            </td>
                            <td class="p-3">
                                {{ timeAgo(ticket.created_at) }}
                                <p class="text-muted-foreground text-xs">{{ formatDateShort(ticket.created_at) }}</p>
                            </td>
                            <td class="max-w-[220px] p-3">
                                <div class="flex items-center gap-2">
                                    <span class="truncate">{{ notesPreview(ticket.notizen) }}</span>
                                    <button
                                        v-if="ticket.notizen"
                                        type="button"
                                        class="text-primary shrink-0 text-xs whitespace-nowrap hover:underline"
                                        @click="openNotesModal(ticket)"
                                    >
                                        Anzeigen
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="10" class="text-muted-foreground p-8 text-center">
                                {{ search ? 'Nichts gefunden.' : 'Keine Tickets vorhanden.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <TablePagination
                :page="tablePage"
                :page-count="pageCount"
                :range-from="rangeFrom"
                :range-to="rangeTo"
                :total="total"
                item-label="Tickets"
                @update:page="tablePage = $event"
            />

            <!-- City-mode notes footer -->
            <div v-if="mode === 'city' && city" class="border-t p-4">
                <h5 class="mb-2 font-semibold">{{ city.pnname }}</h5>
                <ol class="flex flex-col gap-1">
                    <li v-for="note in cityNotes" :key="note.id" class="flex items-center justify-between gap-2 rounded-md border px-3 py-2 text-sm">
                        <template v-if="editingNoteId === note.id">
                            <input v-model="editingContent" type="text" class="border-input bg-background h-8 flex-1 rounded-md border px-2 text-sm" />
                            <button type="button" class="text-primary text-xs font-medium" @click="saveNote(note)">Speichern</button>
                            <button type="button" class="text-muted-foreground text-xs" @click="cancelEditNote">Abbrechen</button>
                        </template>
                        <template v-else>
                            <span @dblclick="deleteNote(note)" title="Doppelklick zum Löschen">{{ note.content }}</span>
                            <button type="button" class="text-muted-foreground hover:text-primary text-xs" @click="startEditNote(note)">Bearbeiten</button>
                        </template>
                    </li>
                    <li v-if="!cityNotes.length" class="text-muted-foreground text-sm">Keine Hinweise vorhanden.</li>
                </ol>
                <div class="mt-3 flex gap-2">
                    <input
                        v-model="newNoteContent"
                        type="text"
                        placeholder="neuer Hinweis?"
                        class="border-input bg-background h-9 flex-1 rounded-md border px-3 text-sm"
                        @keydown.enter="addNote"
                    />
                    <button type="button" class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm" @click="addNote">
                        Hinzufügen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Beschreibung "view full" modal -->
    <Transition enter-active-class="transition-opacity duration-150" leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div
            v-if="notesModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="closeNotesModal"
        >
            <div class="bg-card text-card-foreground flex max-h-[80vh] w-full max-w-lg flex-col rounded-xl border shadow-xl">
                <div class="flex items-center justify-between border-b p-4">
                    <h4 class="font-semibold">Beschreibung{{ notesModalTicket ? ` – ${notesModalTicket.problem_type}` : '' }}</h4>
                    <button type="button" class="text-muted-foreground hover:text-foreground" @click="closeNotesModal">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="overflow-y-auto p-4 text-sm">
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <div v-html="notesModalTicket?.notizen"></div>
                </div>
            </div>
        </div>
    </Transition>
</template>
