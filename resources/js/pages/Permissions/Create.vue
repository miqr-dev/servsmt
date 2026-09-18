<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    categories: Record<string, string>;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Berechtigungen', href: '/permissions' },
            { title: 'Neu', href: '/permissions/create' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm<{ name: string; permissioncategory_id: string }>({
    name: '',
    permissioncategory_id: '',
});

function submit() {
    form.post('/permissions');
}
</script>

<template>
    <Head title="Neue Permission" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="bg-card text-card-foreground max-w-lg rounded-xl border shadow-sm">
            <form @submit.prevent="submit">
                <div class="space-y-4 border-b p-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Name"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                        <p v-if="form.errors.name" class="text-destructive mt-1 text-sm">
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Kategorie</label>
                        <select
                            v-model="form.permissioncategory_id"
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        >
                            <option value="">-- keine --</option>
                            <option
                                v-for="(name, id) in categories"
                                :key="id"
                                :value="id"
                            >
                                {{ name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.permissioncategory_id"
                            class="text-destructive mt-1 text-sm"
                        >
                            {{ form.errors.permissioncategory_id }}
                        </p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4">
                    <Link
                        href="/permissions"
                        class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                        >Zurück</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm disabled:opacity-60"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
