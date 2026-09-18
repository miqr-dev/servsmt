<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { computed, h, ref } from 'vue';
import { ArrowLeft, File, FileDown, FileSpreadsheet, FileText, Pencil, RotateCcw, Trash2, Upload } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import CommentThread from '@/components/CommentThread.vue';
import type { Auth, BreadcrumbItem } from '@/types';
import type { CommentItem } from '@/components/CommentThread.vue';

/**
 * Converted from resources/views/korso/show.blade.php. Two separate comment
 * systems live on this page, and they get very different treatment:
 *
 * - `@comments(['model' => $korso])` was backed by the abandoned laravelista
 *   package; rebuilt as first-party code (app/Concerns/Commentable.php,
 *   CommentController, CommentPolicy - see modernization-audit.md Section 3)
 *   and rendered below via the shared <CommentThread> component. Soft-deleted
 *   comments stay visible even once the ticket is done (Commentable::
 *   comments() uses withTrashed()).
 * - `internalComments` (KorsoInternalComment/KorsoInternalCommentController)
 *   is a separate, fully-working "internal notes" feature specific to Korso
 *   admins - unrelated to the thread above, converted below as-is (add/edit/
 *   soft-delete/restore, own-comment-only editing, same as the old page).
 *
 * The old page's "Priorität" <select> has no change handler anywhere in the
 * original jQuery either (only the Status <select> actually saves via AJAX)
 * - reproduced the same way: enabled for admins, not wired to anything. Not
 * fixing that here, just not regressing it either.
 */

type NamedUser = { id: number; name: string; vorname: string | null } | null;
type SubUser = { vorname: string | null; name: string | null; ort: string | null; position: string | null; abteilung: string | null } | null;
type KorsoItem = { id: number; item_name: string; quantity: number; details: string | null };
type Attachment = { id: number; file_path: string; file_type: string; context: string | null };
type InternalComment = {
    id: number;
    user_id: number;
    comment: string;
    is_deleted: boolean;
    created_at: string;
    user: { id: number; name: string } | null;
};
type NamedItem = { name: string } | null;
type TicketStatus = { id: number; name: string };
type LocationInfo = { place: { pnname: string | null } | null } | null;

type KorsoDetail = {
    id: number;
    problem_type: string;
    priority: number;
    ticket_status_id: number | null;
    tel_number: string | null;
    // Recorded on the ticket itself at creation time - unlike subUser (the
    // App\User relation via "submitter"), always present, so used as a
    // fallback below for tickets where that relation can't resolve.
    submitter_name: string | null;
    submitter_standort: string | null;
    submitter_adresse: string | null;
    created_at: string;
    deleted_at: string | null;
    assignedTo: number | null;
    done_by: number | null;
    notizen: string | null;
    problem_in_city: string | null;
    is_chatgpt_project: boolean;
    chatgpt_project_name: string | null;
    chatgpt_introduction_reason: string | null;
    chatgpt_goal: string | null;
    chatgpt_process_steps: string | null;
    chatgpt_has_existing_process: boolean | null;
    chatgpt_has_output_examples: boolean | null;
    chatgpt_has_knowledge_base: boolean | null;
    chatgpt_output_examples: string | null;
    chatgpt_knowledge_base: string | null;
    chatgpt_additional_requirements: string | null;
    korsoItems: KorsoItem[];
    korsoAttachments: Attachment[];
    internalComments: InternalComment[];
    comments: CommentItem[];
    onlinemarketingItem: NamedItem;
    zertifizierungItem: NamedItem;
    massnahme: NamedItem;
    subUser: SubUser;
    doneByUser: NamedUser;
    assignedUser: NamedUser;
    location: LocationInfo;
};

