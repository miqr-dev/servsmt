import axios from 'axios';
import { ref } from 'vue';

// Shared cascading Standort -> Raum data source for the Tickets (IT
// helpdesk) creation forms, ported from the jQuery `$.ajax` calls against
// `item.listen` (InvAbItemController@listen) that several of the old
// tickets/computer, tickets/printer and tickets/users Blade forms
// duplicated verbatim (hardwareRequest, printer_in_out, pcChangeLocation, ...).
//
// Shape-compatible with useHandwerkLocations()'s LocationOption/InvRoom, so
// the existing components/handwerk/LocationRoomFields.vue can be reused here
// unchanged - only the data source (this composable) differs.
//
// `places` (city name -> place_id) used to be fetched and discarded - every
// consuming page's Standort <select> rendered as one flat list. The old
// jQuery actually grouped it by city via <optgroup> (one per place, in the
// order item.listen returns them, addresses in each group in API order -
// not re-sorted). `places` is now kept and exposed so
// components/tickets/LocationRoomFields.vue can reproduce that grouping.

export type InvRoom = { id: number; rname: string; altrname: string | null };
export type LocationOption = { id: number; place_id: number; address: string; invrooms: InvRoom[] };
export type PlaceGroup = { id: number; name: string };

export function useTicketLocations() {
    const locations = ref<LocationOption[]>([]);
    const places = ref<PlaceGroup[]>([]);

    async function load() {
        const { data } = await axios.get<{ locations: LocationOption[]; places: Record<string, number> }>(
            '/item/listen',
        );

        locations.value = data.locations ?? [];
        places.value = Object.entries(data.places ?? {}).map(([name, id]) => ({ id, name }));
    }

    function roomsFor(locationId: number | string): InvRoom[] {
        const location = locations.value.find((l) => l.id === Number(locationId));

        return location?.invrooms ?? [];
    }

    return { locations, places, load, roomsFor };
}
