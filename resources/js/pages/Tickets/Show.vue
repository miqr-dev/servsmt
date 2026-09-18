<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { computed, h, ref } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import { ArrowLeft, Check, Trash2, X } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CommentThread from '@/components/CommentThread.vue';
import type { CommentItem } from '@/components/CommentThread.vue';
import { TICKET_VIEW_GROUPS, type Participant, type TicketDetail, type TicketViewContext } from '@/lib/ticketViewFields';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/admins/showticket.blade.php, the
 * admin ticket detail page - the last piece of Tickets sub-batch 2 (the
 * list half, Tickets/AdminList.vue, shipped earlier). The old page
 * @include'd one of 35 type-specific partials under view_ticket_blades/ -
 * see resources/js/lib/ticketViewFields.ts for how 33 of those collapsed
 * into one data table (same approach as Handwerk's ITEM_GROUPS_BY_TYPE);
 * neuermitarbeiterticket and neuerteilnehmerticket didn't fit that shape and
 * are hand-built below instead.
 *
 * @comments(['model' => $ticket]) is now the shared <CommentThread>
 * component (see Korso/Show.vue and Handwerk/Show.vue for the same wiring) -
 * no ticket-specific comment work needed, this page just picks up the
 * already-built backend/component per the 2026-09-17 comments-system status
 * update.
 *
 * Found but NOT fixed here, matching this migration's standing practice of
 * flagging rather than silently changing pre-existing backend behavior:
 * - "Aktuelle Rufnummer" (custom_tel_number) was an editable input in the old
 *   page with no save handler wired to it anywhere - reproduced exactly as a
 *   text input with no working autosave, so it looks editable but nothing
 *   persists, same as before.
 * - TicketController@destroy/@restore/@mitarbeitersave all call
 *   `Comment::withTrashed()->where('commentable_id', $id)->restore()` with
 *   no `commentable_type` filter, so if a Korso/Handwerk row ever shares the
 *   same numeric id as this ticket, an unrelated comment could be restored.
 *   Also, restore($id) has no findOrFail/null-guard.
 * - The old page's "Priorität" <select> and this one both DO save (ticket.
 *   ticketPriority) - unlike Korso/Show.vue's Priorität, which is enabled
 *   but never wired to anything. Not the same page, don't conflate them.
 *
 * Restored the one closed-off UX regression this migration's "Next up" note
 * flagged as worth fixing during this port: neuerteilnehmerticket's
 * participant-delete button had a commented-out SweetAlert2 confirm dialog
 * (no confirmation at all, live) - now a plain window.confirm() (SweetAlert2
 * itself hasn't been replaced anywhere yet in this migration).
 */

const props = defineProps<{
    ticket: TicketDetail;
    ticket_status: { id: number; name: string }[];
    ticket_priority: { id: number; name: string }[];
    admins: { id: number; username: string }[];
    teilnehmers: Participant[];
    viewKey: string;
    telNewRoom: { rname: string | null; altrname: string | null } | null;
    telNewAddress: { address: string | null } | null;
    participantRequiredAtFormatted: string | null;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT Ticket', href: '/opentickets' },
            { title: 'Ticket', href: '#' },
        ];
        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const isSuperAdmin = computed(() => roles.value.includes('Super_Admin'));
const isDone = computed(() => !!props.ticket.deleted_at);

function goBack() {
    window.history.back();
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// --- Erledigt / Erledigt 2 / Wiederherstellen ---
// All three redirect to the (already-Inertia) open-tickets list on the
// backend, same as the old page's plain <form method="POST"> submits -
// router.post follows that redirect automatically. No confirm dialog here,
// matching the old page exactly (it had none on any of these three).

const submittingDone = ref(false);

function markDone() {
    submittingDone.value = true;
    const url = props.ticket.problem_type === 'Neuer Mitarbeiter' ? `/ticket.delete2/${props.ticket.id}` : `/ticket.delete/${props.ticket.id}`;
    router.post(
        url,
        {},
        {
            onFinish: () => {
                submittingDone.value = false;
            },
        },
    );
}

function restoreTicket() {
    router.post(`/ticket.restore/${props.ticket.id}`);
}

// --- Vor Ort toggle ---

const onLocation = ref(!!props.ticket.on_location);

async function toggleOnLocation() {
    const next = !onLocation.value;
    try {
        await axios.post(next ? '/ticket/on_location' : '/ticket/on_location_reverse', { ticket_id: props.ticket.id });
        onLocation.value = next;
    } catch {
        toast.error('Aktion fehlgeschlagen.');
    }
}

// --- assign / priority / status ---

const assignedTo = ref<number | ''>(props.ticket.assignedTo ?? '');
async function onAssignChange() {
    try {
        await axios.post('/ticket/assignTo', { assignedTo: assignedTo.value || null, ticket_id: props.ticket.id });
        toast.success('Zugewiesen!');
    } catch {
        toast.error('Zuweisung fehlgeschlagen.');
    }
}

const priorityId = ref(props.ticket.priority_id);
async function onPriorityChange() {
    try {
        await axios.post('/ticket/priority', { priority: priorityId.value, ticket_id: props.ticket.id });
    } catch {
        toast.error('Priorität konnte nicht gespeichert werden.');
    }
}

const ticketStatusId = ref(props.ticket.ticket_status_id);
async function onStatusChange() {
    try {
        await axios.post('/ticket/status', { status: ticketStatusId.value, ticket_id: props.ticket.id });
    } catch {
        toast.error('Status konnte nicht gespeichert werden.');
    }
}

// --- reminder modal ---

const reminderModalOpen = ref(false);
const reminderDate = ref('');
const submittingReminder = ref(false);

async function submitReminder() {
    if (!reminderDate.value) return;
    submittingReminder.value = true;
    try {
        await axios.post('/ticket/set-reminder', { date: reminderDate.value, ticketId: props.ticket.id });
        toast.success('Reminder gesetzt.');
        reminderModalOpen.value = false;
        reminderDate.value = '';
    } catch {
        toast.error('Reminder konnte nicht gesetzt werden.');
    } finally {
        submittingReminder.value = false;
    }
}

// --- dynamic per-type fields (33 of 35 types - see ticketViewFields.ts) ---

const viewContext = computed<TicketViewContext>(() => ({
    ticket: props.ticket,
    telNewRoom: props.telNewRoom,
    telNewAddress: props.telNewAddress,
}));
const viewGroup = computed(() => TICKET_VIEW_GROUPS[props.viewKey] ?? null);
const groupHeading = computed(() => viewGroup.value?.heading?.(viewContext.value) ?? props.ticket.problem_type);
const visibleFlags = computed(() => (viewGroup.value?.flags ?? []).filter((f) => !!props.ticket[f.key]));

// --- neuermitarbeiterticket (editable username/password/email, one-off layout) ---

const employeeUsername = ref(props.ticket.employee_username ?? '');
const employeeEmail = ref(props.ticket.employee_email ?? '');

const saveEmployeeUsername = useDebounceFn(async () => {
    try {
        await axios.post('/ticket/employee_username', { employee_username: employeeUsername.value, ticket_id: props.ticket.id });
    } catch {
        toast.error('Benutzername konnte nicht gespeichert werden.');
    }
}, 1000);
const saveEmployeeEmail = useDebounceFn(async () => {
    try {
        await axios.post('/ticket/employee_email', { employee_email: employeeEmail.value, ticket_id: props.ticket.id });
    } catch {
        toast.error('E-Mail konnte nicht gespeichert werden.');
    }
}, 1000);

const employeeCityBadges = computed(() => {
    const t = props.ticket;
    const badges: string[] = [];
    if (t.email_berlin) badges.push('Berlin');
    if (t.email_erfurt) badges.push('Erfurt');
    if (t.email_suhl) badges.push('Suhl');
    if (t.email_leipzig) badges.push('Leipzig');
    if (t.email_dresden) badges.push('Dresden');
    if (t.email_chemnitz) badges.push('Chemnitz');
    if (t.email_lasch) badges.push('Frau Lasch');
    if (t.email_lorenz) badges.push('Herr Lorenz');
    if (t.email_custom) badges.push(t.email_custom);
    return badges;
});

// --- neuerteilnehmerticket (editable participant table) ---

const participants = ref<Participant[]>(props.teilnehmers.map((p: Participant) => ({ ...p })));

const saveParticipantUsername = useDebounceFn(async (participant: Participant) => {
    try {
        await axios.post('/participant.username', { password_id: participant.id, username: participant.username });
    } catch {
        toast.error('Benutzername konnte nicht gespeichert werden.');
    }
}, 800);

async function deleteParticipant(participant: Participant) {
    if (!confirm('Sind Sie sicher? Sie können dies nicht rückgängig machen!')) return;
    router.delete(`/participants/${participant.id}`, {
        onSuccess: () => {
            participants.value = participants.value.filter((p) => p.id !== participant.id);
        },
        onError: () => toast.error('Teilnehmer konnte nicht gelöscht werden.'),
    });
}
</script>

<template>
    <Head title="Ticket" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <button
            type="button"
            class="border-input hover:bg-accent inline-flex h-9 w-9 items-center justify-center rounded-md border"
            title="Zurück"
            @click="goBack"
        >
            <ArrowLeft class="h-4 w-4" />
        </button>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Left card -->
            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-1">
                <div class="text-center">
                    <p class="text-muted-foreground text-sm">{{ isDone ? 'Erledigt von' : 'Zugewiesen an' }}</p>
                    <h3 class="text-lg font-bold" style="color: #661421">
                        {{ isDone ? ticket.done_by : (ticket.user?.username ?? '') }}
                    </h3>
                </div>

                <button
                    v-if="isDone"
                    type="button"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center justify-center rounded-md text-sm"
                    @click="restoreTicket"
                >
                    Wiederherstellen
                </button>

                <template v-else-if="isSuperAdmin">
                    <button
                        type="button"
                        class="border-input hover:bg-accent inline-flex h-9 items-center justify-center rounded-md border text-sm"
                        @click="reminderModalOpen = true"
                    >
                        Reminder
                    </button>
                    <button
                        type="button"
                        :disabled="submittingDone"
                        class="inline-flex h-9 items-center justify-center rounded-md border-2 border-green-600 px-3 text-sm text-green-600 transition-colors hover:!bg-green-600 hover:!text-white disabled:opacity-50"
                        @click="markDone"
                    >
                        {{ ticket.problem_type === 'Neuer Mitarbeiter' ? 'Erledigt 2' : 'Erledigt' }}
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-9 items-center justify-center rounded-md text-sm text-white"
                        :class="onLocation ? 'bg-destructive' : 'bg-primary hover:bg-primary/90'"
                        @click="toggleOnLocation"
                    >
                        {{ onLocation ? 'Nein, ab Erfurt möglich' : 'Vor Ort' }}
                    </button>
                    <select
                        v-model="assignedTo"
                        class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                        @change="onAssignChange"
                    >
                        <option value="">Zuweisen</option>
                        <option v-for="admin in admins" :key="admin.id" :value="admin.id">{{ admin.username }}</option>
                    </select>
                </template>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-muted-foreground text-xs">Ersteller</label>
                        <input
                            type="text"
                            readonly
                            class="border-input bg-muted mt-1 h-9 w-full rounded-md border px-2 text-sm"
                            :value="`${ticket.subUser?.vorname ?? ''} ${ticket.subUser?.name ?? ''}`"
                        />
                    </div>
                    <div>
                        <label class="text-muted-foreground text-xs">Am</label>
                        <input
                            type="text"
                            readonly
                            class="border-input bg-muted mt-1 h-9 w-full rounded-md border px-2 text-sm"
                            :value="formatDate(ticket.created_at)"
                        />
                    </div>
                    <div>
                        <label class="text-muted-foreground text-xs">Standort</label>
                        <input
                            type="text"
                            readonly
                            class="border-input bg-muted mt-1 h-9 w-full rounded-md border px-2 text-sm"
                            :value="ticket.subUser?.ort ?? ''"
                        />
                    </div>
                    <div>
                        <label class="text-muted-foreground text-xs">Adresse</label>
                        <input
                            type="text"
                            readonly
                            class="border-input bg-muted mt-1 h-9 w-full rounded-md border px-2 text-sm"
                            :value="ticket.subUser?.straße ?? ''"
                        />
                    </div>
                </div>

                <div>
                    <label class="text-muted-foreground text-xs">Priorität</label>
                    <select
                        v-if="isSuperAdmin"
                        v-model="priorityId"
                        class="border-input bg-background mt-1 h-9 w-full rounded-md border px-2 text-sm"
                        @change="onPriorityChange"
                    >
                        <option v-for="p in ticket_priority" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <input
                        v-else
                        type="text"
                        readonly
                        class="border-input bg-muted mt-1 h-9 w-full rounded-md border px-2 text-sm"
                        :value="ticket.ticket_priority?.name ?? ''"
                    />
                </div>

                <div v-if="isSuperAdmin">
                    <label class="text-muted-foreground text-xs">Status</label>
                    <select
                        v-model="ticketStatusId"
                        class="border-input bg-background mt-1 h-9 w-full rounded-md border px-2 text-sm"
                        @change="onStatusChange"
                    >
                        <option v-for="s in ticket_status" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="text-muted-foreground text-xs">Telefon</label>
                    <input
                        type="text"
                        readonly
                        class="border-input bg-muted mt-1 h-9 w-full rounded-md border px-2 text-sm"
                        :value="ticket.tel_number ?? ''"
                    />
                </div>
                <div>
                    <!--
                        Old page: editable input, but no save handler was ever
                        wired to it (checked TicketController.php - nothing
                        reads a "custom_tel_number" field from any AJAX
                        route). Reproduced exactly - looks editable, doesn't
                        persist. Flagged in this file's top doc comment.
                    -->
                    <label class="text-muted-foreground text-xs">Aktuelle Rufnummer</label>
                    <input
                        type="text"
                        :value="ticket.custom_tel_number ?? ''"
                        class="border-input bg-background mt-1 h-9 w-full rounded-md border px-2 text-sm"
                    />
                </div>
            </div>

            <!-- Right card -->
            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <!-- neuermitarbeiterticket: one-off layout -->
                <div v-if="viewKey === 'neuermitarbeiterticket'" class="flex flex-col gap-3">
                    <h4 class="ticket_header text-center font-semibold">{{ ticket.problem_type }}</h4>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        <input
                            v-model="employeeUsername"
                            type="text"
                            placeholder="Benutzername"
                            class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                            @input="saveEmployeeUsername"
                        />
                        <input type="text" value="Miqr.2026#" readonly class="border-input bg-muted h-9 rounded-md border px-2 text-sm" />
                        <input
                            v-model="employeeEmail"
                            type="text"
                            placeholder="Email"
                            class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                            @input="saveEmployeeEmail"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div>
                            <span class="text-muted-foreground text-xs">Berechtigungen wie bei</span>
                            <p class="font-medium">{{ ticket.replication?.name ?? '' }}, {{ ticket.replication?.vorname ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground text-xs">Beginnt am</span>
                            <p class="font-medium">{{ ticket.employee_required_at ? formatDate(ticket.employee_required_at) : '' }}</p>
                        </div>
                        <div v-if="ticket.employee_finish_at">
                            <span class="text-muted-foreground text-xs">Endet zum</span>
                            <p class="font-medium" style="color: rgba(255, 0, 0, 0.6)">{{ formatDate(ticket.employee_finish_at) }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground text-xs">Name</span>
                            <p class="font-medium">{{ ticket.employee_lastname ?? '' }}, {{ ticket.employee_firstname ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground text-xs">Standort</span>
                            <p class="font-medium">{{ ticket.location?.place?.pnname ?? '' }}, {{ ticket.location?.address ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground text-xs">Telefon</span>
                            <p class="font-medium">{{ ticket.telephone_employee ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground text-xs">Position</span>
                            <p class="font-medium">{{ ticket.position_employee ?? '' }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground text-xs">Abteilung</span>
                            <p class="font-medium">{{ ticket.abteilung_employee ?? '' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span v-if="ticket.isplus" class="inline-flex items-center gap-1 text-sm font-semibold">
                            IS+ <Check class="h-4 w-4 text-green-600" />
                        </span>
                        <span v-if="ticket.outlook" class="inline-flex items-center gap-1 text-sm font-semibold">
                            Outlook <Check class="h-4 w-4 text-green-600" />
                        </span>
                    </div>
                    <hr />
                    <div class="flex flex-wrap gap-3">
                        <span v-for="badge in employeeCityBadges" :key="badge" class="inline-flex items-center gap-1 text-sm font-semibold">
                            {{ badge }} <Check class="h-4 w-4 text-green-600" />
                        </span>
                    </div>
                </div>

                <!-- neuerteilnehmerticket: participant table -->
                <div v-else-if="viewKey === 'neuerteilnehmerticket'" class="flex flex-col gap-2">
                    <span v-if="participantRequiredAtFormatted" class="mx-auto mb-2 text-lg font-semibold" style="color: #661421">
                        {{ participantRequiredAtFormatted }}
                    </span>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-muted-foreground text-left">
                                <tr>
                                    <th class="p-2">Vorname</th>
                                    <th class="p-2">Nachname</th>
                                    <th class="p-2">Benutzername</th>
                                    <th class="p-2">Passwort</th>
                                    <th class="p-2">Maßnahme</th>
                                    <th class="p-2">Email</th>
                                    <th class="p-2">Standort</th>
                                    <th class="p-2">Bemerkung</th>
                                    <th class="p-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="teilnehmer in participants" :key="teilnehmer.id">
                                    <td class="p-2">{{ teilnehmer.vorname }}</td>
                                    <td class="p-2">{{ teilnehmer.nachname }}</td>
                                    <td class="p-2">
                                        <input
                                            v-if="isSuperAdmin"
                                            v-model="teilnehmer.username"
                                            type="text"
                                            class="border-input bg-background h-8 w-32 rounded-md border px-2 text-sm"
                                            @blur="saveParticipantUsername(teilnehmer)"
                                        />
                                        <template v-else>{{ teilnehmer.username }}</template>
                                    </td>
                                    <td class="p-2 font-mono">{{ teilnehmer.password }}</td>
                                    <td class="p-2">{{ teilnehmer.course }}</td>
                                    <td class="p-2">{{ teilnehmer.email }}</td>
                                    <td class="p-2">{{ teilnehmer.location }}</td>
                                    <td class="p-2">{{ teilnehmer.notes_participant }}</td>
                                    <td class="p-2">
                                        <button type="button" class="text-destructive hover:opacity-70" @click="deleteParticipant(teilnehmer)">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Every other type: the shared data-driven field list -->
                <div v-else class="flex flex-col gap-3">
                    <h4 class="ticket_header text-center font-semibold">{{ groupHeading }}</h4>
                    <div v-if="viewGroup" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div v-for="field in viewGroup.fields" :key="field.label">
                            <span class="text-muted-foreground text-xs">{{ field.label }}</span>
                            <p class="font-medium">{{ field.value(viewContext) }}</p>
                        </div>
                    </div>
                    <template v-if="viewGroup?.warning && ticket[viewGroup.warning.key]">
                        <p class="text-destructive text-lg font-bold">{{ viewGroup.warning.label }}</p>
                    </template>
                    <template v-if="visibleFlags.length">
                        <strong v-if="viewGroup?.flagsHeading" style="color: #661421">{{ viewGroup.flagsHeading }}</strong>
                        <div class="flex flex-wrap gap-3">
                            <span v-for="flag in visibleFlags" :key="flag.label" class="inline-flex items-center gap-1 text-sm font-medium">
                                {{ flag.label }}
                            </span>
                        </div>
                    </template>
                </div>

                <div class="border-t pt-3">
                    <strong style="color: #661421">Beschreibung</strong>
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <div class="mt-2 text-sm" v-html="ticket.notizen"></div>
                </div>

                <div class="border-t pt-3">
                    <label class="mb-2 block font-medium">Kommentar</label>
                    <CommentThread model-type="App\Ticket" :model-id="ticket.id" :comments="(ticket.comments as CommentItem[])" :is-done="isDone" />
                </div>
            </div>
        </div>
    </div>

    <!-- Reminder modal -->
    <Transition enter-active-class="transition-opacity duration-150" leave-active-class="transition-opacity duration-150" enter-from-class="opacity-0" leave-to-class="opacity-0">
        <div
            v-if="reminderModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            @click.self="reminderModalOpen = false"
        >
            <div class="bg-card text-card-foreground w-full max-w-sm rounded-xl border shadow-xl">
                <div class="flex items-center justify-between border-b p-4">
                    <h4 class="font-semibold">Set Reminder</h4>
                    <button type="button" class="text-muted-foreground hover:text-foreground" @click="reminderModalOpen = false">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <div class="flex flex-col gap-3 p-4">
                    <label class="text-sm font-medium" for="reminder-date">Date</label>
                    <input
                        id="reminder-date"
                        v-model="reminderDate"
                        type="date"
                        required
                        class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                    />
                    <button
                        type="button"
                        :disabled="submittingReminder || !reminderDate"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center justify-center rounded-md text-sm disabled:opacity-50"
                        @click="submitReminder"
                    >
                        Set Reminder
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
