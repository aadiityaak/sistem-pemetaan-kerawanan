<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

defineProps<{ provinsi: Array<any> }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Provinsi', href: '/provinsi' },
];
</script>

<template>
    <Head title="Provinsi" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <h1 class="text-2xl font-bold mb-4">Daftar Provinsi</h1>
            <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr v-for="item in provinsi" :key="item.kode">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.nama }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
