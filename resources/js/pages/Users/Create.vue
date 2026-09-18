<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    roles: Record<string, string>;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Benutzerverwaltung', href: '/users' },
            { title: 'Neuer Benutzer', href: '/users/create' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({
    name: '',
    vorname: '',
    email: '',
    password: '',
    position: '',
    abteilung: '',
    tel: '',
    roles: [] as string[],
});

function submit() {
    form.post('/users');
}
</script>

<template>
    <Head title="Neuer Benutzer" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="bg-card text-card-foreground max-w-xl rounded-xl border shadow-sm">
            <form @submit.prevent="submit">
                <div class="grid gap-4 border-b p-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Vorname</label>
                        <input
                            v-model="form.vorname"
                            type="text"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                        <p v-if="form.errors.name" class="text-destructive mt-1 text-sm">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Passwort</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                        <p class="text-muted-foreground mt-1 text-xs">
                            Nur relevant für lokale Accounts - normale Anmeldung läuft über Windows/LDAP.
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Position</label>
                        <input
                            v-model="form.position"
                            type="text"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Abteilung</label>
                        <input
                            v-model="form.abteilung"
                            type="text"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Rufnummer</label>
                        <input
                            v-model="form.tel"
                            type="text"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Rollen</label>
                        <div class="flex flex-wrap gap-3">
                            <label
                                v-for="roleName in Object.values(roles)"
                                :key="roleName"
                                class="flex items-center gap-1.5 text-sm"
                            >
                                <input
                                    v-model="form.roles"
                                    type="checkbox"
                                    :value="roleName"
                                />
                                {{ roleName }}
                            </label>
                        </div>
                        <p v-if="form.errors.roles" class="text-destructive mt-1 text-sm">
                            {{ form.errors.roles }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4">
                    <Link
                        href="/users"
                        class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                        >Abbrechen</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm disabled:opacity-60"
                    >
                        Speichern
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
