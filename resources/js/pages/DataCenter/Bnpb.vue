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
        title: 'BNPB',
        href: '/data-center/bnpb',
    },
];

const isLoading = ref(true);

const onIframeLoad = () => {
    isLoading.value = false;
};
</script>

<template>
    <Head title="BNPB - Data Center" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-4rem)] flex-col">
            <div class="relative flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 bg-background shadow-sm">
                <!-- Loading State -->
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-background">
                    <div class="flex flex-col items-center gap-2">
                        <div class="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent"></div>
                        <p class="text-sm text-muted-foreground">Memuat Peta BNPB...</p>
                    </div>
                </div>

                <!-- Iframe Content -->
                <iframe
                    src="https://gis.bnpb.go.id/dev/map/"
                    class="h-full w-full border-0"
                    allow="geolocation"
                    @load="onIframeLoad"
                ></iframe>
            </div>
        </div>
    </AppLayout>
</template>
