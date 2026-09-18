<script setup lang="ts">
import { computed } from 'vue';

// Shared "left card" on every Tickets (IT helpdesk) creation form, converted
// from resources/views/tickets/layout_ticket/submitter.blade.php, which every
// one of the ~30 old Blade creation forms @include'd. Binds directly into the
// parent's Inertia useForm() object via the `form` prop so every field posts
// under the same names TicketController@store already expects.
//
// Unlike Handwerk's submitter card, Super_Admin can search and pick a
// DIFFERENT submitter here (the old <datalist> autocomplete over every user);
// picking one fills in that user's Standort/Adresse/Telefon instead of the
// logged-in admin's own. Everyone else just sees their own details, read-only,
// exactly like Handwerk's card.

export type SubmitterUser = {
    id: number;
    vorname: string | null;
    name: string | null;
    username: string;
    ort: string | null;
    strasse: string | null;
    tel: string | null;
};

const props = defineProps<{
    user: SubmitterUser;
    now: string;
    isSuperAdmin: boolean;
    availableSubmitterUsers: SubmitterUser[];
    form: Record<string, unknown>;
}>();

function fullName(u: SubmitterUser): string {
    return `${u.vorname ?? ''} ${u.name ?? ''}`.trim();
}

function optionLabel(u: SubmitterUser): string {
    return `${fullName(u) || u.username || 'Unbekannt'} (${u.username || 'kein-username'})`;
}

const submitterSearch = computed({
    get: () => {
        if (props.form.submitter === props.user.id) return `${fullName(props.user)} (${props.user.username})`;
        const match = props.availableSubmitterUsers.find((u) => u.id === props.form.submitter);
        return match ? optionLabel(match) : '';
    },
    set: (value: string) => {
        const match = props.availableSubmitterUsers.find((u) => optionLabel(u) === value);
        if (!match) {
            props.form.submitter = '';
            return;
        }
        props.form.submitter = match.id;
        props.form.submitter_standort = match.ort ?? '';
        props.form.submitter_adresse = match.strasse ?? '';
        props.form.tel_number = match.tel ?? '';
    },
});
</script>

<template>
    <div class="bg-card text-card-foreground rounded-xl border p-4 shadow-sm lg:col-span-1">
        <div class="grid grid-cols-2 gap-3">
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Erstellt von</label>
                <template v-if="isSuperAdmin">
                    <input
                        v-model="submitterSearch"
                        type="text"
                        list="submitter_list"
                        required
                        class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                    />
                    <datalist id="submitter_list">
                        <option v-for="u in availableSubmitterUsers" :key="u.id" :value="optionLabel(u)" />
                    </datalist>
                </template>
                <input
                    v-else
                    type="text"
                    readonly
                    :value="`${user.vorname ?? ''} ${user.name ?? ''}`"
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
                    :value="form.submitter_standort"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Adresse</label>
                <input
                    type="text"
                    readonly
                    :value="form.submitter_adresse"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>

            <div class="col-span-2 flex flex-col gap-1">
                <label class="text-sm font-medium">Priorität</label>
                <select v-model="form.priority" class="border-input bg-background h-9 rounded-md border px-3 text-sm">
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
                    :value="form.tel_number"
                    class="border-input bg-muted/40 h-9 rounded-md border px-3 text-sm"
                />
            </div>

            <div class="col-span-2 flex flex-col gap-1">
                <label class="text-sm font-medium">
                    Aktuelle Rufnummer
                    <span class="text-muted-foreground" title="Telefonnummer unter der Sie erreichbar sind">?</span>
                </label>
                <input
                    v-model="(form.custom_tel_number as string)"
                    type="text"
                    class="border-input bg-background h-9 rounded-md border px-3 text-sm"
                />
            </div>
        </div>

        <p class="text-muted-foreground mt-3 text-xs">
            <span style="color: #661421">✦</span> Pflichtfeld
        </p>
    </div>
</template>
