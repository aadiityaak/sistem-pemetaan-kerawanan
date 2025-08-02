<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

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

interface MonitoringData {
    id: number;
    provinsi_id: number;
    kabupaten_kota_id: number;
    kecamatan_id: number;
    category_id: number;
    sub_category_id: number;
    latitude: number;
    longitude: number;
    title: string;
    description: string;
    jumlah_terdampak: number | null;
    severity_level: string;
    status: string;
    incident_date: string;
    created_at: string;
    updated_at: string;
    additional_data: Record<string, any>;
    provinsi: { id: number; nama: string };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number };
    category: { id: number; name: string; slug: string; color: string };
    sub_category: { id: number; name: string; slug: string };
}

const props = defineProps<{
    monitoringData: MonitoringData;
    provinsi: Provinsi[];
    kabupatenKota: KabupatenKota[];
    kecamatan: Kecamatan[];
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
        title: 'Edit Data',
        href: `/monitoring-data/${props.monitoringData.id}/edit`,
    },
];

// Helper function to format date for input
const formatDateForInput = (dateString: string): string => {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        return date.toISOString().split('T')[0];
    } catch (error) {
        console.error('Error formatting date:', error);
        return '';
    }
};

const form = useForm({
    provinsi_id: props.monitoringData.provinsi_id.toString(),
    kabupaten_kota_id: props.monitoringData.kabupaten_kota_id.toString(),
    kecamatan_id: props.monitoringData.kecamatan_id.toString(),
    category_id: props.monitoringData.category_id.toString(),
    sub_category_id: props.monitoringData.sub_category_id.toString(),
    latitude: props.monitoringData.latitude.toString(),
    longitude: props.monitoringData.longitude.toString(),
    title: props.monitoringData.title,
    description: props.monitoringData.description,
    jumlah_terdampak: props.monitoringData.jumlah_terdampak?.toString() || '',
    severity_level: props.monitoringData.severity_level,
    status: props.monitoringData.status,
    incident_date: formatDateForInput(props.monitoringData.incident_date),
    additional_data: props.monitoringData.additional_data || {},
    _method: 'PUT',
});

// Map related refs
let map: any;
let marker: any;
const mapContainer = ref();
const selectedLocation = ref<{ lat: number; lng: number }>({
    lat: typeof props.monitoringData.latitude === 'number' ? props.monitoringData.latitude : 0,
    lng: typeof props.monitoringData.longitude === 'number' ? props.monitoringData.longitude : 0,
});

// Filtered lists based on selection
const filteredKabupatenKota = computed(() => {
    if (!form.provinsi_id || !props.kabupatenKota) return [];
    return props.kabupatenKota.filter((kab: KabupatenKota) => kab.provinsi_id === parseInt(form.provinsi_id));
});

const filteredKecamatan = computed(() => {
    if (!form.kabupaten_kota_id || !props.kecamatan) return [];
    return props.kecamatan.filter((kec: Kecamatan) => kec.kabupaten_kota_id === parseInt(form.kabupaten_kota_id));
});

const filteredSubCategories = computed(() => {
    if (!form.category_id || !props.categories) return [];
    const category = props.categories.find((cat) => cat.id === parseInt(form.category_id));
    return category?.sub_categories || [];
});

// Computed for safe coordinate display
const currentLocationText = computed(() => {
    const lat = typeof selectedLocation.value?.lat === 'number' ? selectedLocation.value.lat : 0;
    const lng = typeof selectedLocation.value?.lng === 'number' ? selectedLocation.value.lng : 0;
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
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
    if (form.provinsi_id !== props.monitoringData.provinsi_id.toString()) {
        form.kabupaten_kota_id = '';
        form.kecamatan_id = '';
    }
});

watch(
    () => form.kabupaten_kota_id,
    () => {
        if (form.kabupaten_kota_id !== props.monitoringData.kabupaten_kota_id.toString()) {
            form.kecamatan_id = '';
        }
    },
);

watch(
    () => form.category_id,
    () => {
        if (form.category_id !== props.monitoringData.category_id.toString()) {
            form.sub_category_id = '';
        }
    },
);

// Update marker position when coordinates change
const updateMarkerPosition = () => {
    if (map && marker) {
        const lat = parseFloat(form.latitude);
        const lng = parseFloat(form.longitude);

        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], map.getZoom());
            selectedLocation.value = { lat: lat, lng: lng };
        }
    }
};

watch([() => form.latitude, () => form.longitude], updateMarkerPosition);

// Initialize map
const initializeMap = async () => {
    if (typeof window !== 'undefined') {
        const L = await import('leaflet');

        if (mapContainer.value) {
            const initialLat = typeof props.monitoringData.latitude === 'number' ? props.monitoringData.latitude : -6.2;
            const initialLng = typeof props.monitoringData.longitude === 'number' ? props.monitoringData.longitude : 106.8;

            map = L.map(mapContainer.value).setView([initialLat, initialLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(map);

            // Add initial marker
            marker = L.marker([initialLat, initialLng]).addTo(map);

            // Add click event to map
            map.on('click', (e: any) => {
                const { lat, lng } = e.latlng;
                selectedLocation.value = { lat: lat, lng: lng };
                form.latitude = lat.toString();
                form.longitude = lng.toString();

                // Update marker position
                marker.setLatLng([lat, lng]);
                marker.bindPopup(`Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`).openPopup();
            });
        }
    }
};

// Submit form
const submit = () => {
    form.post(`/monitoring-data/${props.monitoringData.id}`, {
        onSuccess: () => {
            // Redirect will be handled by Inertia
        },
    });
};

// Initialize map on mount
onMounted(() => {
    initializeMap();
});
</script>

<template>
    <Head :title="`Edit: ${monitoringData.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Data Monitoring</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Edit data monitoring: {{ monitoringData.title }}</p>
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
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Deskripsi </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan deskripsi detail"
                                    ></textarea>
                                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</div>
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
                                            {{ category.name }}
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
                                            {{ subCategory.name }}
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
                                        <option v-for="prov in provinsi" :key="prov.id" :value="prov.id">
                                            {{ prov.nama }}
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
                            <Button type="button" variant="outline" @click="$inertia.visit(`/monitoring-data/${monitoringData.id}`)"> Batal </Button>
                            <Button type="submit" :disabled="form.processing">
                                <svg v-if="form.processing" class="mr-3 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                {{ form.processing ? 'Memperbarui...' : 'Perbarui Data' }}
                            </Button>
                        </div>
                    </div>

                    <!-- Map -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Edit Lokasi di Peta</h3>
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Klik pada peta untuk mengubah koordinat lokasi kejadian</p>
                        <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Lokasi saat ini: {{ currentLocationText }}</p>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
