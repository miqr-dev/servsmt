<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import ComputerSelect, { type ComputerOption } from '@/components/tickets/ComputerSelect.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/computer/printer_in_out.blade.php
 * (TicketController@printer_in_out, route /ticket.printer ->
 * TicketController@store, problem_type "Drucker Einrichten").
 *
 * Three-tier cascade: Standort -> Raum (useTicketLocations, same as
 * hardwareRequest) -> Drucker, the third tier fetched on-demand from
 * TicketController@printer_in_room (POST /ticket.printer_search_inroom) for
 * the chosen room, exactly like the old jQuery did. Only printer_name ends up
 * saved on the ticket - printer_place/printer_room are UI-only drill-down,
 * matching TicketController@store (which never reads those two fields).
 *
 * The Drucker picker switched from a plain <select> to the shared Combobox
 * (2026-09-18, at your request - "Rechner Wählen and alike" should be
 * searchable). The "Nicht aufgeführt" fallback stays as a real selectable
 * option in the list, same as before.
 */

type PrinterOption = { gname: string; invnr: string };

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
            { title: 'Druckeranfrage', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const { locations, places, load, roomsFor } = useTicketLocations();
onMounted(load);

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Drucker Einrichten',
    searchcomputer: '',
    printer_place: '',
    printer_room: '',
    printer_name: '',
    notizen: '',
});

const printers = ref<PrinterOption[]>([]);

const printerOptions = computed(() => {
    const opts = printers.value.map((p) => ({ value: p.invnr, label: p.gname }));
    if (form.printer_room && printers.value.length === 0) {
        opts.push({ value: 'not_listed', label: 'Nicht aufgeführt' });
    }
    return opts;
});

watch(
    () => form.printer_room,
    async (roomId) => {
        form.printer_name = '';
        printers.value = [];
        if (!roomId) return;

        const { data } = await axios.post<PrinterOption[]>('/ticket.printer_search_inroom', { printers: roomId });
        printers.value = data;
    },
);

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Druckeranfrage" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Druckeranfrage</h2>

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
                    <ComputerSelect v-model="form.searchcomputer" :computers="computers" />

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Drucker</legend>
                        <LocationRoomFields
                            v-model:place="form.printer_place"
                            v-model:room="form.printer_room"
                            place-label="Druckerstandort"
                            :locations="locations"
                            :places="places"
                            :rooms-for="roomsFor"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Drucker <span class="text-muted-foreground">*</span></label>
                            <Combobox v-model="form.printer_name" :options="printerOptions" required />
                        </div>
                    </fieldset>
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
