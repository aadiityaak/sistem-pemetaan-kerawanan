<template>
    <AppLayout title="Edit Provinsi">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit Provinsi</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Update data provinsi dan koordinatnya</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('provinsi.index')"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informasi Provinsi</h2>
                    
                    <form @submit.prevent="submit">
                        <div class="space-y-4">
                            <!-- Nama Provinsi -->
                            <div>
                                <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Provinsi <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="nama"
                                    v-model="form.nama"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Masukkan nama provinsi"
                                    required
                                />
                                <div v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}</div>
                            </div>

                            <!-- Koordinat -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Latitude
                                    </label>
                                    <input
                                        id="latitude"
                                        v-model="form.latitude"
                                        type="number"
                                        step="any"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="-6.2088"
                                        readonly
                                    />
                                    <div v-if="form.errors.latitude" class="mt-1 text-sm text-red-600">{{ form.errors.latitude }}</div>
                                </div>

                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Longitude
                                    </label>
                                    <input
                                        id="longitude"
                                        v-model="form.longitude"
                                        type="number"
                                        step="any"
                                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="106.8456"
                                        readonly
                                    />
                                    <div v-if="form.errors.longitude" class="mt-1 text-sm text-red-600">{{ form.errors.longitude }}</div>
                                </div>
                            </div>

                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Klik pada peta di sebelah kanan untuk menentukan koordinat provinsi
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-3 pt-4">
                                <Link
                                    :href="route('provinsi.index')"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-600/50"
                                >
                                    <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="stroke-current/25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="fill-current/75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ form.processing ? 'Menyimpan...' : 'Update Provinsi' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Map -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pilih Lokasi</h2>
                    <div ref="mapContainer" class="h-96 w-full rounded-lg border border-gray-300 dark:border-gray-600"></div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <strong>Petunjuk:</strong> Klik pada peta untuk menentukan koordinat pusat provinsi
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Provinsi {
    id: number
    nama: string
    latitude?: number | null
    longitude?: number | null
}

const props = defineProps<{
    provinsi: Provinsi
}>()

const form = useForm({
    nama: props.provinsi.nama,
    latitude: props.provinsi.latitude?.toString() || '',
    longitude: props.provinsi.longitude?.toString() || '',
})

// Map related refs
let map: any
let marker: any
const mapContainer = ref()

// Initialize map
const initializeMap = async () => {
    if (typeof window !== 'undefined') {
        const L = await import('leaflet')
        
        if (mapContainer.value) {
            // Initialize map centered on Indonesia
            const initialLat = props.provinsi.latitude || -2.5489
            const initialLng = props.provinsi.longitude || 118.0149
            
            map = L.map(mapContainer.value).setView([initialLat, initialLng], 6)
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(map)

            // Add existing marker if coordinates exist
            if (props.provinsi.latitude && props.provinsi.longitude) {
                marker = L.marker([props.provinsi.latitude, props.provinsi.longitude])
                    .addTo(map)
                    .bindPopup(`<strong>${props.provinsi.nama}</strong><br>Lat: ${props.provinsi.latitude}<br>Lng: ${props.provinsi.longitude}`)
            }

            // Add click event to map
            map.on('click', (e: any) => {
                const { lat, lng } = e.latlng
                
                // Update form data
                form.latitude = lat.toFixed(7)
                form.longitude = lng.toFixed(7)

                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker)
                }

                // Add new marker
                marker = L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup(`<strong>${props.provinsi.nama}</strong><br>Lat: ${lat.toFixed(7)}<br>Lng: ${lng.toFixed(7)}`)
                    .openPopup()
            })

            // Import and fix default marker icon
            delete (L.Icon.Default.prototype as any)._getIconUrl
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            })
        }
    }
}

// Submit form
const submit = () => {
    form.put(route('provinsi.update', props.provinsi.id), {
        preserveScroll: true,
    })
}

// Initialize map on mount
onMounted(() => {
    initializeMap()
})
</script>