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
                                <option v-for="komoditas in komoditas" :key="komoditas.value" :value="komoditas.value">
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
                            <button
                                @click="clearCache"
                                :disabled="dataCache.size === 0"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                🗑️ Clear Cache ({{ dataCache.size }})
                            </button>
                        </div>
                        <div class="flex flex-col items-end text-sm text-gray-500 dark:text-gray-400">
                            <div v-if="lastUpdated">
                                Terakhir diperbarui: {{ formatDateTime(lastUpdated) }}
                            </div>
                            <div v-if="isCacheData" class="text-xs text-green-600 dark:text-green-400">
                                📦 Data dari cache
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
                        <!-- Map will be inserted here -->
                        <div id="indonesia-map" class="h-[500px] w-full overflow-hidden rounded-md"></div>
                        
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

            <!-- Empty State -->
            <div v-else-if="!loading && !error" class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow dark:border-gray-700 dark:bg-gray-800">
                <Package class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-gray-100">Belum Ada Data</h3>
                <p class="text-gray-500 dark:text-gray-400">Pilih komoditas untuk melihat data harga pangan.</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { AlertCircle, Package, RefreshCw, Search } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';

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
const mapLoading = ref(true);
const isCacheData = ref(false);

// Cache untuk menyimpan data berdasarkan tanggal dan komoditas
const dataCache = ref<Map<string, {
    data: any;
    timestamp: number;
    expiry: number;
}>>(new Map());

// Computed values
const selectedKomoditasName = computed(() => {
    const komoditas = props.komoditas.find(k => k.value === selectedKomoditas.value);
    return komoditas?.label || 'Komoditas';
});

// Cache utilities
const getCacheKey = (): string => {
    const periodDate = `${formatDateForAPI(startDate.value)} - ${formatDateForAPI(endDate.value)}`;
    return `${selectedLevelHarga.value}-${selectedKomoditas.value}-${periodDate}`;
};

const getCachedData = (key: string): any | null => {
    const cached = dataCache.value.get(key);
    if (cached && Date.now() < cached.expiry) {
        console.log('Using cached data for:', key);
        return cached.data;
    }
    if (cached) {
        console.log('Cache expired for:', key);
        dataCache.value.delete(key);
    }
    return null;
};

const setCachedData = (key: string, data: any, hoursToCache: number = 2): void => {
    const now = Date.now();
    dataCache.value.set(key, {
        data,
        timestamp: now,
        expiry: now + (hoursToCache * 60 * 60 * 1000) // Convert hours to milliseconds
    });
    console.log(`Data cached for ${hoursToCache} hours:`, key);
};

const clearCache = (): void => {
    const size = dataCache.value.size;
    dataCache.value.clear();
    isCacheData.value = false;
    console.log(`Cleared ${size} cache entries`);
    
    // Show notification
    alert(`Cache cleared! ${size} entries removed.`);
};

// Methods
const fetchPriceData = async () => {
    if (!selectedKomoditas.value) return;

    const cacheKey = getCacheKey();
    
    // Try to get from cache first
    const cachedData = getCachedData(cacheKey);
    if (cachedData) {
        priceData.value = transformPriceData(cachedData);
        lastUpdated.value = new Date(dataCache.value.get(cacheKey)?.timestamp || Date.now());
        isCacheData.value = true;
        updateMapVisualization(priceData.value);
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        const periodDate = `${formatDateForAPI(startDate.value)} - ${formatDateForAPI(endDate.value)}`;
        
        console.log('Fetching fresh data from API for:', cacheKey);
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
            // Cache the raw response
            setCachedData(cacheKey, response.data, 2); // Cache for 2 hours
            
            // Transform the API response to match our interface
            priceData.value = transformPriceData(response.data);
            lastUpdated.value = new Date();
            isCacheData.value = false;
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
            map_color: item.map_color || 'gray'
        }));
    }

    return [];
};

const updateMapVisualization = (data: PriceDataItem[]) => {
    // Load and update the SVG map with price data
    loadIndonesiaMap(data);
};

