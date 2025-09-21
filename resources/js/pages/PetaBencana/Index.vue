<template>
    <Head title="Peta Bencana Indonesia" />

    <AppLayout>
        <template #breadcrumbs>
            <Breadcrumbs :breadcrumbs="breadcrumbItems" />
        </template>

        <div class="p-6">
            <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Peta Bencana Indonesia</h1>
                    <p class="text-gray-600 dark:text-gray-400">Visualisasi data bencana alam berdasarkan BNPB</p>
                </div>

                <!-- Legend Toggle -->
                <div class="flex items-center gap-4">
                    <button
                        @click="showLegend = !showLegend"
                        class="px-4 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        {{ showLegend ? 'Sembunyikan' : 'Tampilkan' }} Legenda
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kejadian</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalIncidents.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg">
                            <AlertTriangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Korban Meninggal</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalDeaths.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-gray-100 dark:bg-gray-900/20 rounded-lg">
                            <Users class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Korban Luka</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalInjured.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                            <Heart class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Orang Hilang</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalMissing.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                            <Search class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Map Section -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <!-- Map Header -->
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Peta Sebaran Bencana</h3>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Data: BNPB Indonesia
                                </div>
                            </div>
                        </div>

                        <!-- Map Container -->
                        <div class="relative h-[600px]">
                            <div ref="mapContainer" class="absolute inset-0 w-full h-full"></div>

                            <!-- Loading overlay -->
                            <div v-if="loadingData" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center z-[1000]">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Memuat data bencana...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Disaster Type Filter -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filter Jenis Bencana</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input
                                    v-model="selectedDisasters"
                                    value="all"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Semua Bencana</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="selectedDisasters"
                                    value="banjir"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Banjir</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="selectedDisasters"
                                    value="karhutla"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Kebakaran Hutan</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="selectedDisasters"
                                    value="tanah_longsor"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tanah Longsor</span>
                            </label>
                            <label class="flex items-center">
                                <input
                                    v-model="selectedDisasters"
                                    value="puting_beliung"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Puting Beliung</span>
                            </label>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div v-show="showLegend" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Legenda</h3>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-red-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Tinggi (>100 kejadian)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-orange-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Sedang (50-100 kejadian)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-yellow-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Rendah (10-50 kejadian)</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-500 rounded mr-3"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Sangat Rendah (<10 kejadian)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Provinces -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Provinsi Teratas</h3>
                        <div class="space-y-3">
                            <div v-for="(province, index) in topProvinces" :key="province.wilayah" class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center text-xs font-medium text-blue-600 dark:text-blue-400 mr-3">
                                        {{ index + 1 }}
                                    </div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ province.wilayah }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ province.jumlah_kejadian }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kesimpulan dan Rangkuman -->
            <div class="space-y-6">
                <!-- Kesimpulan Utama -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <AlertTriangle class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Kesimpulan Situasi Bencana Indonesia</h3>
                            <div class="space-y-2 text-gray-700 dark:text-gray-300">
                                <p v-if="topProvinces.length > 0">
                                    <strong>Provinsi dengan risiko tertinggi:</strong> {{ topProvinces[0]?.wilayah }} dengan {{ topProvinces[0]?.jumlah_kejadian.toLocaleString() }} kejadian bencana.
                                </p>
                                <p>
                                    <strong>Total dampak nasional:</strong> {{ totalIncidents.toLocaleString() }} kejadian bencana telah terjadi dengan
                                    {{ totalDeaths.toLocaleString() }} korban meninggal, {{ totalInjured.toLocaleString() }} korban luka, dan
                                    {{ totalMissing.toLocaleString() }} orang dilaporkan hilang.
                                </p>
                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-3">
                                    Data ini menunjukkan pentingnya kesiapsiagaan bencana dan sistem peringatan dini yang efektif di seluruh Indonesia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analisis dan Insight -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Jenis Bencana Dominan -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <BarChart3 class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            Jenis Bencana Dominan
                        </h4>
                        <div class="space-y-3">
                            <div v-for="disaster in dominantDisasters" :key="disaster.type" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: disaster.color }"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ disaster.name }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ disaster.count.toLocaleString() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rekomendasi -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <Shield class="h-5 w-5 text-green-600 dark:text-green-400" />
                            Rekomendasi Mitigasi
                        </h4>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Prioritaskan pembangunan infrastruktur tanggap bencana di provinsi dengan kejadian tertinggi
                                </p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Perkuat sistem peringatan dini terutama untuk bencana banjir dan tanah longsor
                                </p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Tingkatkan kapasitas respons darurat dan evakuasi di daerah rawan bencana
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Sumber dan Update -->
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                <Database class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-900 dark:text-white">Sumber Data</h5>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Badan Nasional Penanggulangan Bencana (BNPB) Indonesia
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Terakhir diperbarui</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ new Date().toLocaleDateString('id-ID') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <Info class="h-4 w-4" />
                            <span>
                                Data ini mencakup kejadian bencana alam di seluruh Indonesia berdasarkan laporan resmi BNPB.
                                Peta interaktif menampilkan tingkat risiko per provinsi dengan color coding untuk memudahkan analisis.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-lg p-6 border border-red-200 dark:border-red-800">
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Siaga Bencana, Selamatkan Nyawa</h4>
                        <p class="text-gray-700 dark:text-gray-300 mb-4">
                            Kenali risiko bencana di daerah Anda dan persiapkan rencana tanggap darurat bersama keluarga
                        </p>
                        <div class="flex items-center justify-center gap-4 flex-wrap">
                            <a
                                href="https://bnpb.go.id"
                                target="_blank"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Portal Resmi BNPB
                            </a>
                            <a
                                href="https://magma.esdm.go.id/"
                                target="_blank"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                Info Gunung Api
                            </a>
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
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { type BreadcrumbItem } from '@/types';
import { AlertTriangle, Users, Heart, Search, BarChart3, Shield, Database, Info } from 'lucide-vue-next';

