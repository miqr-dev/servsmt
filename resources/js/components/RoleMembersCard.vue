<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Plus, X } from '@lucide/vue';
import { computed, ref, type Component } from 'vue';

/**
 * Members of one role, with add (user search) / remove right on the card.
 * Used on Rollen & Berechtigungen > Handwerk / Korso. Backend:
 * RoleMemberController (POST /role-members/{role}, DELETE /role-members/{role}/{user}),
 * only for the roles in App\Support\RoleMembers::MANAGED.
 */
export type MemberOption = { id: number; name: string; username: string | null; ort?: string | null };

const props = defineProps<{
    role: string;
    title: string;
    description: string;
    icon?: Component;
    members: MemberOption[];
    users: MemberOption[];
    /** Shown when nobody has the role. */
    emptyWarning?: string;
    /** Extra confirm text when adding (e.g. the role also grants rights). */
    addNote?: string;
}>();

const adding = ref(false);
const query = ref('');
const busy = ref(false);

const memberIds = computed(() => new Set(props.members.map((m) => m.id)));
const matches = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (q.length < 2) return [];

    return props.users
        .filter((u) => !memberIds.value.has(u.id))
        .filter((u) => [u.name, u.username, u.ort].filter(Boolean).join(' ').toLowerCase().includes(q))
        .slice(0, 8);
});

function add(u: MemberOption) {
    if (props.addNote && !confirm(`${u.name}: ${props.addNote}`)) return;
    busy.value = true;
    router.post(
        `/role-members/${encodeURIComponent(props.role)}`,
        { user_id: u.id },
        {
            preserveScroll: true,
            onSuccess: () => {
                query.value = '';
                adding.value = false;
            },
            onFinish: () => (busy.value = false),
        },
    );
}

function remove(m: MemberOption) {
    if (!confirm(`Rolle ${props.role} von ${m.name} entfernen?`)) return;
    router.delete(`/role-members/${encodeURIComponent(props.role)}/${m.id}`, { preserveScroll: true });
}
</script>

<template>
    <section class="bg-card text-card-foreground rounded-xl border shadow-sm">
        <header class="flex flex-wrap items-start justify-between gap-2 border-b px-5 py-4">
            <div class="min-w-0">
                <h2 class="flex items-center gap-2 font-semibold">
                    <component :is="icon" v-if="icon" class="text-primary size-4" />
                    {{ title }}
                </h2>
                <p class="text-muted-foreground text-xs">
                    Rolle <code>{{ role }}</code> - {{ description }}
                </p>
            </div>
            <button
                v-if="!adding"
                type="button"
                class="hover:bg-accent inline-flex h-8 items-center gap-1.5 rounded-md border px-3 text-sm"
                @click="adding = true"
            >
                <Plus class="size-4" /> Hinzufügen
            </button>
        </header>

        <div class="flex flex-col gap-3 px-5 py-4">
            <div v-if="adding" class="flex flex-col gap-1">
                <div class="flex gap-2">
                    <input
                        v-model="query"
                        type="search"
                        placeholder="Name oder Benutzername (mind. 2 Zeichen)"
                        class="border-input bg-background h-9 flex-1 rounded-md border px-3 text-sm"
                        autofocus
                    />
                    <button
                        type="button"
                        class="hover:bg-accent h-9 rounded-md border px-3 text-sm"
                        @click="
                            adding = false;
                            query = '';
                        "
                    >
                        Abbrechen
                    </button>
                </div>
                <ul v-if="matches.length" class="divide-y rounded-md border text-sm">
                    <li v-for="u in matches" :key="u.id">
                        <button type="button" :disabled="busy" class="hover:bg-accent w-full px-3 py-2 text-left disabled:opacity-60" @click="add(u)">
                            {{ u.name }}
                            <span class="text-muted-foreground text-xs">{{ [u.username, u.ort].filter(Boolean).join(' · ') }}</span>
                        </button>
                    </li>
                </ul>
                <p v-else-if="query.trim().length >= 2" class="text-muted-foreground text-xs">Keine Treffer.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span
                    v-for="m in members"
                    :key="m.id"
                    class="bg-primary/10 text-primary inline-flex items-center gap-1.5 rounded-full py-1 pr-1.5 pl-3 text-sm font-medium"
                    :title="[m.username, m.ort].filter(Boolean).join(' · ')"
                >
                    {{ m.name }}
                    <button type="button" class="hover:bg-primary/20 rounded-full p-0.5" :title="`${m.name} entfernen`" @click="remove(m)">
                        <X class="size-3.5" />
                    </button>
                </span>
                <p v-if="!members.length" class="text-sm text-orange-600 dark:text-orange-400">
                    {{ emptyWarning ?? 'Niemand hat diese Rolle.' }}
                </p>
            </div>
        </div>
    </section>
</template>
