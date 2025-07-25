<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{ provinsi: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Provinsi', href: '/provinsi' },
];

const searchQuery = ref('');

function search() {
    router.get('/provinsi', { q: searchQuery.value }, {
        preserveState: true,
        replace: true
    });
}

function clearSearch() {
    searchQuery.value = '';
    router.get('/provinsi', {}, {
        preserveState: true,
        replace: true
    });
}

// Computed statistics
const statistics = computed(() => {
    const data = props.provinsi.data || [];
    const total = data.length;
    const withCrimes = data.filter((item: any) => item.jumlah_tindakan > 0).length;
    const totalCrimes = data.reduce((sum: number, item: any) => sum + (item.jumlah_tindakan || 0), 0);
    const totalKabupatenKota = data.reduce((sum: number, item: any) => sum + (item.jumlah_kabupaten_kota || 0), 0);
    const avgCrimesPerProvinsi = total > 0 ? (totalCrimes / total).toFixed(1) : 0;
    
    return {
        total: props.provinsi.total || total,
        currentPageTotal: total,
        withCrimes,
        totalCrimes,
        totalKabupatenKota,
        avgCrimesPerProvinsi,
        safeProvinces: total - withCrimes
    };
});
</script>

<template>
    <Head title="Provinsi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Daftar Provinsi</h1>
                
                <!-- Search Box -->
                <div class="flex gap-2">
                    <input 
                        v-model="searchQuery"
                        @keyup.enter="search"
                        type="text" 
                        placeholder="Cari provinsi..." 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white"
                    />
                    <button 
                        @click="search"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                    >
                        Cari
                    </button>
                    <button 
                        @click="clearSearch"
                        class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 transition"
                    >
                        Reset
                    </button>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Provinsi</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.total }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Halaman ini: {{ statistics.currentPageTotal }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <span class="text-xl">🏛️</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kasus Kriminal</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.totalCrimes }}</p>
                        </div>
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                            <span class="text-xl">🚨</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Provinsi Dengan Kriminal</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.withCrimes }}</p>
                        </div>
                        <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                            <span class="text-xl">⚠️</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-zinc-900 rounded-lg border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Kasus</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ statistics.avgCrimesPerProvinsi }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <span class="text-xl">📊</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kab/Kota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kecamatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Jumlah Kriminal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr v-for="item in provinsi.data" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ item.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ item.nama }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ item.jumlah_kabupaten_kota || 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ item.jumlah_kecamatan || 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span v-if="item.jumlah_tindakan > 0" class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    {{ item.jumlah_tindakan }} kasus
                                </span>
                                <span v-else class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    0 kasus
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pesan jika tidak ada data -->
                <div v-if="!provinsi.data || provinsi.data.length === 0" class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">Belum ada data provinsi yang tersimpan.</p>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="provinsi.data && provinsi.data.length > 0" class="flex justify-between items-center mt-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan {{ provinsi.from || 0 }} - {{ provinsi.to || 0 }} dari {{ provinsi.total || 0 }} data
                </div>
                <div class="flex gap-2">
                    <a v-if="provinsi.prev_page_url" 
                       :href="provinsi.prev_page_url" 
                       class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Sebelumnya
                    </a>
                    <span class="px-3 py-1 bg-blue-600 text-white rounded">
                        {{ provinsi.current_page || 1 }}
                    </span>
                    <a v-if="provinsi.next_page_url" 
                       :href="provinsi.next_page_url" 
                       class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Selanjutnya
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
