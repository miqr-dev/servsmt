<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, type VNode } from 'vue';
import AdComputerField from '@/components/ad/AdComputerField.vue';
import AdDeviceSelect from '@/components/ad/AdDeviceSelect.vue';
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import { useAdLocations } from '@/composables/useAdLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from four old Blade pages that all shared the exact same
 * "Welcher Rechner" + Standort/Raum/Drucker cascade + Notizen shape, just
 * with a different problem_type / title, and (scanner only) two extra
 * checkboxes up front:
 *  - resources/views/tickets/printer/errors.blade.php
 *    (TicketController@errors, route /ticket.errors, problem_type "Drucker Fehler")
 *  - resources/views/tickets/printer/functuality.blade.php
 *    (TicketController@functuality, route /ticket.function, problem_type "Funktionsanfrage")
 *  - resources/views/tickets/printer/scanner_new.blade.php
 *    (TicketController@scannerNew, route /ticket.scanner.new, problem_type "Scanner Einrichten")
 *  - resources/views/tickets/printer/scanner.blade.php
 *    (TicketController@scanner, route /ticket.scanner, problem_type "Scanner Probleme")
 *
 * All four submit to TicketController@store, which reads searchcomputer
 * (-> gname_id), printer_name, and (scanner only) scanner_not_working /
 * scanner_wrong_folder. printer_place / printer_room are UI-only drill-down
 * fields store() never reads - same cascade pattern already used by
 * Tickets/Computer/PrinterInOut.vue.
 *
 * errors.blade.php additionally had a purely decorative "click here if the
 * printer isn't listed" box whose link went nowhere (href="#"). That need is
 * already covered by the printer select's own "Nicht aufgeführt" fallback
 * option below, so the dead-link box was dropped rather than ported as-is.
 *
 * The Drucker picker switched from a plain <select> to the shared Combobox
 * (2026-09-18, at your request - "Rechner Wählen and alike" should be
 * searchable).
 *
 * AD switch step 4 (2026-10-09): Rechner and Druckerstandort come from AD
 * (ad_computer_id / ad_room_id, the server adds the old gname_id); the
 * printer list is the old inventory of the old rooms linked to the AD room.
 */

type Variant = 'errors' | 'functuality' | 'scannerNew' | 'scanner';

const props = defineProps<{
    variant: Variant;
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
}>();

const TITLES: Record<Variant, string> = {
    errors: 'Drucker Fehlermeldung',
    functuality: 'Funktionsanfrage',
    scannerNew: 'Scannerinstallation',
    scanner: 'Scanner Probleme',
};

const PROBLEM_TYPES: Record<Variant, string> = {
    errors: 'Drucker Fehler',
    functuality: 'Funktionsanfrage',
    scannerNew: 'Scanner Einrichten',
    scanner: 'Scanner Probleme',
};

const title = computed(() => TITLES[props.variant]);
// scanner forms: the device list / "hinzufügen" talk about scanners
const isScanner = computed(() => props.variant === 'scanner' || props.variant === 'scannerNew');

defineOptions({
    layout: (h_: typeof h, page: VNode) => {
        // title per variant (defineOptions can't use TITLES below: it is hoisted) - was a "#" before
        const variant = (page.props as { variant?: string } | null)?.variant ?? '';
        const title =
            (
                {
                    errors: 'Drucker Fehlermeldung',
                    functuality: 'Funktionsanfrage',
                    scannerNew: 'Scannerinstallation',
                    scanner: 'Scanner Probleme',
                } as Record<string, string>
            )[variant] ?? 'Drucker';
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title, href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const SCANNER_CHECKS = [
    { key: 'scanner_not_working', label: 'Scan funktioniert nicht' },
    { key: 'scanner_wrong_folder', label: 'Scans nicht im Scan Ordner' },
] as const;

// AD switch step 4: the PC comes from AD (ad_computer_id), the printer room
// too (ad_room_id); printers are not in AD, so the list shows the old
// inventory printers of the old rooms linked to that AD room.
const { data: adData, loading: adLoading, load } = useAdLocations({ computers: true });
onMounted(load);
const printerRoom = ref<number | string | null>('');

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: PROBLEM_TYPES[props.variant],
    scanner_not_working: false,
    scanner_wrong_folder: false,
    ad_computer_id: '' as number | string,
    ad_pc_device_id: '' as number | string, // PC added in the form (not in AD)
    ad_room_id: '' as number | string,
    ad_device_id: '' as number | string, // device added in the form
    printer_name: '',
    notizen: '',
});

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">{{ title }}</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :user="user" :now="now" :is-super-admin="isSuperAdmin" :available-submitter-users="availableSubmitterUsers" :form="form" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div v-if="variant === 'scanner'" class="flex flex-wrap justify-around gap-4">
                    <label v-for="check in SCANNER_CHECKS" :key="check.key" class="flex items-center gap-2 text-sm">
                        <input v-model="form[check.key]" type="checkbox" class="border-input rounded" />
                        {{ check.label }}
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">{{ isScanner ? 'Scanner' : 'Drucker' }}</legend>
                        <AdRoomPicker
                            v-model:ou="form.ad_room_id"
                            v-model:room="printerRoom"
                            :data="adData"
                            :loading="adLoading"
                            :city="user.ort"
                            :address="user.strasse"
                            :standort-label="isScanner ? 'Scannerstandort' : 'Druckerstandort'"
                            require-room
                        />
                        <AdDeviceSelect
                            v-model="form.printer_name"
                            v-model:device="form.ad_device_id"
                            :room="printerRoom"
                            type="printer"
                            :label="isScanner ? 'Scanner' : 'Drucker'"
                            value-key="invnr"
                            :add-kinds="isScanner ? ['scanner', 'drucker'] : undefined"
                        />
                    </fieldset>

                    <AdComputerField
                        v-model="form.ad_computer_id"
                        v-model:device="form.ad_pc_device_id"
                        :data="adData"
                        :loading="adLoading"
                        :city="user.ort"
                        :address="user.strasse"
                        legend="Ihr Rechner"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Beschreibung</label>
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
