<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ArrowLeft, ExternalLink, FileDown, X } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { computed, h, reactive, ref } from 'vue';
import TableHeadCell from '@/components/table/TableHeadCell.vue';
import TablePagination from '@/components/table/TablePagination.vue';
import TableToolbar from '@/components/table/TableToolbar.vue';
import { useDataTable, type DataTableColumn } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import { PM_REPORT_CATEGORIES, VERSANDTASCHE_GROUPS, pmItemLabel, type PMReportCategory } from '@/lib/printmarketingReport';
import type { BreadcrumbItem } from '@/types';

/**
 * Printmarketing Verwaltung - converted from
 * resources/views/korso/Printmarketing/management.blade.php
 * (KorsoController@printmarketingManagement, route /printmarketing-management).
 * Only users 1 and 312 may open it (now enforced server-side too, not just
 * by hiding the dashboard link).
 *
 * Two tabs, same as before:
 * - Übersicht: per category (Flyer / Give Aways / Geschäftsausstattung /
 *   Beschilderung / Messe), one row per article with its quantity per
 *   Standort and the still-open quantity. The Standort quantity links to
 *   the Detailliste filtered to that article + Standort (the old page
 *   scrolled to and flashed the first matching row). The checkbox in front
 *   of an article marks ALL its lines as ordered / not ordered.
 * - Detailliste: every ordered line (ticket, article, quantity, Standort)
 *   with a "schon bestellt" checkbox; the ticket number opens the ticket.
 *   Now uses the app's standard sortable/searchable/paginated table.
 *
 * Only open tickets are included (done tickets drop out, as before). The
 * Übersicht numbers are calculated from the same rows the Detailliste
 * shows, so ticking something updates both immediately.
 *
 * Small behaviour fixes vs. the old page:
 * - A Standort is crossed out when ALL its lines for that article are
 *   ordered (the old page crossed it out as soon as ANY one was, and never
 *   for the Messe category).
 * - Ticking sends the target state ("bestellt" / "nicht bestellt") instead
 *   of a blind toggle per row (new POST /korso-items/set-ordered), so fast
 *   clicking or the article-level "alle" box can't flip rows the wrong way.
 * - Article names are shown with spaces instead of underscores.
 *
 * PDF buttons unchanged: "PDF (Aktive Ansicht)" exports the currently
 * selected category, "PDF (Alle Kategorien)" all of them
 * (KorsoController@exportPrintmarketingPdf, still a Blade/DomPDF template).
 */

type Item = {
    id: number;
    korso_id: number;
    item_name: string;
    quantity: number;
    ordered: boolean;
    location: string | null;
};

const props = defineProps<{ items: Item[] }>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso', href: '/korso' },
            { title: 'Dashboard', href: '/korso-dashboard' },
            { title: 'Printmarketing Verwaltung', href: '#' },
        ];
        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const items = ref<Item[]>(props.items.map((i) => ({ ...i })));

// --- tabs ---

const mainTab = ref<'summary' | 'detail'>('summary');
const categoryKey = ref<PMReportCategory['key']>('flyer');

// --- summary ---

type LocationSummary = { location: string; total: number; open: number; allOrdered: boolean };
type ArticleSummary = {
    key: string;
    label: string;
    locations: LocationSummary[];
    total: number;
    remaining: number;
    allOrdered: boolean;
    someOrdered: boolean;
};

const itemsByName = computed(() => {
    const map = new Map<string, Item[]>();
    for (const i of items.value) {
        const list = map.get(i.item_name) ?? [];
        list.push(i);
        map.set(i.item_name, list);
    }
    return map;
});

function summarize(key: string, label = pmItemLabel(key)): ArticleSummary | null {
    const all = itemsByName.value.get(key) ?? [];
    // Like the old summary query (inner join on locations): lines without a
    // Standort aren't listed per Standort - they still count as ordered/open.
    const located = all.filter((i) => i.location);
    if (!located.length) return null;

    const byLoc = new Map<string, Item[]>();
    for (const i of located) {
        const list = byLoc.get(i.location as string) ?? [];
        list.push(i);
        byLoc.set(i.location as string, list);
    }
    const locations = [...byLoc.entries()]
        .map(([location, rows]) => ({
            location,
            total: rows.reduce((s, r) => s + r.quantity, 0),
            open: rows.filter((r) => !r.ordered).reduce((s, r) => s + r.quantity, 0),
            allOrdered: rows.every((r) => r.ordered),
        }))
        .sort((a, b) => a.location.localeCompare(b.location, 'de'));

    const total = located.reduce((s, r) => s + r.quantity, 0);
    const orderedQty = all.filter((r) => r.ordered).reduce((s, r) => s + r.quantity, 0);
    return {
        key,
        label,
        locations,
        total,
        remaining: Math.max(0, total - orderedQty),
        allOrdered: all.every((r) => r.ordered),
        someOrdered: all.some((r) => r.ordered),
    };
}

type SummaryEntry = { type: 'article'; article: ArticleSummary; indent?: boolean } | { type: 'group'; label: string };

const versandKeys = new Set(VERSANDTASCHE_GROUPS.flatMap((g) => Object.keys(g.items)));

