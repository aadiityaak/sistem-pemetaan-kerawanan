<template>
    <AppLayout title="Data Sembako">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Data Harga Sembako</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola data harga sembako di seluruh kabupaten/kota</p>
            </div>

            <!-- Action Buttons -->
            <div class="mb-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Bulk Delete Button -->
                    <button
                        v-if="canEdit && selectedItems.length > 0"
                        @click="confirmBulkDelete"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-xs leading-5 font-semibold tracking-widest whitespace-nowrap text-white uppercase transition duration-150 ease-in-out hover:bg-red-700 focus:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none active:bg-red-900"
                    >
                        <Trash class="mr-2 h-4 w-4" />
                        Hapus Terpilih ({{ selectedItems.length }})
                    </button>

                    <!-- Select All Button -->
                    <button
                        v-if="canEdit && sembako.data.length > 0"
                        @click="toggleSelectAll"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-50 focus:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        {{ isAllSelected ? 'Batalkan Pilihan' : 'Pilih Semua' }}
                    </button>

                    <!-- Import/Export Dropdown -->
                    <div class="relative">
                        <button
                            @click="showImportExportMenu = !showImportExportMenu"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-50 focus:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            Import/Export
                            <ChevronDown class="ml-2 h-3 w-3" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="showImportExportMenu"
                            class="absolute top-full left-0 z-10 mt-1 w-64 rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800"
                        >
                            <div class="py-1">
                                <button
                                    @click="exportCsv"
                                    class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <Download class="mr-3 h-4 w-4" />
                                    Export Data (CSV)
                                </button>
                                <button
                                    @click="downloadSample"
                                    class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <FileText class="mr-3 h-4 w-4" />
                                    Download Sample CSV
                                </button>
                                <button
                                    @click="
                                        showImportModal = true;
                                        showImportExportMenu = false;
                                    "
                                    class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <Upload class="mr-3 h-4 w-4" />
                                    Import Data (CSV)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="canEdit">
                    <Link
                        :href="route('sembako.create')"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs leading-5 font-semibold tracking-widest whitespace-nowrap text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900"
                    >
                        <Plus class="mr-2 h-4 w-4" />
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
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <!-- Search -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label>
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
                                    placeholder="Cari komoditas, satuan, keterangan..."
                                    class="block w-full rounded-md border border-gray-300 bg-white py-2 pr-3 pl-10 leading-5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                                />
                            </div>
                        </div>

                        <!-- Filter Komoditas -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Komoditas</label>
                            <select
                                v-model="selectedKomoditas"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">📦 Semua Komoditas</option>
                                <option v-for="commodity in commodities" :key="commodity" :value="commodity">
                                    {{ commodity }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Provinsi -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Provinsi</label>
                            <select
                                v-model="selectedProvinsi"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">🏞️ Semua Provinsi</option>
                                <option v-for="provinsi in provinces" :key="provinsi.id" :value="provinsi.id">
                                    {{ provinsi.nama }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Kabupaten/Kota -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kabupaten/Kota</label>
                            <select
                                v-model="selectedKabupaten"
                                @change="onKabupatenChange"
                                :disabled="!selectedProvinsi"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none disabled:bg-gray-100 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:disabled:bg-gray-600"
                            >
                                <option value="">
                                    {{
                                        !selectedProvinsi
                                            ? 'Pilih Provinsi dulu'
                                            : filteredKabupaten && filteredKabupaten.length === 0
                                              ? `Loading... (Total: ${kabupatenKota.length})`
                                              : `🌍 Semua Kabupaten/Kota (${filteredKabupaten.length})`
                                    }}
                                </option>
                                <option v-for="kabupaten in filteredKabupaten" :key="kabupaten.id" :value="kabupaten.id">
                                    {{ kabupaten.nama }}
                                </option>
                                <!-- Debug option -->
                                <option v-if="selectedProvinsi && filteredKabupaten.length === 0" disabled>
                                    DEBUG: Total available = {{ kabupatenKota.length }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Tanggal Mulai -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                            <input
                                v-model="startDate"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 leading-5 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- Filter Tanggal Akhir -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Akhir</label>
                            <input
                                v-model="endDate"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 leading-5 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- Filter Harga Minimum -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Minimum</label>
                            <input
                                v-model="minPrice"
                                type="number"
                                placeholder="0"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 leading-5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                            />
                        </div>

                        <!-- Filter Harga Maximum -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Maximum</label>
                            <input
                                v-model="maxPrice"
                                type="number"
                                placeholder="999999999"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 leading-5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Clear Filters Button -->
                    <div class="mt-4 flex items-center justify-between">
                        <button
                            @click="clearFilters"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm leading-4 font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            🗑️ Clear Filters
                        </button>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Showing {{ sembako.total }} results</div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Data Harga Sembako</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th
                                    v-if="canEdit"
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isAllSelected"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200/50 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Komoditas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Satuan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Harga
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Kabupaten/Kota
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="item in sembako.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td v-if="canEdit" class="px-6 py-4 whitespace-nowrap">
                                    <input
                                        type="checkbox"
                                        :value="item.id"
                                        :checked="selectedItems.includes(item.id)"
                                        @change="toggleItemSelection(item.id)"
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200/50 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                    {{ item.nama_komoditas }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ item.satuan }}
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold whitespace-nowrap text-green-600 dark:text-green-400">
                                    {{ item.formatted_harga }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="max-w-xs">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ item.kabupaten_kota.nama }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.kabupaten_kota.provinsi.nama }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ formatDate(item.tanggal_pencatatan) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <Link
                                            :href="route('sembako.show', item.id)"
                                            class="inline-flex items-center rounded border border-blue-300 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100 dark:border-blue-600 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                        >
                                            <Eye class="mr-1 h-3 w-3" />
                                            Lihat
                                        </Link>
                                        <Link
                                            v-if="canEdit"
                                            :href="route('sembako.edit', item.id)"
                                            class="inline-flex items-center rounded border border-yellow-300 bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 hover:bg-yellow-100 dark:border-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/40"
                                        >
                                            <Edit class="mr-1 h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            v-if="canEdit"
                                            @click="confirmDelete(item)"
                                            class="inline-flex items-center rounded border border-red-300 bg-red-50 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-100 dark:border-red-600 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                        >
                                            <Trash class="mr-1 h-3 w-3" />
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
                    class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex flex-1 justify-between sm:hidden">
                        <Link
                            v-if="sembako.prev_page_url"
                            :href="sembako.prev_page_url"
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="sembako.next_page_url"
                            :href="sembako.next_page_url"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing {{ sembako.from }} to {{ sembako.to }} of {{ sembako.total }} results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm">
                                <Link
                                    v-for="link in sembako.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    :class="[
                                        'relative inline-flex items-center border px-2 py-2 text-sm font-medium',
                                        !link.url && 'pointer-events-none',
                                        link.active
                                            ? 'z-10 border-blue-500 bg-blue-50 text-blue-600 dark:border-blue-400 dark:bg-blue-900 dark:text-blue-200'
                                            : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700',
                                    ]"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Import Data CSV</h3>
                    <button @click="showImportModal = false" class="rounded-full p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitImport">
                    <div class="mb-4">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pilih File CSV </label>
                        <input
                            ref="csvFileInput"
                            type="file"
                            accept=".csv,.txt"
                            @change="handleFileSelect"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Format: CSV dengan delimiter titik koma (;). Maksimal 5MB.</p>
                    </div>

                    <div class="mb-4 rounded-md bg-blue-50 p-3 dark:bg-blue-900/20">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Format CSV harus sesuai dengan template.
                                    <button type="button" @click="downloadSample" class="font-medium underline hover:no-underline">
                                        Download sample CSV
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="showImportModal = false"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="!selectedFile || importLoading"
                            class="rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span v-if="importLoading">Mengimport...</span>
                            <span v-else>Import Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, Download, Edit, Eye, FileText, Plus, Trash, Upload } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Simple debounce function
