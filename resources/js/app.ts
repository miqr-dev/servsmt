import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';
import type { DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'servsmt';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });
        const page = pages[`./pages/${name}.vue`];

        // Every page uses the sidebar shell for now - pages can opt out later
        // (e.g. auth pages) the same way the Laravel starter kit does, once
        // those get converted too.
        page.default.layout = page.default.layout ?? AppLayout;

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
