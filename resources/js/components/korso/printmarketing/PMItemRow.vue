<script setup lang="ts">
import { grayOutTooltip, isGrayedOut, type PMGrayItem, type PMItem } from '@/lib/printmarketingItems';

/**
 * One "checkbox + quantity" row shared by every Printmarketing item list -
 * ported from the jQuery `.item-checkbox` change handler (show/hide the qty
 * input, prefill it with the item's default quantity) duplicated across all
 * 5 old tab partials. `selected` is the parent's plain `{ [key]: quantity }`
 * map (mutated directly, same convention as components/handwerk/
 * ItemCheckboxGroup.vue's `form` prop) - a key's presence *is* "checked".
 */

const props = defineProps<{
    item: PMItem | PMGrayItem;
    selected: Record<string, number>;
}>();

const grayed = 'grayOut' in props.item && isGrayedOut(props.item);
const tooltip = 'grayOut' in props.item ? grayOutTooltip(props.item) : '';

function onToggle(event: Event) {
    const checked = (event.target as HTMLInputElement).checked;
    if (checked) {
        props.selected[props.item.key] = props.item.qty;
    } else {
        delete props.selected[props.item.key];
    }
}
</script>

<template>
    <div class="flex items-center gap-2 py-1" :title="tooltip">
        <input
            :id="item.key"
            type="checkbox"
            :disabled="grayed"
            :checked="item.key in selected"
            class="text-primary h-4 w-4 shrink-0 disabled:opacity-50"
            @change="onToggle"
        />
        <label :for="item.key" class="flex-1 text-sm" :class="grayed ? 'text-muted-foreground' : ''">
            {{ item.label }}
        </label>
        <input
            v-if="item.key in selected"
            v-model.number="selected[item.key]"
            type="number"
            min="1"
            class="border-input bg-background h-7 w-16 shrink-0 rounded-md border px-2 text-xs"
        />
    </div>
</template>
