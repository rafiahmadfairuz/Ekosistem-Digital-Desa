import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: process.env.VITE_DEV_SERVER_HOST || undefined,
        origin: process.env.VITE_DEV_SERVER_ORIGIN || undefined,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
