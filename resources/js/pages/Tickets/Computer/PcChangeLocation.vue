<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, watch } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AdDeviceAdd from '@/components/ad/AdDeviceAdd.vue';
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import { useAdLocations, type AdUserDevice } from '@/composables/useAdLocations';
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
 *
 * AD switch step 4 (2026-10-09): both cascades are AD rooms. The PCs are
 * the AD computers of the current room (ad_pc_ids); the new room posts as
 * ad_target_room_id. tel_target_place / tel_target_room stay in the form
 * empty and the server fills them (and the ticket_pcs old item ids) from
 * the old-inventory links where they exist.
 * PCs added in the app for that room are listed too (ad_pc_device_ids), and a
 * PC that is missing can be added right here (AdDeviceAdd, 2026-10-09).
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
            { title: 'PC Standort ändern', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const { data: adData, loading: adLoading, load } = useAdLocations({ computers: true });
onMounted(load);
const currentRoom = ref<number | string | null>('');

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Anderer PC Standort',
    ad_pc_ids: [] as number[],
    ad_pc_device_ids: [] as number[],
    ad_target_room_id: '' as number | string,
    tel_target_place: '',
    tel_target_room: '',
    notizen: '',
});

const pcFilter = ref('');

const added = ref<AdUserDevice[]>([]);
// AD computers and PCs added in the app, of the current room
const roomPcs = computed(() => {
    if (!currentRoom.value) return [];
    const room = Number(currentRoom.value);
    const userPcs = [...adData.value.pc_devices, ...added.value.filter((a) => !adData.value.pc_devices.some((d) => d.id === a.id))];
    return [
        ...adData.value.computers
            .filter((pc) => pc.room_id === room)
            .map((pc) => ({ key: `ad:${pc.id}`, kind: 'ad' as const, id: pc.id, name: pc.name, group: pc.group })),
        ...userPcs
            .filter((d) => d.room_id === room)
            .map((d) => ({ key: `dev:${d.id}`, kind: 'dev' as const, id: d.id, name: d.name, group: d.checked ? null : 'neu' })),
    ];
});
const pickedCount = computed(() => form.ad_pc_ids.length + form.ad_pc_device_ids.length);

const filteredPcs = computed(() => {
    const q = pcFilter.value.trim().toLowerCase();
    if (!q) return roomPcs.value;
    return roomPcs.value.filter((pc) => pc.name.toLowerCase().includes(q) || (pc.group ?? '').toLowerCase().includes(q));
});

watch(currentRoom, () => {
    form.ad_pc_ids = [];
    form.ad_pc_device_ids = [];
    pcFilter.value = '';
});

function onAdded(d: AdUserDevice) {
    if (!added.value.some((a) => a.id === d.id)) added.value.push(d);
    if (!form.ad_pc_device_ids.includes(d.id)) form.ad_pc_device_ids.push(d.id);
}

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head title="PC Standort ändern" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">PC Standort ändern</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :user="user" :now="now" :is-super-admin="isSuperAdmin" :available-submitter-users="availableSubmitterUsers" :form="form" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Aktueller Standort</legend>
                        <AdRoomPicker
                            v-model:room="currentRoom"
                            :data="adData"
                            :loading="adLoading"
                            :city="user.ort"
                            :address="user.strasse"
                            require-room
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
                                    <p v-if="filteredPcs.length === 0" class="text-muted-foreground px-2 py-3 text-center text-sm">
                                        {{ currentRoom ? 'Keine Rechner in diesem Raum' : 'Erst den Raum wählen' }}
                                    </p>
                                    <label
                                        v-for="pc in filteredPcs"
                                        :key="pc.key"
                                        class="hover:bg-accent flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm"
                                    >
                                        <input
                                            v-if="pc.kind === 'ad'"
                                            v-model="form.ad_pc_ids"
                                            type="checkbox"
                                            :value="pc.id"
                                            class="border-input rounded"
                                        />
                                        <input v-else v-model="form.ad_pc_device_ids" type="checkbox" :value="pc.id" class="border-input rounded" />
                                        {{ pc.name }}<template v-if="pc.group"> ({{ pc.group }})</template>
                                    </label>
                                </div>
                            </div>
                            <input type="text" tabindex="-1" aria-hidden="true" required :value="pickedCount ? 'x' : ''" class="sr-only" />
                            <AdDeviceAdd
                                :room="currentRoom"
                                :kinds="['rechner']"
                                :open="!!currentRoom && !adLoading && roomPcs.length === 0"
                                @added="onAdded"
                            />
                        </div>
                    </fieldset>

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Neuer Standort</legend>
                        <AdRoomPicker
                            v-model:ou="form.ad_target_room_id"
                            :data="adData"
                            :loading="adLoading"
                            :city="user.ort"
                            :address="user.strasse"
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
