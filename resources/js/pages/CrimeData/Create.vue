<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { LMap, LTileLayer, LMarker } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default markers in Leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
});

interface LocationData {
    id: number;
    kode: string;
    nama: string;
    provinsi_id?: number;
    kabupaten_kota_id?: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
    { title: 'Tambah Data', href: '/crime-data/create' },
];

const form = useForm({
    provinsi_id: '',
    kabupaten_kota_id: '',
    kecamatan_id: '',
    jenis_kriminal: '',
    deskripsi: '',
    latitude: -6.2088,
    longitude: 106.8456,
});

const zoom = ref(13);
const map = ref(null);
const mapCenter = ref<[number, number]>([-6.2088, 106.8456]); // Default Jakarta
const provinsiList = ref<LocationData[]>([]);
const kabupatenKotaList = ref<LocationData[]>([]);
const kecamatanList = ref<LocationData[]>([]);
const selectedProvinsi = ref<LocationData | null>(null);
const selectedKabupatenKota = ref<LocationData | null>(null);

// Daftar jenis kriminal yang tersedia
const jenisKriminalOptions = [
    { value: 'pencurian', label: 'Pencurian' },
    { value: 'perampokan', label: 'Perampokan' },
    { value: 'pembunuhan', label: 'Pembunuhan' },
    { value: 'pemerkosaan', label: 'Pemerkosaan' },
    { value: 'penipuan', label: 'Penipuan' },
    { value: 'narkoba', label: 'Narkoba' },
    { value: 'penganiayaan', label: 'Penganiayaan' },
    { value: 'kekerasan_dalam_rumah_tangga', label: 'Kekerasan Dalam Rumah Tangga' },
    { value: 'cyber_crime', label: 'Cyber Crime' },
    { value: 'korupsi', label: 'Korupsi' },
    { value: 'vandalisme', label: 'Vandalisme' },
    { value: 'perjudian', label: 'Perjudian' },
    { value: 'trafficking', label: 'Human Trafficking' },
    { value: 'pencucian_uang', label: 'Pencucian Uang' },
    { value: 'terorisme', label: 'Terorisme' },
    { value: 'lainnya', label: 'Lainnya' },
];

