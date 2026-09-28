<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import axios from 'axios';
import {
    ClipboardCopy,
    FileSpreadsheet,
    FileText,
    FileDown,
    IdCard,
    Printer,
    CirclePlay,
    Search,
    X,
} from '@lucide/vue';
import { toast } from 'vue-sonner';
import { computed, h, ref, watch } from 'vue';
import TablePagination from '@/components/table/TablePagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Teilnehmer Liste - converted from resources/views/tickets/participant/index.blade.php
 * (ParticipantTicketTableController@index, route /participants).
 *
 * What changed vs. the old DataTables page:
 * - No 6-month limit anymore, and no "load everything" either: the server
 *   pages 50 rows at a time, and search (debounced) runs in the database
 *   across ALL Teilnehmer the user may see. Multi-word search matches each
 *   word against Vorname/Nachname/Benutzername/Maßnahme/Standort/Kurs/
 *   Gruppe/Branch/Email/Erledigt von.
 * - Access: Super_Admin sees all (+ a Standort filter); Teilnehmer_Info sees
 *   only its own Standort (Berlin split into Berlin-PP / Berlin-TBR by
 *   street, same rule as before). Verwaltung no longer has access.
 * - Row selection: checkbox per row (or click the row), kept across pages
 *   and searches until "Auswahl aufheben" - replaces DataTables' select
 *   extension.
 *
 * Buttons - same functions and column sets as the old DataTables buttons:
 * - Kopieren / CSV / PDF: Vorname, Nachname, Benutzername, Passwort, Maßnahme.
 * - Excel: Vorname, Nachname, Benutzername, Standort, Passwort, Alter,
 *   Maßnahme, Deaktivierungsdatum, Kurse, Gruppe, Branch.
 * - Excel Erweitert: Nr., Vorname, Nachname, Maßnahme, Standort, Alter,
 *   Geb.datum, Dauer, Beginn, Ende, Berater, MA/Team, Bemerkungen,
 *   Benutzername, Passwort, A 1.TT, A 2.TT (the extra columns are empty
 *   placeholders to fill in, as before).
 * - Auswahl drucken / PC-Zugänge Drucken: the ticked rows (PC-Zugänge = one
 *   20pt "Zugangsdaten für" card per person, two per row).
 * - Alle drucken: every row matching the current search.
 * Kopieren, CSV, Excel, Excel Erweitert, PDF and Alle drucken all cover every
 * row matching the current search/filter, not just the visible page (the
 * old buttons also exported all filtered rows). Excel/PDF are generated
 * server-side (Maatwebsite Excel / DomPDF); copy/CSV/print in the browser.
 */

type Row = {
    id: number;
    vorname: string | null;
    nachname: string | null;
    username: string | null;
    password: string | null;
    course: string | null;
    location: string | null;
    ticket_created_at: string;
    created_at: string;
    done_by: string;
    kurs: string | null;
    gruppe: string | null;
    branch: string | null;
    deaktivierungsdatum: string | null;
};

