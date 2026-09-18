<script setup lang="ts">
import { router } from '@inertiajs/vue3';

/**
 * Renders a Laravel paginator's default `links` array
 * ({url, label, active}[], from LengthAwarePaginator::links()/toArray()).
 * Labels come from Laravel itself ("&laquo; Previous", page numbers, "Next
 * &raquo;") so v-html is safe here - it's not user input.
 */
type PageLink = {
    url: string | null;
    label: string;
    active: boolean;
};

defineProps<{
    links: PageLink[];
}>();

function visit(url: string | null) {
    if (!url) return;

    router.visit(url, { preserveState: true, preserveScroll: true });
}
</script>

<template>
    <nav v-if="links.length > 3" class="flex flex-wrap items-center gap-1">
        <button
            v-for="(link, index) in links"
            :key="index"
            type="button"
            :disabled="!link.url"
            class="min-w-8 rounded-md border px-2.5 py-1 text-sm"
            :class="[
                link.active
                    ? 'border-primary bg-primary text-primary-foreground'
                    : 'border-border hover:bg-accent',
                !link.url && 'cursor-not-allowed opacity-40 hover:bg-transparent',
            ]"
            v-html="link.label"
            @click="visit(link.url)"
        />
    </nav>
</template>
