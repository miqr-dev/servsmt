import axios from 'axios';
import { ref } from 'vue';

// Shared cascading Standort -> Raum data source for the Handwerk creation
// forms, ported from the jQuery `$.ajax` calls against HandwerkController@room_list
// that every one of the 6 old Blade forms duplicated verbatim.

export type InvRoom = { id: number; rname: string; altrname: string | null };
export type LocationOption = { id: number; address: string; invrooms: InvRoom[] };

export function useHandwerkLocations() {
    const locations = ref<LocationOption[]>([]);
    const placeName = ref('');

    async function load(city?: string) {
        const url = city ? `/room/list/${city}` : '/room/list';
        const { data } = await axios.get<{ locations: LocationOption[]; place: { pnname: string } | null }>(url);

        locations.value = data.locations ?? [];
        placeName.value = data.place?.pnname ?? '';
    }

    function roomsFor(locationId: number | string): InvRoom[] {
        const location = locations.value.find((l) => l.id === Number(locationId));

        return location?.invrooms ?? [];
    }

    return { locations, placeName, load, roomsFor };
}
