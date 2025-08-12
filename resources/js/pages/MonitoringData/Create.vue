<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { RichTextEditor } from '@/components/ui/rich-text-editor';
import { GalleryInput } from '@/components/ui/gallery-input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Provinsi {
    id: number;
    nama: string;
    latitude?: number | null;
    longitude?: number | null;
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
    icon?: string;
    image_url?: string;
    sub_categories: SubCategory[];
}

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    icon?: string;
    image_url?: string;
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
    sumber_berita: '',
    jumlah_terdampak: '',
    severity_level: 'medium',
    status: 'active',
    incident_date: new Date().toISOString().split('T')[0],
    additional_data: {} as Record<string, any>,
    gallery: [] as File[],
});

// Map related refs
let map: any;
const mapContainer = ref();
const selectedLocation = ref<{ lat: number; lng: number } | null>(null);

// Filtered lists based on selection
const filteredKabupatenKota = computed(() => {
    if (!form.provinsi_id) return [];
    return props.kabupatenKotaList.filter((kab) => kab.provinsi_id === parseInt(form.provinsi_id));
});

const filteredKecamatan = computed(() => {
    if (!form.kabupaten_kota_id) return [];
    return props.kecamatanList.filter((kec) => kec.kabupaten_kota_id === parseInt(form.kabupaten_kota_id));
});

