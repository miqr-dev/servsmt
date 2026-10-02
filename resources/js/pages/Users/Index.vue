<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Trash2, X } from '@lucide/vue';
import { computed, h, ref, watch } from 'vue';
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
 *
 * 2026-10-02: role filter ("Rolle: Alle / <role> / Ohne Rolle", with counts;
 * clicking a role badge filters by it too, kept in ?role= so the link can be
 * shared/bookmarked) and ID + Ort columns. The filter uses the roles really
 * assigned (Super_Admin is not listed under every role).
 */

type UserRow = {
    id: number;
    name: string;
    vorname?: string | null;
    username?: string | null;
    ort?: string | null;
    email: string;
    roles: { name: string }[];
};

const props = defineProps<{
    users: UserRow[];
    collectionOfRoles?: string[];
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

const fullName = (u: UserRow) => [u.vorname, u.name].filter(Boolean).join(' ');

// --- role filter ('' = alle, NO_ROLE = users without any role) ---
const NO_ROLE = '__none__';
const roleFilter = ref<string>(new URLSearchParams(window.location.search).get('role') ?? '');

const roleOptions = computed(() => {
    const counts = new Map<string, number>();
    for (const r of props.collectionOfRoles ?? []) counts.set(r, 0);
    for (const u of props.users) for (const r of u.roles) counts.set(r.name, (counts.get(r.name) ?? 0) + 1);

    return [...counts.entries()].sort((a, b) => a[0].localeCompare(b[0], 'de')).map(([name, count]) => ({ name, count }));
});
const noRoleCount = computed(() => props.users.filter((u) => !u.roles.length).length);

function setRole(role: string) {
    roleFilter.value = roleFilter.value === role ? '' : role;
}

watch(roleFilter, (role) => {
    const url = new URL(window.location.href);
    if (role) url.searchParams.set('role', role);
    else url.searchParams.delete('role');
    window.history.replaceState(window.history.state, '', url);
    page.value = 1;
});

const COLUMNS: DataTableColumn<UserRow>[] = [
    { key: 'id', searchable: false },
    { key: 'name', value: (u) => fullName(u) + ' ' + (u.username ?? '') },
    { key: 'email' },
    { key: 'ort', value: (u) => u.ort ?? '' },
    { key: 'status', value: (u) => (u.roles.length ? 1 : 0) },
    { key: 'roles', value: (u) => u.roles.map((r) => r.name).join(', ') },
    { key: 'actions', sortable: false, searchable: false },
];
const users = computed(() => {
    const role = roleFilter.value;
    if (!role) return props.users;
    if (role === NO_ROLE) return props.users.filter((u) => !u.roles.length);

    return props.users.filter((u) => u.roles.some((r) => r.name === role));
});
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

        <div class="flex flex-wrap items-center gap-2">
            <label class="flex items-center gap-2 text-sm">
                <span class="text-muted-foreground">Rolle:</span>
                <select v-model="roleFilter" class="border-input bg-background h-9 min-w-48 rounded-md border px-2 text-sm">
                    <option value="">Alle ({{ props.users.length }})</option>
                    <option v-for="r in roleOptions" :key="r.name" :value="r.name">{{ r.name }} ({{ r.count }})</option>
                    <option :value="NO_ROLE">Ohne Rolle ({{ noRoleCount }})</option>
                </select>
            </label>
            <button
                v-if="roleFilter"
                type="button"
                class="text-muted-foreground hover:text-foreground inline-flex h-9 items-center gap-1 rounded-md px-2 text-sm"
                @click="roleFilter = ''"
            >
                <X class="size-4" />
                Filter entfernen
            </button>
        </div>

        <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Name, Benutzername oder Email suchen..." />

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr>
                            <TableHeadCell label="ID" sort-key="id" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('id')" />
                            <TableHeadCell label="Name" sort-key="name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('name')" />
                            <TableHeadCell label="Email" sort-key="email" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('email')" />
                            <TableHeadCell label="Ort" sort-key="ort" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('ort')" />
                            <TableHeadCell label="Status" sort-key="status" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('status')" />
                            <TableHeadCell label="Rollen" sort-key="roles" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('roles')" />
                            <TableHeadCell label="Aktion" align="right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="user in pagedRows" :key="user.id">
                            <td class="text-muted-foreground p-3 tabular-nums">{{ user.id }}</td>
                            <td class="p-3">
                                {{ fullName(user) || user.name }}
                                <span v-if="user.username" class="text-muted-foreground block text-xs">{{ user.username }}</span>
                            </td>
                            <td class="p-3">{{ user.email }}</td>
                            <td class="p-3">{{ user.ort }}</td>
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
                                    <button
                                        v-for="role in user.roles"
                                        :key="role.name"
                                        type="button"
                                        class="rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            roleFilter === role.name
                                                ? 'bg-primary text-primary-foreground'
                                                : 'bg-primary/10 text-primary hover:bg-primary/20'
                                        "
                                        :title="roleFilter === role.name ? 'Filter entfernen' : `Nur ${role.name} anzeigen`"
                                        @click="setRole(role.name)"
                                    >
                                        {{ role.name }}
                                    </button>
                                </div>
                            </td>
                            <td class="p-3">
                                <RowActions :actions="rowActions(user)" />
                            </td>
                        </tr>
                        <tr v-if="!pagedRows.length">
                            <td colspan="7" class="text-muted-foreground p-3 text-center">Keine Benutzer gefunden.</td>
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
