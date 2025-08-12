<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { RichTextEditor } from '@/components/ui/rich-text-editor';
import { GalleryInput } from '@/components/ui/gallery-input';
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
    sumber_berita?: string;
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
    isUserRestricted?: boolean;
    userProvinsiId?: number;
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
    sumber_berita: props.monitoringData.sumber_berita || '',
    jumlah_terdampak: props.monitoringData.jumlah_terdampak?.toString() || '',
    severity_level: props.monitoringData.severity_level,
    status: props.monitoringData.status,
    incident_date: formatDateForInput(props.monitoringData.incident_date),
    additional_data: props.monitoringData.additional_data || {},
    gallery: [] as File[],
    _method: 'PUT',
});

// Gallery related refs and interfaces
interface GalleryImage {
    file?: File
    preview: string
    existing?: boolean
    path?: string
}

const galleryFileInput = ref<HTMLInputElement>()
const galleryImages = ref<GalleryImage[]>([])
const galleryErrors = ref<string[]>([])
const isDragOver = ref(false)

// Initialize existing gallery images
const initializeExistingGallery = () => {
    if (props.monitoringData.gallery && Array.isArray(props.monitoringData.gallery)) {
        galleryImages.value = props.monitoringData.gallery.map((item: any) => ({
            preview: item.url || `/storage/${item.path || item}`,
            existing: true,
            path: item.path || item
        }))
    }
}

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

// Gallery functions
const triggerFileInput = () => {
    galleryFileInput.value?.click()
}

const handleGalleryFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (target.files) {
        handleGalleryFiles(Array.from(target.files))
    }
}

const handleDrop = (event: DragEvent) => {
    isDragOver.value = false
    if (event.dataTransfer?.files) {
        handleGalleryFiles(Array.from(event.dataTransfer.files))
    }
}

const handleGalleryFiles = (files: File[]) => {
    galleryErrors.value = []
    const maxFiles = 10
    const maxFileSize = 10 // MB
    const acceptedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
    
    files.forEach((file) => {
        // Check file type
        if (!acceptedTypes.includes(file.type)) {
            galleryErrors.value.push(`${file.name}: Tipe file tidak didukung`)
            return
        }
        
        // Check file size
        if (file.size > maxFileSize * 1024 * 1024) {
            galleryErrors.value.push(`${file.name}: Ukuran file terlalu besar (max ${maxFileSize}MB)`)
            return
        }
        
        // Check total files limit
        if (galleryImages.value.length >= maxFiles) {
            galleryErrors.value.push(`Maksimal ${maxFiles} file`)
            return
        }
        
        // Create preview
        const reader = new FileReader()
        reader.onload = (e) => {
            galleryImages.value.push({
                file,
                preview: e.target?.result as string
            })
            updateGalleryFormData()
        }
        reader.readAsDataURL(file)
    })
}

const removeGalleryImage = async (index: number) => {
    const image = galleryImages.value[index]
    
    if (image.existing && image.path) {
        // Delete existing image from server
        try {
            const response = await fetch(`/monitoring-data/${props.monitoringData.id}/gallery`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ image_path: image.path })
            })
            
            if (response.ok) {
                galleryImages.value.splice(index, 1)
            } else {
                galleryErrors.value.push('Gagal menghapus gambar dari server')
            }
        } catch (error) {
            galleryErrors.value.push('Error saat menghapus gambar')
        }
    } else {
        // Remove new image (not yet uploaded)
        galleryImages.value.splice(index, 1)
        updateGalleryFormData()
    }
}

const updateGalleryFormData = () => {
    // Only include new files, not existing ones
    form.gallery = galleryImages.value.filter(img => img.file).map(img => img.file!)
}

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
    initializeExistingGallery();
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
                                        :disabled="props.isUserRestricted"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
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

                    <!-- Gallery & Map -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <!-- Gallery Section -->
                        <div class="mb-8">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Galeri</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Upload gambar-gambar yang berkaitan dengan kejadian ini</p>
                            
                            <!-- Upload Area -->
                            <div
                                @click="triggerFileInput"
                                @dragover.prevent
                                @drop.prevent="handleDrop"
                                class="relative cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-6 transition-colors hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500"
                                :class="{
                                    'border-blue-400 bg-blue-50 dark:bg-blue-900/20': isDragOver
                                }"
                                @dragenter="isDragOver = true"
                                @dragleave="isDragOver = false"
                            >
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    <div class="mt-4">
                                        <label class="cursor-pointer">
                                            <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                                                Klik untuk upload atau drag & drop
                                            </span>
                                            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                                                PNG, JPG, GIF hingga 10MB
                                            </span>
                                            <input
                                                ref="galleryFileInput"
                                                type="file"
                                                multiple
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleGalleryFileSelect"
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Image Preview Grid -->
                            <div v-if="galleryImages.length > 0" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                <div
                                    v-for="(image, index) in galleryImages"
                                    :key="index"
                                    class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 dark:border-gray-600"
                                >
                                    <img
                                        :src="image.preview"
                                        :alt="`Image ${index + 1}`"
                                        class="h-full w-full object-cover transition-transform group-hover:scale-105"
                                    />
                                    <button
                                        type="button"
                                        @click="removeGalleryImage(index)"
                                        class="absolute right-2 top-2 rounded-full bg-red-500 p-1 text-white opacity-0 transition-opacity hover:bg-red-600 group-hover:opacity-100"
                                        title="Hapus gambar"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Error Messages -->
                            <div v-if="galleryErrors.length > 0" class="mt-4">
                                <div v-for="(error, index) in galleryErrors" :key="index" class="text-sm text-red-500">
                                    {{ error }}
                                </div>
                            </div>
                        </div>

                        <!-- Separator -->
                        <div class="border-t border-gray-200 dark:border-gray-600 mb-8"></div>

                        <!-- Map Section -->
                        <div>
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Edit Lokasi di Peta</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Klik pada peta untuk mengubah koordinat lokasi kejadian</p>
                            <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Lokasi saat ini: {{ currentLocationText }}</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
