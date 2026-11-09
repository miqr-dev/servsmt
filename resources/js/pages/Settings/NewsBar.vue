<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { CalendarClock, Megaphone } from '@lucide/vue';
import { computed, h } from 'vue';
import ToggleSwitch from '@/components/ToggleSwitch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Einstellungen > Nachrichtenleiste (NewsBarController, App\NewsBar,
 * 2026-11-03). Replaces settings/publish/newsbar.blade.php. One message that
 * the Dashboard shows to every employee as "Aktuelle Information" while it is
 * published. Same URLs as before (GET /settings/publish/newsbar/1,
 * PUT /newsbar_update/1), Super_Admin only.
 *
 * Differences to the old form:
 * - Stays on this page after saving (the old one jumped to the start page).
 * - Live preview in the Dashboard style.
 * - A message is only required while the bar is published, so it can be
 *   switched off and emptied.
 */

const props = defineProps<{
    message: string;
    published: boolean;
    /** what the Dashboard shows right now (published and not empty) */
    active: boolean;
    updatedAt: string | null;
    /** column length, null = no limit (text column) */
    maxLength: number | null;
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Einstellungen', href: '/settings' },
            { title: 'Nachrichtenleiste', href: '/settings/publish/newsbar/1' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const form = useForm({ message: props.message, published: props.published });

const trimmed = computed(() => form.message.trim());
const canSave = computed(() => !form.processing && form.isDirty && (!form.published || trimmed.value !== ''));

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
    form.put('/newsbar_update/1', {
        preserveScroll: true,
        // Take over what the server stored (it trims the message), so the
        // form counts as saved again.
        onSuccess: () => {
            form.defaults({ message: props.message, published: props.published });
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Nachrichtenleiste" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-xl font-semibold">Nachrichtenleiste</h1>
            <span
                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                :class="
                    active
                        ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200'
                        : 'text-muted-foreground'
                "
            >
                <span class="size-2 rounded-full" :class="active ? 'bg-emerald-500' : 'bg-muted-foreground/40'" />
                {{ active ? 'Aktiv auf dem Dashboard' : 'Nicht aktiv' }}
            </span>
        </div>

        <div class="grid max-w-5xl gap-4 lg:grid-cols-2">
            <form class="bg-card text-card-foreground flex flex-col gap-5 rounded-xl border p-5 shadow-sm" @submit.prevent="save">
                <div class="flex items-start gap-3">
                    <Megaphone class="text-primary mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <h2 class="font-semibold">Nachricht an alle Mitarbeiter</h2>
                        <p class="text-muted-foreground text-sm">
                            Erscheint auf dem Dashboard als „Aktuelle Information“, solange sie veröffentlicht ist.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-lg border p-3">
                    <div>
                        <p id="newsbar-published-label" class="text-sm font-medium">Veröffentlichen</p>
                        <p class="text-muted-foreground text-xs">Aus = die Nachricht bleibt gespeichert, wird aber nicht angezeigt.</p>
                    </div>
                    <ToggleSwitch v-model="form.published" labelledby="newsbar-published-label" />
                </div>

                <label class="flex flex-col gap-1.5 text-sm">
                    <span class="font-medium">Nachricht</span>
                    <textarea
                        v-model="form.message"
                        rows="5"
                        :maxlength="maxLength ?? undefined"
                        placeholder="z. B. Am Freitag ab 14 Uhr sind die Server wegen Wartung nicht erreichbar."
                        class="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                    />
                    <span class="flex justify-between gap-2 text-xs">
                        <span class="text-destructive">{{ form.errors.message || form.errors.published }}</span>
                        <span v-if="maxLength" class="text-muted-foreground tabular-nums">{{ form.message.length }} / {{ maxLength }}</span>
                    </span>
                </label>

                <p v-if="form.published && !trimmed" class="text-xs text-amber-700 dark:text-amber-300">
                    Zum Veröffentlichen bitte eine Nachricht eingeben.
                </p>

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

            <!-- Preview: same markup as the news card on Home.vue -->
            <div class="flex flex-col gap-2">
                <h2 class="text-muted-foreground text-sm font-medium">Vorschau auf dem Dashboard</h2>
                <section
                    v-if="trimmed"
                    class="flex gap-3 rounded-xl border border-rose-200 bg-rose-50 p-6 text-rose-900 shadow-sm dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-100"
                    :class="form.published ? '' : 'opacity-50'"
                >
                    <Megaphone class="mt-0.5 size-5 shrink-0" />
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold">Aktuelle Information</h3>
                        <p class="mt-1 text-sm break-words whitespace-pre-line">{{ trimmed }}</p>
                    </div>
                </section>
                <p v-else class="text-muted-foreground rounded-xl border border-dashed p-6 text-center text-sm">Keine Nachricht.</p>
                <p v-if="trimmed && !form.published" class="text-muted-foreground text-xs">
                    Nicht veröffentlicht: wird auf dem Dashboard nicht angezeigt.
                </p>
            </div>
        </div>
    </div>
</template>
