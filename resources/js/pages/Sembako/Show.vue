<template>
    <Head title="Detail Data Sembako" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Data Sembako</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ sembako.nama_komoditas }}</p>
                </div>
                <div class="flex space-x-3">
                    <Link :href="route('sembako.index')"
                          class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Link>
                    <Link :href="route('sembako.edit', sembako.id)"
                          class="inline-flex items-center rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                    <button @click="confirmDelete"
                            class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <Trash class="mr-2 h-4 w-4" />
                        Hapus
                    </button>
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
import { Link, router, Head } from '@inertiajs/vue3'
import { ArrowLeft, Edit, Trash, TrendingUp } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

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

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', url: '/dashboard' },
    { title: 'Data Sembako', url: '/sembako' },
    { title: 'Detail Data', url: '#' },
]

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