// Koordinat center untuk setiap provinsi (berdasarkan ibukota provinsi)
const provinsiCoordinates: Record<string, { lat: number; lng: number; zoom: number }> = {
  '11': { lat: 5.5483, lng: 95.3238, zoom: 9 }, // Banda Aceh
  '12': { lat: 3.5952, lng: 98.6722, zoom: 9 }, // Medan
  '13': { lat: -0.9471, lng: 100.4172, zoom: 9 }, // Padang
  '14': { lat: 0.5071, lng: 101.4478, zoom: 9 }, // Pekanbaru
  '15': { lat: -1.6100, lng: 103.6131, zoom: 9 }, // Jambi
  '16': { lat: -2.9761, lng: 104.7754, zoom: 9 }, // Palembang
  '17': { lat: -3.8004, lng: 102.2655, zoom: 9 }, // Bengkulu
  '18': { lat: -5.4297, lng: 105.2628, zoom: 9 }, // Bandar Lampung
  '19': { lat: -2.1194, lng: 106.1167, zoom: 9 }, // Pangkal Pinang
  '21': { lat: 1.1030, lng: 104.0444, zoom: 9 }, // Tanjungpinang
  '31': { lat: -6.2088, lng: 106.8456, zoom: 11 }, // Jakarta
  '32': { lat: -6.9175, lng: 107.6191, zoom: 9 }, // Bandung
  '33': { lat: -7.0051, lng: 110.4381, zoom: 9 }, // Semarang
  '34': { lat: -7.8014, lng: 110.3649, zoom: 10 }, // Yogyakarta
  '35': { lat: -7.2575, lng: 112.7521, zoom: 9 }, // Surabaya
  '36': { lat: -6.1200, lng: 106.1500, zoom: 9 }, // Serang
  '51': { lat: -8.6705, lng: 115.2126, zoom: 10 }, // Denpasar
  '52': { lat: -8.5769, lng: 116.1004, zoom: 9 }, // Mataram
  '53': { lat: -10.1772, lng: 123.6070, zoom: 8 }, // Kupang
  '61': { lat: -0.0263, lng: 109.3425, zoom: 8 }, // Pontianak
  '62': { lat: -2.2090, lng: 113.9213, zoom: 8 }, // Palangka Raya
  '63': { lat: -3.3194, lng: 114.5908, zoom: 8 }, // Banjarmasin
  '64': { lat: 0.5022, lng: 117.1537, zoom: 8 }, // Samarinda
  '65': { lat: 2.7251, lng: 116.9110, zoom: 8 }, // Tanjung Selor
  '71': { lat: 1.4748, lng: 124.8421, zoom: 8 }, // Manado
  '72': { lat: -0.8990, lng: 119.8770, zoom: 8 }, // Palu
  '73': { lat: -5.1477, lng: 119.4327, zoom: 8 }, // Makassar
  '74': { lat: -3.9723, lng: 122.5120, zoom: 8 }, // Kendari
  '75': { lat: 0.5435, lng: 123.0595, zoom: 8 }, // Gorontalo
  '76': { lat: -2.6788, lng: 118.8660, zoom: 8 }, // Mamuju
  '81': { lat: -3.6942, lng: 128.1814, zoom: 8 }, // Ambon
  '82': { lat: 0.6889, lng: 127.3902, zoom: 8 }, // Sofifi
  '91': { lat: -0.8629, lng: 134.0775, zoom: 7 }, // Manokwari
  '92': { lat: -1.3361, lng: 132.3000, zoom: 7 }, // Sorong
  '94': { lat: -2.5337, lng: 140.7181, zoom: 7 }, // Jayapura
  '95': { lat: -5.8500, lng: 140.5167, zoom: 7 }, // Merauke
  '96': { lat: -4.0500, lng: 136.1000, zoom: 7 }, // Nabire
  '97': { lat: -4.0775, lng: 138.9597, zoom: 7 }, // Wamena
};

async function fetchProvinsi() {
    try {
        const response = await fetch('/api/provinsi');
        if (!response.ok) throw new Error('Gagal mengambil data provinsi');
        provinsiList.value = await response.json();
    } catch (error) {
        console.error(error);
    }
}

async function fetchKabupatenKota(provinsiId: number) {
    kabupatenKotaList.value = [];
    kecamatanList.value = [];
    form.kabupaten_kota_id = '';
    form.kecamatan_id = '';
    
    if (provinsiId) {
        try {
            const response = await fetch(`/api/kabupaten-kota/${provinsiId}`);
            if (!response.ok) throw new Error('Gagal mengambil data kabupaten/kota');
            kabupatenKotaList.value = await response.json();
        } catch (error) {
            console.error(error);
        }
    }
}

async function fetchKecamatan(kabupatenKotaId: number) {
    kecamatanList.value = [];
    form.kecamatan_id = '';
    
    if (kabupatenKotaId) {
        try {
            const response = await fetch(`/api/kecamatan/${kabupatenKotaId}`);
            if (!response.ok) throw new Error('Gagal mengambil data kecamatan');
            kecamatanList.value = await response.json();
        } catch (error) {
            console.error(error);
        }
    }
}

// Fungsi untuk mengupdate center peta berdasarkan provinsi yang dipilih
function updateMapCenter(provinsiKode: string) {
    if (provinsiKode && provinsiCoordinates[provinsiKode]) {
        const coords = provinsiCoordinates[provinsiKode];
        mapCenter.value = [coords.lat, coords.lng];
        zoom.value = coords.zoom;
        
        // Update form coordinates jika belum dipilih manual
        if (form.latitude === -6.2088 && form.longitude === 106.8456) {
            form.latitude = coords.lat;
            form.longitude = coords.lng;
        }
        
        // Force map to update
        setTimeout(() => {
            if (map.value) {
                (map.value as any).leafletObject?.setView([coords.lat, coords.lng], coords.zoom);
            }
        }, 100);
    }
}

