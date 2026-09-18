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
 * standard (see useDataTable.ts) - was a server-paginated table
 * (RoleController@index used Role::paginate(10) + Pagination.vue). Roles
 * are a short list app-wide, so the full set now comes down as a plain
 * array and sort/search/paging all run client-side like every other
 * table in this migration.
 */

type Role = { id: number; name: string };

const props = defineProps<{
    roles: Role[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Rollen', href: '/roles' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const COLUMNS: DataTableColumn<Role>[] = [{ key: 'id' }, { key: 'name' }];
const roles = computed(() => props.roles);
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(roles, COLUMNS);

function deleteRole(role: Role) {
    if (!confirm(`Rolle "${role.name}" wirklich löschen?`)) return;

    router.delete(`/roles/${role.id}`, { preserveScroll: true });
}

function rowActions(role: Role): RowAction[] {
    return [
        { icon: Eye, label: 'Ansehen', href: `/roles/${role.id}` },
        { icon: Pencil, label: 'Bearbeiten', href: `/roles/${role.id}/edit` },
        {
            icon: Trash2,
            label: 'Löschen',
            variant: 'destructive',
            onClick: () => deleteRole(role),
        },
    ];
}
</script>

<template>
    <Head title="Rollenverwaltung" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Rollenverwaltung</h1>
            <div class="flex gap-2">
                <Link
                    href="/permissions/create"
                    class="border-primary text-primary hover:bg-primary hover:text-primary-foreground inline-flex h-9 items-center rounded-md border px-3 text-sm"
                >
                    + Neue Permission
                </Link>
                <Link
                    href="/roles/create"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm"
                >
                    + Neue Rolle
                </Link>
            </div>
        </div>

        <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Rolle suchen..." />

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="#" sort-key="id" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('id')" />
                            <TableHeadCell label="Rolle" sort-key="name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('name')" />
                            <TableHeadCell label="Aktion" align="right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="role in pagedRows" :key="role.id">
                            <td class="p-3">{{ role.id }}</td>
                            <td class="p-3">{{ role.name }}</td>
                            <td class="p-3">
                                <RowActions :actions="rowActions(role)" />
                            </td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="3" class="text-muted-foreground p-3 text-center">
                                {{ search ? 'Keine Rollen gefunden.' : 'Keine Rollen vorhanden.' }}
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
                item-label="Rollen"
                @update:page="page = $event"
            />
        </div>
    </div>
</template>
