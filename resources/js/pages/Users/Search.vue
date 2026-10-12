<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, Copy, Download, Mail, Phone, RotateCcw, Search, X } from '@lucide/vue';
import { computed, h, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import { copyText } from '@/lib/clipboard';
import type { BreadcrumbItem } from '@/types';

/**
 * Benutzer-Suche (UserSearchController, Super_Admin only, 2026-10-12).
 *
 * Find users by Bundesland, Stadt, Adresse, Abteilung, Position and Rolle
 * (several values per filter = "or", different filters = "and") plus free
 * text. Every filter shows how many users each value has under the OTHER
 * filters - e.g. Bundesland "Sachsen" and then the Abteilungen in Sachsen
 * with their numbers. "Gruppieren nach" counts the result per Abteilung /
 * Stadt / … Filters are kept in the address (?bl=Sachsen&abt=…), so a view
 * can be bookmarked. Results: copy e-mails, CSV for Excel.
 */

type UserRow = {
    id: number;
    name: string;
    nachname: string;
    vorname: string;
    username: string;
    email: string;
    tel: string;
    mobil: string;
    ort: string;
    strasse: string;
    plz: string;
    bundesland: string;
    abteilung: string;
    position: string;
    office: string;
    lastlogin: string | null;
    roles: string[];
    removed: boolean;
};

const props = defineProps<{ users: UserRow[] }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Rollen & Berechtigungen', href: '/roles' },
            { title: 'Benutzer-Suche', href: '/benutzer-suche' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

// ─── Filters ────────────────────────────────────────────────────────────
type FacetKey = 'bundesland' | 'ort' | 'strasse' | 'abteilung' | 'position' | 'rolle';
type Facet = { key: FacetKey; label: string; param: string; values: (u: UserRow) => string[] };

const FACETS: Facet[] = [
    { key: 'bundesland', label: 'Bundesland', param: 'bl', values: (u) => [u.bundesland] },
    { key: 'ort', label: 'Stadt', param: 'ort', values: (u) => [u.ort] },
    { key: 'strasse', label: 'Adresse', param: 'adr', values: (u) => [u.strasse] },
    { key: 'abteilung', label: 'Abteilung', param: 'abt', values: (u) => [u.abteilung] },
    { key: 'position', label: 'Position', param: 'pos', values: (u) => [u.position] },
    { key: 'rolle', label: 'Rolle', param: 'rolle', values: (u) => (u.roles.length ? u.roles : ['']) },
];
const EMPTY = '(leer)';
const label = (v: string) => (v === '' ? EMPTY : v);

type Status = 'active' | 'removed' | 'all';
const search = ref('');
const status = ref<Status>('active');
const selected = reactive<Record<FacetKey, string[]>>({ bundesland: [], ort: [], strasse: [], abteilung: [], position: [], rolle: [] });
const open = reactive<Record<FacetKey, boolean>>({ bundesland: true, ort: true, strasse: false, abteilung: true, position: false, rolle: false });
const showAll = reactive<Record<FacetKey, boolean>>({ bundesland: false, ort: false, strasse: false, abteilung: false, position: false, rolle: false });
const facetSearch = reactive<Record<FacetKey, string>>({ bundesland: '', ort: '', strasse: '', abteilung: '', position: '', rolle: '' });

const norm = (s: string) => s.toLocaleLowerCase('de').normalize('NFD').replace(/[̀-ͯ]/g, '');

const haystack = new Map<number, string>(
    props.users.map((u) => [
        u.id,
        norm([u.name, u.username, u.email, u.tel, u.mobil, u.position, u.abteilung, u.office, u.ort, u.strasse, u.plz].join(' ')),
    ]),
);

/** status + free text (applies to everything, also to the counts) */
const base = computed(() => {
    const words = norm(search.value).split(/\s+/).filter(Boolean);

    return props.users.filter((u) => {
        if (status.value === 'active' && u.removed) return false;
        if (status.value === 'removed' && !u.removed) return false;
        if (!words.length) return true;
        const text = haystack.get(u.id) ?? '';

        return words.every((w) => text.includes(w));
    });
});

function passes(u: UserRow, facet: Facet): boolean {
    const sel = selected[facet.key];

    return sel.length === 0 || facet.values(u).some((v) => sel.includes(v));
}

const filtered = computed(() => base.value.filter((u) => FACETS.every((f) => passes(u, f))));

/** per filter: value -> count under all OTHER filters (classic faceted search) */
const facetOptions = computed(() => {
    const out = {} as Record<FacetKey, { value: string; count: number }[]>;
    for (const facet of FACETS) {
        const counts = new Map<string, number>();
        for (const u of base.value) {
            if (!FACETS.every((g) => g.key === facet.key || passes(u, g))) continue;
            for (const v of new Set(facet.values(u))) counts.set(v, (counts.get(v) ?? 0) + 1);
        }
        // selected values stay visible even with 0 hits
        for (const v of selected[facet.key]) if (!counts.has(v)) counts.set(v, 0);
        out[facet.key] = [...counts.entries()]
            .map(([value, count]) => ({ value, count }))
            .sort((a, b) => b.count - a.count || label(a.value).localeCompare(label(b.value), 'de'));
    }

    return out;
});

const FACET_LIMIT = 8;
function visibleOptions(key: FacetKey) {
    const q = norm(facetSearch[key]);
    let list = facetOptions.value[key];
    if (q) list = list.filter((o) => norm(label(o.value)).includes(q));
    if (showAll[key] || q) return list;
    const top = list.slice(0, FACET_LIMIT);
    // keep selected values visible
    for (const o of list.slice(FACET_LIMIT)) if (selected[key].includes(o.value)) top.push(o);

    return top;
}

function toggle(key: FacetKey, value: string) {
    const i = selected[key].indexOf(value);
    if (i === -1) selected[key].push(value);
    else selected[key].splice(i, 1);
}

const activeChips = computed(() =>
    FACETS.flatMap((f) => selected[f.key].map((value) => ({ key: f.key, facet: f.label, value }))),
);
const hasFilters = computed(() => activeChips.value.length > 0 || search.value.trim() !== '' || status.value !== 'active');

function resetAll() {
    for (const f of FACETS) selected[f.key] = [];
    search.value = '';
    status.value = 'active';
}

// ─── Grouping ───────────────────────────────────────────────────────────
const groupBy = ref<FacetKey | ''>('');
const groups = computed(() => {
    if (!groupBy.value) return [];
    const facet = FACETS.find((f) => f.key === groupBy.value)!;
    const counts = new Map<string, number>();
    for (const u of filtered.value) for (const v of new Set(facet.values(u))) counts.set(v, (counts.get(v) ?? 0) + 1);

    return [...counts.entries()]
        .map(([value, count]) => ({ value, count }))
        .sort((a, b) => b.count - a.count || label(a.value).localeCompare(label(b.value), 'de'));
});
const groupMax = computed(() => Math.max(1, ...groups.value.map((g) => g.count)));

// ─── Table ──────────────────────────────────────────────────────────────
const COLUMNS: DataTableColumn<UserRow>[] = [
    { key: 'nachname', value: (u) => `${u.nachname} ${u.vorname}` },
    { key: 'abteilung' },
    { key: 'position' },
    { key: 'ort' },
    { key: 'strasse' },
    { key: 'bundesland' },
    { key: 'tel' },
    { key: 'email' },
    { key: 'roles', value: (u) => u.roles.join(', ') },
];
const { sortKey, sortDir, toggleSort, pageSize, page, pagedRows, total, pageCount, rangeFrom, rangeTo } = useDataTable(filtered, COLUMNS, {
    pageSize: 25,
});
watch(filtered, () => (page.value = 1));

// ─── Export ─────────────────────────────────────────────────────────────
const emails = computed(() => [...new Set(filtered.value.map((u) => u.email).filter(Boolean))]);

async function copyEmails() {
    if (!emails.value.length) return;
    // copyText: navigator.clipboard alone fails on the http intranet
    if (await copyText(emails.value.join('; '))) toast.success(`${emails.value.length} E-Mail-Adressen kopiert.`);
    else toast.error('Kopieren nicht möglich.');
}

function exportCsv() {
    const head = ['Nachname', 'Vorname', 'Benutzername', 'Abteilung', 'Position', 'Bundesland', 'Stadt', 'Adresse', 'PLZ', 'Büro', 'Telefon', 'Mobil', 'E-Mail', 'Rollen', 'Status'];
    const cell = (v: string) => `"${(v ?? '').replace(/"/g, '""')}"`;
    const rows = filtered.value.map((u) =>
        [u.nachname, u.vorname, u.username, u.abteilung, u.position, u.bundesland, u.ort, u.strasse, u.plz, u.office, u.tel, u.mobil, u.email, u.roles.join(', '), u.removed ? 'ausgeschieden' : 'aktiv']
            .map(cell)
            .join(';'),
    );
    const blob = new Blob(['﻿' + [head.map(cell).join(';'), ...rows].join('\r\n')], { type: 'text/csv;charset=utf-8' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `benutzer_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(a.href);
}

// ─── Filters in the address bar ─────────────────────────────────────────
onMounted(() => {
    const q = new URLSearchParams(window.location.search);
    for (const f of FACETS) {
        const v = q.get(f.param);
        if (v !== null) selected[f.key] = v.split('|').map((s) => (s === EMPTY ? '' : s));
    }
    search.value = q.get('q') ?? '';
    const s = q.get('status');
    if (s === 'removed' || s === 'all') status.value = s;
});
watch(
    [search, status, () => FACETS.map((f) => selected[f.key].join('|'))],
    () => {
        const q = new URLSearchParams();
        if (search.value.trim()) q.set('q', search.value.trim());
        if (status.value !== 'active') q.set('status', status.value);
        for (const f of FACETS) if (selected[f.key].length) q.set(f.param, selected[f.key].map(label).join('|'));
        const qs = q.toString();
        window.history.replaceState(window.history.state, '', window.location.pathname + (qs ? `?${qs}` : ''));
    },
    { deep: true },
);

/** "Muster, Erika"; users without names (service accounts) show their name / username */
function displayName(u: UserRow): string {
    return u.nachname && u.vorname ? `${u.nachname}, ${u.vorname}` : u.name;
}

function formatDate(v: string | null): string {
    if (!v) return '';
    const d = new Date(v);

    return isNaN(d.getTime()) ? v : d.toLocaleDateString('de-DE');
}
</script>

<template>
    <Head title="Benutzer-Suche" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Benutzer-Suche</h1>
                <p class="text-muted-foreground text-sm">
                    <b class="text-foreground">{{ filtered.length }}</b> von {{ base.length }} Benutzern
                    <template v-if="status === 'active'"> (aktiv)</template>
                    <template v-else-if="status === 'removed'"> (ausgeschieden)</template>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="border-border hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm disabled:opacity-50"
                    :disabled="!emails.length"
                    @click="copyEmails"
                >
                    <Copy class="h-4 w-4" /> E-Mails kopieren ({{ emails.length }})
                </button>
                <button
                    type="button"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm disabled:opacity-50"
                    :disabled="!filtered.length"
                    @click="exportCsv"
                >
                    <Download class="h-4 w-4" /> CSV (Excel)
                </button>
            </div>
        </div>

        <div class="grid items-start gap-4 lg:grid-cols-[17rem_minmax(0,1fr)]">
            <!-- Filters -->
            <aside class="bg-card text-card-foreground flex flex-col gap-3 rounded-xl border p-3 shadow-sm lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
                <div class="relative">
                    <Search class="text-muted-foreground absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Name, E-Mail, Telefon …"
                        class="border-input bg-background h-9 w-full rounded-md border pr-3 pl-8 text-sm"
                    />
                </div>

                <div class="bg-muted grid grid-cols-3 gap-1 rounded-md p-1 text-xs">
                    <button
                        v-for="s in [
                            { v: 'active', l: 'Aktiv' },
                            { v: 'removed', l: 'Ausgeschieden' },
                            { v: 'all', l: 'Alle' },
                        ] as const"
                        :key="s.v"
                        type="button"
                        class="rounded px-1 py-1"
                        :class="status === s.v ? 'bg-background font-medium shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        @click="status = s.v"
                    >
                        {{ s.l }}
                    </button>
                </div>

                <div v-for="f in FACETS" :key="f.key" class="border-t pt-2">
                    <button type="button" class="flex w-full items-center justify-between py-1 text-sm font-semibold" @click="open[f.key] = !open[f.key]">
                        <span>
                            {{ f.label }}
                            <span v-if="selected[f.key].length" class="bg-primary text-primary-foreground ml-1 rounded-full px-1.5 text-[11px] font-medium">
                                {{ selected[f.key].length }}
                            </span>
                        </span>
                        <ChevronDown class="text-muted-foreground h-4 w-4 transition-transform" :class="open[f.key] ? 'rotate-180' : ''" />
                    </button>
                    <div v-if="open[f.key]" class="mt-1 flex flex-col gap-0.5">
                        <input
                            v-if="facetOptions[f.key].length > 12"
                            v-model="facetSearch[f.key]"
                            type="search"
                            :placeholder="`${f.label} suchen …`"
                            class="border-input bg-background mb-1 h-8 w-full rounded-md border px-2 text-xs"
                        />
                        <label
                            v-for="o in visibleOptions(f.key)"
                            :key="o.value"
                            class="hover:bg-accent flex cursor-pointer items-center gap-2 rounded px-1 py-0.5 text-sm"
                            :class="o.count === 0 && !selected[f.key].includes(o.value) ? 'opacity-50' : ''"
                        >
                            <input type="checkbox" class="h-3.5 w-3.5" :checked="selected[f.key].includes(o.value)" @change="toggle(f.key, o.value)" />
                            <span class="min-w-0 flex-1 truncate" :class="o.value === '' ? 'text-muted-foreground italic' : ''" :title="label(o.value)">{{
                                label(o.value)
                            }}</span>
                            <span class="text-muted-foreground text-xs tabular-nums">{{ o.count }}</span>
                        </label>
                        <button
                            v-if="!facetSearch[f.key] && facetOptions[f.key].length > FACET_LIMIT"
                            type="button"
                            class="text-primary mt-0.5 self-start text-xs hover:underline"
                            @click="showAll[f.key] = !showAll[f.key]"
                        >
                            {{ showAll[f.key] ? 'weniger anzeigen' : `alle ${facetOptions[f.key].length} anzeigen` }}
                        </button>
                        <p v-if="!facetOptions[f.key].length" class="text-muted-foreground text-xs">keine Werte</p>
                    </div>
                </div>

                <button
                    v-if="hasFilters"
                    type="button"
                    class="border-border hover:bg-accent mt-1 inline-flex h-8 items-center justify-center gap-1.5 rounded-md border text-sm"
                    @click="resetAll"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Filter zurücksetzen
                </button>
            </aside>

            <!-- Results -->
            <div class="flex min-w-0 flex-col gap-3">
                <div v-if="activeChips.length" class="flex flex-wrap gap-1.5">
                    <button
                        v-for="c in activeChips"
                        :key="c.key + c.value"
                        type="button"
                        class="bg-primary/10 text-primary hover:bg-primary/20 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs"
                        @click="toggle(c.key, c.value)"
                    >
                        <span class="opacity-70">{{ c.facet }}:</span> {{ label(c.value) }} <X class="h-3 w-3" />
                    </button>
                </div>

                <!-- Grouping -->
                <section class="bg-card text-card-foreground rounded-xl border shadow-sm">
                    <div class="flex flex-wrap items-center gap-2 p-3 text-sm">
                        <span class="font-medium">Gruppieren nach</span>
                        <button
                            v-for="g in [{ key: '', label: 'keine' } as const, ...FACETS]"
                            :key="g.key"
                            type="button"
                            class="h-7 rounded-full border px-3 text-xs"
                            :class="groupBy === g.key ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-accent'"
                            @click="groupBy = g.key"
                        >
                            {{ g.label }}
                        </button>
                    </div>
                    <div v-if="groupBy && groups.length" class="grid gap-x-6 gap-y-1 border-t p-3 sm:grid-cols-2 xl:grid-cols-3">
                        <button
                            v-for="g in groups"
                            :key="g.value"
                            type="button"
                            class="hover:bg-accent flex items-center gap-2 rounded px-1 py-0.5 text-left text-sm"
                            :title="`Nur ${label(g.value)} anzeigen`"
                            @click="!selected[groupBy].includes(g.value) && toggle(groupBy, g.value)"
                        >
                            <span class="min-w-0 flex-1 truncate" :class="g.value === '' ? 'text-muted-foreground italic' : ''">{{ label(g.value) }}</span>
                            <span class="bg-muted relative h-2 w-20 shrink-0 overflow-hidden rounded-full">
                                <span class="bg-primary absolute inset-y-0 left-0 rounded-full" :style="{ width: `${(g.count / groupMax) * 100}%` }" />
                            </span>
                            <span class="w-8 shrink-0 text-right text-xs tabular-nums">{{ g.count }}</span>
                        </button>
                    </div>
                </section>

                <!-- Table -->
                <section class="bg-card text-card-foreground rounded-xl border shadow-sm">
                    <div class="flex items-center justify-end gap-2 border-b px-3 py-2 text-sm">
                        <span class="text-muted-foreground">Zeilen</span>
                        <select v-model.number="pageSize" class="border-input bg-background h-8 rounded-md border px-2 text-sm">
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                            <option :value="0">alle</option>
                        </select>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-muted-foreground text-left">
                                <tr class="border-b">
                                    <TableHeadCell label="Name" sort-key="nachname" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('nachname')" />
                                    <TableHeadCell label="Abteilung" sort-key="abteilung" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('abteilung')" />
                                    <TableHeadCell label="Position" sort-key="position" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('position')" />
                                    <TableHeadCell label="Stadt" sort-key="ort" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('ort')" />
                                    <TableHeadCell label="Adresse" sort-key="strasse" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('strasse')" />
                                    <TableHeadCell label="Bundesland" sort-key="bundesland" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('bundesland')" />
                                    <TableHeadCell label="Kontakt" sort-key="email" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('email')" />
                                    <TableHeadCell label="Rollen" sort-key="roles" :active-key="sortKey" :direction="sortDir" @sort="toggleSort('roles')" />
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="u in pagedRows" :key="u.id" class="hover:bg-accent/40 align-top" :class="u.removed ? 'opacity-60' : ''">
                                    <td class="px-3 py-2">
                                        <span v-if="u.removed" class="font-medium whitespace-nowrap">{{ displayName(u) }}</span>
                                        <Link v-else :href="`/users/${u.id}/edit`" class="font-medium whitespace-nowrap hover:underline">{{ displayName(u) }}</Link>
                                        <div class="text-muted-foreground text-xs">
                                            {{ u.username }}
                                            <span v-if="u.removed" class="ml-1 rounded bg-red-100 px-1 text-[10px] text-red-700 dark:bg-red-950 dark:text-red-300">
                                                ausgeschieden
                                            </span>
                                            <span v-else-if="u.lastlogin" :title="`Letzte Anmeldung ${formatDate(u.lastlogin)}`"> · {{ formatDate(u.lastlogin) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">{{ u.abteilung }}</td>
                                    <td class="px-3 py-2">{{ u.position }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ u.ort }}</td>
                                    <td class="px-3 py-2">
                                        {{ u.strasse }}
                                        <div v-if="u.office" class="text-muted-foreground text-xs">Büro {{ u.office }}</div>
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ u.bundesland }}</td>
                                    <td class="px-3 py-2">
                                        <a v-if="u.email" :href="`mailto:${u.email}`" class="text-primary inline-flex items-center gap-1 hover:underline">
                                            <Mail class="h-3.5 w-3.5" />{{ u.email }}
                                        </a>
                                        <div v-if="u.tel || u.mobil" class="text-muted-foreground inline-flex flex-wrap items-center gap-x-2 text-xs">
                                            <Phone class="h-3 w-3" />
                                            <a v-if="u.tel" :href="`tel:${u.tel}`" class="hover:underline">{{ u.tel }}</a>
                                            <a v-if="u.mobil" :href="`tel:${u.mobil}`" class="hover:underline">{{ u.mobil }}</a>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="r in u.roles" :key="r" class="bg-muted rounded px-1.5 py-0.5 text-[11px]">{{ r }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!pagedRows.length">
                                    <td colspan="8" class="text-muted-foreground px-3 py-8 text-center">Keine Benutzer gefunden.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <TablePagination
                        :page="page"
                        :page-count="pageCount"
                        :range-from="rangeFrom"
                        :range-to="rangeTo"
                        :total="total"
                        item-label="Benutzern"
                        @update:page="page = $event"
                    />
                </section>
            </div>
        </div>
    </div>
</template>
