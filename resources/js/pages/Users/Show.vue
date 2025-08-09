<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

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

// Helper functions
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getRoleBadgeClass = (role: string): string => {
    const classes: Record<string, string> = {
        admin: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        user: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    };
    return classes[role] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getStatusBadgeClass = (isActive: boolean): string => {
    return isActive 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};
</script>

<template>
    <Head :title="`User: ${user.name}`" />

    <AppLayout :title="`User: ${user.name}`">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">User Details</h1>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">View user account information</p>
                    </div>
                    <div class="flex space-x-3">
                        <Link
                            :href="route('users.edit', user.id)"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit User
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

            <!-- User Information Card -->
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">User Information</h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Full Name</label>
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ user.name }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Email Address</label>
                                <div class="text-gray-900 dark:text-gray-100">{{ user.email }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">User ID</label>
                                <div class="text-gray-900 dark:text-gray-100 font-mono">#{{ user.id }}</div>
                            </div>
                        </div>

                        <!-- Status Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                                <div>
                                    <span
                                        :class="getRoleBadgeClass(user.role)"
                                        class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                    >
                                        {{ user.role === 'admin' ? '👑 Administrator' : '👤 Regular User' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Account Status</label>
                                <div>
                                    <span
                                        :class="getStatusBadgeClass(user.is_active)"
                                        class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                    >
                                        {{ user.is_active ? '✅ Active' : '❌ Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Email Verification</label>
                                <div>
                                    <span
                                        :class="user.email_verified_at 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'"
                                        class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
                                    >
                                        {{ user.email_verified_at ? '✅ Verified' : '⏳ Unverified' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Information Card -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Activity Information</h3>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Last Login</label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never logged in' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Account Created</label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ formatDate(user.created_at) }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Last Updated</label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ formatDate(user.updated_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions Card -->
            <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Access Permissions</h3>
                </div>

                <div class="p-6">
                    <div v-if="user.role === 'admin'" class="space-y-3">
                        <div class="flex items-center text-green-600 dark:text-green-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Full system access</span>
                        </div>
                        <div class="flex items-center text-green-600 dark:text-green-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>User management</span>
                        </div>
                        <div class="flex items-center text-green-600 dark:text-green-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Application settings</span>
                        </div>
                        <div class="flex items-center text-green-600 dark:text-green-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Data management and analytics</span>
                        </div>
                    </div>
                    
                    <div v-else class="space-y-3">
                        <div class="flex items-center text-blue-600 dark:text-blue-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Dashboard access</span>
                        </div>
                        <div class="flex items-center text-blue-600 dark:text-blue-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Monitoring data management</span>
                        </div>
                        <div class="flex items-center text-blue-600 dark:text-blue-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Regional data viewing</span>
                        </div>
                        <div class="flex items-center text-gray-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>User management (restricted)</span>
                        </div>
                        <div class="flex items-center text-gray-400">
                            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Application settings (restricted)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>