<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';

/**
 * Pagination footer, paired with useDataTable.ts - same "X–Y von Z" +
 * prev/next shape Korso/Dashboard.vue's server-side pagination already
 * used, generalized here so every table gets it instead of hand-rolling it
 * again per page.
 */
withDefaults(
    defineProps<{
        page: number;
        pageCount: number;
        rangeFrom: number;
        rangeTo: number;
        total: number;
        itemLabel?: string;
    }>(),
    {
        itemLabel: 'Einträgen',
    },
);

const emit = defineEmits<{ 'update:page': [number] }>();
</script>

<template>
    <div v-if="pageCount > 1" class="flex flex-wrap items-center justify-between gap-2 border-t p-3 text-sm">
        <span class="text-muted-foreground">{{ rangeFrom }}–{{ rangeTo }} von {{ total }} {{ itemLabel }}</span>
        <div class="flex items-center gap-1">
            <button
                type="button"
                :disabled="page <= 1"
                class="border-border hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border disabled:cursor-not-allowed disabled:opacity-40"
                @click="emit('update:page', page - 1)"
            >
                <ChevronLeft class="h-4 w-4" />
            </button>
            <span class="text-muted-foreground px-2">Seite {{ page }} von {{ pageCount }}</span>
            <button
                type="button"
                :disabled="page >= pageCount"
                class="border-border hover:bg-accent inline-flex h-8 w-8 items-center justify-center rounded-md border disabled:cursor-not-allowed disabled:opacity-40"
                @click="emit('update:page', page + 1)"
            >
                <ChevronRight class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
