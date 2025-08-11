<template>
    <AppLayout title="Partai Politik">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Partai Politik</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Kelola data partai politik dan jumlah suara pemilu</p>
            </div>

            <!-- Statistics Cards -->
            <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Partai</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ partaiPolitik.total || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Partai Aktif</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ partaiPolitik.data.filter(p => p.status_aktif).length }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Tidak Aktif</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ partaiPolitik.data.filter(p => !p.status_aktif).length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <!-- Search Input -->
                    <div class="relative">
                        <input
                            v-model="form.search"
                            type="text"
                            placeholder="Cari partai atau singkatan..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pl-10 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            @input="handleSearch"
                        />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <select
                        v-model="form.status_filter"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @change="handleFilter"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>

                <!-- Add Button -->
                <Link
                    :href="route('partai-politik.create')"
                    class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Partai
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">No. Urut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Nama Partai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Singkatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Ketua Umum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Data Suara</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="partai in partaiPolitik.data" :key="partai.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ partai.nomor_urut }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex items-center">
                                    <img v-if="partai.logo_url" :src="partai.logo_url" :alt="partai.nama_partai" class="mr-3 h-8 w-8 rounded-full object-cover">
                                    <div class="mr-3 h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600" v-else></div>
                                    <div>
                                        <div class="font-medium">{{ partai.nama_partai }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                    {{ partai.singkatan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                <div v-if="partai.nama_ketua || partai.foto_ketua_url" class="flex items-center">
                                    <img v-if="partai.foto_ketua_url" :src="partai.foto_ketua_url" :alt="partai.nama_ketua || 'Ketua Umum'" class="mr-3 h-8 w-8 rounded-full object-cover">
                                    <div class="mr-3 h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600" v-else></div>
                                    <div>
                                        <div class="font-medium">{{ partai.nama_ketua || 'Belum diisi' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Ketua Umum</div>
                                    </div>
                                </div>
                                <div v-else class="text-gray-500 dark:text-gray-400 text-xs italic">
                                    Belum diisi
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span v-if="partai.status_aktif" class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    Aktif
                                </span>
                                <span v-else class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    Tidak Aktif
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                <span class="text-xs">{{ partai.jumlah_suara.length }} tahun pemilu</span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="route('partai-politik.show', partai.id)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded border border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 dark:border-blue-600 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/40"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Detail
                                    </Link>
                                    <Link
                                        :href="route('partai-politik.edit', partai.id)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded border border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 dark:border-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400 dark:hover:bg-yellow-900/40"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </Link>
                                    <button
                                        @click="deletePartai(partai)"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded border border-red-300 bg-red-50 text-red-700 hover:bg-red-100 dark:border-red-600 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <tbody v-if="partaiPolitik.data.length === 0" class="bg-white dark:bg-gray-800">
                    <tr>
                        <td colspan="7" class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada partai politik</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan partai politik pertama.</p>
                            <div class="mt-6">
                                <Link
                                    :href="route('partai-politik.create')"
                                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Partai
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </div>

            <!-- Pagination -->
            <div v-if="partaiPolitik.links && partaiPolitik.links.length > 3" class="mt-6">
                <nav class="flex items-center justify-between">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <Link
                            v-if="partaiPolitik.prev_page_url"
                            :href="partaiPolitik.prev_page_url"
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Sebelumnya
                        </Link>
                        <Link
                            v-if="partaiPolitik.next_page_url"
                            :href="partaiPolitik.next_page_url"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Selanjutnya
                        </Link>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan
                                <span class="font-medium">{{ partaiPolitik.from || 0 }}</span>
                                sampai
                                <span class="font-medium">{{ partaiPolitik.to || 0 }}</span>
                                dari
                                <span class="font-medium">{{ partaiPolitik.total || 0 }}</span>
                                hasil
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <template v-for="(link, index) in partaiPolitik.links" :key="index">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-medium"
                                        :class="[
                                            link.active 
                                                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:border-blue-400 dark:text-blue-300' 
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === partaiPolitik.links.length - 1 ? 'rounded-r-md' : '',
                                            'border'
                                        ]"
                                        v-html="link.label"
                                    ></Link>
                                    <span
                                        v-else
                                        class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400"
                                        :class="[
                                            index === 0 ? 'rounded-l-md' : '',
                                            index === partaiPolitik.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                        v-html="link.label"
                                    ></span>
                                </template>
                            </nav>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
// Simple debounce function
const debounce = (func: Function, wait: number) => {
    let timeout: ReturnType<typeof setTimeout>
    return function executedFunction(...args: any[]) {
        const later = () => {
            clearTimeout(timeout)
            func(...args)
        }
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
    }
}

interface PartaiPolitik {
    id: number
    nama_partai: string
    singkatan: string
    nomor_urut: number
    logo_path: string | null
    logo_url: string | null
    foto_ketua: string | null
    foto_ketua_url: string | null
    nama_ketua: string | null
    status_aktif: boolean
    jumlah_suara: Array<{
        id: number
        tahun_pemilu: number
        jumlah_suara: number
    }>
}

interface Props {
    partaiPolitik: {
        data: PartaiPolitik[]
        total: number
        from: number
        to: number
        prev_page_url: string | null
        next_page_url: string | null
        links: Array<{
            url: string | null
            label: string
            active: boolean
        }>
    }
    filters: {
        search?: string
        status_filter?: string
    }
}

const props = defineProps<Props>()

const form = reactive({
    search: props.filters.search || '',
    status_filter: props.filters.status_filter || ''
})

const handleSearch = debounce(() => {
    router.get(route('partai-politik.index'), {
        search: form.search,
        status_filter: form.status_filter
    }, {
        preserveState: true,
        replace: true
    })
}, 300)

const handleFilter = () => {
    router.get(route('partai-politik.index'), {
        search: form.search,
        status_filter: form.status_filter
    }, {
        preserveState: true,
        replace: true
    })
}

const deletePartai = (partai: PartaiPolitik) => {
    if (confirm(`Apakah Anda yakin ingin menghapus partai ${partai.nama_partai}?`)) {
        router.delete(route('partai-politik.destroy', partai.id), {
            preserveScroll: true
        })
    }
}
</script>