<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Clock, FileQuestion, LayoutGrid, ServerCrash, ShieldX, Wrench } from '@lucide/vue';
import { computed } from 'vue';

/**
 * Modern error page (403 / 404 / 419 / 429 / 500 / 503), rendered by
 * App\Exceptions\Handler for normal and Inertia requests. It replaces the old
 * AdminLTE resources/views/errors/*.blade.php pages.
 *
 * Deliberately standalone (no AppLayout / sidebar): a 404 never runs the
 * session/auth middleware and a 403 from EnforceRouteAccess is thrown before
 * HandleInertiaRequests shares the user, so the page can't rely on them.
 */

// No sidebar shell (see app.ts): works without session / logged-in user.
defineOptions({ layout: false });

const props = defineProps<{
    status: number;
    message?: string | null;
}>();

type Info = { title: string; text: string; icon: typeof ShieldX; tone: string };

const INFO: Record<number, Info> = {
    403: {
        title: 'Keine Zugriffsberechtigung',
        text: 'Für diese Seite fehlen Ihnen die nötigen Rechte. Wenn Sie glauben, dass das ein Fehler ist, wenden Sie sich bitte an die IT.',
        icon: ShieldX,
        tone: 'bg-primary/10 text-primary',
    },
    404: {
        title: 'Seite nicht gefunden',
        text: 'Die Seite existiert nicht (mehr). Vielleicht wurde der Eintrag gelöscht oder der Link ist veraltet.',
        icon: FileQuestion,
        tone: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    },
    419: {
        title: 'Sitzung abgelaufen',
        text: 'Die Seite war zu lange geöffnet. Bitte laden Sie sie neu und versuchen Sie es noch einmal.',
        icon: Clock,
        tone: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
    },
    429: {
        title: 'Zu viele Anfragen',
        text: 'Bitte warten Sie einen Moment und versuchen Sie es dann erneut.',
        icon: Clock,
        tone: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
    },
    500: {
        title: 'Da ist etwas schiefgelaufen',
        text: 'Auf dem Server ist ein Fehler aufgetreten. Die IT wurde benachrichtigt - bitte versuchen Sie es später noch einmal.',
        icon: ServerCrash,
        tone: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    },
    503: {
        title: 'Wartungsarbeiten',
        text: 'servsmt wird gerade gewartet und ist gleich wieder erreichbar.',
        icon: Wrench,
        tone: 'bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
    },
};

const info = computed<Info>(() => INFO[props.status] ?? INFO[500]);

function goBack() {
    if (window.history.length > 1) window.history.back();
    else window.location.href = '/';
}
</script>

<template>
    <Head :title="info.title" />

    <div class="bg-background text-foreground flex min-h-svh items-center justify-center p-6">
        <div class="bg-card text-card-foreground w-full max-w-md rounded-2xl border p-8 text-center shadow-sm">
            <div class="mx-auto flex size-16 items-center justify-center rounded-full" :class="info.tone">
                <component :is="info.icon" class="size-8" />
            </div>

            <p class="text-muted-foreground mt-6 text-sm font-semibold tracking-widest">FEHLER {{ status }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ info.title }}</h1>
            <p class="text-muted-foreground mt-3 text-sm leading-relaxed">{{ info.text }}</p>

            <div class="mt-8 flex flex-col-reverse justify-center gap-2 sm:flex-row">
                <button
                    type="button"
                    class="hover:bg-accent inline-flex h-9 items-center justify-center gap-2 rounded-md border px-4 text-sm"
                    @click="goBack"
                >
                    <ArrowLeft class="size-4" />
                    Zurück
                </button>
                <!-- Plain <a>: a full page load, so the Dashboard gets a fresh session/Inertia state. -->
                <a
                    href="/"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 items-center justify-center gap-2 rounded-md px-4 text-sm"
                >
                    <LayoutGrid class="size-4" />
                    Zum Dashboard
                </a>
            </div>
        </div>
    </div>
</template>
