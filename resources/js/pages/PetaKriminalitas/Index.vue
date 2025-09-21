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
                            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2"
                        >
                            <Monitor class="h-4 w-4" />
                            {{ displayMode === 'proxy' ? 'Mode Landing' : 'Mode Iframe' }}
                        </button>
                        <a
                            href="https://pusiknas.polri.go.id/peta_kriminalitas"
                            target="_blank"
                            class="px-4 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center gap-2"
                        >
                            <ExternalLink class="h-4 w-4" />
                            Buka di Tab Baru
                        </a>
                    </div>
                </div>

                <!-- Iframe Mode -->
                <div v-if="displayMode === 'proxy'" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Iframe Header -->
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
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
                                    class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600"
                                >
                                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': isRefreshing }" />
                                </button>
                                <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    Live Data
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Iframe Content -->
                    <div class="relative">
                        <!-- Loading Overlay -->
                        <div v-if="isLoading" class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 flex items-center justify-center z-10">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                <p class="text-gray-600 dark:text-gray-400">Memuat via Proxy...</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">Menghubungkan ke server Pusiknas Polri</p>
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
                        <div v-if="hasError" class="absolute inset-0 bg-white dark:bg-gray-800 flex items-center justify-center">
                            <div class="text-center p-8">
                                <div class="p-4 bg-red-100 dark:bg-red-900/30 rounded-lg mb-4 inline-block">
                                    <AlertTriangle class="h-8 w-8 text-red-600 dark:text-red-400" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Gagal Memuat via Proxy</h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    Server proxy tidak dapat mengakses website Pusiknas Polri.
                                </p>
                                <div class="space-y-2">
                                    <button
                                        @click="retryProxy"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors mr-2"
                                    >
                                        Coba Lagi
                                    </button>
                                    <button
                                        @click="switchToLanding"
                                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                    >
                                        Mode Landing Page
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Landing Page Mode -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                                    <Shield class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pusiknas Bareskrim Polri</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Peta Kriminalitas Indonesia - Dashboard Resmi</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                Live Data
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-8">
                        <div class="text-center max-w-2xl mx-auto">
                            <!-- Security Notice -->
                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-6">
                                <div class="flex items-center justify-center gap-2 text-yellow-800 dark:text-yellow-200">
                                    <AlertTriangle class="h-5 w-5" />
                                    <span class="text-sm font-medium">Akses Langsung ke Website Resmi</span>
                                </div>
                                <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                    Untuk keamanan data, website Pusiknas Polri hanya dapat diakses secara langsung
                                </p>
                            </div>

                            <!-- Preview Image/Screenshot -->
                            <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                                <div class="aspect-video bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-lg flex items-center justify-center">
                                    <div class="text-center">
                                        <Map class="h-16 w-16 text-blue-500 dark:text-blue-400 mx-auto mb-3" />
                                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Peta Kriminalitas Interaktif</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Visualisasi data real-time keamanan Indonesia</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-8">
                                <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                                    Akses Dashboard Kriminalitas Resmi
                                </h4>
                                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                    Dashboard Pusiknas Bareskrim Polri menyediakan visualisasi data kriminalitas real-time
                                    di seluruh Indonesia dengan fitur pemetaan interaktif, statistik lengkap, dan analisis
                                    tren keamanan per wilayah.
                                </p>
                            </div>

                            <!-- Features Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <BarChart3 class="h-8 w-8 text-blue-600 dark:text-blue-400 mx-auto mb-2" />
                                    <h5 class="font-semibold text-gray-900 dark:text-white text-sm">Data Real-time</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Update langsung dari seluruh polres</p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <Map class="h-8 w-8 text-green-600 dark:text-green-400 mx-auto mb-2" />
                                    <h5 class="font-semibold text-gray-900 dark:text-white text-sm">Peta Interaktif</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Visualisasi geografis Indonesia</p>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <TrendingUp class="h-8 w-8 text-purple-600 dark:text-purple-400 mx-auto mb-2" />
                                    <h5 class="font-semibold text-gray-900 dark:text-white text-sm">Analisis Tren</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Insight statistik dan pola</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a
                                    :href="pusikansUrl"
                                    target="_blank"
                                    @click="trackAccess"
                                    class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center justify-center gap-3 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 duration-200"
                                >
                                    <ExternalLink class="h-5 w-5" />
                                    Buka Dashboard Pusiknas
                                </a>
                                <button
                                    @click="openInNewWindow"
                                    class="px-8 py-4 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors flex items-center justify-center gap-3 font-semibold"
                                >
                                    <Monitor class="h-5 w-5" />
                                    Buka di Jendela Baru
                                </button>
                            </div>

                            <!-- Additional Info -->
                            <div class="mt-6 text-sm text-gray-500 dark:text-gray-400">
                                <p>
                                    Link akan membuka tab baru ke
                                    <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">pusiknas.polri.go.id</code>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Footer -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Info class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Tentang Data Kriminalitas</h3>
                            <div class="space-y-2 text-gray-700 dark:text-gray-300 text-sm">
                                <p>
                                    <strong>Sumber:</strong> Pusat Sistem Informasi Kriminal Nasional (Pusiknas) Bareskrim Polri
                                </p>
                                <p>
                                    <strong>Update:</strong> Data diperbarui secara real-time berdasarkan laporan polres di seluruh Indonesia
                                </p>
                                <p>
                                    <strong>Cakupan:</strong> Seluruh wilayah NKRI dengan breakdown per provinsi, kabupaten/kota, dan kecamatan
                                </p>
                                <p class="text-blue-700 dark:text-blue-300 mt-3">
                                    Visualisasi ini menampilkan data kriminalitas resmi yang terintegrasi dalam sistem informasi Polri untuk mendukung transparansi dan akuntabilitas kepolisian.
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
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { type BreadcrumbItem } from '@/types';
import { Shield, ExternalLink, RefreshCw, AlertTriangle, Info, Map, BarChart3, TrendingUp, Monitor } from 'lucide-vue-next';

// Breadcrumbs
const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Peta Kriminalitas', href: '#' }
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