watch(() => form.provinsi_id, (newProvinsiId) => {
    if (newProvinsiId) {
        selectedProvinsi.value = provinsiList.value.find(p => p.id === parseInt(newProvinsiId)) || null;
        const provinsiKode = selectedProvinsi.value?.kode;
        fetchKabupatenKota(parseInt(newProvinsiId));
        if (provinsiKode) {
            updateMapCenter(provinsiKode); // Update center peta
        }
    } else {
        kabupatenKotaList.value = [];
        kecamatanList.value = [];
        // Reset ke center default
        mapCenter.value = [-6.2088, 106.8456];
        zoom.value = 13;
    }
});

watch(() => form.kabupaten_kota_id, (newKabupatenKotaId) => {
    if (newKabupatenKotaId) {
        selectedKabupatenKota.value = kabupatenKotaList.value.find(k => k.id === parseInt(newKabupatenKotaId)) || null;
        fetchKecamatan(parseInt(newKabupatenKotaId));
        // Tidak menggeser peta, hanya fetch data kecamatan
    } else {
        kecamatanList.value = [];
    }
});

watch(() => form.kecamatan_id, (newKecamatanId) => {
    // Hanya untuk tracking, tidak menggeser peta
    if (newKecamatanId) {
        // Data kecamatan sudah dipilih, siap untuk submit
    }
});

onMounted(async () => {
    await fetchProvinsi();
    // Force map to refresh after mounting
    setTimeout(() => {
        if (map.value) {
            (map.value as any).leafletObject?.invalidateSize();
        }
    }, 100);
});

function onMapClick(event: any) {
    form.latitude = event.latlng.lat;
    form.longitude = event.latlng.lng;
}

function submit() {
    form.post('/crime-data');
}

function goBack() {
    window.history.back();
}
</script>

