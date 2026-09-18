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
 * Converted from resources/views/handwerk/my_handwerks.blade.php
 * (HandwerkController@myHandwerks, route /my-handwerks-tickets, name
 * my_handwerks). The old view was dead/never-finished scaffolding - it
 * referenced `$handwerk->title`/`$handwerk->description`, fields that don't
 * exist on the Handwerk model (see app/Handwerk.php - the real columns are
 * problem_type/submitter_name/notizen/etc., same as every other Handwerk
 * page), so this page would have fatal-errored the moment anyone reached it.
 * Rebuilt as a real "my submitted tickets" list using the same table shape
 * as Handwerk/City.vue and Handwerk/History.vue (its soft-deleted/"erledigt"
 * counterpart), instead of trying to patch the broken field names.
 *
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts).
 */

type LocationInfo = { address: string | null } | null;
type RoomInfo = { rname: string | null; altrname: string | null } | null;

type HandwerkRow = {
    id: number;
    problem_type: string;
    location: LocationInfo;
    room: RoomInfo;
    created_at: string;
};

const props = defineProps<{
    handwerks: HandwerkRow[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
            { title: 'Meine Tickets', href: '/my-handwerks-tickets' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const rtf = new Intl.RelativeTimeFormat('de', { numeric: 'auto' });

function timeAgo(value: string): string {
    const diffMs = new Date(value).getTime() - Date.now();
    const diffMin = Math.round(diffMs / 60000);
    if (Math.abs(diffMin) < 60) return rtf.format(diffMin, 'minute');
    const diffHour = Math.round(diffMin / 60);
    if (Math.abs(diffHour) < 24) return rtf.format(diffHour, 'hour');
    const diffDay = Math.round(diffHour / 24);
    if (Math.abs(diffDay) < 30) return rtf.format(diffDay, 'day');
    return rtf.format(Math.round(diffDay / 30), 'month');
}

const HANDWERK_COLUMNS: DataTableColumn<HandwerkRow>[] = [
    { key: 'problem_type' },
    { key: 'location.address' },
    { key: 'room.rname' },
    { key: 'created_at', searchable: false },
];
const handwerks = computed(() => props.handwerks);
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(
    handwerks,
    HANDWERK_COLUMNS,
);
</script>

<template>
    <Head title="Meine Handwerkaufgaben" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Meine Handwerkaufgaben</h1>

        <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Suchen..." />

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('problem_type')" />
                            <TableHeadCell label="Standort" sort-key="location.address" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('location.address')" />
                            <TableHeadCell label="Raum" sort-key="room.rname" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('room.rname')" />
                            <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="sortKey" :direction="sortDir" align="right" @sort="toggleSort('created_at')" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="handwerk in pagedRows" :key="handwerk.id">
                            <td class="p-3">
                                <Link :href="`/handwerk/${handwerk.id}`" class="text-primary hover:underline">
                                    {{ handwerk.problem_type }}
                                </Link>
                            </td>
                            <td class="p-3">{{ handwerk.location?.address }}</td>
                            <td class="p-3">{{ handwerk.room?.rname }} {{ handwerk.room?.altrname }}</td>
                            <td class="p-3 text-right">{{ timeAgo(handwerk.created_at) }}</td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="4" class="text-muted-foreground p-6 text-center">
                                {{ search ? 'Nichts gefunden.' : 'Keine offenen Tickets vorhanden.' }}
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
