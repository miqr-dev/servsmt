<script setup lang="ts">
import { Check, X } from '@lucide/vue';
import { onClickOutside } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

// Searchable multi-select (2026-10-09, first use: "Andere Email" on Neuer
// Mitarbeiter). Picked values show as chips; typing filters the list; Enter
// takes the highlighted entry. With `allowCustom` a typed value that passes
// the check (e.g. an external e-mail address) can be added too.

export type MultiOption = { value: string; label: string; hint?: string };

const props = withDefaults(
    defineProps<{
        options: MultiOption[];
        placeholder?: string;
        emptyText?: string;
        /** accept a typed value that is not in the list (return true if valid) */
        allowCustom?: (value: string) => boolean;
    }>(),
    { placeholder: 'Suchen…', emptyText: 'Keine Treffer', allowCustom: undefined },
);

const model = defineModel<string[]>({ default: () => [] });

const root = ref<HTMLElement | null>(null);
const input = ref<HTMLInputElement | null>(null);
const query = ref('');
const open = ref(false);
const active = ref(0);

onClickOutside(root, () => (open.value = false));

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();
    const list = q
        ? props.options.filter(
              (o) => o.label.toLowerCase().includes(q) || o.value.toLowerCase().includes(q) || (o.hint ?? '').toLowerCase().includes(q),
          )
        : props.options;
    return list.slice(0, 100);
});
watch(query, () => (active.value = 0));

const custom = computed(() => {
    const q = query.value.trim();
    if (!q || !props.allowCustom || !props.allowCustom(q)) return null;
    if (model.value.some((v) => v.toLowerCase() === q.toLowerCase())) return null;
    if (props.options.some((o) => o.value.toLowerCase() === q.toLowerCase())) return null;
    return q;
});

const labelOf = (v: string) => props.options.find((o) => o.value === v)?.label ?? v;

function toggle(v: string) {
    model.value = model.value.includes(v) ? model.value.filter((x) => x !== v) : [...model.value, v];
    query.value = '';
    input.value?.focus();
}
function remove(v: string) {
    model.value = model.value.filter((x) => x !== v);
}
function onEnter() {
    if (custom.value) toggle(custom.value);
    else if (filtered.value[active.value]) toggle(filtered.value[active.value].value);
}
function onBackspace() {
    if (!query.value && model.value.length) remove(model.value[model.value.length - 1]);
}
function focusInput() {
    open.value = true;
    input.value?.focus();
}
</script>

<template>
    <div ref="root" class="relative">
        <div
            class="border-input bg-background flex min-h-9 cursor-text flex-wrap items-center gap-1 rounded-md border px-2 py-1 text-sm"
            @click="focusInput"
        >
            <span v-for="v in model" :key="v" class="bg-muted inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs" :title="v">
                {{ labelOf(v) }}
                <button type="button" class="text-muted-foreground hover:text-foreground" aria-label="Entfernen" @click.stop="remove(v)">
                    <X class="size-3" />
                </button>
            </span>
            <input
                ref="input"
                v-model="query"
                type="text"
                class="placeholder:text-muted-foreground min-w-[8rem] flex-1 bg-transparent py-0.5 outline-none"
                :placeholder="model.length ? '' : placeholder"
                @focus="open = true"
                @keydown.down.prevent="active = Math.min(active + 1, filtered.length - 1)"
                @keydown.up.prevent="active = Math.max(active - 1, 0)"
                @keydown.enter.prevent="onEnter"
                @keydown.backspace="onBackspace"
                @keydown.esc="open = false"
            />
        </div>
        <div
            v-if="open"
            class="bg-popover text-popover-foreground absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-md border p-1 shadow-md"
        >
            <button v-if="custom" type="button" class="hover:bg-accent flex w-full rounded-sm px-2 py-1.5 text-left text-sm" @click="toggle(custom)">
                „{{ custom }}“ hinzufügen
            </button>
            <button
                v-for="(o, i) in filtered"
                :key="o.value"
                type="button"
                class="flex w-full items-center justify-between gap-2 rounded-sm px-2 py-1.5 text-left text-sm"
                :class="i === active ? 'bg-accent' : 'hover:bg-accent'"
                @mouseenter="active = i"
                @click="toggle(o.value)"
            >
                <span class="min-w-0">
                    {{ o.label }}
                    <span v-if="o.hint" class="text-muted-foreground block truncate text-xs">{{ o.hint }}</span>
                </span>
                <Check v-if="model.includes(o.value)" class="size-4 shrink-0" />
            </button>
            <p v-if="!filtered.length && !custom" class="text-muted-foreground px-2 py-3 text-center text-sm">{{ emptyText }}</p>
        </div>
    </div>
</template>
