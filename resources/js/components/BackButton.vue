<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { canGoBackInApp } from '@/lib/navHistory';

/**
 * "Zurück" on every ticket page (IT / Handwerk / Korso): returns to wherever
 * the user came from (Dashboard, Meine Tickets, Offen, city list, …) with its
 * filters/tab still in the URL. Opened directly (e-mail link, new tab), it
 * goes to `fallback` instead of leaving servsmt.
 */
const props = withDefaults(defineProps<{ fallback: string; label?: string }>(), { label: 'Zurück' });

function goBack() {
    if (canGoBackInApp()) window.history.back();
    else router.visit(props.fallback);
}
</script>

<template>
    <button
        type="button"
        class="border-border bg-card hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
        @click="goBack"
    >
        <ArrowLeft class="size-4" />
        {{ label }}
    </button>
</template>
