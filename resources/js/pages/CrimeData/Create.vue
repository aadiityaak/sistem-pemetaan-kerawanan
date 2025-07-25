<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { LMap, LTileLayer, LMarker } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';

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
const provinsiList = ref<LocationData[]>([]);
const kabupatenKotaList = ref<LocationData[]>([]);
const kecamatanList = ref<LocationData[]>([]);
const selectedProvinsi = ref<LocationData | null>(null);
const selectedKabupatenKota = ref<LocationData | null>(null);

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

onMounted(() => {
    fetchProvinsi();
});

function onMapClick(event: any) {
    form.latitude = event.latlng.lat;
    form.longitude = event.latlng.lng;
}

function submit() {
    form.post('/crime-data');
}
</script>

<template>
    <Head title="Tambah Data Kriminal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-2xl font-bold">Tambah Data Kriminal</h1>
            <form @submit.prevent="submit">
                <div class="space-y-4">
                    <!-- Provinsi Dropdown -->
                    <div>
                        <label for="provinsi" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Provinsi</label>
                        <select id="provinsi" v-model="form.kode_provinsi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600">
                            <option value="">Pilih Provinsi</option>
                            <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                                {{ provinsi.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Kabupaten/Kota Dropdown -->
                    <div>
                        <label for="kabupaten_kota" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kabupaten/Kota</label>
                        <select id="kabupaten_kota" v-model="form.kode_kabupaten_kota" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600" :disabled="!form.kode_provinsi || kabupatenKotaList.length === 0">
                            <option value="">Pilih Kabupaten/Kota</option>
                            <option v-for="kabupatenKota in kabupatenKotaList" :key="kabupatenKota.id" :value="kabupatenKota.id">
                                {{ kabupatenKota.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Kecamatan Dropdown -->
                    <div>
                        <label for="kecamatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kecamatan</label>
                        <select id="kecamatan" v-model="form.kode_kecamatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600" :disabled="!form.kode_kabupaten_kota || kecamatanList.length === 0">
                            <option value="">Pilih Kecamatan</option>
                            <option v-for="kecamatan in kecamatanList" :key="kecamatan.id" :value="kecamatan.id">
                                {{ kecamatan.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Other fields -->
                    <div>
                        <label for="jenis_kriminal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kriminal</label>
                        <input type="text" id="jenis_kriminal" v-model="form.jenis_kriminal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600" />
                    </div>
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                        <textarea id="deskripsi" v-model="form.deskripsi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600"></textarea>
                    </div>

                    <!-- Map -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                        <div style="height: 400px" class="mt-1">
                            <l-map ref="map" v-model:zoom="zoom" :center="[form.latitude, form.longitude]" @click="onMapClick">
                                <l-tile-layer
                                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                    layer-type="base"
                                    name="OpenStreetMap"
                                ></l-tile-layer>
                                <l-marker :lat-lng="[form.latitude, form.longitude]"></l-marker>
                            </l-map>
                        </div>
                    </div>

                    <!-- Latitude/Longitude -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                            <input type="text" id="latitude" v-model="form.latitude" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600" readonly />
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                            <input type="text" id="longitude" v-model="form.longitude" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-800 dark:border-gray-600" readonly />
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" :disabled="form.processing">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>