<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    image_path?: string;
    image_url?: string;
    color: string;
    is_active: boolean;
    sort_order: number;
    sub_categories_count?: number;
    monitoring_data_count?: number;
    created_at: string;
    updated_at: string;
}

interface SubCategory {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    image_path?: string;
    image_url?: string;
    color?: string;
    is_active: boolean;
    sort_order: number;
    monitoring_data_count?: number;
    created_at: string;
    updated_at: string;
    category: Category;
}

const props = defineProps<{
    subCategory: SubCategory;
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
        title: 'Sub Kategori',
        href: '/sub-categories',
    },
    {
        title: 'Detail Sub Kategori',
        href: `/sub-categories/${props.subCategory.id}`,
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
    <Head :title="`Detail: ${subCategory.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ subCategory.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Detail sub kategori #{{ subCategory.id }}</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="`/sub-categories/${subCategory.id}/edit`">
                        <Button size="sm">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                            Edit Sub Kategori
                        </Button>
                    </Link>
                    <Link href="/sub-categories">
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
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Sub Kategori</label>
                                    <div class="text-gray-900 dark:text-white">{{ subCategory.name }}</div>
                                </div>

                                <!-- Slug -->
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Slug</label>
                                    <div class="font-mono text-sm text-gray-900 dark:text-white">{{ subCategory.slug }}</div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="subCategory.description">
                                <label class="mb-1 block text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</label>
                                <div class="whitespace-pre-wrap text-gray-900 dark:text-white">{{ subCategory.description }}</div>
                            </div>

                            <!-- Visual Identity -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Icon & Color -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Icon & Warna</label>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg"
                                            :style="subCategory.image_url ? '' : {
                                                backgroundColor: (subCategory.color || subCategory.category.color) + '20',
                                                color: subCategory.color || subCategory.category.color,
                                            }"
                                        >
                                            <img v-if="subCategory.image_url" :src="subCategory.image_url" alt="Subcategory icon" class="h-12 w-12 object-contain rounded-lg" />
                                            <span v-else class="text-2xl">{{ subCategory.icon || '📄' }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <span v-if="subCategory.image_url">Gambar kustom</span>
                                                <span v-else>{{ subCategory.icon || 'Tidak ada icon' }}</span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ subCategory.color || subCategory.category.color }}
                                                <span v-if="!subCategory.color">(dari kategori induk)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status & Sort Order -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-500 dark:text-gray-400">Status & Urutan</label>
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="getStatusColor(subCategory.is_active)"
                                        >
                                            {{ getStatusLabel(subCategory.is_active) }}
                                        </span>
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            Urutan: <span class="font-medium">{{ subCategory.sort_order }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Category Information -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Kategori Induk</h3>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div
                                    class="mr-3 flex h-12 w-12 items-center justify-center rounded-lg"
                                    :style="subCategory.category.image_url ? '' : { backgroundColor: subCategory.category.color + '20', color: subCategory.category.color }"
                                >
                                    <img v-if="subCategory.category.image_url" :src="subCategory.category.image_url" alt="Category icon" class="h-12 w-12 object-contain rounded-lg" />
                                    <span v-else class="text-xl">{{ subCategory.category.icon || '📁' }}</span>
                                </div>
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-white">{{ subCategory.category.name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ subCategory.category.slug }}</div>
                                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400" v-if="subCategory.category.description">
                                        {{ subCategory.category.description }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <Link :href="`/categories/${subCategory.category.id}`" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                    <Button variant="outline" size="sm">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        Lihat Kategori
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Statistik</h3>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ subCategory.monitoring_data_count || 0 }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Data Monitoring</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ subCategory.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Status</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ subCategory.sort_order }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Urutan Tampil</div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="mt-4 flex justify-center">
                            <Link :href="`/monitoring-data?category=${subCategory.category.slug}&subcategory=${subCategory.slug}`">
                                <Button variant="outline" size="sm">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    Lihat Data Monitoring
                                </Button>
                            </Link>
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
                                <span class="text-sm text-gray-500 dark:text-gray-400">ID Sub Kategori</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">#{{ subCategory.id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Dibuat</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(subCategory.created_at) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Diperbarui</span>
                                <span class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(subCategory.updated_at) }}</span>
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
                                    :style="subCategory.image_url ? '' : {
                                        backgroundColor: (subCategory.color || subCategory.category.color) + '20',
                                        color: subCategory.color || subCategory.category.color,
                                    }"
                                >
                                    <img v-if="subCategory.image_url" :src="subCategory.image_url" alt="Subcategory icon" class="h-10 w-10 object-contain rounded-lg" />
                                    <span v-else class="text-lg">{{ subCategory.icon || '📄' }}</span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ subCategory.name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ subCategory.description || 'Sub kategori dalam ' + subCategory.category.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Tampilan sub kategori ini dalam form dan filter</p>
                    </div>

                    <!-- Actions -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Aksi</h3>

                        <div class="space-y-3">
                            <Link :href="`/sub-categories/${subCategory.id}/edit`" class="block">
                                <Button class="w-full" variant="outline">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                    Edit Sub Kategori
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

                            <Link href="/sub-categories" class="block">
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