<template>
    <AppLayout title="Ketahanan Pangan">
        <div class="relative h-screen overflow-hidden">
            <!-- Floating Controls Panel -->
            <div class="absolute left-4 top-4 z-20 w-80 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">🎛️ Panel Kontrol</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 gap-3">
                        <!-- Pilih Komoditas -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Pilih Komoditas</label>
                            <select
                                v-model="selectedKomoditas"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="" disabled>Pilih Komoditas</option>
                                <option v-for="komoditas in komoditasList" :key="komoditas.value" :value="komoditas.value">
                                    {{ komoditas.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Level Harga -->
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Level Harga</label>
                            <select
                                v-model="selectedLevelHarga"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="1">Harga Produsen</option>
                                <option value="2">Harga Grosir</option>
                                <option value="3">Harga Eceran</option>
                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                                <input
                                    v-model="startDate"
                                    type="date"
                                    @change="fetchPriceData"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-700 dark:text-gray-300">Tanggal Akhir</label>
                                <input
                                    v-model="endDate"
                                    type="date"
                                    @change="fetchPriceData"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-2">
                            <button
                                @click="fetchPriceData"
                                :disabled="loading || !selectedKomoditas"
                                class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <RefreshCw v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                                <Search v-else class="mr-2 h-4 w-4" />
                                {{ loading ? 'Memuat...' : 'Perbarui Data' }}
                            </button>
                        </div>

                        <!-- Last Updated -->
                        <div v-if="lastUpdated" class="text-xs text-gray-500 dark:text-gray-400">
                            Terakhir diperbarui: {{ formatDateTime(lastUpdated) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Screen Map -->
            <div v-if="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-gray-100 dark:bg-gray-900">
                <div class="flex flex-col items-center">
                    <RefreshCw class="mb-4 h-8 w-8 animate-spin text-blue-600" />
                    <p class="text-gray-600 dark:text-gray-400">Memuat data peta...</p>
                </div>
            </div>
            
            <div v-else-if="error" class="absolute inset-0 z-10 flex items-center justify-center bg-gray-100 dark:bg-gray-900">
                <div class="text-center">
                    <AlertCircle class="mx-auto mb-4 h-8 w-8 text-red-600" />
                    <p class="text-red-600 dark:text-red-400">{{ error }}</p>
                    <button
                        @click="fetchPriceData"
                        class="mt-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400"
                    >
                        Coba lagi
                    </button>
                </div>
            </div>

            <!-- SVG Map Indonesia Full Screen -->
            <div v-else class="absolute inset-0 bg-gradient-to-b from-blue-50 to-green-50 dark:from-gray-800 dark:to-gray-900">
                <!-- Indonesia SVG Map Background -->
                <iframe 
                    src="/assets/maps/indonesia.svg"
                    class="absolute inset-0 h-full w-full border-0"
                    style="pointer-events: none; transform: scale(1.5) translate(-10%, 0%);"
                ></iframe>
                
                <!-- Interactive Province Markers -->
                <svg
                    viewBox="0 0 800 320"
                    class="absolute inset-0 h-full w-full"
                    xmlns="http://www.w3.org/2000/svg"
                    style="transform: scale(1.5) translate(-10%, 0%);"
                >
                    <g id="province-markers">
                        <circle
                            v-for="province in priceData"
                            :key="province.id"
                            :cx="getProvincePosition(province.province_name).x"
                            :cy="getProvincePosition(province.province_name).y"
                            r="8"
                            :fill="getMarkerColor(province.map_color, province.status)"
                            :stroke="'#ffffff'"
                            stroke-width="2"
                            class="cursor-pointer transition-all hover:r-12 hover:stroke-gray-600"
                            @click="showProvinceDetail(province)"
                        >
                            <title>{{ province.province_name }}: {{ formatPrice(province.price) }}</title>
                        </circle>
                    </g>
                </svg>
                
                <!-- Selected Province Detail -->
                <div
                    v-if="selectedProvince"
                    class="absolute right-4 top-4 z-30 w-72 rounded-lg bg-white p-4 shadow-lg dark:bg-gray-800"
                >
                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ selectedProvince.province_name }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Harga: <span class="font-medium text-green-600">{{ formatPrice(selectedProvince.price) }}</span>
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Status: <span :class="getPriceStatusClass(selectedProvince.status)" class="rounded px-2 py-1 text-xs">{{ getPriceStatusText(selectedProvince.status) }}</span>
                    </p>
                    <button
                        @click="selectedProvince = null"
                        class="mt-2 text-xs text-blue-600 hover:text-blue-800"
                    >
                        Tutup
                    </button>
                </div>

                <!-- Legend -->
                <div class="absolute bottom-4 left-4 z-30 rounded-lg bg-white/90 p-4 shadow-lg dark:bg-gray-800/90">
                    <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Legend Status Harga</h4>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center">
                            <div class="mr-2 h-3 w-3 rounded-full bg-green-500"></div>
                            <span class="text-gray-700 dark:text-gray-300">Aman</span>
                        </div>
                        <div class="flex items-center">
                            <div class="mr-2 h-3 w-3 rounded-full bg-yellow-500"></div>
                            <span class="text-gray-700 dark:text-gray-300">Waspada</span>
                        </div>
                        <div class="flex items-center">
                            <div class="mr-2 h-3 w-3 rounded-full bg-red-500"></div>
                            <span class="text-gray-700 dark:text-gray-300">Intervensi</span>
                        </div>
                        <div class="flex items-center">
                            <div class="mr-2 h-3 w-3 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                            <span class="text-gray-700 dark:text-gray-300">N/A</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Data Table (Collapsible) -->
            <div 
                v-if="priceData && priceData.length > 0" 
                class="absolute bottom-4 right-4 z-30 max-h-96 w-96 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                :class="{ 'h-auto': showTable, 'h-12': !showTable }"
            >
                <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">📊 Data Harga ({{ priceData.length }})</h3>
                        <button
                            @click="showTable = !showTable"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg 
                                class="h-4 w-4 transition-transform" 
                                :class="{ 'rotate-180': showTable }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div v-show="showTable" class="max-h-80 overflow-y-auto">
                    <table class="min-w-full text-xs">
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="item in priceData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-3 py-2 font-medium text-gray-900 dark:text-gray-100">
                                    {{ item.province_name }}
                                </td>
                                <td class="px-3 py-2 font-semibold text-green-600 dark:text-green-400">
                                    {{ formatPrice(item.price) }}
                                </td>
                                <td class="px-3 py-2">
                                    <span :class="getPriceStatusClass(item.status)" class="rounded px-2 py-1 text-xs">
                                        {{ getPriceStatusText(item.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { AlertCircle, RefreshCw, Search } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

// Import route helper
declare global {
    function route(name: string, params?: any): string;
}

interface Komoditas {
    value: string;
    label: string;
}

interface PriceDataItem {
    id: string;
    province_name: string;
    price: number;
    unit: string;
    status: string;
    map_color?: string;
    latlong?: string;
}

const props = defineProps<{
    komoditas: Komoditas[];
}>();

// Reactive data
const selectedKomoditas = ref('35'); // Default to Daging Ayam Ras
const selectedLevelHarga = ref('3'); // Default to Harga Eceran
const startDate = ref(new Date().toISOString().split('T')[0]);
const endDate = ref(new Date().toISOString().split('T')[0]);
const loading = ref(false);
const error = ref('');
const priceData = ref<PriceDataItem[]>([]);
const lastUpdated = ref<Date | null>(null);
const showTable = ref(false);

// Map controls
const selectedProvince = ref<PriceDataItem | null>(null);

// Komoditas list
const komoditasList = props.komoditas;

// Methods
const fetchPriceData = async () => {
    if (!selectedKomoditas.value) return;

    loading.value = true;
    error.value = '';

    try {
        const periodDate = `${formatDateForAPI(startDate.value)} - ${formatDateForAPI(endDate.value)}`;
        
        console.log('Fetching price data from API...');
        const response = await axios.get(route('api.ketahanan-pangan.harga-peta'), {
            params: {
                level_harga_id: selectedLevelHarga.value,
                komoditas_id: selectedKomoditas.value,
                period_date: periodDate,
                'multi_status_map[0]': '',
                'multi_province_id[0]': ''
            }
        });

        if (response.data.error) {
            error.value = response.data.error;
            priceData.value = [];
        } else {
            // Transform the API response to match our interface
            priceData.value = transformPriceData(response.data);
            lastUpdated.value = new Date();
        }
    } catch (err: any) {
        console.error('Error fetching price data:', err);
        error.value = 'Gagal mengambil data dari server. Silakan coba lagi.';
        priceData.value = [];
    } finally {
        loading.value = false;
    }
};

const transformPriceData = (rawData: any): PriceDataItem[] => {
    // Handle the actual API response format
    if (rawData.data && Array.isArray(rawData.data)) {
        return rawData.data.map((item: any) => ({
            id: item.province_id?.toString() || Math.random().toString(),
            province_name: item.province_name || 'Unknown',
            price: parseFloat(item.rata_rata_geometrik || 0),
            unit: 'Rp/Kg',
            status: item.status_map || 'normal',
            map_color: item.map_color || 'gray',
            latlong: item.latlong || ''
        }));
    }

    return [];
};

// Get marker color based on status (used for SVG markers)
const getMarkerColor = (mapColor?: string | null, status?: string | null): string => {
    // First try map_color from API
    switch (mapColor?.toString()?.toLowerCase()) {
        case 'green':
            return '#10b981';
        case 'yellow':
            return '#f59e0b';
        case 'red':
            return '#ef4444';
        default:
            break;
    }
    
    // Fallback to status
    switch (status?.toString()?.toLowerCase()) {
        case 'aman':
            return '#10b981';
        case 'waspada':
            return '#f59e0b';
        case 'intervensi':
            return '#ef4444';
        default:
            return '#6b7280';
    }
};

// Get status badge class for popup
const getStatusBadgeClass = (status: string | null | undefined): string => {
    switch (status?.toString()?.toLowerCase()) {
        case 'aman':
            return 'bg-green-100 text-green-800';
        case 'waspada':
            return 'bg-yellow-100 text-yellow-800';
        case 'intervensi':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatDateForAPI = (dateString: string): string => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}/${month}/${year}`;
};

const formatDateTime = (date: Date): string => {
    return date.toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// SVG Map Functions - Coordinates adjusted for scaled map
const getProvincePosition = (provinceName: string): { x: number; y: number } => {
    // Coordinates adjusted for scaled map (scale 1.5)
    const positions: Record<string, { x: number; y: number }> = {
        'ACEH': { x: 60, y: 58 },
        'SUMATERA UTARA': { x: 75, y: 83 },
        'SUMATERA BARAT': { x: 67, y: 108 },
        'RIAU': { x: 92, y: 100 },
        'KEPULAUAN RIAU': { x: 108, y: 117 },
        'JAMBI': { x: 83, y: 133 },
        'SUMATERA SELATAN': { x: 97, y: 150 },
        'BENGKULU': { x: 75, y: 158 },
        'LAMPUNG': { x: 108, y: 175 },
        'BANGKA BELITUNG': { x: 125, y: 142 },
        'DKI JAKARTA': { x: 142, y: 192 },
        'JAWA BARAT': { x: 150, y: 197 },
        'JAWA TENGAH': { x: 192, y: 200 },
        'DI YOGYAKARTA': { x: 200, y: 205 },
        'JAWA TIMUR': { x: 242, y: 200 },
        'BANTEN': { x: 130, y: 197 },
        'BALI': { x: 275, y: 217 },
        'NUSA TENGGARA BARAT': { x: 308, y: 225 },
        'NUSA TENGGARA TIMUR': { x: 350, y: 233 },
        'KALIMANTAN BARAT': { x: 183, y: 108 },
        'KALIMANTAN TENGAH': { x: 217, y: 133 },
        'KALIMANTAN SELATAN': { x: 233, y: 167 },
        'KALIMANTAN TIMUR': { x: 267, y: 125 },
        'KALIMANTAN UTARA': { x: 250, y: 92 },
        'SULAWESI UTARA': { x: 367, y: 75 },
        'SULAWESI TENGAH': { x: 350, y: 117 },
        'SULAWESI SELATAN': { x: 367, y: 167 },
        'SULAWESI TENGGARA': { x: 400, y: 183 },
        'GORONTALO': { x: 358, y: 92 },
        'SULAWESI BARAT': { x: 342, y: 142 },
        'MALUKU': { x: 467, y: 150 },
        'MALUKU UTARA': { x: 450, y: 100 },
        'PAPUA BARAT': { x: 567, y: 167 },
        'PAPUA': { x: 667, y: 183 },
        'PAPUA SELATAN': { x: 617, y: 217 },
        'PAPUA TENGAH': { x: 650, y: 167 },
        'PAPUA PEGUNUNGAN': { x: 683, y: 150 },
        'PAPUA BARAT DAYA': { x: 583, y: 200 }
    };

    return positions[provinceName.toUpperCase()] || { x: 200, y: 100 };
};

const showProvinceDetail = (province: PriceDataItem) => {
    selectedProvince.value = province;
};

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

const getPriceStatusClass = (status: string | null | undefined): string => {
    switch (status?.toString()?.toLowerCase()) {
        case 'aman':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'waspada':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'intervensi':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    }
};

const getPriceStatusText = (status: string | null | undefined): string => {
    switch (status?.toString()?.toLowerCase()) {
        case 'aman':
            return 'Aman';
        case 'waspada':
            return 'Waspada';
        case 'intervensi':
            return 'Intervensi';
        default:
            return status || 'Normal';
    }
};

// Initialize component
onMounted(() => {
    // Load initial data
    if (selectedKomoditas.value) {
        fetchPriceData();
    }
});

// Watch for changes
watch([selectedKomoditas, selectedLevelHarga, startDate, endDate], (newValues, oldValues) => {
    if (selectedKomoditas.value) {
        // Debounce the API call
        setTimeout(fetchPriceData, 300);
    }
});
</script>

<style scoped>
/* SVG Map Transitions */
.transition-all {
    transition: all 0.3s ease;
}

/* Province marker hover effects */
circle:hover {
    r: 12px;
    stroke-width: 3px;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}
</style>