<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    CalendarRange,
    ChevronDown,
    ChevronRight,
    Forward,
    HardHat,
    Inbox,
    Mail,
    Megaphone,
    FileDown,
    Plus,
    Ticket as TicketIcon,
    Users,
} from '@lucide/vue';
import { computed, h, ref } from 'vue';
import AdminBoxes from '@/components/dashboard/AdminBoxes.vue';
import TerminationsBox, { type TerminationRow } from '@/components/dashboard/TerminationsBox.vue';
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
    own?: boolean;
};

type CityHandwerk = {
    id: number;
    problem_type: string;
    room: string | null;
    address: string | null;
    submitter: string;
    assignee: string | null;
    created_at: string | null;
};

const props = defineProps<{
    myForwardings?: Forwarding[];
    createShortcuts?: boolean;
    newsBar?: string | null;
    standortForwardings?: Forwarding[];
    standortLabel?: string;
    cityHandwerks?: CityHandwerk[];
    handwerkCity?: string;
    // HR / Super_Admin boxes - shapes are typed inside AdminBoxes.vue.
    terminations?: TerminationRow[];
    licenses?: unknown[];
    activeEmailForwardingTickets?: unknown[];
    historyEmailForwardingTickets?: unknown[];
}>();

const hasAdminBoxes = computed(() => !!(props.licenses || props.activeEmailForwardingTickets));

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

// Handwerk table (Sekretariat): sortable, oldest first by default, folded to
// the first row until expanded.
type HwSortKey = 'problem_type' | 'room' | 'submitter' | 'created_at' | 'assignee';
const hwSort = ref<{ key: HwSortKey; dir: 'asc' | 'desc' }>({ key: 'created_at', dir: 'asc' });
const hwExpanded = ref(false);

