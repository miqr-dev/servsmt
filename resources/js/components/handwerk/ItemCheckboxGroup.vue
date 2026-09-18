<script setup lang="ts">
import { computed } from 'vue';
import { ChevronRight } from '@lucide/vue';
import { setItemChecked, type ItemDef } from '@/lib/handwerkItems';

// One category of "checkbox + qty" items on the Mobiliar/Elektro creation
// forms. The Einrichtungsgegenstände form's 8 categories (Tafel/Tische/
// Stühle/etc.) were Bootstrap `data-toggle="collapse"` button groups,
// collapsed by default and revealed on click - reproduced here with a native
// <details> (closed by default, no JS needed). The Elektro form's single
// item group had no such toggle in the old Blade version - it was always
// shown directly - so `collapsible: false` renders it as a plain block
// instead of introducing a click that wasn't there before.
//
// Checking an item auto-sets its qty to 1; unchecking clears it back to ''
// (setItemChecked, shared with the Tisch section in
// Einrichtungsgegenstaende.vue). Rows use :checked/@change rather than
// v-model so that atomic update always wins, instead of racing a plain
// v-model write on the same key.

const props = withDefaults(
    defineProps<{
        title: string;
        items: ItemDef[];
        form: Record<string, unknown>;
        collapsible?: boolean;
    }>(),
    { collapsible: true },
);

const selectedCount = computed(() => props.items.filter((item) => !!props.form[item.key]).length);

function onToggle(key: string, event: Event) {
    setItemChecked(props.form, key, (event.target as HTMLInputElement).checked);
}
</script>

<template>
    <details v-if="collapsible" class="group rounded-md border">
        <summary
            class="bg-muted/40 hover:bg-muted flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm font-semibold select-none"
        >
            <span class="flex items-center gap-2">
                {{ title }}
                <span
                    v-if="selectedCount > 0"
                    class="bg-primary text-primary-foreground inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-medium"
                >
                    {{ selectedCount }}
                </span>
            </span>
            <ChevronRight class="text-muted-foreground h-4 w-4 shrink-0 transition-transform duration-200 group-open:rotate-90" />
        </summary>
        <div class="grid gap-2 p-3 sm:grid-cols-2">
            <div
                v-for="item in items"
                :key="item.key"
                class="flex items-center gap-2 rounded-md border px-3 py-2 transition-colors"
                :class="form[item.key] ? 'border-primary bg-primary/5' : 'border-input hover:bg-muted/40'"
            >
                <label :for="item.key" class="flex flex-1 cursor-pointer items-center gap-2">
                    <input
                        :id="item.key"
                        :checked="!!form[item.key]"
                        type="checkbox"
                        class="border-input text-primary h-4 w-4 rounded"
                        @change="onToggle(item.key, $event)"
                    />
                    <span class="text-sm">{{ item.label }}</span>
                </label>
                <input
                    v-if="form[item.key]"
                    v-model="(form[`${item.key}_qty`] as string)"
                    type="number"
                    min="1"
                    class="border-input bg-background h-8 w-16 rounded-md border px-2 text-sm"
                />
            </div>
        </div>
    </details>

    <div v-else class="rounded-md border p-3">
        <p class="mb-2 flex items-center gap-2 text-sm font-semibold">
            {{ title }}
            <span
                v-if="selectedCount > 0"
                class="bg-primary text-primary-foreground inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-medium"
            >
                {{ selectedCount }}
            </span>
        </p>
        <div class="grid gap-2 sm:grid-cols-2">
            <div
                v-for="item in items"
                :key="item.key"
                class="flex items-center gap-2 rounded-md border px-3 py-2 transition-colors"
                :class="form[item.key] ? 'border-primary bg-primary/5' : 'border-input hover:bg-muted/40'"
            >
                <label :for="item.key" class="flex flex-1 cursor-pointer items-center gap-2">
                    <input
                        :id="item.key"
                        :checked="!!form[item.key]"
                        type="checkbox"
                        class="border-input text-primary h-4 w-4 rounded"
                        @change="onToggle(item.key, $event)"
                    />
                    <span class="text-sm">{{ item.label }}</span>
                </label>
                <input
                    v-if="form[item.key]"
                    v-model="(form[`${item.key}_qty`] as string)"
                    type="number"
                    min="1"
                    class="border-input bg-background h-8 w-16 rounded-md border px-2 text-sm"
                />
            </div>
        </div>
    </div>
</template>