function entriesFor(category: PMReportCategory): SummaryEntry[] {
    const out: SummaryEntry[] = [];
    if (category.key === 'stationery') {
        for (const group of VERSANDTASCHE_GROUPS) {
            const children = Object.entries(group.items)
                .map(([key, short]) => summarize(key, short))
                .filter((a): a is ArticleSummary => !!a);
            if (children.length) {
                out.push({ type: 'group', label: group.label });
                children.forEach((article) => out.push({ type: 'article', article, indent: true }));
            }
        }
    }
    for (const key of category.items) {
        if (category.key === 'stationery' && versandKeys.has(key)) continue;
        const article = summarize(key);
        if (article) out.push({ type: 'article', article });
    }
    return out;
}

const summaryByCategory = computed(() => Object.fromEntries(PM_REPORT_CATEGORIES.map((c) => [c.key, entriesFor(c)])) as Record<string, SummaryEntry[]>);
const activeEntries = computed(() => summaryByCategory.value[categoryKey.value] ?? []);
function openCount(key: string) {
    return (summaryByCategory.value[key] ?? []).filter((e) => e.type === 'article' && e.article.remaining > 0).length;
}

// --- ordering ---

const saving = ref(false);

async function setOrdered(ids: number[], ordered: boolean) {
    const targets = items.value.filter((i) => ids.includes(i.id) && i.ordered !== ordered);
    if (!targets.length) return;
    targets.forEach((i) => (i.ordered = ordered)); // optimistic
    saving.value = true;
    try {
        await axios.post('/korso-items/set-ordered', { ids: targets.map((i) => i.id), ordered });
    } catch {
        targets.forEach((i) => (i.ordered = !ordered));
        toast.error('Speichern fehlgeschlagen.');
    } finally {
        saving.value = false;
    }
}

function toggleArticle(article: ArticleSummary) {
    const ids = (itemsByName.value.get(article.key) ?? []).map((i) => i.id);
    setOrdered(ids, !article.allOrdered);
}

// --- detail list ---

const focus = ref<{ item: string; location: string } | null>(null);

function jumpToDetail(item: string, location: string) {
    focus.value = { item, location };
    detail.search = '';
    detail.page = 1;
    mainTab.value = 'detail';
}

const detailRows = computed(() =>
    focus.value ? items.value.filter((i) => i.item_name === focus.value!.item && i.location === focus.value!.location) : items.value,
);

const DETAIL_COLUMNS: DataTableColumn<Item>[] = [
    { key: 'korso_id' },
    { key: 'item_name', value: (i) => pmItemLabel(i.item_name) },
    { key: 'quantity' },
    { key: 'location', value: (i) => i.location ?? '' },
    { key: 'ordered', value: (i) => (i.ordered ? 'bestellt' : 'offen') },
];
const detail = reactive(useDataTable(detailRows, DETAIL_COLUMNS));

function pdfUrl(tab: string) {
    return `/korso/printmarketing/pdf?tab=${encodeURIComponent(tab)}`;
}

const btn = 'border-input hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm';
</script>

