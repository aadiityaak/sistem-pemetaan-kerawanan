<template>
    <Head title="Prediksi AI - Analisis Kriminalitas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Prediksi AI</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Analisis prediktif data kriminalitas menggunakan AI Gemini
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
                            Silakan aktifkan dan konfigurasi Gemini AI di halaman 
                            <a href="/settings" class="font-medium underline">Pengaturan</a> untuk menggunakan fitur prediksi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Analysis Form -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Analisis Prediktif</h2>
                
                <form @submit.prevent="analyzeCategory" class="space-y-4">
                    <!-- Category Selection -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pilih Kategori Kriminalitas <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="selectedCategory"
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
                            {{ isAnalyzing ? 'Menganalisis...' : 'Mulai Analisis AI' }}
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
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Powered by</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Gemini AI
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Analysis Results -->
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Hasil Analisis AI
                    </h3>
                    <div 
                        class="prose max-w-none dark:prose-invert prose-headings:text-gray-900 dark:prose-headings:text-white prose-p:text-gray-700 dark:prose-p:text-gray-300"
                        v-html="formatAnalysis(analysisResult.analysis)"
                    ></div>
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
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

interface Category {
    id: number
    name: string
    slug: string
    color: string
}

interface AnalysisResult {
    success: boolean
    analysis: string
    statistics: any
    data_period: string
    total_analyzed: number
    category: string
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
const isAnalyzing = ref(false)
const analysisResult = ref<AnalysisResult | null>(null)
const errorMessage = ref<string>('')

const analyzeCategory = async () => {
    if (!selectedCategory.value || !props.geminiEnabled) return

    isAnalyzing.value = true
    errorMessage.value = ''
    analysisResult.value = null

    try {
        const response = await fetch('/ai-prediction/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                category_id: selectedCategory.value
            })
        })

        const data = await response.json()

        if (data.success) {
            analysisResult.value = data
        } else {
            errorMessage.value = data.error || 'Terjadi kesalahan saat menganalisis'
        }
    } catch (error) {
        errorMessage.value = 'Gagal menghubungi server. Silakan coba lagi.'
        console.error('Analysis error:', error)
    } finally {
        isAnalyzing.value = false
    }
}

const formatAnalysis = (analysis: string): string => {
    // Convert markdown-like formatting to HTML
    return analysis
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/### (.*?)(?=\n|$)/g, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>')
        .replace(/## (.*?)(?=\n|$)/g, '<h2 class="text-xl font-semibold mt-6 mb-3">$1</h2>')
        .replace(/# (.*?)(?=\n|$)/g, '<h1 class="text-2xl font-bold mt-8 mb-4">$1</h1>')
        .replace(/\n- (.*?)(?=\n|$)/g, '<li class="ml-4">$1</li>')
        .replace(/\n\n/g, '</p><p class="mb-3">')
        .replace(/^/, '<p class="mb-3">')
        .replace(/$/, '</p>')
        .replace(/(<li.*?>.*?<\/li>)/g, '<ul class="list-disc pl-6 mb-3">$1</ul>')
}
</script>