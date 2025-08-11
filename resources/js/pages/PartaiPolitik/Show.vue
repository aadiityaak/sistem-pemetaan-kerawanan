<template>
    <AppLayout :title="partaiPolitik.nama_partai">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ partaiPolitik.nama_partai }}</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Detail partai politik dan data jumlah suara</p>
                    </div>
                    <div class="flex gap-3">
                        <Link
                            :href="route('partai-politik.edit', partaiPolitik.id)"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Partai
                        </Link>
                        <Link
                            :href="route('partai-politik.index')"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Party Information -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Informasi Partai</h2>
                        
                        <!-- Logo -->
                        <div class="mb-4 text-center">
                            <div class="mx-auto h-24 w-24 rounded-lg bg-gray-100 p-2 dark:bg-gray-700">
                                <img 
                                    v-if="partaiPolitik.logo_url" 
                                    :src="partaiPolitik.logo_url" 
                                    :alt="partaiPolitik.nama_partai"
                                    class="h-full w-full rounded-lg object-cover"
                                />
                                <div v-else class="flex h-full items-center justify-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Nomor Urut -->
                            <div class="text-center">
                                <span class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-xl font-bold text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ partaiPolitik.nomor_urut }}
                                </span>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Nomor Urut</p>
                            </div>

                            <div class="border-t pt-4 dark:border-gray-700">
                                <!-- Nama Partai -->
                                <div class="mb-3">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Partai</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ partaiPolitik.nama_partai }}</dd>
                                </div>

                                <!-- Singkatan -->
                                <div class="mb-3">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Singkatan</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ partaiPolitik.singkatan }}
                                        </span>
                                    </dd>
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd class="text-sm">
                                        <span v-if="partaiPolitik.status_aktif" class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Aktif di KPU
                                        </span>
                                        <span v-else class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-800 dark:bg-red-900 dark:text-red-300">
                                            Tidak Aktif
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vote Data -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Data Jumlah Suara</h2>
                            <button
                                @click="showAddVoteModal = true"
                                class="inline-flex items-center rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                            >
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Data
                            </button>
                        </div>

                        <!-- Vote Data Table -->
                        <div v-if="partaiPolitik.jumlah_suara.length > 0" class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Tahun Pemilu</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Jumlah Suara</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-for="suara in partaiPolitik.jumlah_suara" :key="suara.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ suara.tahun_pemilu }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ suara.jumlah_suara.toLocaleString('id-ID') }} suara
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <button
                                                @click="deleteVoteData(suara)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada data suara</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan data jumlah suara untuk tahun pemilu tertentu.</p>
                            <div class="mt-6">
                                <button
                                    @click="showAddVoteModal = true"
                                    class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Data Suara
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Vote Modal -->
        <div v-if="showAddVoteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="showAddVoteModal = false"></div>
                
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle">&#8203;</span>
                
                <div class="inline-block transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6 sm:align-middle dark:bg-gray-800">
                    <form @submit.prevent="submitVoteData">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Tambah Data Jumlah Suara</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Tambahkan data jumlah suara untuk tahun pemilu tertentu
                            </p>
                        </div>
                        
                        <div class="mt-6 space-y-4">
                            <!-- Tahun Pemilu -->
                            <div>
                                <label for="tahun_pemilu" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Tahun Pemilu <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="tahun_pemilu"
                                    v-model="voteForm.tahun_pemilu"
                                    type="number"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="2024"
                                    min="1900"
                                    :max="new Date().getFullYear() + 10"
                                    required
                                />
                                <div v-if="voteForm.errors.tahun_pemilu" class="mt-1 text-sm text-red-600">{{ voteForm.errors.tahun_pemilu }}</div>
                            </div>

                            <!-- Jumlah Suara -->
                            <div>
                                <label for="jumlah_suara" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Jumlah Suara <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="jumlah_suara"
                                    v-model="voteForm.jumlah_suara"
                                    type="number"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="1000000"
                                    min="0"
                                    required
                                />
                                <div v-if="voteForm.errors.jumlah_suara" class="mt-1 text-sm text-red-600">{{ voteForm.errors.jumlah_suara }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="showAddVoteModal = false"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="voteForm.processing"
                                class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg v-if="voteForm.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ voteForm.processing ? 'Menyimpan...' : 'Simpan Data' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface JumlahSuara {
    id: number
    tahun_pemilu: number
    jumlah_suara: number
}

interface PartaiPolitik {
    id: number
    nama_partai: string
    singkatan: string
    nomor_urut: number
    logo_url: string | null
    status_aktif: boolean
    jumlah_suara: JumlahSuara[]
}

interface Props {
    partaiPolitik: PartaiPolitik
}

const props = defineProps<Props>()

const showAddVoteModal = ref(false)

const voteForm = useForm({
    tahun_pemilu: null as number | null,
    jumlah_suara: null as number | null
})

const submitVoteData = () => {
    voteForm.post(route('partai-politik.jumlah-suara.store', props.partaiPolitik.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAddVoteModal.value = false
            voteForm.tahun_pemilu = null
            voteForm.jumlah_suara = null
        }
    })
}

const deleteVoteData = (suara: JumlahSuara) => {
    if (confirm(`Apakah Anda yakin ingin menghapus data suara tahun ${suara.tahun_pemilu}?`)) {
        router.delete(route('jumlah-suara.destroy', suara.id), {
            preserveScroll: true
        })
    }
}
</script>