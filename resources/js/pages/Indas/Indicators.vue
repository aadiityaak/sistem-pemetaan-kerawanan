<template>
  <AppLayout title="INDAS - Indicator Types">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Indicator Types</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Configure economic, tourism, and social indicators with their weights
              </p>
            </div>
            <div class="mt-4 flex space-x-3 sm:mt-0 sm:ml-4">
              <button
                @click="showAddModal = true"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                <PlusIcon class="h-4 w-4 mr-2" />
                Add Indicator
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

        <!-- Indicators by Category -->
        <div class="space-y-8">
          <!-- Economic Indicators -->
          <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <CurrencyDollarIcon class="h-6 w-6 text-green-500 mr-3" />
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Economic Indicators
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ (indicators.ekonomi || []).length }} indicators configured
                  </p>
                </div>
              </div>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <div v-if="!(indicators.ekonomi || []).length" class="px-6 py-8 text-center">
                <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No economic indicators</h3>
                <p class="mt-1 text-sm text-gray-500">Start by adding your first economic indicator.</p>
              </div>
              
              <div v-for="indicator in indicators.ekonomi || []" :key="indicator.id" class="px-6 py-4">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ indicator.name }}
                      </h4>
                      <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                          Weight: {{ indicator.weight_factor }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                          {{ indicator.unit }}
                        </span>
                        <button
                          @click="confirmDelete(indicator)"
                          class="p-1 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors"
                          title="Delete indicator"
                        >
                          <TrashIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                    <p v-if="indicator.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      {{ indicator.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tourism Indicators -->
          <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <BuildingOffice2Icon class="h-6 w-6 text-blue-500 mr-3" />
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Tourism Indicators
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ (indicators.pariwisata || []).length }} indicators configured
                  </p>
                </div>
              </div>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <div v-if="!(indicators.pariwisata || []).length" class="px-6 py-8 text-center">
                <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tourism indicators</h3>
                <p class="mt-1 text-sm text-gray-500">Start by adding your first tourism indicator.</p>
              </div>
              
              <div v-for="indicator in indicators.pariwisata || []" :key="indicator.id" class="px-6 py-4">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ indicator.name }}
                      </h4>
                      <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                          Weight: {{ indicator.weight_factor }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                          {{ indicator.unit }}
                        </span>
                        <button
                          @click="confirmDelete(indicator)"
                          class="p-1 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors"
                          title="Delete indicator"
                        >
                          <TrashIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                    <p v-if="indicator.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      {{ indicator.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Social Indicators -->
          <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center">
                <UsersIcon class="h-6 w-6 text-purple-500 mr-3" />
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Social Indicators
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ (indicators.sosial || []).length }} indicators configured
                  </p>
                </div>
              </div>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
              <div v-if="!(indicators.sosial || []).length" class="px-6 py-8 text-center">
                <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No social indicators</h3>
                <p class="mt-1 text-sm text-gray-500">Start by adding your first social indicator.</p>
              </div>
              
              <div v-for="indicator in indicators.sosial || []" :key="indicator.id" class="px-6 py-4">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ indicator.name }}
                      </h4>
                      <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                          Weight: {{ indicator.weight_factor }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400">
                          {{ indicator.unit }}
                        </span>
                        <button
                          @click="confirmDelete(indicator)"
                          class="p-1 text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors"
                          title="Delete indicator"
                        >
                          <TrashIcon class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                    <p v-if="indicator.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                      {{ indicator.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Indicator Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Indicator</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Category
              </label>
              <select
                id="category"
                v-model="form.category"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
              >
                <option value="">Select Category</option>
                <option value="ekonomi">Economic</option>
                <option value="pariwisata">Tourism</option>
                <option value="sosial">Social</option>
              </select>
            </div>

            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Indicator Name
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                maxlength="255"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                placeholder="e.g., Number of Shops, Tourist Visits"
              />
            </div>

            <div>
              <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Unit of Measurement
              </label>
              <input
                id="unit"
                v-model="form.unit"
                type="text"
                required
                maxlength="50"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                placeholder="e.g., units, visitors, %"
              />
            </div>

            <div>
              <label for="weight_factor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Weight Factor (0.0 - 1.0)
              </label>
              <input
                id="weight_factor"
                v-model="form.weight_factor"
                type="number"
                step="0.01"
                min="0"
                max="1"
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                placeholder="e.g., 0.4, 0.3, 0.5"
              />
              <p class="mt-1 text-xs text-gray-500">
                Higher values = more important in score calculation
              </p>
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
                placeholder="Brief description of this indicator..."
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
                {{ isSubmitting ? 'Adding...' : 'Add Indicator' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal && indicatorToDelete" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeDeleteModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Indicator</h3>
            <button @click="closeDeleteModal" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-5 w-5" />
            </button>
          </div>
          
          <div class="mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
              Are you sure you want to delete this indicator?
            </p>
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
              <h4 class="font-medium text-gray-900 dark:text-white">{{ indicatorToDelete.name }}</h4>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Category: {{ indicatorToDelete.category }} • Weight: {{ indicatorToDelete.weight_factor }} • Unit: {{ indicatorToDelete.unit }}
              </p>
            </div>
            <p class="text-xs text-red-600 dark:text-red-400 mt-2">
              <strong>Warning:</strong> This action cannot be undone. The indicator will be permanently deleted.
            </p>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeDeleteModal"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
            >
              Cancel
            </button>
            <button
              @click="deleteIndicator"
              :disabled="isDeleting"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
            >
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  CurrencyDollarIcon,
  BuildingOffice2Icon,
  UsersIcon,
  PlusIcon,
  XMarkIcon,
  ChartBarIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'

interface Indicator {
  id: number
  name: string
  category: string
  unit: string
  weight_factor: number
  description?: string
}

defineProps<{
  indicators: {
    ekonomi?: Indicator[]
    pariwisata?: Indicator[]
    sosial?: Indicator[]
  }
}>()

const showAddModal = ref(false)
const showDeleteModal = ref(false)
const isSubmitting = ref(false)
const isDeleting = ref(false)
const indicatorToDelete = ref<Indicator | null>(null)

const form = reactive({
  category: '',
  name: '',
  unit: '',
  weight_factor: '',
  description: ''
})

const closeModal = () => {
  showAddModal.value = false
  resetForm()
}

const resetForm = () => {
  form.category = ''
  form.name = ''
  form.unit = ''
  form.weight_factor = ''
  form.description = ''
}

const submitForm = async () => {
  isSubmitting.value = true
  
  try {
    await router.post(route('indas.indicators.store'), {
      category: form.category,
      name: form.name,
      unit: form.unit,
      weight_factor: parseFloat(form.weight_factor),
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
    console.error('Error adding indicator:', error)
    isSubmitting.value = false
  }
}

const confirmDelete = (indicator: Indicator) => {
  indicatorToDelete.value = indicator
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  indicatorToDelete.value = null
}

const deleteIndicator = async () => {
  if (!indicatorToDelete.value) return
  
  isDeleting.value = true
  
  try {
    await router.delete(route('indas.indicators.delete', indicatorToDelete.value.id), {
      onSuccess: () => {
        closeDeleteModal()
      },
      onFinish: () => {
        isDeleting.value = false
      }
    })
  } catch (error) {
    console.error('Error deleting indicator:', error)
    isDeleting.value = false
  }
}
</script>