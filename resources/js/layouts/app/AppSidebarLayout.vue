<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const buildVersion = ref('');
const isLocal = computed(() => {
    const host = window.location.hostname;
    return import.meta.env.DEV || host === 'localhost' || host === '127.0.0.1';
});

const displayVersion = computed(() => {
    if (buildVersion.value) return buildVersion.value;
    if (!isLocal.value) return '-';
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}${m}${day}`;
});

const applyMeta = (meta: any) => {
    const version = meta?.version;
    if (typeof version === 'string' && version.length > 0) {
        buildVersion.value = version;
    }
};

const checkUpdate = async () => {
    const fn = (window as any).__CHECK_PWA_UPDATE__;
    if (typeof fn === 'function') {
        await fn();
        return;
    }

    if ('serviceWorker' in navigator) {
        const reg = await navigator.serviceWorker.getRegistration().catch(() => undefined);
        await reg?.update().catch(() => undefined);
    }
};

const onBuildMeta = (e: Event) => {
    applyMeta((e as CustomEvent).detail);
};

onMounted(async () => {
    const localVersion = window.localStorage.getItem('buildVersion');
    if (typeof localVersion === 'string' && localVersion.length > 0) {
        buildVersion.value = localVersion;
    }

    const localMeta = window.localStorage.getItem('buildMeta');
    if (typeof localMeta === 'string' && localMeta.length > 0) {
        try {
            applyMeta(JSON.parse(localMeta));
        } catch {}
    }

    try {
        const res = await fetch(`/build-meta.json?ts=${Date.now()}`, { cache: 'no-store' });
        if (res.ok) {
            const meta = await res.json().catch(() => null);
            applyMeta(meta);
        }
    } catch {}

    window.addEventListener('build-meta', onBuildMeta);
});

onUnmounted(() => {
    window.removeEventListener('build-meta', onBuildMeta);
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="flex min-h-screen flex-col overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex-1">
                <slot />
            </div>
            <div class="py-4 flex items-center justify-center text-center text-xs text-muted-foreground">
                <div>Version {{ displayVersion }}</div>
                <button
                    type="button"
                    class="ml-2 px-2 py-1 rounded-md bg-primary text-primary-foreground"
                    @click="checkUpdate"
                >
                    Cek update
                </button>
            </div>
        </AppContent>
    </AppShell>
</template>
