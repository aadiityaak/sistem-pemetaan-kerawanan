<template>
    <AppLayout title="Ketahanan Pangan">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dashboard Ketahanan Pangan</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Pantau harga pangan di seluruh Indonesia dengan data real-time</p>
            </div>

            <!-- Controls Panel -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">🎛️ Panel Kontrol</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Pilih Komoditas -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Komoditas</label>
                            <select
                                v-model="selectedKomoditas"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
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
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="1">Harga Produsen</option>
                                <option value="2">Harga Grosir</option>
                                <option value="3">Harga Eceran</option>
                            </select>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                            <input
                                v-model="startDate"
                                type="date"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 leading-5 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- Tanggal Akhir -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Akhir</label>
                            <input
                                v-model="endDate"
                                type="date"
                                @change="fetchPriceData"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 leading-5 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button
                                @click="fetchPriceData"
                                :disabled="loading || !selectedKomoditas"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <RefreshCw v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                                <Search v-else class="mr-2 h-4 w-4" />
                                {{ loading ? 'Memuat...' : 'Perbarui Data' }}
                            </button>
                        </div>
                        <div class="flex flex-col items-end text-sm text-gray-500 dark:text-gray-400">
                            <div v-if="lastUpdated">
                                Terakhir diperbarui: {{ formatDateTime(lastUpdated) }}
                            </div>
                            <div class="text-xs mt-1" :class="mapInitialized ? 'text-green-600' : 'text-red-600'">
                                Map: {{ mapInitialized ? '✅ Active' : '❌ Inactive' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">🗺️ Peta Harga Pangan Indonesia</h3>
                </div>
                <div class="p-6">
                    <div v-if="loading" class="flex h-96 items-center justify-center">
                        <div class="text-center">
                            <RefreshCw class="mx-auto mb-4 h-8 w-8 animate-spin text-blue-600" />
                            <p class="text-gray-500 dark:text-gray-400">Memuat data harga...</p>
                        </div>
                    </div>
                    <div v-else-if="error" class="flex h-96 items-center justify-center">
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
                    <div v-else class="relative">
                        <!-- Map will be inserted here (same as Dashboard) -->
                        <div 
                            ref="mapContainer" 
                            class="relative z-0 h-[500px] rounded-lg border border-gray-200 dark:border-gray-700"
                        ></div>
                        
                        <!-- Legend -->
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4">
                            <div class="flex items-center">
                                <div class="mr-2 h-4 w-4 rounded-full bg-green-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Aman</span>
                            </div>
                            <div class="flex items-center">
                                <div class="mr-2 h-4 w-4 rounded-full bg-yellow-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Waspada</span>
                            </div>
                            <div class="flex items-center">
                                <div class="mr-2 h-4 w-4 rounded-full bg-red-500"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Intervensi</span>
                            </div>
                            <div class="flex items-center">
                                <div class="mr-2 h-4 w-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                <span class="text-sm text-gray-700 dark:text-gray-300">Data Tidak Tersedia</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Data Table -->
            <div v-if="priceData && priceData.length > 0" class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">📊 Data Harga Provinsi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Provinsi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Harga (Rp)
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Satuan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="item in priceData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                    {{ item.province_name }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold whitespace-nowrap text-green-600 dark:text-green-400">
                                    {{ formatPrice(item.price) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ item.unit }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="getPriceStatusClass(item.status)"
                                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    >
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
import { nextTick, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import 'leaflet/dist/leaflet.css';

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
const mapInitialized = ref(false);

// Map instance (same pattern as Dashboard)
let map: any = null;
let markersLayer: any = null;
const mapContainer = ref<HTMLElement>();

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
            
            // Update the map visualization
            updateMapVisualization(priceData.value);
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

const updateMapVisualization = (data: PriceDataItem[]) => {
    // Update Leaflet map with markers
    if (map) {
        updateMapMarkers(data);
    }
};

// Initialize map (exact same pattern as Dashboard)
const initializeLeafletMap = async () => {
    console.log('🗺️ [MAP INIT] Starting map initialization (Dashboard pattern)');
    
    if (typeof window !== 'undefined') {
        try {
            // Dynamic import Leaflet (same as Dashboard)
            const L = await import('leaflet');
            
            // Initialize map
            if (mapContainer.value) {
                console.log('✅ [MAP INIT] Container ref available:', {
                    width: mapContainer.value.offsetWidth,
                    height: mapContainer.value.offsetHeight
                });
                
                // Clean up existing map
                if (map) {
                    console.log('🧹 [MAP INIT] Cleaning up existing map');
                    map.remove();
                    map = null;
                    markersLayer = null;
                }
                
                // Default center for Indonesia
                const mapCenter: [number, number] = [-2.548926, 118.0148634];
                const zoomLevel = 5;
                
                console.log('🆕 [MAP INIT] Creating Leaflet map instance');
                
                // Create map (exact same as Dashboard)
                map = L.map(mapContainer.value).setView(mapCenter, zoomLevel);
                
                console.log('✅ [MAP INIT] Map instance created successfully');
                
                // Add tile layer (same as Dashboard)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                }).addTo(map);
                
                console.log('🗺️ [MAP INIT] Tiles added to map');
                
                // Create markers layer
                markersLayer = L.layerGroup().addTo(map);
                console.log('📍 [MAP INIT] Markers layer created');
                
                // Success!
                mapInitialized.value = true;
                
                console.log('✅ [MAP INIT] Map initialization completed successfully!');
                
            } else {
                console.error('❌ [MAP INIT] Map container ref not available');
                mapInitialized.value = false;
            }
        } catch (error) {
            console.error('💥 [MAP INIT] Error initializing map:', error);
            mapInitialized.value = false;
        }
    }
};

const updateMapMarkers = async (data: PriceDataItem[]) => {
    if (!map || !markersLayer) {
        console.warn('Map or markers layer not available, skipping marker update');
        return;
    }
    
    console.log('Updating map markers with', data.length, 'data points');
    
    // Clear existing markers
    markersLayer.clearLayers();
    
    // Dynamic import Leaflet
    const L = await import('leaflet');
    
    let validMarkers = 0;
    
    data.forEach((item) => {
        // Parse coordinates from latlong string
        const latLong = item.latlong || '';
        const [lat, lng] = latLong.split(',').map(coord => parseFloat(coord.trim()));
        
        if (isNaN(lat) || isNaN(lng)) {
            console.warn(`Invalid coordinates for ${item.province_name}: ${latLong}`);
            return;
        }
        
        // Get color based on status
        const color = getMarkerColor(item.map_color, item.status);
        const statusIcon = getStatusIcon(item.status);
        
        // Create custom marker icon
        const markerIcon = createCustomMarkerIcon(color, statusIcon, L);
        
        // Create marker
        const marker = L.marker([lat, lng], { icon: markerIcon });
        
        // Create popup content
        const popupContent = `
            <div class="p-2 min-w-[200px]">
                <h3 class="font-semibold text-lg mb-2">${item.province_name}</h3>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Harga:</span>
                        <span class="font-semibold text-green-600">${formatPrice(item.price)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="px-2 py-1 rounded text-xs font-medium ${getStatusBadgeClass(item.status)}">
                            ${getPriceStatusText(item.status)}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Satuan:</span>
                        <span>${item.unit}</span>
                    </div>
                </div>
            </div>
        `;
        
        marker.bindPopup(popupContent);
        markersLayer.addLayer(marker);
        validMarkers++;
    });
    
    console.log(`Added ${validMarkers} valid markers out of ${data.length} data points`);
};

// Get marker color based on status
const getMarkerColor = (mapColor?: string, status?: string): string => {
    // First try map_color from API
    switch (mapColor?.toLowerCase()) {
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
    switch (status?.toLowerCase()) {
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

// Create custom marker icon
const createCustomMarkerIcon = (color: string, statusIcon?: string | null, L?: any) => {
    const iconHtml = `
        <div class="relative">
            <div class="w-6 h-6 rounded-full border-2 border-white shadow-lg flex items-center justify-center text-white text-xs font-bold" 
                 style="background-color: ${color}">
                ${statusIcon || ''}
            </div>
        </div>
    `;
    
    return L.divIcon({
        html: iconHtml,
        className: 'custom-marker-icon',
        iconSize: [24, 24],
        iconAnchor: [12, 12],
        popupAnchor: [0, -12]
    });
};

// Get status icon based on status
const getStatusIcon = (status: string): string | null => {
    switch (status?.toLowerCase()) {
        case 'aman':
            return '✓';
        case 'waspada':
            return '!';
        case 'intervensi':
            return '⚠';
        default:
            return '●';
    }
};

// Get status badge class for popup
const getStatusBadgeClass = (status: string): string => {
    switch (status?.toLowerCase()) {
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

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price);
};

const getPriceStatusClass = (status: string): string => {
    switch (status?.toLowerCase()) {
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

const getPriceStatusText = (status: string): string => {
    switch (status?.toLowerCase()) {
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

// Initialize map (exact same pattern as Dashboard)
onMounted(async () => {
    console.log('🚀 [MOUNT] Component mounted');
    
    // Initialize map immediately (same as Dashboard)
    await initializeLeafletMap();
    
    // Load initial data
    console.log('📊 [MOUNT] Loading initial data');
    fetchPriceData();
});

// Watch for changes
watch([selectedKomoditas, selectedLevelHarga, startDate, endDate], (newValues, oldValues) => {
    if (selectedKomoditas.value && map) {
        console.log('🔍 Parameter change detected - fetching new data');
        // Debounce the API call, only fetch data (don't re-init map)
        setTimeout(fetchPriceData, 300);
    }
});
</script>

<style scoped>
:deep(.custom-marker-icon) {
    background: transparent !important;
    border: none !important;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

:deep(.leaflet-popup-content) {
    margin: 0;
    padding: 0;
}
</style>