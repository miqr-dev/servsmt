<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/computer/pcChangeLocation.blade.php
 * (TicketController@pc_changes_location, route /ticket.pc_changes_location ->
 * TicketController@store, problem_type "Anderer PC Standort").
 *
 * Two independent Standort -> Raum cascades:
 *  - "Aktueller Standort" (pc_current_place/pc_current_room) is UI-only -
 *    TicketController@store never reads those two fields. Choosing a room
 *    here just filters the PCs & Laptops multi-select via
 *    TicketController@pc_in_room (POST /ticket.pc_search_inroom).
 *  - "Neuer Standort" posts as tel_target_place/tel_target_room (the old
 *    Blade form's own field names, reused from the telephone forms) and IS
 *    saved by store().
 * pc_ids[] is the one field that's actually required (store() validates
 * `required|array|min:1` for this problem_type) and gets synced onto the
 * ticket's pcs() relation after save.
 *
 * Each cascade needs its own useTicketLocations() instance since they browse
 * independent Standort/Raum selections.
 *
 * The PCs & Laptops multi-select switched from a plain native <select
 * multiple> to a searchable checkbox list (2026-09-18, at your request -
 * "Rechner Wählen and alike" should be searchable). This is the one item
 * picker in Tickets that's genuinely multi-select, so it doesn't reuse the
 * single-select Combobox component - a plain filter input + checkbox list
 * is the same pattern already used for Korso/UserManagement.vue's role
 * checkbox list (which replaced a select2 multi-select there too).
 */

type PcOption = { id: number; gname: string; invnr: string | null };

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
            { title: 'PC Standort ändern', href: '#' },
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
    problem_type: 'Anderer PC Standort',
    pc_current_place: '',
    pc_current_room: '',
    pc_ids: [] as number[],
    tel_target_place: '',
    tel_target_room: '',
    notizen: '',
});

const availablePcs = ref<PcOption[]>([]);
const pcFilter = ref('');

const filteredPcs = computed(() => {
    const q = pcFilter.value.trim().toLowerCase();
    if (!q) return availablePcs.value;
    return availablePcs.value.filter((pc) => pc.gname.toLowerCase().includes(q) || (pc.invnr ?? '').toLowerCase().includes(q));
});

watch(
    () => form.pc_current_room,
    async (roomId) => {
        form.pc_ids = [];
        availablePcs.value = [];
        pcFilter.value = '';
        if (!roomId) return;

        const { data } = await axios.post<PcOption[]>('/ticket.pc_search_inroom', { pcs: roomId });
        availablePcs.value = data;
    },
);

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="PC Standort ändern" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">PC Standort ändern</h2>

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
                            v-model:place="form.pc_current_place"
                            v-model:room="form.pc_current_room"
                            :locations="current.locations.value"
                            :places="current.places.value"
                            :rooms-for="current.roomsFor"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">PCs & Laptops <span class="text-muted-foreground">*</span></label>
                            <div class="border-input bg-background rounded-md border">
                                <input
                                    v-model="pcFilter"
                                    type="text"
                                    placeholder="Suchen..."
                                    class="placeholder:text-muted-foreground w-full border-b bg-transparent px-3 py-2 text-sm outline-none"
                                />
                                <div class="max-h-48 overflow-y-auto p-1">
                                    <p v-if="filteredPcs.length === 0" class="text-muted-foreground px-2 py-3 text-center text-sm">Keine Treffer</p>
                                    <label
                                        v-for="pc in filteredPcs"
                                        :key="pc.id"
                                        class="hover:bg-accent flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm"
                                    >
                                        <input v-model="form.pc_ids" type="checkbox" :value="pc.id" class="border-input rounded" />
                                        {{ pc.gname }}<template v-if="pc.invnr"> ({{ pc.invnr }})</template>
                                    </label>
                                </div>
                            </div>
                            <input type="text" tabindex="-1" aria-hidden="true" required :value="form.pc_ids.length ? 'x' : ''" class="sr-only" />
                        </div>
                    </fieldset>

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Neuer Standort</legend>
                        <LocationRoomFields
                            v-model:place="form.tel_target_place"
                            v-model:room="form.tel_target_room"
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
