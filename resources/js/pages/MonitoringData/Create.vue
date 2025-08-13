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
    video: null as File | null,
    uploaded_video_path: null as string | null, // For chunked uploaded video
});

// Map related refs
let map: any;
const mapContainer = ref();
const selectedLocation = ref<{ lat: number; lng: number } | null>(null);

// Video related refs
const videoPreview = ref<string | null>(null);
const videoFile = ref<File | null>(null);
const videoUploadProgress = ref(0);
const isVideoUploading = ref(false);
const videoUploadError = ref<string | null>(null);
const uploadedVideoPath = ref<string | null>(null);

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

// Video upload functions
const handleVideoUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        // Check file size (200MB limit - increased for chunked upload)
        if (file.size > 200 * 1024 * 1024) {
            alert('Ukuran file video terlalu besar. Maksimal 200MB.');
            return;
        }
        
        // Check file type
        if (!file.type.startsWith('video/')) {
            alert('File harus berupa video.');
            return;
        }
        
        videoFile.value = file;
        videoUploadError.value = null;
        
        // Create preview URL
        const url = URL.createObjectURL(file);
        videoPreview.value = url;
        
        // Start auto upload
        await uploadVideoChunked(file);
    }
};

// Chunked video upload function
const uploadVideoChunked = async (file: File) => {
    const chunkSize = 1024 * 1024; // 1MB chunks
    const totalChunks = Math.ceil(file.size / chunkSize);
    const uploadId = Date.now().toString();
    
    isVideoUploading.value = true;
    videoUploadProgress.value = 0;
    
    try {
        for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
            const start = chunkIndex * chunkSize;
            const end = Math.min(start + chunkSize, file.size);
            const chunk = file.slice(start, end);
            
            const formData = new FormData();
            formData.append('chunk', chunk);
            formData.append('chunkIndex', chunkIndex.toString());
            formData.append('totalChunks', totalChunks.toString());
            formData.append('uploadId', uploadId);
            formData.append('fileName', file.name);
            formData.append('fileSize', file.size.toString());
            
            const response = await fetch('/api/upload-video-chunk', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            if (!response.ok) {
                throw new Error(`Upload failed: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            // Update progress
            videoUploadProgress.value = Math.round(((chunkIndex + 1) / totalChunks) * 100);
            
            // If this is the last chunk and upload is complete
            if (chunkIndex === totalChunks - 1 && result.videoPath) {
                uploadedVideoPath.value = result.videoPath;
                form.video = null; // Clear the file from form since it's already uploaded
                form.uploaded_video_path = result.videoPath;
            }
        }
    } catch (error) {
        console.error('Video upload error:', error);
        videoUploadError.value = error instanceof Error ? error.message : 'Upload gagal';
    } finally {
        isVideoUploading.value = false;
    }
};

const removeVideo = async () => {
    if (videoPreview.value && videoFile.value) {
        // Only revoke object URL if it's a new file (created with URL.createObjectURL)
        URL.revokeObjectURL(videoPreview.value);
    }
    
    // If video was uploaded, delete it from server
    if (uploadedVideoPath.value) {
        try {
            await fetch('/api/delete-video', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    videoPath: uploadedVideoPath.value,
                }),
            });
        } catch (error) {
            console.error('Failed to delete video:', error);
        }
    }
    
    videoPreview.value = null;
    videoFile.value = null;
    uploadedVideoPath.value = null;
    videoUploadProgress.value = 0;
    videoUploadError.value = null;
    isVideoUploading.value = false;
    form.video = null;
    form.uploaded_video_path = null;
};

// Submit form
const submit = () => {
    // Include uploaded video path if available
    if (uploadedVideoPath.value) {
        form.uploaded_video_path = uploadedVideoPath.value;
    }
    
    form.post('/monitoring-data', {
        onSuccess: () => {
            // Redirect will be handled by Inertia
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
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

                    <div class="space-y-6">
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
                                        Kecamatan <span class="text-gray-400 text-xs">(Opsional)</span>
                                    </label>
                                    <select
                                        v-model="form.kecamatan_id"
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

                        <!-- Map -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Pilih Lokasi di Peta</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Klik pada peta untuk menentukan koordinat lokasi kejadian</p>
                            <div ref="mapContainer" class="h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>
                            <p v-if="selectedLocation" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Lokasi terpilih: {{ selectedLocation.lat.toFixed(6) }}, {{ selectedLocation.lng.toFixed(6) }}
                            </p>
                        </div>

                        <!-- Gallery -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <GalleryInput
                                v-model="form.gallery"
                            />
                        </div>

                        <!-- Video -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Video</h3>
                            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Upload video yang berkaitan dengan kejadian ini (Opsional)</p>
                            
                            <!-- Video Upload Area -->
                            <div class="relative cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-6 transition-colors hover:border-gray-400 dark:border-gray-600 dark:hover:border-gray-500"
                                 :class="{ 'pointer-events-none opacity-50': isVideoUploading }">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <div class="mt-4">
                                        <label class="cursor-pointer" :class="{ 'pointer-events-none': isVideoUploading }">
                                            <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                                                {{ isVideoUploading ? 'Mengupload video...' : 'Klik untuk upload video' }}
                                            </span>
                                            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                                                MP4, MOV, AVI hingga 200MB (Auto upload)
                                            </span>
                                            <input
                                                type="file"
                                                accept="video/*"
                                                class="sr-only"
                                                :disabled="isVideoUploading"
                                                @change="handleVideoUpload"
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Upload Progress -->
                            <div v-if="isVideoUploading" class="mt-4">
                                <div class="mb-2 flex justify-between text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">Mengupload video...</span>
                                    <span class="text-gray-700 dark:text-gray-300">{{ videoUploadProgress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                         :style="{ width: videoUploadProgress + '%' }"></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    {{ videoFile?.name }} - Upload otomatis sedang berlangsung
                                </p>
                            </div>
                            
                            <!-- Upload Error -->
                            <div v-if="videoUploadError" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md dark:bg-red-900/20 dark:border-red-800">
                                <p class="text-sm text-red-600 dark:text-red-400">
                                    <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    Upload Error: {{ videoUploadError }}
                                </p>
                                <button 
                                    @click="videoUploadError = null" 
                                    class="mt-2 text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 underline"
                                >
                                    Tutup
                                </button>
                            </div>
                            
                            <!-- Video Preview -->
                            <div v-if="videoPreview" class="mt-4">
                                <div class="relative rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden">
                                    <video 
                                        :src="videoPreview"
                                        controls
                                        class="w-full aspect-video object-cover"
                                    ></video>
                                    <button
                                        type="button"
                                        @click="removeVideo"
                                        :disabled="isVideoUploading"
                                        class="absolute right-2 top-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                        title="Hapus video"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Upload Success Badge -->
                                    <div v-if="uploadedVideoPath && !isVideoUploading" 
                                         class="absolute left-2 top-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                        <svg class="inline h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Terupload
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ videoFile?.name }}</p>
                                    <div v-if="uploadedVideoPath" class="text-xs text-green-600 dark:text-green-400">
                                        ✓ Upload berhasil
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
