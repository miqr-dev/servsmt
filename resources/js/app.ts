import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import { trackNavigation } from '@/lib/navHistory';
import { initializeFlashToast } from '@/lib/flashToast';
import type { DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'servsmt';

// Lets the ticket pages' "Zurück" button know if there is an in-app page to go back to.
trackNavigation();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });
        const page = pages[`./pages/${name}.vue`];

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
