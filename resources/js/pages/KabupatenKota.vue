<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface KabupatenKotaData {
    id: number;
    nama: string;
    provinsi?: {
        nama: string;
    };
    jumlah_kecamatan?: number;
    crime_data?: any[];
}

interface Statistics {
    total_kabkota: number;
    total_crimes: number;
    affected_kabkota: number;
    avg_crimes_per_kabkota: string;
}

interface PaginatedData {
    data: KabupatenKotaData[];
    current_page: number;
    last_page: number;
    prev_page_url?: string;
    next_page_url?: string;
    from: number;
    to: number;
    total: number;
}

interface Props {
    kabupatenKota: PaginatedData;
    statistics: Statistics;
    filters?: {
        search?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { name: 'Dashboard', title: 'Dashboard', href: '/dashboard' },
    { name: 'Kabupaten/Kota', title: 'Kabupaten/Kota', href: '', current: true },
];

const search = ref(props.filters?.search || '');

// Watch for search input and filter automatically (with debounce)
let debounceTimer: number | null = null;
watch(search, (newValue) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        const params = newValue ? { search: newValue } : {};

        router.get(route('kabupaten_kota.index'), params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 500);
});

// Watch for URL changes to update search query
watch(
    () => props.filters?.search,
    (newValue) => {
        search.value = newValue || '';
    },
);
</script>

<template>
    <Head title="Kabupaten/Kota" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Daftar Kabupaten/Kota</h1>
                <div class="w-64">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari kabupaten/kota..."
                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    />
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h1a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Kabupaten/Kota</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ statistics.total_kabkota.toLocaleString() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Data Monitoring</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ statistics.total_crimes.toLocaleString() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Kabupaten/Kota Komentar</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ statistics.affected_kabkota.toLocaleString() }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                    />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Rata-rata per Kabupaten/Kota</dt>
                                    <dd class="text-lg font-medium text-gray-900 dark:text-white">{{ statistics.avg_crimes_per_kabkota }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kabupaten/Kota Table -->
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                Nama Kabupaten/Kota
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                Provinsi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                Kecamatan
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                Data Monitoring
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        <tr v-for="item in kabupatenKota.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">{{ item.id }}</td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">{{ item.nama }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">{{ item.provinsi?.nama || '-' }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">{{ item.jumlah_kecamatan || 0 }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="
                                        (item.crime_data?.length || 0) > 0
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                    "
                                >
                                    {{ item.crime_data?.length || 0 }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="mt-6 flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="flex flex-1 justify-between sm:hidden">
                    <Link
                        v-if="kabupatenKota.prev_page_url"
                        :href="kabupatenKota.prev_page_url"
                        preserve-scroll
                        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="kabupatenKota.next_page_url"
                        :href="kabupatenKota.next_page_url"
                        preserve-scroll
                        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Next
                    </Link>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan
                            <span class="font-medium">{{ kabupatenKota.from || 0 }}</span>
                            sampai
                            <span class="font-medium">{{ kabupatenKota.to || 0 }}</span>
                            dari
                            <span class="font-medium">{{ kabupatenKota.total }}</span>
                            kabupaten/kota
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <Link
                                v-if="kabupatenKota.prev_page_url"
                                :href="kabupatenKota.prev_page_url"
                                preserve-scroll
                                class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                            >
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path
                                        fill-rule="evenodd"
                                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </Link>

                            <span
                                class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                            >
                                Halaman {{ kabupatenKota.current_page }} dari {{ kabupatenKota.last_page }}
                            </span>

                            <Link
                                v-if="kabupatenKota.next_page_url"
                                :href="kabupatenKota.next_page_url"
                                preserve-scroll
                                class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                            >
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path
                                        fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
