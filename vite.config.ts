import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
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
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            includeAssets: ['favicon.ico', 'apple-touch-icon.png', 'mask-icon.svg'],
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg}'],
                cleanupOutdatedCaches: true,
                clientsClaim: true,
                skipWaiting: true,
            },
            manifest: {
                name: 'Crime Map',
                short_name: 'CrimeMap',
                description: 'Aplikasi Peta Kriminalitas & Bencana',
                theme_color: '#3b82f6',
                background_color: '#000000',
                display: 'standalone',
                start_url: '/',
                scope: '/',
                icons: [
                    {
                        src: 'img/icons/pwa-192x192.svg',
                        sizes: '192x192',
                        type: 'image/svg+xml',
                        purpose: 'any'
                    },
                    {
                        src: 'img/icons/pwa-512x512.svg',
                        sizes: '512x512',
                        type: 'image/svg+xml',
                        purpose: 'any'
                    },
                    {
                        src: 'img/icons/pwa-192x192.svg',
                        sizes: '192x192',
                        type: 'image/svg+xml',
                        purpose: 'maskable'
                    },
                    {
                        src: 'img/icons/pwa-512x512.svg',
                        sizes: '512x512',
                        type: 'image/svg+xml',
                        purpose: 'maskable'
                    }
                ]
            }
        })
    ],
    build: {
        reportCompressedSize: false,
        chunkSizeWarningLimit: 1000,
    },
    logLevel: 'warn',
});
