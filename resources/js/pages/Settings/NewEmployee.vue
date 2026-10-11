<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarClock, KeyRound, TriangleAlert } from '@lucide/vue';
import { computed, h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Einstellungen > Neuer Mitarbeiter (NewEmployeeSettingsController,
 * App\Support\EmployeePassword, 2026-10-11): the default Windows password
 * sent in the "Neuer Mitarbeiter" mail (Erledigt 2). Was hardcoded in
 * TicketNotification. Changes every 1 January (the year goes up); from then
 * until it is saved, Super_Admins get a reminder (Dashboard, ticket page,
 * mail 1-7 January). Saving the same value again also clears the reminder.
 */

const props = defineProps<{
    password: string;
    changedAt: string | null;
    changedBy: string | null;
    outdated: boolean;
    /** same password with this year in it, e.g. 2026 -> 2027 */
    suggestion: string | null;
    year: number;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Neuer Mitarbeiter', href: '/settings/neuer-mitarbeiter' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({ password: props.password });

const changedLabel = computed(() => {
    if (!props.changedAt) return null;
    const date = new Date(props.changedAt).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });

    return props.changedBy ? `${date} von ${props.changedBy}` : date;
});

function useSuggestion() {
    if (props.suggestion) form.password = props.suggestion;
}

function save() {
    form.put('/settings/neuer-mitarbeiter', { preserveScroll: true });
}
</script>

<template>
    <Head title="Neuer Mitarbeiter" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Neuer Mitarbeiter</h1>

        <div
            v-if="outdated"
            class="flex max-w-2xl gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-100"
        >
            <TriangleAlert class="mt-0.5 h-5 w-5 shrink-0" />
            <div class="flex flex-col gap-2">
                <p>
                    <b>Neues Jahr:</b> Das Standard-Passwort wurde {{ year }} noch nicht geändert. Bitte zuerst im Active Directory ändern und hier
                    eintragen.
                </p>
                <button
                    v-if="suggestion"
                    type="button"
                    class="inline-flex h-8 items-center self-start rounded-md border border-amber-400 bg-white px-3 text-sm hover:bg-amber-100 dark:bg-transparent"
                    @click="useSuggestion"
                >
                    Vorschlag übernehmen: <span class="ml-1 font-mono">{{ suggestion }}</span>
                </button>
                <p class="text-xs">Bleibt das Passwort gleich, einfach speichern. Dann verschwindet die Erinnerung auch.</p>
            </div>
        </div>

        <form class="bg-card text-card-foreground flex max-w-2xl flex-col gap-4 rounded-xl border p-5 shadow-sm" @submit.prevent="save">
            <div class="flex items-start gap-3">
                <KeyRound class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <h2 class="font-semibold">Standard-Windows-Passwort</h2>
                    <p class="text-muted-foreground text-sm">
                        Wird mit der E-Mail „Neuer Mitarbeiter“ (Erledigt 2) an alle Empfänger verschickt. Muss zum Standard-Passwort im Active Directory
                        passen.
                    </p>
                </div>
            </div>

            <label class="flex flex-col gap-1.5 text-sm">
                <span class="font-medium">Passwort</span>
                <input
                    v-model="form.password"
                    type="text"
                    autocomplete="off"
                    spellcheck="false"
                    maxlength="100"
                    class="border-input bg-background h-10 w-full max-w-sm rounded-md border px-3 font-mono text-base"
                />
                <span v-if="form.errors.password" class="text-destructive text-xs">{{ form.errors.password }}</span>
            </label>

            <div class="text-muted-foreground flex items-start gap-2 text-xs">
                <CalendarClock class="mt-0.5 h-4 w-4 shrink-0" />
                <div>
                    <p v-if="changedLabel">Zuletzt gespeichert am {{ changedLabel }}.</p>
                    <p>
                        Jedes Jahr am 1. Januar ändern (z. B. 2026 → 2027). Bis es im neuen Jahr gespeichert ist, erinnern Dashboard und das Ticket „Neuer
                        Mitarbeiter“ daran; am 1.–7. Januar kommt zusätzlich eine E-Mail an alle Super_Admins.
                    </p>
                </div>
            </div>

            <div>
                <button
                    type="submit"
                    :disabled="form.processing || !form.password.trim()"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-4 text-sm disabled:opacity-50"
                >
                    Speichern
                </button>
            </div>
        </form>
    </div>
</template>
