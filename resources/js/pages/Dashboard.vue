<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

interface MonitoringData {
    id: number;
    provinsi_id: number;
    kabupaten_kota_id: number;
    kecamatan_id: number;
    category_id: number;
    sub_category_id: number;
    latitude: number;
    longitude: number;
    title: string;
    description: string;
    severity_level: string;
    status: string;
    incident_date: string;
    created_at: string;
    updated_at: string;
    provinsi: { id: number; nama: string; };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number; };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number; };
    category: { id: number; name: string; slug: string; color: string; };
    sub_category: { id: number; name: string; slug: string; };
}

interface Statistics {
    totalData: number;
    totalProvinsi: number;
    totalKabupatenKota: number;
    totalKecamatan: number;
    totalCategories: number;
    dataByCategory: Record<string, number>;
    dataByProvinsi: Record<string, number>;
    dataBySeverity: Record<string, number>;
}

const props = defineProps<{
    monitoringData: MonitoringData[];
    statistics: Statistics;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Map related refs
let map: any;
const mapContainer = ref();
const selectedData = ref<MonitoringData | null>(null);

// Icon mapping untuk setiap severity level
const severityIcons: Record<string, { color: string; icon: string }> = {
    low: { color: '#10B981', icon: '🟢' },      // Green
    medium: { color: '#F59E0B', icon: '🟡' },   // Yellow/Orange
    high: { color: '#EF4444', icon: '🟠' },     // Red
    critical: { color: '#DC2626', icon: '🔴' }, // Dark Red
};

// Computed untuk menghitung top data by category
const topDataByCategory = computed(() => {
    return Object.entries(props.statistics.dataByCategory)
        .sort(([,a], [,b]) => (b as number) - (a as number))
        .slice(0, 5);
});

const topDataByProvinsi = computed(() => {
    return Object.entries(props.statistics.dataByProvinsi)
        .sort(([,a], [,b]) => (b as number) - (a as number))
        .slice(0, 5);
});

const closeModal = () => {
    selectedData.value = null;
};

// Helper function untuk format label severity
const getSeverityLabel = (severity: string): string => {
    const labels: Record<string, string> = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        critical: 'Kritis'
    };
    return labels[severity] || severity;
};

// Initialize map
onMounted(async () => {
    if (typeof window !== 'undefined') {
        // Dynamic import Leaflet
        const L = await import('leaflet');
        
        // Initialize map
        if (mapContainer.value) {
            map = L.map(mapContainer.value).setView([-2.5489, 118.0149], 5); // Indonesia center

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add markers for monitoring data
            props.monitoringData.forEach((data: MonitoringData) => {
                const severityConfig = severityIcons[data.severity_level] || severityIcons['medium'];
                
                // Create custom HTML marker
                const customIcon = L.divIcon({
                    html: `<div style="
                        background-color: ${severityConfig.color}; 
                        border: 2px solid white; 
                        border-radius: 50%; 
                        width: 20px; 
                        height: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 10px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    ">${severityConfig.icon}</div>`,
                    className: 'custom-marker',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });

                const marker = L.marker([data.latitude, data.longitude], { 
                    icon: customIcon 
                }).addTo(map);

                // Add popup
                marker.bindPopup(`
                    <div class="p-2">
                        <div class="font-semibold text-sm">${data.title}</div>
                        <div class="text-xs text-gray-600 mt-1">
                            ${data.category.name} - ${data.sub_category.name}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            ${data.provinsi.nama}, ${data.kabupaten_kota.nama}
                        </div>
                        <div class="text-xs mt-1">
                            <span class="px-2 py-1 bg-gray-100 rounded text-gray-700">
                                ${getSeverityLabel(data.severity_level)}
                            </span>
                        </div>
                    </div>
                `);

                // Add click event
                marker.on('click', () => {
                    selectedData.value = data;
                });
            });
        }
    }
});
</script>

<template>
    <Head title="Dashboard" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Total Data -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalData }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Data</p>
                        </div>
                    </div>
                </div>

                <!-- Total Kategori -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalCategories }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kategori</p>
                        </div>
                    </div>
                </div>

                <!-- Total Provinsi -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalProvinsi }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Provinsi</p>
                        </div>
                    </div>
                </div>

                <!-- Total Kabupaten/Kota -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKabupatenKota }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kab/Kota</p>
                        </div>
                    </div>
                </div>

                <!-- Total Kecamatan -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg mr-4">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKecamatan }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Kecamatan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Map -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Peta Monitoring Data</h3>
                        <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                    </div>
                </div>

                <!-- Top Categories -->
                <div class="space-y-6">
                    <!-- Top Categories by Data Count -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Kategori</h3>
                        <div class="space-y-3">
                            <div v-for="[category, count] in topDataByCategory" :key="category" class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📊</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ category }}</span>
                                </div>
                                <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs font-medium">
                                    {{ count }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Severity Legend -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Level Severity</h3>
                        <div class="space-y-2">
                            <div v-for="(config, severity) in severityIcons" :key="severity" class="flex items-center gap-2">
                                <span class="text-lg">{{ config.icon }}</span>
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ getSeverityLabel(severity) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for selected data -->
        <div v-if="selectedData" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6" @click.stop>
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ severityIcons[selectedData.severity_level]?.icon || '📊' }}</span>
                        <span class="text-gray-900 dark:text-white font-semibold">{{ selectedData.title }}</span>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori:</span>
                        <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ selectedData.category.name }} - {{ selectedData.sub_category.name }}</span>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Lokasi:</span>
                        <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ selectedData.provinsi.nama }}, {{ selectedData.kabupaten_kota.nama }}, {{ selectedData.kecamatan.nama }}</span>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Level:</span>
                        <span class="ml-2 px-2 py-1 text-xs rounded" :style="{ backgroundColor: severityIcons[selectedData.severity_level]?.color || '#6B7280', color: 'white' }">
                            {{ getSeverityLabel(selectedData.severity_level) }}
                        </span>
                    </div>
                    
                    <div v-if="selectedData.description">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi:</span>
                        <p class="text-sm text-gray-900 dark:text-white mt-1">{{ selectedData.description }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Koordinat:</span>
                        <span class="ml-2 text-sm text-gray-900 dark:text-white">{{ selectedData.latitude }}, {{ selectedData.longitude }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>