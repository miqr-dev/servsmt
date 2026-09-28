<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, ref } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { isoDate, isoToDmy } from '@/lib/ticketDates';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/users/participant.blade.php
 * (TicketController@participant, route /ticket.participant).
 *
 * Unlike every other Tickets creation form this one does NOT post to
 * /form_store - it posts multipart to TicketController@store_participant
 * (/ticket.participant.store), which validates an uploaded Excel file
 * (`muster`, xlsx/xls, max 20MB) and imports its rows via
 * ParticipantTicketImport. Unchanged; useForm() switches to FormData
 * automatically because `muster` is a File.
 *
 * Error paths, both unchanged server-side:
 *   - validator failure -> back()->withErrors() -> shown under the file input
 *   - row-level import failures -> back()->with('import_errors', [...]) -
 *     now shared as `flash.importErrors` by HandleInertiaRequests (it
 *     wasn't shared before, since no Inertia page needed it) and listed at
 *     the top of the page like the old red alert boxes.
 *
 * Other bits reproduced:
 *   - "Benötigt bis" daterangepicker -> native date input, default today+7,
 *     not before today, posted as DD-MM-YYYY (store_participant assigns it raw).
 *   - "Für Standort" select: the user's own Ort first, then the fixed city
 *     list. The old list repeated the user's own city a second time further
 *     down - deduplicated here.
 *   - Berlin users: the old page forced a SweetAlert2 "Wählen Sie die Adresse
 *     der Teilnehmer" choice (Berlin-TBR / Berlin-PP, no way to dismiss it)
 *     on load when the submitter's Standort was "Berlin". Same here with a
 *     small blocking dialog. Like before it's keyed off the submitter's
 *     Standort at page load, not re-asked if a Super_Admin switches submitter.
 *   - "Muster Herunterladen" (/donwload_muster - the route's own typo) and the
 *     template-changed notice (23.10.2023) kept verbatim.
 *   - The old "Video Anschauen" help video was removed 2026-09-28 (your
 *     request - all video links in the app were dropped).
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
            { title: 'Neuer Teilnehmer', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ flash: { importErrors?: string[] | null } }>();
const importErrors = computed(() => page.props.flash?.importErrors ?? []);

const CITIES = ['Erfurt', 'Dresden', 'Leipzig', 'Chemnitz', 'Suhl', 'Berlin-TBR', 'Berlin-PP', 'Döbeln'];
const placeOptions = computed(() => {
    const own = props.user.ort ?? '';
    return own ? [own, ...CITIES.filter((c) => c !== own)] : CITIES;
});

const today = isoDate();

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Neuer Teilnehmer',
    participant_required_at: isoDate(7),
    place_id: props.user.ort ?? '',
    muster: null as File | null,
});

function onFileChange(event: Event) {
    form.muster = (event.target as HTMLInputElement).files?.[0] ?? null;
}

// --- Berlin: pick the participants' address first ---

const berlinDialogOpen = ref(false);
const berlinChoice = ref('');
const berlinError = ref('');
onMounted(() => {
    if (form.submitter_standort === 'Berlin') berlinDialogOpen.value = true;
});
function confirmBerlin() {
    if (!berlinChoice.value) {
        berlinError.value = 'Sie müssen eine Adresse auswählen';
        return;
    }
    form.place_id = berlinChoice.value;
    berlinDialogOpen.value = false;
}

function submit() {
    form
        .transform((data) => ({ ...data, participant_required_at: isoToDmy(data.participant_required_at) }))
        .post('/ticket.participant.store', { forceFormData: true });
}
</script>

<template>
    <Head title="Neuer Teilnehmer / n" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Neuer Teilnehmer / n</h2>

        <div v-if="importErrors.length" class="flex flex-col gap-2">
            <div v-for="(err, i) in importErrors" :key="i" class="border-destructive bg-destructive/10 text-destructive rounded-md border px-3 py-2 text-sm">
                {{ err }}
            </div>
        </div>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="border-primary mx-auto max-w-xl rounded-lg border-2 p-4 text-center text-sm font-semibold">
                    Das Muster wurde <span class="text-destructive font-bold">am 23.10.2023</span> erneut geändert, bitte verwenden Sie das
                    Alte nicht mehr und laden Sie das Muster neu herunter.<br />
                    Wenn der Teilnehmer kein Talentlms erhalten soll, können Sie das zusätzliche Feld ignorieren.
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <a
                        href="/donwload_muster"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-4 text-sm"
                    >
                        Muster Herunterladen
                    </a>

                    <div class="flex w-full max-w-xs flex-col gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Benötigt bis <span class="text-muted-foreground">*</span></label>
                            <input
                                v-model="form.participant_required_at"
                                type="date"
                                :min="today"
                                required
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Für Standort</label>
                            <select v-model="form.place_id" class="border-input bg-background h-9 rounded-md border px-3 text-sm">
                                <option v-for="c in placeOptions" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <p class="text-sm">Bitte laden Sie das Muster herunter und tragen die Teilnehmer Daten in die entsprechende Zellen ein.</p>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Ausgefülltes Muster (.xlsx / .xls) <span class="text-muted-foreground">*</span></label>
                    <input
                        type="file"
                        accept=".xlsx,.xls"
                        required
                        class="file:bg-muted file:text-foreground text-sm file:mr-3 file:rounded-md file:border-0 file:px-3 file:py-1.5"
                        @change="onFileChange"
                    />
                    <p v-if="form.errors.muster" class="text-destructive text-xs">{{ form.errors.muster }}</p>
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

        <!-- Berlin: choose participant address (not dismissable, like the old prompt) -->
        <div v-if="berlinDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="bg-card text-card-foreground flex w-full max-w-md flex-col gap-3 rounded-xl border p-5 shadow-lg">
                <h3 class="text-primary text-lg font-semibold">Wählen Sie die Adresse der Teilnehmer</h3>
                <select v-model="berlinChoice" class="border-input bg-background h-9 rounded-md border px-3 text-sm">
                    <option value="" disabled>Wählen Sie eine Adresse</option>
                    <option value="Berlin-TBR">Berlin-TBR</option>
                    <option value="Berlin-PP">Berlin-PP</option>
                </select>
                <p v-if="berlinError" class="text-destructive text-sm">{{ berlinError }}</p>
                <button type="button" class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 self-end rounded-md px-4 text-sm" @click="confirmBerlin">
                    Auswählen
                </button>
            </div>
        </div>

    </div>
</template>
