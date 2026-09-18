<script setup lang="ts">
// Shared "left card" on the Korso ticket-creation forms (converted from
// resources/views/korso/layout/submitter.blade.php, @include'd by all 3 old
// Blade forms - Zertifizierung/Printmarketing/Onlinemarketing). This is a
// separate component from components/handwerk/SubmitterCard.vue: the two
// modules' submitter partials differ in real ways, not just naming -
// Korso's hides the Erstellt-von/Erstellt-am/Standort/Adresse fields
// entirely (the old markup wrapped them in `d-none` rows; the `now` value
// was even rendered into an `<input>` with no `name` attribute, so it was
// never posted either - those fields exist only as pre-filled hidden values
// on `form`, not as visible inputs here) and adds a "Einreichen als"
// Persönlich/Sekretariat radio that Handwerk's forms don't have. Korso's
// "Standort ändern" field also isn't gated behind `isException` the way
// Handwerk's is - that gate was commented out in the old markup
// (`<!-- @if($isException) -->`), so it's unconditionally shown here.
//
// Binds directly into the parent's Inertia useForm() object via the `form`
// prop, same convention as components/handwerk/SubmitterCard.vue.

defineProps<{
    form: Record<string, unknown>;
    tel: string | null;
    sekGroup: { id: number; name: string } | null;
}>();

const emit = defineEmits<{ standortChanged: [] }>();

const STANDORTE = ['Dresden', 'Erfurt', 'Suhl', 'Leipzig', 'Chemnitz', 'Berlin', 'Döbeln', 'Riesa'];
</script>

<template>
    <div class="bg-card text-card-foreground flex flex-col gap-4 rounded-xl border p-4 shadow-sm lg:col-span-1">
        <div v-if="sekGroup" class="flex flex-col gap-2">
            <label class="text-sm font-medium">Einreichen als</label>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="(form.sek_group_id as string)" type="radio" value="" class="text-primary h-4 w-4" />
                Persönlich
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="(form.sek_group_id as string)" type="radio" :value="String(sekGroup.id)" class="text-primary h-4 w-4" />
                Sekretariat ({{ sekGroup.name }})
            </label>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium">Priorität</label>
            <select v-model="(form.priority as string)" class="border-input bg-background h-9 rounded-md border px-3 text-sm">
                <option value="2">Normal</option>
                <option value="3">Hoch</option>
            </select>
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium">Telefon</label>
            <input type="text" readonly :value="tel" class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm" />
        </div>

        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium">Standort ändern</label>
            <select
                v-model="(form.submitter_standort_exception as string)"
                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                @change="emit('standortChanged')"
            >
                <option v-for="city in STANDORTE" :key="city" :value="city">{{ city }}</option>
            </select>
        </div>

        <p class="text-muted-foreground text-xs">
            <span style="color: #65a30d">✦</span> Pflichtfeld
        </p>
    </div>
</template>
