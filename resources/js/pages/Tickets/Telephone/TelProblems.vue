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
 * Converted from resources/views/tickets/telephone/tel_problems.blade.php
 * (TicketController@tel_problems, route /ticket.tel_problems ->
 * TicketController@store, problem_type "Tel Probleme").
 *
 * Standort -> Raum -> Telefon cascade (tel_current_place/tel_current_room are
 * UI-only, store() never reads them - only searchcomputer, the resolved
 * telephone's InvItems id, actually gets saved), same shape as the
 * "Aktueller Standort" fieldset in Tickets/Telephone/TelChangeForm.vue, via
 * TicketController@tel_in_room (POST /ticket.tel_search_inroom). Notizen is
 * required here, unlike TelChangeForm's "location" variant.
 *
 * The Telefon picker switched from a plain <select> to the shared Combobox
 * (2026-09-18, at your request - "Rechner Wählen and alike" should be
 * searchable).
 */

type TelOption = { id: number; gname: string };

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
            { title: 'Telefon Probleme', href: '#' },
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
    problem_type: 'Tel Probleme',
    tel_current_place: '',
    tel_current_room: '',
    searchcomputer: '',
    notizen: '',
});

const telephones = ref<TelOption[]>([]);

const telephoneOptions = computed(() => telephones.value.map((t) => ({ value: t.id, label: t.gname })));

watch(
    () => form.tel_current_room,
    async (roomId) => {
        form.searchcomputer = '';
        telephones.value = [];
        if (!roomId) return;

        const { data } = await axios.post<TelOption[]>('/ticket.tel_search_inroom', { telephones: roomId });
        telephones.value = data;
    },
);

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Telefon Probleme" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Telefon Probleme</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <fieldset class="grid gap-3 rounded-md border p-3 sm:max-w-md">
                    <legend class="px-1 text-sm font-medium">Telefon</legend>
                    <LocationRoomFields
                        v-model:place="form.tel_current_place"
                        v-model:room="form.tel_current_room"
                        place-label="Telefonstandort"
                        :locations="locations"
                        :places="places"
                        :rooms-for="roomsFor"
                    />
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Telefon <span class="text-muted-foreground">*</span></label>
                        <Combobox v-model="form.searchcomputer" :options="telephoneOptions" required />
                    </div>
                </fieldset>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen <span class="text-muted-foreground">*</span></label>
                    <textarea
                        v-model="form.notizen"
                        required
                        rows="5"
                        class="border-input bg-background rounded-md border px-3 py-2 text-sm"
                    ></textarea>
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
