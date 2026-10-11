<script setup lang="ts">
import AdRoomPicker from '@/components/ad/AdRoomPicker.vue';
import type { AdLocationData } from '@/composables/useAdLocations';

// "Adresse / Etage / Raum / Raum-Ort" for the Handwerk creation forms, from
// AD (AD switch step 4, 2026-10-09; replaces LocationRoomFields.vue and the
// old room_list cascade). The Standort is the submitter's city (or the one
// picked under "Standort ändern"), so its select only shows when that city
// has no AD Standort. Rooms include the ones added in the app (kitchen, WC,
// ...). Posts ad_room_id = Raum, else Etage, else Adresse; the server adds
// the old location_id / room_id where a link exists. custom_room stays the
// free-text fallback.

withDefaults(
    defineProps<{
        form: Record<string, unknown>;
        data: AdLocationData;
        loading?: boolean;
        city: string | null;
        /** the user's street: preselects the matching AD address (own city only) */
        address?: string | null;
        showRoomSelect?: boolean;
        requireCustomRoom?: boolean;
    }>(),
    { loading: false, address: null, showRoomSelect: true, requireCustomRoom: false },
);
</script>

<template>
    <div class="grid gap-4 sm:grid-cols-2">
        <AdRoomPicker
            v-model:ou="form.ad_room_id as number | string"
            :data="data"
            :loading="loading"
            :city="city"
            :address="address"
            lock-city
            :level="showRoomSelect ? 'raum' : 'adresse'"
        />

        <div class="flex flex-col gap-1">
            <label class="text-sm font-medium">Raum / Ort{{ requireCustomRoom ? ' *' : '' }}</label>
            <input
                v-model="form.custom_room as string"
                type="text"
                :required="requireCustomRoom"
                class="border-input bg-background h-9 rounded-md border px-3 text-sm"
            />
            <p v-if="showRoomSelect" class="text-muted-foreground text-xs">Raum nicht in der Liste? Hier eintragen.</p>
        </div>
    </div>

    <p v-if="!showRoomSelect" class="text-muted-foreground mt-2 text-xs">
        Falls Stadt oder Adresse nicht im Dropdown-Menü vorhanden sind, kontaktieren Sie bitte die IT-Abteilung.
    </p>
</template>
