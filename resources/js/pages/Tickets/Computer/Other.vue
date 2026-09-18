<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/computer/other.blade.php
 * (TicketController@other, route /ticket.other -> TicketController@store).
 *
 * Simplest of the computer forms: a required subject line plus the shared
 * notes field, no computer/location picker at all.
 */

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'PC / Laptop: Sonstiges', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'PC Laptop Sonstiges',
    pclaptopsonstiges: '',
    notizen: '',
});

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="PC / Laptop: Sonstiges" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Sonstiges</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Betreff <span class="text-muted-foreground">*</span></label>
                    <input
                        v-model="form.pclaptopsonstiges"
                        type="text"
                        required
                        class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Problembeschreibung</label>
                    <textarea v-model="form.notizen" rows="5" class="border-input bg-background rounded-md border px-3 py-2 text-sm"></textarea>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 w-fit items-center self-end rounded-md px-4 text-sm disabled:opacity-60"
                >
                    Einreichen
                </button>
            </div>
        </form>
    </div>
</template>
