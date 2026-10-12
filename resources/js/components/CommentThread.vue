<script setup lang="ts">
// First-party replacement for the old @comments(['model' => $x]) Blade
// directive, for the Vue ticket pages (Tickets/Show.vue, Handwerk/Show.vue,
// Korso/Show.vue). Backend: app/Http/Controllers/CommentController.php,
// app/Concerns/Commentable.php.
//
// 2026-10-13: rich text (RichTextEditor, Tiptap) with pasted / dropped
// pictures and attachments (App\CommentFile, CommentFileController). The text
// is HTML now, cleaned on the server (App\Support\CommentHtml) and shown with
// safeHtml(); old plain-text comments keep their line breaks.
//
// Soft-deleted comments are included on purpose (Commentable::comments()
// uses withTrashed()) so a comment thread doesn't lose history once its
// ticket is marked done - they're rendered dimmed, read-only, below.
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { usePage } from '@inertiajs/vue3';
import { Loader2, Send } from '@lucide/vue';
import CommentThreadNode from './CommentThreadNode.vue';
import RichTextEditor from './RichTextEditor.vue';
import { hasContent, type CommentFileItem } from '@/lib/commentFiles';
import type { Auth } from '@/types';

export interface CommentItem {
    id: number;
    comment: string;
    child_id: number | null;
    commenter_id: string | number | null;
    guest_name: string | null;
    commenter: { id: number; name: string | null; vorname: string | null; username: string | null } | null;
    created_at: string;
    deleted_at: string | null;
    /** pictures in the text + attachments (2026-10-13) */
    files?: CommentFileItem[];
}

export interface CommentNode extends CommentItem {
    replies: CommentNode[];
}

const props = defineProps<{
    /** Fully-qualified model class, e.g. "App\\Korso" - matches get_class($model) server-side. */
    modelType: string;
    modelId: number;
    comments: CommentItem[];
    isDone: boolean;
}>();

const page = usePage<{ auth: Auth }>();
const currentUserId = computed<number | string | null>(() => page.props.auth.user?.id ?? null);

const localComments = ref<CommentItem[]>([...props.comments]);
watch(
    () => props.comments,
    (value) => {
        localComments.value = [...value];
    },
);

const thread = computed<CommentNode[]>(() => {
    const byParent = new Map<number | null, CommentItem[]>();
    for (const comment of localComments.value) {
        const key = comment.child_id ?? null;
        if (!byParent.has(key)) byParent.set(key, []);
        byParent.get(key)!.push(comment);
    }

    const byCreatedAt = (a: CommentItem, b: CommentItem) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime();

    const build = (parentId: number | null): CommentNode[] =>
        (byParent.get(parentId) ?? [])
            .slice()
            .sort(byCreatedAt)
            .map((comment) => ({ ...comment, replies: build(comment.id) }));

    return build(null);
});

function errorMessage(error: unknown, fallback: string): string {
    const response = (error as { response?: { data?: { errors?: Record<string, string[]>; message?: string } } })?.response;
    const firstValidationError = response?.data?.errors ? Object.values(response.data.errors)[0]?.[0] : undefined;
    return firstValidationError ?? response?.data?.message ?? fallback;
}

// --- new top-level comment ---

const newHtml = ref('');
const newFiles = ref<CommentFileItem[]>([]);
const newBusy = ref(false);
const submittingNew = ref(false);
const canSendNew = computed(() => !submittingNew.value && !newBusy.value && hasContent(newHtml.value, newFiles.value));

async function submitNewComment() {
    // also while the first one is still being sent (double click / Ctrl+Enter twice)
    if (!canSendNew.value) return;

    submittingNew.value = true;
    try {
        const { data } = await axios.post<CommentItem>('/comments', {
            commentable_type: props.modelType,
            commentable_id: props.modelId,
            message: newHtml.value,
            attachment_ids: newFiles.value.map((f) => f.id),
        });
        localComments.value.push(data);
        newHtml.value = '';
        newFiles.value = [];
    } catch (error) {
        toast.error(errorMessage(error, 'Kommentar konnte nicht gespeichert werden.'));
    } finally {
        submittingNew.value = false;
    }
}

// --- reply / edit, called by CommentThreadNode (true = saved, the node closes its editor) ---

async function submitReply(parent: CommentItem, message: string, fileIds: number[]): Promise<boolean> {
    try {
        const { data } = await axios.post<CommentItem>(`/comments/${parent.id}`, { message, attachment_ids: fileIds });
        localComments.value.push(data);

        return true;
    } catch (error) {
        toast.error(errorMessage(error, 'Antwort konnte nicht gespeichert werden.'));

        return false;
    }
}

async function submitEdit(comment: CommentItem, message: string, fileIds: number[]): Promise<boolean> {
    try {
        const { data } = await axios.put<CommentItem>(`/comments/${comment.id}`, { message, attachment_ids: fileIds });
        const index = localComments.value.findIndex((c) => c.id === comment.id);
        if (index !== -1) localComments.value[index] = data;

        return true;
    } catch (error) {
        toast.error(errorMessage(error, 'Kommentar konnte nicht aktualisiert werden.'));

        return false;
    }
}
</script>

<template>
    <div>
        <ul v-if="thread.length" class="flex flex-col divide-y">
            <CommentThreadNode
                v-for="node in thread"
                :key="node.id"
                :node="node"
                :is-done="isDone"
                :current-user-id="currentUserId"
                :upload-type="modelType"
                :upload-id="modelId"
                :submit-reply="submitReply"
                :submit-edit="submitEdit"
            />
        </ul>
        <p v-else class="text-muted-foreground text-sm">Noch keine Kommentare.</p>

        <div v-if="!isDone" class="mt-4 border-t pt-4">
            <label class="text-sm font-medium">Neuen Kommentar schreiben</label>
            <div class="mt-1">
                <RichTextEditor
                    v-model="newHtml"
                    v-model:files="newFiles"
                    :upload-type="modelType"
                    :upload-id="modelId"
                    placeholder="Kommentar schreiben … Bilder einfach einfügen (Strg+V) oder hineinziehen."
                    @busy="newBusy = $event"
                    @submit="submitNewComment"
                />
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    :disabled="!canSendNew"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center justify-center gap-1.5 rounded-md px-3 text-sm disabled:opacity-50"
                    @click="submitNewComment"
                >
                    <Loader2 v-if="submittingNew || newBusy" class="h-4 w-4 animate-spin" />
                    <Send v-else class="h-4 w-4" />
                    Einreichen
                </button>
                <span class="text-muted-foreground text-xs">
                    {{ newBusy ? 'Datei wird hochgeladen …' : 'Strg+Enter sendet · Dateien bis 10 MB' }}
                </span>
            </div>
        </div>
        <div v-else class="mt-4 border-t pt-4">
            <p class="text-sm font-semibold" style="color: #661421">Das Ticket ist erledigt.</p>
            <p class="text-muted-foreground text-sm">
                Zum Hinzufügen von weiteren Kommentaren klicken Sie bitte "Wiederherstellen".
            </p>
        </div>
    </div>
</template>
