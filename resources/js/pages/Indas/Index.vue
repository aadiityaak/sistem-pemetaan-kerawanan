<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, Calendar, MapPin, Shield, TrendingUp, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface MonitoringData {
    id: number;
    title: string;
    description: string;
    severity_level: string;
    status: string;
    incident_date: string;
    provinsi: { nama: string };
    kabupaten_kota: { nama: string };
    category: { name: string };
    sub_category: { name: string };
    jumlah_terdampak: number | null;
}

interface SecurityStats {
    total_incidents: number;
    critical_level: number;
    high_level: number;
    medium_level: number;
    low_level: number;
    active_cases: number;
    resolved_cases: number;
    monitoring_cases: number;
    security_index: number;
    affected_population: number;
    provinces_affected: number;
}

interface TrendAnalysis {
    overall_trend: string;
    trend_percentage: number;
    recent_incidents: number;
    previous_incidents: number;
    daily_average: number;
}

interface PriorityThreat {
    id: number;
    title: string;
    severity_level: string;
    provinsi: { nama: string };
    category: { name: string };
    incident_date: string;
}

interface RegionalStatus {
    province: string;
    total_incidents: number;
    critical_incidents: number;
    high_incidents: number;
    risk_level: string;
    affected_population: number;
}

interface Recommendation {
    priority: string;
    category: string;
    title: string;
    description: string;
    actions: string[];
}

const props = defineProps<{
    period: number;
    monitoringData: MonitoringData[];
    securityStats: SecurityStats;
    trendAnalysis: TrendAnalysis;
    priorityThreats: PriorityThreat[];
    regionalStatus: RegionalStatus[];
    recommendations: Recommendation[];
    lastUpdated: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'INDAS', href: '/indas' }
];

const securityIndexColor = computed(() => {
    if (props.securityStats.security_index >= 80) return 'text-green-600 bg-green-100';
    if (props.securityStats.security_index >= 60) return 'text-yellow-600 bg-yellow-100';
    if (props.securityStats.security_index >= 40) return 'text-orange-600 bg-orange-100';
    return 'text-red-600 bg-red-100';
});

const trendIcon = computed(() => {
    return props.trendAnalysis.overall_trend === 'increasing' ? '📈' : 
           props.trendAnalysis.overall_trend === 'decreasing' ? '📉' : '➡️';
});

