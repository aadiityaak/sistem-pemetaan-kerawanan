<template>
  <AppLayout title="INDAS - Input Data">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Input Data</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Masukkan nilai indikator bulanan untuk analisis INDAS
              </p>
            </div>
            <div class="mt-4 sm:mt-0">
              <Link 
                :href="route('indas.index')"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Back to Dashboard
              </Link>
            </div>
          </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-8">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Filter</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Pilih periode dan wilayah untuk input data
            </p>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Kabupaten/Kota Search Selector -->
              <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Kabupaten/Kota
                </label>
                <div class="relative" ref="dropdownRef">
                  <div 
                    @click="toggleDropdown"
                    class="relative w-full cursor-pointer rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 py-2 pl-3 pr-10 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                  >
                    <span class="block truncate text-gray-900 dark:text-white">
                      {{ selectedKabupatenKotaName || 'Select Kabupaten/Kota' }}
                    </span>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                      <ChevronDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </span>
                  </div>

                  <div 
                    v-if="showDropdown"
                    class="absolute z-10 mt-1 w-full rounded-md bg-white dark:bg-gray-700 shadow-lg ring-1 ring-black/5 dark:ring-white/10"
                  >
                    <div class="p-2">
                      <div class="relative">
                        <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-gray-400" />
                        <input
                          v-model="searchQuery"
                          type="text"
                          placeholder="Search kabupaten/kota..."
                          class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2 pl-10 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                      </div>
                    </div>
                    
                    <div class="max-h-60 overflow-auto py-1">
                      <div 
                        v-for="kabkota in filteredKabupatenKota" 
                        :key="kabkota.id"
                        @click="selectKabupatenKota(kabkota.id)"
                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-600"
                        :class="{
                          'bg-indigo-600 text-white': selectedKabupatenKotaId === kabkota.id,
                          'text-gray-900 dark:text-white': selectedKabupatenKotaId !== kabkota.id
                        }"
                      >
                        <div class="flex items-center">
                          <span class="font-normal block truncate">
                            {{ kabkota.nama }}
                          </span>
                          <span class="text-gray-500 ml-2 text-sm truncate">
                            {{ kabkota.provinsi.nama }}
                          </span>
                        </div>
                      </div>
                      
                      <div v-if="filteredKabupatenKota.length === 0" class="relative cursor-default select-none py-2 px-4 text-gray-700 dark:text-gray-300">
                        No results found.
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Month Selector -->
              <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Month
                </label>
                <select 
                  v-model="selectedMonth" 
                  @change="updatePeriod"
                  class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-900 dark:text-white"
                >
                  <option v-for="(month, index) in months" :key="index" :value="index + 1">
                    {{ month }}
                  </option>
                </select>
              </div>

              <!-- Year Selector -->
              <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Year
                </label>
                <select 
                  v-model="selectedYear" 
                  @change="updatePeriod"
                  class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm text-gray-900 dark:text-white"
                >
                  <option v-for="year in years" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
              </div>

              <!-- Info -->
              <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Info
                </label>
                <div class="w-full px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md text-xs text-blue-700 dark:text-blue-300">
                  <p class="font-medium">📊 Auto-calculation</p>
                  <p class="mt-1">Skor INDAS akan dihitung otomatis setelah data disimpan</p>
                </div>
              </div>
            </div>

            <!-- Current Selection Display -->
            <div v-if="selectedKabupatenKota" class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-md">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <MapPinIcon class="h-5 w-5 text-blue-400" />
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Input data untuk: {{ selectedKabupatenKota.nama }}, {{ selectedKabupatenKota.provinsi.nama }}
                  </p>
                  <p class="text-xs text-blue-600 dark:text-blue-300">
                    Period: {{ months[selectedMonth - 1] }} {{ selectedYear }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Data Entry Forms -->
        <div v-if="selectedKabupatenKota" class="space-y-8">
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ selectedKabupatenKota.nama }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ selectedKabupatenKota.provinsi.nama }}
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
                  <div v-for="indicator in indicators.ekonomi || []" :key="`${selectedKabupatenKota.id}-${indicator.id}`">
                    <label :for="`${selectedKabupatenKota.id}-${indicator.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ indicator.name }}
                      <span class="text-gray-500">{{ indicator.unit ? `(${indicator.unit})` : '' }}</span>
                    </label>
                    <div class="mt-1 relative">
                      <input
                        :id="`${selectedKabupatenKota.id}-${indicator.id}`"
                        type="number"
                        step="0.01"
                        min="0"
                        :value="getExistingValue(selectedKabupatenKota.id, indicator.id)"
                        @input="updateValue(selectedKabupatenKota.id, indicator.id, ($event.target as HTMLInputElement).value)"
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
                  <div v-for="indicator in indicators.pariwisata || []" :key="`${selectedKabupatenKota.id}-${indicator.id}`">
                    <label :for="`${selectedKabupatenKota.id}-${indicator.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ indicator.name }}
                      <span class="text-gray-500">{{ indicator.unit ? `(${indicator.unit})` : '' }}</span>
                    </label>
                    <div class="mt-1 relative">
                      <input
                        :id="`${selectedKabupatenKota.id}-${indicator.id}`"
                        type="number"
                        step="0.01"
                        min="0"
                        :value="getExistingValue(selectedKabupatenKota.id, indicator.id)"
                        @input="updateValue(selectedKabupatenKota.id, indicator.id, ($event.target as HTMLInputElement).value)"
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
                  <div v-for="indicator in indicators.sosial || []" :key="`${selectedKabupatenKota.id}-${indicator.id}`">
                    <label :for="`${selectedKabupatenKota.id}-${indicator.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                      {{ indicator.name }}
                      <span class="text-gray-500">{{ indicator.unit ? `(${indicator.unit})` : '' }}</span>
                    </label>
                    <div class="mt-1 relative">
                      <input
                        :id="`${selectedKabupatenKota.id}-${indicator.id}`"
                        type="number"
                        step="0.01"
                        min="0"
                        :value="getExistingValue(selectedKabupatenKota.id, indicator.id)"
                        @input="updateValue(selectedKabupatenKota.id, indicator.id, ($event.target as HTMLInputElement).value)"
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
              
              <!-- Save Button Section -->
              <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p class="mb-1">Pastikan semua data sudah diisi dengan benar</p>
                    <p class="text-xs">Data akan disimpan dan skor INDAS akan dihitung ulang secara otomatis</p>
                  </div>
                  <div class="flex items-center space-x-3">
                    <Link 
                      :href="route('indas.index')"
                      class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                      Batal
                    </Link>
                    <button
                      @click="saveAllData"
                      :disabled="isSaving || !selectedKabupatenKota || !hasChanges"
                      class="inline-flex items-center px-6 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                    >
                      <ArrowPathIcon v-if="isSaving" class="h-4 w-4 mr-2 animate-spin" />
                      <CheckIcon v-else class="h-4 w-4 mr-2" />
                      {{ isSaving ? 'Menyimpan...' : 'Simpan Data' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="availableKabupatenKota.length === 0" class="text-center py-12">
          <MapPinIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No kabupaten/kota available</h3>
          <p class="mt-1 text-sm text-gray-500">
            Contact admin to configure kabupaten/kota data.
          </p>
        </div>

        <!-- No Selection State -->
        <div v-else-if="availableKabupatenKota.length > 0 && !selectedKabupatenKota" class="text-center py-12">
          <MapPinIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Select a Kabupaten/Kota</h3>
          <p class="mt-1 text-sm text-gray-500">
            Please select a kabupaten/kota from the dropdown above to start entering data.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  CurrencyDollarIcon, 
  BuildingOffice2Icon,
  UsersIcon,
  MapPinIcon,
  PlusIcon,
  CalculatorIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
  ChevronDownIcon,
  XMarkIcon,
  CheckIcon
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
  availableKabupatenKota: KabupatenKota[]
  selectedKabupatenKota: KabupatenKota | null
  indicators: {
    ekonomi?: Indicator[]
    pariwisata?: Indicator[]
    sosial?: Indicator[]
  }
  currentMonth: number
  currentYear: number
  selectedKabupatenKotaId: number | null
  existingData: ExistingData
}>()

const selectedMonth = ref(props.currentMonth)
const selectedYear = ref(props.currentYear)
const selectedKabupatenKotaId = ref(props.selectedKabupatenKotaId)
const isCalculating = ref(false)
const isSaving = ref(false)
const searchQuery = ref('')
const showDropdown = ref(false)

// Local state for form values
const formData = reactive<{[key: string]: string}>({})

// Track if there are unsaved changes
const hasChanges = computed(() => {
  return Object.keys(formData).length > 0
})

// Filtered kabupaten/kota based on search
const filteredKabupatenKota = computed(() => {
  if (!searchQuery.value) {
    return props.availableKabupatenKota
  }
  
  return props.availableKabupatenKota.filter(kabkota => 
    kabkota.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    kabkota.provinsi.nama.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Get selected kabupaten/kota name for display
const selectedKabupatenKotaName = computed(() => {
  if (!selectedKabupatenKotaId.value) return ''
  const selected = props.availableKabupatenKota.find(k => k.id === selectedKabupatenKotaId.value)
  return selected ? `${selected.nama} (${selected.provinsi.nama})` : ''
})

// Watch for clicks outside dropdown to close it
const dropdownRef = ref<HTMLElement | null>(null)

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
    year: selectedYear.value,
    kabupaten_kota_id: selectedKabupatenKotaId.value
  }, {
    preserveState: false
  })
}

const updateSelectedKabupatenKota = () => {
  router.get(route('indas.data-entry'), {
    month: selectedMonth.value,
    year: selectedYear.value,
    kabupaten_kota_id: selectedKabupatenKotaId.value
  }, {
    preserveState: false
  })
}

const selectKabupatenKota = (kabkotaId: number) => {
  selectedKabupatenKotaId.value = kabkotaId
  showDropdown.value = false
  searchQuery.value = ''
  updateSelectedKabupatenKota()
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    searchQuery.value = ''
  }
}

