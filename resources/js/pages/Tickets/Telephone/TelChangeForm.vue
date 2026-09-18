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
 * Converted from four old Blade pages that all share the same "Aktueller
 * Standort" (tel_current_place/tel_current_room -> searchcomputer) cascade,
 * differing only in the second fieldset and problem_type:
 *  - resources/views/tickets/telephone/telChangeLocation.blade.php (solo page,
 *    route /ticket.tel_changes_location -> TicketController@tel_changes_location,
 *    problem_type "Anderer Telefonstandort")
 *  - resources/views/tickets/telephone/telChangeName.blade.php (solo page,
 *    route /ticket.tel_changes_name -> TicketController@tel_changes_name,
 *    problem_type "Telefonnamenswechsel")
 *  - resources/views/tickets/telephone/telChangeNumber.blade.php (solo page,
 *    route /ticket.tel_changes_number -> TicketController@tel_changes_number,
 *    problem_type "Telefonnummerntausch")
 *  - resources/views/tickets/telephone/tel_changes.blade.php (all three as
 *    jQuery-swapped tabs under one page, route /ticket.tel_changes ->
 *    TicketController@tel_changes; confirmed byte-identical per-tab markup to
 *    the three solo pages above)
 *
 * Same collapsing precedent as Tickets/Computer/SoftwareForm.vue: `variant`
 * forces a single fixed tab (matching the old solo pages) or shows all three
 * as a switcher (matching the old combined page, starting with nothing
 * selected until a tab is clicked, same as the original).
 *
 * All four submit to TicketController@store, which reads tel_current_place/
 * tel_current_room as UI-only (never persisted - they only filter which
 * telephone the "Telefon" select offers, via TicketController@tel_in_room,
 * POST /ticket.tel_search_inroom) and searchcomputer (-> gname_id, the
 * telephone actually being changed). tel_target_place/tel_target_room,
 * current_tel_name/new_tel_name, and new_tel_number are all confirmed
 * persisted by store().
 *
 * Telephone selects post the InvItems row's numeric id (matching
 * tel_in_room's response shape), unlike the printer forms' cascades, which
 * post invnr - a real distinction in the old app, not an inconsistency to
 * "fix".
 *
 * The Telefon picker switched from a plain <select> to the shared Combobox
 * (2026-09-18, at your request - "Rechner Wählen and alike" should be
 * searchable).
 */

type TelOption = { id: number; gname: string };

type Variant = 'location' | 'name' | 'number' | 'request';

const props = defineProps<{
    variant: Variant;
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
}>();

const TITLES: Record<Variant, string> = {
    location: 'Telefon Standort ändern',
    name: 'Telefon Name ändern',
    number: 'Telefon Nummer ändern',
    request: 'Telefon anfrage',
};

const TAB_LABELS: Record<Exclude<Variant, 'request'>, string> = {
    location: 'Standort ändern',
    name: 'Name ändern',
    number: 'Nummer ändern',
};

const PROBLEM_TYPES: Record<Exclude<Variant, 'request'>, string> = {
    location: 'Anderer Telefonstandort',
    name: 'Telefonnamenswechsel',
    number: 'Telefonnummerntausch',
};

const title = computed(() => TITLES[props.variant]);
const showTabs = props.variant === 'request';
const activeTab = ref<Exclude<Variant, 'request'> | null>(showTabs ? null : (props.variant as Exclude<Variant, 'request'>));

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: '#', href: '#' },
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
    problem_type: activeTab.value ? PROBLEM_TYPES[activeTab.value] : '',
    tel_current_place: '',
    tel_current_room: '',
    searchcomputer: '',
    tel_target_place: '',
    tel_target_room: '',
    current_tel_name: '',
    new_tel_name: '',
    new_tel_number: '',
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

function selectTab(tab: Exclude<Variant, 'request'>) {
    activeTab.value = tab;
    form.problem_type = PROBLEM_TYPES[tab];
    form.tel_target_place = '';
    form.tel_target_room = '';
    form.current_tel_name = '';
    form.new_tel_name = '';
    form.new_tel_number = '';
    form.notizen = '';
}

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">{{ title }}</h2>

        <div v-if="showTabs" class="flex flex-wrap justify-center gap-3">
            <button
                v-for="tab in (['location', 'name', 'number'] as const)"
                :key="tab"
                type="button"
                class="rounded-md border px-4 py-2 text-sm"
                :class="activeTab === tab ? 'bg-primary text-primary-foreground border-primary' : 'border-input hover:bg-muted/40'"
                @click="selectTab(tab)"
            >
                {{ TAB_LABELS[tab] }}
            </button>
        </div>

        <form v-if="activeTab" class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
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
                            v-model:place="form.tel_current_place"
                            v-model:room="form.tel_current_room"
                            place-label="Telefonstandort"
                            :locations="current.locations.value"
                            :places="current.places.value"
                            :rooms-for="current.roomsFor"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Telefon <span class="text-muted-foreground">*</span></label>
                            <Combobox v-model="form.searchcomputer" :options="telephoneOptions" required />
                        </div>
                    </fieldset>

                    <fieldset v-if="activeTab === 'location'" class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Neuer Standort</legend>
                        <LocationRoomFields
                            v-model:place="form.tel_target_place"
                            v-model:room="form.tel_target_room"
                            place-label="Telefonstandort"
                            :locations="target.locations.value"
                            :places="target.places.value"
                            :rooms-for="target.roomsFor"
                        />
                    </fieldset>

                    <fieldset v-else-if="activeTab === 'name'" class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Name Ändern</legend>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Aktuelle Name <span class="text-muted-foreground">*</span></label>
                            <input
                                v-model="form.current_tel_name"
                                type="text"
                                required
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Neue Name <span class="text-muted-foreground">*</span></label>
                            <input
                                v-model="form.new_tel_name"
                                type="text"
                                required
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                    </fieldset>

                    <fieldset v-else-if="activeTab === 'number'" class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Nummer Ändern</legend>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Neue Nummer <span class="text-muted-foreground">*</span></label>
                            <input
                                v-model="form.new_tel_number"
                                type="text"
                                required
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                    </fieldset>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">
                        {{ activeTab === 'location' ? 'Notizen' : 'Grund' }}
                        <span v-if="activeTab !== 'location'" class="text-muted-foreground">*</span>
                    </label>
                    <textarea
                        v-model="form.notizen"
                        :required="activeTab !== 'location'"
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
