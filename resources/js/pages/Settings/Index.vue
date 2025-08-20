<template>
    <AppLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden border border-gray-200 bg-white shadow-sm sm:rounded-lg dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Aplikasi</h2>
                        </div>

                        <div v-if="!canManageSettings" class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Akses Terbatas</h3>
                                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p>
                                            Anda tidak memiliki izin untuk mengubah pengaturan aplikasi. Silakan hubungi Super Admin jika perlu
                                            melakukan perubahan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- View-only mode for Admin VIP -->
                        <div v-if="formsReady && !canManageSettings">
                            <div v-for="(groupSettings, group) in settings" :key="group" class="mb-8">
                                <h3 class="mb-4 text-lg font-semibold text-gray-800 capitalize dark:text-gray-200">{{ group }}</h3>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div
                                        v-for="setting in groupSettings"
                                        :key="setting.key"
                                        class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-600 dark:bg-gray-700/50"
                                    >
                                        <div class="mb-3 flex items-start justify-between">
                                            <div class="flex-1">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ setting.label }}
                                                </label>
                                                <p v-if="setting.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ setting.description }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Read-only display of setting values -->
                                        <div class="mt-2">
                                            <div
                                                v-if="setting.type === 'text' || setting.type === 'email' || setting.type === 'url'"
                                                class="block w-full rounded-md border-gray-300 bg-gray-100 p-2 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                            >
                                                {{ setting.value || '-' }}
                                            </div>
                                            <div
                                                v-else-if="setting.type === 'textarea'"
                                                class="block w-full rounded-md border-gray-300 bg-gray-100 p-2 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300"
                                            >
                                                {{ setting.value || '-' }}
                                            </div>
                                            <div v-else-if="setting.type === 'boolean'" class="flex items-center">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                    :class="
                                                        setting.value === '1'
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                                    "
                                                >
                                                    {{ setting.value === '1' ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="saveAllSettings" v-if="formsReady && canManageSettings">
                            <div v-for="(groupSettings, group) in settings" :key="group" class="mb-8">
                                <h3 class="mb-4 text-lg font-semibold text-gray-800 capitalize dark:text-gray-200">{{ group }}</h3>

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div
                                        v-for="setting in groupSettings"
                                        :key="setting.key"
                                        class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700"
                                    >
                                        <div class="mb-3 flex items-start justify-between">
                                            <div class="flex-1">
                                                <label :for="setting.key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ setting.label }}
                                                </label>
                                                <p v-if="setting.description" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ setting.description }}
                                                </p>
                                            </div>
                                        </div>

                                        <div v-if="setting.type === 'text'" class="mt-2">
                                            <input
                                                :id="setting.key"
                                                v-model="forms[setting.key].value"
                                                type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :placeholder="setting.value || ''"
                                            />
                                        </div>

                                        <div v-else-if="setting.type === 'password'" class="mt-2">
                                            <input
                                                :id="setting.key"
                                                v-model="forms[setting.key].value"
                                                type="password"
                                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :placeholder="setting.value ? '••••••••' : 'Enter API key'"
                                            />
                                        </div>

                                        <div v-else-if="setting.type === 'number'" class="mt-2">
                                            <input
                                                :id="setting.key"
                                                v-model="forms[setting.key].value"
                                                type="number"
                                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :placeholder="setting.value || ''"
                                            />
                                        </div>

                                        <div v-else-if="setting.type === 'boolean'" class="mt-2">
                                            <label class="inline-flex items-center">
                                                <input
                                                    :id="setting.key"
                                                    v-model="forms[setting.key].value"
                                                    type="checkbox"
                                                    :checked="forms[setting.key].value === 'true'"
                                                    @change="forms[setting.key].value = $event.target.checked ? 'true' : 'false'"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                                                />
                                                <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">Aktif</span>
                                            </label>
                                        </div>

                                        <div v-else-if="setting.type === 'image'" class="mt-2">
                                            <div v-if="imagePreviews[setting.key] || setting.value" class="mb-2">
                                                <img
                                                    :src="imagePreviews[setting.key] || setting.value || ''"
                                                    alt="Current image"
                                                    class="h-16 w-16 rounded border border-gray-200 object-cover dark:border-gray-600"
                                                />
                                            </div>
                                            <input
                                                :id="setting.key"
                                                @change="handleFileChange(setting.key, $event)"
                                                type="file"
                                                accept="image/*,image/x-icon"
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 dark:text-gray-400 dark:file:bg-blue-900 dark:file:text-blue-300 dark:hover:file:bg-blue-800"
                                            />
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: JPG, PNG, GIF, ICO, SVG. Max: 2MB</p>
                                        </div>

                                        <div v-else class="mt-2">
                                            <textarea
                                                :id="setting.key"
                                                v-model="forms[setting.key].value"
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-4 py-3 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                                :placeholder="setting.value || ''"
                                            ></textarea>
                                        </div>

                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-medium">Key:</span> {{ setting.key }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Single Save Button -->
                            <div class="mt-8 flex justify-center">
                                <button
                                    type="submit"
                                    :disabled="isProcessing"
                                    class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-6 py-3 text-sm font-semibold tracking-widest text-white uppercase ring-blue-300 transition duration-150 ease-in-out hover:bg-blue-700 focus:border-blue-900 focus:ring focus:outline-none active:bg-blue-900 disabled:opacity-25"
                                >
                                    <svg
                                        v-if="isProcessing"
                                        class="mr-3 -ml-1 h-4 w-4 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    {{ isProcessing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
                                </button>
                            </div>
                        </form>

                        <!-- Loading state while forms are initializing -->
                        <div v-else class="flex items-center justify-center p-8">
                            <div class="h-8 w-8 animate-spin rounded-full border-b-2 border-blue-600"></div>
                            <span class="ml-3 text-sm text-gray-500 dark:text-gray-400">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';

