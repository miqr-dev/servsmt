<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { BellRing, Printer, ShieldCheck } from '@lucide/vue';
import { h } from 'vue';
import RoleMembersCard, { type MemberOption } from '@/components/RoleMembersCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Rollen & Berechtigungen > Korso (KorsoResponsibilityController, 2026-10-02).
 * Replaces hardcoded user ids in the Korso code:
 * - Korso_verwaltung: notified when a submitter restores an unassigned ticket (was 39).
 * - Printmarketing: Printmarketing Verwaltung (was users 1 + 312).
 */
defineProps<{
    korsoVerwaltung: MemberOption[];
    korsoAdmins: MemberOption[];
    printmarketing: MemberOption[];
    users: MemberOption[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Rollen & Berechtigungen', href: '/roles' },
            { title: 'Korso', href: '/korso-zustaendigkeiten' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});
</script>

<template>
    <Head title="Korso-Zuständigkeiten" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-xl font-semibold">Korso-Zuständigkeiten</h1>
            <p class="text-muted-foreground text-sm">Wer Benachrichtigungen bekommt und wer die Printmarketing Verwaltung öffnen darf.</p>
        </div>

        <RoleMembersCard
            role="Korso_verwaltung"
            title="Korso-Verwaltung"
            description="bekommt die Benachrichtigung, wenn ein Ersteller ein Ticket wiederherstellt, das niemandem zugewiesen ist. Nur Benachrichtigung, keine Rechte."
            :icon="BellRing"
            :members="korsoVerwaltung"
            :users="users"
            empty-warning="Niemand hat diese Rolle - Wiederherstellungen ohne Zuweisung gehen an niemanden."
        />

        <RoleMembersCard
            role="Korso_Admin"
            title="Korso-Admin"
            description="alle Korso-Rechte plus Rechtevergabe (Korso_ma), Onlinemarketing-/Zertifizierung-Artikel, Sekretariat-Gruppen und endgültig löschen."
            :icon="ShieldCheck"
            :members="korsoAdmins"
            :users="users"
            empty-warning="Niemand hat diese Rolle - nur Super_Admin hat die Korso-Admin-Rechte."
            add-note="Korso_Admin gibt alle Korso-Admin-Rechte. Hinzufügen?"
        />

        <RoleMembersCard
            role="Printmarketing"
            title="Printmarketing Verwaltung"
            description="darf die Printmarketing Verwaltung öffnen (Bestellungen, PDF-Export, „bestellt“ markieren). Der Link ist im Korso Dashboard."
            :icon="Printer"
            :members="printmarketing"
            :users="users"
            empty-warning="Niemand hat diese Rolle - nur Super_Admin kann die Printmarketing Verwaltung öffnen."
        />
    </div>
</template>
