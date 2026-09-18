<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted } from 'vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/computer/hardwareRequest.blade.php
 * (TicketController@hardwareRequest, route /ticket.hardware_request ->
 * TicketController@store, problem_type "Hardware Anfrage").
 *
 * The old page's Geräte-Art <select> came from a separate $machines
 * collection (Gart rows for the hardware categories), not from $computers -
 * kept as its own prop/select here rather than reusing ComputerSelect.
 *
 * Geräte-Art switched from a plain <select> to the shared Combobox
 * (2026-09-18, at your request - "Rechner Wählen and alike" should be
 * searchable).
 */

type MachineOption = { id: number; name: string };

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    machines: MachineOption[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'Geräteanfrage', href: '#' },
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
    problem_type: 'Hardware Anfrage',
    searchmachine: '',
    location_id: '',
    room_id: '',
    notizen: '',
});

const machineOptions = computed(() => props.machines.map((m) => ({ value: m.id, label: m.name })));

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Geräteanfrage" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Geräteanfrage</h2>

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
                        <label class="text-sm font-medium">Geräte Art <span class="text-muted-foreground">*</span></label>
                        <Combobox v-model="form.searchmachine" :options="machineOptions" required />
                    </div>

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Ort</legend>
                        <LocationRoomFields
                            v-model:place="form.location_id"
                            v-model:room="form.room_id"
                            :locations="locations"
                            :places="places"
                            :rooms-for="roomsFor"
                        />
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
