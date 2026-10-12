<script setup lang="ts">
// Attachments under a saved comment (2026-10-13): pictures as small previews,
// other files as chips. Pictures and PDFs open in a new tab, the rest downloads.
import { computed } from 'vue';
import CommentFileIcon from '@/components/CommentFileIcon.vue';
import { formatFileSize, type CommentFileItem } from '@/lib/commentFiles';

const props = defineProps<{ files: CommentFileItem[] }>();

const attachments = computed(() => props.files.filter((f) => !f.inline));
const pictures = computed(() => attachments.value.filter((f) => f.is_image));
const others = computed(() => attachments.value.filter((f) => !f.is_image));
</script>

<template>
    <div v-if="attachments.length" class="mt-2 flex flex-col gap-2">
        <div v-if="pictures.length" class="flex flex-wrap gap-2">
            <a
                v-for="f in pictures"
                :key="f.id"
                :href="f.url"
                target="_blank"
                rel="noopener"
                class="bg-muted block h-24 w-32 overflow-hidden rounded-md border hover:opacity-90"
                :title="`${f.original_name} (${formatFileSize(f.size)})`"
            >
                <img :src="f.url" :alt="f.original_name" class="h-full w-full object-cover" loading="lazy" />
            </a>
        </div>
        <div v-if="others.length" class="flex flex-wrap gap-1.5">
            <a
                v-for="f in others"
                :key="f.id"
                :href="f.url"
                target="_blank"
                rel="noopener"
                class="bg-muted hover:bg-accent inline-flex max-w-full items-center gap-1.5 rounded-md px-2 py-1 text-xs"
                :title="f.original_name"
            >
                <CommentFileIcon :file="f" class="text-muted-foreground h-3.5 w-3.5 shrink-0" />
                <span class="max-w-56 truncate">{{ f.original_name }}</span>
                <span class="text-muted-foreground">{{ formatFileSize(f.size) }}</span>
            </a>
        </div>
    </div>
</template>
