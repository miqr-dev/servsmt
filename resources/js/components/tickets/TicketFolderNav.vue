<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';

/**
 * The small left-hand "Ordner" (folder) sidebar used by usertickets.blade.php
 * and userticketsdone.blade.php - up to 3 of these stack per page (Ticket /
 * Handwerk / Korso, each role-gated). Old markup varied each one's Bootstrap
 * card-outline accent color (primary/secondary/success) - flattened to the
 * same neutral bg-card treatment here, matching every other card in this
 * migration (Korso/Handwerk pages never reproduced those per-section accent
 * colors either).
 */
export type FolderLink = {
    label: string;
    href: string;
    count: number;
    icon?: LucideIcon;
    active?: boolean;
};

defineProps<{
    title: string;
    links: FolderLink[];
}>();
</script>

<template>
    <div class="bg-card text-card-foreground w-64 shrink-0 rounded-xl border p-4 shadow-sm">
        <h3 class="mb-3 font-semibold">{{ title }}</h3>
        <nav class="flex flex-col gap-1">
            <Link
                v-for="link in links"
                :key="link.href + link.label"
                :href="link.href"
                class="hover:bg-accent flex items-center justify-between gap-2 rounded-md px-3 py-2 text-sm"
                :class="link.active ? 'text-primary font-semibold' : ''"
            >
                <span class="flex min-w-0 items-center gap-2">
                    <component :is="link.icon" v-if="link.icon" class="h-4 w-4 shrink-0" />
                    <span class="truncate">{{ link.label }}</span>
                </span>
                <span class="bg-primary text-primary-foreground shrink-0 rounded-full px-2 py-0.5 text-xs">{{ link.count }}</span>
            </Link>
        </nav>
    </div>
</template>