const props = defineProps<{
    korso: KorsoDetail;
    korso_ma_users: { id: number; name: string; vorname: string | null }[];
    ticket_statuses: TicketStatus[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Ticket', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const currentUserId = computed(() => page.props.auth.user?.id);
const canManage = computed(
    () => roles.value.includes('Super_Admin') || roles.value.includes('Korso_ma') || roles.value.includes('Korso_Admin'),
);
// Matches the old Blade page exactly - attachment upload/delete was gated to
// Korso_ma specifically, not the broader Super_Admin/Korso_Admin set above.
const isKorsoMa = computed(() => roles.value.includes('Korso_ma'));

const isDone = computed(() => !!props.korso.deleted_at);

// Forwards the filter/user_id/page query string this page was opened with
// (set by Dashboard.vue's ticketHref()) back onto every "return to
// dashboard" exit point below, so the admin/filter tab that was active when
// this ticket was opened is still active after going back - not just on the
// explicit Dashboard button, but also after assigning or completing the
// ticket from here, since those redirect to the dashboard too.
const dashboardHref = computed(() => `/korso-dashboard${window.location.search}`);

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function creatorName(subUser: SubUser, submitterName: string | null): string {
    const name = `${subUser?.vorname ?? ''} ${subUser?.name ?? ''}`.trim();
    return name || submitterName?.trim() || 'Unknown';
}

// --- assignment / status / done / restore ---

const assignedTo = ref<number | ''>(props.korso.assignedTo ?? '');

async function onAssignChange() {
    try {
        await axios.post('/korso/assign', { ticket_id: props.korso.id, user_id: assignedTo.value });
        toast.success(assignedTo.value ? 'Benutzer erfolgreich zugewiesen.' : 'Ticketzuweisung erfolgreich aufgehoben!');
        setTimeout(() => {
            window.location.href = dashboardHref.value;
        }, 1200);
    } catch {
        toast.error('Fehler beim Zuweisen des Benutzers.');
    }
}

async function markDone() {
    if (!confirm('Möchten Sie dieses Ticket wirklich als erledigt markieren?')) return;

    try {
        await axios.post(`/korso/${props.korso.id}/done`);
        toast.success('Ticket wurde als erledigt markiert.');
        setTimeout(() => {
            window.location.href = dashboardHref.value;
        }, 1200);
    } catch {
        toast.error('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
    }
}

async function restoreTicket() {
    try {
        await axios.post(`/korso/${props.korso.id}/restore`);
        toast.success('Ticket wiederhergestellt.');
        setTimeout(() => window.location.reload(), 1200);
    } catch {
        toast.error('Fehler beim Wiederherstellen des Tickets.');
    }
}

const ticketStatusId = ref<number | null>(props.korso.ticket_status_id);

async function onStatusChange() {
    try {
        const { data } = await axios.post(`/korso/update-status/${props.korso.id}`, {
            ticket_status_id: ticketStatusId.value,
        });
        toast.success(data.message ?? 'Status erfolgreich aktualisiert.');
    } catch {
        toast.error('Fehler beim Aktualisieren des Status.');
    }
}

// --- notizen (strip the auto-appended ChatGPT block, same as the old @php block) ---

const cleanNotes = computed(() => {
    let notes = props.korso.notizen;
    if (props.korso.is_chatgpt_project && notes?.includes('<h5>ChatGPT-Projektvorschläge</h5>')) {
        const idx = notes.indexOf('<hr>');
        notes = idx !== -1 ? notes.slice(0, idx) : null;
    }
    return notes;
});
const hasCleanNotes = computed(() => !!cleanNotes.value?.replace(/<[^>]*>/g, '').trim());

// --- attachments ---

const attachments = ref<Attachment[]>([...(props.korso.korsoAttachments ?? [])]);

const generalAttachments = computed(() => attachments.value.filter((a) => !a.context));

function attachmentsFor(context: string) {
    return attachments.value.filter((a) => a.context === context);
}

function attachmentUrl(path: string): string {
    return `/storage/${path}`;
}

// Matches the old ucwords(str_replace(['_', '-'], [' ', '-'], $item->item_name))
// exactly - only underscores become spaces, hyphens are left alone.
function formatItemName(name: string): string {
    return name.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

// Same field order/labels and empty-value skipping as the old Blade's
// Visitenkarten detail list.
const VISITENKARTE_FIELDS: { key: string; label: string }[] = [
    { key: 'name', label: 'Name' },
    { key: 'email', label: 'Email' },
    { key: 'telephone', label: 'Telefon' },
    { key: 'position', label: 'Position' },
    { key: 'adresse', label: 'Adresse' },
    { key: 'fax', label: 'Fax' },
];

function visitenkarteDetails(details: string): { label: string; value: string }[] {
    const parsed = JSON.parse(details) as Record<string, string | undefined>;
    return VISITENKARTE_FIELDS.filter((f) => parsed[f.key]).map((f) => ({ label: f.label, value: parsed[f.key]! }));
}

function attachmentIcon(fileType: string) {
    if (fileType === 'application/pdf' || fileType.includes('word')) return FileText;
    if (fileType.includes('excel') || fileType.includes('spreadsheet')) return FileSpreadsheet;
    return File;
}

const uploadInput = ref<HTMLInputElement | null>(null);

async function onFilesSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    const files = input.files;
    if (!files || files.length === 0) return;

    const formData = new FormData();
    for (const file of Array.from(files)) {
        formData.append('attachments[]', file);
    }

    try {
        // Let the browser set the multipart Content-Type (with boundary) itself -
        // setting it manually here would strip the boundary and break parsing.
        await axios.post(`/korso/${props.korso.id}/upload-attachment`, formData);
        toast.success('Dateien wurden erfolgreich hochgeladen!');
        setTimeout(() => window.location.reload(), 1200);
    } catch {
        toast.error('File upload failed!');
    } finally {
        input.value = '';
    }
}

async function deleteAttachment(attachment: Attachment) {
    if (!confirm('Möchten Sie diesen Anhang wirklich löschen?')) return;

    try {
        const { data } = await axios.delete(`/korso/${props.korso.id}/attachment/${attachment.id}`);
        toast.success(data.message ?? 'Anhang wurde erfolgreich gelöscht');
        attachments.value = attachments.value.filter((a) => a.id !== attachment.id);
    } catch {
        toast.error('Löschen des Anhangs fehlgeschlagen!');
    }
}

// --- internal comments (KorsoInternalComment - working feature, not deferred) ---

const internalComments = ref<InternalComment[]>([...(props.korso.internalComments ?? [])]);
const newComment = ref('');
const editingId = ref<number | null>(null);
const editingText = ref('');

async function addComment() {
    const text = newComment.value.trim();
    if (!text) return;

    const { data } = await axios.post('/korso/comments/store', { korso_id: props.korso.id, comment: text });
    internalComments.value.unshift({
        id: data.id,
        user_id: currentUserId.value ?? 0,
        comment: data.comment,
        is_deleted: false,
        created_at: new Date().toISOString(),
        user: { id: currentUserId.value ?? 0, name: data.user },
    });
    newComment.value = '';
}

function startEditComment(comment: InternalComment) {
    editingId.value = comment.id;
    editingText.value = comment.comment;
}

async function saveEditComment(comment: InternalComment) {
    const text = editingText.value.trim();
    if (!text) return;

    await axios.patch(`/korso/comments/${comment.id}`, { comment: text });
    comment.comment = text;
    editingId.value = null;
}

async function softDeleteComment(comment: InternalComment) {
    await axios.patch(`/korso/comments/${comment.id}/delete`);
    comment.is_deleted = true;
}

async function restoreComment(comment: InternalComment) {
    await axios.patch(`/korso/comments/${comment.id}/restore`);
    comment.is_deleted = false;
}

function commentTimeAgo(value: string): string {
    const diffMin = Math.round((Date.now() - new Date(value).getTime()) / 60000);
    if (diffMin < 1) return 'gerade eben';
    if (diffMin < 60) return `vor ${diffMin} Min.`;
    const diffHour = Math.round(diffMin / 60);
    if (diffHour < 24) return `vor ${diffHour} Std.`;
    return `vor ${Math.round(diffHour / 24)} Tagen`;
}
</script>

<template>
    <Head :title="korso.problem_type" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div v-if="canManage" class="flex items-center justify-between">
            <Link
                :href="dashboardHref"
                class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
            >
                <ArrowLeft class="h-4 w-4" />
                Dashboard
            </Link>
            <a
                :href="`/korso/${korso.id}/download-pdf`"
                target="_blank"
                rel="noopener"
                class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
            >
                <FileDown class="h-4 w-4" />
                PDF
            </a>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <!-- Left column -->
            <div class="flex flex-col gap-4">
                <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm">
                    <div class="text-center">
                        <p class="text-sm font-semibold" style="color: #65a30d">
                            {{ isDone ? 'Erledigt von' : 'Zugewiesen an' }}
                        </p>
                        <h3 class="mt-1 text-lg font-bold">
                            {{ isDone ? (korso.doneByUser?.name ?? 'Unbekannt') : (korso.assignedUser?.name ?? 'Nicht zugewiesen') }}
                        </h3>
                    </div>

                    <button
                        v-if="isDone"
                        type="button"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 mt-3 inline-flex h-9 w-full items-center justify-center rounded-md px-3 text-sm"
                        @click="restoreTicket"
                    >
                        Wiederherstellen
                    </button>

                    <template v-else>
                        <button
                            v-if="canManage"
                            type="button"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 mt-3 inline-flex h-9 w-full items-center justify-center rounded-md px-3 text-sm"
                            @click="markDone"
                        >
                            Erledigt
                        </button>

                        <div class="mt-2 flex flex-col gap-1">
                            <label for="assignedTo" class="text-muted-foreground text-xs">Zuweisen</label>
                            <select
                                id="assignedTo"
                                v-model="assignedTo"
                                :disabled="!canManage"
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm disabled:opacity-60"
                                @change="onAssignChange"
                            >
                                <option value="">Zuweisen</option>
                                <option v-for="u in korso_ma_users" :key="u.id" :value="u.id">
                                    {{ (u.vorname ?? '').charAt(0).toUpperCase() }}. {{ u.name }}
                                </option>
                            </select>
                        </div>
                    </template>
                </div>

                <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm">
                    <p class="mb-3 text-sm font-semibold" style="color: #65a30d">Ticket Informationen</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Ersteller</label>
                            <input
                                type="text"
                                readonly
                                :value="creatorName(korso.subUser, korso.submitter_name)"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Am</label>
                            <input
                                type="text"
                                readonly
                                :value="formatDate(korso.created_at)"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Standort</label>
                            <input
                                type="text"
                                readonly
                                :value="korso.subUser?.ort || korso.submitter_standort || '—'"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Position</label>
                            <input
                                type="text"
                                readonly
                                :value="korso.subUser?.position ?? '—'"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Abteilung</label>
                            <input
                                type="text"
                                readonly
                                :value="korso.subUser?.abteilung ?? '—'"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Telefon</label>
                            <input
                                type="text"
                                readonly
                                :value="korso.tel_number"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Adresse</label>
                            <input
                                type="text"
                                readonly
                                :value="korso.submitter_adresse"
                                class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                            />
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Priorität</label>
                            <!-- No save handler on this field in the old page either (only Status
                                 saves via AJAX) - kept the same no-op, just not regressing it. -->
                            <select
                                :disabled="!canManage"
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm disabled:opacity-60"
                            >
                                <option :selected="korso.priority === 3">Hoch</option>
                                <option :selected="korso.priority !== 3">Normal</option>
                            </select>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-muted-foreground text-xs">Status</label>
                            <select
                                v-model="ticketStatusId"
                                :disabled="!canManage"
                                class="border-input bg-background h-9 rounded-md border px-3 text-sm disabled:opacity-60"
                                @change="onStatusChange"
                            >
                                <option v-for="status in ticket_statuses" :key="status.id" :value="status.id">
                                    {{ status.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm lg:col-span-2">
                <h2 class="text-center text-lg font-semibold">
                    {{ korso.problem_type.toUpperCase() }}
                    <span
                        v-if="korso.onlinemarketingItem || korso.zertifizierungItem"
                        class="bg-secondary text-secondary-foreground ml-2 rounded-full px-2 py-0.5 text-xs font-medium"
                    >
                        {{ korso.onlinemarketingItem?.name ?? korso.zertifizierungItem?.name }}
                    </span>
                </h2>

                <div v-if="korso.korsoItems?.length" class="mt-4">
                    <h5 class="mb-2 text-sm font-semibold" style="color: #65a30d">Bestellte Artikel</h5>
                    <ul class="divide-y text-sm">
                        <li v-for="item in korso.korsoItems" :key="item.id" class="py-1.5">
                            <div class="flex items-center justify-between">
                                <span>{{ formatItemName(item.item_name) }}</span>
                                <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs font-semibold">
                                    {{ item.quantity }}
                                </span>
                            </div>
                            <ul
                                v-if="item.item_name === 'Visitenkarten' && item.details"
                                class="text-muted-foreground mt-1 ml-3 flex flex-col gap-0.5 text-xs"
                            >
                                <li v-for="detail in visitenkarteDetails(item.details)" :key="detail.label">
                                    <span class="font-medium">{{ detail.label }}:</span> {{ detail.value }}
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div v-if="hasCleanNotes" class="mt-4">
                    <h5 class="mb-1 text-sm font-semibold" style="color: #65a30d">Beschreibung</h5>
                    <!-- eslint-disable-next-line vue/no-v-html -->
                    <div class="text-sm whitespace-pre-wrap" v-html="cleanNotes"></div>
                </div>

                <div v-if="korso.problem_in_city || korso.location?.place?.pnname" class="mt-4">
                    <h5 class="mb-1 text-sm font-semibold" style="color: #65a30d">Standort</h5>
                    <p class="text-sm">{{ korso.problem_in_city ?? korso.location?.place?.pnname }}</p>
                </div>

                <div v-if="korso.is_chatgpt_project" class="mt-4">
                    <h5 class="mb-2 text-sm font-semibold" style="color: #65a30d">ChatGPT-Projektvorschläge</h5>
                    <div class="overflow-x-auto rounded-md border">
                        <table class="w-full text-sm">
                            <tbody class="divide-y">
                                <tr>
                                    <th class="bg-muted/40 w-1/3 p-2 text-left font-medium">Projektname</th>
                                    <td class="p-2">{{ korso.chatgpt_project_name || '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Einführungsgrund</th>
                                    <td class="p-2 whitespace-pre-wrap">{{ korso.chatgpt_introduction_reason || '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Ziele</th>
                                    <td class="p-2 whitespace-pre-wrap">{{ korso.chatgpt_goal || '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Prozessschritte</th>
                                    <td class="p-2 whitespace-pre-wrap">{{ korso.chatgpt_process_steps || '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Bestehender Prozess</th>
                                    <td class="p-2">
                                        {{ korso.chatgpt_has_existing_process == null ? '—' : korso.chatgpt_has_existing_process ? 'Ja' : 'Nein' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Output-Beispiele vorhanden</th>
                                    <td class="p-2">
                                        {{ korso.chatgpt_has_output_examples == null ? '—' : korso.chatgpt_has_output_examples ? 'Ja' : 'Nein' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Knowledge Base vorhanden</th>
                                    <td class="p-2">
                                        {{ korso.chatgpt_has_knowledge_base == null ? '—' : korso.chatgpt_has_knowledge_base ? 'Ja' : 'Nein' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Perfekter Output</th>
                                    <td class="p-2 whitespace-pre-wrap">{{ korso.chatgpt_output_examples || '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Vorhandenes Wissen</th>
                                    <td class="p-2 whitespace-pre-wrap">{{ korso.chatgpt_knowledge_base || '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-muted/40 p-2 text-left font-medium">Sonstige Anforderungen</th>
                                    <td class="p-2 whitespace-pre-wrap">{{ korso.chatgpt_additional_requirements || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Per-field ChatGPT attachments, grouped the same way the old page did -->
                    <div
                        v-for="context in [
                            'Engpässe und Breaking Points',
                            'Kurzbeschreibung der einzelnen Schritte',
                            'Beispiele für den perfekten Output',
                            'Vorhandenes Wissen / Knowledge Bases',
                            'Sonstige Anforderungen',
                        ]"
                        :key="context"
                    >
                        <div v-if="attachmentsFor(context).length" class="mt-2">
                            <p class="text-muted-foreground mb-1 text-xs font-semibold">Anhänge zu "{{ context }}"</p>
                            <div class="flex flex-wrap gap-2">
                                <a
                                    v-for="att in attachmentsFor(context)"
                                    :key="att.id"
                                    :href="attachmentUrl(att.file_path)"
                                    target="_blank"
                                    rel="noopener"
                                    class="border-border hover:bg-accent inline-flex items-center gap-1.5 rounded-md border px-2 py-1 text-xs"
                                >
                                    <img v-if="att.file_type.includes('image')" :src="attachmentUrl(att.file_path)" class="h-8 w-8 rounded object-cover" />
                                    <component :is="attachmentIcon(att.file_type)" v-else class="h-4 w-4" />
                                    {{ att.file_path.split('/').pop() }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="korso.massnahme" class="mt-4">
                    <h5 class="mb-1 text-sm font-semibold" style="color: #65a30d">Maßnahme</h5>
                    <p class="text-sm">{{ korso.massnahme.name }}</p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <!-- General attachments -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <h5 class="text-sm font-semibold">Anhänge</h5>
                            <div v-if="isKorsoMa" class="flex items-center gap-2">
                                <input ref="uploadInput" type="file" multiple class="hidden" @change="onFilesSelected" />
                                <button
                                    type="button"
                                    class="border-border hover:bg-accent inline-flex h-8 items-center gap-1.5 rounded-md border px-2 text-xs"
                                    @click="uploadInput?.click()"
                                >
                                    <Upload class="h-3.5 w-3.5" />
                                    Hochladen
                                </button>
                            </div>
                        </div>
                        <div v-if="generalAttachments.length" class="flex flex-wrap gap-2">
                            <div
                                v-for="att in generalAttachments"
                                :key="att.id"
                                class="group relative flex h-20 w-20 items-center justify-center rounded-md border"
                            >
                                <a :href="attachmentUrl(att.file_path)" target="_blank" rel="noopener" class="flex h-full w-full items-center justify-center">
                                    <img
                                        v-if="att.file_type.includes('image')"
                                        :src="attachmentUrl(att.file_path)"
                                        class="h-full w-full rounded-md object-cover"
                                    />
                                    <component :is="attachmentIcon(att.file_type)" v-else class="text-muted-foreground h-8 w-8" />
                                </a>
                                <button
                                    v-if="isKorsoMa"
                                    type="button"
                                    class="bg-destructive text-destructive-foreground absolute -top-1.5 -right-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs opacity-0 transition-opacity group-hover:opacity-100"
                                    title="Löschen"
                                    @click="deleteAttachment(att)"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground text-xs">Keine Anhänge vorhanden.</p>
                    </div>

                    <!-- Internal comments -->
                    <div v-if="canManage" class="relative">
                        <h5 class="mb-2 text-sm font-semibold" style="color: #65a30d">Korso Interne Kommentare</h5>

                        <div v-if="isDone" class="bg-background/40 absolute inset-0 z-10 cursor-not-allowed rounded-md"></div>

                        <input
                            v-model="newComment"
                            type="text"
                            placeholder="Neuen Kommentar schreiben..."
                            class="border-input bg-background mb-2 h-9 w-full rounded-md border px-3 text-sm"
                            @keydown.enter.prevent="addComment"
                        />

                        <div class="flex max-h-72 flex-col gap-1 overflow-y-auto">
                            <div
                                v-for="comment in internalComments"
                                :key="comment.id"
                                class="flex items-start justify-between gap-2 border-b py-1.5 text-sm"
                                :class="comment.is_deleted ? 'text-muted-foreground line-through italic' : ''"
                            >
                                <template v-if="editingId === comment.id">
                                    <div class="flex w-full flex-col gap-1">
                                        <textarea
                                            v-model="editingText"
                                            rows="2"
                                            class="border-input bg-background rounded-md border px-2 py-1 text-sm"
                                        ></textarea>
                                        <div class="flex justify-end gap-2 text-xs">
                                            <button type="button" class="text-muted-foreground" @click="editingId = null">Abbrechen</button>
                                            <button type="button" class="text-primary font-medium" @click="saveEditComment(comment)">Speichern</button>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="min-w-0 flex-1">
                                        <strong>{{ comment.user?.name }}</strong>:
                                        <span>{{ comment.comment }}</span>
                                        <span class="text-muted-foreground text-xs"> ({{ commentTimeAgo(comment.created_at) }})</span>
                                    </div>
                                    <div v-if="comment.user_id === currentUserId" class="flex shrink-0 items-center gap-1.5">
                                        <button v-if="comment.is_deleted" type="button" title="Wiederherstellen" @click="restoreComment(comment)">
                                            <RotateCcw class="text-primary h-3.5 w-3.5" />
                                        </button>
                                        <template v-else>
                                            <button type="button" title="Bearbeiten" @click="startEditComment(comment)">
                                                <Pencil class="text-muted-foreground h-3.5 w-3.5" />
                                            </button>
                                            <button type="button" title="Löschen" @click="softDeleteComment(comment)">
                                                <Trash2 class="text-destructive h-3.5 w-3.5" />
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <p v-if="!internalComments.length" class="text-muted-foreground text-xs">Keine internen Kommentare vorhanden.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t pt-4">
                    <h5 class="mb-2 text-sm font-semibold" style="color: #661421">Kommentare</h5>
                    <CommentThread
                        model-type="App\Korso"
                        :model-id="korso.id"
                        :comments="korso.comments"
                        :is-done="isDone"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
