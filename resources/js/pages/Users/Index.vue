<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2 } from '@lucide/vue';
import { computed, h } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Retrofitted 2026-09-17 to the shared sortable/searchable/paginated table
 * standard (see useDataTable.ts) - this page already had a plain client-side
 * search box (no DataTables sorting/paging ever existed here, see the
 * 2026-09-15 status update), so this swaps that hand-rolled `search`
 * ref/`filteredUsers` computed for the shared composable and adds sorting +
 * a page-size picker + pagination on top, rather than changing anything
 * about how the data arrives (still the full `User::with('roles')->get()`
 * array, no controller change needed).
 */

type UserRow = {
    id: number;
    name: string;
    email: string;
    roles: { name: string }[];
};

const props = defineProps<{
    users: UserRow[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Benutzerverwaltung', href: '/users' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const COLUMNS: DataTableColumn<UserRow>[] = [
    { key: 'name' },
    { key: 'email' },
    { key: 'status', value: (u) => (u.roles.length ? 1 : 0) },
    { key: 'roles', value: (u) => u.roles.map((r) => r.name).join(', ') },
    { key: 'actions', sortable: false, searchable: false },
];
const users = computed(() => props.users);
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(users, COLUMNS);

function deleteUser(user: UserRow) {
    if (!confirm(`Benutzer "${user.name}" wirklich löschen?`)) return;

    router.delete(`/users/${user.id}`, { preserveScroll: true });
}

function rowActions(user: UserRow): RowAction[] {
    return [
        { icon: Eye, label: 'Ansehen', href: `/users/${user.id}` },
        { icon: Pencil, label: 'Bearbeiten', href: `/users/${user.id}/edit` },
        {
            icon: Trash2,
            label: 'Löschen',
            variant: 'destructive',
            onClick: () => deleteUser(user),
        },
    ];
}
</script>

<template>
    <Head title="Benutzerverwaltung" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold">Benutzerverwaltung</h1>
            <Link
                href="/users/create"
                class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm"
            >
                + Neuer Benutzer
            </Link>
        </div>

        <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Name oder Email suchen..." />

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="Name" sort-key="name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('name')" />
                            <TableHeadCell label="Email" sort-key="email" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('email')" />
                            <TableHeadCell label="Status" sort-key="status" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('status')" />
                            <TableHeadCell label="Rollen" sort-key="roles" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('roles')" />
                            <TableHeadCell label="Aktion" align="right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="user in pagedRows" :key="user.id">
                            <td class="p-3">{{ user.name }}</td>
                            <td class="p-3">{{ user.email }}</td>
                            <td class="p-3">
                                <span
                                    class="inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="user.roles.length ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                >
                                    <span class="h-2 w-2 rounded-full" :class="user.roles.length ? 'bg-green-500' : 'bg-red-500'" />
                                    {{ user.roles.length ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.name"
                                        class="bg-primary/10 text-primary rounded-full px-2 py-0.5 text-xs font-medium"
                                    >
                                        {{ role.name }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-3">
                                <RowActions :actions="rowActions(user)" />
                            </td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="5" class="text-muted-foreground p-3 text-center">Keine Benutzer gefunden.</td>
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
                item-label="Benutzern"
                @update:page="page = $event"
            />
        </div>
    </div>
</template>
