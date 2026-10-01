import { router } from '@inertiajs/vue3';

/**
 * Counts Inertia page visits in this tab, so a "Zurück" button knows whether
 * history.back() stays inside servsmt. Started once from app.ts.
 */
let visits = 0;
let started = false;

export function trackNavigation(): void {
    if (started) return;
    started = true;
    router.on('navigate', () => {
        visits++;
    });
}

/** True when going back lands on a servsmt page (not an e-mail client, empty tab, …). */
export function canGoBackInApp(): boolean {
    if (visits > 1) return true;
    try {
        return !!document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1;
    } catch {
        return false;
    }
}