const closeDropdown = () => {
  showDropdown.value = false
}

// Handle outside clicks
const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown()
  }
}

// Add event listener for outside clicks
if (typeof window !== 'undefined') {
  document.addEventListener('click', handleClickOutside)
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
  try {
    const existing = props.existingData[kabupatenKotaId]?.[indicatorId]
    if (existing && typeof existing === 'object' && 'value' in existing) {
      const value = existing.value
      if (value !== null && value !== undefined) {
        return value.toString()
      }
    }
  } catch (error) {
    console.warn('Error accessing existing data:', error)
  }
  
  return ''
}

const updateValue = (kabupatenKotaId: number, indicatorId: number, value: string) => {
  const key = getDataKey(kabupatenKotaId, indicatorId)
  formData[key] = value
}

const saveAllData = async () => {
  if (!selectedKabupatenKotaId.value || !hasChanges.value) {
    return
  }
  
  isSaving.value = true
  
  try {
    // Prepare batch data to save
    const dataToSave = []
    
    for (const [key, value] of Object.entries(formData)) {
      if (value && value.trim() !== '') {
        const [kabupatenKotaId, indicatorId] = key.split('-').map(Number)
        const numericValue = parseFloat(value)
        
        if (numericValue >= 0) {
          dataToSave.push({
            kabupaten_kota_id: kabupatenKotaId,
            indicator_type_id: indicatorId,
            value: numericValue,
            month: selectedMonth.value,
            year: selectedYear.value,
          })
        }
      }
    }
    
    if (dataToSave.length === 0) {
      alert('Tidak ada data valid untuk disimpan')
      return
    }
    
    // Save each data point
    for (const data of dataToSave) {
      await router.post(route('indas.data.store'), data, {
        preserveState: true,
        preserveScroll: true,
      })
    }
    
    // Trigger calculation after all data is saved
    await calculateAll()
    
    // Clear form data and show success
    Object.keys(formData).forEach(key => delete formData[key])
    alert('✅ Data berhasil disimpan dan skor INDAS telah dihitung ulang!')
    
  } catch (error) {
    console.error('Error saving data:', error)
    alert('❌ Terjadi kesalahan saat menyimpan data')
  } finally {
    isSaving.value = false
  }
}

const calculateAll = async () => {
  if (!selectedKabupatenKotaId.value) {
    alert('Please select a kabupaten/kota first')
    return
  }
  
  isCalculating.value = true
  
  try {
    await router.post(route('indas.calculate'), {
      month: selectedMonth.value,
      year: selectedYear.value,
      kabupaten_kota_id: selectedKabupatenKotaId.value, // Calculate for selected region only
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