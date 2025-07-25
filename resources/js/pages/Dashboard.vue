<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

interface CrimeData {
    id: number;
    provinsi_id: number;
    kabupaten_kota_id: number;
    kecamatan_id: number;
    latitude: number;
    longitude: number;
    jenis_kriminal: string;
    deskripsi: string;
    created_at: string;
    updated_at: string;
    provinsi: { id: number; nama: string; };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number; };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number; };
}

interface Statistics {
    totalCrimes: number;
    totalProvinsi: number;
    totalKabupatenKota: number;
    totalKecamatan: number;
    crimesByType: Record<string, number>;
    crimesByProvinsi: Record<string, number>;
}

const props = defineProps<{
    crimeData: CrimeData[];
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
const selectedCrime = ref<CrimeData | null>(null);

// Icon mapping untuk setiap jenis kriminal
const crimeIcons: Record<string, { color: string; icon: string }> = {
    'pencurian': { color: '#dc2626', icon: '💰' }, // red
    'perampokan': { color: '#b91c1c', icon: '🔪' }, // dark red
    'pembunuhan': { color: '#7f1d1d', icon: '☠️' }, // very dark red
    'pemerkosaan': { color: '#be123c', icon: '⚠️' }, // rose
    'penipuan': { color: '#ea580c', icon: '🎭' }, // orange
    'narkoba': { color: '#7c2d12', icon: '💊' }, // brown
    'penganiayaan': { color: '#dc2626', icon: '👊' }, // red
    'kekerasan_dalam_rumah_tangga': { color: '#be185d', icon: '🏠' }, // pink
    'cyber_crime': { color: '#1d4ed8', icon: '💻' }, // blue
    'korupsi': { color: '#059669', icon: '💵' }, // green
    'vandalisme': { color: '#7c3aed', icon: '🎨' }, // purple
    'perjudian': { color: '#db2777', icon: '🎲' }, // pink
    'trafficking': { color: '#0f172a', icon: '⛓️' }, // slate
    'pencucian_uang': { color: '#166534', icon: '🏦' }, // green
    'terorisme': { color: '#450a0a', icon: '💥' }, // very dark red
    'lainnya': { color: '#6b7280', icon: '❓' }, // gray
};

// Computed values untuk statistik
const topCrimeTypes = computed(() => {
    return Object.entries(props.statistics.crimesByType)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 5);
});

const topProvinces = computed(() => {
    return Object.entries(props.statistics.crimesByProvinsi)
        .sort(([,a], [,b]) => b - a)
        .slice(0, 5);
});

