<script setup lang="ts">
/**
 * Search box + "rows per page" picker, paired with useDataTable.ts.
 * `v-model:search` / `v-model:pageSize` on the caller side.
 */
withDefaults(
    defineProps<{
        search: string;
        pageSize: number;
        pageSizeOptions?: number[];
        searchPlaceholder?: string;
    }>(),
    {
        pageSizeOptions: () => [15, 25, 50, 100],
        searchPlaceholder: 'Suchen...',
    },
);

const emit = defineEmits<{ 'update:search': [string]; 'update:pageSize': [number] }>();
</script>

<template>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <input
            :value="search"
            type="search"
            :placeholder="searchPlaceholder"
            class="border-input bg-background h-9 w-64 rounded-md border px-3 text-sm"
            @input="emit('update:search', ($event.target as HTMLInputElement).value)"
        />
        <label class="text-muted-foreground flex items-center gap-2 text-sm">
            Anzeigen:
            <select
                :value="pageSize"
                class="border-input bg-background h-9 rounded-md border px-2 text-sm"
                @change="emit('update:pageSize', Number(($event.target as HTMLSelectElement).value))"
            >
                <option v-for="opt in pageSizeOptions" :key="opt" :value="opt">{{ opt }}</option>
                <option :value="0">Alle</option>
            </select>
        </label>
    </div>
</template>
