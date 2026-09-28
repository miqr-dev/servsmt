<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, watch } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { isoDate, isoToDmy } from '@/lib/ticketDates';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/users/emailForward.blade.php
 * (TicketController@emailForward, route /ticket.forward -> TicketController@store,
 * problem_type "Email Weiterleitung").
 *
 * - The two select2 user pickers ("Weiterleitung an" / "Weiterleitung von")
 *   are the shared searchable Combobox now. Labels are computed server-side
 *   exactly like the old Blade did (config/forwarding.php mailbox label for
 *   shared Sekretariat mailboxes, otherwise "Name, Vorname"); "von"
 *   defaults to the logged-in user, same as before.
 * - The two daterangepicker fields are native date inputs; both default to
 *   today with today as the minimum, and "Bis" can't be earlier than
 *   "Benötigt ab" (the old apply.daterangepicker handler pushed the end
 *   date forward the same way). Submitted as DD-MM-YYYY via
 *   form.transform(), since store() validates `date_format:d-m-Y` - no
 *   backend change. store()'s own "Bis before Benötigt ab" check still
 *   runs and its error is shown under the field.
 */

type ForwardUser = { id: number; label: string };

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    forwardUsers: ForwardUser[];
    currentUserId: number;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'Email Weiterleiten', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const today = isoDate();

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Email Weiterleitung',
    forward_on: '' as string | number,
    forward_from: props.currentUserId as string | number,
    forward_required_at: today,
    forward_to_at: today,
    notizen: '',
});

const userOptions = computed(() => props.forwardUsers.map((u) => ({ value: u.id, label: u.label })));

// Keep "Bis" >= "Benötigt ab", like the old picker's minDate update.
watch(
    () => form.forward_required_at,
    (start) => {
        if (start && form.forward_to_at && form.forward_to_at < start) form.forward_to_at = start;
    },
);

function submit() {
    form
        .transform((data) => ({
            ...data,
            forward_required_at: isoToDmy(data.forward_required_at),
            forward_to_at: isoToDmy(data.forward_to_at),
        }))
        .post('/form_store');
}
</script>

<template>
    <Head title="Email Weiterleiten" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Email Weiterleiten</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Weiterleitung an <span class="text-muted-foreground">*</span></label>
                        <Combobox v-model="form.forward_on" :options="userOptions" placeholder="Mitarbeiter" required />
                        <p v-if="form.errors.forward_on" class="text-destructive text-xs">{{ form.errors.forward_on }}</p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Weiterleitung von <span class="text-muted-foreground">*</span></label>
                        <Combobox v-model="form.forward_from" :options="userOptions" placeholder="Mitarbeiter" required />
                        <p v-if="form.errors.forward_from" class="text-destructive text-xs">{{ form.errors.forward_from }}</p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Benötigt ab <span class="text-muted-foreground">*</span></label>
                        <input
                            v-model="form.forward_required_at"
                            type="date"
                            :min="today"
                            required
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                        />
                        <p v-if="form.errors.forward_required_at" class="text-destructive text-xs">{{ form.errors.forward_required_at }}</p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Bis <span class="text-muted-foreground">*</span></label>
                        <input
                            v-model="form.forward_to_at"
                            type="date"
                            :min="form.forward_required_at || today"
                            required
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                        />
                        <p class="text-primary text-xs">
                            Zur Aufhebung ist kein Ticket erforderlich, die Weiterleitung wird nach dem angegebenen Datum entfernt.
                        </p>
                        <p v-if="form.errors.forward_to_at" class="text-destructive text-xs">{{ form.errors.forward_to_at }}</p>
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
    </div>
</template>
