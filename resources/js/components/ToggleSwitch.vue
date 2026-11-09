<script setup lang="ts">
/**
 * On/off switch (role="switch"), v-model boolean. Pass `labelledby` with the
 * id of the visible label text. First used by Einstellungen > Popup and
 * Nachrichtenleiste (2026-11-03).
 */
defineProps<{ labelledby?: string; disabled?: boolean }>();

const model = defineModel<boolean>({ required: true });
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="model"
        :aria-labelledby="labelledby"
        :disabled="disabled"
        class="focus-visible:ring-ring relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus-visible:ring-2 focus-visible:outline-none disabled:opacity-50"
        :class="model ? 'bg-primary' : 'bg-input'"
        @click="model = !model"
    >
        <span class="bg-background inline-block h-5 w-5 rounded-full shadow transition-transform" :class="model ? 'translate-x-5' : 'translate-x-0.5'" />
    </button>
</template>
