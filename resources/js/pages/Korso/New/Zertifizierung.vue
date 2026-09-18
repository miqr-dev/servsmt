<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, watch } from 'vue';
import { toast } from 'vue-sonner';
import SubmitterCard from '@/components/korso/SubmitterCard.vue';
import AttachmentPicker from '@/components/korso/AttachmentPicker.vue';
import { useHandwerkLocations } from '@/composables/useHandwerkLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/korso/Zertifizierung/zertifizierung.blade.php
 * (KorsoController@zertifizierung, route /zertifizierung).
 *
 * All 3 Korso creation forms (this one, Printmarketing, Onlinemarketing) post
 * to the same shared KorsoController@form_store_korso endpoint - only
 * problem_type and the item-specific fields differ.
 *
 * Location dropdown reuses useHandwerkLocations() (the same
 * HandwerkController@room_list endpoint the old jQuery hit) - only the
 * `locations` list is needed here, not room-level data (this form has no
 * Raum select, unlike Handwerk's).
 *
 * The old page's single-selection "checkbox" items (JS unchecked all others
 * on change) are now plain radio inputs - same single-select behavior
 * without reproducing that JS, and the Bootstrap custom-checkbox CSS this
 * page used doesn't apply once styled with Tailwind anyway. Because it's a
 * real radio group now, the browser sends exactly one
 * `zertifizierung_item_id` value, whereas the old same-name checkboxes could
 * only be trusted to do that because of the JS - PHP keeps only the last of
 * several same-named non-array fields, which is what that JS was guarding
 * against.
 */

type ZertifizierungItem = { id: number; name: string; location_needed: boolean; massnahme_needed: boolean };
type Massnahme = { id: number; name: string };
type SubmitterUser = { id: number; username: string; ort: string | null; strasse: string | null; tel: string | null };
type SekGroup = { id: number; name: string } | null;

const props = defineProps<{
    user: SubmitterUser;
    massnahmes: Massnahme[];
    zertifizierung_items: ZertifizierungItem[];
    sekGroup: SekGroup;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Zertifizierung & Qualitätsmanagement', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const { locations, load } = useHandwerkLocations();
onMounted(() => load(props.user.ort ?? undefined));

const form = useForm({
    submitter_name: props.user.username,
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    submitter_standort_exception: props.user.ort ?? '',
    sek_group_id: '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    problem_type: 'Zertifizierung & Qualitätsmanagement',
    zertifizierung_item_id: null as number | null,
    location_id: '',
    massnahme_id: '',
    notizen: '',
    attachments: [] as File[],
});

function onStandortChange() {
    load(form.submitter_standort_exception);
}

const selectedItem = computed(() => props.zertifizierung_items.find((i) => i.id === form.zertifizierung_item_id) ?? null);
const showLocation = computed(() => !!selectedItem.value?.location_needed);
const showMassnahme = computed(() => !!selectedItem.value?.massnahme_needed);
const showInfoNetWarning = computed(() => selectedItem.value?.name === 'Fehlermeldung/Aktualisierung InfoNet');
const attachmentsRequired = computed(() => selectedItem.value?.name === 'Beantragung einer Maßnahmenummer');

watch(selectedItem, () => {
    if (!showLocation.value) form.location_id = '';
    if (!showMassnahme.value) form.massnahme_id = '';
});

function submit() {
    if (attachmentsRequired.value && form.attachments.length === 0) {
        toast.error('Bitte fügen Sie mindestens einen Anhang hinzu.');
        return;
    }
    form.post('/form_store_korso');
}
</script>

<template>
    <Head title="Zertifizierung & Qualitätsmanagement" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Zertifizierung & Qualitätsmanagement</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :form="form" :tel="user.tel" :sek-group="sekGroup" @standort-changed="onStandortChange" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Was brauchen Sie?</label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label
                            v-for="item in zertifizierung_items"
                            :key="item.id"
                            class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm"
                            :class="form.zertifizierung_item_id === item.id ? 'border-primary bg-primary/5' : 'border-input hover:bg-muted/40'"
                        >
                            <input v-model="form.zertifizierung_item_id" type="radio" :value="item.id" class="text-primary h-4 w-4" />
                            {{ item.name }}
                        </label>
                    </div>
                </div>

                <div v-if="showInfoNetWarning" class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                    <i class="fas fa-exclamation-triangle"></i>
                    Bitte geben Sie den genauen Link an oder fügen Sie einen Screenshot bei.
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-if="showLocation" class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Standort</label>
                        <select v-model="form.location_id" class="border-input bg-background h-9 rounded-md border px-3 text-sm">
                            <option value="">Standort...</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.address }}</option>
                        </select>
                    </div>

                    <div v-if="showMassnahme" class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Maßnahme auswählen</label>
                        <select v-model="form.massnahme_id" class="border-input bg-background h-9 rounded-md border px-3 text-sm">
                            <option value="">-- Maßnahme suchen --</option>
                            <option v-for="m in massnahmes" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen</label>
                    <textarea v-model="form.notizen" rows="5" class="border-input bg-background rounded-md border px-3 py-2 text-sm"></textarea>
                </div>

                <AttachmentPicker v-model="form.attachments" :required-hint="attachmentsRequired" />

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