const debounce = (func: Function, wait: number) => {
    let timeout: ReturnType<typeof setTimeout>;
    return function executedFunction(...args: any[]) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

interface KabupatenKota {
    id: number;
    nama: string;
    provinsi: {
        id: number;
        nama: string;
    };
}

interface SembakoItem {
    id: number;
    nama_komoditas: string;
    satuan: string;
    harga: number;
    formatted_harga: string;
    kabupaten_kota_id: number;
    tanggal_pencatatan: string;
    keterangan?: string;
    kabupaten_kota: KabupatenKota;
}

interface PaginatedSembako {
    data: SembakoItem[];
    current_page: number;
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

interface Commodity {
    value: string;
    label: string;
}

interface Provinsi {
    id: number;
    nama: string;
}

const props = defineProps<{
    sembako: PaginatedSembako;
    kabupatenKota: KabupatenKota[];
    commodities: string[];
    provinces: Provinsi[];
    filters: {
        nama_komoditas?: string;
        kabupaten_kota_id?: number;
        provinsi_id?: number;
        tanggal_mulai?: string;
        tanggal_selesai?: string;
        harga_min?: number;
        harga_max?: number;
        search?: string;
        sort_field?: string;
        sort_direction?: string;
    };
}>();

// Get current user and check edit permissions
const page = usePage();
const currentUser = computed(() => page.props.auth.user as any);
const canEdit = computed(() => ['super_admin', 'admin'].includes(currentUser.value.role));

// Filters - initialize from server-side filters
const searchQuery = ref(props.filters.search || '');
const selectedKomoditas = ref(props.filters.nama_komoditas || '');
const selectedProvinsi = ref(props.filters.provinsi_id?.toString() || '');
const selectedKabupaten = ref(props.filters.kabupaten_kota_id?.toString() || '');
const startDate = ref(props.filters.tanggal_mulai || '');
const endDate = ref(props.filters.tanggal_selesai || '');
const minPrice = ref(props.filters.harga_min?.toString() || '');
const maxPrice = ref(props.filters.harga_max?.toString() || '');
const sortField = ref(props.filters.sort_field || 'tanggal_pencatatan');
const sortDirection = ref(props.filters.sort_direction || 'desc');

// Bulk delete functionality
const selectedItems = ref<number[]>([]);

const isAllSelected = computed(() => {
    return props.sembako.data.length > 0 && selectedItems.value.length === props.sembako.data.length;
});

// Import/Export functionality
const showImportExportMenu = ref(false);
const showImportModal = ref(false);
const selectedFile = ref<File | null>(null);
const importLoading = ref(false);
const csvFileInput = ref<HTMLInputElement>();

// Filtered kabupaten based on selected provinsi
const filteredKabupaten = computed(() => {
    console.log('=== SEMBAKO FILTERED KABUPATEN ===');
    console.log('selectedProvinsi.value:', selectedProvinsi.value);
    console.log('props.kabupatenKota length:', props.kabupatenKota?.length || 0);

    if (!selectedProvinsi.value || selectedProvinsi.value === '' || selectedProvinsi.value === '0') {
        console.log('No provinsi selected, returning empty array');
        return [];
    }

    if (!props.kabupatenKota || props.kabupatenKota.length === 0) {
        console.log('No kabupaten data available');
        return [];
    }

    console.log('Filtering kabupaten for provinsi:', selectedProvinsi.value);
    console.log('First 3 available kabupaten:', props.kabupatenKota?.slice(0, 3) || []);

    const selectedProvinsiStr = selectedProvinsi.value.toString();
    const filtered = props.kabupatenKota.filter((k) => {
        if (!k || !k.provinsi || !k.provinsi.id) return false;

        const kProvinsiId = k.provinsi.id.toString();
        const match = kProvinsiId === selectedProvinsiStr;

        if (props.kabupatenKota.indexOf(k) < 3) {
            // Only log first 3 for brevity
            console.log(`Kabupaten ${k.nama}: provinsi_id=${k.provinsi.id}, selected=${selectedProvinsi.value}, match=${match}`);
        }
        return match;
    });

    console.log('Filtered kabupaten result length:', filtered.length);
    console.log('First 3 filtered results:', filtered.slice(0, 3));
    console.log('=== END SEMBAKO FILTERED KABUPATEN ===');

    return filtered;
});

// Database filtering function
const applyFilters = debounce(() => {
    const params: Record<string, any> = {};

    if (searchQuery.value) params.search = searchQuery.value;
    if (selectedKomoditas.value) params.nama_komoditas = selectedKomoditas.value;
    if (selectedProvinsi.value) params.provinsi_id = selectedProvinsi.value;
    if (selectedKabupaten.value) params.kabupaten_kota_id = selectedKabupaten.value;
    if (startDate.value) params.tanggal_mulai = startDate.value;
    if (endDate.value) params.tanggal_selesai = endDate.value;
    if (minPrice.value) params.harga_min = minPrice.value;
    if (maxPrice.value) params.harga_max = maxPrice.value;
    if (sortField.value) params.sort_field = sortField.value;
    if (sortDirection.value) params.sort_direction = sortDirection.value;

    router.get(route('sembako.index'), params, {
        preserveState: true,
        replace: true,
    });
}, 500);

// Clear all filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedKomoditas.value = '';
    selectedProvinsi.value = '';
    selectedKabupaten.value = '';
    startDate.value = '';
    endDate.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    applyFilters();
};

// Watch filters and apply debounced filtering
watch([searchQuery, selectedKomoditas, selectedProvinsi, selectedKabupaten, startDate, endDate, minPrice, maxPrice], () => {
    applyFilters();
});

// onChange handlers for regional filters
const onKabupatenChange = () => {
    console.log('Kabupaten changed to:', selectedKabupaten.value);
    // applyFilters will be called by the watcher
};

// Watch provinsi change to reset kabupaten selection
watch(selectedProvinsi, (newValue) => {
    console.log('=== PROVINSI CHANGE DEBUG SEMBAKO ===');
    console.log('Provinsi changed to:', newValue);
    console.log('Available kabupaten:', props.kabupatenKota.length);
    selectedKabupaten.value = '';
    // Force reactive update
    setTimeout(() => {
        console.log('After timeout - filtered kabupaten:', filteredKabupaten.value.length);
        console.log('Sample filtered:', filteredKabupaten.value.slice(0, 3));
    }, 100);
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const confirmDelete = (item: SembakoItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data ${item.nama_komoditas} di ${item.kabupaten_kota.nama}?`)) {
        router.delete(route('sembako.destroy', item.id));
    }
};

// Bulk delete functions
const toggleItemSelection = (itemId: number) => {
    const index = selectedItems.value.indexOf(itemId);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(itemId);
    }
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = props.sembako.data.map((item) => item.id);
    }
};

const confirmBulkDelete = () => {
    if (selectedItems.value.length === 0) return;

    if (confirm(`Apakah Anda yakin ingin menghapus ${selectedItems.value.length} data sembako yang dipilih?`)) {
        router.delete(route('sembako.bulk-destroy'), {
            data: {
                ids: selectedItems.value,
            },
            onSuccess: () => {
                selectedItems.value = [];
            },
        });
    }
};

// Import/Export functions
const exportCsv = () => {
    // Build URL with current filters
    const params = new URLSearchParams();

    if (searchQuery.value) params.append('search', searchQuery.value);
    if (selectedKomoditas.value) params.append('nama_komoditas', selectedKomoditas.value);
    if (selectedProvinsi.value) params.append('provinsi_id', selectedProvinsi.value);
    if (selectedKabupaten.value) params.append('kabupaten_kota_id', selectedKabupaten.value);
    if (startDate.value) params.append('tanggal_mulai', startDate.value);
    if (endDate.value) params.append('tanggal_selesai', endDate.value);
    if (minPrice.value) params.append('harga_min', minPrice.value);
    if (maxPrice.value) params.append('harga_max', maxPrice.value);

    const url = route('sembako.export-csv') + '?' + params.toString();
    window.open(url, '_blank');
    showImportExportMenu.value = false;
};

const downloadSample = () => {
    window.open(route('sembako.download-sample'), '_blank');
    showImportExportMenu.value = false;
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    selectedFile.value = file || null;
};

const submitImport = () => {
    if (!selectedFile.value) return;

    importLoading.value = true;
    const formData = new FormData();
    formData.append('csv_file', selectedFile.value);

    router.post(route('sembako.import-csv'), formData, {
        onSuccess: () => {
            showImportModal.value = false;
            selectedFile.value = null;
            if (csvFileInput.value) {
                csvFileInput.value.value = '';
            }
        },
        onFinish: () => {
            importLoading.value = false;
        },
    });
};

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
    if (!(event.target as Element).closest('.relative')) {
        showImportExportMenu.value = false;
    }
};

// Add click outside listener
if (typeof window !== 'undefined') {
    document.addEventListener('click', handleClickOutside);
}
</script>
