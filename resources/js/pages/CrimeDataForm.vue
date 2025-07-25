<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
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
    
    // Setup map - tunggu sejenak untuk memastikan DOM sudah ready
    setTimeout(() => {
        const L = (window as any).L;
        if (L && mapContainer.value) {
            const defaultLat = typeof form.latitude === 'number' ? form.latitude : parseFloat(form.latitude || '-6.2088');
            const defaultLng = typeof form.longitude === 'number' ? form.longitude : parseFloat(form.longitude || '106.8456');
            
            map = L.map(mapContainer.value).setView([defaultLat, defaultLng], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
            
            marker.on('dragend', function () {
                const latlng = marker.getLatLng();
                form.latitude = latlng.lat;
                form.longitude = latlng.lng;
            });
            
            map.on('click', function (e: any) {
                marker.setLatLng(e.latlng);
                form.latitude = e.latlng.lat;
                form.longitude = e.latlng.lng;
            });
        }
    }, 500);
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
    if (marker && lat && lng) {
        marker.setLatLng([lat, lng]);
        map.setView([lat, lng], map.getZoom());
    }
});
</script>

<template>
    <Head :title="mode === 'create' ? 'Tambah Data Kriminal' : 'Edit Data Kriminal'" />
    <AppLayout :breadcrumbs="breadcrumbs">
      <div class="w-full mx-2">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Provinsi</label>
            <select v-model="form.provinsi_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
              <option value="">Pilih Provinsi</option>
              <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                {{ provinsi.nama }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kabupaten/Kota</label>
            <select v-model="form.kabupaten_kota_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :disabled="!form.provinsi_id || kabupatenKotaList.length === 0" required>
              <option value="">Pilih Kabupaten/Kota</option>
              <option v-for="kabupatenKota in kabupatenKotaList" :key="kabupatenKota.id" :value="kabupatenKota.id">
                {{ kabupatenKota.nama }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kecamatan</label>
            <select v-model="form.kecamatan_id" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" :disabled="!form.kabupaten_kota_id || kecamatanList.length === 0" required>
              <option value="">Pilih Kecamatan</option>
              <option v-for="kecamatan in kecamatanList" :key="kecamatan.id" :value="kecamatan.id">
                {{ kecamatan.nama }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Jenis Kriminal</label>
            <select v-model="form.jenis_kriminal" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
              <option value="">Pilih Jenis Kriminal</option>
              <option v-for="jenis in jenisKriminalOptions" :key="jenis.value" :value="jenis.value">
                {{ jenis.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="4" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Pilih Lokasi (Latitude, Longitude)</label>
            <div ref="mapContainer" class="w-full h-72 border border-gray-300 dark:border-gray-700 rounded-md mb-4"></div>
            <div class="flex gap-4">
              <input v-model="form.latitude" placeholder="Latitude" class="w-1/2 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
              <input v-model="form.longitude" placeholder="Longitude" class="w-1/2 px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required />
            </div>
          </div>
          <div class="flex gap-3 pt-4">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition duration-200">
              {{ mode === 'create' ? 'Tambah' : 'Update' }}
            </button>
            <a href="/crime-data" class="px-6 py-2 bg-gray-400 text-white rounded-md shadow hover:bg-gray-500 transition duration-200">
              Batal
            </a>
          </div>
        </form>
      </div>
    </AppLayout>
  </template>
  