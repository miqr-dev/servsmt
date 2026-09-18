<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

// Converted from resources/views/korso/zertifizierung_items/create.blade.php
// + _form.blade.php (ZertifizierungItemController@create/store).

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Zert & Quali. Optionen', href: '/zertifizierung_items' },
            { title: 'Neu', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({
    name: '',
    location_needed: false,
    massnahme_needed: false,
});

function submit() {
    form.post('/zertifizierung_items');
}
</script>

<template>
    <Head title="Zert & Quali. hinzufügen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Zert & Quali. hinzufügen</h1>

        <div class="bg-card text-card-foreground max-w-lg rounded-xl border shadow-sm">
            <form @submit.prevent="submit">
                <div class="space-y-4 border-b p-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Bezeichnung</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                        />
                        <p v-if="form.errors.name" class="text-destructive mt-1 text-sm">{{ form.errors.name }}</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.location_needed" type="checkbox" class="text-primary h-4 w-4" />
                        Standort benötigt
                    </label>

                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.massnahme_needed" type="checkbox" class="text-primary h-4 w-4" />
                        Maßnahme benötigt
                    </label>
                </div>

                <div class="flex justify-end gap-2 p-4">
                    <Link
                        href="/zertifizierung_items"
                        class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm"
                    >
                        Zurück
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-3 text-sm disabled:opacity-60"
                    >
                        Erstellen
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
