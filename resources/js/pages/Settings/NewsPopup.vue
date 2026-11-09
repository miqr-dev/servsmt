<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarClock, MessageSquareText } from '@lucide/vue';
import { computed, h } from 'vue';
import ToggleSwitch from '@/components/ToggleSwitch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { safeHtml } from '@/lib/safeHtml';
import type { BreadcrumbItem } from '@/types';

/**
 * Einstellungen > Popup (NewsController, App\News, 2026-11-03). Replaces
 * settings/publish/popup.blade.php. While published, the popup opens on the
 * IT ticket page (Ticketanfrage, Tickets/Index.vue, via /news.popup.checks)
 * each time someone opens it. Was labelled "Dashboard Popup", but it never
 * appeared on the Dashboard. Same URLs as before
 * (GET /settings/publish/popup/1, PUT /popup_update/1), Super_Admin only.
 *
 * Differences to the old form:
 * - Stays on this page after saving (the old one jumped to the start page).
 * - Live preview, rendered like the real popup (safeHtml: plain text keeps
 *   its line breaks, simple HTML is allowed).
 * - Title and text are only required while the popup is published.
 */

const props = defineProps<{
    title: string;
    body: string;
    published: boolean;
    /** what the ticket page shows right now (published and not empty) */
    active: boolean;
    updatedAt: string | null;
    /** column lengths, null = no limit (text column) */
    maxLength: { title: number | null; body: number | null };
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Popup (IT-Ticket-Seite)', href: '/settings/publish/popup/1' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({ title: props.title, body: props.body, published: props.published });

const title = computed(() => form.title.trim());
const body = computed(() => form.body.trim());
const missingForPublish = computed(() => form.published && (!title.value || !body.value));
const canSave = computed(() => !form.processing && form.isDirty && !missingForPublish.value);

const updatedLabel = computed(() => {
    if (!props.updatedAt) return null;
    return new Date(props.updatedAt).toLocaleString('de-DE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
});

function save() {
    form.put('/popup_update/1', {
        preserveScroll: true,
        // Take over what the server stored (it trims), so the form counts as saved again.
        onSuccess: () => {
            form.defaults({ title: props.title, body: props.body, published: props.published });
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Popup (IT-Ticket-Seite)" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-xl font-semibold">Popup auf der IT-Ticket-Seite</h1>
            <span
                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                :class="
                    active
                        ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200'
                        : 'text-muted-foreground'
                "
            >
                <span class="size-2 rounded-full" :class="active ? 'bg-emerald-500' : 'bg-muted-foreground/40'" />
                {{ active ? 'Aktiv' : 'Nicht aktiv' }}
            </span>
        </div>

        <div class="grid max-w-5xl gap-4 lg:grid-cols-2">
            <form class="bg-card text-card-foreground flex flex-col gap-5 rounded-xl border p-5 shadow-sm" @submit.prevent="save">
                <div class="flex items-start gap-3">
                    <MessageSquareText class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <h2 class="font-semibold">Hinweis beim Erstellen eines IT-Tickets</h2>
                        <p class="text-muted-foreground text-sm">
                            Öffnet sich auf der Seite „Ticketanfrage“ (IT Ticket), jedes Mal wenn jemand sie aufruft, solange es veröffentlicht ist.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-lg border p-3">
                    <div>
                        <p id="popup-published-label" class="text-sm font-medium">Veröffentlichen</p>
                        <p class="text-muted-foreground text-xs">Aus = Titel und Text bleiben gespeichert, das Popup erscheint aber nicht.</p>
                    </div>
                    <ToggleSwitch v-model="form.published" labelledby="popup-published-label" />
                </div>

                <label class="flex flex-col gap-1.5 text-sm">
                    <span class="font-medium">Titel</span>
                    <input
                        v-model="form.title"
                        type="text"
                        autocomplete="off"
                        :maxlength="maxLength.title ?? undefined"
                        class="border-input bg-background h-9 w-full rounded-md border px-3 text-sm"
                    />
                    <span class="flex justify-between gap-2 text-xs">
                        <span class="text-destructive">{{ form.errors.title }}</span>
                        <span v-if="maxLength.title" class="text-muted-foreground tabular-nums">{{ form.title.length }} / {{ maxLength.title }}</span>
                    </span>
                </label>

                <label class="flex flex-col gap-1.5 text-sm">
                    <span class="font-medium">Text</span>
                    <textarea
                        v-model="form.body"
                        rows="6"
                        :maxlength="maxLength.body ?? undefined"
                        class="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                    />
                    <span class="flex justify-between gap-2 text-xs">
                        <span class="text-destructive">{{ form.errors.body || form.errors.published }}</span>
                        <span v-if="maxLength.body" class="text-muted-foreground tabular-nums">{{ form.body.length }} / {{ maxLength.body }}</span>
                    </span>
                    <span class="text-muted-foreground text-xs">Zeilenumbrüche bleiben erhalten. Einfaches HTML (z. B. fett, Links, Listen) ist erlaubt.</span>
                </label>

                <p v-if="missingForPublish" class="text-xs text-amber-700 dark:text-amber-300">Zum Veröffentlichen bitte Titel und Text eingeben.</p>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="submit"
                        :disabled="!canSave"
                        class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center rounded-md px-4 text-sm disabled:opacity-50"
                    >
                        Speichern
                    </button>
                    <span v-if="form.isDirty" class="text-muted-foreground text-xs">Ungespeicherte Änderungen</span>
                </div>

                <div v-if="updatedLabel" class="text-muted-foreground flex items-center gap-2 text-xs">
                    <CalendarClock class="h-4 w-4 shrink-0" />
                    Zuletzt gespeichert am {{ updatedLabel }}
                </div>
            </form>

            <!-- Preview: same card as the popup on Tickets/Index.vue, without the dark overlay -->
            <div class="flex flex-col gap-2">
                <h2 class="text-muted-foreground text-sm font-medium">Vorschau</h2>
                <div class="bg-muted/40 flex justify-center rounded-xl border border-dashed p-4" :class="form.published ? '' : 'opacity-50'">
                    <div v-if="title || body" class="bg-card text-primary w-full max-w-lg rounded-xl border p-6 shadow-lg">
                        <div class="mb-2 flex justify-end">
                            <span class="text-muted-foreground text-sm" aria-hidden="true">✕</span>
                        </div>
                        <h3 class="mb-3 text-center text-lg font-semibold">{{ title }}</h3>
                        <div class="text-foreground text-sm" v-html="safeHtml(body)"></div>
                    </div>
                    <p v-else class="text-muted-foreground p-4 text-sm">Kein Inhalt.</p>
                </div>
                <p v-if="(title || body) && !form.published" class="text-muted-foreground text-xs">
                    Nicht veröffentlicht: erscheint auf der IT-Ticket-Seite nicht.
                </p>
            </div>
        </div>
    </div>
</template>
