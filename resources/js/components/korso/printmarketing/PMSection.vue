<script setup lang="ts">
import PMItemRow from './PMItemRow.vue';
import type { PMGrayItem, PMItem } from '@/lib/printmarketingItems';

defineProps<{
    heading?: string;
    items: (PMItem | PMGrayItem)[];
    selected: Record<string, number>;
}>();
</script>

<template>
    <div class="mb-4">
        <h5 v-if="heading" class="mb-2 text-sm font-semibold">{{ heading }}</h5>
        <PMItemRow v-for="item in items" :key="item.key" :item="item" :selected="selected" />
    </div>
</template>
