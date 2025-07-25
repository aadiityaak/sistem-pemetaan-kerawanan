<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface LocationData {
    id: number;
    nama: string;
    provinsi_id?: number;
    kabupaten_kota_id?: number;
}

interface CrimeData {
    id?: number;
    provinsi_id: string | number;
    kabupaten_kota_id: string | number;
    kecamatan_id: string | number;
    jenis_kriminal: string;
    deskripsi: string;
    latitude: number | string;
    longitude: number | string;
}

const props = defineProps<{
    crime?: CrimeData;
}>();

// Computed property untuk menentukan mode berdasarkan ada tidaknya data crime
const mode = computed(() => props.crime ? 'edit' : 'create');

// Data untuk dropdown
const provinsiList = ref<LocationData[]>([]);
const kabupatenKotaList = ref<LocationData[]>([]);
const kecamatanList = ref<LocationData[]>([]);

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

// Koordinat center untuk setiap provinsi
const provinsiCoordinates: Record<string, { lat: number; lng: number; zoom: number }> = {
  'ACEH': { lat: 5.5483, lng: 95.3238, zoom: 9 }, // Banda Aceh
  'SUMATERA UTARA': { lat: 3.5952, lng: 98.6722, zoom: 9 }, // Medan
  'SUMATERA BARAT': { lat: -0.9471, lng: 100.4172, zoom: 9 }, // Padang
  'RIAU': { lat: 0.5071, lng: 101.4478, zoom: 9 }, // Pekanbaru
  'JAMBI': { lat: -1.6100, lng: 103.6131, zoom: 9 }, // Jambi
  'SUMATERA SELATAN': { lat: -2.9761, lng: 104.7754, zoom: 9 }, // Palembang
  'BENGKULU': { lat: -3.8004, lng: 102.2655, zoom: 9 }, // Bengkulu
  'LAMPUNG': { lat: -5.4297, lng: 105.2628, zoom: 9 }, // Bandar Lampung
  'KEPULAUAN BANGKA BELITUNG': { lat: -2.1194, lng: 106.1167, zoom: 9 }, // Pangkal Pinang
  'KEPULAUAN RIAU': { lat: 1.1030, lng: 104.0444, zoom: 9 }, // Tanjungpinang
  'DKI JAKARTA': { lat: -6.2088, lng: 106.8456, zoom: 11 }, // Jakarta
  'JAWA BARAT': { lat: -6.9175, lng: 107.6191, zoom: 9 }, // Bandung
  'JAWA TENGAH': { lat: -7.0051, lng: 110.4381, zoom: 9 }, // Semarang
  'DI YOGYAKARTA': { lat: -7.8014, lng: 110.3649, zoom: 10 }, // Yogyakarta
  'JAWA TIMUR': { lat: -7.2575, lng: 112.7521, zoom: 9 }, // Surabaya
  'BANTEN': { lat: -6.1200, lng: 106.1500, zoom: 9 }, // Serang
  'BALI': { lat: -8.6705, lng: 115.2126, zoom: 10 }, // Denpasar
  'NUSA TENGGARA BARAT': { lat: -8.5769, lng: 116.1004, zoom: 9 }, // Mataram
  'NUSA TENGGARA TIMUR': { lat: -10.1772, lng: 123.6070, zoom: 8 }, // Kupang
  'KALIMANTAN BARAT': { lat: -0.0263, lng: 109.3425, zoom: 8 }, // Pontianak
  'KALIMANTAN TENGAH': { lat: -2.2090, lng: 113.9213, zoom: 8 }, // Palangka Raya
  'KALIMANTAN SELATAN': { lat: -3.3194, lng: 114.5908, zoom: 8 }, // Banjarmasin
  'KALIMANTAN TIMUR': { lat: 0.5022, lng: 117.1537, zoom: 8 }, // Samarinda
  'KALIMANTAN UTARA': { lat: 2.7251, lng: 116.9110, zoom: 8 }, // Tanjung Selor
  'SULAWESI UTARA': { lat: 1.4748, lng: 124.8421, zoom: 8 }, // Manado
  'SULAWESI TENGAH': { lat: -0.8990, lng: 119.8770, zoom: 8 }, // Palu
  'SULAWESI SELATAN': { lat: -5.1477, lng: 119.4327, zoom: 8 }, // Makassar
  'SULAWESI TENGGARA': { lat: -3.9723, lng: 122.5120, zoom: 8 }, // Kendari
  'GORONTALO': { lat: 0.5435, lng: 123.0595, zoom: 8 }, // Gorontalo
  'SULAWESI BARAT': { lat: -2.6788, lng: 118.8660, zoom: 8 }, // Mamuju
  'MALUKU': { lat: -3.6942, lng: 128.1814, zoom: 8 }, // Ambon
  'MALUKU UTARA': { lat: 0.6889, lng: 127.3902, zoom: 8 }, // Sofifi
  'PAPUA BARAT': { lat: -0.8629, lng: 134.0775, zoom: 7 }, // Manokwari
  'PAPUA BARAT DAYA': { lat: -1.3361, lng: 132.3000, zoom: 7 }, // Sorong
  'PAPUA': { lat: -2.5337, lng: 140.7181, zoom: 7 }, // Jayapura
  'PAPUA SELATAN': { lat: -5.8500, lng: 140.5167, zoom: 7 }, // Merauke
  'PAPUA TENGAH': { lat: -4.0500, lng: 136.1000, zoom: 7 }, // Nabire
  'PAPUA PEGUNUNGAN': { lat: -4.0775, lng: 138.9597, zoom: 7 }, // Wamena
};

