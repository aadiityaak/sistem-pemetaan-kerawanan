<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { 
  MapPinIcon, 
  CurrencyDollarIcon, 
  BuildingOffice2Icon,
  UsersIcon,
  ChartBarIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'
import AppLayout from '@/layouts/AppLayout.vue'
import TrendIcon from '@/components/indas/TrendIcon.vue'
import ScoreBadge from '@/components/indas/ScoreBadge.vue'

interface AnalysisResult {
  id: number
  kabupaten_kota: {
    id: number
    nama: string
    provinsi: {
      nama: string
    }
  }
  economic_score: number
  tourism_score: number
  social_score: number
  total_score: number
  economic_trend: number | null
  tourism_trend: number | null
  social_trend: number | null
  total_trend: number | null
}

interface Stats {
  total_regions: number
  avg_economic_score: number
  avg_tourism_score: number
  avg_social_score: number
  avg_total_score: number
}

const props = defineProps<{
  analysisResults: AnalysisResult[]
  currentMonth: number
  currentYear: number
  stats: Stats
}>()

const selectedMonth = ref(props.currentMonth)
const selectedYear = ref(props.currentYear)

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
  router.get(route('indas.index'), {
    month: selectedMonth.value,
    year: selectedYear.value
  }, {
    preserveState: true,
    replace: true
  })
}

const formatScore = (score: number) => {
  return score ? score.toFixed(1) : '0.0'
}

const formatTrend = (trend: number | null) => {
  if (trend === null) return '-'
  return trend >= 0 ? `+${trend.toFixed(1)}` : trend.toFixed(1)
}

const getTrendClass = (trend: number | null) => {
  if (trend === null) return 'text-gray-400'
  if (trend > 0) return 'text-green-600 dark:text-green-400'
  if (trend < 0) return 'text-red-600 dark:text-red-400'
  return 'text-gray-600 dark:text-gray-400'
}
</script>

<template>
  <AppLayout title="INDAS - Intelligence Data Analysis System">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">INDAS Dashboard</h1>
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Intelligence Data Analysis System - Economic, Tourism & Social Indicators
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
              <Link 
                :href="route('indas.data-entry', { month: selectedMonth, year: selectedYear })"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
              >
                Input Data
              </Link>
            </div>
          </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <MapPinIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Regions</dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ stats.total_regions }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <CurrencyDollarIcon class="h-6 w-6 text-green-400" aria-hidden="true" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Economic</dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                      {{ formatScore(stats.avg_economic_score) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <BuildingOffice2Icon class="h-6 w-6 text-blue-400" aria-hidden="true" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Tourism</dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                      {{ formatScore(stats.avg_tourism_score) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <UsersIcon class="h-6 w-6 text-purple-400" aria-hidden="true" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Social</dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                      {{ formatScore(stats.avg_social_score) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <ChartBarIcon class="h-6 w-6 text-orange-400" aria-hidden="true" />
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Avg Total</dt>
                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                      {{ formatScore(stats.avg_total_score) }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Analysis Results Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
              Regional Analysis Results - {{ months[selectedMonth - 1] }} {{ selectedYear }}
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
              Analysis results for all regions with scores and trends
            </p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Region
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Economic
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Tourism
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Social
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Total Score
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Trend
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="analysisResults.length === 0">
                  <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                    No analysis results found for this period. 
                    <Link :href="route('indas.data-entry')" class="text-indigo-600 hover:text-indigo-900">
                      Start by entering indicator data
                    </Link>
                  </td>
                </tr>
                <tr v-for="result in analysisResults" :key="result.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ result.kabupaten_kota.nama }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ result.kabupaten_kota.provinsi.nama }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm text-gray-900 dark:text-white">
                        {{ formatScore(result.economic_score) }}
                      </div>
                      <TrendIcon 
                        :trend="result.economic_trend" 
                        class="ml-2"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm text-gray-900 dark:text-white">
                        {{ formatScore(result.tourism_score) }}
                      </div>
                      <TrendIcon 
                        :trend="result.tourism_trend" 
                        class="ml-2"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="text-sm text-gray-900 dark:text-white">
                        {{ formatScore(result.social_score) }}
                      </div>
                      <TrendIcon 
                        :trend="result.social_trend" 
                        class="ml-2"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <ScoreBadge :score="result.total_score" />
                      <TrendIcon 
                        :trend="result.total_trend" 
                        class="ml-2"
                      />
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <span v-if="result.total_trend !== null" :class="getTrendClass(result.total_trend)">
                      {{ formatTrend(result.total_trend) }}%
                    </span>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
          <Link 
            :href="route('kabupaten_kota.index')"
            class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 hover:shadow-lg transition-shadow duration-200 rounded-lg shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-indigo-50 text-indigo-700 ring-4 ring-white dark:bg-indigo-900/50 dark:text-indigo-300 dark:ring-gray-800">
                <MapPinIcon class="h-6 w-6" aria-hidden="true" />
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Browse Regions
              </h3>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                View kabupaten/kota data for INDAS analysis
              </p>
            </div>
          </Link>

          <Link 
            :href="route('indas.indicators')"
            class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 hover:shadow-lg transition-shadow duration-200 rounded-lg shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-green-50 text-green-700 ring-4 ring-white dark:bg-green-900/50 dark:text-green-300 dark:ring-gray-800">
                <ChartBarIcon class="h-6 w-6" aria-hidden="true" />
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Indicator Types
              </h3>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Configure economic, tourism, and social indicators with weights
              </p>
            </div>
          </Link>

          <Link 
            :href="route('indas.data-entry')"
            class="relative group bg-white dark:bg-gray-800 p-6 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-500 hover:shadow-lg transition-shadow duration-200 rounded-lg shadow"
          >
            <div>
              <span class="rounded-lg inline-flex p-3 bg-blue-50 text-blue-700 ring-4 ring-white dark:bg-blue-900/50 dark:text-blue-300 dark:ring-gray-800">
                <PlusIcon class="h-6 w-6" aria-hidden="true" />
              </span>
            </div>
            <div class="mt-8">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Data Entry
              </h3>
              <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Enter monthly indicator data and trigger automatic calculations
              </p>
            </div>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>