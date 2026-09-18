<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, h, onMounted, reactive } from 'vue';
import { ChevronRight } from '@lucide/vue';
import SubmitterCard from '@/components/handwerk/SubmitterCard.vue';
import LocationRoomFields from '@/components/handwerk/LocationRoomFields.vue';
import ItemCheckboxGroup from '@/components/handwerk/ItemCheckboxGroup.vue';
import { useHandwerkLocations } from '@/composables/useHandwerkLocations';
import {
    DECORATION,
    DESK_FAMILIES,
    KUECHE,
    OTHER_TISCH_ITEMS,
    SCHRANK,
    setItemChecked,
    SONNENSCHUTZ,
    STUHL,
    TAFEL,
    WC,
} from '@/lib/handwerkItems';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/handwerk/new/einrichtungsgegenstände.blade.php
 * ("Mobiliar - Einrichtung") - the biggest of the 6 creation forms. The 3
 * desk families (Schreibtisch TN/DOZ/MA) get their own block below instead of
 * going through ItemCheckboxGroup, since each is a parent label that reveals
 * 3 size-variant checkboxes - the parent checkbox itself isn't a real column
 * (HandwerkController@form_store_handwerk never reads `schreibtisch_TN` etc.,
 * only the `_70x70`/`_80x80`/... dimension fields), so it's local UI state
 * only, not part of the submitted form.
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
            { title: 'Mobiliar - Einrichtung', href: '/einrichtungsgegenstände' },
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

const FLAT_GROUPS = [TAFEL, STUHL, SCHRANK, SONNENSCHUTZ, DECORATION, WC, KUECHE];

function initialItemFields() {
    const fields: Record<string, string | boolean> = {};
    for (const group of FLAT_GROUPS) {
        for (const item of group.items) {
            fields[item.key] = false;
            fields[`${item.key}_qty`] = '';
        }
    }
    for (const item of OTHER_TISCH_ITEMS) {
        fields[item.key] = false;
        fields[`${item.key}_qty`] = '';
    }
    for (const family of DESK_FAMILIES) {
        for (const dim of family.dims) {
            fields[dim.key] = false;
            fields[`${dim.key}_qty`] = '';
        }
    }
    return fields;
}

const form = useForm({
    submitter_name: props.user.username,
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_standort_exception: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    problem_type: 'Mobiliar - Einrichtung',
    location_id: '',
    room_id: '',
    custom_room: '',
    notizen: '',
    ...initialItemFields(),
});

// Local-only "is this desk family's size list expanded" state - not submitted.
const deskFamilyOpen = reactive<Record<string, boolean>>(
    Object.fromEntries(DESK_FAMILIES.map((family) => [family.key, false])),
);

const tischSelectedCount = computed(() => {
    const dimKeys = DESK_FAMILIES.flatMap((family) => family.dims.map((dim) => dim.key));
    const otherKeys = OTHER_TISCH_ITEMS.map((item) => item.key);
    return [...dimKeys, ...otherKeys].filter((key) => !!form[key]).length;
});

function onTischItemToggle(key: string, event: Event) {
    setItemChecked(form, key, (event.target as HTMLInputElement).checked);
}

function submit() {
    form.post('/form_store_handwerk');
}
</script>

<template>
    <Head title="Mobiliar - Einrichtung" />

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

                <div class="flex flex-col gap-3">
                    <ItemCheckboxGroup :title="TAFEL.title" :items="TAFEL.items" :form="form" />

                    <!-- Tisch: 3 desk families (parent toggle -> size checkboxes) + plain items -->
                    <details class="group rounded-md border">
                        <summary
                            class="bg-muted/40 hover:bg-muted flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm font-semibold select-none"
                        >
                            <span class="flex items-center gap-2">
                                Tisch
                                <span
                                    v-if="tischSelectedCount > 0"
                                    class="bg-primary text-primary-foreground inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-medium"
                                >
                                    {{ tischSelectedCount }}
                                </span>
                            </span>
                            <ChevronRight class="text-muted-foreground h-4 w-4 shrink-0 transition-transform duration-200 group-open:rotate-90" />
                        </summary>
                        <div class="flex flex-col gap-3 p-3">
                            <div v-for="family in DESK_FAMILIES" :key="family.key" class="flex flex-col gap-2">
                                <label class="flex items-center gap-2 text-sm font-medium">
                                    <input
                                        v-model="deskFamilyOpen[family.key]"
                                        type="checkbox"
                                        class="border-input h-4 w-4 rounded"
                                    />
                                    {{ family.label }}
                                </label>
                                <div v-if="deskFamilyOpen[family.key]" class="ml-6 grid gap-2 sm:grid-cols-2">
                                    <div
                                        v-for="dim in family.dims"
                                        :key="dim.key"
                                        class="flex items-center gap-2 rounded-md border px-3 py-2 transition-colors"
                                        :class="form[dim.key] ? 'border-primary bg-primary/5' : 'border-input hover:bg-muted/40'"
                                    >
                                        <label :for="dim.key" class="flex flex-1 cursor-pointer items-center gap-2">
                                            <input
                                                :id="dim.key"
                                                :checked="!!form[dim.key]"
                                                type="checkbox"
                                                class="border-input text-primary h-4 w-4 rounded"
                                                @change="onTischItemToggle(dim.key, $event)"
                                            />
                                            <span class="text-sm">{{ dim.label }}</span>
                                        </label>
                                        <input
                                            v-if="form[dim.key]"
                                            v-model="(form[`${dim.key}_qty`] as string)"
                                            type="number"
                                            min="1"
                                            class="border-input bg-background h-8 w-16 rounded-md border px-2 text-sm"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2">
                                <div
                                    v-for="item in OTHER_TISCH_ITEMS"
                                    :key="item.key"
                                    class="flex items-center gap-2 rounded-md border px-3 py-2 transition-colors"
                                    :class="form[item.key] ? 'border-primary bg-primary/5' : 'border-input hover:bg-muted/40'"
                                >
                                    <label :for="item.key" class="flex flex-1 cursor-pointer items-center gap-2">
                                        <input
                                            :id="item.key"
                                            :checked="!!form[item.key]"
                                            type="checkbox"
                                            class="border-input text-primary h-4 w-4 rounded"
                                            @change="onTischItemToggle(item.key, $event)"
                                        />
                                        <span class="text-sm">{{ item.label }}</span>
                                    </label>
                                    <input
                                        v-if="form[item.key]"
                                        v-model="(form[`${item.key}_qty`] as string)"
                                        type="number"
                                        min="1"
                                        class="border-input bg-background h-8 w-16 rounded-md border px-2 text-sm"
                                    />
                                </div>
                            </div>
                        </div>
                    </details>

                    <ItemCheckboxGroup :title="STUHL.title" :items="STUHL.items" :form="form" />
                    <ItemCheckboxGroup :title="SCHRANK.title" :items="SCHRANK.items" :form="form" />
                    <ItemCheckboxGroup :title="SONNENSCHUTZ.title" :items="SONNENSCHUTZ.items" :form="form" />
                    <ItemCheckboxGroup :title="DECORATION.title" :items="DECORATION.items" :form="form" />
                    <ItemCheckboxGroup :title="WC.title" :items="WC.items" :form="form" />
                    <ItemCheckboxGroup :title="KUECHE.title" :items="KUECHE.items" :form="form" />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen</label>
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
