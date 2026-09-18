<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { CircleCheck, FileDown, Pencil, Save } from '@lucide/vue';
import { h, ref } from 'vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/handwerk/city.blade.php. The ToDo list and
 * the "Erledigt" button on each ticket row were plain jQuery/AJAX against
 * JSON-returning endpoints (HandwerkController@ajaxDestroy,
 * HandwerkTodoController) - those endpoints are unchanged, this page just
 * calls them with axios instead and updates local state instead of poking
 * the DOM directly.
 *
 * Retrofitted 2026-09-17: the Handwerk table now goes through useDataTable
 * (sort/search/15-per-page). The ToDo list stays as-is - it's a card list
 * with inline edit forms, not a tabular grid, so the sort/search/pagination
 * standard doesn't apply to it the way it does to an actual table.
 */

type LocationInfo = { address: string | null } | null;
type RoomInfo = { rname: string | null; altrname: string | null } | null;
type SubmitterInfo = { username: string } | null;

type HandwerkRow = {
    id: number;
    problem_type: string;
    submitter_name: string;
    location: LocationInfo;
    room: RoomInfo;
    created_at: string;
    subject: string | null;
    notizen: string | null;
};

type TodoRow = {
    id: number;
    title: string;
    body: string;
    updated_at_german: string;
    submitter: SubmitterInfo;
};