const props = defineProps<{
    participants: Row[];
    pagination: { page: number; pageCount: number; total: number; from: number; to: number; perPage: number };
    filters: { search: string; location: string };
    isSuperAdmin: boolean;
    locations: string[];
    scopeLabel: string | null;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Teilnehmer Liste', href: '/participants' }];
        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const TITLE = 'MIQR | SMT'; // the old page's <title>, used by DataTables as the export/print title

// --- search / filter / paging (server-side, Inertia partial reloads) ---

const search = ref(props.filters.search ?? '');
const location = ref(props.filters.location ?? '');
const page = ref(props.pagination.page);
const loading = ref(false);

watch(
    () => props.pagination.page,
    (p) => (page.value = p),
);

function queryParams(extra: Record<string, unknown> = {}) {
    return {
        search: search.value.trim() || undefined,
        location: props.isSuperAdmin && location.value ? location.value : undefined,
        ...extra,
    };
}

function reload() {
    router.get('/participants', queryParams({ page: page.value > 1 ? page.value : undefined }), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['participants', 'pagination', 'filters'],
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
}
const reloadDebounced = useDebounceFn(reload, 400);

watch(search, () => {
    page.value = 1;
    reloadDebounced();
});
watch(location, () => {
    page.value = 1;
    reload();
});
function goToPage(p: number) {
    page.value = Math.min(Math.max(1, p), props.pagination.pageCount);
    reload();
}

const rowNumber = (i: number) => (props.pagination.page - 1) * props.pagination.perPage + i + 1;

// --- selection (kept across pages/searches) ---

const selected = ref<Record<number, Row>>({});
const selectedCount = computed(() => Object.keys(selected.value).length);
const selectedRows = computed(() => Object.values(selected.value));

function isSelected(row: Row) {
    return !!selected.value[row.id];
}
function toggle(row: Row) {
    const next = { ...selected.value };
    if (next[row.id]) delete next[row.id];
    else next[row.id] = row;
    selected.value = next;
}
function onRowClick(row: Row) {
    // Let people highlight a password/username to copy it without toggling.
    if (window.getSelection()?.toString()) return;
    toggle(row);
}
const allOnPageSelected = computed(() => props.participants.length > 0 && props.participants.every((r) => isSelected(r)));
function togglePage() {
    const next = { ...selected.value };
    if (allOnPageSelected.value) props.participants.forEach((r) => delete next[r.id]);
    else props.participants.forEach((r) => (next[r.id] = r));
    selected.value = next;
}
function clearSelection() {
    selected.value = {};
}

// --- exports ---

const busy = ref<string | null>(null);

async function fetchAllMatching(): Promise<Row[]> {
    const { data } = await axios.get<Row[]>('/participants/export-rows', { params: queryParams() });
    return data;
}

const BASIC_HEADERS = ['Vorname', 'Nachname', 'Benutzername', 'Passwort', 'Maßnahme'];
const basicCells = (r: Row) => [r.vorname, r.nachname, r.username, r.password, r.course].map((v) => v ?? '');

async function copyRows() {
    busy.value = 'copy';
    try {
        const rows = await fetchAllMatching();
        const text = [BASIC_HEADERS, ...rows.map(basicCells)].map((cells) => cells.join('\t')).join('\n');
        await writeClipboard(text);
        toast.success(`${rows.length} Zeilen in die Zwischenablage kopiert`);
    } catch {
        toast.error('Kopieren fehlgeschlagen.');
    } finally {
        busy.value = null;
    }
}

async function writeClipboard(text: string) {
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        ta.remove();
    }
}

async function downloadCsv() {
    busy.value = 'csv';
    try {
        const rows = await fetchAllMatching();
        const quote = (v: string) => `"${String(v).replace(/"/g, '""')}"`;
        const csv = [BASIC_HEADERS, ...rows.map(basicCells)].map((cells) => cells.map(quote).join(',')).join('\r\n');
        // BOM so Excel opens umlauts correctly.
        const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `Teilnehmer_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        URL.revokeObjectURL(a.href);
    } catch {
        toast.error('CSV-Export fehlgeschlagen.');
    } finally {
        busy.value = null;
    }
}

function serverExportUrl(type: 'excel' | 'excel-extended' | 'pdf') {
    const params = new URLSearchParams();
    const q = queryParams();
    if (q.search) params.set('search', String(q.search));
    if (q.location) params.set('location', String(q.location));
    const qs = params.toString();
    return `/participants/export/${type}${qs ? `?${qs}` : ''}`;
}

// --- printing (new window, like DataTables' print button) ---

function escapeHtml(v: unknown): string {
    return String(v ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function openPrintWindow(): Window | null {
    // Opened synchronously inside the click so popup blockers allow it;
    // content is written once the data is there.
    const w = window.open('', '_blank');
    if (!w) {
        toast.error('Druckfenster wurde blockiert - bitte Pop-ups für diese Seite erlauben.');
        return null;
    }
    w.document.write('<p style="font-family:sans-serif">Wird vorbereitet…</p>');
    return w;
}

function writeAndPrint(w: Window, bodyHtml: string, extraCss = '') {
    w.document.open();
    w.document.write(`<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><title>${escapeHtml(TITLE)}</title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; margin: 16px; }
  h1 { font-size: 20px; text-align: center; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  th, td { text-align: left; padding: 4px 6px; border-bottom: 1px solid #ccc; }
  th { border-bottom: 2px solid #333; }
  ${extraCss}
</style></head><body>${bodyHtml}</body></html>`);
    w.document.close();
    w.focus();
    setTimeout(() => {
        w.print();
    }, 250);
}

function tableHtml(rows: Row[]) {
    const head = BASIC_HEADERS.map((hd) => `<th>${escapeHtml(hd)}</th>`).join('');
    const body = rows.map((r) => `<tr>${basicCells(r).map((c) => `<td>${escapeHtml(c)}</td>`).join('')}</tr>`).join('');
    return `<h1>${escapeHtml(TITLE)}</h1><table><thead><tr>${head}</tr></thead><tbody>${body}</tbody></table>`;
}

function requireSelection(): boolean {
    if (selectedCount.value === 0) {
        toast.error('Bitte zuerst Teilnehmer auswählen (Häkchen setzen).');
        return false;
    }
    return true;
}

function printSelected() {
    if (!requireSelection()) return;
    const w = openPrintWindow();
    if (w) writeAndPrint(w, tableHtml(selectedRows.value));
}

async function printAll() {
    const w = openPrintWindow();
    if (!w) return;
    busy.value = 'printAll';
    try {
        writeAndPrint(w, tableHtml(await fetchAllMatching()));
    } catch {
        w.close();
        toast.error('Drucken fehlgeschlagen.');
    } finally {
        busy.value = null;
    }
}

function printPcAccess() {
    if (!requireSelection()) return;
    const w = openPrintWindow();
    if (!w) return;
    const cards = selectedRows.value
        .map(
            (r) => `<div class="card">
  <div><strong>Zugangsdaten für:</strong></div>
  <div>${escapeHtml(`${r.vorname ?? ''} ${r.nachname ?? ''}`.trim())}</div>
  <div style="margin-top:10px"><strong>Anmeldedaten PC</strong></div>
  <div>Benutzername: ${escapeHtml(r.username)}</div>
  <div>Kennwort: ${escapeHtml(r.password)}</div>
</div>`,
        )
        .join('');
    writeAndPrint(
        w,
        `<div class="cards">${cards}</div>`,
        `body { font-size: 20pt; margin: 0; }
   .cards { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px; }
   .card { width: 48%; box-sizing: border-box; padding: 20px; border: 1px solid #000; margin-bottom: 20px; break-inside: avoid; }`,
    );
}

// --- video ---

const showVideo = ref(false);
const videoEl = ref<HTMLVideoElement | null>(null);
function closeVideo() {
    videoEl.value?.pause();
    showVideo.value = false;
}

const btn = 'border-input hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm disabled:opacity-50';
</script>

<template>
    <Head title="Teilnehmer Liste" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">Teilnehmer Liste</h2>
                <p class="text-muted-foreground text-sm">
                    {{ pagination.total }} Teilnehmer<template v-if="scopeLabel"> · Standort {{ scopeLabel }}</template>
                </p>
            </div>
            <button type="button" :class="btn" @click="showVideo = true">
                <CirclePlay class="h-4 w-4" /> Video Anschauen
            </button>
        </div>

        <div class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <!-- toolbar -->
            <div class="flex flex-col gap-3 border-b p-3">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <Search class="text-muted-foreground absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Suchen (alle Teilnehmer)…"
                            class="border-input bg-background h-9 w-72 rounded-md border pr-3 pl-8 text-sm"
                        />
                    </div>
                    <select v-if="isSuperAdmin" v-model="location" class="border-input bg-background h-9 rounded-md border px-2 text-sm">
                        <option value="">Alle Standorte</option>
                        <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" :class="btn" :disabled="!!busy" @click="copyRows"><ClipboardCopy class="h-4 w-4" /> Kopieren</button>
                    <button type="button" :class="btn" :disabled="!!busy" @click="downloadCsv"><FileText class="h-4 w-4" /> CSV</button>
                    <a :class="btn" :href="serverExportUrl('excel')"><FileSpreadsheet class="h-4 w-4" /> Excel</a>
                    <a :class="btn" :href="serverExportUrl('pdf')"><FileDown class="h-4 w-4" /> PDF</a>
                    <button type="button" :class="btn" @click="printSelected"><Printer class="h-4 w-4" /> Auswahl drucken</button>
                    <button type="button" :class="btn" :disabled="!!busy" @click="printAll"><Printer class="h-4 w-4" /> Alle drucken</button>
                    <a :class="btn" :href="serverExportUrl('excel-extended')"><FileSpreadsheet class="h-4 w-4" /> Excel Erweitert</a>
                    <button type="button" :class="btn" @click="printPcAccess"><IdCard class="h-4 w-4" /> PC-Zugänge Drucken</button>
                </div>

                <div v-if="selectedCount" class="bg-primary/5 flex items-center gap-3 rounded-md px-3 py-1.5 text-sm">
                    <span><strong>{{ selectedCount }}</strong> ausgewählt</span>
                    <button type="button" class="text-primary inline-flex items-center gap-1 hover:underline" @click="clearSelection">
                        <X class="h-3.5 w-3.5" /> Auswahl aufheben
                    </button>
                </div>
            </div>

            <!-- table -->
            <div class="overflow-x-auto transition-opacity" :class="loading ? 'opacity-50' : ''">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr class="border-b">
                            <th class="w-10 p-3">
                                <input
                                    type="checkbox"
                                    class="h-4 w-4"
                                    title="Alle auf dieser Seite auswählen"
                                    :checked="allOnPageSelected"
                                    @change="togglePage"
                                />
                            </th>
                            <th class="p-3 font-medium">Nr.</th>
                            <th class="p-3 font-medium">Vorname</th>
                            <th class="p-3 font-medium">Nachname</th>
                            <th class="p-3 font-medium">Benutzername</th>
                            <th class="p-3 font-medium">Passwort</th>
                            <th class="p-3 font-medium">Maßnahme</th>
                            <th class="p-3 font-medium">Standort</th>
                            <th class="p-3 font-medium">Erstellt am</th>
                            <th class="p-3 font-medium">Erledigt am</th>
                            <th class="p-3 font-medium">Erledigt von</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="(row, i) in participants"
                            :key="row.id"
                            class="cursor-pointer"
                            :class="isSelected(row) ? 'bg-primary/10' : 'hover:bg-muted/40'"
                            @click="onRowClick(row)"
                        >
                            <td class="p-3" @click.stop>
                                <input type="checkbox" class="h-4 w-4" :checked="isSelected(row)" @change="toggle(row)" />
                            </td>
                            <td class="text-muted-foreground p-3">{{ rowNumber(i) }}</td>
                            <td class="p-3">{{ row.vorname }}</td>
                            <td class="p-3">{{ row.nachname }}</td>
                            <td class="p-3 font-semibold">{{ row.username }}</td>
                            <td class="p-3 font-mono">{{ row.password }}</td>
                            <td class="p-3">{{ row.course }}</td>
                            <td class="p-3">{{ row.location }}</td>
                            <td class="p-3 text-blue-600">{{ row.ticket_created_at }}</td>
                            <td class="p-3 text-green-600">{{ row.created_at }}</td>
                            <td class="p-3">{{ row.done_by }}</td>
                        </tr>
                        <tr v-if="!participants.length">
                            <td colspan="11" class="text-muted-foreground p-8 text-center">
                                {{ search ? 'Nichts gefunden.' : 'Keine Teilnehmer vorhanden.' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <TablePagination
                :page="page"
                :page-count="pagination.pageCount"
                :range-from="pagination.from"
                :range-to="pagination.to"
                :total="pagination.total"
                item-label="Teilnehmern"
                @update:page="goToPage"
            />
        </div>

        <!-- video modal -->
        <Transition enter-active-class="transition-opacity" leave-active-class="transition-opacity" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="showVideo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeVideo">
                <div class="bg-card relative w-full max-w-4xl rounded-xl p-4 shadow-lg">
                    <button type="button" class="text-muted-foreground hover:text-foreground absolute top-2 right-3 text-xl" @click="closeVideo">&times;</button>
                    <video ref="videoEl" controls autoplay preload="auto" class="w-full rounded-md">
                        <source src="/images/admin_images/Teilnehmer_liste.mp4" type="video/mp4" />
                    </video>
                </div>
            </div>
        </Transition>
    </div>
</template>
