<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ mode: 'create' | 'edit', crime?: any }>();

const form = useForm({
    kode_provinsi: props.crime?.kode_provinsi || '',
    kode_kabupaten_kota: props.crime?.kode_kabupaten_kota || '',
    kode_kecamatan: props.crime?.kode_kecamatan || '',
    jenis_kriminal: props.crime?.jenis_kriminal || '',
    deskripsi: props.crime?.deskripsi || '',
    latitude: props.crime?.latitude || '',
    longitude: props.crime?.longitude || '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
    { title: props.mode === 'create' ? 'Tambah' : 'Edit', href: '#' },
];

function submit() {
    if (props.mode === 'create') {
        form.post('/crime-data');
    } else {
        form.put(`/crime-data/${props.crime.id}`);
    }
}

// Leaflet map
let map: any;
let marker: any;
const mapContainer = ref();

onMounted(() => {
    const L = (window as any).L;
    if (L) {
        map = L.map(mapContainer.value).setView([
            form.latitude || -2.5489,
            form.longitude || 118.0149
        ], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        marker = L.marker([
            form.latitude || -2.5489,
            form.longitude || 118.0149
        ], { draggable: true }).addTo(map);
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
            <input v-model="form.kode_provinsi" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kabupaten/Kota</label>
            <input v-model="form.kode_kabupaten_kota" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kecamatan</label>
            <input v-model="form.kode_kecamatan" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Jenis Kriminal</label>
            <input v-model="form.jenis_kriminal" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required />
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
  