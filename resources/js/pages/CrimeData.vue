<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

defineProps<{ crimeData: any }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Kriminal', href: '/crime-data' },
];
</script>

<template>
    <Head title="Data Kriminal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <h1 class="text-2xl font-bold mb-4">Daftar Data Kriminal</h1>
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
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr v-for="item in crimeData.data" :key="item.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_provinsi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_kabupaten_kota }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_kecamatan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.jenis_kriminal }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.deskripsi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.latitude }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.longitude }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template> 