<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, h } from 'vue';
import { LayoutDashboard } from '@lucide/vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/korso/index.blade.php - 3 "new ticket"
 * category cards (Zertifizierung & Qualitätsmanagement / Printmarketing /
 * Onlinemarketing), each linking out to its own creation form. Those 3 forms
 * (KorsoController@zertifizierung/printmarketing/onlinemarketing) are still
 * Blade for now - Inertia's <Link> falls back to a normal browser navigation
 * for a non-Inertia response, same pattern used for Handwerk's forms before
 * they were converted.
 *
 * KorsoController@index computed a $cityCounts query that the old Blade view
 * never actually referenced (no city badges anywhere in that template,
 * unlike Handwerk/Index.vue) - dropped rather than carried over unused, same
 * as the dead $rolePermissions query removed from RoleController@edit
 * earlier in this project.
 *
 * The "Dashboard" link below reproduces the quick-access kickstarter-icon
 * link admin_header.blade.php shows Korso_ma/Korso_Admin/Super_Admin users
 * (pointing at KorsoController@dashboard) - that old header isn't used by
 * Vue pages, so without this the now-converted Korso/Dashboard.vue would
 * have had no link into it from anywhere in the new UI either.
 */

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Korso Ticket', href: '/korso' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const roles = computed(() => page.props.auth.user?.roles ?? []);
const canSeeDashboard = computed(
    () => roles.value.includes('Korso_ma') || roles.value.includes('Korso_Admin') || roles.value.includes('Super_Admin'),
);

type Card = { title: string; description: string; href: string };

const cards: Card[] = [
    {
        title: 'Zertifizierung & Qualitätsmanagement',
        description:
            'Beantragung Maßnahmenummer, Erstellung Kurzkonzepte, IS+ Verkaufsartikel/Kurse, BAMF, Ausschreibungen sowie Fehlermeldungen oder Aktualisierungen in Kursnet, InfoNet & Laufwerken.',
        href: '/zertifizierung',
    },
    {
        title: 'Printmarketing',
        description:
            'Flyer, Visitenkarten, Give-Aways, Messen, Beklebungen & Beschilderungen sowie Flucht- und Rettungspläne oder sonstige Anliegen.',
        href: '/printmarketing',
    },
    {
        title: 'Onlinemarketing',
        description:
            'Fehlermeldungen oder Aktualisierungen auf der Website, Social-Media-Beiträge oder Ideen sowie der Versand von Newslettern & Anschreiben.',
        href: '/onlinemarketing',
    },
];
</script>

<template>
    <Head title="Korso Aufgaben" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div v-if="canSeeDashboard" class="flex justify-end">
            <Link
                href="/korso-dashboard"
                class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm font-medium"
            >
                <LayoutDashboard class="h-4 w-4" />
                Dashboard
            </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="card in cards" :key="card.title" class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <Link :href="card.href" class="block border-b p-4 text-center font-semibold hover:underline" style="color: #65a30d">
                    {{ card.title }}
                </Link>
                <div class="text-muted-foreground p-4 text-center text-sm">
                    {{ card.description }}
                </div>
            </div>
        </div>
    </div>
</template>
