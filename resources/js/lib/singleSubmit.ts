import { router } from '@inertiajs/vue3';

/**
 * Double-submit guard for every Inertia form (2026-10-12).
 *
 * A second POST / PUT / PATCH / DELETE to the same URL while the first one
 * is still running is dropped here, before it is sent. Before, a double
 * click could start a second request; Inertia then dropped the answer of
 * the first one, but the server had already saved it - the ticket existed
 * twice. The server checks the same (App\Http\Middleware\PreventDuplicateSubmit)
 * for the ticket forms, in case two requests still get through.
 * GET visits (links, filters, reloads) are not affected.
 */
const running = new Set<string>();

function key(visit: { method: string; url: URL }): string {
    return `${visit.method.toLowerCase()} ${visit.url.href}`;
}

export function initializeSingleSubmit(): void {
    router.on('before', (event) => {
        const visit = event.detail.visit;
        if (visit.method.toLowerCase() === 'get') return;
        const k = key(visit);
        if (running.has(k)) return false;
        running.add(k);
    });
    router.on('finish', (event) => {
        running.delete(key(event.detail.visit));
    });
}