function closeModal() {
    selectedCrime.value = null;
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getCrimeTypeLabel(type: string): string {
    const labels: Record<string, string> = {
        'pencurian': 'Pencurian',
        'perampokan': 'Perampokan',
        'pembunuhan': 'Pembunuhan',
        'pemerkosaan': 'Pemerkosaan',
        'penipuan': 'Penipuan',
        'narkoba': 'Narkoba',
        'penganiayaan': 'Penganiayaan',
        'kekerasan_dalam_rumah_tangga': 'KDRT',
        'cyber_crime': 'Cyber Crime',
        'korupsi': 'Korupsi',
        'vandalisme': 'Vandalisme',
        'perjudian': 'Perjudian',
        'trafficking': 'Human Trafficking',
        'pencucian_uang': 'Pencucian Uang',
        'terorisme': 'Terorisme',
        'lainnya': 'Lainnya',
    };
    return labels[type] || type;
}

onMounted(() => {
    // Setup map dengan delay untuk memastikan DOM ready
    setTimeout(() => {
        const L = (window as any).L;
        if (L && mapContainer.value) {
            // Inisialisasi map dengan center Indonesia
            map = L.map(mapContainer.value).setView([-2.5489, 118.0149], 5);
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add markers untuk setiap data kriminal
            props.crimeData.forEach((crime: CrimeData) => {
                const crimeConfig = crimeIcons[crime.jenis_kriminal] || crimeIcons['lainnya'];
                
                // Create custom icon
                const customIcon = L.divIcon({
                    html: `<div style="
                        background-color: ${crimeConfig.color};
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 14px;
                        border: 2px solid white;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                        cursor: pointer;
                    ">${crimeConfig.icon}</div>`,
                    className: 'custom-crime-marker',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });
                
                // Add marker with popup
                const marker = L.marker([parseFloat(String(crime.latitude)), parseFloat(String(crime.longitude))], {
                    icon: customIcon
                }).addTo(map);
                
                // Add click event
                marker.on('click', () => {
                    selectedCrime.value = crime;
                });
                
                // Add hover popup
                marker.bindTooltip(`
                    <strong>${getCrimeTypeLabel(crime.jenis_kriminal)}</strong><br>
                    ${crime.provinsi?.nama || '-'}<br>
                    ${crime.kabupaten_kota?.nama || '-'}<br>
                    ${crime.kecamatan?.nama || '-'}
                `, {
                    direction: 'top',
                    offset: [0, -20]
                });
            });
        }
    }, 500);
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- Statistics Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kriminal</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalCrimes }}</p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <span class="text-2xl">🚨</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Provinsi</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalProvinsi }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <span class="text-2xl">🏛️</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kab/Kota</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKabupatenKota }}</p>
                        </div>
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <span class="text-2xl">🏙️</span>
                        </div>
                    </div>
                </div>
                
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Kecamatan</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKecamatan }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <span class="text-2xl">🏘️</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content: Map and Statistics -->
            <div class="grid gap-4 lg:grid-cols-4">
                <!-- Map Section (占3/4宽度) -->
                <div class="lg:col-span-3 relative min-h-[600px] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900">
                    <div class="absolute top-4 left-4 z-10 bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Peta Kriminalitas Indonesia</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Klik marker untuk detail</p>
                    </div>
                    <div ref="mapContainer" class="w-full h-full rounded-xl"></div>
                </div>

                <!-- Statistics Section (占1/4宽度) -->
                <div class="space-y-4">
                    <!-- Top Crime Types -->
                    <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Jenis Kriminal Teratas</h3>
                        <div class="space-y-2">
                            <div v-for="[type, count] in topCrimeTypes" :key="type" class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ crimeIcons[type]?.icon || '❓' }}</span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ getCrimeTypeLabel(type) }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Provinces -->
                    <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Provinsi Teratas</h3>
                        <div class="space-y-2">
                            <div v-for="[province, count] in topProvinces" :key="province" class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ province }}</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ count }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-zinc-900 p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Legenda</h3>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <div v-for="(config, type) in crimeIcons" :key="type" class="flex items-center gap-2">
                                <div 
                                    class="w-4 h-4 rounded-full border border-white"
                                    :style="{ backgroundColor: config.color }"
                                ></div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">{{ getCrimeTypeLabel(type) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal untuk Detail Kriminal -->
        <div v-if="selectedCrime" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl max-w-md w-full m-4" @click.stop>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Kriminal</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <span class="text-xl">×</span>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Jenis Kriminal</label>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-lg">{{ crimeIcons[selectedCrime.jenis_kriminal]?.icon || '❓' }}</span>
                                <span class="text-gray-900 dark:text-white">{{ getCrimeTypeLabel(selectedCrime.jenis_kriminal) }}</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Lokasi</label>
                            <p class="text-gray-900 dark:text-white">{{ selectedCrime.provinsi?.nama || '-' }}</p>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">{{ selectedCrime.kabupaten_kota?.nama || '-' }}, {{ selectedCrime.kecamatan?.nama || '-' }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Koordinat</label>
                            <p class="text-gray-900 dark:text-white text-sm">{{ selectedCrime.latitude }}, {{ selectedCrime.longitude }}</p>
                        </div>
                        
                        <div v-if="selectedCrime.deskripsi">
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Deskripsi</label>
                            <p class="text-gray-900 dark:text-white">{{ selectedCrime.deskripsi }}</p>
                        </div>
                        
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal</label>
                            <p class="text-gray-900 dark:text-white text-sm">{{ formatDate(selectedCrime.created_at) }}</p>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 mt-6">
                        <a :href="`/crime-data/${selectedCrime.id}/edit`" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                            Edit
                        </a>
                        <button @click="closeModal" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-400 dark:hover:bg-gray-500 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
