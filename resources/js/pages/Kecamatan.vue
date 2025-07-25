<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

defineProps<{ kecamatan: Array<any> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kecamatan', href: '/kecamatan' },
];
</script>

<template>
    <Head title="Kecamatan" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <h1 class="text-2xl font-bold mb-4">Daftar Kecamatan</h1>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode Kabupaten/Kota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="item in kecamatan" :key="item.kode_provinsi + '-' + item.kode_kabupaten_kota + '-' + item.kode">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_provinsi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_kabupaten_kota }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.nama }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