const filteredSubCategories = computed(() => {
    if (!form.category_id) return [];
    const category = props.categories.find((cat) => cat.id === parseInt(form.category_id));
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
watch(() => form.provinsi_id, (newProvinsiId) => {
    form.kabupaten_kota_id = '';
    form.kecamatan_id = '';
    
    // Auto-pan map to selected province if coordinates are available
    if (map && newProvinsiId) {
        const selectedProvinsi = props.provinsiList.find(p => p.id === parseInt(newProvinsiId));
        if (selectedProvinsi && selectedProvinsi.latitude && selectedProvinsi.longitude) {
            map.setView([selectedProvinsi.latitude, selectedProvinsi.longitude], 8, {
                animate: true,
                duration: 1.0
            });
        }
    }
});

watch(
    () => form.kabupaten_kota_id,
    () => {
        form.kecamatan_id = '';
    },
);

watch(
    () => form.category_id,
    () => {
        form.sub_category_id = '';
    },
);

// Initialize map
const initializeMap = async () => {
    if (typeof window !== 'undefined') {
        const L = await import('leaflet');

        if (mapContainer.value) {
            // Use user's province coordinates if available (non-admin users have filtered province list)
            let mapCenter: [number, number] = [-2.5489, 118.0149]; // Default: Indonesia center
            let zoomLevel = 5; // Default zoom for Indonesia
            
            if (props.provinsiList.length === 1 && props.provinsiList[0].latitude && props.provinsiList[0].longitude) {
                // Single province means user is filtered to their province
                mapCenter = [props.provinsiList[0].latitude, props.provinsiList[0].longitude];
                zoomLevel = 8; // Closer zoom for province view
            }
            
            map = L.map(mapContainer.value).setView(mapCenter, zoomLevel);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
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
                L.marker([lat, lng])
                    .addTo(map)
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
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Tambahkan data monitoring baru untuk berbagai kategori</p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Form Fields -->
                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h3>

                            <div class="space-y-4">
                                <!-- Title -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Judul <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan judul data monitoring"
                                    />
                                    <div v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</div>
                                </div>

                                <!-- Description -->
                                <RichTextEditor
                                    v-model="form.description"
                                    label="Deskripsi"
                                    placeholder="Masukkan deskripsi detail tentang kejadian..."
                                    :error="form.errors.description"
                                    help-text="Berikan deskripsi lengkap mengenai kejadian yang terjadi"
                                />

                                <!-- Sumber Berita -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> 
                                        Sumber Berita 
                                        <span class="text-xs text-gray-500">(Opsional)</span>
                                    </label>
                                    <input
                                        v-model="form.sumber_berita"
                                        type="text"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan sumber berita atau referensi"
                                    />
                                    <div v-if="form.errors.sumber_berita" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.sumber_berita }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Contoh: Detik.com, Kompas.com, atau sumber informasi lainnya
                                    </p>
                                </div>

                                <!-- Jumlah Terdampak -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Jumlah Terdampak </label>
                                    <input
                                        v-model="form.jumlah_terdampak"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan jumlah orang yang terdampak"
                                    />
                                    <div v-if="form.errors.jumlah_terdampak" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.jumlah_terdampak }}
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Jumlah orang yang terlibat atau terdampak dalam kejadian ini
                                    </p>
                                </div>

                                <!-- Incident Date -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Kejadian <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.incident_date"
                                        type="date"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                    <div v-if="form.errors.incident_date" class="mt-1 text-sm text-red-500">{{ form.errors.incident_date }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Category & Classification -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Kategori & Klasifikasi</h3>

                            <div class="space-y-4">
                                <!-- Category -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kategori <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.category_id"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Kategori</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.image_url ? '🖼️ ' : (category.icon ? category.icon + ' ' : '') }}{{ category.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-500">{{ form.errors.category_id }}</div>
                                </div>

                                <!-- Sub Category -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Sub Kategori <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.sub_category_id"
                                        required
                                        :disabled="!form.category_id"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Sub Kategori</option>
                                        <option v-for="subCategory in filteredSubCategories" :key="subCategory.id" :value="subCategory.id">
                                            {{ subCategory.image_url ? '🖼️ ' : (subCategory.icon ? subCategory.icon + ' ' : '') }}{{ subCategory.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.sub_category_id" class="mt-1 text-sm text-red-500">{{ form.errors.sub_category_id }}</div>
                                </div>

                                <!-- Severity Level -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Level Severity <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.severity_level"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="severity in severityOptions" :key="severity.value" :value="severity.value">
                                            {{ severity.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.severity_level" class="mt-1 text-sm text-red-500">{{ form.errors.severity_level }}</div>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.status"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.status" class="mt-1 text-sm text-red-500">{{ form.errors.status }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Selection -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Lokasi</h3>

                            <div class="space-y-4">
                                <!-- Provinsi -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Provinsi <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.provinsi_id"
                                        required
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Provinsi</option>
                                        <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                                            {{ provinsi.nama }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.provinsi_id" class="mt-1 text-sm text-red-500">{{ form.errors.provinsi_id }}</div>
                                </div>

                                <!-- Kabupaten/Kota -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kabupaten/Kota <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.kabupaten_kota_id"
                                        required
                                        :disabled="!form.provinsi_id"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Kabupaten/Kota</option>
                                        <option v-for="kabkota in filteredKabupatenKota" :key="kabkota.id" :value="kabkota.id">
                                            {{ kabkota.nama }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.kabupaten_kota_id" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.kabupaten_kota_id }}
                                    </div>
                                </div>

                                <!-- Kecamatan -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kecamatan <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.kecamatan_id"
                                        required
                                        :disabled="!form.kabupaten_kota_id"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Pilih Kecamatan</option>
                                        <option v-for="kecamatan in filteredKecamatan" :key="kecamatan.id" :value="kecamatan.id">
                                            {{ kecamatan.nama }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.kecamatan_id" class="mt-1 text-sm text-red-500">{{ form.errors.kecamatan_id }}</div>
                                </div>

                                <!-- Koordinat Manual -->
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Latitude <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.latitude"
                                            type="number"
                                            step="any"
                                            required
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            placeholder="-6.200000"
                                        />
                                        <div v-if="form.errors.latitude" class="mt-1 text-sm text-red-500">{{ form.errors.latitude }}</div>
                                    </div>
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Longitude <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.longitude"
                                            type="number"
                                            step="any"
                                            required
                                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            placeholder="106.816666"
                                        />
                                        <div v-if="form.errors.longitude" class="mt-1 text-sm text-red-500">{{ form.errors.longitude }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end gap-3">
                            <Button type="button" variant="outline" @click="$inertia.visit('/monitoring-data')"> Batal </Button>
                            <Button type="submit" :disabled="form.processing">
                                <svg v-if="form.processing" class="mr-3 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Gallery & Map -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <!-- Gallery Section -->
                        <GalleryInput
                            v-model="form.gallery"
                        />

                        <!-- Separator -->
                        <div class="border-t border-gray-200 dark:border-gray-600 my-8"></div>

                        <!-- Map Section -->
                        <div>
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Pilih Lokasi di Peta</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Klik pada peta untuk menentukan koordinat lokasi kejadian</p>
                            <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                            <p v-if="selectedLocation" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Lokasi terpilih: {{ selectedLocation.lat.toFixed(6) }}, {{ selectedLocation.lng.toFixed(6) }}
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