const props = defineProps<{
    city: string;
    handwerks: HandwerkRow[];
    todos: TodoRow[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
            { title: 'Stadt', href: '#' },
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

// --- Handwerk table ---

const handwerks = ref<HandwerkRow[]>([...props.handwerks]);

const HANDWERK_COLUMNS: DataTableColumn<HandwerkRow>[] = [
    { key: 'problem_type' },
    { key: 'submitter_name' },
    { key: 'location.address' },
    { key: 'room.rname' },
    { key: 'created_at', searchable: false },
    { key: 'actions', sortable: false, searchable: false },
];
const { search, sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(
    handwerks,
    HANDWERK_COLUMNS,
);

async function markHandwerkDone(handwerk: HandwerkRow) {
    if (!confirm('Diese Aktion kann nicht rückgängig gemacht werden. Ticket als erledigt markieren?')) return;

    try {
        await axios.post(`/handwerk/ajax-destroy/${handwerk.id}`);
        handwerks.value = handwerks.value.filter((h) => h.id !== handwerk.id);
    } catch {
        alert('Fehler beim Löschen des Tickets.');
    }
}

function handwerkActions(handwerk: HandwerkRow): RowAction[] {
    return [
        {
            icon: CircleCheck,
            label: 'Erledigt',
            onClick: () => markHandwerkDone(handwerk),
        },
    ];
}

// --- ToDo list ---

type TodoItem = TodoRow & { editing: boolean; draftTitle: string; draftBody: string };

const todos = ref<TodoItem[]>(
    props.todos.map((t) => ({ ...t, editing: false, draftTitle: t.title, draftBody: t.body })),
);

const newTitle = ref('');
const newBody = ref('');

async function addTodo() {
    if (!newTitle.value.trim() || !newBody.value.trim()) return;

    const { data } = await axios.post(`/handwerker/${props.city}/todos`, {
        title: newTitle.value,
        body: newBody.value,
    });

    todos.value.push({
        id: data.id,
        title: data.title,
        body: data.body,
        updated_at_german: data.updated_at_german,
        submitter: data.submitter ? { username: data.submitter.username } : null,
        editing: false,
        draftTitle: data.title,
        draftBody: data.body,
    });

    newTitle.value = '';
    newBody.value = '';
}

function startEdit(todo: TodoItem) {
    todo.draftTitle = todo.title;
    todo.draftBody = todo.body;
    todo.editing = true;
}

async function saveTodo(todo: TodoItem) {
    const { data } = await axios.put(`/handwerker/${props.city}/todos/${todo.id}`, {
        title: todo.draftTitle,
        body: todo.draftBody,
    });

    todo.title = data.title;
    todo.body = data.body;
    todo.updated_at_german = data.updated_at_german;
    todo.editing = false;
}

async function doneTodo(todo: TodoItem) {
    if (!confirm('Du kannst dies nicht rückgängig machen! Aufgabe erledigt?')) return;

    await axios.delete(`/handwerker/${props.city}/todos/${todo.id}`);
    todos.value = todos.value.filter((t) => t.id !== todo.id);
}
</script>

<template>
    <Head :title="`${city} Handwerks`" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                    @click="() => window.history.back()"
                >
                    ←
                </button>
                <h1 class="text-xl font-semibold capitalize">{{ city }} Handwerks</h1>
            </div>
            <a
                :href="`/handwerk/${city}/open-tickets-pdf`"
                class="bg-destructive text-destructive-foreground hover:bg-destructive/90 inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm"
            >
                <FileDown class="h-4 w-4" />
                PDF Herunterladen
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <!-- ToDo list -->
            <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="flex items-center gap-2 border-b p-4">
                    <h3 class="font-semibold">ToDo</h3>
                    <span class="bg-secondary text-secondary-foreground rounded-full px-2 py-0.5 text-xs font-medium">
                        {{ todos.length }}
                    </span>
                </div>
                <div class="flex flex-col gap-3 p-4">
                    <div v-for="todo in todos" :key="todo.id" class="bg-muted/40 rounded-lg border p-3">
                        <template v-if="!todo.editing">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <h4 class="font-semibold">{{ todo.title }}</h4>
                                    <p class="text-muted-foreground mt-1 text-sm whitespace-pre-wrap">
                                        {{ todo.body }}
                                    </p>
                                    <div class="mt-2 flex items-center justify-end gap-2 text-xs">
                                        <span class="font-medium" style="color: #661421">
                                            {{ todo.submitter?.username }}
                                        </span>
                                        <span class="text-muted-foreground">{{ todo.updated_at_german }}</span>
                                    </div>
                                </div>
                                <div class="flex shrink-0 gap-1">
                                    <button
                                        type="button"
                                        class="text-muted-foreground hover:bg-accent hover:text-foreground inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        title="Bearbeiten"
                                        @click="startEdit(todo)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="text-muted-foreground hover:bg-accent hover:text-foreground inline-flex h-8 w-8 items-center justify-center rounded-md"
                                        title="Erledigt"
                                        @click="doneTodo(todo)"
                                    >
                                        <CircleCheck class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex flex-col gap-2">
                                <input
                                    v-model="todo.draftTitle"
                                    type="text"
                                    class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                                />
                                <textarea
                                    v-model="todo.draftBody"
                                    rows="4"
                                    class="border-input bg-background rounded-md border px-3 py-2 text-sm"
                                ></textarea>
                                <button
                                    type="button"
                                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-8 w-fit items-center gap-1.5 self-end rounded-md px-3 text-sm"
                                    @click="saveTodo(todo)"
                                >
                                    <Save class="h-4 w-4" />
                                    Speichern
                                </button>
                            </div>
                        </template>
                    </div>
                    <p v-if="!todos.length" class="text-muted-foreground p-2 text-center text-sm">
                        Keine Aufgaben vorhanden.
                    </p>
                </div>
                <div class="border-t p-4">
                    <h4 class="mb-2 font-semibold">Neue Aufgabe hinzufügen</h4>
                    <form class="flex flex-col gap-2" @submit.prevent="addTodo">
                        <div class="flex flex-col gap-1">
                            <label for="title" class="text-sm font-medium">Aufgabentitel</label>
                            <input
                                id="title"
                                v-model="newTitle"
                                type="text"
                                required
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="body" class="text-sm font-medium">Aufgabenbeschreibung</label>
                            <textarea
                                id="body"
                                v-model="newBody"
                                rows="4"
                                required
                                class="border-input bg-background rounded-md border px-3 py-2 text-sm"
                            ></textarea>
                        </div>
                        <button
                            type="submit"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 mt-1 inline-flex h-9 items-center justify-center rounded-md px-3 text-sm"
                        >
                            Neue Aufgabe?
                        </button>
                    </form>
                </div>
            </div>

            <!-- Handwerk table -->
            <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h3 class="text-center font-semibold">Handwerk Table</h3>
                </div>
                <div class="p-3">
                    <TableToolbar v-model:search="search" v-model:page-size="pageSize" search-placeholder="Suchen..." />
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-muted-foreground text-left">
                            <tr>
                                <TableHeadCell label="Anfrage" sort-key="problem_type" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('problem_type')" />
                                <TableHeadCell label="Ersteller" sort-key="submitter_name" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('submitter_name')" />
                                <TableHeadCell label="Standort" sort-key="location.address" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('location.address')" />
                                <TableHeadCell label="Raum" sort-key="room.rname" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('room.rname')" />
                                <TableHeadCell label="Erstellt am" sort-key="created_at" :active-key="sortKey" :direction="sortDir" align="right" @sort="toggleSort('created_at')" />
                                <TableHeadCell label="Erledigt?" align="right" />
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="handwerk in pagedRows" :key="handwerk.id">
                                <td class="p-3">
                                    <Link
                                        :href="`/handwerk/${handwerk.id}?from_city=${city}`"
                                        class="text-primary hover:underline"
                                    >
                                        {{ handwerk.problem_type }}
                                    </Link>
                                </td>
                                <td class="p-3">{{ handwerk.submitter_name }}</td>
                                <td class="p-3">{{ handwerk.location?.address }}</td>
                                <td class="p-3">{{ handwerk.room?.rname }}</td>
                                <td class="p-3 text-right">{{ timeAgo(handwerk.created_at) }}</td>
                                <td class="p-3">
                                    <RowActions :actions="handwerkActions(handwerk)" />
                                </td>
                            </tr>
                            <tr v-if="!pagedRows.length">
                                <td colspan="6" class="text-muted-foreground p-3 text-center">
                                    {{ search ? 'Nichts gefunden.' : 'Keine Tickets vorhanden.' }}
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
    </div>
</template>
