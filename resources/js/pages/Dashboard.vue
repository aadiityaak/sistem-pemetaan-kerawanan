<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
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
    jumlah_terdampak: number | null;
    severity_level: string;
    status: string;
    incident_date: string;
    created_at: string;
    updated_at: string;
    provinsi: { id: number; nama: string; };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number; };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number; };
    category: { id: number; name: string; slug: string; color: string; };
    sub_category: { id: number; name: string; slug: string; icon?: string; };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
}

interface Statistics {
    totalData: number;
    totalProvinsi: number;
    totalKabupatenKota: number;
    totalKecamatan: number;
    totalTerdampak: number;
    totalSubCategories: number;
    dataBySubCategory: Record<string, { name: string; icon: string; count: number }>;
    dataByProvinsi: Record<string, number>;
    dataBySeverity: Record<string, number>;
    dataByStatus: Record<string, number>;
}

const props = defineProps<{
    monitoringData: MonitoringData[];
    selectedCategory?: Category | null;
    categories: Category[];
    statistics: Statistics;
    recentActivities: MonitoringData[];
}>();

// Dynamic breadcrumbs based on selected category
const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const base = [{ title: 'Dashboard', href: '/dashboard' }];
    if (props.selectedCategory) {
        base.push({ 
            title: props.selectedCategory.name, 
            href: `/dashboard?category=${props.selectedCategory.slug}` 
        });
    }
    return base;
});

// Map related refs
let map: any;
const mapContainer = ref();

// Category color mapping for theming
const categoryThemes: Record<string, { color: string; icon: string; bgColor: string }> = {
    'keamanan': { color: '#DC2626', icon: '🛡️', bgColor: 'bg-red-100 dark:bg-red-900' },
    'ideologi': { color: '#7C3AED', icon: '🏛️', bgColor: 'bg-purple-100 dark:bg-purple-900' },
    'politik': { color: '#059669', icon: '🗳️', bgColor: 'bg-green-100 dark:bg-green-900' },
    'ekonomi': { color: '#D97706', icon: '💰', bgColor: 'bg-orange-100 dark:bg-orange-900' },
    'sosial-budaya': { color: '#0EA5E9', icon: '🤝', bgColor: 'bg-blue-100 dark:bg-blue-900' },
};

// Get current theme based on selected category
const currentTheme = computed(() => {
    if (props.selectedCategory) {
        return categoryThemes[props.selectedCategory.slug] || categoryThemes['keamanan'];
    }
    return { color: '#6B7280', icon: '📊', bgColor: 'bg-gray-100 dark:bg-gray-900' };
});

// Icon mapping untuk setiap severity level  
const severityIcons: Record<string, { color: string; icon: string }> = {
    low: { color: '#10B981', icon: '🟢' },      
    medium: { color: '#F59E0B', icon: '🟡' },   
    high: { color: '#EF4444', icon: '🟠' },     
    critical: { color: '#DC2626', icon: '🔴' }, 
};

// Computed untuk menghitung top data 
const topDataBySubCategory = computed(() => {
    return Object.entries(props.statistics.dataBySubCategory)
        .sort(([,a], [,b]) => (b.count as number) - (a.count as number))
        .slice(0, 5);
});

const topDataByProvinsi = computed(() => {
    return Object.entries(props.statistics.dataByProvinsi)
        .sort(([,a], [,b]) => (b as number) - (a as number))
        .slice(0, 5);
});

// Helper functions
const getSeverityLabel = (severity: string): string => {
    const labels: Record<string, string> = {
        low: 'Rendah',
        medium: 'Sedang', 
        high: 'Tinggi',
        critical: 'Kritis'
    };
    return labels[severity] || severity;
};

// Function to change category filter
const selectCategory = (categorySlug: string | null) => {
    if (categorySlug) {
        router.get(`/dashboard?category=${categorySlug}`);
    } else {
        router.get('/dashboard');
    }
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
                
                // Create custom HTML marker with theme color if category specific
                const markerColor = props.selectedCategory ? currentTheme.value.color : severityConfig.color;
                
                // Determine marker icon: sub category icon > category theme icon > severity icon
                let markerIcon = severityConfig.icon; // Default to severity icon
                if (props.selectedCategory) {
                    markerIcon = currentTheme.value.icon; // Use category theme icon if filtered
                }
                if (data.sub_category.icon) {
                    markerIcon = data.sub_category.icon; // Use sub category icon if available (priority)
                }
                
                const customIcon = L.divIcon({
                    html: `<div style="
                        background-color: ${markerColor}; 
                        border: 2px solid white; 
                        border-radius: 50%; 
                        width: 24px; 
                        height: 24px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 12px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                    ">${markerIcon}</div>`,
                    className: 'custom-marker',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });

                const marker = L.marker([data.latitude, data.longitude], { 
                    icon: customIcon 
                }).addTo(map);

                // Add popup with sub category icon if available
                const popupIcon = data.sub_category.icon || '📊';
                marker.bindPopup(`
                    <div class="p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-lg">${popupIcon}</span>
                            <div class="font-semibold text-sm" style="color: ${markerColor}">${data.title}</div>
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                            <strong>Kategori:</strong> ${data.category.name} - ${data.sub_category.name}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            <strong>Lokasi:</strong> ${data.provinsi.nama}, ${data.kabupaten_kota.nama}
                        </div>
                        ${data.jumlah_terdampak ? `
                        <div class="text-xs text-gray-600 mt-1">
                            <strong>Terdampak:</strong> ${data.jumlah_terdampak.toLocaleString()} orang
                        </div>
                        ` : ''}
                        <div class="text-xs mt-2">
                            <span class="px-2 py-1 bg-gray-100 rounded text-gray-700 text-xs">
                                Level: ${getSeverityLabel(data.severity_level)}
                            </span>
                        </div>
                    </div>
                `);
            });
        }
    }
});
</script>