// Types
interface DisasterFeature {
    type: 'Feature';
    properties: {
        wilayah: string;
        jumlah_kejadian: number;
        meninggal: number;
        hilang: number;
        terluka: number;
        banjir: number;
        karhutla: number;
        tanah_longsor: number;
        puting_beliung: number;
        terendam: number;
        [key: string]: any;
    };
    geometry: {
        type: 'MultiPolygon';
        coordinates: number[][][][];
    };
}

interface DisasterData {
    type: 'FeatureCollection';
    features: DisasterFeature[];
}

// Breadcrumbs
const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Peta Bencana', href: '#' }
];

// Reactive state
const mapContainer = ref();
const loadingData = ref(true);
const showLegend = ref(true);
const selectedDisasters = ref(['all']);
const disasterData = ref<DisasterData | null>(null);

// Map instance
let map: any = null;
let geoJsonLayer: any = null;

// Computed statistics
const totalIncidents = computed(() => {
    if (!disasterData.value) return 0;
    return disasterData.value.features.reduce((sum, feature) => sum + (feature.properties.jumlah_kejadian || 0), 0);
});

const totalDeaths = computed(() => {
    if (!disasterData.value) return 0;
    return disasterData.value.features.reduce((sum, feature) => sum + (feature.properties.meninggal || 0), 0);
});

const totalInjured = computed(() => {
    if (!disasterData.value) return 0;
    return disasterData.value.features.reduce((sum, feature) => sum + (feature.properties.terluka || 0), 0);
});

const totalMissing = computed(() => {
    if (!disasterData.value) return 0;
    return disasterData.value.features.reduce((sum, feature) => sum + (feature.properties.hilang || 0), 0);
});

const topProvinces = computed(() => {
    if (!disasterData.value) return [];
    return disasterData.value.features
        .map(feature => ({
            wilayah: feature.properties.wilayah,
            jumlah_kejadian: feature.properties.jumlah_kejadian || 0
        }))
        .sort((a, b) => b.jumlah_kejadian - a.jumlah_kejadian)
        .slice(0, 10);
});

// Computed for dominant disasters analysis
const dominantDisasters = computed(() => {
    if (!disasterData.value) return [];

    const totals = {
        banjir: 0,
        karhutla: 0,
        tanah_longsor: 0,
        puting_beliung: 0
    };

    disasterData.value.features.forEach(feature => {
        totals.banjir += feature.properties.banjir || 0;
        totals.karhutla += feature.properties.karhutla || 0;
        totals.tanah_longsor += feature.properties.tanah_longsor || 0;
        totals.puting_beliung += feature.properties.puting_beliung || 0;
    });

    return [
        { type: 'banjir', name: 'Banjir', count: totals.banjir, color: '#3b82f6' },
        { type: 'karhutla', name: 'Kebakaran Hutan', count: totals.karhutla, color: '#dc2626' },
        { type: 'tanah_longsor', name: 'Tanah Longsor', count: totals.tanah_longsor, color: '#92400e' },
        { type: 'puting_beliung', name: 'Puting Beliung', count: totals.puting_beliung, color: '#6b7280' }
    ].sort((a, b) => b.count - a.count);
});

