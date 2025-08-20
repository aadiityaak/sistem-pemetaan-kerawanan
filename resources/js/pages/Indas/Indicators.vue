<template>
    <AppLayout title="INDAS - Indicator Types">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="sm:flex sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Indicator Types</h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Configure economic, tourism, and social indicators with their weights
                            </p>
                        </div>
                        <div class="mt-4 flex space-x-3 sm:mt-0 sm:ml-4">
                            <button
                                @click="showAddModal = true"
                                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900"
                            >
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Add Indicator
                            </button>
                            <Link
                                :href="route('indas.index')"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                            >
                                Back to Dashboard
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Indicators by Category -->
                <div class="space-y-8">
                    <!-- Economic Indicators -->
                    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center">
                                <CurrencyDollarIcon class="mr-3 h-6 w-6 text-green-500" />
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Economic Indicators</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ (indicators.ekonomi || []).length }} indicators configured
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div v-if="!(indicators.ekonomi || []).length" class="px-6 py-8 text-center">
                                <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No economic indicators</h3>
                                <p class="mt-1 text-sm text-gray-500">Start by adding your first economic indicator.</p>
                            </div>

                            <div v-for="indicator in indicators.ekonomi || []" :key="indicator.id" class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ indicator.name }}
                                            </h4>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
                                                >
                                                    {{ indicator.unit }}
                                                </span>
                                                <button
                                                    @click="editIndicator(indicator)"
                                                    class="p-1 text-gray-400 transition-colors hover:text-blue-500 dark:text-gray-500 dark:hover:text-blue-400"
                                                    title="Edit indicator"
                                                >
                                                    <PencilIcon class="h-4 w-4" />
                                                </button>
                                                <button
                                                    @click="confirmDelete(indicator)"
                                                    class="p-1 text-gray-400 transition-colors hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400"
                                                    title="Delete indicator"
                                                >
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="indicator.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ indicator.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tourism Indicators -->
                    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center">
                                <BuildingOffice2Icon class="mr-3 h-6 w-6 text-blue-500" />
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tourism Indicators</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ (indicators.pariwisata || []).length }} indicators configured
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div v-if="!(indicators.pariwisata || []).length" class="px-6 py-8 text-center">
                                <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tourism indicators</h3>
                                <p class="mt-1 text-sm text-gray-500">Start by adding your first tourism indicator.</p>
                            </div>

                            <div v-for="indicator in indicators.pariwisata || []" :key="indicator.id" class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ indicator.name }}
                                            </h4>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
                                                >
                                                    {{ indicator.unit }}
                                                </span>
                                                <button
                                                    @click="editIndicator(indicator)"
                                                    class="p-1 text-gray-400 transition-colors hover:text-blue-500 dark:text-gray-500 dark:hover:text-blue-400"
                                                    title="Edit indicator"
                                                >
                                                    <PencilIcon class="h-4 w-4" />
                                                </button>
                                                <button
                                                    @click="confirmDelete(indicator)"
                                                    class="p-1 text-gray-400 transition-colors hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400"
                                                    title="Delete indicator"
                                                >
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="indicator.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ indicator.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Indicators -->
                    <div class="overflow-hidden bg-white shadow sm:rounded-lg dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center">
                                <UsersIcon class="mr-3 h-6 w-6 text-purple-500" />
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Social Indicators</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ (indicators.sosial || []).length }} indicators configured
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            <div v-if="!(indicators.sosial || []).length" class="px-6 py-8 text-center">
                                <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No social indicators</h3>
                                <p class="mt-1 text-sm text-gray-500">Start by adding your first social indicator.</p>
                            </div>

                            <div v-for="indicator in indicators.sosial || []" :key="indicator.id" class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ indicator.name }}
                                            </h4>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-900/20 dark:text-gray-400"
                                                >
                                                    {{ indicator.unit }}
                                                </span>
                                                <button
                                                    @click="editIndicator(indicator)"
                                                    class="p-1 text-gray-400 transition-colors hover:text-blue-500 dark:text-gray-500 dark:hover:text-blue-400"
                                                    title="Edit indicator"
                                                >
                                                    <PencilIcon class="h-4 w-4" />
                                                </button>
                                                <button
                                                    @click="confirmDelete(indicator)"
                                                    class="p-1 text-gray-400 transition-colors hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400"
                                                    title="Delete indicator"
                                                >
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="indicator.description" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ indicator.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Indicator Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 h-full w-full overflow-y-auto bg-gray-600/50" @click="closeModal">
            <div class="relative top-20 mx-auto w-96 rounded-md border bg-white p-5 shadow-lg dark:bg-gray-800" @click.stop>
                <div class="mt-3">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Indicator</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Category </label>
                            <select
                                id="category"
                                v-model="form.category"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select Category</option>
                                <option value="ekonomi">Economic</option>
                                <option value="pariwisata">Tourism</option>
                                <option value="sosial">Social</option>
                            </select>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Indicator Name </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                maxlength="255"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., Number of Shops, Tourist Visits"
                            />
                        </div>

                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Unit of Measurement </label>
                            <input
                                id="unit"
                                v-model="form.unit"
                                type="text"
                                required
                                maxlength="50"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., units, visitors, %"
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description (Optional)
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Brief description of this indicator..."
                            ></textarea>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="closeModal"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                            >
                                {{ isSubmitting ? 'Adding...' : 'Add Indicator' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Indicator Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 h-full w-full overflow-y-auto bg-gray-600/50" @click="closeEditModal">
            <div class="relative top-20 mx-auto w-96 rounded-md border bg-white p-5 shadow-lg dark:bg-gray-800" @click.stop>
                <div class="mt-3">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Edit Indicator</h3>
                        <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                    <form @submit.prevent="updateIndicator" class="space-y-4">
                        <div>
                            <label for="edit-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Category </label>
                            <select
                                id="edit-category"
                                v-model="form.category"
                                required
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select category</option>
                                <option value="ekonomi">Economic</option>
                                <option value="pariwisata">Tourism</option>
                                <option value="sosial">Social</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Name </label>
                            <input
                                id="edit-name"
                                v-model="form.name"
                                type="text"
                                required
                                maxlength="255"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., Number of Schools"
                            />
                        </div>
                        <div>
                            <label for="edit-unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Unit </label>
                            <input
                                id="edit-unit"
                                v-model="form.unit"
                                type="text"
                                required
                                maxlength="50"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., units, visitors, %"
                            />
                        </div>
                        <div>
                            <label for="edit-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description (Optional)
                            </label>
                            <textarea
                                id="edit-description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="Brief description of this indicator..."
                            />
                        </div>
                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="closeEditModal"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                            >
                                {{ isSubmitting ? 'Updating...' : 'Update Indicator' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="showDeleteModal && indicatorToDelete"
            class="fixed inset-0 z-50 h-full w-full overflow-y-auto bg-gray-600/50"
            @click="closeDeleteModal"
        >
            <div class="relative top-20 mx-auto w-96 rounded-md border bg-white p-5 shadow-lg dark:bg-gray-800" @click.stop>
                <div class="mt-3">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Delete Indicator</h3>
                        <button @click="closeDeleteModal" class="text-gray-400 hover:text-gray-600">
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">Are you sure you want to delete this indicator?</p>
                        <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ indicatorToDelete.name }}</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Category: {{ indicatorToDelete.category }} • Unit: {{ indicatorToDelete.unit }}
                            </p>
                        </div>
                        <p class="mt-2 text-xs text-red-600 dark:text-red-400">
                            <strong>Warning:</strong> This action cannot be undone. The indicator will be permanently deleted.
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button
                            type="button"
                            @click="closeDeleteModal"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteIndicator"
                            :disabled="isDeleting"
                            class="rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                        >
                            {{ isDeleting ? 'Deleting...' : 'Delete' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    BuildingOffice2Icon,
    ChartBarIcon,
    CurrencyDollarIcon,
    PencilIcon,
    PlusIcon,
    TrashIcon,
    UsersIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

interface Indicator {
    id: number;
    name: string;
    category: string;
    unit: string;
    description?: string;
}

defineProps<{
    indicators: {
        ekonomi?: Indicator[];
        pariwisata?: Indicator[];
        sosial?: Indicator[];
    };
}>();

const showAddModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const indicatorToDelete = ref<Indicator | null>(null);
const indicatorToEdit = ref<Indicator | null>(null);

const form = reactive({
    category: '',
    name: '',
    unit: '',
    description: '',
});

const closeModal = () => {
    showAddModal.value = false;
    resetForm();
};

const closeEditModal = () => {
    showEditModal.value = false;
    indicatorToEdit.value = null;
    resetForm();
};

const resetForm = () => {
    form.category = '';
    form.name = '';
    form.unit = '';
    form.description = '';
};

const submitForm = async () => {
    isSubmitting.value = true;

    try {
        await router.post(
            route('indas.indicators.store'),
            {
                category: form.category,
                name: form.name,
                unit: form.unit,
                description: form.description || null,
            },
            {
                onSuccess: () => {
                    closeModal();
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    } catch (error) {
        console.error('Error adding indicator:', error);
        isSubmitting.value = false;
    }
};

const editIndicator = (indicator: Indicator) => {
    indicatorToEdit.value = indicator;
    form.category = indicator.category;
    form.name = indicator.name;
    form.unit = indicator.unit;
    form.description = indicator.description || '';
    showEditModal.value = true;
};

const updateIndicator = async () => {
    if (!indicatorToEdit.value) return;

    isSubmitting.value = true;
    try {
        await router.put(
            route('indas.indicators.update', indicatorToEdit.value.id),
            {
                category: form.category,
                name: form.name,
                unit: form.unit,
                description: form.description || null,
            },
            {
                onSuccess: () => {
                    closeEditModal();
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    } catch (error) {
        console.error('Error updating indicator:', error);
        isSubmitting.value = false;
    }
};

const confirmDelete = (indicator: Indicator) => {
    indicatorToDelete.value = indicator;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    indicatorToDelete.value = null;
};

const deleteIndicator = async () => {
    if (!indicatorToDelete.value) return;

    isDeleting.value = true;

    try {
        await router.delete(route('indas.indicators.delete', indicatorToDelete.value.id), {
            onSuccess: () => {
                closeDeleteModal();
            },
            onFinish: () => {
                isDeleting.value = false;
            },
        });
    } catch (error) {
        console.error('Error deleting indicator:', error);
        isDeleting.value = false;
    }
};
</script>