<template>
    <Head title="Tambah Data Kriminal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tambah Data Kriminal</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Lengkapi formulir berikut untuk menambah data kriminal baru ke dalam sistem.
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Data Lokasi Administratif -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Data Lokasi Administratif
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Provinsi -->
                        <div class="space-y-2">
                            <label for="provinsi" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Provinsi <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="provinsi" 
                                v-model="form.provinsi_id" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200"
                                required
                            >
                                <option value="">Pilih Provinsi</option>
                                <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                                    {{ provinsi.nama }}
                                </option>
                            </select>
                        </div>

                        <!-- Kabupaten/Kota -->
                        <div class="space-y-2">
                            <label for="kabupaten_kota" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Kabupaten/Kota <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="kabupaten_kota" 
                                v-model="form.kabupaten_kota_id" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-600"
                                :disabled="!form.provinsi_id || kabupatenKotaList.length === 0"
                                required
                            >
                                <option value="">Pilih Kabupaten/Kota</option>
                                <option v-for="kabupatenKota in kabupatenKotaList" :key="kabupatenKota.id" :value="kabupatenKota.id">
                                    {{ kabupatenKota.nama }}
                                </option>
                            </select>
                            <p v-if="!form.provinsi_id" class="text-xs text-gray-500 dark:text-gray-400">
                                Pilih provinsi terlebih dahulu
                            </p>
                        </div>

                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <label for="kecamatan" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Kecamatan <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="kecamatan" 
                                v-model="form.kecamatan_id" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-600"
                                :disabled="!form.kabupaten_kota_id || kecamatanList.length === 0"
                                required
                            >
                                <option value="">Pilih Kecamatan</option>
                                <option v-for="kecamatan in kecamatanList" :key="kecamatan.id" :value="kecamatan.id">
                                    {{ kecamatan.nama }}
                                </option>
                            </select>
                            <p v-if="!form.kabupaten_kota_id" class="text-xs text-gray-500 dark:text-gray-400">
                                Pilih kabupaten/kota terlebih dahulu
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Data Kriminal -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Data Kriminal
                    </h2>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Jenis Kriminal -->
                        <div class="space-y-2">
                            <label for="jenis_kriminal" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Jenis Kriminal <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="jenis_kriminal" 
                                v-model="form.jenis_kriminal" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200"
                                required
                            >
                                <option value="">Pilih Jenis Kriminal</option>
                                <option v-for="jenis in jenisKriminalOptions" :key="jenis.value" :value="jenis.value">
                                    {{ jenis.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2">
                            <label for="deskripsi" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Deskripsi
                            </label>
                            <textarea 
                                id="deskripsi" 
                                v-model="form.deskripsi" 
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 resize-none"
                                placeholder="Masukkan deskripsi detail kejadian..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Lokasi dan Peta -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"/>
                        </svg>
                        Lokasi Kejadian
                    </h2>
                    
                    <!-- Map Container -->
                    <div class="space-y-4">
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-700" style="height: 450px;">
                            <l-map 
                                ref="map" 
                                v-model:zoom="zoom" 
                                :center="mapCenter" 
                                @click="onMapClick"
                                style="height: 100%; width: 100%;"
                                :use-global-leaflet="false"
                            >
                                <l-tile-layer
                                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                    layer-type="base"
                                    name="OpenStreetMap"
                                    attribution="&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors"
                                ></l-tile-layer>
                                <l-marker :lat-lng="[form.latitude, form.longitude]"></l-marker>
                            </l-map>
                        </div>
                        
                        <!-- Instructions -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="text-sm">
                                    <p class="font-medium text-blue-800 dark:text-blue-200">Cara menentukan lokasi:</p>
                                    <ul class="text-blue-700 dark:text-blue-300 mt-1 space-y-1">
                                        <li>• Pilih <strong>provinsi</strong> untuk mempersempit area (peta akan otomatis bergeser)</li>
                                        <li>• Pilih kabupaten/kota dan kecamatan untuk data administratif</li>
                                        <li>• Klik pada peta untuk memilih lokasi kejadian yang tepat</li>
                                        <li>• Marker akan bergerak ke posisi yang Anda klik</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Koordinat -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label for="latitude" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Latitude
                                </label>
                                <input 
                                    type="text" 
                                    id="latitude" 
                                    v-model="form.latitude" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                                    readonly 
                                />
                            </div>
                            <div class="space-y-2">
                                <label for="longitude" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Longitude
                                </label>
                                <input 
                                    type="text" 
                                    id="longitude" 
                                    v-model="form.longitude" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed"
                                    readonly 
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        type="button" 
                        class="px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                        @click="goBack"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-8 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 flex items-center"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure Leaflet map displays correctly */
.leaflet-container {
    height: 100% !important;
    width: 100% !important;
    background: #f8f9fa;
    font-family: inherit;
}

/* Fix for Leaflet marker icons */
.leaflet-marker-icon {
    margin-left: -12px !important;
    margin-top: -41px !important;
}

/* Ensure map controls are visible */
.leaflet-control-zoom,
.leaflet-control-attribution {
    z-index: 1000;
}

/* Custom focus states for better accessibility */
select:focus, input:focus, textarea:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgb(59 130 246);
    border-color: rgb(59 130 246);
}

/* Smooth transitions for form elements */
select, input, textarea {
    transition: all 0.2s ease-in-out;
}

/* Disabled state styling */
select:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Loading animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Card hover effects */
.bg-white {
    transition: box-shadow 0.2s ease-in-out;
}

.bg-white:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Button hover effects */
button {
    transition: all 0.2s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .grid-cols-1.md\\:grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .grid-cols-1.md\\:grid-cols-2 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .px-6 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
}
</style>