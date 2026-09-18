<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type UserRow = {
    id: number;
    name: string;
    abteilung: string | null;
    position: string | null;
    tel: string | null;
    email: string;
    straße: string | null;
    bundesland?: string | null;
};

const props = defineProps<{
    user: UserRow;
    roles: Record<string, string>;
    userRoles: string[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Benutzerverwaltung', href: '/users' },
            { title: 'Bearbeiten', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm<{ roles: string[] }>({
    roles: [...props.userRoles],
});

function submit() {
    form.patch(`/users/${props.user.id}`);
}
</script>

<template>
    <Head :title="`${user.name} bearbeiten`" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="bg-card text-card-foreground max-w-lg rounded-xl border shadow-sm">
            <div class="p-4 text-center">
                <h2 class="text-lg font-semibold">{{ user.name }}</h2>
                <p class="text-muted-foreground text-sm">{{ user.abteilung }}</p>
            </div>

            <dl class="divide-y border-t border-b text-sm">
                <div class="flex justify-between p-3">
                    <dt class="text-muted-foreground">Position</dt>
                    <dd>{{ user.position }}</dd>
                </div>
                <div class="flex justify-between p-3">
                    <dt class="text-muted-foreground">Rufnummer</dt>
                    <dd>{{ user.tel }}</dd>
                </div>
                <div class="flex justify-between p-3">
                    <dt class="text-muted-foreground">Mail</dt>
                    <dd>{{ user.email }}</dd>
                </div>
                <div class="flex justify-between p-3">
                    <dt class="text-muted-foreground">Standort</dt>
                    <dd>{{ [user.straße, user.bundesland].filter(Boolean).join(', ') }}</dd>
                </div>
            </dl>

            <form @submit.prevent="submit" class="p-4">
                <label class="mb-2 block text-sm font-medium">Rollen</label>
                <div class="flex flex-wrap gap-3">
                    <label
                        v-for="roleName in Object.values(roles)"
                        :key="roleName"
                        class="flex items-center gap-1.5 text-sm"
                    >
                        <input v-model="form.roles" type="checkbox" :value="roleName" />
                        {{ roleName }}
                    </label>
                </div>
                <p v-if="form.errors.roles" class="text-destructive mt-1 text-sm">
                    {{ form.errors.roles }}
                </p>

                <div class="mt-4 flex justify-end gap-2">
                    <Link
                        href="/users"
                        class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                        >Zurück</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm disabled:opacity-60"
                    >
                        Einreichen
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
