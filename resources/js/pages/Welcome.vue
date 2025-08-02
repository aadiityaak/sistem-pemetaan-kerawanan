<script setup lang="ts">
import type { AppPageProps } from '@/types/index.d';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Get shared data from Inertia with proper typing
const page = usePage<AppPageProps>();

// Computed values for app settings with fallbacks
const appName = computed(() => {
    return page.props.appSettings?.app_name || 'Peta Kriminal Indonesia';
});

const appDescription = computed(() => {
    return page.props.appSettings?.app_description || 'Sistem Informasi Pemetaan Data Kriminal Indonesia';
});

const appLogo = computed(() => {
    return page.props.appSettings?.app_logo || page.props.appSettings?.app_favicon || null;
});
</script>

<template>
    <Head :title="appName">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-red-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
        <!-- Navigation -->
        <nav class="relative z-10 p-6">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-600 shadow-lg">
                        <img v-if="appLogo" :src="appLogo" :alt="appName + ' Logo'" class="h-6 w-6 object-contain" />
                        <svg v-else class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ appName }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Crime Mapping System</p>
                    </div>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-flex items-center rounded-lg bg-red-600 px-6 py-2 font-medium text-white shadow-lg transition-colors duration-200 hover:bg-red-700"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                            />
                        </svg>
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="font-medium text-gray-700 transition-colors duration-200 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400"
                        >
                            Masuk
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 font-medium text-white shadow-lg transition-colors duration-200 hover:bg-red-700"
                        >
                            Daftar
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="mx-auto max-w-7xl px-6 py-12">
            <div class="mb-16 text-center">
                <h2 class="mb-6 text-4xl font-bold text-gray-900 md:text-6xl dark:text-white">
                    Monitoring Keamanan
                    <span class="text-red-600">Indonesia</span>
                </h2>
                <p class="mx-auto mb-8 max-w-3xl text-xl text-gray-600 dark:text-gray-400">
                    {{ appDescription }}
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="inline-flex items-center rounded-lg bg-red-600 px-8 py-4 font-semibold text-white shadow-lg transition-colors duration-200 hover:bg-red-700"
                    >
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Mulai Sekarang
                    </Link>
                    <Link
                        :href="$page.props.auth.user ? route('dashboard') : route('login')"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-8 py-4 font-semibold text-gray-900 shadow-lg transition-colors duration-200 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                    >
                        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            />
                        </svg>
                        {{ $page.props.auth.user ? 'Dashboard' : 'Lihat Data' }}
                    </Link>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mb-16 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Pemetaan Real-time -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition-shadow duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Pemetaan Real-time</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Visualisasi data kriminal secara real-time dengan peta interaktif yang mudah dipahami.
                    </p>
                </div>

                <!-- Monitoring Data -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition-shadow duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Monitoring Data</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Pemantauan data keamanan berdasarkan 5 aspek: Ideologi, Politik, Ekonomi, Sosial Budaya, dan Keamanan.
                    </p>
                </div>

                <!-- Analisis Wilayah -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition-shadow duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Analisis Wilayah</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Analisis mendalam berdasarkan wilayah mulai dari tingkat provinsi, kabupaten/kota, hingga kecamatan.
                    </p>
                </div>

                <!-- Dashboard Interaktif -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition-shadow duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Dashboard Interaktif</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Interface yang intuitif dengan visualisasi data yang mudah dipahami dan dapat disesuaikan.
                    </p>
                </div>

                <!-- Kategorisasi Data -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition-shadow duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Kategorisasi Data</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Sistem kategorisasi dan sub-kategori yang terstruktur dengan icon visual untuk memudahkan identifikasi.
                    </p>
                </div>

                <!-- Keamanan Data -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-lg transition-shadow duration-200 hover:shadow-xl dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900">
                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-900 dark:text-white">Keamanan Data</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Sistem keamanan berlapis dengan autentikasi dan autorisasi untuk melindungi data sensitif.
                    </p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="mb-16 rounded-xl border border-gray-200 bg-white p-8 shadow-lg dark:border-gray-700 dark:bg-gray-800">
                <h3 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white">Jangkauan Sistem</h3>
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-red-600">34</div>
                        <div class="text-gray-600 dark:text-gray-400">Provinsi</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-blue-600">514</div>
                        <div class="text-gray-600 dark:text-gray-400">Kabupaten/Kota</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-green-600">7,000+</div>
                        <div class="text-gray-600 dark:text-gray-400">Kecamatan</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2 text-3xl font-bold text-purple-600">5</div>
                        <div class="text-gray-600 dark:text-gray-400">Aspek Monitoring</div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center">
                <h3 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Siap Memulai Monitoring?</h3>
                <p class="mx-auto mb-8 max-w-2xl text-xl text-gray-600 dark:text-gray-400">
                    Bergabunglah dengan sistem monitoring keamanan nasional untuk mendapatkan akses data real-time dan analisis mendalam.
                </p>
                <Link
                    v-if="!$page.props.auth.user"
                    :href="route('register')"
                    class="inline-flex transform items-center rounded-lg bg-gradient-to-r from-red-600 to-red-700 px-8 py-4 font-semibold text-white shadow-lg transition-all duration-200 hover:scale-105 hover:from-red-700 hover:to-red-800"
                >
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    Daftar Sekarang
                </Link>
                <Link
                    v-else
                    :href="route('dashboard')"
                    class="inline-flex transform items-center rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-4 font-semibold text-white shadow-lg transition-all duration-200 hover:scale-105 hover:from-blue-700 hover:to-blue-800"
                >
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                        />
                    </svg>
                    Akses Dashboard
                </Link>
            </div>
        </main>

        <!-- Footer -->
        <footer class="mt-16 border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-6 py-8">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <div class="mb-4 flex items-center space-x-3 md:mb-0">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-600">
                            <img v-if="appLogo" :src="appLogo" :alt="appName + ' Logo'" class="h-5 w-5 object-contain" />
                            <svg v-else class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                />
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ appName }}</span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">© 2025 {{ appName }}. Semua hak dilindungi.</div>
                </div>
            </div>
        </footer>
    </div>
</template>
