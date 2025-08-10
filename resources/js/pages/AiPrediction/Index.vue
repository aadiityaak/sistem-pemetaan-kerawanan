<template>
    <Head :title="`Prediksi AI - ${analysisTitle.replace('Hasil Analisis AI - ', '')}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Prediksi AI</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ selectedCategory ? 
                        `Analisis prediktif data ${getCategoryContext()} menggunakan AI` : 
                        'Analisis prediktif data menggunakan AI' 
                    }}
                </p>
            </div>

            <!-- AI Status Alert -->
            <div v-if="!geminiEnabled" class="rounded-lg bg-yellow-50 p-4 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800">
                <div class="flex">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 3.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 3.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            AI Belum Dikonfigurasi
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            Silakan aktifkan dan konfigurasi AI di halaman 
                            <a href="/settings" class="font-medium underline">Pengaturan</a> untuk menggunakan fitur prediksi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Analysis Form -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedCategory ? `Analisis Prediktif ${getCategoryContext()}` : 'Analisis Prediktif' }}
                </h2>
                
                <form @submit.prevent="analyzeCategory" class="space-y-4">
                    <!-- Category Selection -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pilih Kategori <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="selectedCategory"
                            @change="resetSubCategory"
                            required
                            :disabled="isAnalyzing || !geminiEnabled"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Sub-Category Selection ---->
                    <div v-if="availableSubCategories.length > 0">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pilih Sub Kategori <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <select
                            v-model="selectedSubCategory"
                            :disabled="isAnalyzing || !geminiEnabled"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">-- Semua Sub Kategori --</option>
                            <option v-for="subCategory in availableSubCategories" :key="subCategory.id" :value="subCategory.id">
                                {{ subCategory.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Analysis Button -->
                    <div>
                        <Button 
                            type="submit" 
                            :disabled="!selectedCategory || isAnalyzing || !geminiEnabled"
                            class="w-full sm:w-auto"
                        >
                            <svg v-if="isAnalyzing" class="mr-3 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            {{ isAnalyzing ? 'Menganalisis...' : (selectedCategory ? `Analisis ${getCategoryContext()}` : 'Mulai Analisis AI') }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Analysis Results -->
            <div v-if="analysisResult" class="space-y-6">
                <!-- Statistics Summary -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Kasus</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ analysisResult.total_analyzed }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode Data</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ analysisResult.data_period }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ analysisResult.category }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ analysisResult.sub_category ? 'Sub Kategori' : 'Powered by' }}
                                    </dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ analysisResult.sub_category || 'Gemini AI' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Severity Distribution Chart -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h4 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Distribusi Tingkat Keparahan</h4>
                        <div class="relative h-64">
                            <canvas ref="severityChartRef"></canvas>
                        </div>
                    </div>

                    <!-- Location Distribution Chart -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h4 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Lokasi Teratas</h4>
                        <div class="relative h-64">
                            <canvas ref="locationChartRef"></canvas>
                        </div>
                    </div>

                    <!-- Monthly Trend Chart -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h4 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Trend Bulanan</h4>
                        <div class="relative h-64">
                            <canvas ref="monthlyTrendChartRef"></canvas>
                        </div>
                    </div>
                </div>

                <!-- AI Analysis Results -->
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 dark:border-gray-700 dark:from-blue-900/20 dark:to-indigo-900/20">
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ analysisTitle }}
                            </h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div 
                            class="analysis-content max-w-none"
                            v-html="formatAnalysis(analysisResult.analysis)"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="rounded-lg bg-red-50 p-4 border border-red-200 dark:bg-red-900/20 dark:border-red-800">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            Error
                        </h3>
                        <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                            {{ errorMessage }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, nextTick } from 'vue'

interface SubCategory {
    id: number
    category_id: number
    name: string
    slug: string
}

interface Category {
    id: number
    name: string
    slug: string
    color: string
    sub_categories?: SubCategory[]
}

interface AnalysisResult {
    success: boolean
    analysis: string
    statistics: any
    data_period: string
    total_analyzed: number
    category: string
    sub_category?: string
}

