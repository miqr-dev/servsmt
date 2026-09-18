<script setup lang="ts">
import { ArrowDown, ArrowUp, ArrowUpDown } from '@lucide/vue';

/**
 * One sortable <th>, paired with useDataTable.ts. Non-sortable columns
 * (an icon-only status column, a trailing actions column with no
 * meaningful sort) just omit `sortKey` and render as a plain <th> - same
 * pattern Korso/Dashboard.vue's own toggleDateSort column used before this
 * was generalized into a shared piece.
 */
defineProps<{
    label: string;
    sortKey?: string;
    activeKey?: string | null;
    direction?: 'asc' | 'desc';
    align?: 'left' | 'right' | 'center';
}>();

const emit = defineEmits<{ sort: [] }>();
</script>

<template>
    <th class="p-3 font-medium" :class="align === 'right' ? 'text-right' : align === 'center' ? 'text-center' : 'text-left'">
        <button v-if="sortKey" type="button" class="hover:text-foreground inline-flex items-center gap-1" @click="emit('sort')">
            {{ label }}
            <ArrowUpDown v-if="activeKey !== sortKey" class="h-3.5 w-3.5 opacity-50" />
            <ArrowDown v-else-if="direction === 'desc'" class="h-3.5 w-3.5" />
            <ArrowUp v-else class="h-3.5 w-3.5" />
        </button>
        <span v-else>{{ label }}</span>
    </th>
</template>