const form = useForm({
    provinsi_id: props.crime?.provinsi_id || '',
    kabupaten_kota_id: props.crime?.kabupaten_kota_id || '',
    kecamatan_id: props.crime?.kecamatan_id || '',
    jenis_kriminal: props.crime?.jenis_kriminal || '',
    deskripsi: props.crime?.deskripsi || '',
    latitude: props.crime?.latitude || -6.2088, // Default Jakarta
    longitude: props.crime?.longitude || 106.8456,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
    { title: mode.value === 'create' ? 'Tambah' : 'Edit', href: '#' },
];

function submit() {
    if (mode.value === 'create') {
        form.post('/crime-data');
    } else if (props.crime?.id) {
        form.put(`/crime-data/${props.crime.id}`);
    }
}

// Fungsi untuk fetch data
async function fetchProvinsi() {
    try {
        const response = await fetch('/api/provinsi');
        if (!response.ok) throw new Error('Gagal mengambil data provinsi');
        provinsiList.value = await response.json();
    } catch (error) {
        console.error(error);
    }
}

async function fetchKabupatenKota(provinsiId: number | string) {
    if (mode.value === 'create') {
        // Hanya reset untuk mode create
        kabupatenKotaList.value = [];
        kecamatanList.value = [];
        form.kabupaten_kota_id = '';
        form.kecamatan_id = '';
    } else {
        // Untuk mode edit, hanya reset kecamatan list
        kecamatanList.value = [];
    }
    
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

async function fetchKecamatan(kabupatenKotaId: number | string) {
    if (mode.value === 'create') {
        // Hanya reset untuk mode create
        kecamatanList.value = [];
        form.kecamatan_id = '';
    } else {
        // Untuk mode edit, hanya reset list
        kecamatanList.value = [];
    }
    
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
function updateMapCenter(namaProvinsi: string) {
    if (namaProvinsi && provinsiCoordinates[namaProvinsi]) {
        const coords = provinsiCoordinates[namaProvinsi];
        if (map && marker) {
            map.setView([coords.lat, coords.lng], coords.zoom);
            // Update form coordinates jika masih default Jakarta atau belum dipilih manual
            const currentLat = typeof form.latitude === 'number' ? form.latitude : parseFloat(form.latitude || '0');
            const currentLng = typeof form.longitude === 'number' ? form.longitude : parseFloat(form.longitude || '0');
            
            // Cek apakah koordinat masih default (Jakarta) atau kosong
            if (!form.latitude || !form.longitude || 
                (Math.abs(currentLat - (-6.2088)) < 0.01 && Math.abs(currentLng - 106.8456) < 0.01)) {
                form.latitude = coords.lat;
                form.longitude = coords.lng;
                marker.setLatLng([coords.lat, coords.lng]);
            }
        }
    }
}

// Leaflet map
let map: any;
let marker: any;
const mapContainer = ref();

onMounted(async () => {
    // Load provinsi data terlebih dahulu
    await fetchProvinsi();
    
    // Jika mode edit, load data terkait dan set form values
    if (mode.value === 'edit' && props.crime) {
        // Set form values dengan ID yang benar
        form.provinsi_id = props.crime.provinsi_id;
        form.kabupaten_kota_id = props.crime.kabupaten_kota_id;
        form.kecamatan_id = props.crime.kecamatan_id;
        form.jenis_kriminal = props.crime.jenis_kriminal;
        form.deskripsi = props.crime.deskripsi;
        form.latitude = props.crime.latitude;
        form.longitude = props.crime.longitude;
        
        // Load cascading data dengan delay untuk memastikan data provinsi sudah loaded
        if (props.crime.provinsi_id) {
            await fetchKabupatenKota(props.crime.provinsi_id);
        }
        if (props.crime.kabupaten_kota_id) {
            await fetchKecamatan(props.crime.kabupaten_kota_id);
        }
    }
    
    // Setup map - tunggu sampai Leaflet benar-benar ready
    const initMap = () => {
        const L = (window as any).L;
        if (L && mapContainer.value) {
            try {
                const defaultLat = typeof form.latitude === 'number' ? form.latitude : parseFloat(form.latitude?.toString() || '-6.2088');
                const defaultLng = typeof form.longitude === 'number' ? form.longitude : parseFloat(form.longitude?.toString() || '106.8456');
                
                // Pastikan container ready
                if (mapContainer.value.offsetWidth === 0 || mapContainer.value.offsetHeight === 0) {
                    setTimeout(initMap, 100);
                    return;
                }
                
                map = L.map(mapContainer.value).setView([defaultLat, defaultLng], 10);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);
                
                marker = L.marker([defaultLat, defaultLng], { 
                    draggable: true,
                    title: 'Drag untuk memindahkan lokasi'
                }).addTo(map);
                
                // Event listener untuk drag marker
                marker.on('dragend', function () {
                    const latlng = marker.getLatLng();
                    form.latitude = parseFloat(latlng.lat.toFixed(6));
                    form.longitude = parseFloat(latlng.lng.toFixed(6));
                });
                
                // Event listener untuk click pada map
                map.on('click', function (e: any) {
                    marker.setLatLng(e.latlng);
                    form.latitude = parseFloat(e.latlng.lat.toFixed(6));
                    form.longitude = parseFloat(e.latlng.lng.toFixed(6));
                });
                
                // Refresh map size setelah semua setup selesai
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize();
                    }
                }, 100);
                
                // Add window resize listener
                const handleResize = () => {
                    if (map) {
                        setTimeout(() => {
                            map.invalidateSize();
                        }, 100);
                    }
                };
                window.addEventListener('resize', handleResize);
                
                // Cleanup on unmount
                const cleanup = () => {
                    window.removeEventListener('resize', handleResize);
                    if (map) {
                        map.remove();
                    }
                };
                
                // Store cleanup function for later use
                (window as any).mapCleanup = cleanup;
                
            } catch (error) {
                console.error('Error initializing map:', error);
                setTimeout(initMap, 500);
            }
        } else {
            setTimeout(initMap, 100);
        }
    };
    
    // Start map initialization
    setTimeout(initMap, 100);
});