const props = defineProps<{
    categories: Category[]
    geminiEnabled: boolean
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Prediksi AI',
        href: '/ai-prediction',
    },
]

const selectedCategory = ref<string>('')
const selectedSubCategory = ref<string>('')
const isAnalyzing = ref(false)
const analysisResult = ref<AnalysisResult | null>(null)
const errorMessage = ref<string>('')

// Chart refs
const severityChartRef = ref<HTMLCanvasElement>()
const locationChartRef = ref<HTMLCanvasElement>()
const monthlyTrendChartRef = ref<HTMLCanvasElement>()
let severityChart: any = null
let locationChart: any = null
let monthlyTrendChart: any = null

// Computed property for available sub-categories based on selected category
const availableSubCategories = computed(() => {
    if (!selectedCategory.value) return []
    const category = props.categories.find(cat => cat.id === parseInt(selectedCategory.value))
    return category?.sub_categories || []
})

// Computed property for dynamic analysis title
const analysisTitle = computed(() => {
    if (!selectedCategory.value) {
        return 'Hasil Analisis AI - Prediksi Data'
    }
    
    const category = props.categories.find(cat => cat.id === parseInt(selectedCategory.value))
    const categoryName = category?.name || 'Kategori'
    
    if (!selectedSubCategory.value) {
        return `Hasil Analisis AI - ${categoryName}`
    }
    
    const subCategory = availableSubCategories.value.find(sub => sub.id === parseInt(selectedSubCategory.value))
    const subCategoryName = subCategory?.name || 'Sub Kategori'
    
    return `Hasil Analisis AI - ${categoryName}: ${subCategoryName}`
})

// Helper function to get category context for dynamic text
const getCategoryContext = () => {
    if (!selectedCategory.value) return ''
    
    const category = props.categories.find(cat => cat.id === parseInt(selectedCategory.value))
    const categoryName = category?.name || ''
    
    // Return appropriate context based on category
    const categoryContextMap: Record<string, string> = {
        'Keamanan': 'keamanan',
        'Ideologi': 'ideologi', 
        'Politik': 'politik',
        'Ekonomi': 'ekonomi',
        'Sosial Budaya': 'sosial budaya',
        'Kamtibmas': 'kamtibmas'
    }
    
    return categoryContextMap[categoryName] || categoryName.toLowerCase()
}

// Watch for category changes to reset sub-category selection
const resetSubCategory = () => {
    selectedSubCategory.value = ''
}

const analyzeCategory = async () => {
    if (!selectedCategory.value || !props.geminiEnabled) return

    isAnalyzing.value = true
    errorMessage.value = ''
    analysisResult.value = null

    try {
        // Get fresh CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        
        if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page.')
        }

        const response = await fetch('/ai-prediction/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                category_id: selectedCategory.value,
                sub_category_id: selectedSubCategory.value || null
            })
        })

        if (response.status === 419) {
            errorMessage.value = 'Session expired. Silakan refresh halaman dan coba lagi.'
            return
        }

        const data = await response.json()

        if (response.ok && data.success) {
            analysisResult.value = data
            // Initialize charts after data is loaded
            await nextTick()
            initializeCharts()
        } else if (response.status === 422) {
            errorMessage.value = data.message || 'Data yang dikirim tidak valid'
        } else {
            errorMessage.value = data.error || 'Terjadi kesalahan saat menganalisis'
        }
    } catch (error) {
        console.error('Analysis error:', error)
        if (error.message.includes('CSRF')) {
            errorMessage.value = 'Session expired. Silakan refresh halaman dan coba lagi.'
        } else {
            errorMessage.value = 'Gagal menghubungi server. Silakan refresh halaman dan coba lagi.'
        }
    } finally {
        isAnalyzing.value = false
    }
}

