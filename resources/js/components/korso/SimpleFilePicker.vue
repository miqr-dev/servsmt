<script setup lang="ts">
/**
 * Minimal "choose files" control with a filename/count readout instead of a
 * thumbnail grid - matches the old ChatGPT-project modal's 5 per-field
 * attachment slots (chatgpt_introduction_attachments etc.), which only ever
 * showed the selected filename(s) as text, not previews.
 */

const props = defineProps<{
    modelValue: File[];
    label: string;
}>();
const emit = defineEmits<{ 'update:modelValue': [File[]] }>();

function onChange(event: Event) {
    const input = event.target as HTMLInputElement;
    emit('update:modelValue', Array.from(input.files ?? []));
}

const summary = () => {
    if (!props.modelValue.length) return 'Keine Dateien ausgewählt';
    if (props.modelValue.length === 1) return props.modelValue[0].name;
    return `${props.modelValue.length} Dateien ausgewählt`;
};
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-muted-foreground text-xs font-medium">{{ label }}</label>
        <div class="flex flex-wrap items-center gap-2">
            <input type="file" multiple class="text-xs" @change="onChange" />
            <span class="text-muted-foreground text-xs">{{ summary() }}</span>
        </div>
    </div>
</template>
