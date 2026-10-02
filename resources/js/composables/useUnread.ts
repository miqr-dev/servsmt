import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed } from 'vue';

/**
 * Unread in-app notifications for ALL systems (IT, Handwerk, Korso, …).
 * Data: the shared `notifications` prop (HandleInertiaRequests →
 * App\Support\NotificationFeed::summary), refreshed every minute by
 * startUnreadPolling() and on every page visit.
 *
 * keys look like "ticket:5", "handwerk:12", "korso:3", "other:<uuid>".
 */
export type UnreadSummary = { count: number; total: number; keys: string[]; more?: boolean };
export type UnreadKind = 'ticket' | 'handwerk' | 'korso';

export function useUnread() {
    const page = usePage<{ notifications?: UnreadSummary | null }>();
    const summary = computed<UnreadSummary>(() => page.props.notifications ?? { count: 0, total: 0, keys: [] });
    const keySet = computed(() => new Set(summary.value.keys));

    return {
        /** Number of tickets/items with something new. */
        // more = only the newest notifications were counted -> show as 99+
        count: computed(() => (summary.value.more ? Math.max(summary.value.count, 100) : summary.value.count)),
        isUnread: (kind: UnreadKind, id: number | string | null | undefined) => id != null && keySet.value.has(`${kind}:${id}`),
        countFor: (kind: UnreadKind) => summary.value.keys.filter((k) => k.startsWith(`${kind}:`)).length,
    };
}

let timer: ReturnType<typeof setInterval> | undefined;

/**
 * Refresh the shared prop every 60 s (skipped while the tab is hidden).
 * Call once from a component that is always mounted (AppSidebar) - it needs
 * usePage(), and the next Inertia visit replaces the value with fresh data.
 */
export function startUnreadPolling(): void {
    if (timer || typeof window === 'undefined') return;
    const page = usePage<{ notifications?: UnreadSummary | null }>();
    const refresh = async () => {
        if (document.hidden) return;
        try {
            const { data } = await axios.get<UnreadSummary>('/notifications/summary');
            page.props.notifications = data;
        } catch {
            // offline / logged out - try again next minute
        }
    };
    timer = setInterval(refresh, 60_000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refresh();
    });
}

/** "(3) " prefix for the browser tab title; read by the title callback in app.ts. */
export const titleState = { count: 0 };

export function withUnreadPrefix(title: string): string {
    const clean = title.replace(/^\(\d+\) /, '');

    return titleState.count > 0 ? `(${titleState.count}) ${clean}` : clean;
}
