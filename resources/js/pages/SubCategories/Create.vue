<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
}

const props = defineProps<{
    categories: Category[];
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
        title: 'Tambah Sub Kategori',
        href: '/sub-categories/create',
    },
];

const form = useForm({
    category_id: '',
    name: '',
    description: '',
    icon: '',
    color: '',
    is_active: true,
    sort_order: '',
});

const selectedCategory = ref<Category | null>(null);

// Watch for category changes to update color
watch(
    () => form.category_id,
    (newCategoryId) => {
        if (newCategoryId) {
            selectedCategory.value = props.categories.find((c) => c.id === Number(newCategoryId)) || null;
            if (selectedCategory.value && !form.color) {
                form.color = selectedCategory.value.color;
            }
        } else {
            selectedCategory.value = null;
        }
    },
);

const submit = () => {
    form.post('/sub-categories', {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

// Common icons for sub categories
const commonIcons = ['📄', '📋', '📊', '📈', '📉', '🔍', '⚡', '🔧', '⚙️', '🎯', '📌', '📎', '🔗', '💡', '🚨', '⚠️', '✅', '❌', '🔒', '🔓'];

const selectIcon = (icon: string) => {
    form.icon = icon;
};
</script>

<template>
    <Head title="Tambah Sub Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Sub Kategori</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Buat sub kategori baru untuk mengelompokkan data monitoring lebih spesifik
                </p>
            </div>

            <!-- Form -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Category -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kategori Induk <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.category_id"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Pilih Kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-500">{{ form.errors.category_id }}</div>
                            </div>

                            <!-- Name -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nama Sub Kategori <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Masukkan nama sub kategori"
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Deskripsi </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Masukkan deskripsi sub kategori (opsional)"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</div>
                            </div>

                            <!-- Sort Order -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Urutan Tampil </label>
                                <input
                                    v-model="form.sort_order"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Kosongkan untuk otomatis"
                                />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Biarkan kosong untuk menambahkan di urutan terakhir dalam kategori
                                </p>
                                <div v-if="form.errors.sort_order" class="mt-1 text-sm text-red-500">{{ form.errors.sort_order }}</div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="focus:ring-opacity-50 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                    />
                                    <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span>
                                </label>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sub kategori aktif akan ditampilkan dalam form dan filter</p>
                                <div v-if="form.errors.is_active" class="mt-1 text-sm text-red-500">{{ form.errors.is_active }}</div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Color -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Warna (Opsional) </label>
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-12 cursor-pointer rounded border border-gray-300 dark:border-gray-600"
                                    />
                                    <input
                                        v-model="form.color"
                                        type="text"
                                        class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="Kosongkan untuk menggunakan warna kategori"
                                    />
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jika kosong, akan menggunakan warna dari kategori induk</p>
                                <div v-if="form.errors.color" class="mt-1 text-sm text-red-500">{{ form.errors.color }}</div>
                            </div>

                            <!-- Icon -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Icon </label>
                                <div class="space-y-3">
                                    <!-- Selected Icon Preview -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600"
                                            :style="{
                                                backgroundColor: (form.color || selectedCategory?.color || '#6B7280') + '20',
                                                borderColor: form.color || selectedCategory?.color || '#6B7280',
                                            }"
                                        >
                                            <span class="text-2xl">{{ form.icon || '📄' }}</span>
                                        </div>
                                        <input
                                            v-model="form.icon"
                                            type="text"
                                            class="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            placeholder="Masukkan emoji atau karakter"
                                        />
                                    </div>

                                    <!-- Common Icons -->
                                    <div>
                                        <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Pilih icon umum:</p>
                                        <div class="grid grid-cols-10 gap-2">
                                            <button
                                                v-for="icon in commonIcons"
                                                :key="icon"
                                                type="button"
                                                @click="selectIcon(icon)"
                                                class="flex h-8 w-8 items-center justify-center rounded border text-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                                                :class="
                                                    form.icon === icon
                                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900'
                                                        : 'border-gray-300 dark:border-gray-600'
                                                "
                                            >
                                                {{ icon }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.errors.icon" class="mt-1 text-sm text-red-500">{{ form.errors.icon }}</div>
                            </div>

                            <!-- Selected Category Info -->
                            <div v-if="selectedCategory">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Kategori yang Dipilih </label>
                                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg"
                                            :style="{ backgroundColor: selectedCategory.color + '20', color: selectedCategory.color }"
                                        >
                                            <span class="text-lg">{{ selectedCategory.icon || '📁' }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ selectedCategory.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ selectedCategory.color }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview -->
                            <div v-if="selectedCategory">
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Preview </label>
                                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg"
                                            :style="{
                                                backgroundColor: (form.color || selectedCategory.color) + '20',
                                                color: form.color || selectedCategory.color,
                                            }"
                                        >
                                            <span class="text-lg">{{ form.icon || '📄' }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ form.name || 'Nama Sub Kategori' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ selectedCategory.name }} → {{ form.description || 'Deskripsi sub kategori' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Button type="button" variant="outline" @click="$inertia.visit('/sub-categories')"> Batal </Button>
                        <Button type="submit" :disabled="form.processing">
                            <svg v-if="form.processing" class="mr-2 -ml-1 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Sub Kategori' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
