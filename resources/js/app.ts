import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { registerSW } from 'virtual:pwa-register';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

// Function to update CSRF token in meta tag and axios headers
const updateCsrfToken = (token: string) => {
    if (!token) return;

    // Update meta tag
    let meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) {
        meta = document.createElement('meta');
        meta.setAttribute('name', 'csrf-token');
        document.head.appendChild(meta);
    }
    meta.setAttribute('content', token);

    // Update axios defaults
    if (axios.defaults.headers.common) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }
};

// Set initial CSRF token for all Axios requests
const initialCsrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (initialCsrfToken) {
    updateCsrfToken(initialCsrfToken);
}

// Global 419 error handler to refresh page automatically
router.on('error', (event) => {
    // Check if any error is 419
    const errors = event.detail.errors;
    if (errors && Object.values(errors).some(e => String(e).includes('419'))) {
        window.location.reload();
    }
});

// Update CSRF token dynamically on every Inertia navigation
router.on('finish', (event) => {
    const props = event.detail.page.props;
    if (props.csrf_token) {
        updateCsrfToken(props.csrf_token as string);
    }
});

// Handle axios errors for 419 specifically
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 419) {
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

// Register Service Worker for PWA
registerSW({ immediate: true });

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
