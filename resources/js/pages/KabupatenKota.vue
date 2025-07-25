<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

defineProps<{ kabupatenKota: Array<any> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Kabupaten/Kota', href: '/kabupaten-kota' },
];
</script>

<template>
    <Head title="Kabupaten/Kota" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <h1 class="text-2xl font-bold mb-4">Daftar Kabupaten/Kota</h1>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr v-for="item in kabupatenKota" :key="item.provinsi_id + '-' + item.id">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.provinsi_id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.nama }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