const loadIndonesiaMap = async (data: PriceDataItem[]) => {
    console.log('loadIndonesiaMap called with', data.length, 'items');
    
    // Wait for DOM to be ready
    await new Promise(resolve => {
        if (document.readyState === 'complete') {
            resolve(true);
        } else {
            window.addEventListener('load', () => resolve(true), { once: true });
        }
    });
    
    const mapContainer = document.getElementById('indonesia-map');
    if (!mapContainer) {
        console.error('Map container not found even after DOM ready');
        console.log('Available elements:', document.querySelectorAll('[id*="map"]'));
        mapLoading.value = false;
        return;
    }
    
    console.log('Map container found:', mapContainer);

    console.log('Loading map with data:', data.length, 'provinces');
    mapLoading.value = true;

    try {
        // Show loading state
        mapContainer.innerHTML = '<div class="flex items-center justify-center h-full"><div class="text-gray-500">Memuat peta...</div></div>';
        
        // Load the SVG map
        console.log('Fetching SVG from /assets/indonesia-map.svg');
        const response = await fetch('/assets/indonesia-map.svg');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const svgContent = await response.text();
        console.log('SVG content length:', svgContent.length);
        
        if (svgContent.length < 100) {
            throw new Error('SVG content seems too short, might be an error page');
        }
        
        mapContainer.innerHTML = svgContent;
        
        const svgElement = mapContainer.querySelector('svg');
        if (svgElement) {
            console.log('SVG element found, applying styles');
            svgElement.style.width = '100%';
            svgElement.style.height = '100%';
            svgElement.setAttribute('preserveAspectRatio', 'xMidYMid meet');
            
            // Get original viewBox or set a default
            const originalViewBox = svgElement.getAttribute('viewBox');
            console.log('Original ViewBox:', originalViewBox);
            
            // Apply price-based colors to provinces
            applyPriceColors(svgElement, data);
            
            mapLoading.value = false;
        } else {
            console.error('SVG element not found in loaded content');
            mapContainer.innerHTML = '<p class="text-center text-gray-500 p-4">SVG element tidak ditemukan dalam file map</p>';
            mapLoading.value = false;
        }
    } catch (err) {
        console.error('Error loading map:', err);
        mapContainer.innerHTML = `<div class="text-center text-red-500 p-4">
            <p>Error loading map: ${err instanceof Error ? err.message : 'Unknown error'}</p>
            <p class="text-sm mt-2">Trying to load: /assets/indonesia-map.svg</p>
        </div>`;
        mapLoading.value = false;
    }
};

const applyPriceColors = (svg: SVGElement, data: PriceDataItem[]) => {
    const provinces = svg.querySelectorAll('path, polygon, circle, g path');
    console.log('Found SVG elements:', provinces.length);
    
    // Clear existing indicators
    const existingIndicators = svg.querySelectorAll('.status-indicator, .status-icon');
    existingIndicators.forEach(indicator => indicator.remove());
    
    provinces.forEach((province: Element) => {
        const provinceElement = province as SVGElement;
        const provinceName = provinceElement.getAttribute('data-name') || 
                           provinceElement.getAttribute('title') || 
                           provinceElement.getAttribute('id') ||
                           provinceElement.getAttribute('name') || '';
        
        // Set default styling first
        provinceElement.style.fill = '#e5e7eb'; // Light gray default
        provinceElement.style.stroke = '#9ca3af';
        provinceElement.style.strokeWidth = '0.5';
        provinceElement.style.cursor = 'pointer';
        
        // If no data, show default map
        if (data.length === 0) {
            (provinceElement as any).title = `${provinceName || 'Provinsi'}: Belum ada data`;
            return;
        }
        
        // Find matching price data - try multiple matching strategies
        let priceItem = data.find(d => 
            d.province_name.toLowerCase() === provinceName.toLowerCase()
        );
        
        // If exact match not found, try partial match
        if (!priceItem && provinceName) {
            priceItem = data.find(d => 
                d.province_name.toLowerCase().includes(provinceName.toLowerCase()) ||
                provinceName.toLowerCase().includes(d.province_name.toLowerCase())
            );
        }
        
        // If still not found, try cleaning up common variations
        if (!priceItem && provinceName) {
            const cleanProvinceName = provinceName.toLowerCase()
                .replace(/^prov\.?\s*/, '') // Remove "Prov." prefix
                .replace(/\s+/g, ' ') // Normalize spaces
                .trim();
            
            priceItem = data.find(d => {
                const cleanApiName = d.province_name.toLowerCase()
                    .replace(/^prov\.?\s*/, '')
                    .replace(/\s+/g, ' ')
                    .trim();
                return cleanApiName.includes(cleanProvinceName) || cleanProvinceName.includes(cleanApiName);
            });
        }
        
        if (priceItem && priceItem.price > 0) {
            let color = '#e5e7eb'; // Default gray
            
            // Use map_color from API response for accurate color coding
            switch (priceItem.map_color?.toLowerCase()) {
                case 'green':
                    color = '#10b981'; // Green - safe price
                    break;
                case 'yellow':
                    color = '#f59e0b'; // Yellow - warning price
                    break;
                case 'red':
                    color = '#ef4444'; // Red - intervention needed
                    break;
                default:
                    // Fallback to status if map_color not available
                    switch (priceItem.status?.toLowerCase()) {
                        case 'aman':
                            color = '#10b981';
                            break;
                        case 'waspada':
                            color = '#f59e0b';
                            break;
                        case 'intervensi':
                            color = '#ef4444';
                            break;
                        default:
                            color = '#10b981';
                            break;
                    }
                    break;
            }
            
            provinceElement.style.fill = color;
            provinceElement.style.stroke = '#374151';
            provinceElement.style.strokeWidth = '1';
            
            // Add tooltip
            (provinceElement as any).title = `${priceItem.province_name}: ${formatPrice(priceItem.price)} - ${getPriceStatusText(priceItem.status)}`;
            
            // Add status indicator dot
            addStatusIndicator(provinceElement, priceItem, color);
        } else {
            // No data available for this province
            (provinceElement as any).title = `${provinceName || 'Provinsi'}: Data tidak tersedia`;
        }
        
        // Add hover effect
        provinceElement.addEventListener('mouseenter', () => {
            provinceElement.style.opacity = '0.8';
        });
        provinceElement.addEventListener('mouseleave', () => {
            provinceElement.style.opacity = '1';
        });
    });
};

