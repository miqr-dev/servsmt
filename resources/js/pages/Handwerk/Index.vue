<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { h } from 'vue';
import { History as HistoryIcon, ListChecks } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/handwerk/index.blade.php. The city badges
 * are only sent from the controller when the current user has
 * Super_Admin/handwerk_admin (same gate the old Blade page checked, now
 * enforced server-side so the counts never reach an unauthorized browser).
 *
 * The "Meine Tickets" / "Verlauf" links below point at
 * Handwerk/MyTickets.vue and Handwerk/History.vue - neither was linked from
 * anywhere in the old app (no sidebar/header entry, handwerkHistory.blade.php's
 * own "Offene"/"Erledigte" mini-nav was the only way in and even that wasn't
 * reachable from here), so they're added here to make both pages actually
 * reachable from the UI.
 */

defineProps<{
    cityCounts: Record<string, number>;
    citySlugs: Record<string, string>;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Handwerkaufgaben', href: '/handwerk' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

type Card = {
    title: string;
    links: { label: string; href: string }[];
};

const cards: Card[] = [
    { title: 'Neu - Standort', links: [{ label: 'Neu Standort vorbereitung', href: '/neustandort' }] },
    {
        title: 'Mobiliar - Einrichtung',
        links: [
            { label: 'Mobiliar', href: '/einrichtungsgegenstände' },
            { label: 'Elektro', href: '/elektro' },
        ],
    },
    {
        title: 'Reparatur',
        links: [
            { label: 'Mobiliar', href: '/reparatur_mobiliar' },
            { label: 'Elektro', href: '/reparatur_elektro' },
        ],
    },
    { title: 'Modifikation / Bauliche Veränderungen', links: [{ label: 'Modifikation', href: '/modifikation' }] },
];
</script>

<template>
    <Head title="Handwerkaufgaben" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div class="flex flex-wrap justify-end gap-2">
            <Link
                href="/my-handwerks-tickets"
                class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm font-medium"
            >
                <ListChecks class="h-4 w-4" />
                Meine Tickets
            </Link>
            <Link
                href="/usertHandwerkicketshistory"
                class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm font-medium"
            >
                <HistoryIcon class="h-4 w-4" />
                Verlauf
            </Link>
        </div>

        <div v-if="Object.keys(cityCounts).length" class="flex flex-wrap justify-center gap-3">
            <Link
                v-for="(count, city) in cityCounts"
                :key="city"
                :href="`/handwerker/${citySlugs[city]}`"
                class="border-border bg-card hover:bg-accent flex items-center gap-2 rounded-md border px-4 py-2 text-sm font-medium transition-colors"
            >
                {{ city }}
                <span
                    class="bg-primary text-primary-foreground inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1.5 text-xs font-semibold"
                >
                    {{ count }}
                </span>
            </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="card in cards"
                :key="card.title"
                class="bg-card text-card-foreground rounded-xl border shadow-sm"
            >
                <div class="border-b p-4 text-center font-semibold">{{ card.title }}</div>
                <div class="divide-y">
                    <Link
                        v-for="link in card.links"
                        :key="link.href"
                        :href="link.href"
                        class="text-primary hover:bg-accent block p-3 text-center text-sm font-medium"
                    >
                        {{ link.label }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
