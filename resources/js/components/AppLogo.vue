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
    // Use app_logo only, do not fallback to favicon
    return page.props.appSettings?.app_logo || null;
});
</script>

<template>
    <div class="flex w-full h-full items-center justify-start rounded-md bg-zinc-900 px-2 overflow-hidden">
        <!-- Custom logo if available -->
        <img v-if="appLogo" :src="appLogo" :alt="appName + ' Logo'" class="h-8 w-8 object-contain shrink-0" />

        <!-- Default SVG icon if no custom logo -->
        <svg
            v-else
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="white"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="lucide lucide-swords-icon lucide-swords shrink-0"
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

        <div class="ml-2 grid flex-1 text-left text-sm leading-tight min-w-0">
            <span class="truncate font-semibold text-white">{{ appName }}</span>
        </div>
    </div>
</template>
