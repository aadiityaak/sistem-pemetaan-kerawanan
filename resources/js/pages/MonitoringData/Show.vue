<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

interface GalleryItem {
    path: string;
    url: string;
}

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
    sumber_berita?: string;
    jumlah_terdampak: number | null;
    severity_level: string;
    status: string;
    incident_date: string;
    created_at: string;
    updated_at: string;
    additional_data: Record<string, any>;
    gallery?: GalleryItem[];
    video_path?: string;
    video_url?: string;
    provinsi: { id: number; nama: string };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number };
    category: { id: number; name: string; slug: string; color: string; icon?: string; image_url?: string };
    sub_category: { id: number; name: string; slug: string; icon?: string; image_url?: string };
}

const props = defineProps<{
    monitoringData: MonitoringData;
}>();

// Get current user and check edit permissions
const page = usePage();
const canEdit = computed(() => {
    const currentUser = page.props.auth.user;
    return currentUser && currentUser.role && ['super_admin', 'admin'].includes(currentUser.role);
});

// Build back URL with category filter
const backUrl = computed(() => {
    if (props.monitoringData.category?.slug) {
        return `/dashboard?category=${props.monitoringData.category.slug}`;
    }
    return '/dashboard';
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: backUrl.value,
    },
    {
        title: 'Detail Data',
        href: `/monitoring-data/${props.monitoringData.id}`,
    },
];

// Map related refs
let map: any;
const mapContainer = ref();

// Gallery carousel refs
const currentImageIndex = ref(0);
const showImageModal = ref(false);
const currentCarouselIndex = ref(0);

// Severity mapping
const severityConfig: Record<string, { label: string; color: string; bgColor: string; icon: string }> = {
    low: {
        label: 'Rendah',
        color: 'text-green-800',
        bgColor: 'bg-green-100',
        icon: '🟢',
    },
    medium: {
        label: 'Sedang',
        color: 'text-yellow-800',
        bgColor: 'bg-yellow-100',
        icon: '🟡',
    },
    high: {
        label: 'Tinggi',
        color: 'text-orange-800',
        bgColor: 'bg-orange-100',
        icon: '🟠',
    },
    critical: {
        label: 'Kritis',
        color: 'text-red-800',
        bgColor: 'bg-red-100',
        icon: '🔴',
    },
};

// Status mapping
const statusConfig: Record<string, { label: string; color: string; bgColor: string; icon: string }> = {
    active: {
        label: 'Aktif',
        color: 'text-blue-800',
        bgColor: 'bg-blue-100',
        icon: '🔵',
    },
    resolved: {
        label: 'Selesai',
        color: 'text-green-800',
        bgColor: 'bg-green-100',
        icon: '✅',
    },
    pending: {
        label: 'Pending',
        color: 'text-yellow-800',
        bgColor: 'bg-yellow-100',
        icon: '⏳',
    },
    cancelled: {
        label: 'Dibatalkan',
        color: 'text-gray-800',
        bgColor: 'bg-gray-100',
        icon: '❌',
    },
};

// Helper functions
const getSeverityConfig = (severity: string) => {
    return severityConfig[severity] || severityConfig['medium'];
};

const getStatusConfig = (status: string) => {
    return statusConfig[status] || statusConfig['active'];
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const printData = () => {
    if (typeof window !== 'undefined') {
        window.print();
    }
};

// Gallery carousel methods
const openImageModal = (index: number) => {
    currentImageIndex.value = index;
    showImageModal.value = true;
};

const closeImageModal = () => {
    showImageModal.value = false;
};

const nextImage = () => {
    if (props.monitoringData.gallery && currentImageIndex.value < props.monitoringData.gallery.length - 1) {
        currentImageIndex.value++;
    }
};

const prevImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    }
};

const goToImage = (index: number) => {
    currentImageIndex.value = index;
};

// Carousel navigation functions
const nextCarouselImage = () => {
    if (props.monitoringData.gallery && currentCarouselIndex.value < props.monitoringData.gallery.length - 1) {
        currentCarouselIndex.value++;
    }
};

