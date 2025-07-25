<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

defineProps<{ crimeData: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
];

function deleteCrime(id: number) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        router.delete(`/crime-data/${id}`);
    }
}
</script>

<template>
    <Head title="Data Kriminal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Daftar Data Kriminal</h1>
                <a
                    href="/crime-data/create"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                >Tambah Data</a>
            </div>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kab/Kota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kecamatan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Jenis Kriminal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Latitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Longitude</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr v-for="item in crimeData.data" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.provinsi?.nama || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kabupaten_kota?.nama || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kecamatan?.nama || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ item.jenis_kriminal }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate">{{ item.deskripsi || '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ parseFloat(item.latitude).toFixed(6) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ parseFloat(item.longitude).toFixed(6) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                                <a :href="`/crime-data/${item.id}/edit`" class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</a>
                                <form :action="`/crime-data/${item.id}`" method="POST" @submit.prevent="deleteCrime(item.id)">
                                    <input type="hidden" name="_method" value="DELETE" />
                                    <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pesan jika tidak ada data -->
                <div v-if="!crimeData.data || crimeData.data.length === 0" class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">Belum ada data kriminal yang tersimpan.</p>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="crimeData.data && crimeData.data.length > 0" class="flex justify-between items-center mt-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Menampilkan {{ crimeData.from || 0 }} - {{ crimeData.to || 0 }} dari {{ crimeData.total || 0 }} data
                </div>
                <div class="flex gap-2">
                    <a v-if="crimeData.prev_page_url" 
                       :href="crimeData.prev_page_url" 
                       class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                        Sebelumnya
                    </a>
                    <a v-if="crimeData.next_page_url" 
                       :href="crimeData.next_page_url" 
                       class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">
                        Selanjutnya
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 