// Cleanup on component unmount
onUnmounted(() => {
    if ((window as any).mapCleanup) {
        (window as any).mapCleanup();
    }
});

// Watchers untuk cascading dropdown
watch(() => form.provinsi_id, (newProvinsiId) => {
    if (newProvinsiId) {
        fetchKabupatenKota(newProvinsiId);
        // Update center peta berdasarkan provinsi yang dipilih
        const selectedProvinsi = provinsiList.value.find(p => p.id == newProvinsiId);
        if (selectedProvinsi) {
            updateMapCenter(selectedProvinsi.nama);
        }
    } else if (mode.value === 'create') {
        // Hanya reset untuk mode create
        kabupatenKotaList.value = [];
        kecamatanList.value = [];
    }
});

watch(() => form.kabupaten_kota_id, (newKabupatenKotaId) => {
    if (newKabupatenKotaId) {
        fetchKecamatan(newKabupatenKotaId);
    } else if (mode.value === 'create') {
        // Hanya reset untuk mode create
        kecamatanList.value = [];
    }
});

watch(() => [form.latitude, form.longitude], ([lat, lng]) => {
    if (marker && lat && lng && !isNaN(parseFloat(lat.toString())) && !isNaN(parseFloat(lng.toString()))) {
        const latNum = parseFloat(lat.toString());
        const lngNum = parseFloat(lng.toString());
        marker.setLatLng([latNum, lngNum]);
        if (map) {
            map.setView([latNum, lngNum], map.getZoom());
        }
    }
});
</script>