<template>
    <Head title="Printmarketing Verwaltung" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <Link href="/korso-dashboard" :class="btn"><ArrowLeft class="h-4 w-4" /> Zurück</Link>
                <h2 class="text-xl font-semibold">Printmarketing Verwaltung</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a :href="pdfUrl(categoryKey)" target="_blank" :class="btn"><FileDown class="h-4 w-4" /> PDF (Aktive Ansicht)</a>
                <a :href="pdfUrl('all')" target="_blank" :class="btn"><FileDown class="h-4 w-4" /> PDF (Alle Kategorien)</a>
            </div>
        </div>

        <!-- main tabs -->
        <div class="flex gap-1 border-b">
            <button
                v-for="t in [{ key: 'summary', label: 'Übersicht' }, { key: 'detail', label: 'Detailliste' }] as const"
                :key="t.key"
                type="button"
                class="-mb-px border-b-2 px-4 py-2 text-sm"
                :class="mainTab === t.key ? 'border-primary text-primary font-semibold' : 'text-muted-foreground hover:text-foreground border-transparent'"
                @click="mainTab = t.key"
            >
                {{ t.label }}
            </button>
        </div>

        <!-- SUMMARY -->
        <template v-if="mainTab === 'summary'">
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="c in PM_REPORT_CATEGORIES"
                    :key="c.key"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm"
                    :class="categoryKey === c.key ? 'bg-primary text-primary-foreground border-primary' : 'border-input hover:bg-muted/40'"
                    @click="categoryKey = c.key"
                >
                    {{ c.label }}
                    <span
                        v-if="openCount(c.key)"
                        class="rounded-full px-1.5 text-xs"
                        :class="categoryKey === c.key ? 'bg-white/20' : 'bg-primary/10 text-primary'"
                        :title="`${openCount(c.key)} Artikel mit offener Menge`"
                    >
                        {{ openCount(c.key) }}
                    </span>
                </button>
            </div>

            <div class="bg-card text-card-foreground overflow-x-auto rounded-xl border shadow-sm">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr class="border-b">
                            <th class="p-3 font-medium">Artikel</th>
                            <th class="p-3 font-medium">Standort (Menge)</th>
                            <th class="p-3 text-right font-medium">Offen / Gesamt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template v-for="(entry, idx) in activeEntries" :key="entry.type === 'group' ? `g-${entry.label}` : entry.article.key">
                            <tr v-if="entry.type === 'group'" class="bg-muted/30">
                                <td colspan="3" class="px-3 py-2 font-semibold">{{ entry.label }}</td>
                            </tr>
                            <tr v-else :class="entry.article.allOrdered ? 'text-muted-foreground' : ''">
                                <td class="p-3" :class="entry.indent ? 'pl-8' : ''">
                                    <label class="inline-flex cursor-pointer items-center gap-2" title="Alle Positionen dieses Artikels als bestellt markieren">
                                        <input
                                            type="checkbox"
                                            class="h-4 w-4"
                                            :checked="entry.article.allOrdered"
                                            :indeterminate.prop="!entry.article.allOrdered && entry.article.someOrdered"
                                            :disabled="saving"
                                            @change="toggleArticle(entry.article)"
                                        />
                                        <span :class="entry.article.allOrdered ? 'line-through' : 'font-medium'">{{ entry.article.label }}</span>
                                    </label>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="loc in entry.article.locations"
                                            :key="loc.location"
                                            type="button"
                                            class="hover:border-primary inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs"
                                            :class="loc.allOrdered ? 'text-muted-foreground line-through' : ''"
                                            :title="`Detailliste für ${entry.article.label} @ ${loc.location} anzeigen`"
                                            @click="jumpToDetail(entry.article.key, loc.location)"
                                        >
                                            {{ loc.location }}: <strong class="text-primary">{{ loc.total }}</strong>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <strong :class="entry.article.remaining > 0 ? '' : 'text-green-600'">{{ entry.article.remaining }}</strong>
                                    <span class="text-muted-foreground"> / {{ entry.article.total }}</span>
                                </td>
                            </tr>
                        </template>
                        <tr v-if="!activeEntries.length">
                            <td colspan="3" class="text-muted-foreground p-8 text-center">Keine offenen Bestellungen in dieser Kategorie.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- DETAIL -->
        <div v-else class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="flex flex-col gap-2 p-3">
                <TableToolbar v-model:search="detail.search" v-model:page-size="detail.pageSize" search-placeholder="Suchen..." />
                <div v-if="focus" class="bg-primary/5 flex items-center gap-2 self-start rounded-md px-3 py-1.5 text-sm">
                    Gefiltert: <strong>{{ pmItemLabel(focus.item) }}</strong> @ <strong>{{ focus.location }}</strong>
                    <button type="button" class="text-primary inline-flex items-center gap-1 hover:underline" @click="focus = null">
                        <X class="h-3.5 w-3.5" /> Filter entfernen
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr class="border-b">
                            <TableHeadCell label="Ticket" sort-key="korso_id" :active-key="detail.sortKey" :direction="detail.sortDir" @sort="detail.toggleSort('korso_id')" />
                            <TableHeadCell label="Artikel" sort-key="item_name" :active-key="detail.sortKey" :direction="detail.sortDir" @sort="detail.toggleSort('item_name')" />
                            <TableHeadCell label="Menge" sort-key="quantity" :active-key="detail.sortKey" :direction="detail.sortDir" @sort="detail.toggleSort('quantity')" />
                            <TableHeadCell label="Standort" sort-key="location" :active-key="detail.sortKey" :direction="detail.sortDir" @sort="detail.toggleSort('location')" />
                            <TableHeadCell label="Schon bestellt?" sort-key="ordered" :active-key="detail.sortKey" :direction="detail.sortDir" @sort="detail.toggleSort('ordered')" />
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="row in detail.pagedRows" :key="row.id" :class="row.ordered ? 'text-muted-foreground' : ''">
                            <td class="p-3">
                                <a :href="`/korso/${row.korso_id}`" target="_blank" class="text-primary inline-flex items-center gap-1 hover:underline" title="Ticket öffnen">
                                    #{{ row.korso_id }} <ExternalLink class="h-3 w-3" />
                                </a>
                            </td>
                            <td class="p-3" :class="row.ordered ? 'line-through' : ''">{{ pmItemLabel(row.item_name) }}</td>
                            <td class="p-3" :class="row.ordered ? 'line-through' : ''">{{ row.quantity }}</td>
                            <td class="p-3">{{ row.location ?? '—' }}</td>
                            <td class="p-3">
                                <input type="checkbox" class="h-4 w-4" :checked="row.ordered" @change="setOrdered([row.id], !row.ordered)" />
                            </td>
                        </tr>
                        <tr v-if="!detail.pagedRows.length">
                            <td colspan="5" class="text-muted-foreground p-8 text-center">Keine Einträge.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <TablePagination
                :page="detail.page"
                :page-count="detail.pageCount"
                :range-from="detail.rangeFrom"
                :range-to="detail.rangeTo"
                :total="detail.total"
                item-label="Positionen"
                @update:page="detail.page = $event"
            />
        </div>
    </div>
</template>
