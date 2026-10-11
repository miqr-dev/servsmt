<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Bell, Check, ChevronDown, CircleAlert, Code, Crown, ExternalLink, Info, Search, TriangleAlert, Users, X } from '@lucide/vue';
import { computed, h, onMounted, ref } from 'vue';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Rollen & Berechtigungen > Übersicht (RoleOverviewController,
 * App\Support\RoleOverview, 2026-10-11). Super_Admin only.
 *  - Diagram: Super_Admin › Verwaltung (every employee) › add-on roles per
 *    area, with member counts. Click a role for the details panel: what it
 *    can do, what it is notified about, where it is assigned, its members.
 *  - Matrix: every right × every role. Rights from config/route_access.php
 *    are resolved live on the server; the rest is listed in RoleOverview.
 *  - "Zu prüfen": roles missing in the DB, roles nobody has, DB roles the
 *    code never checks, users without Verwaltung.
 * ?rolle=Korso_ma opens that role directly.
 */

type Person = { id: number; name: string; username: string; ort: string | null };
type Kind = 'all' | 'base' | 'rights' | 'notify';
type RoleInfo = {
    key: string;
    group: string;
    kind: Kind;
    summary: string;
    notes: string[];
    assign: string;
    menu: string[];
    exists: boolean;
    queried: string | null;
    count: number;
    members: Person[];
};
type Capability = {
    id: number;
    area: string;
    label: string;
    hint: string | null;
    others: string | null;
    open: boolean;
    strict: boolean;
    /** role => limitation (null = no limitation) */
    cells: Record<string, string | null>;
    source: string | null;
    missingRoute: boolean;
};
type UnknownRole = { key: string; count: number; members: Person[] };

