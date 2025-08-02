<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Button } from '@/components/ui/button';

interface Provinsi {
    id: number;
    nama: string;
}

interface KabupatenKota {
    id: number;
    nama: string;
    provinsi_id: number;
}

interface Kecamatan {
    id: number;
    nama: string;
    kabupaten_kota_id: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    sub_categories: SubCategory[];
}

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    category_id: number;
}

const props = defineProps<{
    provinsiList: Provinsi[];
    kabupatenKotaList: KabupatenKota[];
    kecamatanList: Kecamatan[];
    categories: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Data Monitoring',
        href: '/monitoring-data',
    },
    {
        title: 'Tambah Data',
        href: '/monitoring-data/create',
    },
];

const form = useForm({
    provinsi_id: '',
    kabupaten_kota_id: '',
    kecamatan_id: '',
    category_id: '',
    sub_category_id: '',
    latitude: '',
    longitude: '',
    title: '',
    description: '',
    jumlah_terdampak: '',
    severity_level: 'medium',
    status: 'active',
    incident_date: new Date().toISOString().split('T')[0],
    additional_data: {} as Record<string, any>,
});

// Map related refs
let map: any;
const mapContainer = ref();
const selectedLocation = ref<{lat: number, lng: number} | null>(null);

// Filtered lists based on selection
const filteredKabupatenKota = computed(() => {
    if (!form.provinsi_id) return [];
    return props.kabupatenKotaList.filter(kab => kab.provinsi_id === parseInt(form.provinsi_id));
});

const filteredKecamatan = computed(() => {
    if (!form.kabupaten_kota_id) return [];
    return props.kecamatanList.filter(kec => kec.kabupaten_kota_id === parseInt(form.kabupaten_kota_id));
});

const filteredSubCategories = computed(() => {
    if (!form.category_id) return [];
    const category = props.categories.find(cat => cat.id === parseInt(form.category_id));
    return category?.sub_categories || [];
});

// Severity options
const severityOptions = [
    { value: 'low', label: 'Rendah', color: 'bg-green-100 text-green-800' },
    { value: 'medium', label: 'Sedang', color: 'bg-yellow-100 text-yellow-800' },
    { value: 'high', label: 'Tinggi', color: 'bg-orange-100 text-orange-800' },
    { value: 'critical', label: 'Kritis', color: 'bg-red-100 text-red-800' },
];

