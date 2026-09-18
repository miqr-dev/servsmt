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
 * standard (see useDataTable.ts) - was server-paginated
 * (PermissionController@index used ->paginate(15) + Pagination.vue), same
 * as Roles/Index.vue.
 */

type Permission = {
    id: number;
    name: string;
    category: { id: number; name: string } | null;
};

const props = defineProps<{
    permissions: Permission[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Rollen', href: '/roles' },
            { title: 'Berechtigungen', href: '/permissions' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const COLUMNS: DataTableColumn<Permission>[] = [{ key: 'id' }, { key: 'name' }, { key: 'category.name' }];
const permissions = computed(() => props.permissions);
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(
    permissions,
    COLUMNS,
);

function deletePermission(permission: Permission) {
    if (!confirm(`Permission "${permission.name}" wirklich löschen?`)) return;

    router.delete(`/permissions/${permission.id}`, { preserveScroll: true });
}

function rowActions(permission: Permission): RowAction[] {
    return [
        { icon: Eye, label: 'Ansehen', href: `/permissions/${permission.id}` },
        {
            icon: Pencil,
            label: 'Bearbeiten',
            href: `/permissions/${permission.id}/edit`,
        },
        {
            icon: Trash2,
            label: 'Löschen',
            variant: 'destructive',
            onClick: () => deletePermission(permission),
        },
    ];
}
</script>

<template>
    <Head title="Berechtigungen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Berechtigungen</h1>
            <div class="flex gap-2">
                <Link
                    href="/roles"
                    class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                >
                    Rollen
                </Link>
                <Link
                    href="/permissions/create"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm"
                >
                    + Neue Permission
                </Link>
            </div>
        </div>

        <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Berechtigung suchen..." />

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="#" sort-key="id" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('id')" />
                            <TableHeadCell label="Name" sort-key="name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('name')" />
                            <TableHeadCell
                                label="Kategorie"
                                sort-key="category.name"
                                :active-key="sortKey"
                                :direction="sortDir"
                                @sort="toggleSort('category.name')"
                            />
                            <TableHeadCell label="Aktion" align="right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="permission in pagedRows" :key="permission.id">
                            <td class="p-3">{{ permission.id }}</td>
                            <td class="p-3">{{ permission.name }}</td>
                            <td class="p-3">{{ permission.category?.name ?? '—' }}</td>
                            <td class="p-3">
                                <RowActions :actions="rowActions(permission)" />
                            </td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="4" class="text-muted-foreground p-3 text-center">
                                {{ search ? 'Keine Berechtigungen gefunden.' : 'Keine Berechtigungen vorhanden.' }}
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
                item-label="Berechtigungen"
                @update:page="page = $event"
            />
        </div>
    </div>
</template>
