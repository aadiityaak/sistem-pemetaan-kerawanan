<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

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
    provinsi: { id: number; nama: string };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number };
    category: { id: number; name: string; slug: string; color: string; icon?: string; image_url?: string };
    sub_category: { id: number; name: string; slug: string; icon?: string; image_url?: string };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
    image_url?: string;
}

interface SubCategory {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    icon?: string;
    image_url?: string;
}

interface Statistics {
    totalData: number;
    totalProvinsi: number;
    totalKabupatenKota: number;
    totalKecamatan: number;
    totalTerdampak: number;
    totalSubCategories: number;
    dataBySubCategory: Record<string, { name: string; icon: string; image_url?: string; count: number }>;
    allSubCategoriesData: Record<string, { name: string; icon: string; image_url?: string; count: number }>;
    dataByProvinsi: Record<string, number>;
    dataBySeverity: Record<string, number>;
    dataByStatus: Record<string, number>;
}

const props = defineProps<{
    monitoringData: MonitoringData[];
    selectedCategory?: Category | null;
    selectedSubCategory?: SubCategory | null;
    selectedMonth?: string | null;
    selectedYear?: string | null;
    categories: Category[];
    subCategories: SubCategory[];
    statistics: Statistics;
    recentActivities: MonitoringData[];
}>();

// Dynamic breadcrumbs based on selected category and subcategory
const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const base = [{ title: 'Dashboard', href: '/dashboard' }];
    if (props.selectedCategory) {
        base.push({
            title: props.selectedCategory.name,
            href: `/dashboard?category=${props.selectedCategory.slug}`,
        });
        if (props.selectedSubCategory) {
            base.push({
                title: props.selectedSubCategory.name,
                href: `/dashboard?category=${props.selectedCategory.slug}&subcategory=${props.selectedSubCategory.slug}`,
            });
        }
    }
    return base;
});

// Map related refs
let map: any;
const mapContainer = ref();

// Category color mapping for theming
const categoryThemes: Record<string, { color: string; icon: string; bgColor: string }> = {
    keamanan: { color: '#DC2626', icon: '🛡️', bgColor: 'bg-red-100 dark:bg-red-900' },
    ideologi: { color: '#7C3AED', icon: '🏛️', bgColor: 'bg-purple-100 dark:bg-purple-900' },
    politik: { color: '#059669', icon: '🗳️', bgColor: 'bg-green-100 dark:bg-green-900' },
    ekonomi: { color: '#D97706', icon: '💰', bgColor: 'bg-orange-100 dark:bg-orange-900' },
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
        .sort(([, a], [, b]) => (b.count as number) - (a.count as number))
        .slice(0, 5);
});

// Computed untuk menampilkan semua sub kategori dalam category (untuk analytics card)
const allSubCategoriesDisplay = computed(() => {
    return Object.entries(props.statistics.allSubCategoriesData)
        .sort(([, a], [, b]) => (b.count as number) - (a.count as number))
        .slice(0, 8); // Show up to 8 subcategories
});

const topDataByProvinsi = computed(() => {
    return Object.entries(props.statistics.dataByProvinsi)
        .sort(([, a], [, b]) => (b as number) - (a as number))
        .slice(0, 5);
});

// Helper functions
const getSeverityLabel = (severity: string): string => {
    const labels: Record<string, string> = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        critical: 'Kritis',
    };
    return labels[severity] || severity;
};

