<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs = [
    {
        title: 'Data Center',
        href: '#',
    },
    {
        title: 'War Monitor',
        href: '/data-center/war-monitor',
    },
];

const isLoading = ref(true);

const onIframeLoad = () => {
    isLoading.value = false;
};
</script>

<template>
    <Head title="War Monitor - Data Center" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] flex-col">
            <div class="relative flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 bg-background shadow-sm">
                <!-- Loading State -->
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-background z-10">
                    <div class="flex flex-col items-center gap-2">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                        <p class="text-sm text-muted-foreground">Memuat War Monitor...</p>
                    </div>
                </div>

                <!-- Iframe Content -->
                <iframe
                    src="https://tech.worldmonitor.app/?lat=-2.4178&lon=118.0872&zoom=3.35&view=global&timeRange=7d&layers=cables%2Cweather%2Ceconomic%2Coutages%2Cdatacenters%2Cnatural%2CstartupHubs%2CcloudRegions%2CtechHQs%2CtechEvents&country=ID"
                    class="h-full w-full border-0"
                    allow="geolocation; clipboard-write; encrypted-media; picture-in-picture"
                    @load="onIframeLoad"
                ></iframe>
            </div>
        </div>
    </AppLayout>
</template>
