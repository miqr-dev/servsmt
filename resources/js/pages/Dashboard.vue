<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Power, PowerOff, Trash2 } from '@lucide/vue';
import { computed, h, reactive, ref } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * First page converted from the old AdminLTE shell (resources/views/wilkommen.blade.php).
 * Server-side gating is unchanged: LicenseController@index only renders this for
 * Super_Admin/HR, and only Super_Admin additionally sees the forwarding cards -
 * see the isSuperAdmin check below, which mirrors the old @if(hasRole('Super_Admin')).
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts) - all 4 tables here already arrive as full
 * arrays, so this needed no controller change, just wiring each one through
 * its own useDataTable() instance (they're 4 independent tables, not one).
 * Date columns (valid/exit/forward_required_at/forward_to_at/
 * forward_removed_at) are marked `searchable: false` - they still sort
 * correctly (on the raw ISO value, not the dd.mm.yyyy display string), but
 * matching the free-text box against a raw ISO timestamp isn't something a
 * user would ever type, so leaving them out of search avoids confusing
 * "why doesn't typing today's date find anything" results.
 */

type LicenseRow = {
    id: number;
    name: string;
    where: string | null;
    comment: string | null;
    valid: string | null;
    version: string | null;
};

type TerminationRow = {
    id: number;
    name: string;
    exit: string | null;
    is_active: boolean;
    location: string | null;
    occupation: string | null;
};

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
    licenses: LicenseRow[];
    terminations: TerminationRow[];
    activeEmailForwardingTickets: ForwardingTicketRow[];
    historyEmailForwardingTickets: ForwardingTicketRow[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Dashboard', href: '/dashboard' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('Super_Admin'));
const isHrOrSuperAdmin = computed(
    () => roles.value.includes('HR') || isSuperAdmin.value,
);

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

const now = Date.now();
const WEEK_MS = 7 * 24 * 60 * 60 * 1000;
const MONTH_MS = 30 * 24 * 60 * 60 * 1000;

function dateColorClass(value: string | null, inactive = false): string {
    if (inactive) return 'font-semibold text-neutral-500';
    if (!value) return '';

    const diff = new Date(value).getTime() - now;

    if (diff <= WEEK_MS) return 'font-semibold text-red-600 dark:text-red-400';
    if (diff <= MONTH_MS)
        return 'font-semibold text-orange-500 dark:text-orange-400';

    return 'font-semibold text-green-600 dark:text-green-400';
}

function deleteLicense(license: LicenseRow) {
    if (!confirm(`"${license.name}" wirklich löschen?`)) return;

    router.delete(`/licenses/${license.id}`, { preserveScroll: true });
}

function deleteTermination(termination: TerminationRow) {
    if (!confirm(`"${termination.name}" wirklich löschen?`)) return;

    router.delete(`/terminations/${termination.id}`, { preserveScroll: true });
}

function toggleTermination(termination: TerminationRow) {
    router.post(
        `/terminations/${termination.id}/toggle`,
        {},
        { preserveScroll: true },
    );
}

function markForwardingRemoved(ticket: ForwardingTicketRow) {
    router.post(
        `/ticket/${ticket.id}/forwarding-removed`,
        {},
        { preserveScroll: true },
    );
}

function licenseActions(license: LicenseRow): RowAction[] {
    return [
        {
            icon: Pencil,
            label: 'Bearbeiten',
            href: `/licenses/${license.id}/edit`,
        },
        {
            icon: Trash2,
            label: 'Löschen',
            variant: 'destructive',
            onClick: () => deleteLicense(license),
        },
    ];
}

function terminationActions(termination: TerminationRow): RowAction[] {
    return [
        {
            icon: Pencil,
            label: 'Bearbeiten',
            href: `/terminations/${termination.id}/edit`,
        },
        {
            icon: termination.is_active ? PowerOff : Power,
            label: termination.is_active ? 'Deaktivieren' : 'Aktivieren',
            onClick: () => toggleTermination(termination),
        },
        {
            icon: Trash2,
            label: 'Löschen',
            variant: 'destructive',
            onClick: () => deleteTermination(termination),
        },
    ];
}

// --- Licenses table ---
const LICENSE_COLUMNS: DataTableColumn<LicenseRow>[] = [
    { key: 'name' },
    { key: 'where' },
    { key: 'comment' },
    { key: 'valid', searchable: false },
    { key: 'version' },
    { key: 'actions', sortable: false, searchable: false },
];
const licenses = computed(() => props.licenses);
const licenseTable = reactive(useDataTable(licenses, LICENSE_COLUMNS));

// --- Terminations table ---
const TERMINATION_COLUMNS: DataTableColumn<TerminationRow>[] = [
    { key: 'name' },
    { key: 'exit', searchable: false },
    { key: 'location' },
    { key: 'actions', sortable: false, searchable: false },
];
const terminations = computed(() => props.terminations);
const terminationTable = reactive(useDataTable(terminations, TERMINATION_COLUMNS));

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
const activeForwarding = computed(() => props.activeEmailForwardingTickets);
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
const historyForwarding = computed(() => props.historyEmailForwardingTickets);
const historyForwardingTable = reactive(useDataTable(historyForwarding, HISTORY_FORWARDING_COLUMNS));
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div v-if="isHrOrSuperAdmin" class="grid gap-4 lg:grid-cols-3">
            <!-- Licenses -->
            <div
                class="bg-card text-card-foreground rounded-xl border shadow-sm lg:col-span-2"
            >
                <div class="flex flex-wrap items-center justify-between gap-2 border-b p-4">
                    <h3 class="font-semibold">Lizenzen</h3>
                    <Link
                        href="/licenses/create"
                        class="border-primary text-primary hover:bg-primary hover:text-primary-foreground inline-flex h-8 items-center rounded-md border px-3 text-sm"
                    >
                        + Neu
                    </Link>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="licenseTable.search" v-model:page-size="licenseTable.pageSize" search-placeholder="Lizenz suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Lizenzname" sort-key="name" :active-key="licenseTable.sortKey" :direction="licenseTable.sortDir" @sort="licenseTable.toggleSort('name')" />
                                <TableHeadCell label="Wo" sort-key="where" :active-key="licenseTable.sortKey" :direction="licenseTable.sortDir" @sort="licenseTable.toggleSort('where')" />
                                <TableHeadCell label="Bemerkung" sort-key="comment" :active-key="licenseTable.sortKey" :direction="licenseTable.sortDir" @sort="licenseTable.toggleSort('comment')" />
                                <TableHeadCell label="Gültig" sort-key="valid" :active-key="licenseTable.sortKey" :direction="licenseTable.sortDir" @sort="licenseTable.toggleSort('valid')" />
                                <TableHeadCell label="Version" sort-key="version" :active-key="licenseTable.sortKey" :direction="licenseTable.sortDir" @sort="licenseTable.toggleSort('version')" />
                                <TableHeadCell label="Ändern" align="right" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="license in licenseTable.pagedRows" :key="license.id">
                                <td class="p-3">{{ license.name }}</td>
                                <td class="p-3">{{ license.where }}</td>
                                <td class="p-3">{{ license.comment }}</td>
                                <td
                                    class="p-3"
                                    :class="dateColorClass(license.valid)"
                                >
                                    {{ formatDate(license.valid) }}
                                </td>
                                <td class="p-3">{{ license.version }}</td>
                                <td class="p-3">
                                    <RowActions :actions="licenseActions(license)" />
                                </td>
                            </tr>
                            <tr v-if="!licenseTable.pagedRows.length">
                                <td
                                    colspan="6"
                                    class="text-muted-foreground p-3 text-center"
                                >
                                    {{ licenseTable.search ? 'Keine Lizenzen gefunden.' : 'Keine Lizenzen vorhanden.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :page="licenseTable.page"
                    :page-count="licenseTable.pageCount"
                    :range-from="licenseTable.rangeFrom"
                    :range-to="licenseTable.rangeTo"
                    :total="licenseTable.total"
                    item-label="Lizenzen"
                    @update:page="licenseTable.page = $event"
                />
            </div>

            <!-- Terminations -->
            <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="flex items-center justify-between border-b p-4">
                    <h3 class="font-semibold">Kündigungen</h3>
                    <div class="flex gap-2">
                        <Link
                            href="/terminations/history"
                            class="text-muted-foreground hover:text-foreground text-sm"
                            >Verlauf</Link
                        >
                        <Link
                            href="/terminations/create"
                            class="border-primary text-primary hover:bg-primary hover:text-primary-foreground inline-flex h-8 items-center rounded-md border px-3 text-sm"
                            >+ Neu</Link
                        >
                    </div>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="terminationTable.search" v-model:page-size="terminationTable.pageSize" search-placeholder="Kündigung suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Name" sort-key="name" :active-key="terminationTable.sortKey" :direction="terminationTable.sortDir" @sort="terminationTable.toggleSort('name')" />
                                <TableHeadCell label="Austritt" sort-key="exit" :active-key="terminationTable.sortKey" :direction="terminationTable.sortDir" @sort="terminationTable.toggleSort('exit')" />
                                <TableHeadCell label="Standort" sort-key="location" :active-key="terminationTable.sortKey" :direction="terminationTable.sortDir" @sort="terminationTable.toggleSort('location')" />
                                <TableHeadCell label="Aktion" align="right" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="termination in terminationTable.pagedRows"
                                :key="termination.id"
                            >
                                <td class="p-3">{{ termination.name }}</td>
                                <td
                                    class="p-3"
                                    :class="
                                        dateColorClass(
                                            termination.exit,
                                            !termination.is_active,
                                        )
                                    "
                                >
                                    {{ formatDate(termination.exit) }}
                                </td>
                                <td class="p-3">{{ termination.location }}</td>
                                <td class="p-3">
                                    <RowActions
                                        :actions="terminationActions(termination)"
                                    />
                                </td>
                            </tr>
                            <tr v-if="!terminationTable.pagedRows.length">
                                <td
                                    colspan="4"
                                    class="text-muted-foreground p-3 text-center"
                                >
                                    {{ terminationTable.search ? 'Keine Kündigungen gefunden.' : 'Keine Kündigungen vorhanden.' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <TablePagination
                    :page="terminationTable.page"
                    :page-count="terminationTable.pageCount"
                    :range-from="terminationTable.rangeFrom"
                    :range-to="terminationTable.rangeTo"
                    :total="terminationTable.total"
                    item-label="Kündigungen"
                    @update:page="terminationTable.page = $event"
                />
            </div>
        </div>

        <!-- Email forwarding - Super_Admin only, matches the old @if(hasRole('Super_Admin')) -->
        <template v-if="isSuperAdmin">
            <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="flex items-center justify-between border-b p-4">
                    <h3 class="font-semibold">
                        E-Mail-Weiterleitungen (Aktiv)
                    </h3>
                    <button
                        type="button"
                        class="border-border hover:bg-accent inline-flex h-8 items-center rounded-md border px-3 text-sm"
                        @click="showForwardingHistory = !showForwardingHistory"
                    >
                        {{
                            showForwardingHistory
                                ? 'Verlauf ausblenden'
                                : 'Verlauf anzeigen'
                        }}
                    </button>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="activeForwardingTable.search" v-model:page-size="activeForwardingTable.pageSize" search-placeholder="Weiterleitung suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Von" sort-key="from" :active-key="activeForwardingTable.sortKey" :direction="activeForwardingTable.sortDir" @sort="activeForwardingTable.toggleSort('from')" />
                                <TableHeadCell label="An" sort-key="on" :active-key="activeForwardingTable.sortKey" :direction="activeForwardingTable.sortDir" @sort="activeForwardingTable.toggleSort('on')" />
                                <TableHeadCell label="Von Datum" sort-key="forward_required_at" :active-key="activeForwardingTable.sortKey" :direction="activeForwardingTable.sortDir" @sort="activeForwardingTable.toggleSort('forward_required_at')" />
                                <TableHeadCell label="Bis Datum" sort-key="forward_to_at" :active-key="activeForwardingTable.sortKey" :direction="activeForwardingTable.sortDir" @sort="activeForwardingTable.toggleSort('forward_to_at')" />
                                <TableHeadCell label="Status" sort-key="status" :active-key="activeForwardingTable.sortKey" :direction="activeForwardingTable.sortDir" @sort="activeForwardingTable.toggleSort('status')" />
                                <TableHeadCell label="Erstellt von" sort-key="submitter" :active-key="activeForwardingTable.sortKey" :direction="activeForwardingTable.sortDir" @sort="activeForwardingTable.toggleSort('submitter')" />
                                <TableHeadCell label="Aktion" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="ticket in activeForwardingTable.pagedRows"
                                :key="ticket.id"
                            >
                                <td class="p-3">
                                    {{
                                        personLabel(
                                            ticket.forwardFromUser,
                                            ticket.forward_from,
                                        )
                                    }}
                                </td>
                                <td class="p-3">
                                    {{
                                        personLabel(
                                            ticket.forwardOnUser,
                                            ticket.forward_on,
                                        )
                                    }}
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
                                <td
                                    colspan="7"
                                    class="text-muted-foreground p-3 text-center"
                                >
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

            <div
                v-if="showForwardingHistory"
                class="bg-card text-card-foreground rounded-xl border shadow-sm"
            >
                <div class="border-b p-4">
                    <h3 class="font-semibold">
                        E-Mail-Weiterleitungen (Verlauf)
                    </h3>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="historyForwardingTable.search" v-model:page-size="historyForwardingTable.pageSize" search-placeholder="Verlauf suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Von" sort-key="from" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('from')" />
                                <TableHeadCell label="An" sort-key="on" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('on')" />
                                <TableHeadCell label="Von Datum" sort-key="forward_required_at" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('forward_required_at')" />
                                <TableHeadCell label="Bis Datum" sort-key="forward_to_at" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('forward_to_at')" />
                                <TableHeadCell label="Erstellt von" sort-key="submitter" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('submitter')" />
                                <TableHeadCell label="Entfernt am" sort-key="forward_removed_at" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('forward_removed_at')" />
                                <TableHeadCell label="Entfernt von" sort-key="removedBy" :active-key="historyForwardingTable.sortKey" :direction="historyForwardingTable.sortDir" @sort="historyForwardingTable.toggleSort('removedBy')" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="ticket in historyForwardingTable.pagedRows"
                                :key="ticket.id"
                            >
                                <td class="p-3">
                                    {{
                                        personLabel(
                                            ticket.forwardFromUser,
                                            ticket.forward_from,
                                        )
                                    }}
                                </td>
                                <td class="p-3">
                                    {{
                                        personLabel(
                                            ticket.forwardOnUser,
                                            ticket.forward_on,
                                        )
                                    }}
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
                                    {{
                                        personLabel(
                                            ticket.forwardRemovedByUser,
                                            null,
                                        )
                                    }}
                                </td>
                            </tr>
                            <tr v-if="!historyForwardingTable.pagedRows.length">
                                <td
                                    colspan="7"
                                    class="text-muted-foreground p-3 text-center"
                                >
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

        <div
            v-if="!isHrOrSuperAdmin"
            class="text-muted-foreground rounded-xl border border-dashed p-8 text-center text-sm"
        >
            Kein Zugriff auf dieses Dashboard.
        </div>
    </div>
</template>
