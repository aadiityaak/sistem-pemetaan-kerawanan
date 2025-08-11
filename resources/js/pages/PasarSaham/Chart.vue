<template>
    <AppLayout title="Advanced Chart - Pasar Saham">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Advanced Chart</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Chart lengkap dengan indikator teknikal dan tools analisis</p>
            </div>

            <!-- Navigation Pills -->
            <div class="mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <Link :href="route('pasar-saham.chart')"
                          class="border-b-2 border-blue-500 px-1 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                        Advanced Chart
                    </Link>
                    <Link :href="route('pasar-saham.watchlist')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Watchlist
                    </Link>
                </nav>
            </div>

            <!-- Advanced Chart Widget -->
            <div class="rounded-lg bg-white shadow dark:bg-gray-800" style="min-height: 700px;">
                <div id="tradingview-advanced-chart" style="height: 700px;"></div>
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
    script.src = 'https://s3.tradingview.com/tv.js'
    script.async = true
    script.onload = initAdvancedChart
    document.head.appendChild(script)
})

onUnmounted(() => {
    // Clean up
    const existingScript = document.querySelector('script[src="https://s3.tradingview.com/tv.js"]')
    if (existingScript) {
        document.head.removeChild(existingScript)
    }
})

const initAdvancedChart = () => {
    if (!window.TradingView) return

    new window.TradingView.widget({
        width: "100%",
        height: 700,
        symbol: "IDX:BBCA",
        interval: "1D",
        timezone: "Asia/Jakarta",
        theme: "light",
        style: "1",
        locale: "id",
        toolbar_bg: "#f1f3f6",
        enable_publishing: false,
        allow_symbol_change: true,
        container_id: "tradingview-advanced-chart",
        hide_top_toolbar: false,
        hide_legend: false,
        save_image: true,
        studies: [
            "Volume@tv-basicstudies",
            "MACD@tv-basicstudies",
            "RSI@tv-basicstudies"
        ],
        show_popup_button: true,
        popup_width: "1000",
        popup_height: "650",
        details: true,
        hotlist: true,
        calendar: true,
        news: ["headlines"],
        watchlist: [
            "IDX:BBCA",
            "IDX:BBRI", 
            "IDX:BMRI",
            "IDX:TLKM",
            "IDX:ASII",
            "IDX:UNVR",
            "IDX:ICBP",
            "IDX:INDF",
            "IDX:GGRM",
            "IDX:KLBF"
        ]
    })
}
</script>