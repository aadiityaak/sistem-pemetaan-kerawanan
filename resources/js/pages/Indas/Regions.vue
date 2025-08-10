<template>
  <AppLayout title="INDAS - Regions">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Manage Regions</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Add and manage kabupaten/kota regions for INDAS analysis
              </p>
            </div>
            <div class="mt-4 flex space-x-3 sm:mt-0 sm:ml-4">
              <button
                @click="showAddModal = true"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                <PlusIcon class="h-4 w-4 mr-2" />
                Add Region
              </button>
              <Link 
                :href="route('indas.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Back to Dashboard
              </Link>
            </div>
          </div>
        </div>

        <!-- Regions List -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
              Active Regions ({{ regions.length }})
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
              Regions currently configured for INDAS analysis
            </p>
          </div>
          
          <div v-if="regions.length === 0" class="text-center py-12">
            <MapPinIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No regions configured</h3>
            <p class="mt-1 text-sm text-gray-500">
              Start by adding your first region for analysis.
            </p>
            <div class="mt-6">
              <button
                @click="showAddModal = true"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                Add your first region
              </button>
            </div>
          </div>

          <ul v-else role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
            <li v-for="region in regions" :key="region.id" class="px-6 py-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <MapPinIcon class="h-8 w-8 text-indigo-500" />
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ region.nama }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ region.kabupaten_kota?.nama }}, {{ region.kabupaten_kota?.provinsi?.nama }}
                    </div>
                    <div v-if="region.description" class="text-xs text-gray-400 mt-1">
                      {{ region.description }}
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                    Active
                  </span>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Add Region Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Region</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label for="provinsi" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Province
              </label>
              <select
                id="provinsi"
                v-model="form.selectedProvinsi"
                @change="loadKabupatenKota"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
              >
                <option value="">Select Province</option>
                <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                  {{ provinsi.nama }}
                </option>
              </select>
            </div>

            <div>
              <label for="kabupaten_kota_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Kabupaten/Kota
              </label>
              <select
                id="kabupaten_kota_id"
                v-model="form.kabupaten_kota_id"
                required
                :disabled="!form.selectedProvinsi"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm disabled:opacity-50"
              >
                <option value="">Select Kabupaten/Kota</option>
                <option v-for="kabkota in kabupatenKotaList" :key="kabkota.id" :value="kabkota.id">
                  {{ kabkota.nama }}
                </option>
              </select>
            </div>

            <div>
              <label for="nama" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Region Name
              </label>
              <input
                id="nama"
                v-model="form.nama"
                type="text"
                required
                maxlength="255"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                placeholder="e.g., Klaten Analysis Region"
              />
            </div>

            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description (Optional)
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                placeholder="Brief description of this region..."
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                {{ isSubmitting ? 'Adding...' : 'Add Region' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  MapPinIcon,
  PlusIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'

interface Region {
  id: number
  nama: string
  description?: string
  kabupaten_kota: {
    nama: string
    provinsi: {
      nama: string
    }
  }
}

interface Provinsi {
  id: number
  nama: string
}

interface KabupatenKota {
  id: number
  nama: string
}

defineProps<{
  regions: Region[]
}>()

const showAddModal = ref(false)
const isSubmitting = ref(false)
const provinsiList = ref<Provinsi[]>([])
const kabupatenKotaList = ref<KabupatenKota[]>([])

const form = reactive({
  selectedProvinsi: '',
  kabupaten_kota_id: '',
  nama: '',
  description: ''
})

const closeModal = () => {
  showAddModal.value = false
  resetForm()
}

const resetForm = () => {
  form.selectedProvinsi = ''
  form.kabupaten_kota_id = ''
  form.nama = ''
  form.description = ''
  kabupatenKotaList.value = []
}

const loadProvinsi = async () => {
  try {
    const response = await fetch('/api/provinsi')
    provinsiList.value = await response.json()
  } catch (error) {
    console.error('Error loading provinces:', error)
  }
}

const loadKabupatenKota = async () => {
  if (!form.selectedProvinsi) {
    kabupatenKotaList.value = []
    form.kabupaten_kota_id = ''
    return
  }

  try {
    const response = await fetch(`/api/kabupaten-kota/${form.selectedProvinsi}`)
    kabupatenKotaList.value = await response.json()
    form.kabupaten_kota_id = '' // Reset selection
  } catch (error) {
    console.error('Error loading kabupaten/kota:', error)
  }
}

const submitForm = async () => {
  isSubmitting.value = true
  
  try {
    await router.post(route('indas.regions.store'), {
      kabupaten_kota_id: form.kabupaten_kota_id,
      nama: form.nama,
      description: form.description || null
    }, {
      onSuccess: () => {
        closeModal()
      },
      onFinish: () => {
        isSubmitting.value = false
      }
    })
  } catch (error) {
    console.error('Error adding region:', error)
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadProvinsi()
})
</script>