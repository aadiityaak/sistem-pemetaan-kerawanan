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
    id: string;
    nama: string;
    kode_provinsi?: string;
    kode_kabupaten_kota?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
    { title: 'Tambah Data', href: '/crime-data/create' },
];

const form = useForm({
    kode_provinsi: '',
    kode_kabupaten_kota: '',
    kode_kecamatan: '',
    jenis_kriminal: '',
    deskripsi: '',
    latitude: -6.2088,
    longitude: 106.8456,
});

const zoom = ref(13);
const map = ref(null);
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

async function fetchProvinsi() {
    try {
        const response = await fetch('/api/provinsi');
        if (!response.ok) throw new Error('Gagal mengambil data provinsi');
        provinsiList.value = await response.json();
    } catch (error) {
        console.error(error);
    }
}

async function fetchKabupatenKota(provinsiKode: string) {
    kabupatenKotaList.value = [];
    kecamatanList.value = [];
    form.kode_kabupaten_kota = '';
    form.kode_kecamatan = '';
    
    if (provinsiKode) {
        try {
            const response = await fetch(`/api/kabupaten-kota/${provinsiKode}`);
            if (!response.ok) throw new Error('Gagal mengambil data kabupaten/kota');
            kabupatenKotaList.value = await response.json();
        } catch (error) {
            console.error(error);
        }
    }
}

async function fetchKecamatan(provinsiKode: string, kabupatenKotaKode: string) {
    kecamatanList.value = [];
    form.kode_kecamatan = '';
    
    if (provinsiKode && kabupatenKotaKode) {
        try {
            const response = await fetch(`/api/kecamatan/${provinsiKode}/${kabupatenKotaKode}`);
            if (!response.ok) throw new Error('Gagal mengambil data kecamatan');
            kecamatanList.value = await response.json();
        } catch (error) {
            console.error(error);
        }
    }
}

watch(() => form.kode_provinsi, (newProvinsiKode) => {
    if (newProvinsiKode) {
        selectedProvinsi.value = provinsiList.value.find(p => p.id === newProvinsiKode) || null;
        fetchKabupatenKota(newProvinsiKode);
    } else {
        kabupatenKotaList.value = [];
        kecamatanList.value = [];
    }
});

watch(() => form.kode_kabupaten_kota, (newKabupatenKotaKode) => {
    if (newKabupatenKotaKode && form.kode_provinsi) {
        selectedKabupatenKota.value = kabupatenKotaList.value.find(k => k.id === newKabupatenKotaKode) || null;
        fetchKecamatan(form.kode_provinsi, newKabupatenKotaKode);
    } else {
        kecamatanList.value = [];
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
                                v-model="form.kode_provinsi" 
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
                                v-model="form.kode_kabupaten_kota" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-600"
                                :disabled="!form.kode_provinsi || kabupatenKotaList.length === 0"
                                required
                            >
                                <option value="">Pilih Kabupaten/Kota</option>
                                <option v-for="kabupatenKota in kabupatenKotaList" :key="kabupatenKota.id" :value="kabupatenKota.id">
                                    {{ kabupatenKota.nama }}
                                </option>
                            </select>
                            <p v-if="!form.kode_provinsi" class="text-xs text-gray-500 dark:text-gray-400">
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
                                v-model="form.kode_kecamatan" 
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors duration-200 disabled:bg-gray-100 disabled:text-gray-400 dark:disabled:bg-gray-600"
                                :disabled="!form.kode_kabupaten_kota || kecamatanList.length === 0"
                                required
                            >
                                <option value="">Pilih Kecamatan</option>
                                <option v-for="kecamatan in kecamatanList" :key="kecamatan.id" :value="kecamatan.id">
                                    {{ kecamatan.nama }}
                                </option>
                            </select>
                            <p v-if="!form.kode_kabupaten_kota" class="text-xs text-gray-500 dark:text-gray-400">
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
                                :center="[form.latitude, form.longitude]" 
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
                                    <p class="text-blue-700 dark:text-blue-300 mt-1">
                                        Klik pada peta untuk memilih lokasi kejadian. Marker akan bergerak ke posisi yang Anda klik.
                                    </p>
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