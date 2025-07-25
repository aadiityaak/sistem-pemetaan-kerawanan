<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';

defineProps<{ kecamatan: any }>();

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
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode Provinsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode Kabupaten/Kota</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                        <tr v-for="item in kecamatan.data" :key="item.kode_provinsi + '-' + item.kode_kabupaten_kota + '-' + item.kode">
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_provinsi }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode_kabupaten_kota }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.kode }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ item.nama }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-4">
                <div>
                    Menampilkan {{ kecamatan.from }} - {{ kecamatan.to }} dari {{ kecamatan.total }} kecamatan
                </div>
                <div class="flex gap-2">
                    <Link v-if="kecamatan.prev_page_url" :href="kecamatan.prev_page_url" preserve-scroll class="px-3 py-1 rounded border bg-zinc-100 dark:bg-zinc-800">&laquo; Prev</Link>
                    <span>Halaman {{ kecamatan.current_page }} / {{ kecamatan.last_page }}</span>
                    <Link v-if="kecamatan.next_page_url" :href="kecamatan.next_page_url" preserve-scroll class="px-3 py-1 rounded border bg-zinc-100 dark:bg-zinc-800">Next &raquo;</Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
