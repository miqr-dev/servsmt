<script setup lang="ts">
// Icon for a comment attachment by file type (2026-10-13).
import { File, FileArchive, FileImage, FileSpreadsheet, FileText, Mail, Presentation } from '@lucide/vue';
import { computed } from 'vue';
import { fileExtension, type CommentFileItem } from '@/lib/commentFiles';

const props = defineProps<{ file: Pick<CommentFileItem, 'original_name' | 'is_image'> }>();

const icon = computed(() => {
    if (props.file.is_image) return FileImage;
    const ext = fileExtension(props.file.original_name);
    if (['xls', 'xlsx', 'csv'].includes(ext)) return FileSpreadsheet;
    if (['ppt', 'pptx'].includes(ext)) return Presentation;
    if (['zip'].includes(ext)) return FileArchive;
    if (['msg', 'eml'].includes(ext)) return Mail;
    if (['pdf', 'doc', 'docx', 'txt'].includes(ext)) return FileText;

    return File;
});
</script>

<template>
    <component :is="icon" aria-hidden="true" />
</template>
