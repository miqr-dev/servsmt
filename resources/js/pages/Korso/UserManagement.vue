<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { UserMinus, UserPlus } from '@lucide/vue';
import { computed, h, ref } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/korso/user-management.blade.php
 * (KorsoController@userManagement/assignRole/removeRole, route
 * /user-management) - Korso_ma role assignment, linked from
 * Korso/Dashboard.vue's Korso_Admin nav ("Rechtevergabe").
 *
 * The old page's select2 multi-select is a plain checkbox list with a text
 * filter here (same reasoning as dropping DataTables/summernote/other
 * jQuery-plugin UI elsewhere in this migration) - assignRole()/removeRole()
 * both just redirect back with a flash message (the old page's
 * `location.reload()` after the jQuery $.post achieved the same refresh),
 * so both actions go through Inertia's router.post rather than axios.
 */

type KorsoUser = { id: number; name: string; vorname: string | null };

const props = defineProps<{
    users: KorsoUser[];
    korsoMaUsers: KorsoUser[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Dashboard', href: '/korso-dashboard' },
            { title: 'Benutzerverwaltung', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const search = ref('');
const selected = ref<Set<number>>(new Set());
const assigning = ref(false);

const filteredUsers = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return props.users;
    return props.users.filter((u) => `${u.vorname ?? ''} ${u.name}`.toLowerCase().includes(term));
});

function toggle(id: number, checked: boolean) {
    if (checked) selected.value.add(id);
    else selected.value.delete(id);
}

function assign() {
    if (!selected.value.size) {
        toast.error('Bitte wählen Sie mindestens einen Benutzer aus.');
        return;
    }

    assigning.value = true;
    router.post(
        '/assign-role',
        { user_ids: Array.from(selected.value) },
        {
            preserveScroll: true,
            onSuccess: () => {
                selected.value = new Set();
            },
            onFinish: () => {
                assigning.value = false;
            },
        },
    );
}

function remove(user: KorsoUser) {
    router.post('/remove-role', { user_id: user.id }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Benutzerverwaltung – Korso Rollen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Benutzerverwaltung – Korso Rollen</h2>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="bg-card text-card-foreground flex flex-col gap-3 rounded-xl border p-4 shadow-sm">
                <h3 class="text-sm font-semibold">
                    Benutzer auswählen und <span class="text-primary">Korso_ma</span>-Rolle zuweisen
                </h3>

                <input
                    v-model="search"
                    type="text"
                    placeholder="Benutzer suchen..."
                    class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                />

                <div class="max-h-80 overflow-y-auto rounded-md border">
                    <label
                        v-for="user in filteredUsers"
                        :key="user.id"
                        class="hover:bg-accent flex items-center gap-2 border-b px-3 py-2 text-sm last:border-b-0"
                    >
                        <input
                            type="checkbox"
                            :checked="selected.has(user.id)"
                            class="text-primary h-4 w-4"
                            @change="toggle(user.id, ($event.target as HTMLInputElement).checked)"
                        />
                        {{ user.name }} ({{ user.vorname }})
                    </label>
                    <p v-if="!filteredUsers.length" class="text-muted-foreground p-3 text-center text-sm">Keine Benutzer gefunden.</p>
                </div>

                <button
                    type="button"
                    :disabled="assigning"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 w-fit items-center gap-1.5 rounded-md px-3 text-sm disabled:opacity-60"
                    @click="assign"
                >
                    <UserPlus class="h-4 w-4" />
                    Rolle zuweisen
                </button>
            </div>

            <div class="bg-card text-card-foreground flex flex-col gap-3 rounded-xl border p-4 shadow-sm">
                <h3 class="text-sm font-semibold">
                    Benutzer mit <span class="text-primary">Korso_ma</span>-Rolle
                </h3>

                <ul class="divide-y rounded-md border">
                    <li v-for="user in korsoMaUsers" :key="user.id" class="flex items-center justify-between px-3 py-2 text-sm">
                        <span>{{ user.name }} ({{ user.vorname }})</span>
                        <button
                            type="button"
                            class="text-destructive hover:bg-destructive/10 inline-flex h-8 items-center gap-1.5 rounded-md px-2 text-xs"
                            @click="remove(user)"
                        >
                            <UserMinus class="h-3.5 w-3.5" />
                            Entfernen
                        </button>
                    </li>
                    <li v-if="!korsoMaUsers.length" class="text-muted-foreground p-3 text-center text-sm">Keine Benutzer mit dieser Rolle.</li>
                </ul>
            </div>
        </div>
    </div>
</template>