<template>
    <Head :title="mode === 'create' ? 'Tambah Data Kriminal' : 'Edit Data Kriminal'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto p-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        {{ mode === 'create' ? 'Tambah Data Kriminal' : 'Edit Data Kriminal' }}
                    </h1>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ mode === 'create' 
                        ? 'Lengkapi formulir di bawah untuk menambahkan data kriminal baru ke sistem.' 
                        : 'Perbarui informasi data kriminal yang sudah ada.' 
                    }}
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Location Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Informasi Lokasi
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Pilih lokasi administratif dimana kejadian terjadi</p>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Provinsi -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Provinsi <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.provinsi_id" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors" 
                                    required>
                                <option value="">Pilih Provinsi</option>
                                <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                                    {{ provinsi.nama }}
                                </option>
                            </select>
                            <div v-if="form.errors.provinsi_id" class="text-red-500 text-sm">{{ form.errors.provinsi_id }}</div>
                        </div>

                        <!-- Kabupaten/Kota -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kabupaten/Kota <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.kabupaten_kota_id" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed" 
                                    :disabled="!form.provinsi_id || kabupatenKotaList.length === 0" 
                                    required>
                                <option value="">
                                    {{ !form.provinsi_id ? 'Pilih provinsi terlebih dahulu' : 'Pilih Kabupaten/Kota' }}
                                </option>
                                <option v-for="kabupatenKota in kabupatenKotaList" :key="kabupatenKota.id" :value="kabupatenKota.id">
                                    {{ kabupatenKota.nama }}
                                </option>
                            </select>
                            <div v-if="form.errors.kabupaten_kota_id" class="text-red-500 text-sm">{{ form.errors.kabupaten_kota_id }}</div>
                        </div>

                        <!-- Kecamatan -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kecamatan <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.kecamatan_id" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed" 
                                    :disabled="!form.kabupaten_kota_id || kecamatanList.length === 0" 
                                    required>
                                <option value="">
                                    {{ !form.kabupaten_kota_id ? 'Pilih kabupaten/kota terlebih dahulu' : 'Pilih Kecamatan' }}
                                </option>
                                <option v-for="kecamatan in kecamatanList" :key="kecamatan.id" :value="kecamatan.id">
                                    {{ kecamatan.nama }}
                                </option>
                            </select>
                            <div v-if="form.errors.kecamatan_id" class="text-red-500 text-sm">{{ form.errors.kecamatan_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Crime Details Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            Detail Kejahatan
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Informasi tentang jenis dan detail kejahatan</p>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Jenis Kriminal -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Jenis Kriminal <span class="text-red-500">*</span>
                            </label>
                            <select v-model="form.jenis_kriminal" 
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors" 
                                    required>
                                <option value="">Pilih Jenis Kriminal</option>
                                <option v-for="jenis in jenisKriminalOptions" :key="jenis.value" :value="jenis.value">
                                    {{ jenis.label }}
                                </option>
                            </select>
                            <div v-if="form.errors.jenis_kriminal" class="text-red-500 text-sm">{{ form.errors.jenis_kriminal }}</div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Deskripsi Kejadian
                            </label>
                            <textarea v-model="form.deskripsi" 
                                      rows="4" 
                                      placeholder="Jelaskan detail kejadian kriminal yang terjadi..."
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors resize-none"></textarea>
                            <div v-if="form.errors.deskripsi" class="text-red-500 text-sm">{{ form.errors.deskripsi }}</div>
                        </div>
                    </div>
                </div>

                <!-- Location Map Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Koordinat Lokasi
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Klik pada peta atau seret marker untuk menentukan lokasi kejadian</p>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Map -->
                        <div class="relative">
                            <div ref="mapContainer" class="w-full h-80 border border-gray-300 dark:border-gray-600 rounded-lg shadow-inner"></div>
                            <div class="absolute top-3 left-3 bg-white dark:bg-gray-800 px-3 py-2 rounded-lg shadow-md">
                                <p class="text-xs text-gray-600 dark:text-gray-400">Klik peta atau drag marker untuk memilih lokasi</p>
                            </div>
                        </div>

                        <!-- Coordinate Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Latitude <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.latitude" 
                                       type="number" 
                                       step="any" 
                                       placeholder="Contoh: -6.2088" 
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors" 
                                       required />
                                <div v-if="form.errors.latitude" class="text-red-500 text-sm">{{ form.errors.latitude }}</div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Longitude <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.longitude" 
                                       type="number" 
                                       step="any" 
                                       placeholder="Contoh: 106.8456" 
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors" 
                                       required />
                                <div v-if="form.errors.longitude" class="text-red-500 text-sm">{{ form.errors.longitude }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6">
                    <button type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Menyimpan...' : (mode === 'create' ? 'Tambah Data' : 'Update Data') }}
                    </button>
                    <a href="/crime-data" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-gray-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
  