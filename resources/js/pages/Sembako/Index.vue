<template>
    <AppLayout title="Data Sembako">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Data Harga Sembako</h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola data harga sembako di seluruh kabupaten/kota</p>
                </div>
                <Link :href="route('sembako.create')"
                      class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-blue-600/50 transition ease-in-out duration-150">
                    <Plus class="w-4 h-4 mr-2" />
                    Tambah Data
                </Link>
            </div>

            <!-- Filters -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Komoditas</label>
                    <input
                        type="text"
                        id="search"
                        v-model="searchQuery"
                        placeholder="Nama komoditas..."
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
                <div>
                    <label for="kabupaten" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kabupaten/Kota</label>
                    <select
                        id="kabupaten"
                        v-model="selectedKabupaten"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    >
                        <option value="">Semua Kabupaten/Kota</option>
                        <option
                            v-for="kabupaten in kabupatenKota"
                            :key="kabupaten.id"
                            :value="kabupaten.id"
                        >
                            {{ kabupaten.nama }} - {{ kabupaten.provinsi.nama }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                    <input
                        type="date"
                        id="tanggal"
                        v-model="selectedDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>
            </div>

            <!-- Data Table -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Komoditas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Satuan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Harga
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Kabupaten/Kota
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="item in filteredSembako" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ item.nama_komoditas }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.satuan }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600 dark:text-green-400">
                                    {{ item.formatted_harga }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="max-w-xs">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ item.kabupaten_kota.nama }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.kabupaten_kota.provinsi.nama }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(item.tanggal_pencatatan) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <Link :href="route('sembako.show', item.id)"
                                              class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                            <Eye class="w-4 h-4" />
                                        </Link>
                                        <Link :href="route('sembako.edit', item.id)"
                                              class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                        <button @click="confirmDelete(item)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <Trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <Link v-if="sembako.prev_page_url"
                              :href="sembako.prev_page_url"
                              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Previous
                        </Link>
                        <Link v-if="sembako.next_page_url"
                              :href="sembako.next_page_url"
                              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ sembako.from }} to {{ sembako.to }} of {{ sembako.total }} results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link v-for="link in sembako.links"
                                      :key="link.label"
                                      :href="link.url"
                                      v-html="link.label"
                                      :class="[
                                          'relative inline-flex items-center px-2 py-2 border text-sm font-medium',
                                          link.active
                                              ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-200'
                                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700'
                                      ]"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Plus, Eye, Edit, Trash } from 'lucide-vue-next'
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

interface PaginatedSembako {
    data: SembakoItem[]
    current_page: number
    from: number
    to: number
    total: number
    prev_page_url?: string
    next_page_url?: string
    links: Array<{
        url?: string
        label: string
        active: boolean
    }>
}

const props = defineProps<{
    sembako: PaginatedSembako
    kabupatenKota: KabupatenKota[]
}>()

// Filters
const searchQuery = ref('')
const selectedKabupaten = ref('')
const selectedDate = ref('')

// Computed filtered data
const filteredSembako = computed(() => {
    let filtered = props.sembako.data

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(item => 
            item.nama_komoditas.toLowerCase().includes(query)
        )
    }

    if (selectedKabupaten.value) {
        filtered = filtered.filter(item => 
            item.kabupaten_kota_id == parseInt(selectedKabupaten.value)
        )
    }

    if (selectedDate.value) {
        filtered = filtered.filter(item => 
            item.tanggal_pencatatan === selectedDate.value
        )
    }

    return filtered
})

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const confirmDelete = (item: SembakoItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${item.nama_komoditas} di ${item.kabupaten_kota.nama}?`)) {
        router.delete(route('sembako.destroy', item.id))
    }
}
</script>