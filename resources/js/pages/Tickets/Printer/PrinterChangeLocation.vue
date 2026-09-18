<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/printer/printerChangeLocation.blade.php
 * (TicketController@printer_changes_location, route /ticket.printer_changes_location
 * -> TicketController@store, problem_type "Anderer Druckerstandort").
 *
 * Two independent Standort -> Raum cascades, mirroring the pc_changes_location
 * page (Tickets/Computer/PcChangeLocation.vue):
 *  - "Aktueller Standort" (printer_current_place/printer_current_room) is
 *    UI-only - TicketController@store never reads those two fields. Picking a
 *    room here just filters the printer select via
 *    TicketController@printer_in_room (POST /ticket.printer_search_inroom),
 *    same endpoint the other printer forms use.
 *  - "Neuer Standort" posts as tel_target_place/tel_target_room (the old
 *    Blade form's own field names, reused from the telephone/PC forms) and IS
 *    saved by store().
 * The printer being moved posts as searchcomputer (-> gname_id), the same
 * generic field name every other "which item" select in Tickets uses -
 * matching the old form's id="printer_name" name="searchcomputer" quirk.
 *
 * Each cascade needs its own useTicketLocations() instance since they browse
 * independent Standort/Raum selections.
 *
 * The current-side Drucker picker switched from a plain <select> to the
 * shared Combobox (2026-09-18, at your request - "Rechner Wählen and alike"
 * should be searchable).
 */

type PrinterOption = { gname: string; invnr: string };

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
            { title: 'Drucker Standort ändern', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const current = useTicketLocations();
const target = useTicketLocations();
onMounted(() => {
    current.load();
    target.load();
});

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Anderer Druckerstandort',
    printer_current_place: '',
    printer_current_room: '',
    searchcomputer: '',
    tel_target_place: '',
    tel_target_room: '',
    notizen: '',
});

const printers = ref<PrinterOption[]>([]);

const printerOptions = computed(() => {
    const opts = printers.value.map((p) => ({ value: p.invnr, label: p.gname }));
    if (form.printer_current_room && printers.value.length === 0) {
        opts.push({ value: 'not_listed', label: 'Nicht aufgeführt' });
    }
    return opts;
});

watch(
    () => form.printer_current_room,
    async (roomId) => {
        form.searchcomputer = '';
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
    <Head title="Drucker Standort ändern" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Drucker Standort ändern</h2>

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
                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Aktueller Standort</legend>
                        <LocationRoomFields
                            v-model:place="form.printer_current_place"
                            v-model:room="form.printer_current_room"
                            place-label="Druckerstandort"
                            :locations="current.locations.value"
                            :places="current.places.value"
                            :rooms-for="current.roomsFor"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Drucker <span class="text-muted-foreground">*</span></label>
                            <Combobox v-model="form.searchcomputer" :options="printerOptions" required />
                        </div>
                    </fieldset>

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Neuer Standort</legend>
                        <LocationRoomFields
                            v-model:place="form.tel_target_place"
                            v-model:room="form.tel_target_room"
                            place-label="Druckerstandort"
                            :locations="target.locations.value"
                            :places="target.places.value"
                            :rooms-for="target.roomsFor"
                        />
                    </fieldset>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen</label>
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
