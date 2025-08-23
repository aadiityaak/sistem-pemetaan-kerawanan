<template>
    <AppLayout title="Ketahanan Pangan">
        <div class="flex h-screen flex-col">
            <!-- Controls Panel Card -->
            <div class="border-b border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">🗺️ Peta Ketahanan Pangan Indonesia</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Pilih Komoditas -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Komoditas</label>
                            <select
                                v-model="selectedKomoditas"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="" disabled>Pilih Komoditas</option>
                                <option v-for="komoditas in komoditasList" :key="komoditas.value" :value="komoditas.value">
                                    {{ komoditas.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Level Harga -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Level Harga</label>
                            <select
                                v-model="selectedLevelHarga"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="1">Harga Produsen</option>
                                <option value="2">Harga Grosir</option>
                                <option value="3">Harga Eceran</option>
                            </select>
                        </div>

                        <!-- Tanggal -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                                <input
                                    v-model="startDate"
                                    type="date"
                                    @change="fetchPriceData"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Akhir</label>
                                <input
                                    v-model="endDate"
                                    type="date"
                                    @change="fetchPriceData"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Action Button & Last Updated -->
                        <div class="flex flex-col justify-end">
                            <button
                                @click="fetchPriceData"
                                :disabled="loading || !selectedKomoditas"
                                class="mb-2 inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <RefreshCw v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                                <Search v-else class="mr-2 h-4 w-4" />
                                {{ loading ? 'Memuat...' : 'Perbarui Data' }}
                            </button>
                            <div v-if="lastUpdated" class="text-xs text-gray-500 dark:text-gray-400">
                                Terakhir diperbarui: {{ formatDateTime(lastUpdated) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="relative flex-1 overflow-hidden min-h-96">
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
                <div v-else class="relative h-full bg-gradient-to-b from-blue-50 to-green-50 dark:from-gray-800 dark:to-gray-900">
                    <!-- Indonesia SVG Map with Dynamic Colors -->
                    <svg
                        viewBox="0 0 792.54596 316.66394"
                        class="absolute inset-0 h-full w-full"
                        xmlns="http://www.w3.org/2000/svg"
                        preserveAspectRatio="xMinYMin"
                    >
                        <!-- Base Indonesia Map Paths -->
                        <path
                            v-for="provinceCode in provincePathData"
                            :key="provinceCode.id"
                            :d="provinceCode.path"
                            :fill="getProvinceMapColor(provinceCode.name)"
                            stroke="#ffffff"
                            stroke-width="1"
                            stroke-linejoin="round"
                            class="cursor-pointer transition-all hover:stroke-gray-800"
                            @click="showProvinceByName(provinceCode.name)"
                        >
                            <title>{{ provinceCode.name }}</title>
                        </path>
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
            </div>

            <!-- Data Harga Card - Bawah Peta -->
            <div v-if="priceData && priceData.length > 0" class="border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">📊 Data Harga {{ komoditasList.find(k => k.value === selectedKomoditas)?.label || 'Komoditas' }}</h3>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium">{{ priceData.length }}</span> Provinsi
                            </div>
                            <button
                                @click="showTable = !showTable"
                                class="inline-flex items-center rounded-md bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                            >
                                {{ showTable ? 'Sembunyikan' : 'Tampilkan' }} Detail
                                <svg 
                                    class="ml-1 h-4 w-4 transition-transform" 
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
                </div>
                
                <!-- Statistik Ringkas -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Rata-rata Harga -->
                        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Rata-rata Harga</p>
                                    <p class="text-lg font-bold text-blue-900 dark:text-blue-100">{{ formatPrice(getAveragePrice()) }}</p>
                                </div>
                                <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-800">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Harga Tertinggi -->
                        <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-red-600 dark:text-red-400">Harga Tertinggi</p>
                                    <p class="text-lg font-bold text-red-900 dark:text-red-100">{{ formatPrice(getHighestPrice().price) }}</p>
                                    <p class="text-xs text-red-600 dark:text-red-400">{{ getHighestPrice().province }}</p>
                                </div>
                                <div class="rounded-full bg-red-100 p-2 dark:bg-red-800">
                                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Harga Terendah -->
                        <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Harga Terendah</p>
                                    <p class="text-lg font-bold text-green-900 dark:text-green-100">{{ formatPrice(getLowestPrice().price) }}</p>
                                    <p class="text-xs text-green-600 dark:text-green-400">{{ getLowestPrice().province }}</p>
                                </div>
                                <div class="rounded-full bg-green-100 p-2 dark:bg-green-800">
                                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Status Dominan -->
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Dominan</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ getDominantStatus().status }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ getDominantStatus().count }} Provinsi</p>
                                </div>
                                <div class="rounded-full p-2" :class="getDominantStatus().bgClass">
                                    <div class="h-6 w-6 rounded-full" :class="getDominantStatus().colorClass"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Table -->
                <div v-show="showTable" class="border-t border-gray-200 dark:border-gray-700">
                    <div class="max-h-96 overflow-y-auto">
                        <table class="min-w-full">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Provinsi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Trend</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">vs HPP/HAP</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in priceData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-3 w-3 rounded-full mr-3" :style="{ backgroundColor: getMarkerColor(item.map_color, item.status) }"></div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ item.province_name }}</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ formatPrice(item.price) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="getPriceStatusClass(item.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                            {{ getPriceStatusText(item.status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <svg v-if="getTrendDirection(item) === 'up'" class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                                            </svg>
                                            <svg v-else-if="getTrendDirection(item) === 'down'" class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6" />
                                            </svg>
                                            <svg v-else class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                            </svg>
                                            <span class="ml-1 text-xs" :class="{
                                                'text-red-600': getTrendDirection(item) === 'up',
                                                'text-green-600': getTrendDirection(item) === 'down',
                                                'text-gray-500': getTrendDirection(item) === 'stable'
                                            }">
                                                {{ getTrendText(item) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center">
                                            <span class="text-xs font-medium" :class="{
                                                'text-red-600': getHppHapPercentage(item) > 0,
                                                'text-green-600': getHppHapPercentage(item) < 0,
                                                'text-gray-500': getHppHapPercentage(item) === 0
                                            }">
                                                {{ getHppHapPercentage(item) > 0 ? '+' : '' }}{{ getHppHapPercentage(item) }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
import { indonesiaProvinces, type ProvincePathData } from '@/data/indonesiaProvinces';

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
    hpp_hap?: string;
    hpp_hap_percentage?: number;
    hpp_hap_percentage_gap_change?: 'up' | 'down' | 'stable';
    hpp_hap_color_gap?: string;
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
            latlong: item.latlong || '',
            hpp_hap: item.hpp_hap || '',
            hpp_hap_percentage: parseFloat(item.hpp_hap_percentage || 0),
            hpp_hap_percentage_gap_change: item.hpp_hap_percentage_gap_change || 'stable',
            hpp_hap_color_gap: item.hpp_hap_color_gap || 'gray'
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

// Gunakan data provinsi dari file terpisah
const provincePathData = ref<ProvincePathData[]>(indonesiaProvinces);

// Get province color based on price data
const getProvinceMapColor = (provinceName: string): string => {
    const province = priceData.value.find(p => 
        p.province_name.toUpperCase() === provinceName.toUpperCase()
    );
    
    if (!province) {
        return '#e5e7eb'; // Default gray untuk provinsi tanpa data
    }
    
    return getMarkerColor(province.map_color, province.status);
};

// Show province detail by name
const showProvinceByName = (provinceName: string) => {
    const province = priceData.value.find(p => 
        p.province_name.toUpperCase() === provinceName.toUpperCase()
    );
    
    if (province) {
        selectedProvince.value = province;
    }
};

const showProvinceDetail = (province: PriceDataItem) => {
    selectedProvince.value = province;
};

// Statistics Functions
const getAveragePrice = (): number => {
    if (!priceData.value.length) return 0;
    const total = priceData.value.reduce((sum, item) => sum + item.price, 0);
    return total / priceData.value.length;
};

const getHighestPrice = (): { price: number; province: string } => {
    if (!priceData.value.length) return { price: 0, province: '' };
    const highest = priceData.value.reduce((max, item) => 
        item.price > max.price ? item : max
    );
    return { price: highest.price, province: highest.province_name };
};

const getLowestPrice = (): { price: number; province: string } => {
    if (!priceData.value.length) return { price: 0, province: '' };
    const lowest = priceData.value.reduce((min, item) => 
        item.price < min.price ? item : min
    );
    return { price: lowest.price, province: lowest.province_name };
};

const getDominantStatus = (): { status: string; count: number; bgClass: string; colorClass: string } => {
    if (!priceData.value.length) return { status: 'N/A', count: 0, bgClass: 'bg-gray-100', colorClass: 'bg-gray-300' };
    
    const statusCount: Record<string, number> = {};
    priceData.value.forEach(item => {
        const status = item.status || 'normal';
        statusCount[status] = (statusCount[status] || 0) + 1;
    });
    
    const dominantStatus = Object.keys(statusCount).reduce((a, b) => 
        statusCount[a] > statusCount[b] ? a : b
    );
    
    const statusMapping: Record<string, { label: string; bgClass: string; colorClass: string }> = {
        'aman': { label: 'Aman', bgClass: 'bg-green-100 dark:bg-green-800', colorClass: 'bg-green-500' },
        'waspada': { label: 'Waspada', bgClass: 'bg-yellow-100 dark:bg-yellow-800', colorClass: 'bg-yellow-500' },
        'intervensi': { label: 'Intervensi', bgClass: 'bg-red-100 dark:bg-red-800', colorClass: 'bg-red-500' },
        'normal': { label: 'Normal', bgClass: 'bg-gray-100 dark:bg-gray-800', colorClass: 'bg-gray-400' }
    };
    
    const mapping = statusMapping[dominantStatus.toLowerCase()] || statusMapping['normal'];
    
    return {
        status: mapping.label,
        count: statusCount[dominantStatus] || 0,
        bgClass: mapping.bgClass,
        colorClass: mapping.colorClass
    };
};

// Trend Analysis Functions (based on API data structure)
const getTrendDirection = (item: PriceDataItem): 'up' | 'down' | 'stable' => {
    // Simulasi trend berdasarkan gap change dari API
    const gapChange = (item as any).hpp_hap_percentage_gap_change;
    if (gapChange === 'up') return 'up';
    if (gapChange === 'down') return 'down';
    
    // Fallback: bandingkan dengan rata-rata
    const avg = getAveragePrice();
    if (item.price > avg * 1.05) return 'up';
    if (item.price < avg * 0.95) return 'down';
    return 'stable';
};

const getTrendText = (item: PriceDataItem): string => {
    const direction = getTrendDirection(item);
    const percentage = Math.abs(((item.price - getAveragePrice()) / getAveragePrice()) * 100);
    
    switch (direction) {
        case 'up':
            return `+${percentage.toFixed(1)}%`;
        case 'down':
            return `-${percentage.toFixed(1)}%`;
        default:
            return '0.0%';
    }
};

const getHppHapPercentage = (item: PriceDataItem): number => {
    // Gunakan data hpp_hap_percentage dari API jika ada
    const hppHapPercentage = (item as any).hpp_hap_percentage;
    if (typeof hppHapPercentage === 'number') {
        return Math.round(hppHapPercentage * 100) / 100;
    }
    
    // Fallback calculation
    return 0;
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