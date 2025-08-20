<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
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
    category: Category;
}

const props = defineProps<{
    subCategory: SubCategory;
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
        title: 'Edit Sub Kategori',
        href: `/sub-categories/${props.subCategory.id}/edit`,
    },
];

const form = useForm({
    category_id: props.subCategory.category_id || '',
    name: props.subCategory.name || '',
    slug: props.subCategory.slug || '',
    description: props.subCategory.description || '',
    icon: props.subCategory.icon || '',
    image: null,
    color: props.subCategory.color || '',
    is_active: props.subCategory.is_active !== undefined ? props.subCategory.is_active : true,
    sort_order: props.subCategory.sort_order || 0,
});

const submit = () => {
    // Use POST with _method spoofing for file uploads
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(`/sub-categories/${props.subCategory.id}`, {
        onSuccess: () => {
            // Redirect handled by controller
        },
    });
};

// Common icons for sub categories
const commonIcons = ['📄', '📊', '📈', '📋', '🔍', '⚡', '🔧', '🎯', '📝', '💼', '🏢', '🌍', '🔒', '⚠️', '✅', '❌', '⏰', '📞', '💻', '🗂️'];

const selectIcon = (icon: string) => {
    form.icon = icon;
};

// Helper function to get effective color
const getEffectiveColor = () => {
    if (form.color) return form.color;
    const parentCategory = props.categories.find((c) => c.id == form.category_id);
    return parentCategory?.color || '#3B82F6';
};

// Image preview state
const imagePreview = ref<string | null>(props.subCategory.image_url || null);
const hasExistingImage = ref<boolean>(!!props.subCategory.image_path);

const handleImageUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.image = file;

        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    hasExistingImage.value = false;
    // Reset file input
    const fileInput = document.getElementById('image-upload') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

const deleteExistingImage = async () => {
    if (confirm('Apakah Anda yakin ingin menghapus gambar ini?')) {
        try {
            const response = await fetch(`/sub-categories/${props.subCategory.id}/delete-image`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    Accept: 'application/json',
                },
            });

            if (response.ok) {
                hasExistingImage.value = false;
                imagePreview.value = null;
                alert('Gambar berhasil dihapus.');
            } else {
                alert('Gagal menghapus gambar.');
            }
        } catch (error) {
            console.error('Error deleting image:', error);
            alert('Terjadi kesalahan saat menghapus gambar.');
        }
    }
};
</script>

<template>
    <Head title="Edit Sub Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Sub Kategori</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Perbarui informasi sub kategori "{{ subCategory.name }}"</p>
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

                            <!-- Slug -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Slug <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    required
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="masukkan-slug-sub-kategori"
                                />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Slug digunakan untuk URL. Gunakan huruf kecil, angka, dan tanda hubung (-)
                                </p>
                                <div v-if="form.errors.slug" class="mt-1 text-sm text-red-500">{{ form.errors.slug }}</div>
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
                                    placeholder="Urutan tampil sub kategori"
                                />
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
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Warna Kustom </label>
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
                                        placeholder="#3B82F6 (kosongkan untuk warna kategori induk)"
                                    />
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Jika dikosongkan, akan menggunakan warna dari kategori induk
                                </p>
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
                                                backgroundColor: getEffectiveColor() + '20',
                                                borderColor: getEffectiveColor(),
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

                            <!-- Custom Image Upload -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Upload Gambar Kustom </label>
                                <div class="space-y-3">
                                    <!-- Existing Image Display -->
                                    <div v-if="hasExistingImage && !imagePreview" class="relative">
                                        <div
                                            class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-600 dark:bg-gray-700"
                                        >
                                            <div class="flex items-center gap-3">
                                                <img
                                                    v-if="subCategory.image_url"
                                                    :src="subCategory.image_url"
                                                    alt="Current image"
                                                    class="h-12 w-12 rounded object-cover"
                                                />
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Gambar saat ini</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Upload gambar baru untuk mengganti</p>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="deleteExistingImage"
                                                class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                Hapus
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Image Upload Area -->
                                    <div class="relative">
                                        <input id="image-upload" type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
                                        <label
                                            for="image-upload"
                                            class="flex h-32 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 transition-colors hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600"
                                        >
                                            <div v-if="!imagePreview" class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg
                                                    class="mb-4 h-8 w-8 text-gray-500 dark:text-gray-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                                    ></path>
                                                </svg>
                                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="font-semibold">Klik untuk upload</span> atau drag & drop
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF, SVG (MAX. 2MB)</p>
                                            </div>
                                            <div v-else class="relative flex h-full w-full items-center justify-center">
                                                <img :src="imagePreview" alt="Preview" class="max-h-full max-w-full rounded object-contain" />
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Remove New Image Button -->
                                    <div v-if="imagePreview && !hasExistingImage" class="flex justify-center">
                                        <button
                                            type="button"
                                            @click="removeImage"
                                            class="text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        >
                                            Hapus Gambar Baru
                                        </button>
                                    </div>

                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Gambar kustom akan digunakan sebagai prioritas utama. Jika tidak ada gambar, akan menggunakan emoji icon.
                                    </p>
                                </div>
                                <div v-if="form.errors.image" class="mt-1 text-sm text-red-500">{{ form.errors.image }}</div>
                            </div>

                            <!-- Preview -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Preview </label>
                                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="flex items-center">
                                        <div
                                            class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg"
                                            :style="
                                                imagePreview || (hasExistingImage && subCategory.image_url)
                                                    ? ''
                                                    : {
                                                          backgroundColor: getEffectiveColor() + '20',
                                                          color: getEffectiveColor(),
                                                      }
                                            "
                                        >
                                            <img v-if="imagePreview" :src="imagePreview" alt="Preview" class="h-10 w-10 rounded-lg object-cover" />
                                            <img
                                                v-else-if="hasExistingImage && subCategory.image_url"
                                                :src="subCategory.image_url"
                                                alt="Current"
                                                class="h-10 w-10 rounded-lg object-cover"
                                            />
                                            <span v-else class="text-lg">{{ form.icon || '📄' }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ form.name || 'Nama Sub Kategori' }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ form.description || 'Deskripsi sub kategori' }}
                                            </div>
                                            <div
                                                v-if="imagePreview || (hasExistingImage && subCategory.image_url)"
                                                class="text-xs text-blue-600 dark:text-blue-400"
                                            >
                                                Menggunakan gambar kustom
                                            </div>
                                            <div v-else-if="form.icon" class="text-xs text-green-600 dark:text-green-400">Menggunakan emoji icon</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Parent Category Info -->
                            <div v-if="form.category_id" class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <h4 class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Kategori Induk:</h4>
                                <div class="flex items-center" v-if="categories.find((c) => c.id == form.category_id)">
                                    <div
                                        class="mr-2 h-4 w-4 rounded"
                                        :style="{ backgroundColor: categories.find((c) => c.id == form.category_id)?.color }"
                                    ></div>
                                    <span class="text-sm text-gray-900 dark:text-white">
                                        {{ categories.find((c) => c.id == form.category_id)?.name }}
                                    </span>
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
                            {{ form.processing ? 'Menyimpan...' : 'Perbarui Sub Kategori' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
