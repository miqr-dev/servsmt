<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { isoDate, isoToDmy } from '@/lib/ticketDates';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/web/terminal_tn.blade.php
 * (TicketController@terminal_tn, route /ticket.terminal_tn ->
 * TicketController@store, problem_type "Terminal TN Benutzer").
 *
 * Single fixed form (no tabs): participant username, Maßnahmeende
 * (daterangepicker, default today, not before today -> native date input,
 * posted as DD-MM-YYYY like before; store() assigns terminal_expiry raw and
 * Tickets/Show.vue prints it as-is), Datev / Lexware checkboxes, Beschreibung.
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
            { title: 'Terminal TN', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const today = isoDate();

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Terminal TN Benutzer',
    terminal_name: '',
    terminal_expiry: today,
    terminal_datev: false,
    terminal_lexware: false,
    notizen: '',
});

function submit() {
    form.transform((data) => ({ ...data, terminal_expiry: isoToDmy(data.terminal_expiry) })).post('/form_store');
}
</script>

<template>
    <Head title="Terminal TN" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Terminal TN</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Benutzername des Teilnehmers <span class="text-muted-foreground">*</span></label>
                        <input v-model="form.terminal_name" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Maßnahmeende <span class="text-muted-foreground">*</span></label>
                        <input
                            v-model="form.terminal_expiry"
                            type="date"
                            :min="today"
                            required
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                        />
                    </div>
                </div>

                <div class="flex gap-8">
                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.terminal_datev" type="checkbox" class="text-primary h-4 w-4" />
                        Datev
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.terminal_lexware" type="checkbox" class="text-primary h-4 w-4" />
                        Lexware
                    </label>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Beschreibung</label>
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
