<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AdDeviceSelect from '@/components/ad/AdDeviceSelect.vue';
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import { useAdLocations } from '@/composables/useAdLocations';
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
 *
 * AD switch step 4 (2026-10-09): both rooms are AD rooms (ad_room_id /
 * ad_target_room_id; the server fills tel_target_place / tel_target_room
 * from the old-inventory links). Printers are not in AD: the list is the
 * old inventory of the old rooms linked to the current AD room. The printer
 * now posts its item id as searchcomputer (gname_id) - it used to post the
 * Inventarnummer there, so the ticket page looked up the wrong item.
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
            { title: 'Drucker Standort ändern', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const { data: adData, loading: adLoading, load } = useAdLocations();
onMounted(load);
const printerRoom = ref<number | string | null>('');

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Anderer Druckerstandort',
    ad_room_id: '' as number | string,
    ad_device_id: '' as number | string, // device added in the form
    ad_target_room_id: '' as number | string,
    searchcomputer: '',
    tel_target_place: '',
    tel_target_room: '',
    notizen: '',
});

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Drucker Standort ändern" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Drucker Standort ändern</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :user="user" :now="now" :is-super-admin="isSuperAdmin" :available-submitter-users="availableSubmitterUsers" :form="form" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Aktueller Standort</legend>
                        <AdRoomPicker
                            v-model:ou="form.ad_room_id"
                            v-model:room="printerRoom"
                            :data="adData"
                            :loading="adLoading"
                            :city="user.ort"
                            :address="user.strasse"
                            standort-label="Druckerstandort"
                            require-room
                        />
                        <AdDeviceSelect
                            v-model="form.searchcomputer"
                            v-model:device="form.ad_device_id"
                            :room="printerRoom"
                            type="printer"
                            label="Drucker"
                        />
                    </fieldset>

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Neuer Standort</legend>
                        <AdRoomPicker
                            v-model:ou="form.ad_target_room_id"
                            :data="adData"
                            :loading="adLoading"
                            :city="user.ort"
                            :address="user.strasse"
                            standort-label="Druckerstandort"
                            require-room
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
