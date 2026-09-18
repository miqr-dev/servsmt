<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, h } from 'vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/handwerk/handwerkHistory.blade.php
 * (HandwerkController@userhandwerkticketshistory, route
 * /usertHandwerkicketshistory, name handwerk.userhandwerkticketsdone). Lists
 * a user's own completed ("erledigt"/soft-deleted) Handwerk tickets. The old
 * Blade page's little left-hand "Handwerk Ordner" nav (Offene/Erledigte) is
 * reproduced below - "Offene" links out to the ticket.usertickets page
 * (Tickets/UserTickets.vue, converted separately - now a real Inertia
 * transition instead of the full-page fallback navigation this comment used
 * to describe), "Erledigte" is this page itself.
 *
 * Not linked from anywhere in the old app's sidebar/header either - added a
 * "Verlauf" link from Handwerk/Index.vue so it's actually reachable, same as
 * MyTickets.vue below.
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts).
 */

type LocationInfo = { address: string | null } | null;
type RoomInfo = { rname: string | null; altrname: string | null } | null;

type HandwerkRow = {
    id: number;
    problem_type: string;
    submitter_name: string;
    location: LocationInfo;
    room: RoomInfo;
    done_by: string | null;
    deleted_at: string | null;
    created_at: string;
    notizen: string | null;
};

const props = defineProps<{
    user: { ort: string | null };
    handwerkticketsdone: HandwerkRow[];
    myhandwerkTicketsCountCity: number;
    myhandwerkTicketsCount: number;
    handwerkticketsdoneCount: number;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
            { title: 'Verlauf', href: '/usertHandwerkicketshistory' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

function formatDate(value: string | null): string {
    if (!value) return '';
    return new Date(value).toLocaleDateString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

const HANDWERK_COLUMNS: DataTableColumn<HandwerkRow>[] = [
    { key: 'problem_type' },
    { key: 'submitter_name' },
    { key: 'location.address' },
    { key: 'room.rname' },
    { key: 'done_by' },
    { key: 'deleted_at', searchable: false },
    { key: 'created_at', searchable: false },
];
const handwerkticketsdone = computed(() => props.handwerkticketsdone);
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(
    handwerkticketsdone,
    HANDWERK_COLUMNS,
);
</script>

<template>
    <Head title="Handwerk Verlauf" />

    <div class="flex flex-1 flex-col gap-4 p-4 lg:grid lg:grid-cols-4 lg:gap-4">
        <div class="bg-card text-card-foreground rounded-xl border shadow-sm lg:col-span-1">
            <div class="border-b p-4">
                <h3 class="font-semibold">Handwerk Ordner</h3>
            </div>
            <nav class="flex flex-col gap-1 p-2">
                <Link
                    href="/usertickets"
                    class="hover:bg-accent flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium"
                >
                    Offene
                    <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs">
                        {{ myhandwerkTicketsCount }}
                    </span>
                </Link>
                <span
                    class="bg-accent flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium"
                >
                    Erledigte
                    <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs">
                        {{ handwerkticketsdoneCount }}
                    </span>
                </span>
            </nav>
        </div>

        <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-3">
            <h2 class="text-sm font-medium">
                Eigene offene Tickets:
                <span class="text-primary font-semibold">{{ myhandwerkTicketsCount }}</span>
                <span class="text-muted-foreground ml-4">
                    Erledigte Tickets in {{ user.ort }}:
                    <span class="text-primary font-semibold">{{ myhandwerkTicketsCountCity }}</span>
                </span>
            </h2>

            <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Suchen..." />

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('problem_type')" />
                            <TableHeadCell label="Ersteller" sort-key="submitter_name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('submitter_name')" />
                            <TableHeadCell label="Standort" sort-key="location.address" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('location.address')" />
                            <TableHeadCell label="Raum" sort-key="room.rname" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('room.rname')" />
                            <TableHeadCell label="Erledigt von" sort-key="done_by" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('done_by')" />
                            <TableHeadCell label="Erledigt am" sort-key="deleted_at" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('deleted_at')" />
                            <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="sortKey" :direction="sortDir" align="right" @sort="toggleSort('created_at')" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="ticket in pagedRows" :key="ticket.id">
                            <td class="p-3">
                                <Link :href="`/handwerk/${ticket.id}`" class="text-primary hover:underline">
                                    {{ ticket.problem_type }}
                                </Link>
                            </td>
                            <td class="p-3">{{ ticket.submitter_name }}</td>
                            <td class="p-3">{{ ticket.location?.address }}</td>
                            <td class="p-3">{{ ticket.room?.rname }} {{ ticket.room?.altrname }}</td>
                            <td class="p-3">{{ ticket.done_by }}</td>
                            <td class="p-3">{{ formatDate(ticket.deleted_at) }}</td>
                            <td class="p-3 text-right">{{ formatDate(ticket.created_at) }}</td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="7" class="text-muted-foreground p-6 text-center">
                                {{ search ? 'Nichts gefunden.' : 'Keine erledigten Tickets vorhanden.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <TablePagination
                :page="page"
                :page-count="pageCount"
                :range-from="rangeFrom"
                :range-to="rangeTo"
                :total="total"
                item-label="Tickets"
                @update:page="page = $event"
            />
        </div>
    </div>
</template>
