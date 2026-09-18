<script setup lang="ts">
import { computed } from 'vue';
import type { InvRoom, LocationOption, PlaceGroup } from '@/composables/useTicketLocations';

// Shared "Standort -> Raum" cascade for the Tickets creation forms, backed by
// useTicketLocations() (item.listen). Unlike Handwerk's equivalent
// (components/handwerk/LocationRoomFields.vue) this has no "custom_room"
// free-text fallback field, because none of the old tickets/* Blade forms had
// one and TicketController@store has no column to receive it. It also binds
// via v-model:place / v-model:room instead of a shared `form` object, since
// different Tickets forms post these two selects under different field names
// (location_id/room_id for hardwareRequest, printer_place/printer_room for
// printer_in_out, pc_current_place/pc_current_room or
// tel_target_place/tel_target_room for pcChangeLocation's two cascades) -
// letting each page wire it to whichever pair of form fields it needs.
//
// The Standort <select> is grouped by city via <optgroup>, matching every
// old tickets/* Blade form's own jQuery-built select2 markup exactly (one
// optgroup per Place, its address options in API order underneath) - this
// used to render as one flat list, which is what this component was fixed
// for. Being the one shared component every Tickets creation-form page's
// Standort/Raum cascade already goes through, this single fix applies to
// all of them at once - no per-page changes needed beyond passing `places`.

const props = withDefaults(
    defineProps<{
        locations: LocationOption[];
        places: PlaceGroup[];
        roomsFor: (locationId: unknown) => InvRoom[];
        placeLabel?: string;
        roomLabel?: string;
        showRoomSelect?: boolean;
    }>(),
    { placeLabel: 'Standort', roomLabel: 'Raum', showRoomSelect: true },
);

const place = defineModel<string | number>('place', { default: '' });
const room = defineModel<string | number>('room', { default: '' });

const groupedLocations = computed(() =>
    props.places.map((group) => ({
        ...group,
        options: props.locations.filter((loc) => loc.place_id === group.id),
    })),
);
</script>

<template>
    <div class="flex flex-col gap-1">
        <label class="text-sm font-medium">{{ placeLabel }} <span class="text-muted-foreground">*</span></label>
        <select v-model="place" required class="border-input bg-background h-9 rounded-md border px-3 text-sm">
            <option value="">Standort...</option>
            <optgroup v-for="group in groupedLocations" :key="group.id" :label="group.name">
                <option v-for="loc in group.options" :key="loc.id" :value="loc.id">{{ loc.address }}</option>
            </optgroup>
        </select>
    </div>

    <div v-if="showRoomSelect" class="flex flex-col gap-1">
        <label class="text-sm font-medium">{{ roomLabel }} <span class="text-muted-foreground">*</span></label>
        <select v-model="room" required class="border-input bg-background h-9 rounded-md border px-3 text-sm">
            <option value="">Raum...</option>
            <option v-for="r in roomsFor(place)" :key="r.id" :value="r.id">
                {{ r.rname }}<template v-if="r.altrname"> ({{ r.altrname }})</template>
            </option>
        </select>
    </div>
</template>
