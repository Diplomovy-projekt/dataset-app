import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
/*    server: {
        host: '0.0.0.0',
        hmr: {
            clientPort: 5173,
            host: 'dataset-app.test',
            protocol: 'ws'
        },
        port: 5173,
        watch: {
            usePolling: true
        }
    },*/
    server: {
        watch: {
            ignored: ['**/storage/**', '**/app/**']
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/chunkedUpload.js'
            ],
            refresh: true,
        }),
    ],
});
