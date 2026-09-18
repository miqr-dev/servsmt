<script setup lang="ts">
import { computed, ref } from 'vue';

/**
 * Multi-file picker with image/PDF thumbnail preview and per-file removal,
 * shared by the Korso ticket-creation forms (Zertifizierung/Printmarketing/
 * Onlinemarketing) - ported from the jQuery `selectedFiles` array +
 * FileReader preview pattern duplicated across all 3 old Blade forms'
 * <script> blocks (which also reconstructed a DataTransfer on submit just to
 * keep the underlying <input>'s native `required` validity in sync with an
 * array it maintained separately - that dance isn't needed here since Vue
 * just holds the files directly).
 *
 * v-model is the File[] that lands on the parent's Inertia useForm()
 * `attachments` field, so submitting the form posts them as multipart as-is.
 * `requiredHint` is informational only (native `required` on a file input
 * can't reflect files added across multiple selections, since each `change`
 * only ever reports that event's batch) - the parent validates before
 * calling form.post(), matching the one old form that conditionally required
 * attachments (Zertifizierung's "Beantragung einer Maßnahmenummer").
 *
 * `accept`/`hint` default to the image-or-PDF wording Zertifizierung and
 * Printmarketing used; Onlinemarketing's general attachment field accepts
 * Office documents too, so it overrides both.
 */

const props = withDefaults(
    defineProps<{
        modelValue: File[];
        requiredHint?: boolean;
        accept?: string;
        hint?: string;
    }>(),
    {
        accept: 'image/*,application/pdf',
        hint: 'Bilder oder PDFs hochladen',
    },
);
const emit = defineEmits<{ 'update:modelValue': [File[]] }>();

const inputRef = ref<HTMLInputElement | null>(null);

const previews = computed(() =>
    props.modelValue.map((file) => ({
        file,
        url: file.type.startsWith('image') ? URL.createObjectURL(file) : null,
        isPdf: file.type === 'application/pdf',
    })),
);

function onChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const newFiles = Array.from(input.files ?? []);
    emit('update:modelValue', [...props.modelValue, ...newFiles]);
    input.value = '';
}

function removeAt(index: number) {
    const next = [...props.modelValue];
    next.splice(index, 1);
    emit('update:modelValue', next);
}
</script>

<template>
    <div class="flex flex-col gap-2">
        <label class="text-sm font-medium">
            Anhänge <span class="text-muted-foreground font-normal">({{ hint }})</span>
            <span v-if="requiredHint" class="text-red-600">*</span>
        </label>

        <input
            ref="inputRef"
            type="file"
            multiple
            :accept="accept"
            class="text-sm"
            @change="onChange"
        />
        <p class="text-muted-foreground text-xs">Maximale Größe: 5MB pro Datei</p>

        <div v-if="previews.length" class="bg-muted/30 flex flex-wrap gap-3 rounded-md border p-2">
            <div v-for="(p, idx) in previews" :key="idx" class="flex flex-col items-center gap-1">
                <img v-if="p.url" :src="p.url" class="h-16 w-16 rounded border object-cover" />
                <div v-else-if="p.isPdf" class="flex h-16 w-16 items-center justify-center rounded border text-red-600">PDF</div>
                <div v-else class="text-muted-foreground flex h-16 w-16 items-center justify-center rounded border text-xs">Datei</div>
                <span class="max-w-16 truncate text-xs">{{ p.file.name }}</span>
                <button type="button" class="text-xs text-red-600 hover:underline" @click="removeAt(idx)">Entfernen</button>
            </div>
        </div>
    </div>
</template>
