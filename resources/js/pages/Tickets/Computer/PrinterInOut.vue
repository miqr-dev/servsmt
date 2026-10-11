<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';
import AdComputerField from '@/components/ad/AdComputerField.vue';
import AdDeviceSelect from '@/components/ad/AdDeviceSelect.vue';
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import { useAdLocations } from '@/composables/useAdLocations';
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
 *
 * AD switch step 4 (2026-10-09): Rechner and Druckerstandort come from AD
 * (ad_computer_id / ad_room_id, the server adds the old gname_id); the
 * printer list is the old inventory of the old rooms linked to the AD room.
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
            { title: 'Druckeranfrage', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

// AD switch step 4: the PC comes from AD (ad_computer_id), the printer room
// too (ad_room_id); printers are not in AD, so the list shows the old
// inventory printers of the old rooms linked to that AD room.
const { data: adData, loading: adLoading, load } = useAdLocations({ computers: true });
onMounted(load);
const printerRoom = ref<number | string | null>('');

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Drucker Einrichten',
    ad_computer_id: '' as number | string,
    ad_pc_device_id: '' as number | string, // PC added in the form (not in AD)
    ad_room_id: '' as number | string,
    ad_device_id: '' as number | string, // device added in the form
    printer_name: '',
    notizen: '',
});

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Druckeranfrage" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Druckeranfrage</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :user="user" :now="now" :is-super-admin="isSuperAdmin" :available-submitter-users="availableSubmitterUsers" :form="form" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Drucker</legend>
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
                            v-model="form.printer_name"
                            v-model:device="form.ad_device_id"
                            :room="printerRoom"
                            type="printer"
                            label="Drucker"
                            value-key="invnr"
                        />
                    </fieldset>

                    <AdComputerField
                        v-model="form.ad_computer_id"
                        v-model:device="form.ad_pc_device_id"
                        :data="adData"
                        :loading="adLoading"
                        :city="user.ort"
                        :address="user.strasse"
                        legend="Ihr Rechner"
                        label="Auf welchem Rechner einrichten?"
                    />
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
