<script setup lang="ts">
import type { AppPageProps } from '@/types/index.d';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Get shared data from Inertia with proper typing
const page = usePage<AppPageProps>();

// Computed values for app settings with fallbacks
const appName = computed(() => {
    return page.props.appSettings?.app_name || 'Peta Kriminal Indonesia';
});

const appLogo = computed(() => {
    // Use app_logo if available, otherwise fallback to favicon
    return page.props.appSettings?.app_logo || page.props.appSettings?.app_favicon || null;
});
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
        <!-- Custom logo if available -->
        <img v-if="appLogo" :src="appLogo" :alt="appName + ' Logo'" class="h-6 w-6 object-contain" />

        <!-- Default SVG icon if no custom logo -->
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="black"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="lucide lucide-swords-icon lucide-swords"
        >
            <polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5" />
            <line x1="13" x2="19" y1="19" y2="13" />
            <line x1="16" x2="20" y1="16" y2="20" />
            <line x1="19" x2="21" y1="21" y2="19" />
            <polyline points="14.5 6.5 18 3 21 3 21 6 17.5 9.5" />
            <line x1="5" x2="9" y1="14" y2="18" />
            <line x1="7" x2="4" y1="17" y2="20" />
            <line x1="3" x2="5" y1="19" y2="21" />
        </svg>
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">{{ appName }}</span>
    </div>
</template>