interface Setting {
    key: string;
    value: string | null;
    type: string;
    group: string;
    label: string;
    description: string | null;
}

interface Props {
    settings: Record<string, Setting[]>;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
}

const props = defineProps<Props>();

// Permission check - only Super Admin and Admin can manage settings
const page = usePage();
const currentUser = computed(() => page.props.auth.user as User);
const canManageSettings = computed(() => {
    const userRole = currentUser.value.role;
    return userRole === 'super_admin' || userRole === 'admin';
});

// Redirect Admin VIP to dashboard if they somehow access this page
if (!canManageSettings.value && currentUser.value.role === 'admin_vip') {
    router.get('/dashboard');
}

// Create ref for forms and state
const forms = ref<Record<string, any>>({});
const formsReady = ref(false);
const isProcessing = ref(false);
const imagePreviews = ref<Record<string, string>>({});

// Initialize forms when component mounts
onMounted(async () => {
    await nextTick();

    const formMap: Record<string, any> = {};
    Object.values(props.settings)
        .flat()
        .forEach((setting) => {
            formMap[setting.key] = useForm({
                value: setting.value || '',
                file: null as File | null,
            });
        });

    forms.value = formMap;
    formsReady.value = true;
});

const handleFileChange = (key: string, event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];
        forms.value[key].file = file;

        // Create preview URL for the selected image
        const reader = new FileReader();
        reader.onload = (e) => {
            if (e.target?.result) {
                imagePreviews.value[key] = e.target.result as string;
            }
        };
        reader.readAsDataURL(file);
    }
};

const saveAllSettings = async () => {
    isProcessing.value = true;

    try {
        // Get all settings that need to be updated
        const allSettings = Object.values(props.settings).flat();

        for (const setting of allSettings) {
            const form = forms.value[setting.key];
            if (!form) continue;

            // Check if the setting has changes
            const hasTextChange = form.value !== (setting.value || '');
            const hasFileChange = form.file !== null;

            // Debug: Log form data before submission
            if (hasFileChange) {
                console.log('Submitting file for:', setting.key, {
                    file: form.file,
                    fileName: form.file?.name,
                    fileSize: form.file?.size,
                    fileType: form.file?.type,
                });
            }

            if (hasTextChange || hasFileChange) {
                // Submit this setting update
                await new Promise<void>((resolve, reject) => {
                    form.post(route('settings.update', setting.key), {
                        preserveScroll: true,
                        forceFormData: true,
                        onSuccess: () => {
                            console.log('Success updating:', setting.key);
                            // Reset file input if it was a file upload
                            if (form.file) {
                                form.file = null;
                                const fileInput = document.getElementById(setting.key) as HTMLInputElement;
                                if (fileInput && fileInput.type === 'file') {
                                    fileInput.value = '';
                                }
                                // Clear preview
                                delete imagePreviews.value[setting.key];
                            }
                            resolve();
                        },
                        onError: (errors: any) => {
                            console.error('Error updating:', setting.key, errors);
                            reject(new Error(`Failed to update ${setting.key}`));
                        },
                    });
                });
            }
        }

        // Refresh the page to show updated values
        router.reload();
    } catch (error) {
        console.error('Error updating settings:', error);
    } finally {
        isProcessing.value = false;
    }
};
</script>
