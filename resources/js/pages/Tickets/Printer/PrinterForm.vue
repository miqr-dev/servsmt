<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import ComputerSelect, { type ComputerOption } from '@/components/tickets/ComputerSelect.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
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
 */

type PrinterOption = { gname: string; invnr: string };

type Variant = 'errors' | 'functuality' | 'scannerNew' | 'scanner';

const props = defineProps<{
    variant: Variant;
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    computers: ComputerOption[];
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

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: '#', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const SCANNER_CHECKS = [
    { key: 'scanner_not_working', label: 'Scan funktioniert nicht' },
    { key: 'scanner_wrong_folder', label: 'Scans nicht im Scan Ordner' },
] as const;

const { locations, places, load, roomsFor } = useTicketLocations();
onMounted(load);

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
    searchcomputer: '',
    printer_place: '',
    printer_room: '',
    printer_name: '',
    notizen: '',
});

const printers = ref<PrinterOption[]>([]);

const printerOptions = computed(() => {
    const opts = printers.value.map((p) => ({ value: p.invnr, label: p.gname }));
    if (form.printer_room && printers.value.length === 0) {
        opts.push({ value: 'not_listed', label: 'Nicht aufgeführt' });
    }
    return opts;
});

watch(
    () => form.printer_room,
    async (roomId) => {
        form.printer_name = '';
        printers.value = [];
        if (!roomId) return;

        const { data } = await axios.post<PrinterOption[]>('/ticket.printer_search_inroom', { printers: roomId });
        printers.value = data;
    },
);

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">{{ title }}</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div v-if="variant === 'scanner'" class="flex flex-wrap justify-around gap-4">
                    <label v-for="check in SCANNER_CHECKS" :key="check.key" class="flex items-center gap-2 text-sm">
                        <input v-model="form[check.key]" type="checkbox" class="border-input rounded" />
                        {{ check.label }}
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <ComputerSelect v-model="form.searchcomputer" :computers="computers" />

                    <fieldset class="grid gap-3 rounded-md border p-3">
                        <legend class="px-1 text-sm font-medium">Drucker</legend>
                        <LocationRoomFields
                            v-model:place="form.printer_place"
                            v-model:room="form.printer_room"
                            place-label="Druckerstandort"
                            :locations="locations"
                            :places="places"
                            :rooms-for="roomsFor"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Drucker <span class="text-muted-foreground">*</span></label>
                            <Combobox v-model="form.printer_name" :options="printerOptions" required />
                        </div>
                    </fieldset>
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