const prevCarouselImage = () => {
    if (currentCarouselIndex.value > 0) {
        currentCarouselIndex.value--;
    }
};

const goToCarouselImage = (index: number) => {
    currentCarouselIndex.value = index;
};

// Initialize map
onMounted(async () => {
    if (typeof window !== 'undefined') {
        const L = await import('leaflet');

        if (mapContainer.value) {
            map = L.map(mapContainer.value).setView([props.monitoringData.latitude, props.monitoringData.longitude], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(map);

            // Add marker for the monitoring data location
            const severityConf = getSeverityConfig(props.monitoringData.severity_level);

            const customIcon = L.divIcon({
                html: `<div style="
                    background-color: ${severityConf.bgColor}; 
                    border: 2px solid white; 
                    border-radius: 50%; 
                    width: 30px; 
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 14px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                ">${severityConf.icon}</div>`,
                className: 'custom-marker',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });

            L.marker([props.monitoringData.latitude, props.monitoringData.longitude], {
                icon: customIcon,
            })
                .addTo(map)
                .bindPopup(
                    `
                    <div class="p-2">
                        <div class="font-semibold">${props.monitoringData.title}</div>
                        <div class="text-sm text-gray-600 mt-1">
                            ${props.monitoringData.category.name} - ${props.monitoringData.sub_category.name}
                        </div>
                    </div>
                `,
                )
                .openPopup();
        }
    }
});
</script>

<template>
    <Head :title="`Detail: ${monitoringData.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ monitoringData.title }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Detail data monitoring #{{ monitoringData.id }}</p>
                </div>
                <div class="flex gap-3">
                    <Link v-if="canEdit" :href="`/monitoring-data/${monitoringData.id}/edit`">
                        <Button size="sm">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Edit Data
                        </Button>
                    </Link>
                    <Link :href="backUrl">
                        <Button variant="outline" size="sm">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Information -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Basic Information -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Title -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Judul</label>
                                    <div class="text-gray-900 dark:text-white">{{ monitoringData.title }}</div>
                                </div>

                                <!-- Incident Date -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Kejadian</label>
                                    <div class="text-gray-900 dark:text-white">{{ formatDate(monitoringData.incident_date) }}</div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="monitoringData.description">
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</label>
                                <div
                                    class="prose prose-sm dark:prose-invert max-w-none text-gray-900 dark:text-white"
                                    v-html="monitoringData.description"
                                ></div>
                            </div>

                            <!-- Sumber Berita -->
                            <div v-if="monitoringData.sumber_berita">
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Sumber Berita</label>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"
                                        ></path>
                                    </svg>
                                    <span class="text-gray-900 dark:text-white">{{ monitoringData.sumber_berita }}</span>
                                </div>
                            </div>

                            <!-- Jumlah Terdampak -->
                            <div v-if="monitoringData.jumlah_terdampak">
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Terdampak</label>
                                <div class="text-gray-900 dark:text-white">
                                    <span class="text-2xl font-bold">{{ monitoringData.jumlah_terdampak.toLocaleString() }}</span>
                                    <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">orang</span>
                                </div>
                            </div>

                            <!-- Classification -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Severity & Status -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Level & Status</label>
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                            :class="[
                                                getSeverityConfig(monitoringData.severity_level).bgColor,
                                                getSeverityConfig(monitoringData.severity_level).color,
                                            ]"
                                        >
                                            {{ getSeverityConfig(monitoringData.severity_level).icon }}
                                            {{ getSeverityConfig(monitoringData.severity_level).label }}
                                        </span>
                                        <span
                                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                            :class="[getStatusConfig(monitoringData.status).bgColor, getStatusConfig(monitoringData.status).color]"
                                        >
                                            {{ getStatusConfig(monitoringData.status).icon }} {{ getStatusConfig(monitoringData.status).label }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Coordinates -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Koordinat</label>
                                    <div class="font-mono text-sm text-gray-900 dark:text-white">
                                        {{ monitoringData.latitude }}, {{ monitoringData.longitude }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video -->
                    <div
                        v-if="monitoringData.video_path"
                        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Video</h3>

                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600">
                            <video :src="monitoringData.video_url" controls class="aspect-video w-full object-cover" preload="metadata">
                                Browser Anda tidak mendukung tag video.
                            </video>
                        </div>
                    </div>

                    <!-- Category Information -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Kategori</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Kategori Utama</label>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center">
                                        <img
                                            v-if="monitoringData.category.image_url"
                                            :src="monitoringData.category.image_url"
                                            alt="Category icon"
                                            class="h-6 w-6 rounded object-contain"
                                        />
                                        <span v-else-if="monitoringData.category.icon" class="text-sm">{{ monitoringData.category.icon }}</span>
                                        <div
                                            v-else
                                            class="h-4 w-4 rounded-full border border-gray-200"
                                            :style="{ backgroundColor: monitoringData.category.color }"
                                        ></div>
                                    </div>
                                    <span class="text-gray-900 dark:text-white">{{ monitoringData.category.name }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Sub Kategori</label>
                                <div class="flex items-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center">
                                        <img
                                            v-if="monitoringData.sub_category.image_url"
                                            :src="monitoringData.sub_category.image_url"
                                            alt="Subcategory icon"
                                            class="h-5 w-5 rounded object-contain"
                                        />
                                        <span v-else-if="monitoringData.sub_category.icon" class="text-xs">{{
                                            monitoringData.sub_category.icon
                                        }}</span>
                                    </div>
                                    <span class="text-gray-900 dark:text-white">{{ monitoringData.sub_category.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Lokasi</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Provinsi</label>
                                <div class="text-gray-900 dark:text-white">{{ monitoringData.provinsi?.nama || 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Kabupaten/Kota</label>
                                <div class="text-gray-900 dark:text-white">{{ monitoringData.kabupaten_kota?.nama || 'N/A' }}</div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Kecamatan</label>
                                <div class="text-gray-900 dark:text-white">{{ monitoringData.kecamatan?.nama || 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Data -->
                    <div
                        v-if="monitoringData.additional_data && Object.keys(monitoringData.additional_data).length > 0"
                        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Data Tambahan</h3>

                        <div class="space-y-2">
                            <div v-for="(value, key) in monitoringData.additional_data" :key="key" class="grid grid-cols-3 gap-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ key }}</div>
                                <div class="col-span-2 text-sm text-gray-900 dark:text-white">
                                    {{ typeof value === 'object' ? JSON.stringify(value, null, 2) : value }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Map -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Lokasi di Peta</h3>
                        <div ref="mapContainer" class="h-64 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                    </div>

                    <!-- Gallery Carousel -->
                    <div
                        v-if="monitoringData.gallery && monitoringData.gallery.length > 0"
                        class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Galeri Foto</h3>

                        <!-- Main Image Display -->
                        <div class="relative mb-4 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600">
                            <img
                                :src="monitoringData.gallery[currentCarouselIndex].url"
                                :alt="`Gallery image ${currentCarouselIndex + 1}`"
                                class="aspect-4/3 w-full cursor-pointer object-cover"
                                @click="openImageModal(currentCarouselIndex)"
                            />

                            <!-- Navigation Arrows -->
                            <button
                                v-if="monitoringData.gallery.length > 1 && currentCarouselIndex > 0"
                                @click="prevCarouselImage"
                                class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white transition-colors hover:bg-black/70"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button
                                v-if="monitoringData.gallery.length > 1 && currentCarouselIndex < monitoringData.gallery.length - 1"
                                @click="nextCarouselImage"
                                class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white transition-colors hover:bg-black/70"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <!-- Image Counter -->
                            <div class="absolute right-2 bottom-2 rounded bg-black/50 px-2 py-1 text-xs text-white">
                                {{ currentCarouselIndex + 1 }} / {{ monitoringData.gallery.length }}
                            </div>

                            <!-- Click to enlarge indicator -->
                            <div class="absolute top-2 right-2 rounded bg-black/50 px-2 py-1 text-xs text-white">
                                <svg class="mr-1 inline h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    />
                                </svg>
                                Klik untuk perbesar
                            </div>
                        </div>

                        <!-- Thumbnail Navigation -->
                        <div v-if="monitoringData.gallery.length > 1" class="flex gap-2 overflow-x-auto pb-2">
                            <div
                                v-for="(image, index) in monitoringData.gallery"
                                :key="index"
                                class="h-16 w-16 flex-shrink-0 cursor-pointer rounded border-2 transition-all duration-200"
                                :class="[
                                    currentCarouselIndex === index
                                        ? 'border-blue-500'
                                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-600 dark:hover:border-gray-500',
                                ]"
                                @click="goToCarouselImage(index)"
                            >
                                <img :src="image.url" :alt="`Thumbnail ${index + 1}`" class="h-full w-full rounded object-cover" />
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Cepat</h3>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">ID Data</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">#{{ monitoringData.id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Dibuat</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(monitoringData.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Diperbarui</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(monitoringData.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Aksi</h3>

                        <div class="space-y-3">
                            <Link v-if="canEdit" :href="`/monitoring-data/${monitoringData.id}/edit`" class="block">
                                <Button class="w-full" variant="outline">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    Edit Data
                                </Button>
                            </Link>

                            <Button class="w-full" variant="outline" @click="printData">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                    />
                                </svg>
                                Cetak Data
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Modal/Carousel -->
        <div
            v-if="showImageModal && monitoringData.gallery && monitoringData.gallery.length > 0"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75 p-4"
            @click="closeImageModal"
        >
            <div class="relative max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-lg bg-white shadow-2xl dark:bg-gray-800" @click.stop>
                <!-- Close Button -->
                <button
                    @click="closeImageModal"
                    class="absolute top-4 right-4 z-10 rounded-full bg-black/50 p-2 text-white transition-all hover:bg-black/75"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Main Image Container -->
                <div class="relative bg-gray-100 dark:bg-gray-900">
                    <div class="flex max-h-[70vh] min-h-[60vh] items-center justify-center">
                        <img
                            :src="monitoringData.gallery[currentImageIndex].url"
                            :alt="`Gallery image ${currentImageIndex + 1}`"
                            class="max-h-full max-w-full object-contain"
                        />
                    </div>

                    <!-- Navigation Arrows -->
                    <button
                        v-if="currentImageIndex > 0"
                        @click="prevImage"
                        class="absolute top-1/2 left-4 -translate-y-1/2 rounded-full bg-white/80 p-3 text-gray-800 shadow-lg transition-all hover:bg-white/100 dark:bg-gray-800 dark:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button
                        v-if="currentImageIndex < monitoringData.gallery.length - 1"
                        @click="nextImage"
                        class="absolute top-1/2 right-4 -translate-y-1/2 rounded-full bg-white/80 p-3 text-gray-800 shadow-lg transition-all hover:bg-white/100 dark:bg-gray-800 dark:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Bottom Section -->
                <div class="bg-white p-4 dark:bg-gray-800">
                    <!-- Image Counter -->
                    <div class="mb-3 text-center text-sm text-gray-600 dark:text-gray-400">
                        {{ currentImageIndex + 1 }} dari {{ monitoringData.gallery.length }}
                    </div>

                    <!-- Thumbnail Navigation -->
                    <div v-if="monitoringData.gallery.length > 1" class="flex justify-center space-x-2 overflow-x-auto pb-2">
                        <button
                            v-for="(image, index) in monitoringData.gallery"
                            :key="index"
                            @click="goToImage(index)"
                            class="h-14 w-14 flex-shrink-0 overflow-hidden rounded border-2 transition-all"
                            :class="[
                                index === currentImageIndex
                                    ? 'border-blue-500 ring-2 ring-blue-300'
                                    : 'border-gray-300 hover:border-blue-300 dark:border-gray-600 dark:hover:border-blue-400',
                            ]"
                        >
                            <img :src="image.url" :alt="`Thumbnail ${index + 1}`" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
