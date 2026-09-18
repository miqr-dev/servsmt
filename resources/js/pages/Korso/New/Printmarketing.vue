<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, reactive, ref } from 'vue';
import { toast } from 'vue-sonner';
import SubmitterCard from '@/components/korso/SubmitterCard.vue';
import AttachmentPicker from '@/components/korso/AttachmentPicker.vue';
import PMSection from '@/components/korso/printmarketing/PMSection.vue';
import PMItemRow from '@/components/korso/printmarketing/PMItemRow.vue';
import { useHandwerkLocations } from '@/composables/useHandwerkLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    flyerArbeitgeber,
    flyerColumns,
    flyerKompakt,
    giveawaysAfterWarning,
    giveawaysBeforeWarning,
    messeItems,
    signageItems,
    stationeryAfterWarning,
    stationeryBeforeWarning,
    stationeryVersandtaschen,
    visitenkarteFields,
} from '@/lib/printmarketingItems';

/**
 * Converted from resources/views/korso/Printmarketing/printmarketing.blade.php
 * (KorsoController@printmarketing, route /printmarketing) plus its 5
 * @include'd tab partials (korso.partials.flyer_info/give_aways/stationery/
 * signage/fair_and_misc) - see resources/js/lib/printmarketingItems.ts for
 * the item catalog and the dead-code notes on what wasn't carried over
 * (an unused `$flyerItems` PHP array, and a `specialItems` jQuery submit
 * handler that checked input names - "C4"/"C5"/"DL"/etc. - that don't exist
 * anywhere in this form; every real item is already an `.item-checkbox`
 * picked up by the generic collection loop below).
 *
 * `$payers` (with its `kcourses` relation) was passed to the old Blade view
 * but only ever used inside a `<!-- commented-out -->` alternate "Flyer
 * options" tab-pane that was never active - dropped from the Inertia props
 * for the same reason `$flyerItems` was dropped from the item catalog: dead
 * on arrival.
 *
 * The old Bootstrap `nav-tabs` + `data-toggle="tab"` switching is plain Vue
 * state here (`activeTab`), matching how jQuery-plugin-driven UI has been
 * replaced everywhere else in this migration (Korso/Dashboard.vue's
 * sideslide, Handwerk's collapsible `<details>` groups, etc.).
 *
 * Item selection is a single reactive `{ [itemKey]: quantity }` map instead
 * of the old page's DOM-scraping-at-submit-time (`$('.item-checkbox:checked')`)
 * - `submit()` converts it to the same `[{name, quantity}]` shape
 * KorsoController@form_store_korso's `selected_items` JSON expects.
 */

type SubmitterUser = { id: number; username: string; ort: string | null; strasse: string | null; tel: string | null };
type SekGroup = { id: number; name: string } | null;