// Color scale for provinces based on incident count
const getProvinceColor = (incidentCount: number): string => {
    if (incidentCount > 100) return '#ef4444'; // red-500
    if (incidentCount > 50) return '#f97316'; // orange-500
    if (incidentCount > 10) return '#eab308'; // yellow-500
    return '#22c55e'; // green-500
};

// Fetch disaster data from BNPB API
const fetchDisasterData = async () => {
    try {
        loadingData.value = true;
        const response = await fetch('https://gis.bnpb.go.id/dev/api/poligon/');
        const data = await response.json();
        disasterData.value = data;
    } catch (error) {
        console.error('Error fetching disaster data:', error);
    } finally {
        loadingData.value = false;
    }
};

// Initialize map
const initializeMap = async () => {
    if (typeof window !== 'undefined' && mapContainer.value) {
        const L = await import('leaflet');

        // Initialize map centered on Indonesia
        map = L.map(mapContainer.value).setView([-2.5489, 118.0149], 5);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        // Load and display disaster data
        await fetchDisasterData();
        updateMapData();
    }
};

// Update map with disaster data
const updateMapData = async () => {
    if (!map || !disasterData.value) return;

    const L = await import('leaflet');

    // Remove existing GeoJSON layer
    if (geoJsonLayer) {
        map.removeLayer(geoJsonLayer);
    }

    // Add GeoJSON layer with styling
    geoJsonLayer = L.geoJSON(disasterData.value, {
        style: (feature) => {
            const incidentCount = feature?.properties?.jumlah_kejadian || 0;
            return {
                fillColor: getProvinceColor(incidentCount),
                weight: 2,
                opacity: 1,
                color: '#fff',
                dashArray: '',
                fillOpacity: 0.7
            };
        },
        onEachFeature: (feature, layer) => {
            const props = feature.properties;
            const popupContent = `
                <div class="p-2">
                    <h3 class="font-bold text-lg mb-2">${props.wilayah}</h3>
                    <div class="space-y-1 text-sm">
                        <div><strong>Total Kejadian:</strong> ${(props.jumlah_kejadian || 0).toLocaleString()}</div>
                        <div><strong>Korban Meninggal:</strong> ${(props.meninggal || 0).toLocaleString()}</div>
                        <div><strong>Korban Luka:</strong> ${(props.terluka || 0).toLocaleString()}</div>
                        <div><strong>Orang Hilang:</strong> ${(props.hilang || 0).toLocaleString()}</div>
                        <hr class="my-2">
                        <div><strong>Banjir:</strong> ${(props.banjir || 0).toLocaleString()}</div>
                        <div><strong>Kebakaran Hutan:</strong> ${(props.karhutla || 0).toLocaleString()}</div>
                        <div><strong>Tanah Longsor:</strong> ${(props.tanah_longsor || 0).toLocaleString()}</div>
                        <div><strong>Puting Beliung:</strong> ${(props.puting_beliung || 0).toLocaleString()}</div>
                    </div>
                </div>
            `;

            layer.bindPopup(popupContent);

            // Hover effects
            layer.on({
                mouseover: function(e) {
                    const layer = e.target;
                    layer.setStyle({
                        weight: 3,
                        color: '#666',
                        dashArray: '',
                        fillOpacity: 0.9
                    });
                    layer.bringToFront();
                },
                mouseout: function(e) {
                    geoJsonLayer.resetStyle(e.target);
                }
            });
        }
    }).addTo(map);

    // Fit map to bounds
    map.fitBounds(geoJsonLayer.getBounds());
};

// Watch for filter changes
watch(selectedDisasters, () => {
    // In a real implementation, you would filter the data here
    // For now, we'll just update the map with all data
    updateMapData();
});

// Lifecycle hooks
onMounted(() => {
    initializeMap();
});

onBeforeUnmount(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<style scoped>
/* Custom styles for leaflet popups */
:global(.leaflet-popup-content-wrapper) {
    border-radius: 8px;
}

:global(.leaflet-popup-content) {
    margin: 0;
    font-family: inherit;
}
</style>