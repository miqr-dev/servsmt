<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';
import AdDeviceSelect from '@/components/ad/AdDeviceSelect.vue';
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import { useAdLocations } from '@/composables/useAdLocations';
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
 *
 * AD switch step 4 (2026-10-09): the room comes from AD (ad_room_id); the
 * projector list is the old inventory of the old rooms linked to it.
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
            { title: 'Beamer Probleme', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

// AD switch step 4: the room comes from AD (ad_room_id); projectors are not
// in AD, so the list shows the old inventory projectors of the linked old rooms.
const { data: adData, loading: adLoading, load } = useAdLocations();
onMounted(load);
const projectorRoom = ref<number | string | null>('');

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Beamer Probleme',
    ad_room_id: '' as number | string,
    ad_device_id: '' as number | string, // device added in the form
    searchcomputer: '' as string | number,
    notizen: '',
});

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="Beamer Probleme" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Beamer Probleme</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :user="user" :now="now" :is-super-admin="isSuperAdmin" :available-submitter-users="availableSubmitterUsers" :form="form" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <fieldset class="grid gap-3 rounded-md border p-3 sm:max-w-md">
                    <legend class="px-1 text-sm font-medium">Beamer</legend>
                    <AdRoomPicker
                        v-model:ou="form.ad_room_id"
                        v-model:room="projectorRoom"
                        :data="adData"
                        :loading="adLoading"
                        :city="user.ort"
                        :address="user.strasse"
                        standort-label="Beamer Standort"
                        require-room
                    />
                    <AdDeviceSelect
                        v-model="form.searchcomputer"
                        v-model:device="form.ad_device_id"
                        :room="projectorRoom"
                        :room-label="adData.raeume.find((r) => r.id === Number(projectorRoom))?.label"
                        type="projector"
                        label="Beamer"
                    />
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
