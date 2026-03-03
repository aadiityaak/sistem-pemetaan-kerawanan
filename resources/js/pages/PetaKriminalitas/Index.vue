<template>
    <Head title="Peta Kriminalitas Indonesia" />

    <AppLayout>
        <template #breadcrumbs>
            <Breadcrumbs :breadcrumbs="breadcrumbItems" />
        </template>

        <div class="p-6">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Peta Kriminalitas Indonesia</h1>
                        <p class="text-gray-600 dark:text-gray-400">Data resmi dari Pusiknas Bareskrim Polri</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-4">
                        <button
                            @click="toggleDisplayMode"
                            class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm text-white transition-colors hover:bg-blue-700"
                        >
                            <Monitor class="h-4 w-4" />
                            {{ displayMode === 'proxy' ? 'Mode Landing' : 'Mode Iframe' }}
                        </button>
                        <a
                            href="https://pusiknas.polri.go.id/peta_kriminalitas"
                            target="_blank"
                            class="flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-sm text-white transition-colors hover:bg-gray-700"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Buka di Tab Baru
                        </a>
                    </div>
                </div>

                <!-- Iframe Mode -->
                <div
                    v-if="displayMode === 'proxy'"
                    class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
                >
                    <!-- Iframe Header -->
                    <div class="border-b border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                                    <Shield class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pusiknas Dashboard (Via Proxy)</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Peta Kriminalitas Indonesia - Data Real-time</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="refreshProxy"
                                    class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-gray-200"
                                >
                                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': isRefreshing }" />
                                </button>
                                <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                    <div class="h-2 w-2 animate-pulse rounded-full bg-green-500"></div>
                                    Live Data
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Iframe Content -->
                    <div class="relative">
                        <!-- Loading Overlay -->
                        <div v-if="isLoading" class="absolute inset-0 z-10 flex items-center justify-center bg-white/90 dark:bg-gray-800/90">
                            <div class="text-center">
                                <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-b-2 border-blue-600"></div>
                                <p class="text-gray-600 dark:text-gray-400">Memuat via Proxy...</p>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">Menghubungkan ke server Pusiknas Polri</p>
                            </div>
                        </div>

                        <!-- Iframe -->
                        <iframe
                            ref="crimeMapIframe"
                            :src="proxyUrl"
                            @load="onIframeLoad"
                            @error="onIframeError"
                            class="w-full border-0"
                            :style="{ height: iframeHeight + 'px' }"
                            loading="lazy"
                            title="Peta Kriminalitas Indonesia - Pusiknas Bareskrim Polri"
                        ></iframe>

                        <!-- Error State -->
                        <div v-if="hasError" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800">
                            <div class="p-8 text-center">
                                <div class="mb-4 inline-block rounded-lg bg-red-100 p-4 dark:bg-red-900/30">
                                    <AlertTriangle class="h-8 w-8 text-red-600 dark:text-red-400" />
                                </div>
                                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Gagal Memuat via Proxy</h3>
                                <p class="mb-4 text-gray-600 dark:text-gray-400">Server proxy tidak dapat mengakses website Pusiknas Polri.</p>
                                <div class="space-y-2">
                                    <button
                                        @click="retryProxy"
                                        class="mr-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                                    >
                                        Coba Lagi
                                    </button>
                                    <button
                                        @click="switchToLanding"
                                        class="rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700"
                                    >
                                        Mode Landing Page
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Landing Page Mode -->
                <div v-else class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <!-- Header -->
                    <div
                        class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 dark:border-gray-700 dark:from-blue-900/20 dark:to-indigo-900/20"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                                    <Shield class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pusiknas Bareskrim Polri</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Peta Kriminalitas Indonesia - Dashboard Resmi</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                <div class="h-2 w-2 animate-pulse rounded-full bg-green-500"></div>
                                Live Data
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-8">
                        <div class="mx-auto max-w-2xl text-center">
                            <!-- Security Notice -->
                            <div class="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                                <div class="flex items-center justify-center gap-2 text-yellow-800 dark:text-yellow-200">
                                    <AlertTriangle class="h-5 w-5" />
                                    <span class="text-sm font-medium">Akses Langsung ke Website Resmi</span>
                                </div>
                                <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                                    Untuk keamanan data, website Pusiknas Polri hanya dapat diakses secara langsung
                                </p>
                            </div>

                            <!-- Preview Image/Screenshot -->
                            <div class="mb-6 rounded-lg border-2 border-dashed border-gray-300 bg-gray-100 p-4 dark:border-gray-600 dark:bg-gray-700">
                                <div
                                    class="flex aspect-video items-center justify-center rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30"
                                >
                                    <div class="text-center">
                                        <Map class="mx-auto mb-3 h-16 w-16 text-blue-500 dark:text-blue-400" />
                                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Peta Kriminalitas Interaktif</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Visualisasi data real-time keamanan Indonesia</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-8">
                                <h4 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Akses Dashboard Kriminalitas Resmi</h4>
                                <p class="leading-relaxed text-gray-600 dark:text-gray-400">
                                    Dashboard Pusiknas Bareskrim Polri menyediakan visualisasi data kriminalitas real-time di seluruh Indonesia dengan
                                    fitur pemetaan interaktif, statistik lengkap, dan analisis tren keamanan per wilayah.
                                </p>
                            </div>

                            <!-- Features Grid -->
                            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <BarChart3 class="mx-auto mb-2 h-8 w-8 text-blue-600 dark:text-blue-400" />
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Data Real-time</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Update langsung dari seluruh polres</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <Map class="mx-auto mb-2 h-8 w-8 text-green-600 dark:text-green-400" />
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Peta Interaktif</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Visualisasi geografis Indonesia</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                    <TrendingUp class="mx-auto mb-2 h-8 w-8 text-purple-600 dark:text-purple-400" />
                                    <h5 class="text-sm font-semibold text-gray-900 dark:text-white">Analisis Tren</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Insight statistik dan pola</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                                <a
                                    :href="pusikansUrl"
                                    target="_blank"
                                    @click="trackAccess"
                                    class="flex transform items-center justify-center gap-3 rounded-lg bg-blue-600 px-8 py-4 font-semibold text-white shadow-lg transition-colors duration-200 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-xl"
                                >
                                    <ExternalLink class="h-5 w-5" />
                                    Buka Dashboard Pusiknas
                                </a>
                                <button
                                    @click="openInNewWindow"
                                    class="flex items-center justify-center gap-3 rounded-lg bg-gray-600 px-8 py-4 font-semibold text-white transition-colors hover:bg-gray-700"
                                >
                                    <Monitor class="h-5 w-5" />
                                    Buka di Jendela Baru
                                </button>
                            </div>

                            <!-- Additional Info -->
                            <div class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                                <p>
                                    Link akan membuka tab baru ke
                                    <code class="rounded bg-gray-100 px-2 py-1 text-xs dark:bg-gray-700">pusiknas.polri.go.id</code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Footer -->
                <div
                    class="rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20"
                >
                    <div class="flex items-start gap-4">
                        <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                            <Info class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">Tentang Data Kriminalitas</h3>
                            <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                                <p><strong>Sumber:</strong> Pusat Sistem Informasi Kriminal Nasional (Pusiknas) Bareskrim Polri</p>
                                <p><strong>Update:</strong> Data diperbarui secara real-time berdasarkan laporan polres di seluruh Indonesia</p>
                                <p><strong>Cakupan:</strong> Seluruh wilayah NKRI dengan breakdown per provinsi, kabupaten/kota, dan kecamatan</p>
                                <p class="mt-3 text-blue-700 dark:text-blue-300">
                                    Visualisasi ini menampilkan data kriminalitas resmi yang terintegrasi dalam sistem informasi Polri untuk mendukung
                                    transparansi dan akuntabilitas kepolisian.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, BarChart3, ExternalLink, Info, Map, Monitor, RefreshCw, Shield, TrendingUp } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';

