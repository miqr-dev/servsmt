<script setup lang="ts">
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import SimpleFilePicker from '@/components/korso/SimpleFilePicker.vue';

/**
 * "KI-Assistent (CustomGPT) Lastenheft" 4-step wizard, opened when the
 * Onlinemarketing creation form's "ChatGPT-Projektvorschläge" radio option
 * is picked - ported from the old page's Bootstrap modal + jQuery step
 * navigation (resources/views/korso/Onlinemarketing/onlinemarketing.blade.php).
 * Field names match KorsoController@form_store_korso's `chatgpt_*` inputs
 * exactly; this is the only one of the 3 Korso creation forms that ever
 * populates them.
 *
 * Binds directly into the parent's Inertia useForm() object (same
 * convention as SubmitterCard/ItemCheckboxGroup), so "Formular übernehmen"
 * just closes the modal - the fields it filled are already on `form`.
 */

const props = defineProps<{
    open: boolean;
    form: Record<string, unknown>;
}>();
const emit = defineEmits<{ 'update:open': [boolean]; saved: [] }>();

const step = ref(1);
const STEP_LABELS = ['Use Case', 'Ziele der KI-Einführung', 'Beschreibung des Prozesses', 'Sonstige Anforderungen'];

const STEP_REQUIREMENTS: Record<number, string[]> = {
    1: ['chatgpt_project_name'],
    2: ['chatgpt_introduction_reason', 'chatgpt_goal', 'chatgpt_process_steps'],
    3: ['chatgpt_has_existing_process', 'chatgpt_has_output_examples', 'chatgpt_has_knowledge_base'],
    4: [],
};
const ALL_FIELDS = [1, 2, 3].flatMap((s) => STEP_REQUIREMENTS[s]);

function missingFields(fields: string[]): string[] {
    return fields.filter((f) => {
        const v = props.form[f];
        return v === '' || v === null || v === undefined;
    });
}

function stepForField(field: string): number {
    for (const s of [1, 2, 3, 4]) {
        if (STEP_REQUIREMENTS[s].includes(field)) return s;
    }
    return 4;
}

function goToStep(target: number) {
    if (target > step.value) {
        const missing = missingFields(STEP_REQUIREMENTS[step.value] ?? []);
        if (missing.length) {
            toast.error('Bitte füllen Sie zuerst alle Pflichtfelder dieses Schritts aus.');
            return;
        }
    }
    step.value = target;
}

const showOutputExamples = computed(() => props.form.chatgpt_has_output_examples === '1');
const showKnowledgeBase = computed(() => props.form.chatgpt_has_knowledge_base === '1');

function save() {
    const missing = missingFields(ALL_FIELDS);
    if (missing.length) {
        toast.error('Bitte füllen Sie alle Pflichtfelder des ChatGPT-Projektformulars aus.');
        goToStep(stepForField(missing[0]));
        return;
    }
    emit('saved');
    emit('update:open', false);
}

