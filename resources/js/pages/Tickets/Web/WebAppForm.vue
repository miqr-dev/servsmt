<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from four resources/views/tickets/web/* Blade forms, all posting
 * to TicketController@store (/form_store). They were the same jQuery
 * tab-swap page four times over, differing only in title, tab set and the
 * column-name prefix of the two fields, so one component with an `app` prop:
 *
 *   app        route              title             prefix   tabs
 *   bbb        /ticket.bbb        Big Blue Button   bbb_     Neuer Benutzer / Funktionsanfragen / Fehlermeldung
 *   vtiger     /ticket.vtiger     VTiger            vtiger_  Funktionsanfragen / Fehlermeldung
 *   firmenvz   /ticket.firmenvz   FirmenVZ          firmen_  Funktionsanfragen / Fehlermeldung
 *   smt        /ticket.smt        SMT               smt_     Funktionsanfragen / Fehlermeldung
 *
 * Every tab has a required Betreff (<prefix>subject) and a Beschreibung;
 * "Neuer Benutzer" and "Fehlermeldung" also have an optional Benutzername
 * (<prefix>username), "Funktionsanfragen" doesn't. problem_type values are
 * copied verbatim from the old hidden inputs (e.g. "BBB Neuer Benutzer",
 * "FirmenVZ Fehlermeldung") - Tickets/Show.vue's ticketViewFields keys are
 * derived from them, so they must not change. Like the old pages, nothing
 * is shown until a tab is picked, and switching tabs starts from empty
 * fields. The form holds generic subject/username and form.transform()
 * renames them to the prefixed column names on submit.
 *
 * Summernote Beschreibung -> plain textarea, as on every converted form.
 */

type AppKey = 'bbb' | 'vtiger' | 'firmenvz' | 'smt';
type TabDef = { key: string; label: string; problemType: string; hasUsername: boolean };

const APPS: Record<AppKey, { title: string; prefix: string; tabs: TabDef[] }> = {
    bbb: {
        title: 'Big Blue Button',
        prefix: 'bbb_',
        tabs: [
            { key: 'new_user', label: 'Neuer Benutzer', problemType: 'BBB Neuer Benutzer', hasUsername: true },
            { key: 'requests', label: 'Funktionsanfragen', problemType: 'BBB Funktionsanfragen', hasUsername: false },
            { key: 'errors', label: 'Fehlermeldung', problemType: 'BBB Fehlermeldung', hasUsername: true },
        ],
    },
    vtiger: {
        title: 'VTiger',
        prefix: 'vtiger_',
        tabs: [
            { key: 'requests', label: 'Funktionsanfragen', problemType: 'Vtiger Funktionsanfragen', hasUsername: false },
            { key: 'errors', label: 'Fehlermeldung', problemType: 'Vtiger Fehlermeldung', hasUsername: true },
        ],
    },
    firmenvz: {
        title: 'FirmenVZ',
        prefix: 'firmen_',
        tabs: [
            { key: 'requests', label: 'Funktionsanfragen', problemType: 'FirmenVZ Funktionsanfragen', hasUsername: false },
            { key: 'errors', label: 'Fehlermeldung', problemType: 'FirmenVZ Fehlermeldung', hasUsername: true },
        ],
    },
    smt: {
        title: 'SMT',
        prefix: 'smt_',
        tabs: [
            { key: 'requests', label: 'Funktionsanfragen', problemType: 'SMT Funktionsanfragen', hasUsername: false },
            { key: 'errors', label: 'Fehlermeldung', problemType: 'SMT Fehlermeldung', hasUsername: true },
        ],
    },
};

const props = defineProps<{
    app: AppKey;
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: 'Web', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const config = APPS[props.app];
const activeTab = ref<TabDef | null>(null);

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: '',
    subject: '',
    username: '',
    notizen: '',
});

function selectTab(tab: TabDef) {
    form.reset('subject', 'username', 'notizen');
    activeTab.value = tab;
    form.problem_type = tab.problemType;
}

const pageTitle = computed(() => (activeTab.value ? `${config.title} — ${activeTab.value.label}` : config.title));

function submit() {
    const hasUsername = activeTab.value?.hasUsername ?? false;
    form
        .transform(({ subject, username, ...rest }) => ({
            ...rest,
            [`${config.prefix}subject`]: subject,
            ...(hasUsername ? { [`${config.prefix}username`]: username } : {}),
        }))
        .post('/form_store');
}
</script>

<template>
    <Head :title="pageTitle" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">{{ config.title }}</h2>

        <div class="flex flex-wrap justify-center gap-3">
            <button
                v-for="tab in config.tabs"
                :key="tab.key"
                type="button"
                class="rounded-md border px-4 py-2 text-sm"
                :class="activeTab?.key === tab.key ? 'bg-primary text-primary-foreground border-primary' : 'border-input hover:bg-muted/40'"
                @click="selectTab(tab)"
            >
                {{ tab.label }}
            </button>
        </div>

        <form v-if="activeTab" class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard
                :user="user"
                :now="now"
                :is-super-admin="isSuperAdmin"
                :available-submitter-users="availableSubmitterUsers"
                :form="form"
            />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Betreff <span class="text-muted-foreground">*</span></label>
                        <input v-model="form.subject" type="text" required class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                    </div>
                    <div v-if="activeTab.hasUsername" class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Benutzername</label>
                        <input v-model="form.username" type="text" class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Beschreibung</label>
                    <textarea v-model="form.notizen" rows="5" class="border-input bg-background rounded-md border px-3 py-2 text-sm"></textarea>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 w-fit items-center self-end rounded-md px-4 text-sm disabled:opacity-60"
                >
                    Einreichen
                </button>
            </div>
        </form>
    </div>
</template>
