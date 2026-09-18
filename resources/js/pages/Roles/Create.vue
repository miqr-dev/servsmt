<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import PermissionCheckboxGroups from '@/components/PermissionCheckboxGroups.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type PermissionRow = { id: number; name: string };

defineProps<{
    permissionsByCategory: Record<string, PermissionRow[]>;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Rollen', href: '/roles' },
            { title: 'Neue Rolle', href: '/roles/create' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm<{ name: string; permission: number[] }>({
    name: '',
    permission: [],
});

function submit() {
    form.post('/roles');
}
</script>

<template>
    <Head title="Neue Rolle" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <form @submit.prevent="submit">
                <div class="space-y-4 border-b p-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium"
                            >Rollenname</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Neue Rolle"
                            class="border-input bg-background h-9 w-full max-w-sm rounded-md border px-3 text-sm"
                        />
                        <p v-if="form.errors.name" class="text-destructive mt-1 text-sm">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <PermissionCheckboxGroups
                        v-model="form.permission"
                        :permissions-by-category="permissionsByCategory"
                    />
                    <p v-if="form.errors.permission" class="text-destructive text-sm">
                        {{ form.errors.permission }}
                    </p>
                </div>
                <div class="flex justify-end gap-2 p-4">
                    <Link
                        href="/roles"
                        class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                        >Verwerfen</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm disabled:opacity-60"
                    >
                        Einfügen
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