// Chart initialization and cleanup
const initializeCharts = async () => {
    // Destroy existing charts
    destroyCharts()
    
    if (!analysisResult.value?.statistics) return

    try {
        // Dynamically import Chart.js
        const { Chart, registerables } = await import('chart.js')
        Chart.register(...registerables)

        const stats = analysisResult.value.statistics

        // Initialize Severity Distribution Chart
        if (severityChartRef.value && stats.severity_distribution) {
            const severityData = Object.entries(stats.severity_distribution)
            const severityColors = {
                'low': '#10B981',
                'medium': '#F59E0B', 
                'high': '#EF4444',
                'critical': '#DC2626'
            }

            severityChart = new Chart(severityChartRef.value, {
                type: 'doughnut',
                data: {
                    labels: severityData.map(([level]) => level.charAt(0).toUpperCase() + level.slice(1)),
                    datasets: [{
                        data: severityData.map(([, count]) => count),
                        backgroundColor: severityData.map(([level]) => severityColors[level] || '#6B7280'),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            })
        }

        // Initialize Location Distribution Chart
        if (locationChartRef.value && stats.location_distribution) {
            const locationData = Object.values(stats.location_distribution).slice(0, 5)
            
            locationChart = new Chart(locationChartRef.value, {
                type: 'bar',
                data: {
                    labels: locationData.map(item => item.location),
                    datasets: [{
                        label: 'Jumlah Kasus',
                        data: locationData.map(item => item.count),
                        backgroundColor: '#3B82F6',
                        borderColor: '#1D4ED8',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            })
        }

        // Initialize Monthly Trend Chart
        if (monthlyTrendChartRef.value && stats.monthly_trend) {
            const trendData = Object.entries(stats.monthly_trend)
            
            monthlyTrendChart = new Chart(monthlyTrendChartRef.value, {
                type: 'line',
                data: {
                    labels: trendData.map(([month]) => month),
                    datasets: [{
                        label: 'Kasus per Bulan',
                        data: trendData.map(([, count]) => count),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            })
        }
    } catch (error) {
        console.error('Error initializing charts:', error)
    }
}

const destroyCharts = () => {
    if (severityChart) {
        severityChart.destroy()
        severityChart = null
    }
    if (locationChart) {
        locationChart.destroy()
        locationChart = null
    }
    if (monthlyTrendChart) {
        monthlyTrendChart.destroy()
        monthlyTrendChart = null
    }
}

const formatAnalysis = (analysis: string): string => {
    if (!analysis) return ''
    
    // Enhanced markdown to HTML conversion
    let html = analysis
        // Headers
        .replace(/^### (.*$)/gm, '<h3 class="text-lg font-semibold mt-6 mb-3 text-blue-600 dark:text-blue-400">$1</h3>')
        .replace(/^## (.*$)/gm, '<h2 class="text-xl font-semibold mt-8 mb-4 text-blue-700 dark:text-blue-300">$1</h2>')
        .replace(/^# (.*$)/gm, '<h1 class="text-2xl font-bold mt-10 mb-6 text-blue-800 dark:text-blue-200">$1</h1>')
        
        // Bold and Italic
        .replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>')
        .replace(/\*(.*?)\*/g, '<em class="italic text-gray-700 dark:text-gray-300">$1</em>')
        
        // Lists - handle nested structure better
        .replace(/^\s*[-*+]\s+(.+)$/gm, '<li class="mb-1 text-gray-700 dark:text-gray-300">$1</li>')
        
        // Numbers (1., 2., etc.)
        .replace(/^\s*(\d+)\.\s+(.+)$/gm, '<li class="mb-2 text-gray-700 dark:text-gray-300"><span class="font-medium text-blue-600 dark:text-blue-400">$1.</span> $2</li>')
        
        // Code blocks or highlighted text
        .replace(/`([^`]+)`/g, '<code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm font-mono">$1</code>')
        
        // Line breaks to paragraphs
        .replace(/\n\s*\n/g, '</p><p class="mb-4 text-gray-700 dark:text-gray-300">')
        
        // Wrap in paragraph tags
        .replace(/^/, '<p class="mb-4 text-gray-700 dark:text-gray-300">')
        .replace(/$/, '</p>')
    
    // Wrap consecutive li elements in ul tags
    html = html.replace(/(<li[^>]*>.*?<\/li>\s*)+/g, (match) => {
        return `<ul class="list-disc pl-6 mb-4 space-y-1">${match}</ul>`
    })
    
    // Clean up empty paragraphs
    html = html.replace(/<p[^>]*><\/p>/g, '')
    
    return html
}
</script>