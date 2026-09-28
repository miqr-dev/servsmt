<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref, watch } from 'vue';
import ComputerSelect, { type ComputerOption } from '@/components/tickets/ComputerSelect.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from two resources/views/tickets/users/* Blade forms, both
 * posting to TicketController@store (/form_store):
 *   - loginProblem.blade.php (/ticket.users_loginProblem, variant "loginProblem",
 *     problem_type "Anmelde Probleme")
 *   - nameChange.blade.php   (/ticket.users_namechange,   variant "nameChange",
 *     problem_type "Wechsel Name")
 *
 * A third page, usersOthers.blade.php (/ticket.users_others, a tabbed
 * Anmeldeprobleme / Namensänderung / "Benutzer sonstiges" page), was removed
 * 2026-09-28 at your request - the first two tabs duplicated these two
 * pages, and it was only reachable from the also-removed users/all
 * sub-landing page. Existing "Benutzer sonstiges" tickets still display
 * (Tickets/Show.vue's ticketViewFields entry is kept).
 *
 * Namensänderung (fixed 2026-09-28, at your request): in the old app
 * nameChange.blade.php - the "Namensänderung" link on the ticket picker -
 * was a copy of the Anmeldeprobleme form (problem_type "Anmelde Probleme",
 * Welcher Rechner + Konto-checkboxes). variant "nameChange" now renders the
 * real name-change form instead (problem_type "Wechsel Name", which
 * Tickets/Show.vue already displays as Alter Name / Neuer Name): the
 * current name is picked from the user list (searchable Combobox, stored
 * as its "Name, Vorname" label in user_oldname) and the new name is typed
 * into user_newname.
 *
 * "Konto Abgelaufen": the old page opened a SweetAlert2 prompt asking for
 * a freelancer end date (free text, required) - confirming stored it in the
 * hidden `expiring_date` field, cancelling/Esc unticked the checkbox again.
 * Reproduced with a small inline dialog (no SweetAlert2 on the Vue side,
 * same as everywhere else in this migration). One small difference:
 * unticking the checkbox now also clears expiring_date, so a stale date
 * can't be submitted without the checkbox.
 *
 * Summernote rich-text Beschreibung -> plain <textarea>, same as every
 * other converted Tickets form.
 */

const props = defineProps<{
    variant: 'loginProblem' | 'nameChange';
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    computers?: ComputerOption[];
    nameUsers?: { id: number; label: string }[];
}>();

const TITLES = {
    loginProblem: 'Benutzer - Anmeldeprobleme',
    nameChange: 'Benutzer - Namensänderung',
} as const;
const PROBLEM_TYPES = {
    loginProblem: 'Anmelde Probleme',
    nameChange: 'Wechsel Name',
} as const;

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'Benutzer', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const isRename = props.variant === 'nameChange';
const title = TITLES[props.variant];

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: PROBLEM_TYPES[props.variant],
    // Anmeldeprobleme
    password_name: '',
    searchcomputer: '' as string | number,
    expiring_date: '',
    abgelaufen: false,
    inaktiv: false,
    forgotten: false,
    other_error_participant: false,
    // Namensänderung
    user_oldname: '',
    user_newname: '',
    notizen: '',
});

// --- Namensänderung: pick the current name from the user list ---

const oldNameUserId = ref<string | number>('');
const nameUserOptions = computed(() => (props.nameUsers ?? []).map((u) => ({ value: u.id, label: u.label })));
watch(oldNameUserId, (id) => {
    form.user_oldname = nameUserOptions.value.find((o) => String(o.value) === String(id))?.label ?? '';
});

// --- "Konto Abgelaufen" freelancer end-date prompt ---

const expiryDialogOpen = ref(false);
const expiryInput = ref('');
const expiryError = ref('');

function onAbgelaufenChange(event: Event) {
    const checked = (event.target as HTMLInputElement).checked;
    form.abgelaufen = checked;
    if (checked) {
        expiryInput.value = '';
        expiryError.value = '';
        expiryDialogOpen.value = true;
    } else {
        form.expiring_date = '';
    }
}
function confirmExpiry() {
    if (!expiryInput.value.trim()) {
        expiryError.value = 'Enddatum ist für Freie Mitarbeiter / in erforderlich';
        return;
    }
    form.expiring_date = expiryInput.value.trim();
    expiryDialogOpen.value = false;
}
function cancelExpiry() {
    form.abgelaufen = false;
    form.expiring_date = '';
    expiryDialogOpen.value = false;
}

const LOGIN_FLAGS = [
    { key: 'inaktiv', label: 'Inaktives Konto' },
    { key: 'forgotten', label: 'Passwort vergessen' },
    { key: 'other_error_participant', label: 'Anderer Fehler' },
] as const;

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
                <template v-if="!isRename">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Vollständiger Name <span class="text-muted-foreground">*</span></label>
                            <input v-model="form.password_name" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                        </div>
                        <ComputerSelect v-model="form.searchcomputer" :computers="computers ?? []" :required="false" />
                    </div>

                    <div class="flex flex-wrap justify-around gap-4">
                        <label class="flex items-center gap-2 text-sm">
                            <input :checked="form.abgelaufen" type="checkbox" class="text-primary h-4 w-4" @change="onAbgelaufenChange" />
                            Konto Abgelaufen
                            <span v-if="form.abgelaufen && form.expiring_date" class="text-muted-foreground text-xs">(bis {{ form.expiring_date }})</span>
                        </label>
                        <label v-for="flag in LOGIN_FLAGS" :key="flag.key" class="flex items-center gap-2 text-sm">
                            <input v-model="form[flag.key]" type="checkbox" class="text-primary h-4 w-4" />
                            {{ flag.label }}
                        </label>
                    </div>
                </template>

                <div v-else class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Mitarbeiter (aktueller Name) <span class="text-muted-foreground">*</span></label>
                        <Combobox v-model="oldNameUserId" :options="nameUserOptions" placeholder="Mitarbeiter wählen" required clearable />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Neuer Name <span class="text-muted-foreground">*</span></label>
                        <input v-model="form.user_newname" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                    </div>
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

        <!-- "Konto Abgelaufen" -> freelancer end date prompt -->
        <Transition enter-active-class="transition-opacity" leave-active-class="transition-opacity" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="expiryDialogOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @keydown.esc="cancelExpiry">
                <div class="bg-card text-card-foreground flex w-full max-w-md flex-col gap-3 rounded-xl border p-5 shadow-lg">
                    <h3 class="text-primary text-lg font-semibold">Freier Mitarbeiter/in ?</h3>
                    <label class="text-sm font-medium">Bitte Enddatum einfügen</label>
                    <input
                        v-model="expiryInput"
                        type="text"
                        autofocus
                        class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                        @keydown.enter.prevent="confirmExpiry"
                    />
                    <p v-if="expiryError" class="text-destructive text-sm">{{ expiryError }}</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="bg-destructive text-destructive-foreground h-9 rounded-md px-3 text-sm" @click="cancelExpiry">
                            Kein Freie Mitarbeiter/in
                        </button>
                        <button type="button" class="bg-primary text-primary-foreground hover:bg-primary/90 h-9 rounded-md px-3 text-sm" @click="confirmExpiry">
                            Ja, Freie Mitarbeiter/in
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
