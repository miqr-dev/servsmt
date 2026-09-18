<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import ComputerSelect, { type ComputerOption } from '@/components/tickets/ComputerSelect.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/computer/pc_problems.blade.php
 * (TicketController@pc_problems, route /ticket.pc_problems -> TicketController@store,
 * problem_type "Probleme").
 *
 * The three old <fieldset> checkbox groups (Allgemein / Peripherie /
 * Sonstiges) map 1:1 to boolean columns on Ticket that TicketController@store
 * already assigns by name (geht_nicht_an, blue, black, ... laud_fan, other).
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
            { title: 'PC-Probleme', href: '#' },
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
    problem_type: 'Probleme',
    geht_nicht_an: false,
    blue: false,
    black: false,
    slow_computer: false,
    web_cam_problem: false,
    head_set_problem: false,
    lautsprecher_mal: false,
    keyboard_malfunction: false,
    mouse_mal: false,
    slow_network: false,
    no_network_drive: false,
    laud_fan: false,
    other: false,
    searchcomputer: '',
    notizen: '',
});

const ALLGEMEIN = [
    { key: 'geht_nicht_an', label: 'Geht nicht an' },
    { key: 'blue', label: 'Geht an / Blue Screen' },
    { key: 'black', label: 'Geht an / Black Screen' },
    { key: 'slow_computer', label: 'Sehr Langsam' },
] as const;

const PERIPHERIE = [
    { key: 'web_cam_problem', label: 'Webcam funktioniert nicht' },
    { key: 'head_set_problem', label: 'Headset funktioniert nicht' },
    { key: 'lautsprecher_mal', label: 'Lautsprecher funktioniert nicht' },
    { key: 'keyboard_malfunction', label: 'Tastatur funktioniert nicht' },
    { key: 'mouse_mal', label: 'Maus funktioniert nicht' },
] as const;

const SONSTIGES = [
    { key: 'slow_network', label: 'Netzwerkzugriff langsam' },
    { key: 'no_network_drive', label: 'Keine Netzlaufwerke' },
    { key: 'laud_fan', label: 'lautes Lüftergeräusch' },
    { key: 'other', label: 'Sonstiges' },
] as const;

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="PC-Probleme" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Probleme</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-3">
                    <fieldset class="flex flex-col gap-2 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Allgemein</legend>
                        <label v-for="item in ALLGEMEIN" :key="item.key" class="flex items-center gap-2 text-sm">
                            <input v-model="(form as Record<string, boolean>)[item.key]" type="checkbox" class="text-primary h-4 w-4" />
                            {{ item.label }}
                        </label>
                    </fieldset>

                    <fieldset class="flex flex-col gap-2 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Peripherie</legend>
                        <label v-for="item in PERIPHERIE" :key="item.key" class="flex items-center gap-2 text-sm">
                            <input v-model="(form as Record<string, boolean>)[item.key]" type="checkbox" class="text-primary h-4 w-4" />
                            {{ item.label }}
                        </label>
                    </fieldset>

                    <fieldset class="flex flex-col gap-2 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Sonstiges</legend>
                        <label v-for="item in SONSTIGES" :key="item.key" class="flex items-center gap-2 text-sm">
                            <input v-model="(form as Record<string, boolean>)[item.key]" type="checkbox" class="text-primary h-4 w-4" />
                            {{ item.label }}
                        </label>
                    </fieldset>
                </div>

                <ComputerSelect v-model="form.searchcomputer" :computers="computers" />

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
