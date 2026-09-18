<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

// Converted from resources/views/korso/onlinemarketing_items/create.blade.php
// + _form.blade.php (OnlinemarketingItemController@create/store).

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Onlinemarketing Optionen', href: '/onlinemarketing_items' },
            { title: 'Neu', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({ name: '' });

function submit() {
    form.post('/onlinemarketing_items');
}
</script>

<template>
    <Head title="Neue Onlinemarketing Option hinzufügen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Neue Onlinemarketing Option hinzufügen</h1>

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
                </div>

                <div class="flex justify-end gap-2 p-4">
                    <Link
                        href="/onlinemarketing_items"
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
