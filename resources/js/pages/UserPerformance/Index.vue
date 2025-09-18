<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

interface ChartData {
    date: string;
    count: number;
    formatted_date: string;
}

interface DataSourceChart {
    date: string;
    online: number;
    offline: number;
}

interface UserStat {
    user_id: number;
    name: string;
    total_posts: number;
    online_percentage: number;
}

interface ProvinceStat {
    provinsi_id: number;
    nama: string;
    total_posts: number;
}

interface FilterOptions {
    provinsiList: Array<{ id: number; nama: string }>;
    userList: Array<{ id: number; name: string }>;
}

interface Statistics {
    total_posts: number;
    online_posts: number;
    offline_posts: number;
    unique_users: number;
    online_percentage: number;
    offline_percentage: number;
}

interface Filters {
    start_date: string;
    end_date: string;
    provinsi_id?: number;
    user_id?: number;
    data_source?: string;
}

const props = defineProps<{
    chartData: ChartData[];
    dataSourceChart: DataSourceChart[];
    userStats: UserStat[];
    provinceStats: ProvinceStat[];
    statistics: Statistics;
    filters: Filters;
    filterOptions: FilterOptions;
}>();

// Chart refs
const chartCanvas = ref<HTMLCanvasElement>();
const dataSourceChartCanvas = ref<HTMLCanvasElement>();
let chart: Chart | null = null;
let dataSourceChart: Chart | null = null;

// Filters
const currentFilters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
    provinsi_id: props.filters.provinsi_id || '',
    user_id: props.filters.user_id || '',
    data_source: props.filters.data_source || '',
});

// Apply filters
const applyFilters = () => {
    const params = new URLSearchParams();

    Object.entries(currentFilters.value).forEach(([key, value]) => {
        if (value && value !== '') {
            params.append(key, value.toString());
        }
    });

    router.get(route('user-performance.index'), Object.fromEntries(params));
};

// Reset filters
const resetFilters = () => {
    currentFilters.value = {
        start_date: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0],
        provinsi_id: '',
        user_id: '',
        data_source: '',
    };
    applyFilters();
};

// Format number with commas
const formatNumber = (num: number): string => {
    return new Intl.NumberFormat('id-ID').format(num);
};

// Initialize charts
onMounted(() => {
    // Main chart - total posts per day
    if (chartCanvas.value) {
        chart = new Chart(chartCanvas.value, {
            type: 'line',
            data: {
                labels: props.chartData.map(d => d.formatted_date),
                datasets: [{
                    label: 'Jumlah Data Monitoring',
                    data: props.chartData.map(d => d.count),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Trend Data Monitoring Harian'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Data source chart - online vs offline
    if (dataSourceChartCanvas.value) {
        dataSourceChart = new Chart(dataSourceChartCanvas.value, {
            type: 'bar',
            data: {
                labels: props.dataSourceChart.map(d => d.date),
                datasets: [
                    {
                        label: '🌐 Online',
                        data: props.dataSourceChart.map(d => d.online),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                    },
                    {
                        label: '📝 Offline',
                        data: props.dataSourceChart.map(d => d.offline),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Perbandingan Data Online vs Offline'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>

<template>
    <Head title="Dashboard Performa User" />

    <AppLayout title="Dashboard Performa User">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dashboard Performa User</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Analisis statistik performa pengguna dalam mengelola data monitoring</p>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">🔍 Filter Data</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                        <input
                            v-model="currentFilters.start_date"
                            type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                        <input
                            v-model="currentFilters.end_date"
                            type="date"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <!-- Province Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provinsi</label>
                        <select
                            v-model="currentFilters.provinsi_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Semua Provinsi</option>
                            <option v-for="provinsi in filterOptions.provinsiList" :key="provinsi.id" :value="provinsi.id">
                                {{ provinsi.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Penulis</label>
                        <select
                            v-model="currentFilters.user_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Semua Penulis</option>
                            <option v-for="user in filterOptions.userList" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Data Source Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Data</label>
                        <select
                            v-model="currentFilters.data_source"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="online">🌐 Online</option>
                            <option value="offline">📝 Offline</option>
                        </select>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-3 mt-4">
                    <Button @click="applyFilters" class="bg-blue-600 hover:bg-blue-700">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Terapkan Filter
                    </Button>
                    <Button @click="resetFilters" variant="outline">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Data</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatNumber(statistics.total_posts) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">User Aktif</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ formatNumber(statistics.unique_users) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-indigo-100 p-3 dark:bg-indigo-900">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Data Online</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.online_percentage }}%</p>
                            <p class="text-xs text-gray-500">{{ formatNumber(statistics.online_posts) }} data</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Data Offline</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.offline_percentage }}%</p>
                            <p class="text-xs text-gray-500">{{ formatNumber(statistics.offline_posts) }} data</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Main Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="h-80">
                        <canvas ref="chartCanvas"></canvas>
                    </div>
                </div>

                <!-- Data Source Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
                    <div class="h-80">
                        <canvas ref="dataSourceChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tables Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Users -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">🏆 Top Penulis</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">% Online</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(user, index) in userStats" :key="user.user_id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ index + 1 }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatNumber(user.total_posts) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ Math.round(user.online_percentage) }}%
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Provinces -->
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">📍 Top Provinsi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Provinsi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Data</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="(province, index) in provinceStats" :key="province.provinsi_id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ province.nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatNumber(province.total_posts) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>