<template>
    <AppLayout title="Stock Screener - Pasar Saham">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Stock Screener</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Analisis dan filter saham berdasarkan kriteria fundamental dan teknikal</p>
            </div>

            <!-- Navigation Pills -->
            <div class="mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <Link :href="route('pasar-saham.screener')"
                          class="border-b-2 border-blue-500 px-1 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                        Stock Screener
                    </Link>
                    <Link :href="route('pasar-saham.heatmap')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Market Heatmap
                    </Link>
                    <Link :href="route('pasar-saham.chart')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Advanced Chart
                    </Link>
                    <Link :href="route('pasar-saham.watchlist')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Watchlist
                    </Link>
                </nav>
            </div>

            <!-- Stock Screener Widget -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div id="tradingview-screener" style="min-height: 600px;"></div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// Declare TradingView global variable
declare global {
    interface Window {
        TradingView: any
    }
}

onMounted(() => {
    // Load TradingView script
    const script = document.createElement('script')
    script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-screener.js'
    script.async = true
    script.innerHTML = JSON.stringify({
        "width": "100%",
        "height": "600",
        "defaultColumn": "overview",
        "screener_type": "stock_market",
        "displayCurrency": "IDR",
        "colorTheme": "light",
        "locale": "id",
        "market": "indonesia"
    })
    
    const container = document.getElementById('tradingview-screener')
    if (container) {
        container.appendChild(script)
    }
})

onUnmounted(() => {
    // Clean up
    const container = document.getElementById('tradingview-screener')
    if (container) {
        container.innerHTML = ''
    }
})
</script>