// Breadcrumbs
const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Peta Kriminalitas', href: '#' },
];

// Reactive state
const displayMode = ref('proxy'); // 'proxy' atau 'landing' - default langsung iframe
const pusikansUrl = ref('https://pusiknas.polri.go.id/peta_kriminalitas');
const proxyUrl = ref('/proxy/pusiknas');
const accessCount = ref(0);
const crimeMapIframe = ref<HTMLIFrameElement>();
const isLoading = ref(false);
const hasError = ref(false);
const isRefreshing = ref(false);
const iframeHeight = ref(800);

// Toggle display mode
const toggleDisplayMode = () => {
    displayMode.value = displayMode.value === 'proxy' ? 'landing' : 'proxy';
    if (displayMode.value === 'proxy') {
        isLoading.value = true;
        hasError.value = false;
    }
};

// Switch to landing page
const switchToLanding = () => {
    displayMode.value = 'landing';
};

// Iframe event handlers
const onIframeLoad = () => {
    isLoading.value = false;
    hasError.value = false;
    isRefreshing.value = false;
};

const onIframeError = () => {
    isLoading.value = false;
    hasError.value = true;
    isRefreshing.value = false;
};

// Refresh proxy
const refreshProxy = () => {
    if (crimeMapIframe.value) {
        isRefreshing.value = true;
        isLoading.value = true;
        hasError.value = false;

        // Force reload by changing src
        const currentUrl = crimeMapIframe.value.src;
        crimeMapIframe.value.src = '';

        setTimeout(() => {
            if (crimeMapIframe.value) {
                crimeMapIframe.value.src = currentUrl;
            }
        }, 100);
    }
};

// Retry proxy
const retryProxy = () => {
    hasError.value = false;
    isLoading.value = true;
    refreshProxy();
};

// Track access function
const trackAccess = () => {
    accessCount.value++;
    console.log('User accessed Pusiknas dashboard:', accessCount.value);
};

// Open in new window with specific dimensions
const openInNewWindow = () => {
    const width = 1200;
    const height = 800;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;

    const windowFeatures = `
        width=${width},
        height=${height},
        left=${left},
        top=${top},
        resizable=yes,
        scrollbars=yes,
        toolbar=no,
        menubar=no,
        location=no,
        status=no
    `.replace(/\s/g, '');

    window.open(pusikansUrl.value, 'PusiknasDashboard', windowFeatures);
    trackAccess();
};

// Adjust iframe height
const adjustIframeHeight = () => {
    const viewportHeight = window.innerHeight;
    const headerHeight = 200;
    const footerHeight = 200;
    const minHeight = 600;

    iframeHeight.value = Math.max(minHeight, viewportHeight - headerHeight - footerHeight);
};

// Lifecycle hooks
onMounted(() => {
    adjustIframeHeight();
    window.addEventListener('resize', adjustIframeHeight);

    // Auto-start loading iframe mode
    if (displayMode.value === 'proxy') {
        isLoading.value = true;
        hasError.value = false;
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', adjustIframeHeight);
});
</script>
