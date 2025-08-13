<template>
    <AppLayout title="Data Monitoring">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Data Monitoring</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola dan pantau semua data monitoring kejahatan</p>
            </div>

            <!-- Statistics Cards -->
            <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Data</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.total || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Aktif</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.active || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-gray-100 p-2 dark:bg-gray-700">
                            <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Selesai</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.completed || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 18.5c-.77.833.192 2.5 1.732 2.5z"
                                ></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Level Kritis</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.critical || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mb-6 flex justify-between items-center">
                <div></div>
                <div>
                    <Link
                        :href="route('monitoring-data.create')"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900 leading-5 whitespace-nowrap"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Tambah Data
                    </Link>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">🔍 Filter & Pencarian</h3>
                </div>

                <div class="p-6">
                    <!-- Baris 1: Search, Provinsi, Kabupaten, Status -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-4">
                        <!-- Search -->
                        <div class="sm:col-span-2 lg:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pencarian</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        ></path>
                                    </svg>
                                </div>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari data monitoring..."
                                    class="block w-full rounded-md border border-gray-300 bg-white py-2 pr-3 pl-10 leading-5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                                />
                            </div>
                        </div>

                        <!-- Filter Provinsi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Provinsi</label>
                            <select
                                v-model="selectedProvinsi"
                                @change="onProvinsiChange"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">🏞️ Semua Provinsi</option>
                                <option v-for="provinsi in provinsiList" :key="provinsi.id" :value="provinsi.id">
                                    {{ provinsi.nama }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Kabupaten/Kota -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Kabupaten/Kota</label>
                            <select
                                v-model="selectedKabupaten"
                                @change="onKabupatenChange"
                                :disabled="!selectedProvinsi"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-600"
                            >
                                <option value="">
                                    {{ !selectedProvinsi ? 'Pilih Provinsi dulu' : 
                                       (filteredKabupaten && filteredKabupaten.length === 0) ? `Loading...` : 
                                       `🌍 Semua Kabupaten/Kota` }}
                                </option>
                                <option v-for="kabupaten in filteredKabupaten" :key="kabupaten.id" :value="kabupaten.id">
                                    {{ kabupaten.nama }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">🟢 Semua Status</option>
                                <option value="active">🔵 Aktif</option>
                                <option value="resolved">✅ Selesai</option>
                                <option value="monitoring">⏳ Monitoring</option>
                                <option value="archived">📁 Arsip</option>
                            </select>
                        </div>
                    </div>

                    <!-- Baris 2: Tingkat Keparahan, Tanggal Mulai, Tanggal Akhir, Reset Button -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Filter Level -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tingkat Keparahan</label>
                            <select
                                v-model="selectedLevel"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">🟢 Semua Level</option>
                                <option value="low">🟡 Rendah</option>
                                <option value="medium">🟠 Sedang</option>
                                <option value="high">🔴 Tinggi</option>
                                <option value="critical">⚠️ Kritis</option>
                            </select>
                        </div>

                        <!-- Start Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                            <input
                                v-model="startDate"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 leading-5 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- End Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                            <input
                                v-model="endDate"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white py-2 px-3 leading-5 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- Reset Filters Button -->
                        <div class="flex items-end">
                            <button
                                @click="resetFilters"
                                type="button"
                                class="w-full rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500 transition-colors duration-200"
                            >
                                <svg class="inline h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Data Monitoring</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Judul
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Lokasi
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Kategori
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Tanggal Laporan
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Level
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Sumber
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                >
                                    Korban
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-if="monitoringData.data.length === 0">
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data yang ditemukan</td>
                            </tr>
                            <tr v-else v-for="data in monitoringData.data" :key="data.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 max-w-xs">
                                        {{ data.title || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ data.kabupatenKota?.nama || data.kabupaten_kota?.nama || 'N/A' }}
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
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                    {{ formatDate(data.tanggal_laporan) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getLevelBadgeClass(data.level_kejadian)"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ data.level_kejadian }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-xs">
                                    <div v-if="data.sumber_berita" class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path>
                                        </svg>
                                        <span class="truncate">{{ data.sumber_berita }}</span>
                                    </div>
                                    <span v-else class="text-gray-400 italic text-xs">-</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusBadgeClass(data.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ data.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                    {{ data.jumlah_korban || 0 }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <Link
                                            :href="route('monitoring-data.show', data.id)"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-400 dark:bg-blue-900/50 dark:hover:bg-blue-900 transition-colors duration-200"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            Lihat
                                        </Link>
                                        <Link
                                            :href="route('monitoring-data.edit', data.id)"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:text-indigo-400 dark:bg-indigo-900/50 dark:hover:bg-indigo-900 transition-colors duration-200"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            Edit
                                        </Link>
                                        <button
                                            @click="confirmDelete(data)"
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 dark:text-red-400 dark:bg-red-900/50 dark:hover:bg-red-900 transition-colors duration-200"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-800"
                    v-if="monitoringData.last_page > 1"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="monitoringData.prev_page_url"
                                :href="monitoringData.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="monitoringData.next_page_url"
                                :href="monitoringData.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
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
                                <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <!-- Previous Page Link -->
                                    <Link
                                        v-if="monitoringData.prev_page_url"
                                        :href="monitoringData.prev_page_url"
                                        class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600"
                                    >
                                        <span class="sr-only">Previous</span>
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </Link>

                                    <!-- Page Numbers -->
                                    <template v-for="link in monitoringData.links" :key="link.label">
                                        <Link
                                            v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                            :href="link.url"
                                            :class="[
                                                'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
                                                link.active
                                                    ? 'z-10 border-blue-500 bg-blue-50 text-blue-600 dark:bg-blue-900 dark:text-blue-400'
                                                    : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600',
                                            ]"
                                        >
                                            {{ link.label }}
                                        </Link>
                                        <span
                                            v-else-if="!link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                            class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        >
                                            {{ link.label }}
                                        </span>
                                    </template>

                                    <!-- Next Page Link -->
                                    <Link
                                        v-if="monitoringData.next_page_url"
                                        :href="monitoringData.next_page_url"
                                        class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600"
                                    >
                                        <span class="sr-only">Next</span>
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
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
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop - prevent closing on click -->
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click.prevent.stop></div>
            <!-- Modal content - prevent event bubbling -->
            <div
                @click.stop
                class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-lg dark:bg-gray-800"
            >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 dark:bg-red-900"
                            >
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 18.5c-.77.833.192 2.5 1.732 2.5z"
                                    ></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">Hapus Data Monitoring</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin menghapus data monitoring ini? Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                        <button
                            @click="deleteData"
                            type="button"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Hapus
                        </button>
                        <button
                            @click="closeDeleteModal"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                    </div>
                </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

// Define types
interface Category {
    id: number;
    name: string;
}

interface SubCategory {
    id: number;
    name: string;
}

interface KabupatenKota {
    id: number;
    name?: string;
    nama?: string;
}

interface Kecamatan {
    id: number;
    name?: string;
    nama?: string;
    kabupaten_kota: KabupatenKota;
}

interface MonitoringDataItem {
    id: number;
    title?: string;
    category: Category;
    subCategory?: SubCategory;
    kecamatan: Kecamatan;
    tanggal_laporan: string;
    level_kejadian: string;
    status: string;
    jumlah_korban?: number;
}

interface PaginatedData {
    data: MonitoringDataItem[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    prev_page_url?: string;
    next_page_url?: string;
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
}

interface Statistics {
    total: number;
    active: number;
    completed: number;
    critical: number;
}

interface Provinsi {
    id: number;
    nama: string;
}

interface KabupatenKotaItem {
    id: number;
    nama: string;
    provinsi_id: number;
}

interface KecamatanItem {
    id: number;
    nama: string;
    kabupaten_kota_id: number;
}

// Props
const props = defineProps<{
    monitoringData: PaginatedData;
    statistics: Statistics;
    filters: {
        search?: string;
        status?: string;
        level?: string;
        start_date?: string;
        end_date?: string;
        provinsi_id?: string;
        kabupaten_kota_id?: string;
    };
    provinsiList: Provinsi[];
    kabupatenKotaList: KabupatenKotaItem[];
}>();

// Reactive data
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedLevel = ref(props.filters.level || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const selectedProvinsi = ref(props.filters.provinsi_id || '');
const selectedKabupaten = ref(props.filters.kabupaten_kota_id || '');
const showDeleteModal = ref(false);
const dataToDelete = ref<MonitoringDataItem | null>(null);
const searchTimeout = ref<number | null>(null);


// Computed properties for regional filtering
const filteredKabupaten = computed(() => {
    if (!selectedProvinsi.value || selectedProvinsi.value === '' || selectedProvinsi.value === '0') {
        return [];
    }
    
    if (!props.kabupatenKotaList || props.kabupatenKotaList.length === 0) {
        return [];
    }
    
    const selectedProvinsiStr = selectedProvinsi.value.toString();
    const filtered = props.kabupatenKotaList.filter(k => {
        if (!k || !k.provinsi_id) return false;
        const kProvinsiId = k.provinsi_id.toString();
        return kProvinsiId === selectedProvinsiStr;
    });
    
    return filtered;
});


// onChange handlers for regional filters
const onProvinsiChange = () => {
    selectedKabupaten.value = '';
    applyFilters();
};

const onKabupatenChange = () => {
    applyFilters();
};

// Reset all filters
const resetFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    selectedLevel.value = '';
    startDate.value = '';
    endDate.value = '';
    selectedProvinsi.value = '';
    selectedKabupaten.value = '';
    applyFilters();
};

// Helper function to apply filters
const applyFilters = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    searchTimeout.value = setTimeout(() => {
        const params: Record<string, any> = {};

        if (searchQuery.value) params.search = searchQuery.value;
        if (selectedStatus.value) params.status = selectedStatus.value;
        if (selectedLevel.value) params.level = selectedLevel.value;
        if (startDate.value) params.start_date = startDate.value;
        if (endDate.value) params.end_date = endDate.value;
        if (selectedProvinsi.value) params.provinsi_id = selectedProvinsi.value;
        if (selectedKabupaten.value) params.kabupaten_kota_id = selectedKabupaten.value;

        console.log('Applying filters:', params);

        router.get(route('monitoring-data.index'), params, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

// Watch for filter changes with debounce (non-regional filters)
watch([searchQuery, selectedStatus, selectedLevel, startDate, endDate], () => {
    applyFilters();
});


// Methods
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getLevelBadgeClass = (level: string) => {
    const classes = {
        rendah: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        sedang: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        tinggi: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        kritis: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[level as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getStatusBadgeClass = (status: string) => {
    const classes = {
        aktif: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        selesai: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        dalam_proses: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const confirmDelete = (data: MonitoringDataItem) => {
    dataToDelete.value = data;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    dataToDelete.value = null;
};

const deleteData = () => {
    if (dataToDelete.value) {
        router.delete(route('monitoring-data.destroy', dataToDelete.value.id), {
            onSuccess: () => {
                closeDeleteModal();
            },
        });
    }
};
</script>
