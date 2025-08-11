<template>
    <AppLayout title="Detail Data Sembako">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Link :href="route('sembako.index')"
                              class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                            <ArrowLeft class="w-5 h-5" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail Data Sembako</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">{{ sembako.nama_komoditas }}</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Link :href="route('sembako.edit', sembako.id)"
                              class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 disabled:bg-yellow-600/50 transition ease-in-out duration-150">
                            <Edit class="w-4 h-4 mr-2" />
                            Edit
                        </Link>
                        <button @click="confirmDelete"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:bg-red-600/50 transition ease-in-out duration-150">
                            <Trash class="w-4 h-4 mr-2" />
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Main Info -->
                <div class="bg-white shadow-sm rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">Informasi Komoditas</h2>
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Komoditas</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ sembako.nama_komoditas }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Satuan</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ sembako.satuan }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Harga</dt>
                                <dd class="text-lg font-bold text-green-600 dark:text-green-400">{{ sembako.formatted_harga }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pencatatan</dt>
                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(sembako.tanggal_pencatatan) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Location Info -->
                <div class="bg-white shadow-sm rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">Informasi Lokasi</h2>
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Provinsi</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ sembako.kabupaten_kota.provinsi.nama }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kabupaten/Kota</dt>
                                <dd class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ sembako.kabupaten_kota.nama }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Additional Info -->
                <div v-if="sembako.keterangan" class="lg:col-span-2">
                    <div class="bg-white shadow-sm rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">Keterangan</h2>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ sembako.keterangan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Price History Card Placeholder -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-sm rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4 dark:text-gray-100">Riwayat Harga</h2>
                            <div class="text-center py-8">
                                <div class="text-gray-500 dark:text-gray-400">
                                    <TrendingUp class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                    <p class="text-sm">Riwayat harga untuk komoditas ini akan ditampilkan di sini</p>
                                    <p class="text-xs mt-2">Fitur ini akan dikembangkan lebih lanjut</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ArrowLeft, Edit, Trash, TrendingUp } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

interface KabupatenKota {
    id: number
    nama: string
    provinsi: {
        id: number
        nama: string
    }
}

interface SembakoItem {
    id: number
    nama_komoditas: string
    satuan: string
    harga: number
    formatted_harga: string
    kabupaten_kota_id: number
    tanggal_pencatatan: string
    keterangan?: string
    kabupaten_kota: KabupatenKota
}

const props = defineProps<{
    sembako: SembakoItem
}>()

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const confirmDelete = () => {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${props.sembako.nama_komoditas} di ${props.sembako.kabupaten_kota.nama}?`)) {
        router.delete(route('sembako.destroy', props.sembako.id))
    }
}
</script>