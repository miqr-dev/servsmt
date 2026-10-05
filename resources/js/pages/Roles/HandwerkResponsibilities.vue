<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { BellRing, Check, FileText, Pencil, Plus, Trash2, X } from '@lucide/vue';
import { computed, h, ref } from 'vue';
import RoleMembersCard from '@/components/RoleMembersCard.vue';
import RowActions, { type RowAction } from '@/components/RowActions.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Rollen & Berechtigungen > Handwerk (HandwerkResponsibilityController,
 * App\Support\HandwerkResponsibility, 2026-10-02). Replaces the hardcoded
 * user ids in the Handwerk code:
 * - Handwerk_verwaltung (role, managed under Benutzer): notifications that
 *   went to user 327.
 * - per user: Standorte sichtbar (Meine Tickets), Zuweisbar für Standorte
 *   ("Zuweisen" dropdown), PDF per Mail (assignment e-mail with the ticket PDF).
 */

type Setting = {
    user_id: number;
    name: string;
    username: string | null;
    ort: string | null;
    view_cities: string[];
    assign_cities: string[];
    pdf_by_mail: boolean;
    inactive?: boolean;
};
type UserOption = { id: number; name: string; username: string | null; ort?: string | null };

const props = defineProps<{
    settings: Setting[];
    verwaltung: UserOption[];
    verwaltungRole: string;
    users: UserOption[];
    cities: string[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Rollen & Berechtigungen', href: '/roles' },
            { title: 'Handwerk', href: '/handwerk-zustaendigkeiten' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

// --- list ---
const search = ref('');
const rows = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.settings;

    return props.settings.filter((s) =>
        [s.name, s.username, s.ort, ...s.view_cities, ...s.assign_cities].filter(Boolean).join(' ').toLowerCase().includes(q),
    );
});

function actions(s: Setting): RowAction[] {
    return [
        { icon: Pencil, label: 'Bearbeiten', onClick: () => openEdit(s) },
        { icon: Trash2, label: 'Entfernen', variant: 'destructive', onClick: () => remove(s) },
    ];
}

function remove(s: Setting) {
    if (!confirm(`Alle Handwerk-Zuständigkeiten von "${s.name}" entfernen?`)) return;
    router.delete(`/handwerk-zustaendigkeiten/${s.user_id}`, { preserveScroll: true });
}

// --- dialog ---
const open = ref(false);
const editing = ref<Setting | null>(null);
const selectedUser = ref<UserOption | null>(null);
const userSearch = ref('');
const form = useForm({ view_cities: [] as string[], assign_cities: [] as string[], pdf_by_mail: false });

const configuredIds = computed(() => new Set(props.settings.map((s) => s.user_id)));
const userMatches = computed(() => {
    const q = userSearch.value.trim().toLowerCase();
    if (q.length < 2) return [];

    return props.users
        .filter((u) => !configuredIds.value.has(u.id))
        .filter((u) => [u.name, u.username, u.ort].filter(Boolean).join(' ').toLowerCase().includes(q))
        .slice(0, 8);
});

function openCreate() {
    editing.value = null;
    selectedUser.value = null;
    userSearch.value = '';
    form.reset();
    form.clearErrors();
    open.value = true;
}

function openEdit(s: Setting) {
    editing.value = s;
    selectedUser.value = { id: s.user_id, name: s.name, username: s.username, ort: s.ort };
    form.clearErrors();
    form.view_cities = [...s.view_cities];
    form.assign_cities = [...s.assign_cities];
    form.pdf_by_mail = s.pdf_by_mail;
    open.value = true;
}

function toggle(list: 'view_cities' | 'assign_cities', city: string) {
    const arr = form[list];
    form[list] = arr.includes(city) ? arr.filter((c) => c !== city) : [...arr, city];
}

function toggleAll(list: 'view_cities' | 'assign_cities') {
    form[list] = form[list].length === props.cities.length ? [] : [...props.cities];
}

function submit() {
    if (!selectedUser.value) return;
    form.put(`/handwerk-zustaendigkeiten/${selectedUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => (open.value = false),
    });
}

const SECTIONS = [
    {
        key: 'view_cities' as const,
        title: 'Sieht Handwerk-Tickets dieser Standorte',
        hint: 'In „Meine Tickets“ (Handwerk) und darf diese Tickets öffnen.',
    },
    {
        key: 'assign_cities' as const,
        title: 'Zuweisbar für Tickets dieser Standorte',
        hint: 'Erscheint bei „Zuweisen“ auf Tickets dieser Standorte (zusätzlich zu handwerk_admin).',
    },
];

const chip = 'bg-muted rounded-full px-2 py-0.5 text-xs font-medium';
</script>

<template>
    <Head title="Handwerk-Zuständigkeiten" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-xl font-semibold">Handwerk-Zuständigkeiten</h1>
            <p class="text-muted-foreground text-sm">Wer welche Standorte sieht, wem zugewiesen werden kann und wer Benachrichtigungen bekommt.</p>
        </div>

        <!-- Handwerk_verwaltung -->
        <RoleMembersCard
            :role="verwaltungRole"
            title="Handwerk-Verwaltung"
            description="bekommt Benachrichtigungen bei neuen und wiederhergestellten Tickets, Kommentaren auf nicht zugewiesenen Tickets und wenn das Sekretariat ein Ticket erledigt."
            :icon="BellRing"
            :members="verwaltung"
            :users="users"
            empty-warning="Niemand hat diese Rolle - die Benachrichtigungen gehen zurzeit an niemanden."
        />

        <!-- per-user settings -->
        <section class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <header class="flex flex-wrap items-center justify-between gap-2 border-b px-5 py-4">
                <h2 class="font-semibold">Mitarbeiter</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Suchen..."
                        class="border-input bg-background h-9 w-56 rounded-md border px-3 text-sm"
                    />
                    <button
                        type="button"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm"
                        @click="openCreate"
                    >
                        <Plus class="size-4" /> Mitarbeiter hinzufügen
                    </button>
                </div>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr class="border-b">
                            <th class="p-3 font-medium">Mitarbeiter</th>
                            <th class="p-3 font-medium">Sieht Standorte</th>
                            <th class="p-3 font-medium">Zuweisbar für</th>
                            <th class="p-3 text-center font-medium">PDF per Mail</th>
                            <th class="p-3 text-right font-medium">Aktion</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="s in rows" :key="s.user_id" class="align-top">
                            <td class="p-3">
                                <span class="font-medium">{{ s.name }}</span>
                                <span
                                    v-if="s.inactive"
                                    class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/40 dark:text-red-300"
                                    title="Benutzer ist in servsmt deaktiviert (gelöscht), z. B. weil er nicht mehr im AD-Import ist - bekommt keine Benachrichtigungen und erscheint nicht bei Zuweisen."
                                    >inaktiv</span
                                >
                                <span class="text-muted-foreground block text-xs">{{ [s.username, s.ort].filter(Boolean).join(' · ') }}</span>
                            </td>
                            <td class="p-3">
                                <div class="flex max-w-xs flex-wrap gap-1">
                                    <span v-for="c in s.view_cities" :key="c" :class="chip">{{ c }}</span>
                                    <span v-if="!s.view_cities.length" class="text-muted-foreground">–</span>
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="flex max-w-xs flex-wrap gap-1">
                                    <span v-for="c in s.assign_cities" :key="c" :class="chip">{{ c }}</span>
                                    <span v-if="!s.assign_cities.length" class="text-muted-foreground">–</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <FileText v-if="s.pdf_by_mail" class="mx-auto size-4 text-green-600 dark:text-green-400" />
                                <span v-else class="text-muted-foreground">–</span>
                            </td>
                            <td class="p-3"><RowActions :actions="actions(s)" /></td>
                        </tr>
                        <tr v-if="!rows.length">
                            <td colspan="5" class="text-muted-foreground p-4 text-center">
                                {{ search ? 'Keine Treffer.' : 'Noch keine Zuständigkeiten eingetragen.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- dialog -->
        <Teleport to="body">
            <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="open = false">
                <form
                    class="bg-card text-card-foreground flex max-h-[90vh] w-full max-w-2xl flex-col rounded-xl border shadow-lg"
                    @submit.prevent="submit"
                >
                    <div class="flex items-center justify-between border-b px-5 py-4">
                        <h3 class="font-semibold">{{ editing ? 'Zuständigkeiten bearbeiten' : 'Mitarbeiter hinzufügen' }}</h3>
                        <button type="button" class="text-muted-foreground hover:text-foreground" @click="open = false"><X class="size-4" /></button>
                    </div>

                    <div class="flex flex-col gap-5 overflow-y-auto px-5 py-4">
                        <!-- user -->
                        <div class="flex flex-col gap-1 text-sm">
                            <span class="font-medium">Mitarbeiter</span>
                            <div v-if="selectedUser" class="flex items-center justify-between rounded-md border px-3 py-2">
                                <span>
                                    {{ selectedUser.name }}
                                    <span class="text-muted-foreground text-xs">{{
                                        [selectedUser.username, selectedUser.ort].filter(Boolean).join(' · ')
                                    }}</span>
                                </span>
                                <button
                                    v-if="!editing"
                                    type="button"
                                    class="text-muted-foreground hover:text-foreground text-xs"
                                    @click="selectedUser = null"
                                >
                                    ändern
                                </button>
                            </div>
                            <template v-else>
                                <input
                                    v-model="userSearch"
                                    type="search"
                                    placeholder="Name oder Benutzername (mind. 2 Zeichen)"
                                    class="border-input bg-background h-9 rounded-md border px-3"
                                    autofocus
                                />
                                <ul v-if="userMatches.length" class="divide-y rounded-md border">
                                    <li v-for="u in userMatches" :key="u.id">
                                        <button type="button" class="hover:bg-accent w-full px-3 py-2 text-left" @click="selectedUser = u">
                                            {{ u.name }}
                                            <span class="text-muted-foreground text-xs">{{ [u.username, u.ort].filter(Boolean).join(' · ') }}</span>
                                        </button>
                                    </li>
                                </ul>
                                <p v-else-if="userSearch.trim().length >= 2" class="text-muted-foreground text-xs">
                                    Keine Treffer (bereits eingetragene Mitarbeiter über „Bearbeiten“ ändern).
                                </p>
                            </template>
                        </div>

                        <!-- city sections -->
                        <div v-for="sec in SECTIONS" :key="sec.key" class="flex flex-col gap-2 text-sm">
                            <div class="flex items-end justify-between gap-2">
                                <div>
                                    <span class="font-medium">{{ sec.title }}</span>
                                    <p class="text-muted-foreground text-xs">{{ sec.hint }}</p>
                                </div>
                                <button type="button" class="text-primary shrink-0 text-xs hover:underline" @click="toggleAll(sec.key)">
                                    {{ form[sec.key].length === cities.length ? 'Keine' : 'Alle' }}
                                </button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="c in cities"
                                    :key="c"
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-full border px-3 py-1 text-xs font-medium transition-colors"
                                    :class="
                                        form[sec.key].includes(c)
                                            ? 'bg-primary text-primary-foreground border-primary'
                                            : 'hover:bg-accent text-muted-foreground'
                                    "
                                    @click="toggle(sec.key, c)"
                                >
                                    <Check v-if="form[sec.key].includes(c)" class="size-3" />
                                    {{ c }}
                                </button>
                            </div>
                            <span v-if="form.errors[sec.key]" class="text-destructive text-xs">{{ form.errors[sec.key] }}</span>
                        </div>

                        <label class="flex items-start gap-2 text-sm">
                            <input v-model="form.pdf_by_mail" type="checkbox" class="mt-0.5 size-4" />
                            <span>
                                <span class="font-medium">Ticket als PDF per Mail</span>
                                <span class="text-muted-foreground block text-xs"
                                    >Bei Zuweisung wird das Ticket als PDF an die E-Mail angehängt.</span
                                >
                            </span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 border-t px-5 py-3">
                        <button type="button" class="hover:bg-accent h-9 rounded-md border px-4 text-sm" @click="open = false">Abbrechen</button>
                        <button
                            type="submit"
                            :disabled="form.processing || !selectedUser"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-4 text-sm disabled:opacity-60"
                        >
                            Speichern
                        </button>
                    </div>
                </form>
            </div>
        </Teleport>
    </div>
</template>