const getSeverityColor = (level: string) => {
    const colors = {
        critical: 'bg-red-100 text-red-800 border-red-200',
        high: 'bg-orange-100 text-orange-800 border-orange-200',
        medium: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        low: 'bg-green-100 text-green-800 border-green-200'
    };
    return colors[level as keyof typeof colors] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getRiskLevelColor = (level: string) => {
    const colors = {
        high: 'bg-red-500',
        medium: 'bg-yellow-500', 
        low: 'bg-green-500'
    };
    return colors[level as keyof typeof colors] || 'bg-gray-500';
};

const getPriorityColor = (priority: string) => {
    const colors = {
        high: 'border-l-red-500 bg-red-50',
        medium: 'border-l-yellow-500 bg-yellow-50',
        low: 'border-l-green-500 bg-green-50'
    };
    return colors[priority as keyof typeof colors] || 'border-l-gray-500 bg-gray-50';
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="INDAS - Indikator Keamanan dan Intelijen Dasar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="mr-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                            <Shield class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                INDAS - Indikator Keamanan dan Intelijen Dasar
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Ringkasan situasi keamanan berdasarkan data monitoring {{ period }} hari terakhir
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Terakhir diperbarui</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ lastUpdated }}</div>
                    </div>
                </div>
            </div>

            <!-- Statistik Keamanan -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
                <!-- Indeks Keamanan -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 rounded-lg p-2" :class="securityIndexColor">
                            <Shield class="h-6 w-6" />
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ securityStats.security_index }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Indeks Keamanan</p>
                    </div>
                </div>

                <!-- Total Insiden -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-900">
                            <AlertTriangle class="h-5 w-5 text-gray-600 dark:text-gray-400" />
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ securityStats.total_incidents }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Total Insiden</p>
                    </div>
                </div>

                <!-- Kasus Kritis -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                            <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ securityStats.critical_level }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Kritis</p>
                    </div>
                </div>

                <!-- Kasus Aktif -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                            <Calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ securityStats.active_cases }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Kasus Aktif</p>
                    </div>
                </div>

                <!-- Populasi Terdampak -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                            <Users class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ securityStats.affected_population.toLocaleString() }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Terdampak</p>
                    </div>
                </div>

                <!-- Provinsi Terdampak -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                            <MapPin class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ securityStats.provinces_affected }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Provinsi</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Analisis Tren -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 mb-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                📊 Analisis Tren Keamanan
                            </h3>
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-2xl">{{ trendIcon }}</span>
                                <span class="font-medium" :class="trendAnalysis.overall_trend === 'increasing' ? 'text-red-600' : 'text-green-600'">
                                    {{ trendAnalysis.trend_percentage }}%
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-blue-600">{{ trendAnalysis.recent_incidents }}</p>
                                <p class="text-sm text-gray-500">Periode Terbaru</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-600">{{ trendAnalysis.previous_incidents }}</p>
                                <p class="text-sm text-gray-500">Periode Sebelumnya</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600">{{ trendAnalysis.daily_average }}</p>
                                <p class="text-sm text-gray-500">Rata-rata Harian</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <TrendingUp class="h-4 w-4 text-blue-600" />
                                <span class="font-medium text-gray-900 dark:text-white">Status Tren:</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span v-if="trendAnalysis.overall_trend === 'increasing'" class="text-red-600 font-medium">
                                    Peningkatan {{ trendAnalysis.trend_percentage }}% insiden dalam periode terakhir. Diperlukan perhatian khusus.
                                </span>
                                <span v-else-if="trendAnalysis.overall_trend === 'decreasing'" class="text-green-600 font-medium">
                                    Penurunan {{ trendAnalysis.trend_percentage }}% insiden. Kondisi keamanan membaik.
                                </span>
                                <span v-else class="text-yellow-600 font-medium">
                                    Situasi relatif stabil dengan fluktuasi minimal.
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Ancaman Prioritas -->
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                ⚠️ Ancaman Prioritas Tinggi
                            </h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Insiden kritis dan tinggi yang memerlukan perhatian segera
                            </p>
                        </div>
                        
                        <div class="p-6">
                            <div v-if="priorityThreats.length === 0" class="text-center py-8">
                                <Shield class="mx-auto h-12 w-12 text-green-400" />
                                <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak Ada Ancaman Prioritas</p>
                                <p class="text-sm text-gray-500">Situasi keamanan dalam kondisi baik</p>
                            </div>
                            
                            <div v-else class="space-y-4">
                                <div 
                                    v-for="threat in priorityThreats.slice(0, 5)" 
                                    :key="threat.id"
                                    class="flex items-start gap-4 p-4 rounded-lg border" 
                                    :class="getSeverityColor(threat.severity_level)"
                                >
                                    <AlertTriangle class="h-5 w-5 mt-0.5 flex-shrink-0" />
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h4 class="font-medium">{{ threat.title }}</h4>
                                                <p class="text-sm opacity-75">{{ threat.category.name }} - {{ threat.provinsi.nama }}</p>
                                            </div>
                                            <span class="text-xs">{{ formatDate(threat.incident_date) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Analytics -->
                <div class="space-y-6">
                    <!-- Status Regional -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            🗺️ Status Regional
                        </h3>
                        <div class="space-y-3">
                            <div 
                                v-for="region in regionalStatus.slice(0, 8)" 
                                :key="region.province"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" :class="getRiskLevelColor(region.risk_level)"></div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ region.province }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ region.total_incidents }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ region.risk_level.toUpperCase() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekomendasi Aksi -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            💡 Rekomendasi Aksi
                        </h3>
                        <div class="space-y-4">
                            <div 
                                v-for="rec in recommendations.slice(0, 3)" 
                                :key="rec.title"
                                class="p-4 rounded-lg border-l-4" 
                                :class="getPriorityColor(rec.priority)"
                            >
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-white/50">
                                        {{ rec.priority.toUpperCase() }}
                                    </span>
                                    <span class="text-xs text-gray-600">{{ rec.category }}</span>
                                </div>
                                <h4 class="font-medium text-sm mb-1">{{ rec.title }}</h4>
                                <p class="text-xs text-gray-600 mb-2">{{ rec.description }}</p>
                                <ul class="text-xs space-y-1">
                                    <li v-for="action in rec.actions.slice(0, 2)" :key="action" class="flex items-start gap-1">
                                        <span>•</span>
                                        <span>{{ action }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>