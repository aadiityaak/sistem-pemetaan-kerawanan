<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    color?: string;
    is_active: boolean;
    sort_order: number;
    monitoring_data_count?: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    color: string;
    is_active: boolean;
    sort_order: number;
    sub_categories_count?: number;
    monitoring_data_count?: number;
    created_at: string;
    updated_at: string;
    sub_categories?: SubCategory[];
}

const props = defineProps<{
    category: Category;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Data Monitoring',
        href: '/monitoring-data',
    },
    {
        title: 'Kategori',
        href: '/categories',
    },
    {
        title: 'Detail Kategori',
        href: `/categories/${props.category.id}`,
    },
];

// Helper functions
const getStatusLabel = (isActive: boolean): string => {
    return isActive ? 'Aktif' : 'Tidak Aktif';
};

const getStatusColor = (isActive: boolean): string => {
    return isActive ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

const formatDateTime = (dateString: string): string => {
    return new Date(dateString).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const printData = () => {
    if (typeof window !== 'undefined') {
        window.print();
    }
};
</script>

<template>
    <Head :title="`Detail: ${category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ category.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Detail kategori #{{ category.id }}</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="`/categories/${category.id}/edit`">
                        <Button size="sm">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Edit Kategori
                        </Button>
                    </Link>
                    <Link href="/categories">
                        <Button variant="outline" size="sm">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Main Information -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Basic Information -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Dasar</h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Name -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Kategori</label>
                                    <div class="text-gray-900 dark:text-white">{{ category.name }}</div>
                                </div>

                                <!-- Slug -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Slug</label>
                                    <div class="font-mono text-sm text-gray-900 dark:text-white">{{ category.slug }}</div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="category.description">
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</label>
                                <div class="whitespace-pre-wrap text-gray-900 dark:text-white">{{ category.description }}</div>
                            </div>

                            <!-- Visual Identity -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Icon & Color -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Icon & Warna</label>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg"
                                            :style="{ backgroundColor: category.color + '20', color: category.color }"
                                        >
                                            <span class="text-2xl">{{ category.icon || '📁' }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ category.icon || 'Tidak ada icon' }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ category.color }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Sort Order -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Status & Urutan</label>
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="getStatusColor(category.is_active)"
                                        >
                                            {{ getStatusLabel(category.is_active) }}
                                        </span>
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            Urutan: <span class="font-medium">{{ category.sort_order }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Statistik</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ category.sub_categories_count || 0 }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Sub Kategori</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ category.monitoring_data_count || 0 }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Data Monitoring</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ category.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ category.sort_order }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Urutan Tampil</div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-4 flex justify-center gap-3">
                            <Link :href="`/monitoring-data?category=${category.slug}`">
                                <Button variant="outline" size="sm">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Data Monitoring
                                </Button>
                            </Link>
                            <Link :href="`/sub-categories?category=${category.id}`">
                                <Button variant="outline" size="sm">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Sub Kategori
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <!-- Sub Categories -->
                    <div v-if="category.sub_categories && category.sub_categories.length > 0" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Daftar Sub Kategori</h3>

                        <div class="space-y-3">
                            <div v-for="subCategory in category.sub_categories" :key="subCategory.id" class="rounded-lg border border-gray-100 p-3 dark:border-gray-600">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-3 flex h-8 w-8 items-center justify-center rounded-lg"
                                            :style="{
                                                backgroundColor: (subCategory.color || category.color) + '20',
                                                color: subCategory.color || category.color,
                                            }"
                                        >
                                            <span class="text-sm">{{ subCategory.icon || '📄' }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ subCategory.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400" v-if="subCategory.description">
                                                {{ subCategory.description.length > 50 ? subCategory.description.substring(0, 50) + '...' : subCategory.description }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                            :class="getStatusColor(subCategory.is_active)"
                                        >
                                            {{ getStatusLabel(subCategory.is_active) }}
                                        </span>
                                        <div class="flex gap-1">
                                            <Link :href="`/sub-categories/${subCategory.id}`">
                                                <Button variant="outline" size="sm">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        />
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        />
                                                    </svg>
                                                </Button>
                                            </Link>
                                            <Link :href="`/sub-categories/${subCategory.id}/edit`">
                                                <Button variant="outline" size="sm">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                        />
                                                    </svg>
                                                </Button>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <Link href="/sub-categories/create">
                                <Button variant="outline" size="sm">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Sub Kategori Baru
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <!-- Empty Sub Categories State -->
                    <div v-else class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Sub Kategori</h3>
                        <div class="py-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h4 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada sub kategori</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kategori ini belum memiliki sub kategori</p>
                            <div class="mt-4">
                                <Link href="/sub-categories/create">
                                    <Button>
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah Sub Kategori Pertama
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Quick Info -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Informasi Cepat</h3>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">ID Kategori</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">#{{ category.id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Dibuat</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(category.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Diperbarui</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(category.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Preview Tampilan</h3>

                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-600">
                            <div class="flex items-center">
                                <div
                                    class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg"
                                    :style="{ backgroundColor: category.color + '20', color: category.color }"
                                >
                                    <span class="text-lg">{{ category.icon || '📁' }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ category.name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ category.description || 'Kategori untuk data monitoring' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Tampilan kategori ini dalam form dan filter</p>
                    </div>

                    <!-- Actions -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Aksi</h3>

                        <div class="space-y-3">
                            <Link :href="`/categories/${category.id}/edit`" class="block">
                                <Button class="w-full" variant="outline">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    Edit Kategori
                                </Button>
                            </Link>

                            <Button class="w-full" variant="outline" @click="printData">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                                    />
                                </svg>
                                Cetak Data
                            </Button>

                            <Link href="/categories" class="block">
                                <Button class="w-full" variant="outline">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Kembali ke Daftar
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>