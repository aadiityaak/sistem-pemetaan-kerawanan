<template>
    <Head title="Edit Data Sembako" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Data Sembako</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui data harga sembako - {{ sembako.nama_komoditas }}</p>
            </div>

            <form @submit.prevent="submit">
                <div class="mx-auto max-w-2xl space-y-6">
                    <!-- Informasi Komoditas -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Komoditas</h3>
                        <div class="space-y-4">
                        <!-- Nama Komoditas -->
                        <div>
                            <label for="nama_komoditas" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nama Komoditas *
                            </label>
                            <input
                                id="nama_komoditas"
                                v-model="form.nama_komoditas"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Contoh: Beras, Gula, Minyak Goreng"
                                required
                            />
                            <div v-if="form.errors.nama_komoditas" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.nama_komoditas }}
                            </div>
                        </div>

                        <!-- Satuan -->
                        <div>
                            <label for="satuan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Satuan *
                            </label>
                            <select
                                id="satuan"
                                v-model="form.satuan"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                required
                            >
                                <option value="">Pilih Satuan</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="gram">Gram</option>
                                <option value="ons">Ons</option>
                                <option value="bungkus">Bungkus</option>
                                <option value="botol">Botol</option>
                                <option value="karung">Karung</option>
                            </select>
                            <div v-if="form.errors.satuan" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.satuan }}
                            </div>
                        </div>

                        <!-- Harga -->
                        <div>
                            <label for="harga" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Harga (Rp) *
                            </label>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input
                                    id="harga"
                                    v-model="form.harga"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="0"
                                    required
                                />
                            </div>
                            <div v-if="form.errors.harga" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.harga }}
                            </div>
                        </div>

                        <!-- Kabupaten/Kota -->
                        <div>
                            <label for="kabupaten_kota_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Kabupaten/Kota *
                            </label>
                            <select
                                id="kabupaten_kota_id"
                                v-model="form.kabupaten_kota_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                required
                            >
                                <option value="">Pilih Kabupaten/Kota</option>
                                <optgroup
                                    v-for="provinsi in groupedKabupaten"
                                    :key="provinsi.nama"
                                    :label="provinsi.nama"
                                >
                                    <option
                                        v-for="kabupaten in provinsi.kabupaten"
                                        :key="kabupaten.id"
                                        :value="kabupaten.id"
                                    >
                                        {{ kabupaten.nama }}
                                    </option>
                                </optgroup>
                            </select>
                            <div v-if="form.errors.kabupaten_kota_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.kabupaten_kota_id }}
                            </div>
                        </div>

                        <!-- Tanggal Pencatatan -->
                        <div>
                            <label for="tanggal_pencatatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tanggal Pencatatan *
                            </label>
                            <input
                                id="tanggal_pencatatan"
                                v-model="form.tanggal_pencatatan"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                required
                            />
                            <div v-if="form.errors.tanggal_pencatatan" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.tanggal_pencatatan }}
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div>
                            <label for="keterangan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Keterangan
                            </label>
                            <textarea
                                id="keterangan"
                                v-model="form.keterangan"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Keterangan tambahan (opsional)"
                            ></textarea>
                            <div v-if="form.errors.keterangan" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.keterangan }}
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <Link :href="route('sembako.index')"
                                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-blue-600/50"
                            >
                                <span v-if="form.processing">Memperbarui...</span>
                                <span v-else>Perbarui Data</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useForm, Link, Head } from '@inertiajs/vue3'
import { ArrowLeft } from 'lucide-vue-next'
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
    kabupaten_kota_id: number
    tanggal_pencatatan: string
    keterangan?: string
    kabupaten_kota: KabupatenKota
}

const props = defineProps<{
    sembako: SembakoItem
    kabupatenKota: KabupatenKota[]
}>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { label: 'Dashboard', href: '/dashboard' },
    { label: 'Data Sembako', href: '/sembako' },
    { label: 'Edit Data', href: '#' },
]

const form = useForm({
    nama_komoditas: props.sembako.nama_komoditas,
    satuan: props.sembako.satuan,
    harga: props.sembako.harga,
    kabupaten_kota_id: props.sembako.kabupaten_kota_id,
    tanggal_pencatatan: props.sembako.tanggal_pencatatan,
    keterangan: props.sembako.keterangan || '',
})

const groupedKabupaten = computed(() => {
    const grouped = props.kabupatenKota.reduce((acc, kabupaten) => {
        const provinsiNama = kabupaten.provinsi.nama
        if (!acc[provinsiNama]) {
            acc[provinsiNama] = {
                nama: provinsiNama,
                kabupaten: []
            }
        }
        acc[provinsiNama].kabupaten.push(kabupaten)
        return acc
    }, {} as Record<string, { nama: string; kabupaten: KabupatenKota[] }>)

    return Object.values(grouped).sort((a, b) => a.nama.localeCompare(b.nama))
})

const submit = () => {
    form.put(route('sembako.update', props.sembako.id))
}
</script>