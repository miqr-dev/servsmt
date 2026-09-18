<script setup lang="ts">
import { computed } from 'vue';

type PermissionRow = { id: number; name: string };

const props = defineProps<{
    permissionsByCategory: Record<string, PermissionRow[]>;
    modelValue: number[];
}>();

const emit = defineEmits<{
    'update:modelValue': [number[]];
}>();

const selected = computed({
    get: () => new Set(props.modelValue),
    set: () => {},
});

function isChecked(id: number): boolean {
    return selected.value.has(id);
}

function toggle(id: number, checked: boolean) {
    const next = new Set(props.modelValue);

    if (checked) {
        next.add(id);
    } else {
        next.delete(id);
    }

    emit('update:modelValue', Array.from(next));
}

function categoryState(perms: PermissionRow[]): 'all' | 'some' | 'none' {
    const checkedCount = perms.filter((p) => isChecked(p.id)).length;

    if (checkedCount === 0) return 'none';
    if (checkedCount === perms.length) return 'all';

    return 'some';
}

function toggleCategory(perms: PermissionRow[], checked: boolean) {
    const next = new Set(props.modelValue);

    for (const p of perms) {
        if (checked) {
            next.add(p.id);
        } else {
            next.delete(p.id);
        }
    }

    emit('update:modelValue', Array.from(next));
}

function setIndeterminate(el: Event, state: 'all' | 'some' | 'none') {
    const input = el.target as HTMLInputElement | null;
    if (input) input.indeterminate = state === 'some';
}
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div
            v-for="(perms, categoryName) in permissionsByCategory"
            :key="categoryName"
            class="rounded-lg border p-3"
        >
            <label class="flex items-center gap-2 border-b pb-2 font-semibold">
                <input
                    type="checkbox"
                    :checked="categoryState(perms) === 'all'"
                    @change="
                        (e: Event) => {
                            setIndeterminate(e, categoryState(perms));
                            toggleCategory(
                                perms,
                                (e.target as HTMLInputElement).checked,
                            );
                        }
                    "
                />
                {{ categoryName }}
            </label>
            <ul class="mt-2 space-y-1">
                <li v-for="perm in perms" :key="perm.id">
                    <label class="flex items-center gap-2 text-sm">
                        <input
                            type="checkbox"
                            :checked="isChecked(perm.id)"
                            @change="
                                toggle(
                                    perm.id,
                                    ($event.target as HTMLInputElement).checked,
                                )
                            "
                        />
                        {{ perm.name }}
                    </label>
                </li>
            </ul>
        </div>
    </div>
</template>
