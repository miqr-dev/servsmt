<script setup lang="ts">
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { computed, h, onMounted, reactive } from 'vue';
import LocationRoomFields from '@/components/tickets/LocationRoomFields.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';
import { useTicketLocations } from '@/composables/useTicketLocations';
import AppLayout from '@/layouts/AppLayout.vue';
import { isoDate, isoToDmy } from '@/lib/ticketDates';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/users/employee.blade.php
 * (TicketController@employee, route /ticket.employee -> TicketController@store,
 * problem_type "Neuer Mitarbeiter").
 *
 * - "Berechtigungen wie bei" (replication_id) select2 -> shared Combobox,
 *   labels "Name, Vorname" as before.
 * - Standort (location_id) is the shared grouped-by-city Standort select
 *   (components/tickets/LocationRoomFields.vue with the Raum select hidden),
 *   fed by item.listen exactly like the old inline jQuery.
 * - "Beginnt am" (default today + 7 days, not before today) and the
 *   optional "Endet zum" (freelancers only, starts empty) are native date
 *   inputs, submitted as DD-MM-YYYY via form.transform() - the same string
 *   format the old daterangepicker posted, so store()'s raw assignment is
 *   unchanged.
 * - "Benutzerdaten senden an": each checkbox posts its fixed mailbox
 *   address as the value (email_erfurt = "Sekretariat_Erfurt@miqr.de", ...)
 *   when ticked and nothing otherwise - same as the old form's
 *   checkbox value="..." attributes. "Herrn Kirchner" and the current user
 *   were always-ticked, un-untickable display-only boxes with no field that
 *   store() reads (email_kirchner is never saved; the current-user box had
 *   no name at all) - reproduced as disabled, ticked boxes. The
 *   commented-out "Herrn Ockel" box stays dropped.
 * - Outlook / IS+ checkboxes post booleans (same as every other converted
 *   Tickets form's checkboxes).
 */

type ReplicationUser = { id: number; label: string };

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    replicationUsers: ReplicationUser[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'Neuer Mitarbeiter', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const currentUsername = computed(() => (page.props.auth.user as { username?: string } | null)?.username ?? '');

const EMAIL_RECIPIENTS = [
    { key: 'email_erfurt', label: 'Sek. Erfurt', email: 'Sekretariat_Erfurt@miqr.de', col: 1 },
    { key: 'email_berlin', label: 'Sek. Berlin', email: 'Sekretariat_Berlin@miqr.de', col: 1 },
    { key: 'email_leipzig', label: 'Sek. Leipzig', email: 'Sekretariat_Leipzig@miqr.de', col: 1 },
    { key: 'email_dresden', label: 'Sek. Dresden', email: 'Sekretariat_Dresden@miqr.de', col: 1 },
    { key: 'email_chemnitz', label: 'Sek. Chemnitz', email: 'Sekretariat_Chemnitz@miqr.de', col: 1 },
    { key: 'email_suhl', label: 'Sek. Suhl', email: 'Sekretariat_Suhl@miqr.de', col: 1 },
    { key: 'email_lorenz', label: 'Herrn Lorenz', email: 'Martin.Lorenz@miqr.de', col: 2 },
    { key: 'email_lasch', label: 'Frau Lasch', email: 'Antje.Lasch-Rosenhoff@miqr.de', col: 2 },
] as const;
type EmailKey = (typeof EMAIL_RECIPIENTS)[number]['key'];

const emailChecked = reactive<Record<EmailKey, boolean>>(
    Object.fromEntries(EMAIL_RECIPIENTS.map((r) => [r.key, false])) as Record<EmailKey, boolean>,
);

const today = isoDate();

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: 'Neuer Mitarbeiter',
    replication_id: '' as string | number,
    employee_required_at: isoDate(7),
    employee_lastname: '',
    employee_firstname: '',
    employee_finish_at: '',
    email_custom: '',
    location_id: '' as string | number,
    position_employee: '',
    abteilung_employee: '',
    telephone_employee: '',
    outlook: false,
    isplus: false,
    notizen: '',
});

const replicationOptions = computed(() => props.replicationUsers.map((u) => ({ value: u.id, label: u.label })));

const { locations, places, load, roomsFor } = useTicketLocations();
onMounted(load);

