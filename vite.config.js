import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import Components from 'unplugin-vue-components/vite';
import {PrimeVueResolver} from '@primevue/auto-import-resolver';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
        Components({
            resolvers: [PrimeVueResolver()],
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('@primeuix')) {
                            return 'primeuix';
                        }
                        if (
                            id.includes('primevue') ||
                            id.includes('@primevue')
                        ) {
                            return 'primevue';
                        }
                        if (id.includes('vue-i18n')) {
                            return 'i18n';
                        }
                        if (id.includes('axios')) {
                            return 'http';
                        }
                    }
                },
            },
        },
    },
});