<template>
    <Head :title="selectedCategory ? `Dashboard ${selectedCategory.name}` : 'Dashboard'" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header with Category Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div 
                            class="flex items-center justify-center w-12 h-12 rounded-lg mr-4"
                            :class="selectedCategory ? currentTheme.bgColor : 'bg-gray-100 dark:bg-gray-900'"
                        >
                            <span class="text-2xl">{{ selectedCategory ? currentTheme.icon : '📊' }}</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ selectedCategory ? `Dashboard ${selectedCategory.name}` : 'Dashboard Monitoring' }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ selectedCategory ? `Monitoring khusus kategori ${selectedCategory.name}` : 'Monitoring data komprehensif semua kategori' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Category Filter Dropdown -->
                    <div class="relative">
                        <select 
                            @change="selectCategory(($event.target as HTMLSelectElement).value || null)"
                            :value="selectedCategory?.slug || ''"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="category in categories" :key="category.id" :value="category.slug">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <!-- Total Data -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col items-center text-center">
                        <div 
                            class="flex items-center justify-center w-10 h-10 rounded-lg mb-2"
                            :class="selectedCategory ? currentTheme.bgColor : 'bg-blue-100 dark:bg-blue-900'"
                        >
                            <svg 
                                class="w-5 h-5" 
                                :class="selectedCategory ? 'text-white' : 'text-blue-600 dark:text-blue-400'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalData }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Total Data</p>
                    </div>
                </div>

                <!-- Total Terdampak -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-lg mb-2">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalTerdampak.toLocaleString() }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Terdampak</p>
                    </div>
                </div>

                <!-- Total Sub Kategori / Kategori -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg mb-2">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalSubCategories }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ selectedCategory ? 'Sub Kategori' : 'Kategori' }}</p>
                    </div>
                </div>

                <!-- Total Provinsi -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg mb-2">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalProvinsi }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Provinsi</p>
                    </div>
                </div>

                <!-- Total Kabupaten/Kota -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg mb-2">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKabupatenKota }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Kab/Kota</p>
                    </div>
                </div>

                <!-- Total Kecamatan -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center justify-center w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg mb-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKecamatan }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Kecamatan</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Map -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                🗺️ {{ selectedCategory ? `Peta ${selectedCategory.name}` : 'Peta Monitoring Data' }}
                            </h3>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <span 
                                    class="w-3 h-3 rounded-full"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                ></span>
                                <span>{{ selectedCategory ? selectedCategory.name : 'Semua Data' }}</span>
                            </div>
                        </div>
                        <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="space-y-6">
                    <!-- Top Sub Categories / Categories -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            🎯 {{ selectedCategory ? `Sub Kategori ${selectedCategory.name}` : 'Top Kategori' }}
                        </h3>
                        <div class="space-y-3">
                            <div v-for="[subCategoryId, subCategoryData] in topDataBySubCategory" :key="subCategoryId" class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ subCategoryData.icon }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ subCategoryData.name }}</span>
                                </div>
                                <span 
                                    class="px-2 py-1 rounded text-xs font-medium text-white"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                >
                                    {{ subCategoryData.count }} data
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Severity Level Distribution -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📊 Tingkat Keparahan</h3>
                        <div class="space-y-3">
                            <div v-for="(config, severity) in severityIcons" :key="severity" class="flex items-center gap-3">
                                <span class="text-lg">{{ config.icon }}</span>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getSeverityLabel(severity) }}</span>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div 
                                            class="h-2 rounded-full" 
                                            :style="{ 
                                                backgroundColor: config.color, 
                                                width: `${(statistics.dataBySeverity[severity] || 0) / Math.max(...Object.values(statistics.dataBySeverity)) * 100}%` 
                                            }"
                                        ></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ statistics.dataBySeverity[severity] || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Provinces -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🏛️ Top Provinsi</h3>
                        <div class="space-y-2">
                            <div v-for="[provinsi, count] in topDataByProvinsi" :key="provinsi" class="flex justify-between items-center text-sm">
                                <span class="text-gray-900 dark:text-white">{{ provinsi }}</span>
                                <span 
                                    class="px-2 py-1 rounded text-xs text-white"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                >
                                    {{ count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