const props = defineProps<{
    groups: Record<string, string>;
    areas: Record<string, string>;
    roles: RoleInfo[];
    capabilities: Capability[];
    unknownRoles: UnknownRole[];
    activeUsers: number;
    withoutVerwaltungCount: number;
    withoutVerwaltung: Person[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Rollen & Berechtigungen', href: '/roles' },
            { title: 'Übersicht', href: '/rollen-uebersicht' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

// ─── Styles per group (full class strings, so Tailwind finds them) ──────
type GroupStyle = { head: string; soft: string; dot: string; col: string };
const GROUP_STYLE: Record<string, GroupStyle> = {
    system: {
        head: 'bg-slate-800 text-white dark:bg-slate-200 dark:text-slate-900',
        soft: 'border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-900/40',
        dot: 'bg-slate-700 dark:bg-slate-300',
        col: 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-100',
    },
    basis: {
        head: 'bg-sky-600 text-white',
        soft: 'border-sky-200 bg-sky-50 dark:border-sky-900 dark:bg-sky-950/40',
        dot: 'bg-sky-500',
        col: 'bg-sky-100 text-sky-900 dark:bg-sky-950 dark:text-sky-100',
    },
    it: {
        head: 'bg-blue-600 text-white',
        soft: 'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950/40',
        dot: 'bg-blue-500',
        col: 'bg-blue-100 text-blue-900 dark:bg-blue-950 dark:text-blue-100',
    },
    standort: {
        head: 'bg-teal-600 text-white',
        soft: 'border-teal-200 bg-teal-50 dark:border-teal-900 dark:bg-teal-950/40',
        dot: 'bg-teal-500',
        col: 'bg-teal-100 text-teal-900 dark:bg-teal-950 dark:text-teal-100',
    },
    handwerk: {
        head: 'bg-amber-600 text-white',
        soft: 'border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/40',
        dot: 'bg-amber-500',
        col: 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-100',
    },
    korso: {
        head: 'bg-emerald-600 text-white',
        soft: 'border-emerald-200 bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/40',
        dot: 'bg-emerald-500',
        col: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-100',
    },
    personal: {
        head: 'bg-rose-600 text-white',
        soft: 'border-rose-200 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/40',
        dot: 'bg-rose-500',
        col: 'bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-100',
    },
    inventar: {
        head: 'bg-cyan-700 text-white',
        soft: 'border-cyan-200 bg-cyan-50 dark:border-cyan-900 dark:bg-cyan-950/40',
        dot: 'bg-cyan-600',
        col: 'bg-cyan-100 text-cyan-900 dark:bg-cyan-950 dark:text-cyan-100',
    },
    uebungsfirmen: {
        head: 'bg-violet-600 text-white',
        soft: 'border-violet-200 bg-violet-50 dark:border-violet-900 dark:bg-violet-950/40',
        dot: 'bg-violet-500',
        col: 'bg-violet-100 text-violet-900 dark:bg-violet-950 dark:text-violet-100',
    },
};
const style = (group: string): GroupStyle => GROUP_STYLE[group] ?? GROUP_STYLE.system;

const KIND_LABEL: Record<Kind, string> = {
    all: 'Alle Rechte',
    base: 'Basisrolle – jeder Mitarbeiter',
    rights: 'Zusatzrolle',
    notify: 'Nur Benachrichtigungen',
};

// ─── Lookups ────────────────────────────────────────────────────────────
const roleByKey = computed<Record<string, RoleInfo>>(() => Object.fromEntries(props.roles.map((r) => [r.key, r])));
const superAdmin = computed(() => roleByKey.value['Super_Admin']);
const verwaltung = computed(() => roleByKey.value['Verwaltung']);

const groupList = computed(() =>
    Object.entries(props.groups)
        .map(([key, label]) => ({ key, label, roles: props.roles.filter((r) => r.group === key) }))
        .filter((g) => g.roles.length > 0),
);
/** add-on groups for the bottom row of the diagram */
const addonGroups = computed(() => groupList.value.filter((g) => g.key !== 'system' && g.key !== 'basis'));
/** matrix columns in diagram order */
const matrixRoles = computed(() => groupList.value.flatMap((g) => g.roles));

function memberLabel(r: RoleInfo): string {
    if (!r.exists) return 'fehlt in der Datenbank';
    if (r.count === 0) return 'niemand';

    return r.count === 1 ? '1 Mitglied' : `${r.count} Mitglieder`;
}

const has = (c: Capability, role: string): boolean => Object.prototype.hasOwnProperty.call(c.cells, role);

type CellState = 'yes' | 'limited' | 'base' | 'none';
function cell(c: Capability, role: string): { state: CellState; note: string | null } {
    if (has(c, role)) {
        const note = c.cells[role];

        return { state: note ? 'limited' : 'yes', note };
    }
    if (role !== 'Super_Admin' && role !== 'Verwaltung' && has(c, 'Verwaltung')) {
        return { state: 'base', note: null };
    }

    return { state: 'none', note: null };
}

function cellTitle(c: Capability, role: string): string {
    const { state, note } = cell(c, role);
    if (state === 'yes') return `${role}: ja`;
    if (state === 'limited') return `${role}: ${note}`;
    if (state === 'base') return `${role}: über Verwaltung (jeder Mitarbeiter)`;
    if (role === 'Super_Admin' && c.strict) return 'Super_Admin: nur, wenn ihm die Rolle wirklich zugewiesen ist';

    return `${role}: nein`;
}

/** limitations shown under the label: "handwerk: nur eigene Stadt"; same note = one line */
function notesOf(c: Capability): { role: string; note: string }[] {
    const byNote = new Map<string, string[]>();
    for (const [role, note] of Object.entries(c.cells)) {
        if (note) byNote.set(note, [...(byNote.get(note) ?? []), role]);
    }

    return [...byNote].map(([note, roles]) => ({ role: roles.join(', '), note }));
}

function assignHref(assign: string): string | null {
    if (assign.includes('Rollen & Berechtigungen › Handwerk')) return '/handwerk-zustaendigkeiten';
    if (assign.includes('Rollen & Berechtigungen › Korso')) return '/korso-zustaendigkeiten';
    if (assign.includes('Benutzer')) return '/users';

    return null;
}

// ─── Role details (sheet) ───────────────────────────────────────────────
const selectedKey = ref<string | null>(null);
const sheetOpen = ref(false);
const memberSearch = ref('');
const selected = computed<RoleInfo | null>(() => (selectedKey.value ? (roleByKey.value[selectedKey.value] ?? null) : null));

function openRole(key: string) {
    selectedKey.value = key;
    memberSearch.value = '';
    sheetOpen.value = true;
}

type AreaBlock = { key: string; label: string; items: Capability[] };
function byArea(list: Capability[]): AreaBlock[] {
    return Object.entries(props.areas)
        .map(([key, label]) => ({ key, label, items: list.filter((c) => c.area === key) }))
        .filter((a) => a.items.length > 0);
}

const detail = computed(() => {
    const r = selected.value;
    if (!r) return null;
    const direct = props.capabilities.filter((c) => !c.open && has(c, r.key));
    const isAddon = r.key !== 'Super_Admin' && r.key !== 'Verwaltung';

    return {
        rights: byArea(direct.filter((c) => c.area !== 'mail')),
        rightsCount: direct.filter((c) => c.area !== 'mail').length,
        mail: direct.filter((c) => c.area === 'mail'),
        viaBase: isAddon ? props.capabilities.filter((c) => !c.open && has(c, 'Verwaltung') && !has(c, r.key)) : [],
        open: props.capabilities.filter((c) => c.open),
    };
});

const filteredMembers = computed<Person[]>(() => {
    const list = selected.value?.members ?? [];
    const q = memberSearch.value.trim().toLowerCase();
    if (!q) return list;

    return list.filter((p) => `${p.name} ${p.username} ${p.ort ?? ''}`.toLowerCase().includes(q));
});

// ─── Matrix ─────────────────────────────────────────────────────────────
const search = ref('');
const area = ref<string>('all');
const highlighted = ref<string | null>(null);
const onlyHighlighted = ref(false);
const showSource = ref(false);

function matches(c: Capability, q: string): boolean {
    if (!q) return true;
    const text = [c.label, c.hint, c.others, c.source, ...notesOf(c).map((n) => `${n.role} ${n.note}`), props.areas[c.area]]
        .filter(Boolean)
        .join(' ')
        .toLowerCase();

    return text.includes(q);
}

const matrixAreas = computed<AreaBlock[]>(() => {
    const q = search.value.trim().toLowerCase();
    const role = onlyHighlighted.value ? highlighted.value : null;

    return byArea(
        props.capabilities.filter(
            (c) =>
                (area.value === 'all' || c.area === area.value) &&
                matches(c, q) &&
                (!role || c.open || cell(c, role).state !== 'none'),
        ),
    );
});
const matrixCount = computed(() => matrixAreas.value.reduce((n, a) => n + a.items.length, 0));

function toggleHighlight(key: string) {
    highlighted.value = highlighted.value === key ? null : key;
    if (!highlighted.value) onlyHighlighted.value = false;
}

function showInMatrix(key: string) {
    highlighted.value = key;
    sheetOpen.value = false;
    requestAnimationFrame(() => document.getElementById('matrix')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
}

const colClass = (key: string) => (highlighted.value === key ? 'bg-primary/10' : '');

// ─── Zu prüfen ──────────────────────────────────────────────────────────
type Issue = { level: 'danger' | 'warn' | 'info'; title: string; text: string; role?: string; people?: Person[] };
const issues = computed<Issue[]>(() => {
    const out: Issue[] = [];
    for (const r of props.roles) {
        // admin: open question since 2026-10-09 - nobody has it = remove from code
        if (r.key === 'admin' && (!r.exists || r.count === 0)) {
            out.push({
                level: 'info',
                role: r.key,
                title: r.exists ? 'Niemand hat „admin“ (IT-Admin)' : 'Rolle „admin“ (IT-Admin) gibt es nicht',
                text: 'Sie kann aus dem Code entfernt werden: IT-Admin = Super_Admin.',
            });
        } else if (!r.exists && r.queried) {
            out.push({
                level: 'danger',
                role: r.key,
                title: `Rolle „${r.key}“ fehlt in der Datenbank`,
                text: `Der Code lädt ihre Mitglieder (${r.queried}). Ohne die Rolle bricht das mit einem Fehler ab. Anlegen unter Rollen › + Neue Rolle, Name genau so.`,
            });
        } else if (!r.exists) {
            out.push({
                level: 'warn',
                role: r.key,
                title: `Rolle „${r.key}“ ist nicht angelegt`,
                text: 'Wird erst gebraucht, wenn jemand sie bekommen soll. Anlegen unter Rollen › + Neue Rolle, Name genau so.',
            });
        } else if (r.count === 0 && r.kind !== 'all') {
            out.push({
                level: r.kind === 'notify' ? 'warn' : 'info',
                role: r.key,
                title: `Niemand hat „${r.key}“`,
                text: r.kind === 'notify' ? 'Diese Benachrichtigungen gehen im Moment an niemanden.' : 'Die Rechte dieser Rolle nutzt im Moment niemand.',
            });
        }
    }
    for (const u of props.unknownRoles) {
        out.push({
            level: 'info',
            title: `„${u.key}“ wird im Code nicht geprüft`,
            text: `${u.count} Benutzer. Vermutlich eine Altlast der alten App: gibt keine Rechte. Nach Prüfung unter Rollen löschen.`,
            people: u.members,
        });
    }
    for (const c of props.capabilities.filter((x) => x.missingRoute)) {
        out.push({
            level: 'warn',
            title: `Beispiel-Route fehlt: ${c.label}`,
            text: `${c.source ?? ''} gibt es nicht mehr. Bitte in App\\Support\\RoleOverview anpassen.`,
        });
    }
    if (props.withoutVerwaltungCount > 0) {
        out.push({
            level: 'warn',
            title: `${props.withoutVerwaltungCount} Benutzer ohne Verwaltung`,
            text: 'Sie können keine Tickets erstellen. Meist Dienst- oder Testkonten; sonst unter Benutzer › Bearbeiten „Verwaltung“ geben.',
            people: props.withoutVerwaltung,
        });
    }
    const rank = { danger: 0, warn: 1, info: 2 } as const;

    return out.sort((a, b) => rank[a.level] - rank[b.level]);
});
const ISSUE_STYLE = {
    danger: 'border-l-red-600 bg-red-50 dark:bg-red-950/30',
    warn: 'border-l-amber-500 bg-amber-50 dark:bg-amber-950/30',
    info: 'border-l-sky-500 bg-sky-50 dark:bg-sky-950/30',
} as const;

onMounted(() => {
    const key = new URLSearchParams(window.location.search).get('rolle');
    if (key && roleByKey.value[key]) openRole(key);
});
</script>

<template>
    <Head title="Rollen-Übersicht" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Rollen-Übersicht</h1>
                <p class="text-muted-foreground text-sm">Wer darf was. Die Regeln aus config/route_access.php werden live gelesen, die Mitglieder kommen aus der Datenbank.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link href="/roles" class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm">Rollen</Link>
                <Link href="/users" class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm">Benutzer</Link>
                <Link href="/handwerk-zustaendigkeiten" class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm">Handwerk</Link>
                <Link href="/korso-zustaendigkeiten" class="border-border hover:bg-accent inline-flex h-9 items-center rounded-md border px-3 text-sm">Korso</Link>
            </div>
        </div>

        <!-- How roles work -->
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="bg-card flex gap-3 rounded-xl border p-3 text-sm shadow-sm">
                <Users class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                <p><b>Rollen addieren sich.</b> Jeder Mitarbeiter hat Verwaltung. Zusatzrollen geben Rechte dazu, sie nehmen nichts weg.</p>
            </div>
            <div class="bg-card flex gap-3 rounded-xl border p-3 text-sm shadow-sm">
                <Crown class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                <p><b>Super_Admin darf alles</b> und zählt als jede Rolle. Ausnahme: Benachrichtigungen und „Zuweisen“-Listen.</p>
            </div>
            <div class="bg-card flex gap-3 rounded-xl border p-3 text-sm shadow-sm">
                <Bell class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                <p><b>Benachrichtigungen</b> gehen nur an Benutzer, denen die Rolle wirklich zugewiesen ist.</p>
            </div>
            <div class="bg-card flex gap-3 rounded-xl border p-3 text-sm shadow-sm">
                <Info class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                <p>
                    <b>Keine Rollen:</b> der Ersteller sieht immer sein Ticket; „Sieht Standorte“ und „Zuweisbar“ sind Einstellungen pro Benutzer unter Rollen & Berechtigungen ›
                    Handwerk.
                </p>
            </div>
        </div>

        <!-- Diagram -->
        <section class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm">
            <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                <h2 class="font-semibold">Aufbau der Rollen</h2>
                <p class="text-muted-foreground text-xs">Rolle anklicken für alle Details · {{ activeUsers }} aktive Benutzer</p>
            </div>

            <div class="flex flex-col items-center">
                <!-- Super_Admin -->
                <button
                    v-if="superAdmin"
                    type="button"
                    class="flex w-full max-w-xl items-center gap-3 rounded-xl px-4 py-3 text-left shadow-sm transition hover:opacity-95"
                    :class="[style('system').head, selectedKey === 'Super_Admin' && sheetOpen ? 'ring-primary ring-2 ring-offset-2' : '']"
                    @click="openRole('Super_Admin')"
                >
                    <Crown class="h-6 w-6 shrink-0" />
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">Super_Admin</span>
                        <span class="block text-xs opacity-80">{{ superAdmin.summary }}</span>
                    </span>
                    <span class="shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-xs font-medium">{{ memberLabel(superAdmin) }}</span>
                </button>

                <div class="flex flex-col items-center" aria-hidden="true">
                    <div class="bg-border h-3 w-px" />
                    <span class="text-muted-foreground bg-background rounded-full border px-2 py-0.5 text-[11px]">hat automatisch alle Rechte darunter</span>
                    <div class="bg-border h-3 w-px" />
                    <ChevronDown class="text-muted-foreground -mt-1.5 h-3 w-3" />
                </div>

                <!-- Verwaltung -->
                <button
                    v-if="verwaltung"
                    type="button"
                    class="flex w-full max-w-xl items-center gap-3 rounded-xl px-4 py-3 text-left shadow-sm transition hover:opacity-95"
                    :class="[style('basis').head, selectedKey === 'Verwaltung' && sheetOpen ? 'ring-primary ring-2 ring-offset-2' : '']"
                    @click="openRole('Verwaltung')"
                >
                    <Users class="h-6 w-6 shrink-0" />
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">Verwaltung <span class="text-xs font-normal opacity-80">· Basis für jeden Mitarbeiter</span></span>
                        <span class="block text-xs opacity-90">{{ verwaltung.summary }}</span>
                    </span>
                    <span class="shrink-0 rounded-full bg-white/20 px-2 py-0.5 text-xs font-medium">{{ memberLabel(verwaltung) }}</span>
                </button>

                <div class="flex flex-col items-center" aria-hidden="true">
                    <div class="bg-border h-3 w-px" />
                    <span class="text-muted-foreground bg-background rounded-full border px-2 py-0.5 text-[11px]">+ Zusatzrollen geben weitere Rechte dazu</span>
                    <div class="bg-border h-3 w-px" />
                    <ChevronDown class="text-muted-foreground -mt-1.5 h-3 w-3" />
                </div>
            </div>

            <!-- Add-on groups -->
            <div class="relative mt-1">
                <div class="bg-border absolute top-0 right-[7%] left-[7%] hidden h-px 2xl:block" aria-hidden="true" />
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-7 2xl:pt-4">
                    <div
                        v-for="g in addonGroups"
                        :key="g.key"
                        class="relative flex flex-col overflow-visible rounded-xl border 2xl:before:absolute 2xl:before:-top-4 2xl:before:left-1/2 2xl:before:h-4 2xl:before:w-px 2xl:before:bg-border"
                        :class="style(g.key).soft"
                    >
                        <div class="flex items-center justify-between rounded-t-xl px-3 py-1.5 text-sm font-semibold" :class="style(g.key).head">
                            <span>{{ g.label }}</span>
                            <span class="text-xs font-normal opacity-80">{{ g.roles.length }} {{ g.roles.length === 1 ? 'Rolle' : 'Rollen' }}</span>
                        </div>
                        <div class="flex flex-1 flex-col gap-2 p-2">
                            <button
                                v-for="r in g.roles"
                                :key="r.key"
                                type="button"
                                class="bg-background flex w-full flex-col gap-0.5 rounded-lg border px-2.5 py-2 text-left transition hover:shadow-md"
                                :class="[
                                    selectedKey === r.key && sheetOpen ? 'ring-primary ring-2' : '',
                                    !r.exists ? 'border-dashed border-red-400 dark:border-red-700' : '',
                                ]"
                                @click="openRole(r.key)"
                            >
                                <span class="flex items-center gap-1.5">
                                    <span class="h-2 w-2 shrink-0 rounded-full" :class="style(r.group).dot" />
                                    <span class="text-sm font-medium [overflow-wrap:anywhere]">{{ r.key }}</span>
                                    <Bell v-if="r.kind === 'notify'" class="text-muted-foreground h-3 w-3 shrink-0" />
                                </span>
                                <span class="text-muted-foreground line-clamp-3 text-xs leading-snug">{{ r.summary }}</span>
                                <span
                                    class="mt-0.5 text-[11px] font-medium"
                                    :class="!r.exists ? 'text-red-600 dark:text-red-400' : r.count === 0 ? 'text-muted-foreground' : 'text-primary'"
                                >
                                    {{ memberLabel(r) }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-muted-foreground mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                <span class="inline-flex items-center gap-1"><Bell class="h-3 w-3" /> nur Benachrichtigungen, keine Rechte</span>
                <span class="inline-flex items-center gap-1"><span class="h-3 w-5 rounded border border-dashed border-red-400" /> Rolle fehlt in der Datenbank</span>
            </div>
        </section>

        <!-- Zu prüfen -->
        <section v-if="issues.length" class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="border-b px-4 py-3">
                <h2 class="font-semibold">Zu prüfen ({{ issues.length }})</h2>
                <p class="text-muted-foreground text-xs">Aus der Datenbank und dem Code berechnet. Verschwindet von selbst, wenn es erledigt ist.</p>
            </div>
            <ul class="divide-y">
                <li v-for="(issue, i) in issues" :key="i" class="border-l-4 px-4 py-2.5" :class="ISSUE_STYLE[issue.level]">
                    <div class="flex items-start gap-2">
                        <CircleAlert v-if="issue.level === 'danger'" class="mt-0.5 h-4 w-4 shrink-0 text-red-600" />
                        <TriangleAlert v-else-if="issue.level === 'warn'" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                        <Info v-else class="mt-0.5 h-4 w-4 shrink-0 text-sky-600" />
                        <div class="min-w-0 flex-1 text-sm">
                            <div class="font-medium">
                                {{ issue.title }}
                                <button v-if="issue.role" type="button" class="text-primary ml-1 text-xs font-normal hover:underline" @click="openRole(issue.role)">Details</button>
                            </div>
                            <div class="text-muted-foreground text-xs">{{ issue.text }}</div>
                            <details v-if="issue.people?.length" class="mt-1 text-xs">
                                <summary class="text-primary cursor-pointer select-none">{{ issue.people.length }} Benutzer anzeigen</summary>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <Link
                                        v-for="p in issue.people"
                                        :key="p.id"
                                        :href="`/users/${p.id}/edit`"
                                        class="bg-background hover:bg-accent rounded border px-1.5 py-0.5"
                                        :title="p.username"
                                    >
                                        {{ p.name }}<span v-if="p.ort" class="text-muted-foreground"> · {{ p.ort }}</span>
                                    </Link>
                                </div>
                            </details>
                        </div>
                    </div>
                </li>
            </ul>
        </section>

        <!-- Matrix -->
        <section id="matrix" class="bg-card text-card-foreground scroll-mt-4 rounded-xl border shadow-sm">
            <div class="flex flex-col gap-3 border-b p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-semibold">Wer darf was ({{ matrixCount }})</h2>
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <label v-if="highlighted" class="inline-flex items-center gap-1.5">
                            <input v-model="onlyHighlighted" type="checkbox" class="h-4 w-4" />
                            nur was {{ highlighted }} darf
                        </label>
                        <label class="inline-flex items-center gap-1.5">
                            <input v-model="showSource" type="checkbox" class="h-4 w-4" />
                            <Code class="h-3.5 w-3.5" /> Quelle anzeigen
                        </label>
                        <div class="relative">
                            <Search class="text-muted-foreground absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2" />
                            <input
                                v-model="search"
                                type="search"
                                placeholder="Recht suchen …"
                                class="border-input bg-background h-9 w-56 rounded-md border pr-3 pl-8 text-sm"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <button
                        type="button"
                        class="h-7 rounded-full border px-3 text-xs"
                        :class="area === 'all' ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent'"
                        @click="area = 'all'"
                    >
                        Alle Bereiche
                    </button>
                    <button
                        v-for="(label, key) in areas"
                        :key="key"
                        type="button"
                        class="h-7 rounded-full border px-3 text-xs"
                        :class="area === key ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent'"
                        @click="area = String(key)"
                    >
                        {{ label }}
                    </button>
                </div>

                <div class="text-muted-foreground flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-white"><Check class="h-2.5 w-2.5" :stroke-width="3" /></span>
                        darf
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-white"><Check class="h-2.5 w-2.5" :stroke-width="3" /></span>
                        eingeschränkt (steht unter dem Recht)
                    </span>
                    <span class="inline-flex items-center gap-1"><Check class="h-3.5 w-3.5 opacity-40" /> über Verwaltung (jeder Mitarbeiter)</span>
                    <span class="inline-flex items-center gap-1"><span class="opacity-40">·</span> nein</span>
                    <span>Rollenname anklicken = Spalte markieren</span>
                    <button v-if="highlighted" type="button" class="text-primary inline-flex items-center gap-0.5 hover:underline" @click="toggleHighlight(highlighted)">
                        <X class="h-3 w-3" /> Markierung {{ highlighted }} aufheben
                    </button>
                </div>
            </div>

            <div class="max-h-[78vh] overflow-auto">
                <table class="border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr>
                            <th
                                rowspan="2"
                                class="bg-card sticky top-0 left-0 z-30 border-r border-b px-3 py-2 text-left align-bottom text-xs font-medium"
                            >
                                Recht
                            </th>
                            <th
                                v-for="g in groupList"
                                :key="g.key"
                                :colspan="g.roles.length"
                                class="sticky top-0 z-20 h-7 border-b border-l px-0 text-center text-[11px] font-semibold"
                                :class="style(g.key).col"
                                :title="g.label"
                            >
                                <!-- absolute: a long label must not widen a one-role group -->
                                <span class="relative block h-7"><span v-if="g.roles.length > 1 || g.label.length <= 3" class="absolute inset-x-1 top-1/2 -translate-y-1/2 truncate">{{ g.label }}</span></span>
                            </th>
                        </tr>
                        <tr>
                            <th
                                v-for="r in matrixRoles"
                                :key="r.key"
                                class="bg-card sticky top-7 z-20 h-36 w-10 min-w-10 border-b border-l px-0 py-2 align-bottom"
                            >
                                <span v-if="highlighted === r.key" class="bg-primary/10 pointer-events-none absolute inset-0" />
                                <button
                                    type="button"
                                    class="hover:text-primary mx-auto flex rotate-180 items-center gap-1 text-xs font-medium whitespace-nowrap [writing-mode:vertical-rl]"
                                    :class="highlighted === r.key ? 'text-primary' : ''"
                                    :title="`${r.key}: ${r.summary}`"
                                    @click="toggleHighlight(r.key)"
                                >
                                    {{ r.key }}
                                    <Bell v-if="r.kind === 'notify'" class="h-3 w-3" />
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="a in matrixAreas" :key="a.key">
                            <tr>
                                <td :colspan="matrixRoles.length + 1" class="bg-muted/70 border-b px-0 py-1.5">
                                    <span class="sticky left-3 inline-flex items-center gap-2 px-3 text-xs font-semibold tracking-wide uppercase">
                                        {{ a.label }}
                                        <span class="text-muted-foreground font-normal normal-case">
                                            {{ a.items.length }}<template v-if="a.key === 'mail'"> · gehen nur an Benutzer, denen die Rolle wirklich zugewiesen ist</template>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr v-for="c in a.items" :key="c.id" class="group">
                                <td class="bg-card group-hover:bg-accent sticky left-0 z-10 w-[15rem] min-w-[15rem] border-r border-b px-3 py-1.5 align-top sm:w-[24rem] sm:min-w-[24rem] lg:w-[32rem] lg:min-w-[32rem]">
                                    <div class="leading-snug">{{ c.label }}</div>
                                    <div v-if="c.hint" class="text-muted-foreground text-xs">{{ c.hint }}</div>
                                    <div v-for="n in notesOf(c)" :key="n.role" class="text-xs text-amber-700 dark:text-amber-400">{{ n.role }}: {{ n.note }}</div>
                                    <div v-if="c.others" class="text-xs text-sky-700 dark:text-sky-400">+ {{ c.others }}</div>
                                    <div v-if="(c.strict && c.area !== 'mail') || c.missingRoute" class="mt-0.5 flex flex-wrap gap-1">
                                        <span
                                            v-if="c.strict && c.area !== 'mail'"
                                            class="bg-muted text-muted-foreground rounded px-1.5 text-[10px]"
                                            title="Super_Admin zählt hier nicht automatisch"
                                        >
                                            nur zugewiesene Rolle
                                        </span>
                                        <span v-if="c.missingRoute" class="rounded bg-red-100 px-1.5 text-[10px] text-red-700 dark:bg-red-950 dark:text-red-300">Route fehlt</span>
                                    </div>
                                    <div v-if="showSource && c.source" class="text-muted-foreground font-mono text-[11px]">{{ c.source }}</div>
                                </td>
                                <td
                                    v-if="c.open"
                                    :colspan="matrixRoles.length"
                                    class="group-hover:bg-accent/40 text-muted-foreground border-b border-l px-3 py-1.5 text-center text-xs"
                                >
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-emerald-600 text-white"><Check class="h-2.5 w-2.5" :stroke-width="3" /></span>
                                        alle angemeldeten Benutzer
                                    </span>
                                </td>
                                <template v-else>
                                    <td
                                        v-for="r in matrixRoles"
                                        :key="r.key"
                                        class="group-hover:bg-accent/40 border-b border-l px-0 py-1.5 text-center align-middle"
                                        :class="colClass(r.key)"
                                        :title="cellTitle(c, r.key)"
                                    >
                                        <span
                                            v-if="cell(c, r.key).state === 'yes'"
                                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white"
                                        >
                                            <Check class="h-3 w-3" :stroke-width="3" />
                                        </span>
                                        <span
                                            v-else-if="cell(c, r.key).state === 'limited'"
                                            class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-white"
                                        >
                                            <Check class="h-3 w-3" :stroke-width="3" />
                                        </span>
                                        <Check v-else-if="cell(c, r.key).state === 'base'" class="text-muted-foreground mx-auto h-4 w-4 opacity-40" />
                                        <span v-else class="text-muted-foreground opacity-40">·</span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                        <tr v-if="matrixCount === 0">
                            <td :colspan="matrixRoles.length + 1" class="text-muted-foreground px-4 py-8 text-center text-sm">Kein Recht gefunden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Role details -->
    <Sheet :open="sheetOpen" @update:open="(v: boolean) => (sheetOpen = v)">
        <SheetContent side="right" class="w-full gap-0 overflow-y-auto sm:max-w-2xl">
            <template v-if="selected && detail">
                <SheetHeader class="border-b">
                    <div class="flex items-center gap-2 pr-6">
                        <span class="h-3 w-3 shrink-0 rounded-full" :class="style(selected.group).dot" />
                        <SheetTitle class="text-lg">{{ selected.key }}</SheetTitle>
                        <span class="rounded-full px-2 py-0.5 text-[11px] font-medium" :class="style(selected.group).col">{{ groups[selected.group] }}</span>
                        <span class="bg-muted text-muted-foreground rounded-full px-2 py-0.5 text-[11px]">{{ KIND_LABEL[selected.kind] }}</span>
                    </div>
                    <SheetDescription class="text-foreground text-sm">{{ selected.summary }}</SheetDescription>
                </SheetHeader>

                <div class="flex flex-col gap-5 p-4">
                    <!-- warnings -->
                    <div
                        v-if="!selected.exists"
                        class="flex gap-2 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"
                    >
                        <CircleAlert class="mt-0.5 h-4 w-4 shrink-0" />
                        <div>
                            <b>Fehlt in der Datenbank.</b>
                            <template v-if="selected.queried"> Der Code lädt ihre Mitglieder ({{ selected.queried }}); ohne die Rolle bricht das ab.</template>
                            Anlegen unter Rollen › + Neue Rolle, Name genau „{{ selected.key }}“.
                        </div>
                    </div>
                    <div
                        v-else-if="selected.count === 0 && selected.kind !== 'all'"
                        class="flex gap-2 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200"
                    >
                        <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
                        <div>Niemand hat diese Rolle.</div>
                    </div>

                    <!-- facts -->
                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div class="rounded-lg border p-3">
                            <dt class="text-muted-foreground text-xs">Wird vergeben unter</dt>
                            <dd class="mt-0.5">
                                <Link v-if="assignHref(selected.assign)" :href="assignHref(selected.assign)!" class="text-primary inline-flex items-center gap-1 hover:underline">
                                    {{ selected.assign }} <ExternalLink class="h-3 w-3" />
                                </Link>
                                <span v-else>{{ selected.assign }}</span>
                            </dd>
                        </div>
                        <div class="rounded-lg border p-3">
                            <dt class="text-muted-foreground text-xs">Zusätzlich im Menü</dt>
                            <dd class="mt-1 flex flex-wrap gap-1">
                                <span v-for="m in selected.menu" :key="m" class="bg-muted rounded px-1.5 py-0.5 text-xs">{{ m }}</span>
                                <span v-if="!selected.menu.length" class="text-muted-foreground">nichts</span>
                            </dd>
                        </div>
                    </dl>

                    <div v-if="selected.notes.length">
                        <h3 class="mb-1.5 text-sm font-semibold">Wichtig</h3>
                        <ul class="list-disc space-y-1 pl-5 text-sm">
                            <li v-for="n in selected.notes" :key="n">{{ n }}</li>
                        </ul>
                    </div>

                    <!-- rights -->
                    <div>
                        <h3 class="mb-1.5 text-sm font-semibold">
                            {{ selected.kind === 'base' ? 'Darf' : selected.kind === 'all' ? 'Darf (alles)' : 'Darf zusätzlich' }}
                            <span class="text-muted-foreground font-normal">({{ detail.rightsCount }})</span>
                        </h3>
                        <p v-if="!detail.rightsCount" class="text-muted-foreground text-sm">Keine zusätzlichen Rechte.</p>
                        <div v-for="block in detail.rights" :key="block.key" class="mb-3">
                            <div class="text-muted-foreground mb-1 text-[11px] font-semibold tracking-wide uppercase">{{ block.label }}</div>
                            <ul class="space-y-1">
                                <li v-for="c in block.items" :key="c.id" class="flex gap-2 text-sm">
                                    <span
                                        class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full text-white"
                                        :class="c.cells[selected.key] ? 'bg-amber-500' : 'bg-emerald-600'"
                                    >
                                        <Check class="h-2.5 w-2.5" :stroke-width="3" />
                                    </span>
                                    <span class="min-w-0">
                                        {{ c.label }}
                                        <span v-if="c.cells[selected.key]" class="text-amber-700 dark:text-amber-400"> – {{ c.cells[selected.key] }}</span>
                                        <span v-if="c.hint" class="text-muted-foreground block text-xs">{{ c.hint }}</span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- notifications -->
                    <div v-if="detail.mail.length">
                        <h3 class="mb-1.5 flex items-center gap-1.5 text-sm font-semibold"><Bell class="h-4 w-4" /> Bekommt Benachrichtigungen / steht in „Zuweisen“</h3>
                        <ul class="space-y-1">
                            <li v-for="c in detail.mail" :key="c.id" class="flex gap-2 text-sm">
                                <Bell class="text-muted-foreground mt-0.5 h-4 w-4 shrink-0" />
                                <span>
                                    {{ c.label }}
                                    <span v-if="c.hint" class="text-muted-foreground block text-xs">{{ c.hint }}</span>
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- base rights -->
                    <details v-if="detail.viaBase.length" class="rounded-lg border p-3 text-sm">
                        <summary class="cursor-pointer select-none">
                            <b>Dazu über Verwaltung</b>
                            <span class="text-muted-foreground"> ({{ detail.viaBase.length }}, wenn das Mitglied auch Verwaltung hat – normalerweise jeder)</span>
                        </summary>
                        <ul class="mt-2 space-y-0.5">
                            <li v-for="c in detail.viaBase" :key="c.id" class="flex gap-2">
                                <Check class="text-muted-foreground mt-0.5 h-4 w-4 shrink-0 opacity-60" />{{ c.label }}
                            </li>
                        </ul>
                    </details>
                    <details class="rounded-lg border p-3 text-sm">
                        <summary class="cursor-pointer select-none">
                            <b>Dazu für alle angemeldeten Benutzer</b> <span class="text-muted-foreground">({{ detail.open.length }})</span>
                        </summary>
                        <ul class="mt-2 space-y-0.5">
                            <li v-for="c in detail.open" :key="c.id" class="flex gap-2">
                                <Check class="text-muted-foreground mt-0.5 h-4 w-4 shrink-0 opacity-60" />{{ c.label }}
                            </li>
                        </ul>
                    </details>

                    <!-- members -->
                    <div>
                        <div class="mb-1.5 flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold">Mitglieder <span class="text-muted-foreground font-normal">({{ selected.count }})</span></h3>
                            <input
                                v-if="selected.members.length > 8"
                                v-model="memberSearch"
                                type="search"
                                placeholder="Mitglied suchen …"
                                class="border-input bg-background h-8 w-48 rounded-md border px-2.5 text-sm"
                            />
                        </div>
                        <p v-if="selected.key === 'Verwaltung'" class="text-muted-foreground text-sm">
                            {{ selected.count }} von {{ activeUsers }} aktiven Benutzern.
                            <template v-if="withoutVerwaltungCount">{{ withoutVerwaltungCount }} ohne Verwaltung, siehe „Zu prüfen“.</template>
                        </p>
                        <p v-else-if="!selected.members.length" class="text-muted-foreground text-sm">Niemand.</p>
                        <ul v-else class="divide-y rounded-lg border">
                            <li v-for="p in filteredMembers" :key="p.id" class="flex items-center justify-between gap-2 px-3 py-1.5 text-sm">
                                <span class="min-w-0">
                                    <span class="font-medium">{{ p.name }}</span>
                                    <span class="text-muted-foreground"> · {{ p.username }}</span>
                                    <span v-if="p.ort" class="text-muted-foreground"> · {{ p.ort }}</span>
                                </span>
                                <Link :href="`/users/${p.id}/edit`" class="text-primary shrink-0 text-xs hover:underline">bearbeiten</Link>
                            </li>
                            <li v-if="!filteredMembers.length" class="text-muted-foreground px-3 py-2 text-sm">Kein Treffer.</li>
                        </ul>
                    </div>

                    <button
                        type="button"
                        class="border-border hover:bg-accent inline-flex h-9 items-center justify-center gap-1.5 self-start rounded-md border px-3 text-sm"
                        @click="showInMatrix(selected.key)"
                    >
                        Spalte in der Tabelle markieren
                    </button>
                </div>
            </template>
        </SheetContent>
    </Sheet>
</template>
