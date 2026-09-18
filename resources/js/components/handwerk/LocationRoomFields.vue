<script setup lang="ts">
import type { LocationOption } from '@/composables/useHandwerkLocations';

// Shared "Standort / Raum / Raum-Ort" fields for the Handwerk creation
// forms. The Standort -> Raum cascade (populating the room <select> from the
// chosen location's `invrooms`) mirrors the jQuery version every old form
// duplicated; `roomsFor` comes from useHandwerkLocations().

withDefaults(
    defineProps<{
        form: Record<string, unknown>;
        locations: LocationOption[];
        roomsFor: (locationId: unknown) => { id: number; rname: string; altrname: string | null }[];
        showRoomSelect?: boolean;
        requireCustomRoom?: boolean;
    }>(),
    { showRoomSelect: true, requireCustomRoom: false },
);
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium">Standort *</label>
            <select
                v-model="(form.location_id as string)"
                required
                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
            >
                <option value="">Standort...</option>
                <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.address }}</option>
            </select>
        </div>

        <div v-if="showRoomSelect" class="flex flex-col gap-1">
            <label class="text-sm font-medium">Raum</label>
            <select
                v-model="(form.room_id as string)"
                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
            >
                <option value="">Raum...</option>
                <option v-for="room in roomsFor(form.location_id)" :key="room.id" :value="room.id">
                    {{ room.rname }}<template v-if="room.altrname"> ({{ room.altrname }})</template>
                </option>
            </select>
            <p class="text-muted-foreground text-xs">
                Falls der Raum nicht im Dropdown-Menü enthalten ist, verwenden Sie die benutzerdefinierte Eingabe
                daneben.
            </p>
        </div>

        <div class="flex flex-col gap-1" :class="{ 'sm:col-span-2': !showRoomSelect }">
            <label class="text-sm font-medium">Raum / Ort{{ requireCustomRoom ? ' *' : '' }}</label>
            <input
                v-model="(form.custom_room as string)"
                type="text"
                :required="requireCustomRoom"
                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
            />
        </div>
    </div>

    <p v-if="!showRoomSelect" class="text-muted-foreground mt-2 text-xs">
        Falls Stadt oder Adresse nicht im Dropdown-Menü vorhanden sind, kontaktieren Sie bitte die IT-Abteilung.
    </p>
</template>
