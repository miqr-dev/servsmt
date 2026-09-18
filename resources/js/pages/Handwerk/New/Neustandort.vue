<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, h, onMounted } from 'vue';
import SubmitterCard from '@/components/handwerk/SubmitterCard.vue';
import LocationRoomFields from '@/components/handwerk/LocationRoomFields.vue';
import { useHandwerkLocations } from '@/composables/useHandwerkLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/handwerk/new/neustandort.blade.php. Submits
 * to the existing HandwerkController@form_store_handwerk (unchanged) via
 * Inertia's useForm - it redirects to a still-Blade page on success, which
 * Inertia follows as a normal full-page navigation.
 */

type SubmitterUser = { id: number; username: string; ort: string | null; strasse: string | null; tel: string | null };

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isException: boolean;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
            { title: 'Neu Standort', href: '/neustandort' },
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
    problem_type: 'Neustandort',
    location_id: '',
    custom_room: '',
    notizen: '',
});

function submit() {
    form.post('/form_store_handwerk');
}
</script>

<template>
    <Head title="Neu Standort" />

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
                <LocationRoomFields
                    :form="form"
                    :locations="locations"
                    :rooms-for="roomsFor"
                    :show-room-select="false"
                    :require-custom-room="true"
                />

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