// Helper functions for table display
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getLevelBadgeClass = (level: string): string => {
    const classes: Record<string, string> = {
        low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[level] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getStatusBadgeClass = (status: string): string => {
    const classes: Record<string, string> = {
        active: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        monitoring: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        archived: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        active: 'Aktif',
        resolved: 'Selesai',
        monitoring: 'Dalam Proses',
        archived: 'Arsip',
    };
    return labels[status] || status;
};

// Helper function to build URL with all filters
const buildFilterUrl = (params: Record<string, string | null>) => {
    const urlParams = new URLSearchParams();
    
    Object.entries(params).forEach(([key, value]) => {
        if (value) {
            urlParams.append(key, value);
        }
    });
    
    const queryString = urlParams.toString();
    return `/dashboard${queryString ? `?${queryString}` : ''}`;
};

// Function to change category filter
const selectCategory = (categorySlug: string | null) => {
    router.get(buildFilterUrl({
        category: categorySlug,
        subcategory: categorySlug ? props.selectedSubCategory?.slug || null : null,
        month: props.selectedMonth,
        year: props.selectedYear,
    }));
};

// Function to change subcategory filter
const selectSubCategory = (subCategorySlug: string | null) => {
    router.get(buildFilterUrl({
        category: props.selectedCategory?.slug || null,
        subcategory: subCategorySlug,
        month: props.selectedMonth,
        year: props.selectedYear,
    }));
};

// Function to change month filter
const selectMonth = (month: string | null) => {
    router.get(buildFilterUrl({
        category: props.selectedCategory?.slug || null,
        subcategory: props.selectedSubCategory?.slug || null,
        month: month,
        year: props.selectedYear,
    }));
};

// Function to change year filter
const selectYear = (year: string | null) => {
    router.get(buildFilterUrl({
        category: props.selectedCategory?.slug || null,
        subcategory: props.selectedSubCategory?.slug || null,
        month: props.selectedMonth,
        year: year,
    }));
};

// Generate month and year options
const monthOptions = [
    { value: '1', label: 'Januari' },
    { value: '2', label: 'Februari' },
    { value: '3', label: 'Maret' },
    { value: '4', label: 'April' },
    { value: '5', label: 'Mei' },
    { value: '6', label: 'Juni' },
    { value: '7', label: 'Juli' },
    { value: '8', label: 'Agustus' },
    { value: '9', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const yearOptions = Array.from({ length: 10 }, (_, i) => ({
    value: String(currentYear - i),
    label: String(currentYear - i)
}));

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
                attribution: '© OpenStreetMap contributors',
            }).addTo(map);

            // Add markers for monitoring data
            props.monitoringData.forEach((data: MonitoringData) => {
                const severityConfig = severityIcons[data.severity_level] || severityIcons['medium'];

                // Create custom HTML marker with theme color if category specific
                const markerColor = props.selectedCategory ? currentTheme.value.color : severityConfig.color;

                // Determine marker icon: sub category image > sub category icon > category theme icon > severity icon
                let markerIcon = severityConfig.icon; // Default to severity icon
                let hasCustomImage = false;
                
                if (props.selectedCategory) {
                    markerIcon = currentTheme.value.icon; // Use category theme icon if filtered
                }
                
                if (data.sub_category.image_url) {
                    // If subcategory has custom image, use a generic map marker icon but mark as custom
                    markerIcon = '📍'; // Generic pin icon for custom image categories
                    hasCustomImage = true;
                } else if (data.sub_category.icon) {
                    markerIcon = data.sub_category.icon; // Use sub category icon if available
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
                    iconAnchor: [12, 12],
                });

                const marker = L.marker([data.latitude, data.longitude], {
                    icon: customIcon,
                }).addTo(map);

                // Add popup with sub category icon or image if available  
                const popupIcon = data.sub_category.image_url 
                    ? `<img src="${data.sub_category.image_url}" alt="Subcategory" style="width: 16px; height: 16px; object-fit: contain; border-radius: 2px;">` 
                    : `<span style="font-size: 16px;">${data.sub_category.icon || '📊'}</span>`;
                marker.bindPopup(`
                    <div class="p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex items-center justify-center" style="min-width: 20px;">${popupIcon}</div>
                            <div class="font-semibold text-sm" style="color: ${markerColor}">${data.title}</div>
                        </div>
                        <div class="text-xs text-gray-600 mt-1">
                            <strong>Kategori:</strong> ${data.category.name} - ${data.sub_category.name}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            <strong>Lokasi:</strong> ${data.provinsi.nama}, ${data.kabupaten_kota.nama}
                        </div>
                        ${
                            data.jumlah_terdampak
                                ? `
                        <div class="text-xs text-gray-600 mt-1">
                            <strong>Terdampak:</strong> ${data.jumlah_terdampak.toLocaleString()} orang
                        </div>
                        `
                                : ''
                        }
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
    <Head :title="selectedSubCategory 
        ? `Dashboard ${selectedCategory?.name} - ${selectedSubCategory.name}` 
        : selectedCategory 
        ? `Dashboard ${selectedCategory.name}` 
        : 'Dashboard'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header with Category Filter -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div
                            class="mr-4 flex h-12 w-12 items-center justify-center rounded-lg"
                            :class="selectedCategory?.image_url ? 'bg-gray-50 dark:bg-gray-800' : (selectedCategory ? currentTheme.bgColor : 'bg-gray-100 dark:bg-gray-900')"
                        >
                            <img v-if="selectedCategory?.image_url" :src="selectedCategory.image_url" alt="Category icon" class="h-10 w-10 object-contain rounded" />
                            <span v-else class="text-2xl">{{ selectedCategory ? (selectedCategory.icon || currentTheme.icon) : '📊' }}</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>Dashboard</span>
                                <template v-if="selectedSubCategory">
                                    <span>{{ selectedSubCategory.name }}</span>
                                </template>
                                <span v-if="!selectedCategory">Monitoring</span>
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ selectedSubCategory
                                    ? `Monitoring khusus subcategory ${selectedSubCategory.name}`
                                    : selectedCategory
                                    ? `Monitoring khusus kategori ${selectedCategory.name}`
                                    : 'Monitoring data komprehensif semua kategori'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Filter Dropdowns -->
                    <div class="flex flex-wrap gap-3">
                        <!-- Category Filter -->
                        <div class="relative">
                            <select
                                @change="selectCategory(($event.target as HTMLSelectElement).value || null)"
                                :value="selectedCategory?.slug || ''"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="category in categories" :key="category.id" :value="category.slug">
                                    {{ category.image_url ? '🖼️ ' : (category.icon ? category.icon + ' ' : '') }}{{ category.name }}
                                </option>
                            </select>
                        </div>

                        <!-- SubCategory Filter (only show when category is selected) -->
                        <div v-if="selectedCategory && subCategories.length > 0" class="relative">
                            <select
                                @change="selectSubCategory(($event.target as HTMLSelectElement).value || null)"
                                :value="selectedSubCategory?.slug || ''"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Sub Kategori</option>
                                <option v-for="subCategory in subCategories" :key="subCategory.id" :value="subCategory.slug">
                                    {{ subCategory.image_url ? '🖼️ ' : (subCategory.icon ? subCategory.icon + ' ' : '') }}{{ subCategory.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Year Filter -->
                        <div class="relative">
                            <select
                                @change="selectYear(($event.target as HTMLSelectElement).value || null)"
                                :value="selectedYear || ''"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Tahun</option>
                                <option v-for="year in yearOptions" :key="year.value" :value="year.value">
                                    {{ year.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Month Filter (only show when year is selected) -->
                        <div v-if="selectedYear" class="relative">
                            <select
                                @change="selectMonth(($event.target as HTMLSelectElement).value || null)"
                                :value="selectedMonth || ''"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Bulan</option>
                                <option v-for="month in monthOptions" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                <!-- Total Data -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg"
                            :class="selectedCategory ? currentTheme.bgColor : 'bg-blue-100 dark:bg-blue-900'"
                        >
                            <svg
                                class="h-5 w-5"
                                :class="selectedCategory ? 'text-white' : 'text-blue-600 dark:text-blue-400'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalData }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Total Data</p>
                    </div>
                </div>

                <!-- Total Terdampak -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalTerdampak.toLocaleString() }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Terdampak</p>
                    </div>
                </div>

                <!-- Total Sub Kategori / Kategori -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalSubCategories }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ selectedCategory ? 'Sub Kategori' : 'Kategori' }}</p>
                    </div>
                </div>

                <!-- Total Provinsi -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalProvinsi }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Provinsi</p>
                    </div>
                </div>

                <!-- Total Kabupaten/Kota -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900">
                            <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKabupatenKota }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Kab/Kota</p>
                    </div>
                </div>

                <!-- Total Kecamatan -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                            <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKecamatan }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Kecamatan</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Map -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 mb-7">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                🗺️ {{ selectedSubCategory 
                                    ? `Peta ${selectedSubCategory.name}` 
                                    : selectedCategory 
                                    ? `Peta ${selectedCategory.name}` 
                                    : 'Peta Monitoring Data' }}
                            </h3>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <span
                                    class="h-3 w-3 rounded-full"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                ></span>
                                <span>{{ selectedSubCategory 
                                    ? selectedSubCategory.name 
                                    : selectedCategory 
                                    ? selectedCategory.name 
                                    : 'Semua Data' }}</span>
                            </div>
                        </div>
                        <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                    </div>

                    <!-- Monitoring Data Table -->
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        📋 {{ selectedSubCategory 
                                            ? `Data ${selectedSubCategory.name}` 
                                            : selectedCategory 
                                            ? `Data ${selectedCategory.name}` 
                                            : 'Daftar Data Monitoring' }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Data monitoring yang ditampilkan pada peta di atas
                                    </p>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ monitoringData.length }} data
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Judul & Lokasi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Kategori
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Tanggal Kejadian
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Level
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Terdampak
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-if="monitoringData.length === 0">
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="font-medium">Tidak ada data monitoring</p>
                                                <p class="text-sm">{{ selectedCategory 
                                                    ? `Tidak ada data untuk kategori ${selectedCategory.name}` 
                                                    : 'Tidak ada data yang sesuai dengan filter yang dipilih' }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-else v-for="data in monitoringData.slice(0, 10)" :key="data.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ data.title }}</div>
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    {{ data.kecamatan?.nama || 'N/A' }}, {{ data.kabupaten_kota?.nama || 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm space-y-1">
                                                <div class="flex items-center gap-1">
                                                    <img v-if="data.category.image_url" :src="data.category.image_url" alt="Category icon" class="h-4 w-4 object-contain" />
                                                    <span v-else-if="data.category.icon" class="text-xs">{{ data.category.icon }}</span>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ data.category.name }}</span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <img v-if="data.sub_category.image_url" :src="data.sub_category.image_url" alt="Subcategory icon" class="h-3 w-3 object-contain" />
                                                    <span v-else-if="data.sub_category.icon" class="text-xs">{{ data.sub_category.icon }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ data.sub_category.name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ formatDate(data.incident_date) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                :class="getLevelBadgeClass(data.severity_level)"
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            >
                                                {{ getSeverityLabel(data.severity_level) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span 
                                                :class="getStatusBadgeClass(data.status)" 
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            >
                                                {{ getStatusLabel(data.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ data.jumlah_terdampak?.toLocaleString() || '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="`/monitoring-data/${data.id}`"
                                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                    title="Lihat Detail"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        />
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        />
                                                    </svg>
                                                </Link>
                                                <Link
                                                    :href="`/monitoring-data/${data.id}/edit`"
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    title="Edit"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                        />
                                                    </svg>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- View All Button -->
                        <div v-if="monitoringData.length > 10" class="border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-700">
                            <div class="flex justify-center">
                                <Link
                                    href="/monitoring-data"
                                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Lihat Semua Data Monitoring ({{ monitoringData.length }})
                                    <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="space-y-6">
                    <!-- Top Sub Categories / Categories -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            🎯 {{ selectedCategory 
                                ? `Sub Kategori ${selectedCategory.name}` 
                                : 'Top Kategori' }}
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="[subCategoryId, subCategoryData] in allSubCategoriesDisplay"
                                :key="subCategoryId"
                                class="flex items-center justify-between"
                                :class="selectedSubCategory && subCategoryData.name === selectedSubCategory.name ? 'bg-gray-200 dark:bg-gray-700 rounded-lg p-2 mb-2' : ''"
                            >
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center justify-center w-5 h-5">
                                        <img v-if="subCategoryData.image_url" :src="subCategoryData.image_url" alt="Subcategory" class="w-4 h-4 object-contain rounded" />
                                        <span v-else class="text-lg">{{ subCategoryData.icon }}</span>
                                    </div>
                                    <span 
                                        class="text-sm font-medium"
                                        :class="selectedSubCategory && subCategoryData.name === selectedSubCategory.name 
                                            ? 'text-gray-900 dark:text-white font-semibold' 
                                            : 'text-gray-900 dark:text-white'"
                                    >
                                        {{ subCategoryData.name }}
                                    </span>
                                </div>
                                <span
                                    class="rounded px-2 py-1 text-xs font-medium text-white"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                >
                                    {{ subCategoryData.count }} data
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Severity Level Distribution -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">📊 Tingkat Keparahan</h3>
                        <div class="space-y-3">
                            <div v-for="(config, severity) in severityIcons" :key="severity" class="flex items-center gap-3">
                                <span class="text-lg">{{ config.icon }}</span>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getSeverityLabel(severity) }}</span>
                                    <div class="mt-1 h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full"
                                            :style="{
                                                backgroundColor: config.color,
                                                width: `${((statistics.dataBySeverity[severity] || 0) / Math.max(...Object.values(statistics.dataBySeverity))) * 100}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ statistics.dataBySeverity[severity] || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Provinces -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">🏛️ Top Provinsi</h3>
                        <div class="space-y-2">
                            <div v-for="[provinsi, count] in topDataByProvinsi" :key="provinsi" class="flex items-center justify-between text-sm">
                                <span class="text-gray-900 dark:text-white">{{ provinsi }}</span>
                                <span
                                    class="rounded px-2 py-1 text-xs text-white"
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
