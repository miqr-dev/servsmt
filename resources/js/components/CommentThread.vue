<script setup lang="ts">
// First-party replacement for the old @comments(['model' => $x]) Blade
// directive (resources/views/vendor/comments/components/comments.blade.php),
// for use on Vue/Inertia pages (Handwerk/Show.vue, Korso/Show.vue). Talks to
// the same backend as the still-Blade Tickets admin page's comment thread:
// app/Http/Controllers/CommentController.php, config/comments.php,
// app/Concerns/Commentable.php. See modernization-audit.md Section 3 and
// frontend-modernization-plan.md's 2026-09-17 status update.
//
// Soft-deleted comments are included on purpose (Commentable::comments()
// uses withTrashed()) so a comment thread doesn't lose history once its
// ticket is marked done - they're rendered dimmed, read-only, below.
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { usePage } from '@inertiajs/vue3';
import CommentThreadNode from './CommentThreadNode.vue';
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

const newCommentText = ref('');
const submittingNew = ref(false);

async function submitNewComment() {
    if (!newCommentText.value.trim()) return;

    submittingNew.value = true;
    try {
        const { data } = await axios.post<CommentItem>('/comments', {
            commentable_type: props.modelType,
            commentable_id: props.modelId,
            message: newCommentText.value,
        });
        localComments.value.push(data);
        newCommentText.value = '';
    } catch (error) {
        toast.error(errorMessage(error, 'Kommentar konnte nicht gespeichert werden.'));
    } finally {
        submittingNew.value = false;
    }
}

// --- reply / edit, bubbled up from CommentThreadNode ---

async function handleReply(parent: CommentItem, message: string) {
    try {
        const { data } = await axios.post<CommentItem>(`/comments/${parent.id}`, { message });
        localComments.value.push(data);
    } catch (error) {
        toast.error(errorMessage(error, 'Antwort konnte nicht gespeichert werden.'));
    }
}

async function handleEdit(comment: CommentItem, message: string) {
    try {
        const { data } = await axios.put<CommentItem>(`/comments/${comment.id}`, { message });
        const index = localComments.value.findIndex((c) => c.id === comment.id);
        if (index !== -1) localComments.value[index] = data;
    } catch (error) {
        toast.error(errorMessage(error, 'Kommentar konnte nicht aktualisiert werden.'));
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
                @reply="handleReply"
                @edit="handleEdit"
            />
        </ul>
        <p v-else class="text-muted-foreground text-sm">Noch keine Kommentare.</p>

        <div v-if="!isDone" class="mt-4 border-t pt-4">
            <label class="text-sm font-medium">Neuen Kommentar schreiben</label>
            <textarea
                v-model="newCommentText"
                rows="3"
                class="border-input bg-background mt-1 w-full rounded-md border px-3 py-2 text-sm"
                style="resize: none"
            ></textarea>
            <button
                type="button"
                :disabled="submittingNew || !newCommentText.trim()"
                class="bg-primary text-primary-foreground hover:bg-primary/90 mt-2 inline-flex h-9 items-center justify-center rounded-md px-3 text-sm disabled:opacity-50"
                @click="submitNewComment"
            >
                Einreichen
            </button>
        </div>
        <div v-else class="mt-4 border-t pt-4">
            <p class="text-sm font-semibold" style="color: #661421">Das Ticket ist erledigt.</p>
            <p class="text-muted-foreground text-sm">
                Zum Hinzufügen von weiteren Kommentaren klicken Sie bitte "Wiederherstellen".
            </p>
        </div>
    </div>
</template>
