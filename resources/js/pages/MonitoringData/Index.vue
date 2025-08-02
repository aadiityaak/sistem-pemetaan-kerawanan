<template>
  <AppLayout title="Data Monitoring">
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Data Monitoring</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola dan pantau semua data monitoring kejahatan</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Data</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.total || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Aktif</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.active || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
              <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Selesai</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.completed || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 dark:bg-red-900 rounded-lg">
              <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Level Kritis</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.critical || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 mb-6">
        <div class="p-6">
          <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari data monitoring..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <!-- Filter Status -->
            <div class="sm:w-48">
              <select
                v-model="selectedStatus"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="selesai">Selesai</option>
                <option value="dalam_proses">Dalam Proses</option>
              </select>
            </div>

            <!-- Filter Level -->
            <div class="sm:w-48">
              <select
                v-model="selectedLevel"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Semua Level</option>
                <option value="rendah">Rendah</option>
                <option value="sedang">Sedang</option>
                <option value="tinggi">Tinggi</option>
                <option value="kritis">Kritis</option>
              </select>
            </div>

            <!-- Add Button -->
            <div class="sm:w-auto">
              <Link
                :href="route('monitoring-data.create')"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tambah Data
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Data Monitoring</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Lokasi
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Kategori
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Tanggal Laporan
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Level
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Korban
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="monitoringData.data.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  Tidak ada data yang ditemukan
                </td>
              </tr>
              <tr v-else v-for="data in monitoringData.data" :key="data.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ data.kecamatan?.nama || data.kecamatan?.name || 'N/A' }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ data.kecamatan?.kabupaten_kota?.nama || data.kecamatan?.kabupaten_kota?.name || 'N/A' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    {{ data.category.name }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400" v-if="data.subCategory">
                    {{ data.subCategory.name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ formatDate(data.tanggal_laporan) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getLevelBadgeClass(data.level_kejadian)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ data.level_kejadian }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusBadgeClass(data.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ data.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ data.jumlah_korban || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <Link
                      :href="route('monitoring-data.show', data.id)"
                      class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                      title="Lihat Detail"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </Link>
                    <Link
                      :href="route('monitoring-data.edit', data.id)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      title="Edit"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </Link>
                    <button
                      @click="confirmDelete(data)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      title="Hapus"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6" v-if="monitoringData.last_page > 1">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link
                v-if="monitoringData.prev_page_url"
                :href="monitoringData.prev_page_url"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                Previous
              </Link>
              <Link
                v-if="monitoringData.next_page_url"
                :href="monitoringData.next_page_url"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                Next
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  Showing
                  <span class="font-medium">{{ monitoringData.from }}</span>
                  to
                  <span class="font-medium">{{ monitoringData.to }}</span>
                  of
                  <span class="font-medium">{{ monitoringData.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <!-- Previous Page Link -->
                  <Link
                    v-if="monitoringData.prev_page_url"
                    :href="monitoringData.prev_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600"
                  >
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </Link>

                  <!-- Page Numbers -->
                  <template v-for="link in monitoringData.links" :key="link.label">
                    <Link
                      v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                      :href="link.url"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active
                          ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 text-blue-600 dark:text-blue-400'
                          : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600'
                      ]"
                    >
                      {{ link.label }}
                    </Link>
                    <span
                      v-else-if="!link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                      {{ link.label }}
                    </span>
                  </template>

                  <!-- Next Page Link -->
                  <Link
                    v-if="monitoringData.next_page_url"
                    :href="monitoringData.next_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600"
                  >
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </Link>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                  Hapus Data Monitoring
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus data monitoring ini? Tindakan ini tidak dapat dibatalkan.
                  </p>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="deleteData"
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Hapus
            </button>
            <button
              @click="closeDeleteModal"
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Batal
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// Define types
interface Category {
  id: number
  name: string
}

interface SubCategory {
  id: number
  name: string
}

interface KabupatenKota {
  id: number
  name?: string
  nama?: string
}

interface Kecamatan {
  id: number
  name?: string
  nama?: string
  kabupaten_kota: KabupatenKota
}

interface MonitoringDataItem {
  id: number
  category: Category
  subCategory?: SubCategory
  kecamatan: Kecamatan
  tanggal_laporan: string
  level_kejadian: string
  status: string
  jumlah_korban?: number
}

interface PaginatedData {
  data: MonitoringDataItem[]
  current_page: number
  last_page: number
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

interface Statistics {
  total: number
  active: number
  completed: number
  critical: number
}

// Props
const props = defineProps<{
  monitoringData: PaginatedData
  statistics: Statistics
  filters: {
    search?: string
    status?: string
    level?: string
  }
}>()

// Reactive data
const searchQuery = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')
const selectedLevel = ref(props.filters.level || '')
const showDeleteModal = ref(false)
const dataToDelete = ref<MonitoringDataItem | null>(null)
const searchTimeout = ref<number | null>(null)

// Watch for filter changes with debounce
watch([searchQuery, selectedStatus, selectedLevel], () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  
  searchTimeout.value = setTimeout(() => {
    const params: Record<string, any> = {}
    
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedStatus.value) params.status = selectedStatus.value
    if (selectedLevel.value) params.level = selectedLevel.value
    
    router.get(route('monitoring-data.index'), params, {
      preserveState: true,
      replace: true
    })
  }, 300)
})

// Methods
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getLevelBadgeClass = (level: string) => {
  const classes = {
    'rendah': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'sedang': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'tinggi': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'kritis': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
  }
  return classes[level as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const getStatusBadgeClass = (status: string) => {
  const classes = {
    'aktif': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'selesai': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'dalam_proses': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
  }
  return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

const confirmDelete = (data: MonitoringDataItem) => {
  dataToDelete.value = data
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  dataToDelete.value = null
}

const deleteData = () => {
  if (dataToDelete.value) {
    router.delete(route('monitoring-data.destroy', dataToDelete.value.id), {
      onSuccess: () => {
        closeDeleteModal()
      }
    })
  }
}
</script>
