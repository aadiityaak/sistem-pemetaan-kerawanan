<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    last_login_at?: string;
    created_at: string;
    updated_at: string;
    email_verified_at?: string;
}

const props = defineProps<{
    user: User;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    is_active: props.user.is_active,
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head :title="`Edit User: ${user.name}`" />

    <AppLayout :title="`Edit User: ${user.name}`">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit User</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Update user account information</p>
                    </div>
                    <div class="flex space-x-3">
                        <Link
                            :href="route('users.show', user.id)"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-50 focus:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View User
                        </Link>
                        <Link
                            :href="route('users.index')"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-50 focus:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
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
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update the user's account details below</p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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

                    <!-- Password Section -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Change Password</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Leave password fields empty to keep the current password</p>
                        
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    New Password
                                </label>
                                <input
                                    v-model="form.password"
                                    id="password"
                                    type="password"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter new password"
                                />
                                <div v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.password }}
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Password must be at least 8 characters long
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Confirm New Password
                                </label>
                                <input
                                    v-model="form.password_confirmation"
                                    id="password_confirmation"
                                    type="password"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Confirm new password"
                                />
                                <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.password_confirmation }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <!-- Role -->
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                User Role <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.role"
                                id="role"
                                required
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="user">👤 Regular User</option>
                                <option value="admin">👑 Administrator</option>
                            </select>
                            <div v-if="form.errors.role" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.role }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Admins have access to all system features including user management
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Account Status
                            </label>
                            <div class="flex items-center">
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ form.is_active ? '✅ Active Account' : '❌ Inactive Account' }}
                                    </span>
                                </label>
                            </div>
                            <div v-if="form.errors.is_active" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.is_active }}
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Inactive users cannot log in to the system
                            </p>
                        </div>
                    </div>

                    <!-- User Info Display -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Account Information</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">User ID:</span>
                                <span class="ml-2 font-mono text-gray-900 dark:text-gray-100">#{{ user.id }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Created:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">
                                    {{ new Date(user.created_at).toLocaleDateString('id-ID') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Last Login:</span>
                                <span class="ml-2 text-gray-900 dark:text-gray-100">
                                    {{ user.last_login_at ? new Date(user.last_login_at).toLocaleDateString('id-ID') : 'Never' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <Link
                            :href="route('users.index')"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ form.processing ? 'Updating...' : 'Update User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>