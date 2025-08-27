<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <div class="flex flex-col">
        <!-- Top header with logo on left and menu button on right -->
        <header
            class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
        >
            <!-- Left side: Logo on mobile, Breadcrumbs on desktop -->
            <div class="flex items-center gap-3">
                <!-- Logo - visible on mobile only -->
                <Link :href="route('dashboard')" class="block md:hidden">
                    <div class="h-8 w-24 max-w-24">
                        <AppLogo />
                    </div>
                </Link>
                
                <!-- Breadcrumbs - hidden on mobile, visible on desktop -->
                <template v-if="breadcrumbs && breadcrumbs.length > 0">
                    <div class="hidden md:block">
                        <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    </div>
                </template>
            </div>

            <!-- Right side: Menu button always on right -->
            <SidebarTrigger />
        </header>

        <!-- Breadcrumbs below header - visible on mobile only -->
        <template v-if="breadcrumbs && breadcrumbs.length > 0">
            <div class="border-b border-sidebar-border/70 px-6 py-3 md:hidden">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </template>
    </div>
</template>
