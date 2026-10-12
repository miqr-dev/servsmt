<script setup lang="ts">
// Recursive single comment + its replies. Split out from CommentThread.vue
// so the recursion is an explicit self-import (works reliably with Vite).
// 2026-10-13: rich text shown with safeHtml(), pictures open full size on
// click, attachments below; reply / edit with RichTextEditor.
import axios from 'axios';
import { computed, ref } from 'vue';
import { Loader2, Pencil, Reply } from '@lucide/vue';
import { safeHtml } from '@/lib/safeHtml';
import { hasContent, type CommentFileItem } from '@/lib/commentFiles';
import CommentAttachments from './CommentAttachments.vue';
import CommentThreadNode from './CommentThreadNode.vue';
import RichTextEditor from './RichTextEditor.vue';
import type { CommentItem, CommentNode } from './CommentThread.vue';

type Submit = (comment: CommentItem, message: string, fileIds: number[]) => Promise<boolean>;

const props = defineProps<{
    node: CommentNode;
    isDone: boolean;
    currentUserId: number | string | null;
    uploadType: string;
    uploadId: number;
    submitReply: Submit;
    submitEdit: Submit;
}>();

function commenterName(c: CommentItem): string {
    if (c.commenter) {
        return c.commenter.username || `${c.commenter.vorname ?? ''} ${c.commenter.name ?? ''}`.trim() || 'Unbekannt';
    }
    return c.guest_name || 'Gast';
}

function avatarUrl(c: CommentItem): string {
    if (!c.commenter) return '/images/admin_images/mitarbeiter/nopic.jpg';
    return `/images/admin_images/mitarbeiter/${c.commenter.name}, ${c.commenter.vorname}.jpg`;
}

function onAvatarError(event: Event) {
    const img = event.target as HTMLImageElement;
    // only once - a missing nopic.jpg would otherwise retry forever
    if (!img.src.endsWith('/nopic.jpg')) img.src = '/images/admin_images/mitarbeiter/nopic.jpg';
}

function isOwn(c: CommentItem): boolean {
    return props.currentUserId != null && String(c.commenter_id) === String(props.currentUserId);
}

function timeAgo(value: string): string {
    const diffMs = Date.now() - new Date(value).getTime();
    const minutes = Math.round(diffMs / 60000);
    if (minutes < 1) return 'gerade eben';
    if (minutes < 60) return `vor ${minutes} Min.`;
    const hours = Math.round(minutes / 60);
    if (hours < 24) return `vor ${hours} Std.`;
    const days = Math.round(hours / 24);
    return `vor ${days} Tg.`;
}

/**
 * Editor comments are HTML with paragraphs. Older ones are text (sometimes
 * with a bit of HTML) whose line breaks were shown as typed - keep them.
 */
function commentHtml(raw: string): string {
    const legacy = !/<(p|div|br|ul|ol|li|blockquote|pre|h[1-6]|table)\b/i.test(raw);

    return safeHtml(legacy && /<\/?[a-z][^>]*>/i.test(raw) ? raw.replace(/\r\n|\r|\n/g, '<br>') : raw);
}

const html = computed(() => commentHtml(props.node.comment));

/** a picture in the text opens full size */
function openPicture(event: MouseEvent) {
    const target = event.target as HTMLElement;
    if (target.tagName === 'IMG') window.open((target as HTMLImageElement).src, '_blank', 'noopener');
}

/** uploads that never made it into a saved comment */
function discardUploads(files: CommentFileItem[]) {
    for (const f of files) if (f.comment_id == null) axios.delete(`/comment-files/${f.id}`).catch(() => {});
}

// --- reply ---
const replying = ref(false);
const replyHtml = ref('');
const replyFiles = ref<CommentFileItem[]>([]);
const replyBusy = ref(false);
const replySending = ref(false);
const canReply = computed(() => !replySending.value && !replyBusy.value && hasContent(replyHtml.value, replyFiles.value));

function startReply() {
    replying.value = true;
    replyHtml.value = '';
    replyFiles.value = [];
}

function cancelReply() {
    discardUploads(replyFiles.value);
    replying.value = false;
    replyHtml.value = '';
    replyFiles.value = [];
}

async function sendReply() {
    if (!canReply.value) return;
    replySending.value = true;
    const ok = await props.submitReply(props.node, replyHtml.value, replyFiles.value.map((f) => f.id));
    replySending.value = false;
    if (ok) {
        replying.value = false;
        replyHtml.value = '';
        replyFiles.value = [];
    }
}

