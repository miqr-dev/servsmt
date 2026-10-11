<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Einstellungen', href: '/settings' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

type SettingsLink = { label: string; href: string };

const sections: { title: string; links: SettingsLink[] }[] = [
    {
        title: 'Benutzereinstellungen',
        links: [
            { label: 'Benutzerverwaltung', href: '/users' },
            { label: 'Rollen & Berechtigungen', href: '/roles' },
            { label: 'Rollen-Übersicht (wer darf was)', href: '/rollen-uebersicht' },
            { label: 'Mitarbeiteraustritt', href: '/terminations' },
            { label: 'Neuer Mitarbeiter: Standard-Passwort', href: '/settings/neuer-mitarbeiter' },
        ],
    },
    {
        title: 'Publish News',
        links: [
            { label: 'Dashboard Popup', href: '/settings/publish/popup/1' },
            { label: 'Nachrichtenleiste', href: '/settings/publish/newsbar/1' },
            { label: 'Standortbesuch', href: '/settings/standortbesuch' },
        ],
    },
    {
        title: 'Address Book',
        links: [{ label: 'Address Book', href: '/settings/addressbook' }],
    },
    {
        // app-only settings for the AD rooms in the ticket forms (2026-10-09)
        title: 'Standorte & Räume',
        links: [
            { label: 'Standorte & Adressen (AD)', href: '/settings/ad-standorte' },
            { label: 'AD Räume & Computer', href: '/ad-inventory' },
        ],
    },
    {
        title: 'Inventur Einstellungen',
        links: [
            {
                label: 'Stadt / Adresse / Raum verwalten (klassische Ansicht)',
                href: '/settings/legacy',
            },
        ],
    },
];
</script>

<template>
    <Head title="Einstellungen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-xl font-semibold">Einstellungen</h1>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="section in sections" :key="section.title" class="bg-card text-card-foreground rounded-xl border shadow-sm">
                <div class="border-b p-4">
                    <h2 class="font-semibold">{{ section.title }}</h2>
                </div>
                <div class="divide-y">
                    <Link v-for="link in section.links" :key="link.href" :href="link.href" class="hover:bg-accent block px-4 py-2.5 text-sm">
                        {{ link.label }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