// Status options
const statusOptions = [
    { value: 'active', label: 'Aktif' },
    { value: 'resolved', label: 'Selesai' },
    { value: 'pending', label: 'Pending' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

// Watch for location changes
watch([() => form.provinsi_id, () => form.kabupaten_kota_id], () => {
    form.kabupaten_kota_id = '';
    form.kecamatan_id = '';
});

watch(() => form.kabupaten_kota_id, () => {
    form.kecamatan_id = '';
});

watch(() => form.category_id, () => {
    form.sub_category_id = '';
});

// Initialize map
const initializeMap = async () => {
    if (typeof window !== 'undefined') {
        const L = await import('leaflet');
        
        if (mapContainer.value) {
            map = L.map(mapContainer.value).setView([-2.5489, 118.0149], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add click event to map
            map.on('click', (e: any) => {
                const { lat, lng } = e.latlng;
                selectedLocation.value = { lat, lng };
                form.latitude = lat.toString();
                form.longitude = lng.toString();

                // Clear existing markers
                map.eachLayer((layer: any) => {
                    if (layer instanceof L.Marker) {
                        map.removeLayer(layer);
                    }
                });

                // Add new marker
                L.marker([lat, lng]).addTo(map)
                    .bindPopup(`Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`)
                    .openPopup();
            });
        }
    }
};

// Submit form
const submit = () => {
    form.post('/monitoring-data', {
        onSuccess: () => {
            // Redirect will be handled by Inertia
        },
    });
};

// Initialize map on mount
import { onMounted } from 'vue';
onMounted(() => {
    initializeMap();
});
</script>

<template>
    <Head title="Tambah Data Monitoring" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Data Monitoring</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Tambahkan data monitoring baru untuk berbagai kategori
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Form Fields -->
                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Dasar</h3>
                            
                            <div class="space-y-4">
                                <!-- Title -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Judul <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan judul data monitoring"
                                    />
                                    <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">{{ form.errors.title }}</div>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Deskripsi
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan deskripsi detail"
                                    ></textarea>
                                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                                </div>

                                <!-- Jumlah Terdampak -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Jumlah Terdampak
                                    </label>
                                    <input
                                        v-model="form.jumlah_terdampak"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan jumlah orang yang terdampak"
                                    />
                                    <div v-if="form.errors.jumlah_terdampak" class="text-red-500 text-sm mt-1">{{ form.errors.jumlah_terdampak }}</div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Jumlah orang yang terlibat atau terdampak dalam kejadian ini
                                    </p>
                                </div>

                                <!-- Incident Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Tanggal Kejadian <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.incident_date"
                                        type="date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    />
                                    <div v-if="form.errors.incident_date" class="text-red-500 text-sm mt-1">{{ form.errors.incident_date }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Category & Classification -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Kategori & Klasifikasi</h3>
                            
                            <div class="space-y-4">
                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Kategori <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.category_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Kategori</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.category_id" class="text-red-500 text-sm mt-1">{{ form.errors.category_id }}</div>
                                </div>

                                <!-- Sub Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Sub Kategori <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.sub_category_id"
                                        required
                                        :disabled="!form.category_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                                    >
                                        <option value="">Pilih Sub Kategori</option>
                                        <option v-for="subCategory in filteredSubCategories" :key="subCategory.id" :value="subCategory.id">
                                            {{ subCategory.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.sub_category_id" class="text-red-500 text-sm mt-1">{{ form.errors.sub_category_id }}</div>
                                </div>

                                <!-- Severity Level -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Level Severity <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.severity_level"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="severity in severityOptions" :key="severity.value" :value="severity.value">
                                            {{ severity.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.severity_level" class="text-red-500 text-sm mt-1">{{ form.errors.severity_level }}</div>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.status"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Selection -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lokasi</h3>
                            
                            <div class="space-y-4">
                                <!-- Provinsi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Provinsi <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.provinsi_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Provinsi</option>
                                        <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                                            {{ provinsi.nama }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.provinsi_id" class="text-red-500 text-sm mt-1">{{ form.errors.provinsi_id }}</div>
                                </div>

                                <!-- Kabupaten/Kota -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Kabupaten/Kota <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.kabupaten_kota_id"
                                        required
                                        :disabled="!form.provinsi_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                                    >
                                        <option value="">Pilih Kabupaten/Kota</option>
                                        <option v-for="kabkota in filteredKabupatenKota" :key="kabkota.id" :value="kabkota.id">
                                            {{ kabkota.nama }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.kabupaten_kota_id" class="text-red-500 text-sm mt-1">{{ form.errors.kabupaten_kota_id }}</div>
                                </div>

                                <!-- Kecamatan -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Kecamatan <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.kecamatan_id"
                                        required
                                        :disabled="!form.kabupaten_kota_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                                    >
                                        <option value="">Pilih Kecamatan</option>
                                        <option v-for="kecamatan in filteredKecamatan" :key="kecamatan.id" :value="kecamatan.id">
                                            {{ kecamatan.nama }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.kecamatan_id" class="text-red-500 text-sm mt-1">{{ form.errors.kecamatan_id }}</div>
                                </div>

                                <!-- Koordinat Manual -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Latitude <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.latitude"
                                            type="number"
                                            step="any"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="-6.200000"
                                        />
                                        <div v-if="form.errors.latitude" class="text-red-500 text-sm mt-1">{{ form.errors.latitude }}</div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Longitude <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.longitude"
                                            type="number"
                                            step="any"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="106.816666"
                                        />
                                        <div v-if="form.errors.longitude" class="text-red-500 text-sm mt-1">{{ form.errors.longitude }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="$inertia.visit('/monitoring-data')">
                                Batal
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pilih Lokasi di Peta</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Klik pada peta untuk menentukan koordinat lokasi kejadian
                        </p>
                        <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                        <p v-if="selectedLocation" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            Lokasi terpilih: {{ selectedLocation.lat.toFixed(6) }}, {{ selectedLocation.lng.toFixed(6) }}
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
