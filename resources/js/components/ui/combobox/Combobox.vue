<script setup lang="ts">
import { computed } from 'vue';
import {
    ComboboxAnchor,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxRoot,
    ComboboxTrigger,
    ComboboxViewport,
} from 'reka-ui';
import { Check, ChevronsUpDown } from '@lucide/vue';
import { cn } from '@/lib/utils';

// Shared searchable single-select, replacing the plain <select> every
// Tickets "which item" picker (Rechner/Drucker/Telefon/Geräte Art) used -
// these lists can run into dozens of entries with nothing else to narrow
// them by, unlike the Standort/Raum cascades (already filtered by the
// cascade itself, and grouped by city - see LocationRoomFields.vue). Built
// on reka-ui's Combobox primitives (already a transitive dependency via the
// shadcn-vue DropdownMenu/Tooltip components this app already uses - this is
// just the first place this migration wires Combobox up specifically).
//
// Deliberately one file rather than the per-primitive split
// components/ui/dropdown-menu and components/ui/tooltip use - this is the
// only consumer of Combobox so far, so that extra indirection isn't worth
// it yet. Revisit if a second, differently-styled combobox usage shows up.
//
// The dropdown list renders inline (no ComboboxPortal) so it stays inside
// this component's own DOM position - matches how every other overlay in
// the Tickets pages (the video/news modals on Tickets/Index.vue) is built
// with a plain positioned element rather than a teleport, and avoids a
// portal-to-body needing its own z-index coordination against the sidebar.

export type ComboboxOption = { value: string | number; label: string };

const props = withDefaults(
    defineProps<{
        options: ComboboxOption[];
        placeholder?: string;
        emptyText?: string;
        disabled?: boolean;
        required?: boolean;
    }>(),
    { placeholder: 'Bitte Wählen', emptyText: 'Keine Treffer', disabled: false, required: false },
);

const model = defineModel<string | number | null>({ default: '' });

function labelFor(value: string | number | null | undefined): string {
    if (value === null || value === undefined || value === '') return '';
    return props.options.find((o) => String(o.value) === String(value))?.label ?? '';
}

const displayValue = (val: unknown) => labelFor(val as string | number);

// A hidden native input mirroring "is something selected", purely so the
// browser's built-in required-field validation (which every old <select
// required> gave for free) still fires on submit - Combobox's own text
// input isn't a real form control the browser's constraint validation can
// check against.
const hiddenRequiredValue = computed(() => (model.value ? 'x' : ''));
</script>

<template>
    <ComboboxRoot v-model="model" :disabled="disabled" open-on-click class="relative">
        <ComboboxAnchor
            :class="cn('border-input bg-background flex h-9 items-center gap-1 rounded-md border px-3 text-sm', disabled && 'opacity-60')"
        >
            <ComboboxInput
                class="placeholder:text-muted-foreground w-full bg-transparent outline-none"
                :placeholder="placeholder"
                :display-value="displayValue"
            />
            <ComboboxTrigger class="text-muted-foreground shrink-0">
                <ChevronsUpDown class="h-4 w-4" />
            </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxContent
            class="bg-popover text-popover-foreground data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 absolute z-50 mt-1 max-h-64 w-[var(--reka-combobox-trigger-width)] overflow-hidden rounded-md border shadow-md"
        >
            <ComboboxViewport class="p-1">
                <ComboboxEmpty class="text-muted-foreground px-2 py-3 text-center text-sm">
                    {{ emptyText }}
                </ComboboxEmpty>
                <ComboboxItem
                    v-for="opt in options"
                    :key="opt.value"
                    :value="opt.value"
                    class="data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground flex cursor-pointer items-center justify-between rounded-sm px-2 py-1.5 text-sm outline-none"
                >
                    {{ opt.label }}
                    <ComboboxItemIndicator>
                        <Check class="h-4 w-4" />
                    </ComboboxItemIndicator>
                </ComboboxItem>
            </ComboboxViewport>
        </ComboboxContent>

        <input v-if="required" type="text" tabindex="-1" aria-hidden="true" required :value="hiddenRequiredValue" class="sr-only" />
    </ComboboxRoot>
</template>
