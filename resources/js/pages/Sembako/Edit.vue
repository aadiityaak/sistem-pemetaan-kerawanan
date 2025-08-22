<template>
    <Head title="Edit Data Sembako" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Data Sembako</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui data harga sembako - {{ sembako.nama_komoditas }}</p>
            </div>

            <form @submit.prevent="submit">
                <div class="mx-auto max-w-2xl space-y-6">
                    <!-- Informasi Komoditas -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Komoditas</h3>
                        <div class="space-y-4">
                            <!-- Nama Komoditas -->
                            <div>
                                <label for="nama_komoditas" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Komoditas <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="nama_komoditas"
                                    v-model="form.nama_komoditas"
                                    type="text"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Contoh: Beras, Gula, Minyak Goreng"
                                    required
                                />
                                <div v-if="form.errors.nama_komoditas" class="mt-1 text-sm text-red-600">{{ form.errors.nama_komoditas }}</div>
                            </div>

                            <!-- Satuan -->
                            <div>
                                <label for="satuan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Satuan <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="satuan"
                                    v-model="form.satuan"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option value="">Pilih Satuan</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="liter">Liter</option>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="gram">Gram</option>
                                    <option value="ons">Ons</option>
                                    <option value="bungkus">Bungkus</option>
                                    <option value="botol">Botol</option>
                                    <option value="karung">Karung</option>
                                </select>
                                <div v-if="form.errors.satuan" class="mt-1 text-sm text-red-600">{{ form.errors.satuan }}</div>
                            </div>

                            <!-- Harga -->
                            <div>
                                <label for="harga" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Harga (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input
                                        id="harga"
                                        v-model="form.harga"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 pl-12 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="0"
                                        required
                                    />
                                </div>
                                <div v-if="form.errors.harga" class="mt-1 text-sm text-red-600">{{ form.errors.harga }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi dan Waktu -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Lokasi dan Waktu</h3>
                        <div class="space-y-4">
                            <!-- Kabupaten/Kota -->
                            <div>
                                <label for="kabupaten_kota_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kabupaten/Kota <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="kabupaten_kota_id"
                                    v-model="form.kabupaten_kota_id"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                >
                                    <option value="">Pilih Kabupaten/Kota</option>
                                    <optgroup v-for="provinsi in groupedKabupaten" :key="provinsi.nama" :label="provinsi.nama">
                                        <option v-for="kabupaten in provinsi.kabupaten" :key="kabupaten.id" :value="kabupaten.id">
                                            {{ kabupaten.nama }}
                                        </option>
                                    </optgroup>
                                </select>
                                <div v-if="form.errors.kabupaten_kota_id" class="mt-1 text-sm text-red-600">{{ form.errors.kabupaten_kota_id }}</div>
                            </div>

                            <!-- Komentar -->
                            <div>
                                <label for="tanggal_pencatatan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Komentar <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="tanggal_pencatatan"
                                    v-model="form.tanggal_pencatatan"
                                    type="date"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    required
                                />
                                <div v-if="form.errors.tanggal_pencatatan" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.tanggal_pencatatan }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Tambahan</h3>
                        <div class="space-y-4">
                            <!-- Keterangan -->
                            <div>
                                <label for="keterangan" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Keterangan </label>
                                <textarea
                                    id="keterangan"
                                    v-model="form.keterangan"
                                    rows="3"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Keterangan tambahan (opsional)"
                                ></textarea>
                                <div v-if="form.errors.keterangan" class="mt-1 text-sm text-red-600">{{ form.errors.keterangan }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-4">
                        <Link
                            :href="route('sembako.index')"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:bg-blue-600/50"
                        >
                            <span v-if="form.processing">Memperbarui...</span>
                            <span v-else>Perbarui Data</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';

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
    tanggal_pencatatan: string | Date; // Bisa berupa string ISO atau Date object
    keterangan?: string;
    kabupaten_kota: KabupatenKota;
}

const props = defineProps<{
    sembako: SembakoItem;
    kabupatenKota: KabupatenKota[];
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', url: '/dashboard' },
    { title: 'Data Sembako', url: '/sembako' },
    { title: 'Edit Data', url: '#' },
];

// Format tanggal untuk input date HTML (YYYY-MM-DD)
const formatDateForInput = (dateValue: string | Date) => {
    if (!dateValue) return '';
    try {
        let date: Date;
        if (dateValue instanceof Date) {
            date = dateValue;
        } else if (typeof dateValue === 'string') {
            date = new Date(dateValue);
        } else {
            return '';
        }

        // Check if date is valid
        if (isNaN(date.getTime())) {
            console.warn('Invalid date:', dateValue);
            return '';
        }

        return date.toISOString().split('T')[0];
    } catch (error) {
        console.warn('Error formatting date:', dateValue, error);
        return '';
    }
};

const formattedDate = formatDateForInput(props.sembako.tanggal_pencatatan);

const form = useForm({
    nama_komoditas: props.sembako.nama_komoditas,
    satuan: props.sembako.satuan,
    harga: props.sembako.harga,
    kabupaten_kota_id: props.sembako.kabupaten_kota_id,
    tanggal_pencatatan: formattedDate,
    keterangan: props.sembako.keterangan || '',
});

const groupedKabupaten = computed(() => {
    const grouped = props.kabupatenKota.reduce(
        (acc, kabupaten) => {
            const provinsiNama = kabupaten.provinsi.nama;
            if (!acc[provinsiNama]) {
                acc[provinsiNama] = {
                    nama: provinsiNama,
                    kabupaten: [],
                };
            }
            acc[provinsiNama].kabupaten.push(kabupaten);
            return acc;
        },
        {} as Record<string, { nama: string; kabupaten: KabupatenKota[] }>,
    );

    return Object.values(grouped).sort((a, b) => a.nama.localeCompare(b.nama));
});

const submit = () => {
    form.put(route('sembako.update', props.sembako.id));
};
</script>
