<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, CalendarRange, Forward, Inbox, Mail, Plus } from '@lucide/vue';
import { computed, h } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Auth, BreadcrumbItem } from '@/types';

/**
 * The unified Dashboard ("/", DashboardController@index) - one landing page
 * for every user. Boxes are added per role: each box shows only when the
 * controller sent its prop, so the server decides who sees what.
 *
 * Verwaltung (base role): greeting, own email forwardings (from or to the
 * user, current + upcoming), shortcut to a new Email Weiterleitung ticket.
 */

type Forwarding = {
    id: number;
    direction: 'out' | 'in';
    from: string;
    to: string;
    start: string;
    end: string;
    active: boolean;
};

const props = defineProps<{
    myForwardings?: Forwarding[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth.user);

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Guten Morgen';
    if (hour < 18) return 'Guten Tag';

    return 'Guten Abend';
});

const firstName = computed(() => user.value?.vorname || user.value?.name || '');

const todayLabel = new Date().toLocaleDateString('de-DE', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
});

/** "2026-10-05" -> "05.10.2026" */
function fmt(iso: string): string {
    const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(iso);

    return m ? `${m[3]}.${m[2]}.${m[1]}` : iso;
}

const FORWARD_FORM = '/ticket.forward';
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Greeting -->
        <section class="bg-card text-card-foreground rounded-xl border p-6 shadow-sm">
            <p class="text-muted-foreground text-sm">{{ todayLabel }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                {{ greeting }}<span v-if="firstName">, {{ firstName }}</span>!
            </h1>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Verwaltung: own email forwardings -->
            <section v-if="props.myForwardings" class="bg-card text-card-foreground flex flex-col rounded-xl border shadow-sm">
                <header class="flex items-center justify-between gap-3 border-b px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Mail class="text-primary size-5" />
                        <h2 class="font-semibold">Meine E-Mail-Weiterleitungen</h2>
                    </div>
                    <Button as-child size="sm">
                        <Link :href="FORWARD_FORM">
                            <Plus class="size-4" />
                            Neue Weiterleitung
                        </Link>
                    </Button>
                </header>

                <ul v-if="props.myForwardings.length" class="divide-y">
                    <li v-for="f in props.myForwardings" :key="f.id" class="flex flex-col gap-2 px-5 py-4">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-medium"
                                :class="
                                    f.direction === 'out'
                                        ? 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200'
                                        : 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200'
                                "
                            >
                                <component :is="f.direction === 'out' ? Forward : Inbox" class="size-3" />
                                {{ f.direction === 'out' ? 'Meine E-Mails werden weitergeleitet' : 'Ich erhalte Weiterleitung' }}
                            </span>
                            <span
                                class="rounded-full px-2 py-0.5 font-medium"
                                :class="
                                    f.active
                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'
                                "
                            >
                                {{ f.active ? 'Aktiv' : 'Geplant' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="font-medium">{{ f.from }}</span>
                            <ArrowRight class="text-muted-foreground size-4 shrink-0" />
                            <span class="font-medium">{{ f.to }}</span>
                        </div>

                        <div class="text-muted-foreground flex items-center gap-1.5 text-sm">
                            <CalendarRange class="size-4" />
                            vom {{ fmt(f.start) }} bis {{ fmt(f.end) }}
                        </div>
                    </li>
                </ul>

                <div v-else class="text-muted-foreground flex flex-1 flex-col items-center justify-center gap-3 px-5 py-10 text-center text-sm">
                    <Mail class="size-8 opacity-40" />
                    <p>Keine aktuellen oder geplanten E-Mail-Weiterleitungen.</p>
                    <Link :href="FORWARD_FORM" class="text-primary font-medium hover:underline">Weiterleitung beantragen</Link>
                </div>
            </section>
        </div>
    </div>
</template>
