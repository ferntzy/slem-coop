import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/src/main.tsx',
                'resources/css/filament/admin/theme.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js/src'),
        },
    },

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
        hmr: {
            host: 'localhost',
        },
    },
});
