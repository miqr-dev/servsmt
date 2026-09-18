<script setup lang="ts">
// Shared "left card" on every Handwerk ticket-creation form (converted from
// resources/views/handwerk/layout/submitter.blade.php, @include'd by all 6
// old Blade forms). Binds directly into the parent's Inertia useForm() object
// via the `form` prop so every field posts under the same names
// HandwerkController@form_store_handwerk already expects.

type SubmitterUser = {
    id: number;
    username: string;
    ort: string | null;
    strasse: string | null;
    tel: string | null;
};

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isException: boolean;
    isSuperAdmin: boolean;
    form: Record<string, unknown>;
}>();

const emit = defineEmits<{ standortChanged: [city: string] }>();

const STANDORTE = ['Dresden', 'Erfurt', 'Suhl', 'Leipzig', 'Chemnitz', 'Berlin', 'Döbeln', 'Riesa'];

function onStandortChange(event: Event) {
    const city = (event.target as HTMLSelectElement).value;
    emit('standortChanged', city);
}
</script>

<template>
    <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm lg:col-span-1">
        <div class="grid grid-cols-2 gap-3">
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Erstellt von</label>
                <input
                    type="text"
                    readonly
                    :value="user.username"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Erstellt Am</label>
                <input
                    type="text"
                    readonly
                    :value="now"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Standort</label>
                <input
                    type="text"
                    readonly
                    :value="user.ort"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Adresse</label>
                <input
                    type="text"
                    readonly
                    :value="user.strasse"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>

            <div class="col-span-2 flex flex-col gap-1">
                <label class="text-sm font-medium">Priorität</label>
                <select
                    v-model="form.priority"
                    class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                >
                    <template v-if="isSuperAdmin">
                        <option value="1">Niedrig</option>
                        <option value="2">Normal</option>
                        <option value="3">Hoch</option>
                    </template>
                    <option v-else value="2">Normal</option>
                </select>
            </div>

            <div class="col-span-2 flex flex-col gap-1">
                <label class="text-sm font-medium">Telefon</label>
                <input
                    type="text"
                    readonly
                    :value="user.tel"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>

            <div v-if="isException" class="col-span-2 flex flex-col gap-1">
                <label class="text-sm font-medium">Standort ändern</label>
                <select
                    v-model="form.submitter_standort_exception"
                    class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                    @change="onStandortChange"
                >
                    <option v-for="city in STANDORTE" :key="city" :value="city">{{ city }}</option>
                </select>
            </div>
        </div>

        <p class="text-muted-foreground mt-3 text-xs">
            <span style="color: #004873">✦</span> Pflichtfeld
        </p>
    </div>
</template>