// --- edit (own comment) ---
const editing = ref(false);
const editHtml = ref('');
const editFiles = ref<CommentFileItem[]>([]);
const editBusy = ref(false);
const editSending = ref(false);
const canSaveEdit = computed(() => !editSending.value && !editBusy.value && hasContent(editHtml.value, editFiles.value));

function startEdit() {
    editing.value = true;
    // old plain-text comments: escaped, line breaks kept
    editHtml.value = commentHtml(props.node.comment);
    editFiles.value = (props.node.files ?? []).filter((f) => !f.inline);
}

function cancelEdit() {
    discardUploads(editFiles.value);
    editing.value = false;
}

async function saveEdit() {
    if (!canSaveEdit.value) return;
    editSending.value = true;
    const ok = await props.submitEdit(props.node, editHtml.value, editFiles.value.map((f) => f.id));
    editSending.value = false;
    if (ok) editing.value = false;
}
</script>

<template>
    <li class="flex gap-3 py-3" :class="node.deleted_at ? 'opacity-60' : ''">
        <img
            :src="avatarUrl(node)"
            :alt="`${commenterName(node)} Avatar`"
            class="mt-0.5 h-9 w-9 shrink-0 rounded-full object-cover"
            @error="onAvatarError"
        />
        <div class="min-w-0 flex-1">
            <h5 class="text-sm font-semibold" style="color: #661421">
                {{ commenterName(node) }}
                <span class="text-muted-foreground ml-1 text-xs font-normal">- {{ timeAgo(node.created_at) }}</span>
                <span v-if="node.deleted_at" class="text-muted-foreground ml-1 text-xs font-normal italic">(gelöscht)</span>
            </h5>

            <template v-if="editing">
                <div class="mt-1 flex flex-col gap-1.5">
                    <RichTextEditor
                        v-model="editHtml"
                        v-model:files="editFiles"
                        :upload-type="uploadType"
                        :upload-id="uploadId"
                        autofocus
                        @busy="editBusy = $event"
                        @submit="saveEdit"
                    />
                    <div class="flex items-center justify-end gap-3 text-xs">
                        <button type="button" class="text-muted-foreground" @click="cancelEdit">Verwerfen</button>
                        <button type="button" class="text-primary inline-flex items-center gap-1 font-medium disabled:opacity-50" :disabled="!canSaveEdit" @click="saveEdit">
                            <Loader2 v-if="editSending || editBusy" class="h-3 w-3 animate-spin" />
                            Speichern
                        </button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="rich-text mt-0.5 text-sm" @click="openPicture" v-html="html"></div>
                <CommentAttachments :files="node.files ?? []" />

                <div v-if="!isDone && !node.deleted_at" class="mt-1 flex gap-3 text-xs">
                    <button
                        v-if="!isOwn(node)"
                        type="button"
                        class="text-muted-foreground hover:text-primary inline-flex items-center gap-1"
                        @click="startReply"
                    >
                        <Reply class="h-3 w-3" /> Antworten
                    </button>
                    <button
                        v-if="isOwn(node)"
                        type="button"
                        class="text-muted-foreground hover:text-primary inline-flex items-center gap-1"
                        @click="startEdit"
                    >
                        <Pencil class="h-3 w-3" /> Bearbeiten
                    </button>
                </div>
            </template>

            <div v-if="replying" class="mt-2 flex flex-col gap-1.5">
                <RichTextEditor
                    v-model="replyHtml"
                    v-model:files="replyFiles"
                    :upload-type="uploadType"
                    :upload-id="uploadId"
                    placeholder="Ihre Antwort …"
                    min-height="3.5rem"
                    autofocus
                    @busy="replyBusy = $event"
                    @submit="sendReply"
                />
                <div class="flex items-center justify-end gap-3 text-xs">
                    <button type="button" class="text-muted-foreground" @click="cancelReply">Verwerfen</button>
                    <button type="button" class="text-primary inline-flex items-center gap-1 font-medium disabled:opacity-50" :disabled="!canReply" @click="sendReply">
                        <Loader2 v-if="replySending || replyBusy" class="h-3 w-3 animate-spin" />
                        Antworten
                    </button>
                </div>
            </div>

            <ul v-if="node.replies.length" class="mt-2 flex flex-col divide-y border-l pl-4">
                <CommentThreadNode
                    v-for="child in node.replies"
                    :key="child.id"
                    :node="child"
                    :is-done="isDone"
                    :current-user-id="currentUserId"
                    :upload-type="uploadType"
                    :upload-id="uploadId"
                    :submit-reply="submitReply"
                    :submit-edit="submitEdit"
                />
            </ul>
        </div>
    </li>
</template>
