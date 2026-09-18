import { fileURLToPath, URL } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
    'resources/css/app.css',
    'resources/js/app.ts',
    'resources/sass/app.scss',
    'resources/js/app.js',
],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
        watch: {
            // storage/ is Laravel's own runtime/cache directory (sessions,
            // logs, and - notably - storage/inertia-devtools/*.tmp, which
            // Inertia writes to rapidly enough to trip a Windows file-lock
            // race and crash the whole dev server with EBUSY). None of it
            // should ever be watched. vendor/ and node_modules/ are excluded
            // for the same reason (large, never edited through this app).
            ignored: ['**/storage/**', '**/vendor/**', '**/node_modules/**'],
        },
    },
});
