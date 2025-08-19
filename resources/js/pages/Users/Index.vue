<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Define types
interface Provinsi {
    id: number;
    nama: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    last_login_at?: string;
    created_at: string;
    provinsi?: Provinsi;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    prev_page_url?: string;
    next_page_url?: string;
    links: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
}

interface Statistics {
    total: number;
    super_admins: number;
    admin_vips: number;
    admins: number;
    active: number;
    inactive: number;
}

// Props
const props = defineProps<{
    users: PaginatedUsers;
    statistics: Statistics;
    filters: {
        search?: string;
        role?: string;
        status?: string;
    };
}>();

// Reactive data
const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const selectedStatus = ref(props.filters.status || '');
const showDeleteModal = ref(false);
const userToDelete = ref<User | null>(null);
const searchTimeout = ref<number | null>(null);

// Watch for filter changes with debounce
watch([searchQuery, selectedRole, selectedStatus], () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    searchTimeout.value = setTimeout(() => {
        const params: Record<string, any> = {};

        if (searchQuery.value) params.search = searchQuery.value;
        if (selectedRole.value) params.role = selectedRole.value;
        if (selectedStatus.value) params.status = selectedStatus.value;

        router.get(route('users.index'), params, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

// Methods
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
        super_admin: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        admin_vip: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        admin: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    };
    return classes[role] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getRoleLabel = (role: string): string => {
    const labels: Record<string, string> = {
        super_admin: '👑 Super Admin',
        admin_vip: '👁️ Admin VIP',
        admin: '👤 Admin Regional',
    };
    return labels[role] || '❓ Unknown';
};

const getStatusBadgeClass = (isActive: boolean): string => {
    return isActive
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    userToDelete.value = null;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(route('users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                closeDeleteModal();
            },
        });
    }
};

const toggleUserStatus = (user: User) => {
    router.post(
        route('users.toggle-status', user.id),
        {},
        {
            preserveState: true,
        },
    );
};
</script>

<template>
    <AppLayout title="User Management">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">User Management</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Manage system users and their permissions</p>
            </div>

            <!-- Statistics Cards -->
            <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.total }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Super Admin</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.super_admins }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Admin VIP</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.admin_vips }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Admin Regional</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.admins }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.active }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-red-100 p-2 dark:bg-red-900">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Inactive</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.inactive }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mb-6 flex items-center justify-between">
                <div></div>
                <div>
                    <Link
                        :href="route('users.create')"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs leading-5 font-semibold tracking-widest whitespace-nowrap text-white uppercase transition duration-150 ease-in-out hover:bg-blue-700 focus:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none active:bg-blue-900"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add User
                    </Link>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">🔍 Filter & Search</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Search -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                        />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search users..."
                                    class="block w-full rounded-md border border-gray-300 bg-white py-2 pr-3 pl-10 leading-5 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                                />
                            </div>
                        </div>

                        <!-- Filter Role -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                            <select
                                v-model="selectedRole"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">🟢 All Roles</option>
                                <option value="admin">👑 Admin</option>
                                <option value="user">👤 User</option>
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">🟢 All Status</option>
                                <option value="active">✅ Active</option>
                                <option value="inactive">❌ Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg border border-gray-200 bg-white shadow dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Users List</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Role
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Province
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Last Login
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                    Created
                                </th>
                                <th class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-if="users.data.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No users found</td>
                            </tr>
                            <tr v-else v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getRoleBadgeClass(user.role)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ getRoleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <span
                                        v-if="user.provinsi"
                                        class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
                                    >
                                        🌍 {{ user.provinsi.nama }}
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400"
                                    >
                                        🌐 All Provinces
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusBadgeClass(user.is_active)"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                    >
                                        {{ user.is_active ? '✅ Active' : '❌ Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                    {{ user.last_login_at ? formatDate(user.last_login_at) : 'Never' }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <Link
                                            :href="route('users.show', user.id)"
                                            class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 transition-colors duration-200 hover:bg-blue-200 dark:bg-blue-900/50 dark:text-blue-400 dark:hover:bg-blue-900"
                                        >
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            View
                                        </Link>
                                        <Link
                                            :href="route('users.edit', user.id)"
                                            class="inline-flex items-center rounded-md bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700 transition-colors duration-200 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-400 dark:hover:bg-indigo-900"
                                        >
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                            Edit
                                        </Link>
                                        <button
                                            @click="toggleUserStatus(user)"
                                            :class="
                                                user.is_active
                                                    ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900/50 dark:text-yellow-400 dark:hover:bg-yellow-900'
                                                    : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/50 dark:text-green-400 dark:hover:bg-green-900'
                                            "
                                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium transition-colors duration-200"
                                        >
                                            <svg v-if="user.is_active" class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"
                                                />
                                            </svg>
                                            <svg v-else class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                            {{ user.is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <button
                                            @click="confirmDelete(user)"
                                            class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 transition-colors duration-200 hover:bg-red-200 dark:bg-red-900/50 dark:text-red-400 dark:hover:bg-red-900"
                                        >
                                            <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6 dark:border-gray-700 dark:bg-gray-800" v-if="users.last_page > 1">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="users.prev_page_url"
                                :href="users.prev_page_url"
                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="users.next_page_url"
                                :href="users.next_page_url"
                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Next
                            </Link>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Showing <span class="font-medium">{{ users.from }}</span> to <span class="font-medium">{{ users.to }}</span> of
                                    <span class="font-medium">{{ users.total }}</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm">
                                    <template v-for="link in users.links" :key="link.label">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            :class="[
                                                'relative inline-flex items-center border px-4 py-2 text-sm font-medium',
                                                link.active
                                                    ? 'z-10 border-blue-500 bg-blue-50 text-blue-600'
                                                    : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50',
                                            ]"
                                            v-html="link.label"
                                        ></Link>
                                        <span
                                            v-else
                                            class="relative inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700"
                                            v-html="link.label"
                                        ></span>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-500/75" @click.prevent.stop></div>
            <div @click.stop class="relative w-full max-w-lg transform overflow-hidden rounded-lg bg-white shadow-xl dark:bg-gray-800">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 dark:bg-red-900"
                        >
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 18.5c-.77.833.192 2.5 1.732 2.5z"
                                />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">Delete User</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Are you sure you want to delete user "{{ userToDelete?.name }}"? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                    <button
                        @click="deleteUser"
                        type="button"
                        class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Delete
                    </button>
                    <button
                        @click="closeDeleteModal"
                        type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