function cancel() {
    emit('update:open', false);
}
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="px-6 py-4 text-white" style="background: linear-gradient(135deg, #661421, #7f1d2d)">
                <h3 class="text-lg font-semibold">KI-Assistent (CustomGPT) Lastenheft</h3>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-4">
                <p class="text-muted-foreground mb-4 text-sm">
                    Bitte füllen Sie alle Abschnitte für Ihren gewünschten KI-Assistenten aus.
                </p>

                <div class="mb-4 flex flex-wrap gap-2">
                    <button
                        v-for="s in [1, 2, 3, 4]"
                        :key="s"
                        type="button"
                        class="rounded-full border px-3 py-1.5 text-xs font-semibold"
                        :class="step === s ? 'text-white' : ''"
                        :style="step === s ? 'background:#661421;border-color:#661421' : 'color:#661421;border-color:#d7b0b6'"
                        @click="goToStep(s)"
                    >
                        {{ s }}. {{ STEP_LABELS[s - 1] }}
                    </button>
                </div>

                <!-- Step 1 -->
                <div v-if="step === 1" class="flex flex-col gap-2">
                    <label class="text-sm font-semibold">
                        Definiere deinen Anwendungsfall in 3-4 Wörtern („Projektname“) <span class="text-red-600">*</span>
                    </label>
                    <textarea
                        v-model="(form.chatgpt_project_name as string)"
                        rows="3"
                        class="border-input bg-background rounded-md border px-3 py-2 text-sm"
                    ></textarea>
                    <button
                        type="button"
                        class="mt-2 self-end rounded-md px-4 py-2 text-sm font-semibold text-white"
                        style="background: #661421"
                        @click="goToStep(2)"
                    >
                        Weiter
                    </button>
                </div>

                <!-- Step 2 -->
                <div v-else-if="step === 2" class="flex flex-col gap-4">
                    <div class="rounded-lg border">
                        <div class="bg-muted/40 border-b px-3 py-2 text-sm font-semibold">
                            Engpässe und „Breaking Points“ im bestehenden Prozess (Anlass der Einführung)
                        </div>
                        <textarea v-model="(form.chatgpt_introduction_reason as string)" rows="5" class="w-full px-3 py-2 text-sm"></textarea>
                        <div class="bg-muted/20 border-t p-3">
                            <SimpleFilePicker v-model="(form.chatgpt_introduction_attachments as File[])" label="Anhänge nur für dieses Feld" />
                        </div>
                    </div>

                    <div class="rounded-lg border">
                        <div class="bg-muted/40 border-b px-3 py-2 text-sm font-semibold">
                            Ziele die mit der Einführung des KI-Assistenten verfolgt werden
                        </div>
                        <textarea v-model="(form.chatgpt_goal as string)" rows="5" class="w-full px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="rounded-lg border">
                        <div class="bg-muted/40 border-b px-3 py-2 text-sm font-semibold">
                            Kurzbeschreibung der einzelnen Schritte (Deine „Vorstellung“)
                        </div>
                        <textarea v-model="(form.chatgpt_process_steps as string)" rows="5" class="w-full px-3 py-2 text-sm"></textarea>
                        <div class="bg-muted/20 border-t p-3">
                            <SimpleFilePicker v-model="(form.chatgpt_process_attachments as File[])" label="Anhänge nur für dieses Feld" />
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" class="border-border rounded-md border px-4 py-2 text-sm" @click="goToStep(1)">Zurück</button>
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm font-semibold text-white"
                            style="background: #661421"
                            @click="goToStep(3)"
                        >
                            Weiter
                        </button>
                    </div>
                </div>

                <!-- Step 3 -->
                <div v-else-if="step === 3" class="flex flex-col gap-4">
                    <p class="text-sm font-semibold">IST-Zustand und vorhandenes Material</p>
                    <p class="text-muted-foreground text-xs">
                        Wichtig: Je mehr Beispiele bzw. konkrete Vorstellungen des Ergebnisses Du hast, desto besser wird der
                        KI-Assistent.
                    </p>

                    <table class="w-full border-collapse text-sm">
                        <tbody>
                            <tr class="border-b">
                                <td class="p-2">Existiert für den Use Case bereits ein Prozess?</td>
                                <td class="p-2">
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(form.chatgpt_has_existing_process as string)" type="radio" value="0" class="text-primary h-4 w-4" />
                                        Nein
                                    </label>
                                </td>
                                <td class="p-2">
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(form.chatgpt_has_existing_process as string)" type="radio" value="1" class="text-primary h-4 w-4" />
                                        Ja
                                    </label>
                                </td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2">Gibt es Beispiele für ein perfektes Ergebnis?</td>
                                <td class="p-2">
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(form.chatgpt_has_output_examples as string)" type="radio" value="0" class="text-primary h-4 w-4" />
                                        Nein
                                    </label>
                                </td>
                                <td class="p-2">
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(form.chatgpt_has_output_examples as string)" type="radio" value="1" class="text-primary h-4 w-4" />
                                        Ja (unten einfügen)
                                    </label>
                                </td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2">Gibt es eine Knowledge Base / gespeichertes Wissen?</td>
                                <td class="p-2">
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(form.chatgpt_has_knowledge_base as string)" type="radio" value="0" class="text-primary h-4 w-4" />
                                        Nein
                                    </label>
                                </td>
                                <td class="p-2">
                                    <label class="flex items-center gap-1.5">
                                        <input v-model="(form.chatgpt_has_knowledge_base as string)" type="radio" value="1" class="text-primary h-4 w-4" />
                                        Ja (unten einfügen)
                                    </label>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="showOutputExamples" class="rounded-lg border">
                        <div class="bg-muted/40 border-b px-3 py-2 text-sm font-semibold">Beispiele für den perfekten Output (falls vorhanden)</div>
                        <textarea v-model="(form.chatgpt_output_examples as string)" rows="6" class="w-full px-3 py-2 text-sm"></textarea>
                        <div class="bg-muted/20 border-t p-3">
                            <SimpleFilePicker v-model="(form.chatgpt_output_attachments as File[])" label="Anhänge nur für dieses Feld" />
                        </div>
                    </div>

                    <div v-if="showKnowledgeBase" class="rounded-lg border">
                        <div class="bg-muted/40 border-b px-3 py-2 text-sm font-semibold">Vorhandenes Wissen / Knowledge Bases (falls vorhanden)</div>
                        <textarea v-model="(form.chatgpt_knowledge_base as string)" rows="6" class="w-full px-3 py-2 text-sm"></textarea>
                        <div class="bg-muted/20 border-t p-3">
                            <SimpleFilePicker v-model="(form.chatgpt_knowledge_attachments as File[])" label="Anhänge nur für dieses Feld" />
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" class="border-border rounded-md border px-4 py-2 text-sm" @click="goToStep(2)">Zurück</button>
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm font-semibold text-white"
                            style="background: #661421"
                            @click="goToStep(4)"
                        >
                            Weiter
                        </button>
                    </div>
                </div>

                <!-- Step 4 -->
                <div v-else class="flex flex-col gap-2">
                    <label class="text-sm font-semibold">Ergänzende Anforderungen an den KI-Assistenten</label>
                    <div class="rounded-lg border">
                        <div class="bg-muted/40 border-b px-3 py-2 text-sm font-semibold">Sonstige Anforderungen</div>
                        <textarea v-model="(form.chatgpt_additional_requirements as string)" rows="8" class="w-full px-3 py-2 text-sm"></textarea>
                        <div class="bg-muted/20 border-t p-3">
                            <SimpleFilePicker v-model="(form.chatgpt_additional_attachments as File[])" label="Anhänge nur für dieses Feld" />
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" class="border-border rounded-md border px-4 py-2 text-sm" @click="goToStep(3)">Zurück</button>
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm font-semibold text-white"
                            style="background: #661421"
                            @click="save"
                        >
                            Formular übernehmen
                        </button>
                    </div>
                </div>
            </div>

            <div class="border-t px-6 py-3">
                <button type="button" class="border-border rounded-md border px-4 py-2 text-sm" @click="cancel">Abbrechen</button>
            </div>
        </div>
    </div>
</template>