// Add status indicator dot to provinces
const addStatusIndicator = (provinceElement: SVGElement, priceItem: PriceDataItem, color: string) => {
    // Get bounding box of the province
    const bbox = provinceElement.getBBox();
    const centerX = bbox.x + bbox.width / 2;
    const centerY = bbox.y + bbox.height / 2;
    
    // Create status indicator circle
    const indicator = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    indicator.setAttribute('cx', centerX.toString());
    indicator.setAttribute('cy', centerY.toString());
    indicator.setAttribute('r', '3');
    indicator.setAttribute('fill', color);
    indicator.setAttribute('stroke', '#ffffff');
    indicator.setAttribute('stroke-width', '1');
    indicator.setAttribute('class', 'status-indicator');
    indicator.style.filter = 'drop-shadow(0 1px 2px rgba(0,0,0,0.3))';
    
    // Add status icon based on price status
    const statusIcon = getStatusIcon(priceItem.status);
    if (statusIcon) {
        const iconElement = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        iconElement.setAttribute('x', centerX.toString());
        iconElement.setAttribute('y', (centerY + 1).toString());
        iconElement.setAttribute('text-anchor', 'middle');
        iconElement.setAttribute('dominant-baseline', 'central');
        iconElement.setAttribute('font-size', '8');
        iconElement.setAttribute('fill', '#ffffff');
        iconElement.setAttribute('font-weight', 'bold');
        iconElement.setAttribute('class', 'status-icon');
        iconElement.textContent = statusIcon;
        
        // Add both indicator and icon to SVG
        provinceElement.parentNode?.appendChild(indicator);
        provinceElement.parentNode?.appendChild(iconElement);
    } else {
        // Just add the indicator circle
        provinceElement.parentNode?.appendChild(indicator);
    }
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
            return null;
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

// Lifecycle hooks
onMounted(() => {
    console.log('Component mounted, loading map and data');
    // Load map first, even without data
    setTimeout(() => {
        loadIndonesiaMap([]);
    }, 100);
    // Then load initial data
    setTimeout(() => {
        fetchPriceData();
    }, 500);
});

// Watch for changes
watch([selectedKomoditas, selectedLevelHarga, startDate, endDate], () => {
    if (selectedKomoditas.value) {
        // Debounce the API call
        setTimeout(fetchPriceData, 300);
    }
});
</script>