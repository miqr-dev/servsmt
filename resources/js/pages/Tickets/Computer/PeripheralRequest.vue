<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import ComputerSelect, { type ComputerOption } from '@/components/tickets/ComputerSelect.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/computer/peripheralRequest.blade.php
 * (TicketController@peripheralRequest, route /ticket.peripheral_request ->
 * TicketController@store, problem_type "Peripherie Anfrage").
 */

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    computers: ComputerOption[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'Peripherie-Anfrage', href: '#' },
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
    problem_type: 'Peripherie Anfrage',
    keyboard: false,
    mouse: false,
    speaker: false,
    headset: false,
    webcam: false,
    other: false,
    searchcomputer: '',
    notizen: '',
});

const ITEMS = [
    { key: 'keyboard', label: 'Tastatur' },
    { key: 'mouse', label: 'Maus' },
    { key: 'speaker', label: 'Lautsprecher' },
    { key: 'headset', label: 'Kopfhörer' },
    { key: 'webcam', label: 'Webcam' },
    { key: 'other', label: 'Sonstiges' },
] as const;

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Peripherie-Anfrage" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Peripherie-Anfrage</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="flex flex-wrap justify-between gap-3">
                    <label v-for="item in ITEMS" :key="item.key" class="flex items-center gap-2 text-sm">
                        <input v-model="(form as Record<string, boolean>)[item.key]" type="checkbox" class="text-primary h-4 w-4" />
                        {{ item.label }}
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <ComputerSelect v-model="form.searchcomputer" :computers="computers" />

                    <div class="bg-muted/30 flex flex-col gap-1 rounded-md border p-3 text-sm">
                        <p>Anzahl über <strong>1</strong>? Bitte schreiben Sie die benötigte Anzahl und den Grund in das Notizfeld.</p>
                        <p>
                            <strong>Bildschirm?</strong>
                            <a href="/ticket.hardware_request" class="text-primary underline">Hardware-Anfrage</a>
                        </p>
                    </div>
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
