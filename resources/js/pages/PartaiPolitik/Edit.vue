<template>
    <AppLayout :title="`Edit ${partaiPolitik.nama_partai}`">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit Partai Politik</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Perbarui data partai politik {{ partaiPolitik.nama_partai }}</p>
                    </div>
                    <Link
                        :href="route('partai-politik.show', partaiPolitik.id)"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="mx-auto max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <div class="space-y-6">
                            <!-- Current Images Preview -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Current Logo -->
                                <div v-if="partaiPolitik.logo_url" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Logo Saat Ini:</p>
                                    <img 
                                        :src="partaiPolitik.logo_url" 
                                        :alt="partaiPolitik.nama_partai"
                                        class="h-16 w-16 rounded-lg object-cover shadow-sm"
                                    />
                                </div>
                                
                                <!-- Current Chairman Photo -->
                                <div v-if="partaiPolitik.foto_ketua_url" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Foto Ketua Umum Saat Ini:</p>
                                    <img 
                                        :src="partaiPolitik.foto_ketua_url" 
                                        :alt="partaiPolitik.nama_ketua || 'Ketua Umum'"
                                        class="h-20 w-20 rounded-lg object-cover shadow-sm"
                                    />
                                    <p v-if="partaiPolitik.nama_ketua" class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ partaiPolitik.nama_ketua }}</p>
                                </div>
                            </div>

                            <!-- Nama Partai -->
                            <div>
                                <label for="nama_partai" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Partai <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="nama_partai"
                                    v-model="form.nama_partai"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="Masukkan nama partai politik"
                                    maxlength="100"
                                    required
                                />
                                <div v-if="form.errors.nama_partai" class="mt-1 text-sm text-red-600">{{ form.errors.nama_partai }}</div>
                            </div>

                            <!-- Singkatan -->
                            <div>
                                <label for="singkatan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Singkatan <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="singkatan"
                                    v-model="form.singkatan"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="Masukkan singkatan partai"
                                    maxlength="50"
                                    required
                                />
                                <div v-if="form.errors.singkatan" class="mt-1 text-sm text-red-600">{{ form.errors.singkatan }}</div>
                            </div>

                            <!-- Nomor Urut -->
                            <div>
                                <label for="nomor_urut" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nomor Urut <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="nomor_urut"
                                    v-model="form.nomor_urut"
                                    type="number"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="Masukkan nomor urut partai"
                                    min="1"
                                    required
                                />
                                <div v-if="form.errors.nomor_urut" class="mt-1 text-sm text-red-600">{{ form.errors.nomor_urut }}</div>
                            </div>

                            <!-- Nama Ketua Umum -->
                            <div>
                                <label for="nama_ketua" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Ketua Umum
                                </label>
                                <input
                                    id="nama_ketua"
                                    v-model="form.nama_ketua"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="Masukkan nama lengkap ketua umum"
                                    maxlength="100"
                                />
                                <div v-if="form.errors.nama_ketua" class="mt-1 text-sm text-red-600">{{ form.errors.nama_ketua }}</div>
                            </div>

                            <!-- Foto Ketua Umum Upload -->
                            <div>
                                <label for="foto_ketua" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ganti Foto Ketua Umum
                                </label>
                                <input
                                    id="foto_ketua"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,image/gif"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    @change="handleFotoKetuaChange"
                                />
                                <div v-if="form.errors.foto_ketua" class="mt-1 text-sm text-red-600">{{ form.errors.foto_ketua }}</div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Upload foto baru (format: JPG, PNG, GIF, maksimal 2MB) - Kosongkan jika tidak ingin mengganti
                                </p>
                            </div>

                            <!-- Logo Upload -->
                            <div>
                                <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Ganti Logo Partai
                                </label>
                                <input
                                    id="logo"
                                    type="file"
                                    accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml"
                                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    @change="handleLogoChange"
                                />
                                <div v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Upload logo baru (format: JPG, PNG, GIF, SVG, maksimal 2MB) - Kosongkan jika tidak ingin mengganti
                                </p>
                            </div>

                            <!-- Status Aktif -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status
                                </label>
                                <div class="mt-2">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.status_aktif"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Partai aktif di KPU
                                        </span>
                                    </label>
                                </div>
                                <div v-if="form.errors.status_aktif" class="mt-1 text-sm text-red-600">{{ form.errors.status_aktif }}</div>
                            </div>

                            <!-- Preview New Images -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Preview New Logo -->
                                <div v-if="logoPreview" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Preview Logo Baru:</p>
                                    <img 
                                        :src="logoPreview" 
                                        :alt="form.nama_partai"
                                        class="h-16 w-16 rounded-lg object-cover shadow-sm"
                                    />
                                </div>
                                
                                <!-- Preview New Chairman Photo -->
                                <div v-if="fotoKetuaPreview" class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">
                                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Preview Foto Ketua Baru:</p>
                                    <img 
                                        :src="fotoKetuaPreview" 
                                        :alt="form.nama_ketua || 'Ketua Umum'"
                                        class="h-20 w-20 rounded-lg object-cover shadow-sm"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end gap-3">
                            <Link
                                :href="route('partai-politik.show', partaiPolitik.id)"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Batal
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-blue-600/50"
                            >
                                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="stroke-current/25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="fill-current/75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Menyimpan...' : 'Perbarui Partai' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

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
}

interface Props {
    partaiPolitik: PartaiPolitik
}

const props = defineProps<Props>()

const logoError = ref(false)

const form = useForm({
    nama_partai: props.partaiPolitik.nama_partai,
    singkatan: props.partaiPolitik.singkatan,
    nomor_urut: props.partaiPolitik.nomor_urut,
    logo: null as File | null,
    nama_ketua: props.partaiPolitik.nama_ketua || '',
    foto_ketua: null as File | null,
    status_aktif: props.partaiPolitik.status_aktif
})

const logoPreview = ref<string | null>(null)
const fotoKetuaPreview = ref<string | null>(null)

const handleLogoChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    
    if (file) {
        form.logo = file
        
        // Create preview URL
        const reader = new FileReader()
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    } else {
        form.logo = null
        logoPreview.value = null
    }
}

const handleFotoKetuaChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    
    if (file) {
        form.foto_ketua = file
        
        // Create preview URL
        const reader = new FileReader()
        reader.onload = (e) => {
            fotoKetuaPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    } else {
        form.foto_ketua = null
        fotoKetuaPreview.value = null
    }
}

const submit = () => {
    form.transform(data => ({
        ...data,
        _method: 'PUT'
    })).post(route('partai-politik.update', props.partaiPolitik.id), {
        preserveScroll: true,
        forceFormData: true
    })
}
</script>