<script setup lang="ts">
import { computed } from 'vue';
import Combobox from '@/components/ui/combobox/Combobox.vue';

// The "Welcher Rechner" picker, repeated identically (down to the exact
// Bitte-Wählen placeholder wording) across most tickets/computer creation
// forms - pc_problems, peripheralRequest, printer_in_out, and the
// software-request variants all @foreach'd the same $computers collection
// (InvItems where gart_id in [2, 3]) passed in from TicketController.
//
// Switched from a plain <select> to the shared Combobox (2026-09-18, at your
// request - "Rechner Wählen and alike" should be searchable): this list can
// run into the dozens/hundreds of entries with no other way to narrow it.

export type ComputerOption = { id: number; gname: string };

const props = withDefaults(
    defineProps<{
        computers: ComputerOption[];
        required?: boolean;
        label?: string;
    }>(),
    { required: true, label: 'Welcher Rechner' },
);

const model = defineModel<string | number | null>({ default: '' });

const options = computed(() => props.computers.map((c) => ({ value: c.id, label: c.gname })));
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-sm font-medium">{{ label }} <span v-if="required" class="text-muted-foreground">*</span></label>
        <Combobox v-model="model" :options="options" :required="required" />
    </div>
</template>