function toggleHwSort(key: HwSortKey) {
    hwSort.value = hwSort.value.key === key ? { key, dir: hwSort.value.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' };
}

const hwSorted = computed(() => {
    const { key, dir } = hwSort.value;
    const factor = dir === 'asc' ? 1 : -1;

    return [...(props.cityHandwerks ?? [])].sort((a, b) => {
        const va = a[key] ?? '';
        const vb = b[key] ?? '';
        // Empty values always last, whatever the direction.
        if (va === '' && vb !== '') return 1;
        if (vb === '' && va !== '') return -1;

        return String(va).localeCompare(String(vb), 'de', { numeric: true, sensitivity: 'base' }) * factor || (a.id - b.id) * factor;
    });
});

const hwVisible = computed(() => (hwExpanded.value ? hwSorted.value : hwSorted.value.slice(0, 1)));

const HW_COLUMNS: { key: HwSortKey; label: string; class: string }[] = [
    { key: 'problem_type', label: 'Aufgabe', class: '' },
    { key: 'room', label: 'Raum', class: 'hidden md:table-cell' },
    { key: 'submitter', label: 'Von', class: 'hidden sm:table-cell' },
    { key: 'created_at', label: 'Datum', class: '' },
    { key: 'assignee', label: 'Zugewiesen', class: 'hidden sm:table-cell' },
];

// Same targets and icons as the sidebar entries.
const SHORTCUTS = [
    {
        title: 'IT Ticket',
        href: '/ticket.index',
        icon: TicketIcon,
        color: 'text-sky-600 bg-sky-100 dark:bg-sky-900/40 dark:text-sky-300',
    },
    {
        title: 'Korso Ticket',
        href: '/korso',
        icon: Users,
        color: 'text-emerald-600 bg-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-300',
    },
    {
        title: 'Handwerk Ticket',
        href: '/handwerk',
        icon: HardHat,
        color: 'text-amber-600 bg-amber-100 dark:bg-amber-900/40 dark:text-amber-300',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6 lg:flex-row lg:items-start">
        <div class="flex min-w-0 flex-1 flex-col gap-6">
            <!-- Greeting + news (the news bar formerly on the IT ticket page) -->
            <div class="grid gap-6" :class="props.newsBar ? 'lg:grid-cols-2' : ''">
                <section class="bg-card text-card-foreground rounded-xl border p-6 shadow-sm">
                    <p class="text-muted-foreground text-sm">
                        {{ todayLabel }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight">
                        {{ greeting }}<span v-if="firstName">, {{ firstName }}</span
                        >!
                    </h1>
                </section>

                <section
                    v-if="props.newsBar"
                    class="flex gap-3 rounded-xl border border-rose-200 bg-rose-50 p-6 text-rose-900 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-100"
                >
                    <Megaphone class="mt-0.5 size-5 shrink-0" />
                    <div class="min-w-0">
                        <h2 class="text-sm font-semibold">Aktuelle Information</h2>
                        <p class="mt-1 text-sm break-words whitespace-pre-line">
                            {{ props.newsBar }}
                        </p>
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div v-if="props.standortForwardings || props.cityHandwerks" class="flex min-w-0 flex-col gap-6">
                    <!-- Sekretariat: active forwardings at their Standort + their own (replaces the personal list) -->
                    <section v-if="props.standortForwardings" class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
                        <header class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
                            <div class="flex min-w-0 items-center gap-2">
                                <Mail class="text-primary size-5 shrink-0" />
                                <div class="min-w-0">
                                    <h2 class="font-semibold">E-Mail-Weiterleitungen</h2>
                                    <p class="text-muted-foreground truncate text-xs">
                                        Aktiv am Standort
                                        {{ props.standortLabel }} ·
                                        {{ props.standortForwardings.length }}
                                    </p>
                                </div>
                            </div>
                            <Button as-child size="sm">
                                <Link :href="FORWARD_FORM">
                                    <Plus class="size-4" />
                                    Neue Weiterleitung
                                </Link>
                            </Button>
                        </header>

                        <div v-if="props.standortForwardings.length" class="max-h-[26rem] overflow-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/60 text-muted-foreground sticky top-0 text-left text-xs backdrop-blur">
                                    <tr>
                                        <th class="px-4 py-2 font-medium">Von</th>
                                        <th class="px-4 py-2 font-medium">An</th>
                                        <th class="px-4 py-2 font-medium">Zeitraum</th>
                                        <th class="hidden px-4 py-2 font-medium sm:table-cell">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="f in props.standortForwardings" :key="f.id" :class="f.own ? 'bg-primary/5' : ''">
                                        <td class="px-4 py-2 align-top">
                                            <span class="font-medium">{{ f.from }}</span>
                                            <span
                                                v-if="f.own"
                                                class="bg-primary/10 text-primary ml-1.5 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase"
                                                >Ich</span
                                            >
                                        </td>
                                        <td class="px-4 py-2 align-top">
                                            {{ f.to }}
                                        </td>
                                        <td class="text-muted-foreground px-4 py-2 align-top whitespace-nowrap">
                                            {{ fmt(f.start) }} –
                                            {{ fmt(f.end) }}
                                            <span class="mt-1 block text-xs sm:hidden" :class="f.active ? 'text-emerald-600' : 'text-amber-600'">{{
                                                f.active ? 'Aktiv' : 'Geplant'
                                            }}</span>
                                        </td>
                                        <td class="hidden px-4 py-2 align-top sm:table-cell">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="
                                                    f.active
                                                        ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200'
                                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'
                                                "
                                                >{{ f.active ? 'Aktiv' : 'Geplant' }}</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div
                            v-else
                            class="text-muted-foreground flex flex-1 flex-col items-center justify-center gap-3 px-5 py-10 text-center text-sm"
                        >
                            <Mail class="size-8 opacity-40" />
                            <p>Zurzeit keine aktiven E-Mail-Weiterleitungen an Ihrem Standort.</p>
                        </div>
                    </section>

                    <!-- Sekretariat: open Handwerk tasks of their city -->
                    <section v-if="props.cityHandwerks" class="bg-card text-card-foreground flex min-w-0 flex-col rounded-xl border shadow-sm">
                        <header class="flex flex-wrap items-center justify-between gap-3 border-b px-5 py-4">
                            <div class="flex min-w-0 items-center gap-2">
                                <HardHat class="size-5 shrink-0 text-amber-600" />
                                <div class="min-w-0">
                                    <h2 class="font-semibold">Handwerkaufgaben</h2>
                                    <p class="text-muted-foreground truncate text-xs">
                                        Offen in {{ props.handwerkCity }} ·
                                        {{ props.cityHandwerks.length }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button v-if="props.cityHandwerks.length" as-child size="sm" variant="outline">
                                    <a :href="`/handwerk/${encodeURIComponent(props.handwerkCity ?? '')}/open-tickets-pdf`">
                                        <FileDown class="size-4" />
                                        PDF
                                    </a>
                                </Button>
                                <Button as-child size="sm">
                                    <Link href="/handwerk">
                                        <Plus class="size-4" />
                                        Neue Aufgabe
                                    </Link>
                                </Button>
                            </div>
                        </header>

                        <div v-if="props.cityHandwerks.length" class="max-h-[26rem] overflow-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/60 text-muted-foreground sticky top-0 text-left text-xs backdrop-blur">
                                    <tr>
                                        <th v-for="c in HW_COLUMNS" :key="c.key" class="px-4 py-2 font-medium" :class="c.class">
                                            <button
                                                type="button"
                                                class="hover:text-foreground inline-flex items-center gap-1"
                                                :class="hwSort.key === c.key ? 'text-foreground' : ''"
                                                @click="toggleHwSort(c.key)"
                                            >
                                                {{ c.label }}
                                                <ArrowUp v-if="hwSort.key === c.key && hwSort.dir === 'asc'" class="size-3" />
                                                <ArrowDown v-else-if="hwSort.key === c.key" class="size-3" />
                                                <ArrowUpDown v-else class="size-3 opacity-40" />
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr
                                        v-for="h in hwVisible"
                                        :key="h.id"
                                        class="hover:bg-accent/60 cursor-pointer"
                                        @click="router.visit(`/handwerk/${h.id}?from=dashboard`)"
                                    >
                                        <td class="px-4 py-2 align-top">
                                            <Link :href="`/handwerk/${h.id}?from=dashboard`" class="font-medium hover:underline" @click.stop>
                                                {{ h.problem_type }}
                                            </Link>
                                            <span class="text-muted-foreground block text-xs">#{{ h.id }}</span>
                                            <span class="text-muted-foreground block text-xs sm:hidden">{{ h.submitter }}</span>
                                        </td>
                                        <td class="hidden px-4 py-2 align-top md:table-cell">
                                            {{ h.room ?? '–' }}
                                            <span v-if="h.address" class="text-muted-foreground block text-xs">{{ h.address }}</span>
                                        </td>
                                        <td class="hidden px-4 py-2 align-top sm:table-cell">
                                            {{ h.submitter }}
                                        </td>
                                        <td class="text-muted-foreground px-4 py-2 align-top whitespace-nowrap">
                                            {{ h.created_at ? fmt(h.created_at) : '–' }}
                                        </td>
                                        <td class="hidden px-4 py-2 align-top sm:table-cell">
                                            <span v-if="h.assignee">{{ h.assignee }}</span>
                                            <span
                                                v-else
                                                class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
                                                >Offen</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button
                            v-if="props.cityHandwerks.length > 1"
                            type="button"
                            class="text-muted-foreground hover:text-foreground hover:bg-accent/60 flex items-center justify-center gap-1 border-t px-4 py-2 text-xs font-medium transition-colors"
                            :aria-expanded="hwExpanded"
                            @click="hwExpanded = !hwExpanded"
                        >
                            <ChevronDown class="size-4 transition-transform" :class="hwExpanded ? 'rotate-180' : ''" />
                            {{ hwExpanded ? 'Weniger anzeigen' : `Alle ${props.cityHandwerks.length} anzeigen` }}
                        </button>

                        <div
                            v-if="!props.cityHandwerks.length"
                            class="text-muted-foreground flex flex-1 flex-col items-center justify-center gap-3 px-5 py-10 text-center text-sm"
                        >
                            <HardHat class="size-8 opacity-40" />
                            <p>
                                Keine offenen Handwerkaufgaben in
                                {{ props.handwerkCity }}.
                            </p>
                        </div>
                    </section>
                </div>

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

                <!-- HR: Kündigungen - right column, under the news box, beside the forwardings -->
                <TerminationsBox v-if="props.terminations" class="lg:self-start" :terminations="props.terminations" />
            </div>

            <!-- Super_Admin: Lizenzen + all email forwardings -->
            <AdminBoxes
                v-if="hasAdminBoxes"
                :licenses="props.licenses as any"
                :active-email-forwarding-tickets="props.activeEmailForwardingTickets as any"
                :history-email-forwarding-tickets="props.historyEmailForwardingTickets as any"
            />
        </div>

        <!-- Verwaltung: small "Neues Ticket" shortcut box, far right -->
        <aside
            v-if="props.createShortcuts"
            class="bg-card text-card-foreground w-full shrink-0 rounded-xl border shadow-sm lg:sticky lg:top-4 lg:w-56"
        >
            <h2 class="text-muted-foreground border-b px-4 py-3 text-xs font-semibold tracking-wide uppercase">Neues Ticket</h2>
            <nav class="flex flex-col p-2">
                <Link
                    v-for="s in SHORTCUTS"
                    :key="s.href"
                    :href="s.href"
                    class="hover:bg-accent group flex items-center gap-3 rounded-lg px-2 py-2 text-sm font-medium transition-colors"
                >
                    <span class="flex size-8 items-center justify-center rounded-md" :class="s.color">
                        <component :is="s.icon" class="size-4" />
                    </span>
                    <span class="flex-1">{{ s.title }}</span>
                    <ChevronRight class="text-muted-foreground size-4 opacity-0 transition-opacity group-hover:opacity-100" />
                </Link>
            </nav>
        </aside>
    </div>
</template>
