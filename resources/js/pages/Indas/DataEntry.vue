<template>
  <AppLayout title="INDAS - Data Entry">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Entry</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Enter monthly indicator values for {{ months[currentMonth - 1] }} {{ currentYear }}
              </p>
            </div>
            <div class="mt-4 flex space-x-3 sm:mt-0 sm:ml-4">
              <select 
                v-model="selectedMonth" 
                @change="updatePeriod"
                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option v-for="(month, index) in months" :key="index" :value="index + 1">
                  {{ month }}
                </option>
              </select>
              <select 
                v-model="selectedYear" 
                @change="updatePeriod"
                class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
              >
                <option v-for="year in years" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
              <button
                @click="calculateAll"
                :disabled="isCalculating"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
              >
                <CalculatorIcon v-if="!isCalculating" class="h-4 w-4 mr-2" />
                <ArrowPathIcon v-else class="h-4 w-4 mr-2 animate-spin" />
                {{ isCalculating ? 'Calculating...' : 'Calculate All' }}
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

        <!-- Data Entry Forms -->
        <div class="space-y-8">
          <div v-for="kabkota in kabupatenKota" :key="kabkota.id" class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ kabkota.nama }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ kabkota.provinsi.nama }}
              </p>
            </div>

            <div class="p-6">
              <!-- Economic Indicators -->
              <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                  <CurrencyDollarIcon class="h-5 w-5 text-green-500 mr-2" />
                  Economic Indicators
                </h4>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="indicator in indicators.ekonomi || []" :key="`${kabkota.id}-${indicator.id}`">
                    <label :for="`${kabkota.id}-${indicator.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ indicator.name }}
                      <span class="text-gray-500">{{ indicator.unit ? `(${indicator.unit})` : '' }}</span>
                      <span class="text-xs text-gray-400 ml-1">Weight: {{ indicator.weight_factor }}</span>
                    </label>
                    <div class="mt-1 relative">
                      <input
                        :id="`${kabkota.id}-${indicator.id}`"
                        type="number"
                        step="0.01"
                        min="0"
                        :value="getExistingValue(kabkota.id, indicator.id)"
                        @input="updateValue(kabkota.id, indicator.id, ($event.target as HTMLInputElement).value)"
                        @blur="saveValue(kabkota.id, indicator.id)"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        :placeholder="`Enter ${indicator.name.toLowerCase()}`"
                      />
                    </div>
                    <p v-if="indicator.description" class="mt-1 text-xs text-gray-500">
                      {{ indicator.description }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Tourism Indicators -->
              <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                  <BuildingOffice2Icon class="h-5 w-5 text-blue-500 mr-2" />
                  Tourism Indicators
                </h4>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="indicator in indicators.pariwisata || []" :key="`${kabkota.id}-${indicator.id}`">
                    <label :for="`${kabkota.id}-${indicator.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ indicator.name }}
                      <span class="text-gray-500">{{ indicator.unit ? `(${indicator.unit})` : '' }}</span>
                      <span class="text-xs text-gray-400 ml-1">Weight: {{ indicator.weight_factor }}</span>
                    </label>
                    <div class="mt-1 relative">
                      <input
                        :id="`${kabkota.id}-${indicator.id}`"
                        type="number"
                        step="0.01"
                        min="0"
                        :value="getExistingValue(kabkota.id, indicator.id)"
                        @input="updateValue(kabkota.id, indicator.id, ($event.target as HTMLInputElement).value)"
                        @blur="saveValue(kabkota.id, indicator.id)"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        :placeholder="`Enter ${indicator.name.toLowerCase()}`"
                      />
                    </div>
                    <p v-if="indicator.description" class="mt-1 text-xs text-gray-500">
                      {{ indicator.description }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Social Indicators -->
              <div class="mb-8">
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                  <UsersIcon class="h-5 w-5 text-purple-500 mr-2" />
                  Social Indicators
                </h4>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                  <div v-for="indicator in indicators.sosial || []" :key="`${kabkota.id}-${indicator.id}`">
                    <label :for="`${kabkota.id}-${indicator.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ indicator.name }}
                      <span class="text-gray-500">{{ indicator.unit ? `(${indicator.unit})` : '' }}</span>
                      <span class="text-xs text-gray-400 ml-1">Weight: {{ indicator.weight_factor }}</span>
                    </label>
                    <div class="mt-1 relative">
                      <input
                        :id="`${kabkota.id}-${indicator.id}`"
                        type="number"
                        step="0.01"
                        min="0"
                        :value="getExistingValue(kabkota.id, indicator.id)"
                        @input="updateValue(kabkota.id, indicator.id, ($event.target as HTMLInputElement).value)"
                        @blur="saveValue(kabkota.id, indicator.id)"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        :placeholder="`Enter ${indicator.name.toLowerCase()}`"
                      />
                    </div>
                    <p v-if="indicator.description" class="mt-1 text-xs text-gray-500">
                      {{ indicator.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="kabupatenKota.length === 0" class="text-center py-12">
          <MapPinIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No kabupaten/kota available</h3>
          <p class="mt-1 text-sm text-gray-500">
            Contact admin to configure kabupaten/kota data.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  CurrencyDollarIcon, 
  BuildingOffice2Icon,
  UsersIcon,
  MapPinIcon,
  PlusIcon,
  CalculatorIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'

interface KabupatenKota {
  id: number
  nama: string
  provinsi: {
    nama: string
  }
}

interface Indicator {
  id: number
  name: string
  category: string
  unit: string
  weight_factor: number
  description?: string
}

interface ExistingData {
  [regionId: number]: {
    [indicatorId: number]: {
      value: number
      notes?: string
    }
  }
}

const props = defineProps<{
  kabupatenKota: KabupatenKota[]
  indicators: {
    ekonomi?: Indicator[]
    pariwisata?: Indicator[]
    sosial?: Indicator[]
  }
  currentMonth: number
  currentYear: number
  existingData: ExistingData
}>()

const selectedMonth = ref(props.currentMonth)
const selectedYear = ref(props.currentYear)
const isCalculating = ref(false)

// Local state for form values
const formData = reactive<{[key: string]: string}>({})

const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

const years = computed(() => {
  const currentYear = new Date().getFullYear()
  const startYear = 2020
  const yearsList = []
  for (let year = startYear; year <= currentYear + 2; year++) {
    yearsList.push(year)
  }
  return yearsList
})

const updatePeriod = () => {
  router.get(route('indas.data-entry'), {
    month: selectedMonth.value,
    year: selectedYear.value
  }, {
    preserveState: false
  })
}

const getDataKey = (kabupatenKotaId: number, indicatorId: number) => {
  return `${kabupatenKotaId}-${indicatorId}`
}

const getExistingValue = (kabupatenKotaId: number, indicatorId: number) => {
  const key = getDataKey(kabupatenKotaId, indicatorId)
  
  // Check local form data first
  if (formData[key] !== undefined) {
    return formData[key]
  }
  
  // Check existing data from server
  const existing = props.existingData[kabupatenKotaId]?.[indicatorId]
  return existing ? existing.value.toString() : ''
}

const updateValue = (kabupatenKotaId: number, indicatorId: number, value: string) => {
  const key = getDataKey(kabupatenKotaId, indicatorId)
  formData[key] = value
}

const saveValue = async (kabupatenKotaId: number, indicatorId: number) => {
  const key = getDataKey(kabupatenKotaId, indicatorId)
  const value = formData[key]
  
  if (!value || parseFloat(value) < 0) {
    return
  }
  
  try {
    await router.post(route('indas.data.store'), {
      kabupaten_kota_id: kabupatenKotaId,
      indicator_type_id: indicatorId,
      value: parseFloat(value),
      month: selectedMonth.value,
      year: selectedYear.value,
    }, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        // Data saved successfully - the backend will trigger recalculation
      }
    })
  } catch (error) {
    console.error('Error saving data:', error)
  }
}

const calculateAll = async () => {
  isCalculating.value = true
  
  try {
    await router.post(route('indas.calculate'), {
      month: selectedMonth.value,
      year: selectedYear.value,
    }, {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        isCalculating.value = false
      }
    })
  } catch (error) {
    console.error('Error calculating scores:', error)
    isCalculating.value = false
  }
}
</script>