const props = defineProps<{
    user: SubmitterUser;
    sekGroup: SekGroup;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Printmarketing', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const { locations, load } = useHandwerkLocations();
onMounted(() => load(props.user.ort ?? undefined));

const TABS = [
    { key: 'flyer', label: 'Flyer / Infomaterial' },
    { key: 'giveaways', label: 'Give Aways' },
    { key: 'stationery', label: 'Geschäftsausstattung' },
    { key: 'signage', label: 'Beschilderung / Gestaltung' },
    { key: 'messe', label: 'Messe & Sonstiges' },
] as const;
const activeTab = ref<(typeof TABS)[number]['key']>('flyer');

const selected = reactive<Record<string, number>>({});
const visitenkarte = reactive({
    visitenkarte_name: '',
    visitenkarte_adresse: '',
    visitenkarte_email: '',
    visitenkarte_telephone: '',
    visitenkarte_position: '',
    visitenkarte_fax: '',
});
const showVisitenkarteExtra = computed(() => 'Visitenkarten' in selected);

const form = useForm({
    submitter_name: props.user.username,
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    submitter_standort_exception: props.user.ort ?? '',
    sek_group_id: '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    problem_type: 'Printmarketing',
    location_id: '',
    notizen: '',
    attachments: [] as File[],
});

function onStandortChange() {
    load(form.submitter_standort_exception);
}

function submit() {
    const selectedItems = Object.entries(selected).map(([name, quantity]) => ({ name, quantity }));

    form.transform((data) => ({
        ...data,
        ...(showVisitenkarteExtra.value ? visitenkarte : {}),
        selected_items: JSON.stringify(selectedItems),
    })).post('/form_store_korso', {
        onError: () => toast.error('Bitte überprüfen Sie Ihre Eingaben.'),
    });
}
</script>

<template>
    <Head title="Printmarketing" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Printmarketing</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :form="form" :tel="user.tel" :sek-group="sekGroup" @standort-changed="onStandortChange" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-1 sm:max-w-xs">
                    <label class="text-sm font-medium">Standort *</label>
                    <select v-model="form.location_id" required class="border-input bg-background h-9 rounded-md border px-3 text-sm">
                        <option value="">Standort...</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.address }}</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-1 border-b">
                    <button
                        v-for="tab in TABS"
                        :key="tab.key"
                        type="button"
                        class="rounded-t-md px-3 py-2 text-sm"
                        :class="activeTab === tab.key ? 'border-primary text-primary border-b-2 font-semibold' : 'text-muted-foreground hover:bg-muted/40'"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Flyer / Infomaterial -->
                <div v-show="activeTab === 'flyer'">
                    <PMSection heading="Bestellung Kompaktflyer A5" :items="flyerKompakt" :selected="selected" />
                    <PMSection heading="Arbeitgeberflyer" :items="flyerArbeitgeber" :selected="selected" />

                    <h4 class="mb-3 text-sm font-semibold">A4-Infomaterial</h4>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="(column, idx) in flyerColumns" :key="idx">
                            <PMSection v-for="section in column" :key="section.heading" :heading="section.heading" :items="section.items" :selected="selected" />
                        </div>
                    </div>
                </div>

                <!-- Give Aways -->
                <div v-show="activeTab === 'giveaways'">
                    <div v-for="item in giveawaysBeforeWarning" :key="item.key">
                        <PMItemRow :item="item" :selected="selected" />
                    </div>

                    <div class="my-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <strong>ACHTUNG:</strong> Diese Artikel können nur quartalsweise bestellt werden. Bitte bestellen Sie vorausschauend!
                    </div>

                    <div v-for="item in giveawaysAfterWarning" :key="item.key">
                        <PMItemRow :item="item" :selected="selected" />
                    </div>
                </div>

                <!-- Geschäftsausstattung -->
                <div v-show="activeTab === 'stationery'">
                    <div v-for="item in stationeryBeforeWarning" :key="item.key">
                        <PMItemRow :item="item" :selected="selected" />

                        <div v-if="item.key === 'Visitenkarten' && showVisitenkarteExtra" class="mt-2 mb-3 ml-6 grid gap-3 rounded-md border p-3 sm:grid-cols-2">
                            <div v-for="field in visitenkarteFields" :key="field.key" class="flex flex-col gap-1">
                                <label class="text-xs font-medium">{{ field.label }}</label>
                                <input
                                    v-model="(visitenkarte as Record<string, string>)[field.key]"
                                    :type="field.type"
                                    class="border-input bg-background h-8 rounded-md border px-2 text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="my-3 rounded-md border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <strong>ACHTUNG:</strong> Diese Artikel können nur quartalsweise bestellt werden. Bitte bestellen Sie vorausschauend!
                    </div>

                    <div v-for="item in stationeryAfterWarning" :key="item.key">
                        <PMItemRow :item="item" :selected="selected" />
                    </div>

                    <div v-for="group in stationeryVersandtaschen" :key="group.label" class="mb-2">
                        <div class="text-sm font-semibold">{{ group.label }}</div>
                        <div class="ml-3">
                            <PMItemRow v-for="item in group.items" :key="item.key" :item="item" :selected="selected" />
                        </div>
                    </div>
                </div>

                <!-- Beschilderung / Gestaltung -->
                <div v-show="activeTab === 'signage'">
                    <PMItemRow v-for="item in signageItems" :key="item.key" :item="item" :selected="selected" />
                </div>

                <!-- Messe & Sonstiges -->
                <div v-show="activeTab === 'messe'">
                    <PMItemRow v-for="item in messeItems" :key="item.key" :item="item" :selected="selected" />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen</label>
                    <textarea v-model="form.notizen" rows="5" class="border-input bg-background rounded-md border px-3 py-2 text-sm"></textarea>
                </div>

                <AttachmentPicker v-model="form.attachments" />

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
