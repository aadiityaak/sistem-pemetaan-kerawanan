<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Provinsi {
    id: number;
    nama: string;
}

interface Props {
    provinsiList: Provinsi[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'admin',
    is_active: true,
    provinsi_id: null as number | null,
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Add User" />

    <AppLayout title="Add User">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Add User</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Create a new user account</p>
                    </div>
                    <div>
                        <Link
                            :href="route('users.index')"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-50 focus:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back to Users
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">User Information</h3>
                </div>

                <form @submit.prevent="submit" class="space-y-6 p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                id="name"
                                type="text"
                                required
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Enter full name"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.email"
                                id="email"
                                type="email"
                                required
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Enter email address"
                            />
                            <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.email }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Password -->
                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password"
                                id="password"
                                type="password"
                                required
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Enter password"
                            />
                            <div v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Password must be at least 8 characters long</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                id="password_confirmation"
                                type="password"
                                required
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Confirm password"
                            />
                            <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.password_confirmation }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Role -->
                        <div>
                            <label for="role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                User Role <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.role"
                                id="role"
                                required
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="admin">👤 Admin Regional</option>
                                <option value="admin_vip">👁️ Admin VIP (View Only)</option>
                                <option value="super_admin">👑 Super Administrator</option>
                            </select>
                            <div v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.role }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Admin Regional dapat edit data di wilayahnya, Admin VIP hanya melihat semua data, Super Admin mengatur semua
                            </p>
                        </div>

                        <!-- Province Selection -->
                        <div>
                            <label for="provinsi_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Province </label>
                            <select
                                v-model="form.provinsi_id"
                                id="provinsi_id"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option :value="null">Select Province</option>
                                <option v-for="provinsi in props.provinsiList" :key="provinsi.id" :value="provinsi.id">
                                    {{ provinsi.nama }}
                                </option>
                            </select>
                            <div v-if="form.errors.provinsi_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.provinsi_id }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Admin Regional hanya dapat akses data provinsinya. Kosongkan untuk Super Admin dan Admin VIP (akses semua).
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-1">
                        <!-- Active Status -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Account Status </label>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="focus:ring-opacity-50 rounded border-gray-300 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ form.is_active ? '✅ Active Account' : '❌ Inactive Account' }}
                                    </span>
                                </label>
                            </div>
                            <div v-if="form.errors.is_active" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.is_active }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inactive users cannot log in to the system</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            :href="route('users.index')"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                            {{ form.processing ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
