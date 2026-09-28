<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, h, onMounted, ref, watch } from 'vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/projector/projector_problem.blade.php
 * (TicketController@projectorProblems, route /ticket.projector_problems ->
 * TicketController@store, problem_type "Beamer Probleme").
 *
 * Same Standort -> Raum -> device cascade shape as Telephone/TelProblems.vue:
 * the shared grouped Standort/Raum fields (item.listen), then the room's
 * projectors from the unchanged TicketController@pro_in_room endpoint
 * (POST /ticket.pro_search_inroom, InvItems with gart_id 13 in that room),
 * picked in the shared searchable Combobox and posted as `searchcomputer`
 * (-> gname_id), exactly like the old select.
 *
 * pro_current_place / pro_current_room are UI-only, as before - store()
 * never reads them (it only knows pro_target_place/pro_target_room, which
 * this form never had). The old jQuery also posted a `location_id` read
 * from a #location_id_listen element that doesn't exist on the page (always
 * undefined, and pro_in_room ignores it) - dropped. Notizen is required,
 * like before.
 */

type ProjectorOption = { id: number; gname: string };

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
            { title: 'Beamer Probleme', href: '#' },
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
    problem_type: 'Beamer Probleme',
    pro_current_place: '' as string | number,
    pro_current_room: '' as string | number,
    searchcomputer: '' as string | number,
    notizen: '',
});

const projectors = ref<ProjectorOption[]>([]);
const loadingProjectors = ref(false);
const projectorOptions = computed(() => projectors.value.map((p) => ({ value: p.id, label: p.gname })));

// New Standort -> start the room over (the old page rebuilt the Raum list).
watch(
    () => form.pro_current_place,
    () => {
        form.pro_current_room = '';
    },
);

watch(
    () => form.pro_current_room,
    async (roomId) => {
        form.searchcomputer = '';
        projectors.value = [];
        if (!roomId) return;

        loadingProjectors.value = true;
        try {
            const { data } = await axios.post<ProjectorOption[]>('/ticket.pro_search_inroom', { projectors: roomId });
            projectors.value = data;
        } finally {
            loadingProjectors.value = false;
        }
    },
);

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Beamer Probleme" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Beamer Probleme</h2>

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
                    <legend class="px-1 text-sm font-medium">Beamer</legend>
                    <LocationRoomFields
                        v-model:place="form.pro_current_place"
                        v-model:room="form.pro_current_room"
                        place-label="Beamer Standort"
                        :locations="locations"
                        :places="places"
                        :rooms-for="roomsFor"
                    />
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Beamer <span class="text-muted-foreground">*</span></label>
                        <Combobox
                            v-model="form.searchcomputer"
                            :options="projectorOptions"
                            :disabled="!form.pro_current_room"
                            :empty-text="loadingProjectors ? 'Lädt…' : 'Kein Beamer in diesem Raum'"
                            required
                        />
                    </div>
                </fieldset>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen <span class="text-muted-foreground">*</span></label>
                    <textarea v-model="form.notizen" required rows="5" class="border-input bg-background rounded-md border px-3 py-2 text-sm"></textarea>
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
