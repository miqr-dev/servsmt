import { Circle, Wrench, CheckCircle2, Users, Pause, MessageCircle, Copy } from '@lucide/vue';
import type { LucideIcon } from '@lucide/vue';

/**
 * Shared "IT-Ticket" (Ticket model) status icon/color/label table -
 * reproduced from usertickets.blade.php's inline @if/@elseif chain on
 * ticket_status_id (id 6 was never an @elseif branch there, only reachable
 * via the trailing @else - kept that gap as-is below). Centralized here
 * rather than duplicated per page since the not-yet-converted admin ticket
 * list (tickettable.blade.php, Tickets batch 2) uses the exact same status
 * set and will need this too.
 */
export const TICKET_STATUS_META: Record<number, { icon: LucideIcon; color: string; label: string }> = {
    1: { icon: Circle, color: '#001B2E', label: 'Nicht begonnen' },
    2: { icon: Wrench, color: '#3490DC', label: 'In Bearbeitung' },
    3: { icon: CheckCircle2, color: '#285D17', label: 'Erledigt' },
    4: { icon: Users, color: '#F9A620', label: 'Wartet auf jemand anderen' },
    5: { icon: Pause, color: '#e3342f', label: 'Zurückgestellt' },
    7: { icon: MessageCircle, color: '#c2410c', label: 'Warten auf Antwort' },
};
const TICKET_STATUS_FALLBACK = { icon: Copy, color: '#285D17', label: 'Duplikat' };

export function ticketStatusMeta(statusId: number | null | undefined) {
    if (statusId != null && TICKET_STATUS_META[statusId]) return TICKET_STATUS_META[statusId];
    return TICKET_STATUS_FALLBACK;
}

/**
 * Ticket.priority_id badge - reproduced from the same file's priority
 * @if/@elseif chain (1=Niedrig, 2=Normal, else=Hoch). Kept separate from
 * Korso's own priority scale (Korso/Dashboard.vue, Korso/Show.vue) even
 * though the numbers mean the same thing on both - Handwerk doesn't show a
 * priority badge in any table at all, so there's no existing single
 * three-way convention to unify into yet.
 */
export function ticketPriorityLabel(priorityId: number): string {
    if (priorityId === 1) return 'Niedrig';
    if (priorityId === 2) return 'Normal';
    return 'Hoch';
}
export function ticketPriorityBadgeClass(priorityId: number): string {
    if (priorityId === 1) return 'bg-blue-100 text-blue-800';
    if (priorityId === 2) return 'bg-green-100 text-green-800';
    return 'bg-red-100 text-red-800';
}

/**
 * TicketStatus id -> badge color, for the Korso ticket queue's status column
 * ($statusColors in the old usertickets.blade.php). Korso shares the same
 * TicketStatus table/ids as Ticket, and this exact mapping already exists
 * once in Korso/Dashboard.vue's local STATUS_BADGE_CLASSES - duplicated here
 * rather than importing from a page component (importing from a .vue page
 * isn't a pattern used anywhere in this codebase) or refactoring
 * Dashboard.vue to use this file instead, which would touch already-shipped,
 * working code for no functional gain.
 */
export const KORSO_STATUS_BADGE_CLASSES: Record<number, string> = {
    1: 'bg-gray-100 text-gray-700', // Nicht begonnen
    2: 'bg-amber-100 text-amber-800', // In Bearbeitung
    3: 'bg-green-100 text-green-800', // Erledigt
    4: 'bg-blue-100 text-blue-800', // Wartet auf jemand anderen
    5: 'bg-slate-800 text-white', // Zurückgestellt
    6: 'bg-red-100 text-red-800', // Duplikat
    7: 'bg-indigo-100 text-indigo-800', // Warten auf Antwort
    8: 'bg-amber-100 text-amber-800', // Wiederhergestellt
};
export function korsoStatusBadgeClass(statusId: number | null | undefined): string {
    if (statusId == null) return 'bg-gray-100 text-gray-700';
    return KORSO_STATUS_BADGE_CLASSES[statusId] ?? 'bg-gray-100 text-gray-700';
}
