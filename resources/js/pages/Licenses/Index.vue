<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { h } from 'vue';
import LicensesBox, { type LicenseRow } from '@/components/dashboard/LicensesBox.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Lizenzen on their own page (/licenses, LicenseController@index, sidebar
 * "Verwaltung > Lizenzen", Super_Admin only). Same component as the
 * Dashboard box (which shows only the ones expiring within 30 days).
 */
defineProps<{ licenses: LicenseRow[] }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Lizenzen', href: '/licenses' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});
</script>

<template>
    <Head title="Lizenzen" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <LicensesBox class="mx-auto w-full max-w-5xl" :licenses="licenses" full-page />
    </div>
</template>
