<script setup lang="ts">
// Recursive single comment + its replies. Split out from CommentThread.vue
// so the recursion is an explicit self-import (works reliably with Vite),
// mirroring resources/views/vendor/comments/_comment.blade.php's own
// recursive @include('comments::_comment', ...) for child replies.
import { ref } from 'vue';
import { Pencil, Reply } from '@lucide/vue';
import CommentThreadNode from './CommentThreadNode.vue';
import type { CommentItem, CommentNode } from './CommentThread.vue';

const props = defineProps<{
    node: CommentNode;
    isDone: boolean;
    currentUserId: number | string | null;
}>();

const emit = defineEmits<{
    reply: [parent: CommentItem, message: string];
    edit: [comment: CommentItem, message: string];
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
    (event.target as HTMLImageElement).src = '/images/admin_images/mitarbeiter/nopic.jpg';
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

const replying = ref(false);
const replyText = ref('');

function startReply() {
    replying.value = true;
    replyText.value = '';
}

function cancelReply() {
    replying.value = false;
    replyText.value = '';
}

function submitReply() {
    if (!replyText.value.trim()) return;
    emit('reply', props.node, replyText.value);
    cancelReply();
}

const editing = ref(false);
const editText = ref('');

function startEdit() {
    editing.value = true;
    editText.value = props.node.comment;
}

function cancelEdit() {
    editing.value = false;
    editText.value = '';
}

function submitEdit() {
    if (!editText.value.trim()) return;
    emit('edit', props.node, editText.value);
    editing.value = false;
}

// Bubble a reply/edit from a nested reply up to the top-level thread owner,
// which is the only one holding the axios/localComments logic.
function onChildReply(parent: CommentItem, message: string) {
    emit('reply', parent, message);
}
function onChildEdit(comment: CommentItem, message: string) {
    emit('edit', comment, message);
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
                    <textarea
                        v-model="editText"
                        rows="3"
                        class="border-input bg-background rounded-md border px-2 py-1.5 text-sm"
                    ></textarea>
                    <div class="flex justify-end gap-2 text-xs">
                        <button type="button" class="text-muted-foreground" @click="cancelEdit">Verwerfen</button>
                        <button type="button" class="text-primary font-medium" @click="submitEdit">Speichern</button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="mt-0.5 text-sm whitespace-pre-wrap">{{ node.comment }}</div>

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
                <textarea
                    v-model="replyText"
                    rows="2"
                    placeholder="Ihre Antwort..."
                    class="border-input bg-background rounded-md border px-2 py-1.5 text-sm"
                ></textarea>
                <div class="flex justify-end gap-2 text-xs">
                    <button type="button" class="text-muted-foreground" @click="cancelReply">Verwerfen</button>
                    <button type="button" class="text-primary font-medium" @click="submitReply">Antworten</button>
                </div>
            </div>

            <ul v-if="node.replies.length" class="mt-2 flex flex-col divide-y border-l pl-4">
                <CommentThreadNode
                    v-for="child in node.replies"
                    :key="child.id"
                    :node="child"
                    :is-done="isDone"
                    :current-user-id="currentUserId"
                    @reply="onChildReply"
                    @edit="onChildEdit"
                />
            </ul>
        </div>
    </li>
</template>
