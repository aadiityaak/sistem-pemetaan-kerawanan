<template>
    <AppLayout title="Pasar Saham Indonesia">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pasar Saham Indonesia</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Dashboard pasar saham dengan data real-time dari TradingView</p>
            </div>

            <!-- Navigation Pills -->
            <div class="mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <Link :href="route('pasar-saham.index')" 
                          class="border-b-2 border-blue-500 px-1 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                        Overview
                    </Link>
                    <Link :href="route('pasar-saham.screener')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
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

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Market Overview Widget -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Market Overview - IDX</h2>
                    <div id="tradingview-market-overview" class="h-96"></div>
                </div>

                <!-- Economic Calendar Widget -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Economic Calendar</h2>
                    <div id="tradingview-economic-calendar" class="h-96"></div>
                </div>

                <!-- Market Data Widget - Full Width -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Market Data - Top Indonesian Stocks</h2>
                        <div id="tradingview-market-data" class="h-96"></div>
                    </div>
                </div>

                <!-- Ticker Tape Widget - Full Width -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Market Ticker - IDX</h2>
                        <div id="tradingview-ticker-tape" class="h-16"></div>
                    </div>
                </div>
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
    script.onload = initTradingViewWidgets
    document.head.appendChild(script)
})

onUnmounted(() => {
    // Clean up TradingView widgets
    const existingScript = document.querySelector('script[src="https://s3.tradingview.com/tv.js"]')
    if (existingScript) {
        document.head.removeChild(existingScript)
    }
})

const initTradingViewWidgets = () => {
    if (!window.TradingView) return

    // Market Overview Widget
    new window.TradingView.MediumWidget({
        container_id: "tradingview-market-overview",
        symbols: [
            ["IHSG", "IDX:COMPOSITE|1D"],
            ["BBCA", "IDX:BBCA|1D"],
            ["BBRI", "IDX:BBRI|1D"],
            ["BMRI", "IDX:BMRI|1D"],
            ["TLKM", "IDX:TLKM|1D"],
            ["ASII", "IDX:ASII|1D"]
        ],
        chartOnly: false,
        width: "100%",
        height: "100%",
        locale: "id",
        colorTheme: "light",
        autosize: true,
        showVolume: false,
        hideDateRanges: false,
        scalePosition: "right",
        scaleMode: "Normal",
        fontFamily: "-apple-system, BlinkMacSystemFont, Trebuchet MS, Roboto, Ubuntu, sans-serif",
        noTimeScale: false,
        chartType: "area",
        lineColor: "#2962FF",
        bottomColor: "rgba(41, 98, 255, 0.12)",
        topColor: "rgba(41, 98, 255, 0.4)",
        timezone: "Asia/Jakarta"
    })

    // Economic Calendar Widget
    new window.TradingView.EconomicCalendarWidget({
        container_id: "tradingview-economic-calendar",
        width: "100%",
        height: "100%",
        locale: "id",
        importanceFilter: "-1,0,1",
        countryFilter: "id,us,cn,jp",
        currencyFilter: "USD,IDR,CNY,JPY",
        dateFormat: "dd/MM/yyyy"
    })

    // Market Data Widget
    new window.TradingView.widget({
        container_id: "tradingview-market-data",
        width: "100%",
        height: "100%",
        symbol: "IDX:COMPOSITE",
        interval: "1D",
        timezone: "Asia/Jakarta",
        theme: "light",
        style: "1",
        locale: "id",
        toolbar_bg: "#f1f3f6",
        enable_publishing: false,
        hide_top_toolbar: false,
        hide_legend: true,
        save_image: false
    })

    // Ticker Tape Widget
    new window.TradingView.widget({
        container_id: "tradingview-ticker-tape",
        width: "100%",
        height: "100%",
        symbols: [
            {
                proName: "IDX:COMPOSITE",
                title: "IHSG"
            },
            {
                proName: "IDX:BBCA",
                title: "BBCA"
            },
            {
                proName: "IDX:BBRI", 
                title: "BBRI"
            },
            {
                proName: "IDX:BMRI",
                title: "BMRI"
            },
            {
                proName: "IDX:TLKM",
                title: "TLKM"
            },
            {
                proName: "IDX:ASII",
                title: "ASII"
            },
            {
                proName: "IDX:UNVR",
                title: "UNVR"
            },
            {
                proName: "IDX:ICBP",
                title: "ICBP"
            }
        ],
        showSymbolLogo: true,
        colorTheme: "light",
        isTransparent: false,
        displayMode: "adaptive",
        locale: "id"
    })
}
</script>