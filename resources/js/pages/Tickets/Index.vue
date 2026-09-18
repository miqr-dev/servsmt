<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { h, onMounted, ref } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/tickets/index.blade.php (TicketController@index,
 * route /ticket.index) - the real entry point into every one of the ~30 ticket
 * creation forms: a static grid of 4 cards linking directly to each form's own
 * route (NOT the dynamic category->subcategory->form picker
 * TicketController@problem_type/@dependant_forms implement - those AJAX
 * endpoints aren't wired to this page at all, confirmed during the computer/
 * sub-batch's research and re-confirmed here).
 *
 * Card links are literal route URIs (matching every other page in this
 * migration's convention - no Ziggy installed). Some already resolve to
 * converted Inertia pages (Tickets/Computer|Printer|Telephone/*), the rest
 * are still Blade - Inertia's <Link> already does "already-Inertia -> SPA
 * transition, still-Blade -> normal full page load" automatically, so no
 * card had to wait for its target category to be converted first.
 *
 * The 5th card, "Vor Ort Termin" (per-city next-visit dates backed by
 * Standortbesuch), was removed at your explicit request (2026-09-18) - the
 * card, its VISIT_CITIES data, the visitDates prop, and
 * TicketController@index's Standortbesuch query/date computation are all
 * gone. The Standortbesuch model/table and its other usages elsewhere in
 * TicketController (unrelated to this page) were deliberately left alone.
 *
 * The news popup + scrolling news bar (news.popup.checks / newsbar.check)
 * are a real, narrow feature of this one page (grepped the old app - the
 * only other place they appear is the separate, out-of-scope
 * video_index.blade.php) - reproduced via plain axios against the existing
 * JSON endpoints, matching this app's "no useForm for non-CRUD AJAX"
 * convention. Worth noting for whoever touches NewsController next:
 * news_check()'s `News::find(1)->get()` doesn't do what it looks like -
 * Eloquent forwards the undefined ->get() call to a *fresh* query builder
 * with no id filter, so it returns every News row, not just id 1; result[0]
 * ends up being whichever row sorts first by default ordering. Preserved
 * as-is (reading result[0], same as the old jQuery) - not this page's bug
 * to fix.
 *
 * The video modal for "Ticket-Erstellung Video Anschauen" (inbox3.mp4) is
 * reproduced. A second modal (id="overall", smt3.mp4) existed in the old
 * markup with no button or link anywhere that ever opened it - dropped as
 * dead markup rather than ported.
 */

type LinkItem = { lines: string[]; href: string };
type Section = { title: string; color: string; links: LinkItem[] };
type CardDef = { title: string; sections: Section[] };

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [{ title: 'IT-Tickets', href: '/ticket.index' }];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const CARDS: CardDef[] = [
    {
        title: 'Neu - Anforderungen',
        sections: [
            { title: 'Softwareinstallation', color: 'text-emerald-600', links: [{ lines: ['neue Software benötigt'], href: '/ticket.software_install' }] },
            {
                title: 'Hardwarebedarf',
                color: 'text-sky-600',
                links: [
                    { lines: ['Maus | Tastatur | Kopfhörer', 'Webcam | Lautsprecher'], href: '/ticket.peripheral_request' },
                    { lines: ['PC | Laptop | Tablet | Telefon', 'Drucker | Beamer | Scanner'], href: '/ticket.hardware_request' },
                ],
            },
            {
                title: 'Benutzer',
                color: 'text-purple-600',
                links: [
                    { lines: ['Neuer Mitarbeiter'], href: '/ticket.employee' },
                    { lines: ['Neuer Teilnehmer'], href: '/ticket.participant' },
                ],
            },
        ],
    },
    {
        title: 'Probleme & Fehlermeldungen',
        sections: [
            { title: 'Software', color: 'text-emerald-600', links: [{ lines: ['Funktioniert nicht | Aktivieren'], href: '/ticket.software_error' }] },
            {
                title: 'PC',
                color: 'text-sky-600',
                links: [
                    {
                        lines: [
                            'Geht nicht an | Blue / Black Screen',
                            'Webcam | Headset | Lautsprecher',
                            'Tastatur | Maus',
                            'Sehr langsam',
                            'Netzwerkzugriff langsam',
                            'lautes Lüftergeräusch',
                            'Keine Netzlaufwerke',
                        ],
                        href: '/ticket.pc_problems',
                    },
                ],
            },
            { title: 'Drucker', color: 'text-indigo-600', links: [{ lines: ['Druckt nicht', 'Defekt'], href: '/ticket.errors' }] },
            { title: 'Scanner', color: 'text-indigo-600', links: [{ lines: ['Scannt nicht', 'Scans nicht im Scan Ordner'], href: '/ticket.scanner' }] },
        ],
    },
    {
        title: 'Probleme & Fehlermeldungen',
        sections: [
            {
                title: 'Telefon',
                color: 'text-amber-600',
                links: [{ lines: ['Keine Anrufe möglich', 'Keine Verbindung', 'Defekt'], href: '/ticket.tel_problems' }],
            },
            { title: 'Beamer', color: 'text-indigo-600', links: [{ lines: ['Kein Signal', 'Flackern | Lampe defekt'], href: '/ticket.projector_problems' }] },
            { title: 'Benutzer', color: 'text-purple-600', links: [{ lines: ['Anmeldeprobleme'], href: '/ticket.users_loginProblem' }] },
            {
                title: 'Web',
                color: 'text-red-600',
                links: [
                    { lines: ['Terminal TN'], href: '/ticket.terminal_tn' },
                    { lines: ['Big Blue Button'], href: '/ticket.bbb' },
                    { lines: ['Vtiger'], href: '/ticket.vtiger' },
                    { lines: ['FirmenVZ'], href: '/ticket.firmenvz' },
                    { lines: ['SMT'], href: '/ticket.smt' },
                ],
            },
        ],
    },
    {
        title: 'Services & Einrichten',
        sections: [
            {
                title: 'Drucker & Scanner',
                color: 'text-indigo-600',
                links: [
                    { lines: ['Druckerinstallation | Einrichten'], href: '/ticket.printer' },
                    { lines: ['Scannerinstallation | Einrichten'], href: '/ticket.scanner.new' },
                    { lines: ['Standort ändern'], href: '/ticket.printer_changes_location' },
                ],
            },
            {
                title: 'Benutzer',
                color: 'text-purple-600',
                links: [
                    { lines: ['Namensänderung'], href: '/ticket.users_namechange' },
                    { lines: ['Email Weiterleiten'], href: '/ticket.forward' },
                ],
            },
            {
                title: 'Telefon',
                color: 'text-amber-600',
                links: [
                    { lines: ['Standort ändern'], href: '/ticket.tel_changes_location' },
                    { lines: ['Name ändern'], href: '/ticket.tel_changes_name' },
                    { lines: ['Nummer ändern'], href: '/ticket.tel_changes_number' },
                ],
            },
            { title: 'PCs', color: 'text-blue-600', links: [{ lines: ['Standort ändern'], href: '/ticket.pc_changes_location' }] },
        ],
    },
];

const newsPopup = ref<{ title: string; body: string } | null>(null);
const newsBarText = ref<string | null>(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/news.popup.checks');
        if (data?.[0]?.isPublished === 'on') {
            newsPopup.value = { title: data[0].title, body: data[0].body };
        }
    } catch {
        // matches the old page's silent-fail behavior - no error handler there either
    }

    try {
        const { data } = await axios.get('/newsbar.check');
        if (data?.[0]?.isNewsBar === 'on') {
            newsBarText.value = data[0].name;
        }
    } catch {
        //
    }
});

const showVideo = ref(false);
const videoEl = ref<HTMLVideoElement | null>(null);

function closeVideo() {
    showVideo.value = false;
    videoEl.value?.pause();
}
</script>

<template>
    <Head title="Ticketanfrage" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div v-if="newsBarText" class="bg-primary/10 text-primary marquee-track rounded-md border px-4 py-2 text-sm font-medium">
            <span class="marquee-text">{{ newsBarText }}</span>
        </div>

        <h2 class="text-center text-xl font-semibold">Ticketanfrage</h2>

        <div class="flex justify-center">
            <button
                type="button"
                class="border-input hover:bg-muted/40 rounded-md border px-4 py-2 text-sm"
                @click="showVideo = true"
            >
                Ticket-Erstellung Video Anschauen
            </button>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div v-for="(card, i) in CARDS" :key="i" class="bg-card text-card-foreground flex flex-col rounded-xl border shadow-sm">
                <div class="text-primary border-b px-4 py-3 text-center font-semibold">{{ card.title }}</div>
                <div class="flex flex-col divide-y">
                    <div v-for="(section, j) in card.sections" :key="j" class="flex flex-col gap-2 px-4 py-3">
                        <h3 class="text-center text-sm font-semibold underline" :class="section.color">{{ section.title }}</h3>
                        <div class="flex flex-col items-center gap-2">
                            <Link
                                v-for="(link, k) in section.links"
                                :key="k"
                                :href="link.href"
                                class="hover:text-primary text-foreground/80 text-center text-sm leading-snug"
                            >
                                <span v-for="(line, l) in link.lines" :key="l" class="block">{{ line }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Transition enter-active-class="transition-opacity" leave-active-class="transition-opacity" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="showVideo" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeVideo">
                <div class="bg-card w-full max-w-3xl rounded-xl border p-4 shadow-lg">
                    <div class="mb-2 flex justify-end">
                        <button type="button" class="text-muted-foreground hover:text-foreground text-sm" @click="closeVideo">Schließen ✕</button>
                    </div>
                    <video ref="videoEl" controls preload="auto" class="w-full rounded-md">
                        <source src="/images/admin_images/inbox3.mp4" type="video/mp4" />
                    </video>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="transition-opacity" leave-active-class="transition-opacity" enter-from-class="opacity-0" leave-to-class="opacity-0">
            <div v-if="newsPopup" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="newsPopup = null">
                <div class="bg-card text-primary w-full max-w-lg rounded-xl border p-6 shadow-lg">
                    <div class="mb-2 flex justify-end">
                        <button type="button" class="text-muted-foreground hover:text-foreground text-sm" @click="newsPopup = null">✕</button>
                    </div>
                    <h3 class="mb-3 text-center text-lg font-semibold">{{ newsPopup?.title }}</h3>
                    <div class="text-foreground text-sm" v-html="newsPopup?.body"></div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.marquee-track {
    overflow: hidden;
}

.marquee-text {
    display: inline-block;
    white-space: nowrap;
    padding-left: 100%;
    animation: marquee 18s linear infinite;
}

.marquee-track:hover .marquee-text {
    animation-play-state: paused;
}

@keyframes marquee {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-100%);
    }
}
</style>
