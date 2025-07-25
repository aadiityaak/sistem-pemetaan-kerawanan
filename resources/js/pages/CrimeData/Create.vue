<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { LMap, LTileLayer, LMarker } from '@vue-leaflet/vue-leaflet';
import 'leaflet/dist/leaflet.css';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
    { title: 'Tambah Data', href: '/crime-data/create' },
];

const form = useForm({
    provinsi: '',
    kabupaten_kota: '',
    kecamatan: '',
    jenis_kriminal: '',
    deskripsi: '',
    latitude: -6.2088,
    longitude: 106.8456,
});

const zoom = ref(13);

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
                    <div>
                        <label for="provinsi" class="block text-sm font-medium text-gray-700">Provinsi</label>
                        <input type="text" id="provinsi" v-model="form.provinsi" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <label for="kabupaten_kota" class="block text-sm font-medium text-gray-700">Kabupaten/Kota</label>
                        <input type="text" id="kabupaten_kota" v-model="form.kabupaten_kota" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <label for="kecamatan" class="block text-sm font-medium text-gray-700">Kecamatan</label>
                        <input type="text" id="kecamatan" v-model="form.kecamatan" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <label for="jenis_kriminal" class="block text-sm font-medium text-gray-700">Jenis Kriminal</label>
                        <input type="text" id="jenis_kriminal" v-model="form.jenis_kriminal" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea id="deskripsi" v-model="form.deskripsi" class="mt-1 block w-full"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                        <div style="height: 400px">
                            <ClientOnly>
                                <l-map ref="map" v-model:zoom="zoom" :center="[form.latitude, form.longitude]" @click="onMapClick">
                                    <l-tile-layer
                                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                        layer-type="base"
                                        name="OpenStreetMap"
                                    ></l-tile-layer>
                                    <l-marker :lat-lng="[form.latitude, form.longitude]"></l-marker>
                                </l-map>
                            </ClientOnly>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                            <input type="text" id="latitude" v-model="form.latitude" class="mt-1 block w-full" readonly />
                        </div>
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                            <input type="text" id="longitude" v-model="form.longitude" class="mt-1 block w-full" readonly />
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
