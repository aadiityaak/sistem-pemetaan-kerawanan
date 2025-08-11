<template>
    <AppLayout title="Market Heatmap - Pasar Saham">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Market Heatmap</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Visualisasi pergerakan saham dalam bentuk heatmap berdasarkan sektor dan kapitalisasi</p>
            </div>

            <!-- Navigation Pills -->
            <div class="mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <Link :href="route('pasar-saham.screener')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Stock Screener
                    </Link>
                    <Link :href="route('pasar-saham.heatmap')"
                          class="border-b-2 border-blue-500 px-1 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
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

            <!-- Market Heatmap Widget -->
            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div id="tradingview-heatmap" style="min-height: 600px;"></div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

onMounted(() => {
    // Load TradingView Heatmap Widget
    const script = document.createElement('script')
    script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-stock-heatmap.js'
    script.async = true
    script.innerHTML = JSON.stringify({
        "exchanges": ["IDX"],
        "dataSource": "SPX500",
        "grouping": "sector",
        "blockSize": "market_cap_basic",
        "blockColor": "change",
        "locale": "id",
        "symbolUrl": "",
        "colorTheme": "light",
        "hasTopBar": true,
        "isDataSetEnabled": true,
        "isZoomEnabled": true,
        "hasSymbolTooltip": true,
        "width": "100%",
        "height": "600"
    })
    
    const container = document.getElementById('tradingview-heatmap')
    if (container) {
        container.appendChild(script)
    }
})

onUnmounted(() => {
    // Clean up
    const container = document.getElementById('tradingview-heatmap')
    if (container) {
        container.innerHTML = ''
    }
})
</script>