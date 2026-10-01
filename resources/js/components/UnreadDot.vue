<script setup lang="ts">
/** Small "Neu" dot for list rows with unread notifications (see composables/useUnread.ts). */
defineProps<{ show: boolean }>();
</script>

<template>
    <span v-if="show" class="bg-primary mr-1.5 inline-block size-2 shrink-0 rounded-full align-middle" title="Neue Aktivität" aria-label="Neu" />
</template>
