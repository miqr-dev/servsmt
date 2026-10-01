<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { BellRing, CheckCheck, ChevronDown, ChevronRight, MessageSquare } from '@lucide/vue';
import { computed, ref } from 'vue';

/**
 * "Neu für dich" on the Dashboard: unread notifications of ALL systems
 * (IT, Handwerk, Korso, Kündigungen, Erinnerungen), one line per ticket /
 * item instead of one per event. Data: DashboardController → NotificationFeed::groups().
 * Opening a ticket marks its notifications read on the server, so the line
 * is gone when you come back. Hidden when there is nothing new.
 */
export type FeedGroup = {
    key: string;
    kind: 'ticket' | 'handwerk' | 'korso' | 'other';
    id: number | null;
    title: string | null;
    events: string[];
    comments: number;
    latest: string | null;
    url: string | null;
    open: string | null;
};

const props = defineProps<{ groups: FeedGroup[] }>();

const VISIBLE = 5;
const expanded = ref(false);
const shown = computed(() => (expanded.value ? props.groups : props.groups.slice(0, VISIBLE)));

const KIND: Record<FeedGroup['kind'], { label: string; cls: string }> = {
    ticket: { label: 'IT', cls: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200' },
    handwerk: { label: 'Handwerk', cls: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' },
    korso: { label: 'Korso', cls: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' },
    other: { label: 'Info', cls: 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300' },
};

function summary(g: FeedGroup): string {
    const parts = [...g.events];
    if (g.comments === 1) parts.push('1 neuer Kommentar');
    else if (g.comments > 1) parts.push(`${g.comments} neue Kommentare`);

    return parts.join(' · ');
}

function timeAgo(iso: string | null): string {
    if (!iso) return '';
    const mins = Math.round((Date.now() - new Date(iso).getTime()) / 60000);
    if (mins < 1) return 'gerade eben';
    if (mins < 60) return `vor ${mins} Min.`;
    const h = Math.round(mins / 60);
    if (h < 24) return `vor ${h} Std.`;
    const d = Math.round(h / 24);

    return d === 1 ? 'gestern' : `vor ${d} Tagen`;
}

function href(g: FeedGroup): string {
    return g.open ?? g.url ?? '/';
}

const marking = ref(false);
function readAll() {
    marking.value = true;
    router.post('/notifications/read-all', {}, { preserveScroll: true, onFinish: () => (marking.value = false) });
}
</script>

<template>
    <section v-if="groups.length" class="bg-card text-card-foreground rounded-xl border shadow-sm">
        <header class="flex items-center justify-between gap-3 border-b px-5 py-3">
            <div class="flex items-center gap-2">
                <BellRing class="text-primary size-5" />
                <h2 class="font-semibold">Neu für dich</h2>
                <span class="bg-primary text-primary-foreground rounded-full px-2 py-0.5 text-xs font-semibold">{{ groups.length }}</span>
            </div>
            <button
                type="button"
                class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs font-medium disabled:opacity-50"
                :disabled="marking"
                @click="readAll"
            >
                <CheckCheck class="size-4" />
                Alle gelesen
            </button>
        </header>

        <ul class="divide-y">
            <li v-for="g in shown" :key="g.key">
                <Link :href="href(g)" class="hover:bg-accent/60 group flex items-center gap-3 px-5 py-3">
                    <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-semibold" :class="KIND[g.kind].cls">{{ KIND[g.kind].label }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">
                            <span v-if="g.id && g.kind !== 'other'" class="text-muted-foreground font-normal">#{{ g.id }} · </span
                            >{{ g.title || 'Ohne Titel' }}
                        </p>
                        <p class="text-muted-foreground flex items-center gap-1 truncate text-xs">
                            <MessageSquare v-if="g.comments" class="size-3 shrink-0" />
                            {{ summary(g) }}
                        </p>
                    </div>
                    <span class="text-muted-foreground shrink-0 text-xs whitespace-nowrap">{{ timeAgo(g.latest) }}</span>
                    <ChevronRight class="text-muted-foreground size-4 shrink-0 opacity-0 transition-opacity group-hover:opacity-100" />
                </Link>
            </li>
        </ul>

        <button
            v-if="groups.length > VISIBLE"
            type="button"
            class="text-muted-foreground hover:text-foreground hover:bg-accent/60 flex w-full items-center justify-center gap-1 border-t px-4 py-2 text-xs font-medium"
            @click="expanded = !expanded"
        >
            <ChevronDown class="size-4 transition-transform" :class="expanded ? 'rotate-180' : ''" />
            {{ expanded ? 'Weniger anzeigen' : `Alle ${groups.length} anzeigen` }}
        </button>
    </section>
</template>
