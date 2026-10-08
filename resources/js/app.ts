import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import { trackNavigation } from '@/lib/navHistory';
import { withUnreadPrefix } from '@/composables/useUnread';
import { initializeFlashToast } from '@/lib/flashToast';
import type { DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'servsmt';

// Lets the ticket pages' "Zurück" button know if there is an in-app page to go back to.
trackNavigation();

createInertiaApp({
    // "(3) …" when there are unread notifications (see composables/useUnread.ts).
    title: (title) => withUnreadPrefix(title ? `${title} - ${appName}` : appName),
    // Pages are loaded on demand (2026-10-07). Before, `eager: true` put all
    // ~70 pages into the first download, so every full page load had to
    // fetch and parse the whole app (and, with the Vite dev server, hundreds
    // of separate modules). Now each page is its own chunk.
    resolve: async (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');
        const loader = pages[`./pages/${name}.vue`];
        if (!loader) throw new Error(`Page not found: ${name}`);
        const page = await loader();

        // Every page uses the sidebar shell unless it opts out with
        // `defineOptions({ layout: false })` (e.g. pages/Error.vue, which is
        // also shown without a logged-in user).
        if (page.default.layout !== false) {
            page.default.layout = page.default.layout ?? AppLayout;
        } else {
            page.default.layout = undefined;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#661421',
    },
});

// Set light / dark mode on page load...
initializeTheme();

// Listen for flash-message toasts from the server...
initializeFlashToast();
