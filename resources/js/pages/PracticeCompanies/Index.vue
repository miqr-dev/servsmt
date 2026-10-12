<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Building2, Calculator, Check, Copy, Download, Eye, EyeOff, Monitor, Pencil, Plus, Search, Trash2, X } from '@lucide/vue';
import { computed, h, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';
import { copyText } from '@/lib/clipboard';
import type { BreadcrumbItem } from '@/types';

/**
 * Übungsfirmen (PracticeCompanyController, modernized 2026-10-12).
 *
 * One page instead of the 9 old Blade pages: pick the Standort, then
 * "Windows & E-Mail" or "Lexware". Passwords are hidden (eye to show, copy
 * button next to every value - copying works on the http intranet too).
 * Terminal sees and copies; Super_Admin also adds, edits, deletes and exports.
 * ?stadt=Leipzig&art=lexware opens a list directly (the old
 * /practice-companies/city|lex/{city} links redirect here).
 */

type Company = {
    id: number;
    place_id: number;
    Windows_Username: string;
    Windows_Password: string;
    Email_Username: string;
    Email_Password: string;
    Lexware_Username: string;
    Lexware_Password: string;
};
type FieldKey = Exclude<keyof Company, 'id' | 'place_id'>;
type City = { id: number; name: string };
type Kind = 'windows' | 'lexware';

const props = defineProps<{
    companies: Company[];
    cities: City[];
    otherCities: City[];
    canEdit: boolean;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'Übungsfirmen', href: '/practice-companies' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const KINDS: Record<Kind, { label: string; icon: typeof Monitor; cols: { key: FieldKey; label: string; secret: boolean }[] }> = {
    windows: {
        label: 'Windows & E-Mail',
        icon: Monitor,
        cols: [
            { key: 'Windows_Username', label: 'Windows-Benutzer', secret: false },
            { key: 'Windows_Password', label: 'Windows-Passwort', secret: true },
            { key: 'Email_Username', label: 'E-Mail', secret: false },
            { key: 'Email_Password', label: 'E-Mail-Passwort', secret: true },
        ],
    },
    lexware: {
        label: 'Lexware',
        icon: Calculator,
        cols: [
            { key: 'Lexware_Username', label: 'Lexware-Benutzer', secret: false },
            { key: 'Lexware_Password', label: 'Lexware-Passwort', secret: true },
        ],
    },
};
const KIND_KEYS = Object.keys(KINDS) as Kind[];

// ─── Standort + list ────────────────────────────────────────────────────
const addedCities = ref<City[]>([]);
const cityList = computed(() => {
    const seen = new Set(props.cities.map((c) => c.id));

    return [...props.cities, ...addedCities.value.filter((c) => !seen.has(c.id))];
});
const remainingCities = computed(() => props.otherCities.filter((c) => !cityList.value.some((x) => x.id === c.id)));
const cityId = ref<number | null>(props.cities[0]?.id ?? null);
const city = computed(() => cityList.value.find((c) => c.id === cityId.value) ?? null);
const kind = ref<Kind>('windows');
const search = ref('');

const hasData = (c: Company, k: Kind) => KINDS[k].cols.some((col) => c[col.key].trim() !== '');
const natural = (a: string, b: string) => a.localeCompare(b, 'de', { numeric: true, sensitivity: 'base' });

function rowsOf(placeId: number | null, k: Kind): Company[] {
    const first = KINDS[k].cols[0].key;

    return props.companies.filter((c) => c.place_id === placeId && hasData(c, k)).sort((a, b) => natural(a[first], b[first]) || a.id - b.id);
}
const countOf = (placeId: number, k: Kind) => rowsOf(placeId, k).length;
const cityCount = (placeId: number) => props.companies.filter((c) => c.place_id === placeId && KIND_KEYS.some((k) => hasData(c, k))).length;

const cols = computed(() => KINDS[kind.value].cols);
const allRows = computed(() => rowsOf(cityId.value, kind.value));
const rows = computed(() => {
    const words = search.value.toLocaleLowerCase('de').split(/\s+/).filter(Boolean);
    if (!words.length) return allRows.value;

    return allRows.value.filter((c) => {
        const text = cols.value
            .filter((col) => !col.secret)
            .map((col) => c[col.key])
            .join(' ')
            .toLocaleLowerCase('de');

        return words.every((w) => text.includes(w));
    });
});

function pickCity(id: number) {
    cancelEdit();
    cityId.value = id;
}
function pickKind(k: Kind) {
    cancelEdit();
    kind.value = k;
}
function addCity(e: Event) {
    const sel = e.target as HTMLSelectElement;
    const c = props.otherCities.find((x) => x.id === Number(sel.value));
    sel.value = '';
    if (!c) return;
    addedCities.value.push(c);
    pickCity(c.id);
    startNew();
}

// ─── Passwords + copy ───────────────────────────────────────────────────
const showAll = ref(false);
const revealed = reactive(new Set<number>());
const isRevealed = (id: number) => showAll.value || revealed.has(id);
function toggleReveal(id: number) {
    if (revealed.has(id)) revealed.delete(id);
    else revealed.add(id);
}
watch(showAll, () => revealed.clear());

const copied = ref<string | null>(null);
let copiedTimer: ReturnType<typeof setTimeout> | undefined;
async function copy(value: string, key: string) {
    if (!(await copyText(value))) {
        toast.error('Kopieren nicht möglich.');

        return;
    }
    copied.value = key;
    clearTimeout(copiedTimer);
    copiedTimer = setTimeout(() => (copied.value = null), 1500);
}

// ─── Editing (Super_Admin) ──────────────────────────────────────────────
const editing = ref<number | 'new' | null>(null);
const draft = reactive<Record<string, string>>({});
const saving = ref(false);

function fillDraft(c: Company | null) {
    for (const k of Object.keys(draft)) delete draft[k];
    for (const col of cols.value) draft[col.key] = c ? c[col.key] : '';
}
async function focusFirst() {
    await nextTick();
    document.querySelector<HTMLInputElement>('[data-edit-row] input')?.focus();
}
function startNew() {
    if (!props.canEdit || !cityId.value) return;
    search.value = '';
    fillDraft(null);
    editing.value = 'new';
    focusFirst();
}
function startEdit(c: Company) {
    fillDraft(c);
    editing.value = c.id;
    focusFirst();
}
function cancelEdit() {
    editing.value = null;
}

const visitOptions = {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => (editing.value = null),
    onError: (errors: Record<string, string>) => toast.error(Object.values(errors)[0] ?? 'Speichern fehlgeschlagen.'),
    onFinish: () => (saving.value = false),
};
function save() {
    if (saving.value || editing.value === null) return;
    if (!cols.value.some((col) => (draft[col.key] ?? '').trim() !== '')) {
        toast.error('Bitte mindestens ein Feld ausfüllen.');

        return;
    }
    saving.value = true;
    const data = { kind: kind.value, ...draft };
    if (editing.value === 'new') router.post('/practice-companies', { ...data, place_id: cityId.value }, visitOptions);
    else router.put(`/practice-companies/${editing.value}`, data, visitOptions);
}
function remove(c: Company) {
    const name = c[cols.value[0].key] || c[cols.value[2]?.key ?? cols.value[0].key] || `#${c.id}`;
    const other = KIND_KEYS.find((k) => k !== kind.value && hasData(c, k));
    const note = other ? `\n\nDie ${KINDS[other].label}-Daten dieses Eintrags bleiben erhalten.` : '';
    if (!confirm(`${KINDS[kind.value].label}-Zugang „${name}“ in ${city.value?.name} löschen?${note}`)) return;
    router.delete(`/practice-companies/${c.id}?kind=${kind.value}`, {
        preserveScroll: true,
        preserveState: true,
        onError: () => toast.error('Löschen fehlgeschlagen.'),
    });
}
function onKey(e: KeyboardEvent) {
    if (e.key === 'Enter') {
        e.preventDefault();
        save();
    } else if (e.key === 'Escape') {
        cancelEdit();
    }
}

const exportUrl = computed(() => `/practice-companies/export?city=${encodeURIComponent(city.value?.name ?? '')}`);

// ─── ?stadt=…&art=… ─────────────────────────────────────────────────────
onMounted(() => {
    const q = new URLSearchParams(window.location.search);
    const name = (q.get('stadt') ?? '').toLocaleLowerCase('de');
    if (name) {
        const found = props.cities.find((c) => c.name.toLocaleLowerCase('de') === name);
        const other = props.canEdit ? props.otherCities.find((c) => c.name.toLocaleLowerCase('de') === name) : undefined;
        if (found) cityId.value = found.id;
        else if (other) {
            addedCities.value.push(other);
            cityId.value = other.id;
        }
    }
    const art = q.get('art');
    if (art === 'windows' || art === 'lexware') kind.value = art;
});
watch([city, kind], () => {
    const q = new URLSearchParams();
    if (city.value) q.set('stadt', city.value.name);
    q.set('art', kind.value);
    window.history.replaceState(window.history.state, '', `${window.location.pathname}?${q.toString()}`);
});
</script>

<template>
    <Head title="Übungsfirmen" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="flex items-center gap-2 text-xl font-semibold"><Building2 class="h-5 w-5" /> Übungsfirmen</h1>
                <p class="text-muted-foreground text-sm">Zugangsdaten der Übungsfirmen je Standort.</p>
            </div>
            <div v-if="canEdit && city" class="flex flex-wrap items-center gap-2 text-sm">
                <a :href="exportUrl" class="border-border hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3">
                    <Download class="h-4 w-4" /> Excel: {{ city.name }}
                </a>
                <a href="/practice-companies/export" class="text-muted-foreground hover:text-foreground px-1 underline-offset-2 hover:underline">
                    alle Standorte
                </a>
            </div>
        </div>

        <!-- Standorte -->
        <div v-if="cityList.length || (canEdit && otherCities.length)" class="flex flex-wrap items-center gap-2">
            <button
                v-for="c in cityList"
                :key="c.id"
                type="button"
                class="inline-flex h-9 items-center gap-2 rounded-full border px-4 text-sm transition-colors"
                :class="c.id === cityId ? 'bg-primary text-primary-foreground border-primary' : 'bg-card hover:bg-accent'"
                @click="pickCity(c.id)"
            >
                {{ c.name }}
                <span class="rounded-full px-1.5 text-xs tabular-nums" :class="c.id === cityId ? 'bg-primary-foreground/20' : 'bg-muted text-muted-foreground'">
                    {{ cityCount(c.id) }}
                </span>
            </button>
            <select
                v-if="canEdit && remainingCities.length"
                class="border-input bg-background text-muted-foreground h-9 rounded-full border px-3 text-sm"
                aria-label="Weiteren Standort hinzufügen"
                @change="addCity"
            >
                <option value="">+ Standort …</option>
                <option v-for="c in remainingCities" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
        </div>

        <div v-if="!city" class="bg-card text-muted-foreground rounded-xl border p-10 text-center text-sm shadow-sm">
            Noch keine Übungsfirmen erfasst.
            <template v-if="canEdit && otherCities.length"> Oben einen Standort wählen, um den ersten Eintrag anzulegen.</template>
        </div>

        <section v-else class="bg-card text-card-foreground rounded-xl border shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b p-3">
                <div class="bg-muted inline-flex rounded-lg p-1" role="tablist">
                    <button
                        v-for="k in KIND_KEYS"
                        :key="k"
                        type="button"
                        role="tab"
                        :aria-selected="kind === k"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md px-3 text-sm"
                        :class="kind === k ? 'bg-background font-medium shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        @click="pickKind(k)"
                    >
                        <component :is="KINDS[k].icon" class="h-4 w-4" />
                        {{ KINDS[k].label }}
                        <span class="text-muted-foreground text-xs tabular-nums">{{ countOf(city.id, k) }}</span>
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <Search class="text-muted-foreground absolute top-1/2 left-2.5 h-4 w-4 -translate-y-1/2" />
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Suchen …"
                            class="border-input bg-background h-9 w-44 rounded-md border pr-3 pl-8 text-sm"
                        />
                    </div>
                    <button
                        type="button"
                        class="border-border hover:bg-accent inline-flex h-9 items-center gap-1.5 rounded-md border px-3 text-sm"
                        :aria-pressed="showAll"
                        @click="showAll = !showAll"
                    >
                        <component :is="showAll ? EyeOff : Eye" class="h-4 w-4" />
                        {{ showAll ? 'Passwörter verbergen' : 'Passwörter anzeigen' }}
                    </button>
                    <button
                        v-if="canEdit"
                        type="button"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center gap-1.5 rounded-md px-3 text-sm disabled:opacity-50"
                        :disabled="editing !== null"
                        @click="startNew"
                    >
                        <Plus class="h-4 w-4" /> Neuer Eintrag
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-muted-foreground text-left">
                        <tr class="border-b">
                            <th class="w-10 p-3 font-medium">#</th>
                            <th v-for="col in cols" :key="col.key" class="p-3 font-medium whitespace-nowrap">{{ col.label }}</th>
                            <th v-if="canEdit" class="w-24 p-3"><span class="sr-only">Aktionen</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <!-- new entry -->
                        <tr v-if="editing === 'new'" data-edit-row class="bg-primary/5">
                            <td class="text-muted-foreground p-3">neu</td>
                            <td v-for="col in cols" :key="col.key" class="p-2">
                                <input
                                    v-model="draft[col.key]"
                                    type="text"
                                    autocomplete="off"
                                    :placeholder="col.label"
                                    class="border-input bg-background h-8 w-full min-w-36 rounded-md border px-2 font-mono text-sm"
                                    @keydown="onKey"
                                />
                            </td>
                            <td class="p-2 whitespace-nowrap">
                                <button type="button" class="hover:bg-accent rounded-md p-1.5 text-green-700 dark:text-green-400" title="Speichern (Enter)" :disabled="saving" @click="save">
                                    <Check class="h-4 w-4" />
                                </button>
                                <button type="button" class="hover:bg-accent text-muted-foreground rounded-md p-1.5" title="Abbrechen (Esc)" @click="cancelEdit">
                                    <X class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>

                        <template v-for="(c, i) in rows" :key="c.id">
                            <!-- edit -->
                            <tr v-if="editing === c.id" data-edit-row class="bg-primary/5">
                                <td class="text-muted-foreground p-3 tabular-nums">{{ i + 1 }}</td>
                                <td v-for="col in cols" :key="col.key" class="p-2">
                                    <input
                                        v-model="draft[col.key]"
                                        type="text"
                                        autocomplete="off"
                                        :placeholder="col.label"
                                        class="border-input bg-background h-8 w-full min-w-36 rounded-md border px-2 font-mono text-sm"
                                        @keydown="onKey"
                                    />
                                </td>
                                <td class="p-2 whitespace-nowrap">
                                    <button type="button" class="hover:bg-accent rounded-md p-1.5 text-green-700 dark:text-green-400" title="Speichern (Enter)" :disabled="saving" @click="save">
                                        <Check class="h-4 w-4" />
                                    </button>
                                    <button type="button" class="hover:bg-accent text-muted-foreground rounded-md p-1.5" title="Abbrechen (Esc)" @click="cancelEdit">
                                        <X class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>

                            <!-- view -->
                            <tr v-else class="group hover:bg-accent/40">
                                <td class="text-muted-foreground p-3 tabular-nums">{{ i + 1 }}</td>
                                <td v-for="col in cols" :key="col.key" class="px-3 py-2">
                                    <div v-if="c[col.key]" class="flex items-center gap-1">
                                        <span v-if="col.secret && !isRevealed(c.id)" class="text-muted-foreground font-mono tracking-widest select-none">••••••••</span>
                                        <span v-else class="font-mono whitespace-nowrap">{{ c[col.key] }}</span>
                                        <button
                                            v-if="col.secret"
                                            type="button"
                                            class="text-muted-foreground hover:text-foreground hover:bg-accent shrink-0 rounded p-1"
                                            :title="isRevealed(c.id) ? 'Verbergen' : 'Anzeigen'"
                                            :disabled="showAll"
                                            @click="toggleReveal(c.id)"
                                        >
                                            <component :is="isRevealed(c.id) ? EyeOff : Eye" class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            class="text-muted-foreground hover:text-foreground hover:bg-accent shrink-0 rounded p-1"
                                            :title="`${col.label} kopieren`"
                                            @click="copy(c[col.key], `${c.id}:${col.key}`)"
                                        >
                                            <Check v-if="copied === `${c.id}:${col.key}`" class="h-3.5 w-3.5 text-green-600" />
                                            <Copy v-else class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                    <span v-else class="text-muted-foreground">–</span>
                                </td>
                                <td v-if="canEdit" class="px-2 py-2 whitespace-nowrap">
                                    <button
                                        type="button"
                                        class="hover:bg-accent text-muted-foreground hover:text-foreground rounded-md p-1.5 disabled:opacity-40"
                                        title="Bearbeiten"
                                        :disabled="editing !== null"
                                        @click="startEdit(c)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="text-muted-foreground rounded-md p-1.5 hover:bg-red-50 hover:text-red-600 disabled:opacity-40 dark:hover:bg-red-950"
                                        title="Löschen"
                                        :disabled="editing !== null"
                                        @click="remove(c)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="!rows.length && editing !== 'new'">
                            <td :colspan="cols.length + (canEdit ? 2 : 1)" class="text-muted-foreground px-3 py-10 text-center">
                                <template v-if="search">Keine Treffer für „{{ search }}“.</template>
                                <template v-else>Keine {{ KINDS[kind].label }}-Zugänge für {{ city.name }}.</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-if="!canEdit" class="text-muted-foreground border-t px-3 py-2 text-xs">Änderungen nimmt die IT vor (Super_Admin).</p>
        </section>
    </div>
</template>
