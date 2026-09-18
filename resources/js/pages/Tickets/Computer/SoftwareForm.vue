<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import ComputerSelect, { type ComputerOption } from '@/components/tickets/ComputerSelect.vue';
import SubmitterCard, { type SubmitterUser } from '@/components/tickets/SubmitterCard.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from three old Blade pages that all shared the exact same
 * install/activate/sonstiges field sets, just exposed differently:
 *  - resources/views/tickets/computer/softwareinstall.blade.php (solo page,
 *    route /ticket.software_install -> TicketController@softwareInstall)
 *  - resources/views/tickets/computer/softwareerror.blade.php (solo page,
 *    route /ticket.software_error -> TicketController@softwareError)
 *  - resources/views/tickets/computer/softwareRequest.blade.php (all three as
 *    jQuery-swapped tabs under one page, route /ticket.software_request ->
 *    TicketController@softwareRequest; only reachable via
 *    tickets/computer/all.blade.php, not linked from the main tickets index)
 *
 * softwareinstall.blade.php and softwareerror.blade.php were byte-identical
 * to softwareRequest.blade.php's "Software installieren" and "Funktioniert
 * nicht | Aktivieren" tab markup respectively - so all three routes now
 * render this one component, with `variant` deciding whether the tab
 * switcher shows (variant="request") or a single fixed tab is forced
 * (variant="install" / variant="error"), mirroring the
 * Handwerk SimpleTicket.vue precedent for collapsing identical old forms.
 *
 * All three submit to TicketController@store, which reads the same field
 * names (searchcomputer, searchsoftware, software_name, software_reason,
 * notizen) regardless of problem_type.
 */

type Tab = 'install' | 'error' | 'other';

const props = defineProps<{
    variant: 'install' | 'error' | 'request';
    title: string;
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    computers: ComputerOption[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'IT-Tickets', href: '/ticket.index' },
            { title: '#', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

const TAB_LABELS: Record<Tab, string> = {
    install: 'Software installieren',
    error: 'Funktioniert nicht | Aktivieren',
    other: 'Sonstiges',
};

const PROBLEM_TYPES: Record<Tab, string> = {
    install: 'Software Installieren',
    error: 'Aktivieren',
    other: 'Softwareanfrage Sonstiges',
};

const activeTab = ref<Tab | null>(props.variant === 'request' ? null : (props.variant as Tab));
const showTabs = props.variant === 'request';

const SOFTWARE_SUGGESTIONS = ['Teamviewer', 'FireFox', 'Chrome'];

const form = useForm({
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    custom_tel_number: '',
    problem_type: activeTab.value ? PROBLEM_TYPES[activeTab.value] : '',
    searchcomputer: '',
    searchsoftware: '',
    software_name: '',
    software_reason: '',
    notizen: '',
});

function selectTab(tab: Tab) {
    activeTab.value = tab;
    form.problem_type = PROBLEM_TYPES[tab];
    form.searchsoftware = '';
    form.software_name = '';
    form.software_reason = '';
    form.notizen = '';
}

const pageTitle = computed(() => (showTabs && activeTab.value ? `${props.title} — ${TAB_LABELS[activeTab.value]}` : props.title));

function submit() {
    form.post('/form_store');
}
</script>

<template>
    <Head :title="pageTitle" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">{{ title }}</h2>

        <div v-if="showTabs" class="flex flex-wrap justify-center gap-3">
            <button
                v-for="tab in (['install', 'error', 'other'] as Tab[])"
                :key="tab"
                type="button"
                class="rounded-md border px-4 py-2 text-sm"
                :class="activeTab === tab ? 'bg-primary text-primary-foreground border-primary' : 'border-input hover:bg-muted/40'"
                @click="selectTab(tab)"
            >
                {{ TAB_LABELS[tab] }}
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
                    <ComputerSelect v-model="form.searchcomputer" :computers="computers" />

                    <div v-if="activeTab !== 'other'" class="flex flex-col gap-1">
                        <label class="text-sm font-medium">
                            Welche App <span v-if="activeTab === 'error'" class="text-muted-foreground">*</span>
                        </label>
                        <input
                            v-model="form.searchsoftware"
                            type="text"
                            list="software_suggestions"
                            :required="activeTab === 'error'"
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                        />
                        <datalist id="software_suggestions">
                            <option v-for="s in SOFTWARE_SUGGESTIONS" :key="s" :value="s" />
                        </datalist>
                        <p class="text-muted-foreground text-xs">App nicht in der Liste? Einfach den Namen eintragen.</p>
                    </div>
                </div>

                <template v-if="activeTab === 'install'">
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">Link (falls verfügbar)</label>
                        <input v-model="form.software_name" type="text" class="border-input bg-background h-9 rounded-md border px-3 text-sm" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-sm font-medium">
                            Warum benötigen Sie die Software <span class="text-muted-foreground">*</span>
                        </label>
                        <input
                            v-model="form.software_reason"
                            type="text"
                            required
                            class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                        />
                    </div>
                </template>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">
                        {{ activeTab === 'other' ? 'Bitte beschreiben Sie das Problem / Anfrage' : 'Notizen' }}
                    </label>
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
