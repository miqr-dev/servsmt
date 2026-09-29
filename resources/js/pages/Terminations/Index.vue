<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { h } from 'vue';
import TerminationsBox, { type TerminationRow } from '@/components/dashboard/TerminationsBox.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Kündigungen on their own page (/terminations, TerminationController@index,
 * sidebar "Verwaltung > Kündigungen", HR + Super_Admin). Same table and
 * actions as the Dashboard box, without the row limit, plus Beschäftigung.
 */
defineProps<{ terminations: TerminationRow[] }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Kündigungen', href: '/terminations' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});
</script>

<template>
    <Head title="Kündigungen" />

    <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
        <TerminationsBox class="mx-auto w-full max-w-5xl" :terminations="terminations" full-page />
    </div>
</template>
