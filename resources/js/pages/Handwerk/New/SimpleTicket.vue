<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, h, onMounted } from 'vue';
import SubmitterCard from '@/components/handwerk/SubmitterCard.vue';
import LocationRoomFields from '@/components/handwerk/LocationRoomFields.vue';
import { useHandwerkLocations } from '@/composables/useHandwerkLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Shared page for the 3 Handwerk creation forms that were byte-identical in
 * Blade apart from title/problem_type: reparatur/elektro.blade.php,
 * reparatur/mobiliar.blade.php, modification/modifikation.blade.php.
 * HandwerkController passes `problemType`/`pageTitle` to pick which.
 */

type SubmitterUser = { id: number; username: string; ort: string | null; strasse: string | null; tel: string | null };

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isException: boolean;
    problemType: string;
    pageTitle: string;
}>();

// Static breadcrumb label - defineOptions() is hoisted out of setup() by the
// Vue compiler and can't reference `props` (see Users/Edit.vue for the same
// constraint hit earlier in this migration), so this can't say e.g.
// "Reparatur - Elektro" dynamically; the <Head :title> below covers that.
defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
            { title: 'Neues Ticket', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const isSuperAdmin = computed(() => (page.props.auth.user?.roles ?? []).includes('Super_Admin'));

const { locations, load, roomsFor } = useHandwerkLocations();

onMounted(() => {
    load();
});

function onStandortChanged(city: string) {
    load(city);
}

const form = useForm({
    submitter_name: props.user.username,
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_standort_exception: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    problem_type: props.problemType,
    location_id: '',
    room_id: '',
    custom_room: '',
    subject: '',
    notizen: '',
});

function submit() {
    form.post('/form_store_handwerk');
}
</script>

<template>
    <Head :title="pageTitle" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-exception="isException"
                :is-super-admin="isSuperAdmin"
                :form="form"
                @standort-changed="onStandortChanged"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <LocationRoomFields :form="form" :locations="locations" :rooms-for="roomsFor" />

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Betreff</label>
                    <input
                        v-model="form.subject"
                        type="text"
                        class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Anforderung</label>
                    <textarea
                        v-model="form.notizen"
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
