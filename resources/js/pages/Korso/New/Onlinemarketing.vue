<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, h, ref } from 'vue';
import { toast } from 'vue-sonner';
import SubmitterCard from '@/components/korso/SubmitterCard.vue';
import AttachmentPicker from '@/components/korso/AttachmentPicker.vue';
import ChatGptProjectModal from '@/components/korso/onlinemarketing/ChatGptProjectModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

/**
 * Converted from resources/views/korso/Onlinemarketing/onlinemarketing.blade.php
 * (KorsoController@onlinemarketing, route /onlinemarketing). No location or
 * Maßnahme fields here, unlike Zertifizierung/Printmarketing - this form is
 * just a single-select of `onlinemarketingItems` (radio, already truly
 * single-select in the old markup - no dead "uncheck siblings" JS to drop,
 * unlike Zertifizierung's checkbox-based item picker).
 *
 * The "Zugang Chat GPT" item has a sibling "ChatGPT-Projektvorschläge" radio
 * that opens a 4-step wizard (ChatGptProjectModal.vue) collecting the
 * `chatgpt_*` fields KorsoController@form_store_korso conditionally requires
 * when `is_chatgpt_project` is true - this is the only one of the 3 Korso
 * creation forms that ever populates those fields.
 *
 * General attachments accept Office documents here in addition to
 * images/PDFs (unlike Zertifizierung/Printmarketing), so AttachmentPicker's
 * `accept`/`hint` props are overridden.
 */

type SubmitterUser = { id: number; username: string; ort: string | null; strasse: string | null; tel: string | null };
type SekGroup = { id: number; name: string } | null;
type OnlinemarketingItem = { id: number; name: string };

const props = defineProps<{
    user: SubmitterUser;
    sekGroup: SekGroup;
    onlinemarketingItems: OnlinemarketingItem[];
}>();

defineOptions({
    layout: (h_: typeof h, page: unknown) => {
        const breadcrumbs: BreadcrumbItem[] = [
            { title: 'Korso Ticket', href: '/korso' },
            { title: 'Onlinemarketing', href: '#' },
        ];

        return h_(AppLayout, { breadcrumbs }, () => page);
    },
});

function chunk<T>(arr: T[], size: number): T[][] {
    const out: T[][] = [];
    for (let i = 0; i < arr.length; i += size) out.push(arr.slice(i, i + size));
    return out;
}
const itemColumns = computed(() => chunk(props.onlinemarketingItems, 15));

const form = useForm({
    submitter_name: props.user.username,
    submitter: props.user.id,
    submitter_standort: props.user.ort ?? '',
    submitter_adresse: props.user.strasse ?? '',
    submitter_standort_exception: props.user.ort ?? '',
    sek_group_id: '',
    priority: '2',
    tel_number: props.user.tel ?? '',
    problem_type: 'Onlinemarketing',
    onlinemarketing_item: '',
    is_chatgpt_project: false,
    chatgpt_project_name: '',
    chatgpt_introduction_reason: '',
    chatgpt_goal: '',
    chatgpt_process_steps: '',
    chatgpt_has_existing_process: '',
    chatgpt_has_output_examples: '',
    chatgpt_has_knowledge_base: '',
    chatgpt_output_examples: '',
    chatgpt_knowledge_base: '',
    chatgpt_additional_requirements: '',
    chatgpt_introduction_attachments: [] as File[],
    chatgpt_process_attachments: [] as File[],
    chatgpt_output_attachments: [] as File[],
    chatgpt_knowledge_attachments: [] as File[],
    chatgpt_additional_attachments: [] as File[],
    notizen: '',
    attachments: [] as File[],
});

const modalOpen = ref(false);
const chatgptSaved = ref(false);

function onItemChange() {
    chatgptSaved.value = false;
    if (form.onlinemarketing_item === 'chatgpt_project') {
        form.is_chatgpt_project = true;
        modalOpen.value = true;
    } else {
        form.is_chatgpt_project = false;
    }
}

function onChatgptSaved() {
    chatgptSaved.value = true;
}

function submit() {
    if (!form.onlinemarketing_item) {
        toast.error("Bitte wählen Sie eine Option unter 'Was brauchen Sie ?' aus.");
        return;
    }
    if (form.onlinemarketing_item === 'chatgpt_project' && !chatgptSaved.value) {
        toast.error('Bitte vervollständigen Sie zuerst das Formular „ChatGPT-Projektvorschläge“.');
        modalOpen.value = true;
        return;
    }
    form.post('/form_store_korso');
}
</script>

<template>
    <Head title="Onlinemarketing" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h2 class="text-xl font-semibold">Onlinemarketing</h2>

        <form class="grid gap-4 lg:grid-cols-3" @submit.prevent="submit">
            <SubmitterCard :form="form" :tel="user.tel" :sek-group="sekGroup" />

            <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-2">
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium">Was brauchen Sie?</label>
                    <div class="grid gap-x-6 sm:grid-cols-2">
                        <div v-for="column in itemColumns" :key="column[0]?.id">
                            <template v-for="item in column" :key="item.id">
                                <label class="mb-2 flex items-start gap-2 text-sm">
                                    <input
                                        v-model="form.onlinemarketing_item"
                                        type="radio"
                                        :value="String(item.id)"
                                        class="text-primary mt-0.5 h-4 w-4"
                                        @change="onItemChange"
                                    />
                                    <span>
                                        {{ item.name }}
                                        <small v-if="item.name === 'Fehlermeldung Chat GPT'" class="text-muted-foreground block">
                                            bitte Accountname und Screenshot mitsenden
                                        </small>
                                    </span>
                                </label>

                                <label v-if="item.name === 'Zugang Chat GPT'" class="mb-4 flex items-start gap-2 text-sm">
                                    <input
                                        v-model="form.onlinemarketing_item"
                                        type="radio"
                                        value="chatgpt_project"
                                        class="text-primary mt-0.5 h-4 w-4"
                                        @change="onItemChange"
                                    />
                                    <span>
                                        ChatGPT-Projektvorschläge
                                        <span v-if="chatgptSaved" class="text-primary mt-1 block text-xs font-semibold">Formular ausgefüllt.</span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium">Notizen</label>
                    <textarea v-model="form.notizen" rows="5" class="border-input bg-background rounded-md border px-3 py-2 text-sm"></textarea>
                </div>

                <AttachmentPicker
                    v-model="form.attachments"
                    accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                    hint="Bilder, PDFs, Office-Dateien"
                />

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-9 w-fit items-center self-end rounded-md px-4 text-sm disabled:opacity-60"
                >
                    Einreichen
                </button>
            </div>
        </form>

        <ChatGptProjectModal v-model:open="modalOpen" :form="form" @saved="onChatgptSaved" />
    </div>
</template>
