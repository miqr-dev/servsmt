<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { h } from 'vue';
import AdminBoxes from '@/components/dashboard/AdminBoxes.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * E-Mail-Weiterleitungen (/email-forwardings, DashboardController@emailForwardings,
 * sidebar "Verwaltung > E-Mail-Weiterleitungen", Super_Admin only).
 * Active forwardings + history of removed ones - moved here from the
 * Dashboard, which now only shows the overdue ones (bis ≤ gestern).
 */
defineProps<{
    activeEmailForwardingTickets: unknown[];
    historyEmailForwardingTickets: unknown[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'E-Mail-Weiterleitungen', href: '/email-forwardings' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});
</script>

<template>
    <Head title="E-Mail-Weiterleitungen" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <AdminBoxes
            :active-email-forwarding-tickets="activeEmailForwardingTickets as any"
            :history-email-forwarding-tickets="historyEmailForwardingTickets as any"
        />
    </div>
</template>
