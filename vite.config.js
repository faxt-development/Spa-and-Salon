import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/onboarding.css', 'resources/js/app.js'],
            refresh: true,
        }),
        react({
            // This fixes the "can't detect preamble" error
            jsxRuntime: 'classic'
        }),
    ],
    server: {
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                secure: false,
                changeOrigin: true
            },
            '/sanctum': {
                target: 'http://localhost:8000',
                secure: false,
                changeOrigin: true
            }
        }
    }
});