function submit() {
    form
        .transform((data) => ({
            ...data,
            employee_required_at: isoToDmy(data.employee_required_at),
            employee_finish_at: isoToDmy(data.employee_finish_at) || null,
            ...Object.fromEntries(EMAIL_RECIPIENTS.map((r) => [r.key, emailChecked[r.key] ? r.email : null])),
        }))
        .post('/form_store');
}
</script>

<template>
    <Head title="Neuer Mitarbeiter" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Neuer Mitarbeiter</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- left column: person + recipients -->
                    <div class="flex flex-col gap-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="flex flex-col gap-1 sm:col-span-2">
                                <label class="text-sm font-medium">Berechtigungen wie bei <span class="text-muted-foreground">*</span></label>
                                <Combobox v-model="form.replication_id" :options="replicationOptions" required />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium">Beginnt am <span class="text-muted-foreground">*</span></label>
                                <input
                                    v-model="form.employee_required_at"
                                    type="date"
                                    :min="today"
                                    required
                                    class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium">Nachname <span class="text-muted-foreground">*</span></label>
                                <input v-model="form.employee_lastname" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium">Vorname <span class="text-muted-foreground">*</span></label>
                                <input v-model="form.employee_firstname" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                            </div>
                            <div class="flex flex-col gap-1 sm:col-span-2">
                                <label class="text-sm font-medium">
                                    Endet zum <span class="text-muted-foreground text-xs font-normal">Nur bei freien MA.</span>
                                </label>
                                <input v-model="form.employee_finish_at" type="date" class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                            </div>
                        </div>

                        <fieldset class="flex flex-col gap-2 rounded-md border p-3">
                            <legend class="px-1 text-sm font-medium">Benutzerdaten senden an</legend>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                                <div class="flex flex-col gap-1">
                                    <label v-for="r in EMAIL_RECIPIENTS.filter((x) => x.col === 1)" :key="r.key" class="flex items-center gap-2 text-sm">
                                        <input v-model="emailChecked[r.key]" type="checkbox" class="text-primary h-4 w-4" />
                                        {{ r.label }}
                                    </label>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label v-for="r in EMAIL_RECIPIENTS.filter((x) => x.col === 2)" :key="r.key" class="flex items-center gap-2 text-sm">
                                        <input v-model="emailChecked[r.key]" type="checkbox" class="text-primary h-4 w-4" />
                                        {{ r.label }}
                                    </label>
                                    <label class="text-muted-foreground flex items-center gap-2 text-sm">
                                        <input type="checkbox" checked disabled class="h-4 w-4" />
                                        Herrn Kirchner
                                    </label>
                                    <label class="text-muted-foreground flex items-center gap-2 text-sm">
                                        <input type="checkbox" checked disabled class="h-4 w-4" />
                                        {{ currentUsername }}
                                    </label>
                                </div>
                            </div>
                            <div class="mt-2 flex flex-col gap-1">
                                <label class="text-sm font-medium">Andere Email</label>
                                <span class="text-muted-foreground text-xs">
                                    Mehrere E-Mails? Bitte trennen Sie sie mit einem Semikolon <strong class="text-primary">( ; )</strong>
                                </span>
                                <input v-model="form.email_custom" type="text" class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                            </div>
                        </fieldset>
                    </div>

                    <!-- right column: job details -->
                    <fieldset class="flex flex-col gap-3 self-start rounded-md border p-3">
                        <LocationRoomFields
                            v-model:place="form.location_id"
                            :locations="locations"
                            :places="places"
                            :rooms-for="roomsFor"
                            :show-room-select="false"
                        />
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Position <span class="text-muted-foreground">*</span></label>
                            <input v-model="form.position_employee" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Abteilung <span class="text-muted-foreground">*</span></label>
                            <input v-model="form.abteilung_employee" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm font-medium">Telefon <span class="text-muted-foreground">*</span></label>
                            <input v-model="form.telephone_employee" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                        </div>
                        <div class="flex justify-around gap-4 pt-1">
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.outlook" type="checkbox" class="text-primary h-4 w-4" />
                                Outlook
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input v-model="form.isplus" type="checkbox" class="text-primary h-4 w-4" />
                                IS+
                            </label>
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
