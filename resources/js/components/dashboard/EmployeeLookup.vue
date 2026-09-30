<script setup lang="ts">
import { Briefcase, Mail, MapPin, Phone, Search, UserRound, X } from '@lucide/vue';
import axios from 'axios';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

/**
 * "Mitarbeiter Info" box on the Dashboard (every employee / Verwaltung).
 * Modern version of the old contacts "Suche nach Name": type a first name,
 * last name or username, pick the colleague, see Beruf, Adresse, Telefon,
 * E-Mail. Data: GET /dashboard/mitarbeiter-suche?q= (DashboardController@employeeSearch).
 */

type Employee = {
    id: number;
    vorname: string | null;
    name: string | null;
    username: string | null;
    position: string | null;
    abteilung: string | null;
    street: string | null;
    plz: string | null;
    ort: string | null;
    tel: string | null;
    email: string | null;
};

const query = ref('');
const results = ref<Employee[]>([]);
const selected = ref<Employee | null>(null);
const loading = ref(false);
const searched = ref(false);
let timer: ReturnType<typeof setTimeout> | undefined;
let requestId = 0;

function fullName(e: Employee): string {
    return [e.vorname, e.name].filter(Boolean).join(' ') || e.username || '';
}

function initials(e: Employee): string {
    return ((e.vorname?.[0] ?? '') + (e.name?.[0] ?? '')).toUpperCase() || '?';
}

const address = computed(() => {
    const e = selected.value;
    if (!e) return '';
    const cityLine = [e.plz, e.ort].filter(Boolean).join(' ');

    return [e.street, cityLine].filter(Boolean).join(', ');
});

async function runSearch(q: string) {
    const id = ++requestId;
    loading.value = true;
    try {
        const { data } = await axios.get<Employee[]>('/dashboard/mitarbeiter-suche', { params: { q } });
        if (id !== requestId) return; // a newer search is running
        results.value = data;
        searched.value = true;
        // Exactly one hit: show it right away.
        if (data.length === 1) selected.value = data[0];
    } catch {
        if (id === requestId) results.value = [];
    } finally {
        if (id === requestId) loading.value = false;
    }
}

watch(query, (q) => {
    selected.value = null;
    clearTimeout(timer);
    if (q.trim().length < 2) {
        results.value = [];
        searched.value = false;
        loading.value = false;
        requestId++;

        return;
    }
    timer = setTimeout(() => runSearch(q.trim()), 250);
});

onBeforeUnmount(() => clearTimeout(timer));

function clear() {
    query.value = '';
}
</script>

<template>
    <section class="bg-card text-card-foreground rounded-xl border shadow-sm">
        <h2 class="text-muted-foreground border-b px-4 py-3 text-xs font-semibold tracking-wide uppercase">Mitarbeiter Info</h2>

        <div class="p-3">
            <div class="relative">
                <Search class="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
                <input
                    v-model="query"
                    type="text"
                    placeholder="Vorname, Nachname oder Benutzername"
                    class="border-input bg-background h-9 w-full rounded-md border pr-8 pl-8 text-sm"
                    autocomplete="off"
                    @keydown.esc="clear"
                />
                <button
                    v-if="query"
                    type="button"
                    class="text-muted-foreground hover:text-foreground absolute top-1/2 right-2 -translate-y-1/2"
                    aria-label="Suche leeren"
                    @click="clear"
                >
                    <X class="size-4" />
                </button>
            </div>
        </div>

        <!-- Details of the chosen colleague -->
        <div v-if="selected" class="border-t p-4">
            <div class="flex items-center gap-3">
                <span class="bg-primary/10 text-primary flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold">
                    {{ initials(selected) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate font-semibold">{{ fullName(selected) }}</p>
                    <p class="text-muted-foreground truncate text-xs">{{ selected.username }}</p>
                </div>
            </div>

            <dl class="mt-4 flex flex-col gap-3 text-sm">
                <div class="flex gap-2">
                    <Briefcase class="text-muted-foreground mt-0.5 size-4 shrink-0" />
                    <div class="min-w-0">
                        <dt class="sr-only">Beruf</dt>
                        <dd>{{ selected.position || '–' }}</dd>
                        <dd v-if="selected.abteilung" class="text-muted-foreground text-xs">{{ selected.abteilung }}</dd>
                    </div>
                </div>
                <div class="flex gap-2">
                    <MapPin class="text-muted-foreground mt-0.5 size-4 shrink-0" />
                    <div class="min-w-0">
                        <dt class="sr-only">Adresse</dt>
                        <dd>{{ address || '–' }}</dd>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Phone class="text-muted-foreground mt-0.5 size-4 shrink-0" />
                    <div class="min-w-0">
                        <dt class="sr-only">Telefon</dt>
                        <dd>
                            <a v-if="selected.tel" :href="`tel:${selected.tel.replace(/[^\d+]/g, '')}`" class="hover:underline">{{ selected.tel }}</a>
                            <span v-else>–</span>
                        </dd>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Mail class="text-muted-foreground mt-0.5 size-4 shrink-0" />
                    <div class="min-w-0">
                        <dt class="sr-only">E-Mail</dt>
                        <dd class="break-all">
                            <a v-if="selected.email" :href="`mailto:${selected.email}`" class="text-primary hover:underline">{{ selected.email }}</a>
                            <span v-else>–</span>
                        </dd>
                    </div>
                </div>
            </dl>

            <button v-if="results.length > 1" type="button" class="text-muted-foreground hover:text-foreground mt-4 text-xs" @click="selected = null">
                ← Zurück zu {{ results.length }} Treffern
            </button>
        </div>

        <!-- Result list -->
        <ul v-else-if="results.length" class="max-h-72 divide-y overflow-auto border-t">
            <li v-for="e in results" :key="e.id">
                <button type="button" class="hover:bg-accent flex w-full items-center gap-3 px-4 py-2 text-left" @click="selected = e">
                    <span class="bg-muted flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold">{{
                        initials(e)
                    }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium">{{ fullName(e) }}</span>
                        <span class="text-muted-foreground block truncate text-xs">{{
                            [e.position, e.ort].filter(Boolean).join(' · ') || e.username
                        }}</span>
                    </span>
                </button>
            </li>
        </ul>

        <p v-else-if="loading" class="text-muted-foreground border-t px-4 py-3 text-sm">Suche…</p>
        <p v-else-if="searched" class="text-muted-foreground border-t px-4 py-3 text-sm">Keine Mitarbeiter gefunden.</p>
        <p v-else class="text-muted-foreground flex items-center gap-2 px-4 pb-3 text-xs">
            <UserRound class="size-4" />
            Mindestens 2 Zeichen eingeben.
        </p>
    </section>
</template>
