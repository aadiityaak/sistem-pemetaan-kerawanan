<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import type { AppPageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage<AppPageProps>();
const appSettings = computed(() => page.props.appSettings);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo/Icon section with clean, flat design -->
            <div class="flex justify-center">
                <Link :href="route('home')" class="flex flex-col items-center gap-3">
                    <div class="relative">
                        <div v-if="appSettings.app_favicon && appSettings.app_favicon !== '/favicon.ico'" class="h-12 w-12 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center shadow-sm">
                            <img :src="appSettings.app_favicon" :alt="appSettings.app_name" class="h-8 w-8 object-contain" />
                        </div>
                        <div v-else class="h-12 w-12 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center shadow-sm">
                            <AppLogoIcon class="h-8 w-8 text-gray-600 dark:text-gray-300" />
                        </div>
                    </div>
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ appSettings.app_name || 'Crime Map' }}</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ appSettings.app_description || 'Sistem Pemetaan Kriminal' }}</p>
                    </div>
                </Link>
            </div>

            <!-- Auth card with flat, clean design -->
            <div class="mt-8">
                <div class="bg-white dark:bg-gray-800 py-8 px-6 shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="mb-6 text-center">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ description }}</p>
                    </div>
                    
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
