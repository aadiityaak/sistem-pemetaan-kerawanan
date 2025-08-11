<template>
    <AppLayout title="Watchlist - Pasar Saham">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Stock Watchlist</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Pantau pergerakan saham pilihan dalam format tabel yang mudah dibaca</p>
            </div>

            <!-- Navigation Pills -->
            <div class="mb-6">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <Link :href="route('pasar-saham.chart')"
                          class="border-b-2 border-transparent px-1 py-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        Advanced Chart
                    </Link>
                    <Link :href="route('pasar-saham.watchlist')"
                          class="border-b-2 border-blue-500 px-1 py-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                        Watchlist
                    </Link>
                </nav>
            </div>

            <!-- Watchlist Widgets Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Blue Chip Stocks -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Blue Chip Stocks (LQ45)</h2>
                    <div id="tradingview-watchlist-bluechip" style="height: 400px;"></div>
                </div>

                <!-- Banking Sector -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Banking Sector</h2>
                    <div id="tradingview-watchlist-banking" style="height: 400px;"></div>
                </div>

                <!-- Technology & Telecom -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Technology & Telecom</h2>
                    <div id="tradingview-watchlist-tech" style="height: 400px;"></div>
                </div>

                <!-- Consumer Goods -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Consumer Goods</h2>
                    <div id="tradingview-watchlist-consumer" style="height: 400px;"></div>
                </div>
            </div>

            <!-- Market News Widget -->
            <div class="mt-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Latest Market News</h2>
                <div id="tradingview-market-news" style="height: 400px;"></div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

onMounted(() => {
    initWatchlistWidgets()
})

const initWatchlistWidgets = () => {
    // Blue Chip Stocks Watchlist
    const script1 = document.createElement('script')
    script1.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-quotes.js'
    script1.async = true
    script1.innerHTML = JSON.stringify({
        "width": "100%",
        "height": "400",
        "symbolsGroups": [
            {
                "name": "Blue Chip",
                "originalName": "LQ45",
                "symbols": [
                    {"name": "IDX:BBCA", "displayName": "Bank BCA"},
                    {"name": "IDX:BBRI", "displayName": "Bank BRI"},
                    {"name": "IDX:BMRI", "displayName": "Bank Mandiri"},
                    {"name": "IDX:TLKM", "displayName": "Telkom"},
                    {"name": "IDX:ASII", "displayName": "Astra International"},
                    {"name": "IDX:UNVR", "displayName": "Unilever"},
                    {"name": "IDX:GGRM", "displayName": "Gudang Garam"},
                    {"name": "IDX:ICBP", "displayName": "Indofood CBP"}
                ]
            }
        ],
        "showSymbolLogo": true,
        "colorTheme": "light",
        "isTransparent": false,
        "locale": "id"
    })
    const container1 = document.getElementById('tradingview-watchlist-bluechip')
    if (container1) container1.appendChild(script1)

    // Banking Sector Watchlist
    const script2 = document.createElement('script')
    script2.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-quotes.js'
    script2.async = true
    script2.innerHTML = JSON.stringify({
        "width": "100%",
        "height": "400",
        "symbolsGroups": [
            {
                "name": "Banking",
                "originalName": "Banks",
                "symbols": [
                    {"name": "IDX:BBCA", "displayName": "Bank BCA"},
                    {"name": "IDX:BBRI", "displayName": "Bank BRI"},
                    {"name": "IDX:BMRI", "displayName": "Bank Mandiri"},
                    {"name": "IDX:BBNI", "displayName": "Bank BNI"},
                    {"name": "IDX:BDMN", "displayName": "Bank Danamon"},
                    {"name": "IDX:MEGA", "displayName": "Bank Mega"}
                ]
            }
        ],
        "showSymbolLogo": true,
        "colorTheme": "light",
        "isTransparent": false,
        "locale": "id"
    })
    const container2 = document.getElementById('tradingview-watchlist-banking')
    if (container2) container2.appendChild(script2)

    // Technology & Telecom Watchlist
    const script3 = document.createElement('script')
    script3.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-quotes.js'
    script3.async = true
    script3.innerHTML = JSON.stringify({
        "width": "100%",
        "height": "400",
        "symbolsGroups": [
            {
                "name": "Tech & Telecom",
                "originalName": "Technology",
                "symbols": [
                    {"name": "IDX:TLKM", "displayName": "Telkom"},
                    {"name": "IDX:EXCL", "displayName": "XL Axiata"},
                    {"name": "IDX:ISAT", "displayName": "Indosat"},
                    {"name": "IDX:GOTO", "displayName": "GoTo"},
                    {"name": "IDX:BUKA", "displayName": "Bukalapak"}
                ]
            }
        ],
        "showSymbolLogo": true,
        "colorTheme": "light",
        "isTransparent": false,
        "locale": "id"
    })
    const container3 = document.getElementById('tradingview-watchlist-tech')
    if (container3) container3.appendChild(script3)

    // Consumer Goods Watchlist
    const script4 = document.createElement('script')
    script4.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-quotes.js'
    script4.async = true
    script4.innerHTML = JSON.stringify({
        "width": "100%",
        "height": "400",
        "symbolsGroups": [
            {
                "name": "Consumer",
                "originalName": "Consumer Goods",
                "symbols": [
                    {"name": "IDX:UNVR", "displayName": "Unilever"},
                    {"name": "IDX:ICBP", "displayName": "Indofood CBP"},
                    {"name": "IDX:INDF", "displayName": "Indofood"},
                    {"name": "IDX:KLBF", "displayName": "Kalbe Farma"},
                    {"name": "IDX:GGRM", "displayName": "Gudang Garam"},
                    {"name": "IDX:HMSP", "displayName": "HM Sampoerna"}
                ]
            }
        ],
        "showSymbolLogo": true,
        "colorTheme": "light",
        "isTransparent": false,
        "locale": "id"
    })
    const container4 = document.getElementById('tradingview-watchlist-consumer')
    if (container4) container4.appendChild(script4)

    // Market News Widget
    const script5 = document.createElement('script')
    script5.src = 'https://s3.tradingview.com/external-embedding/embed-widget-timeline.js'
    script5.async = true
    script5.innerHTML = JSON.stringify({
        "feedMode": "market",
        "market": "stock",
        "isTransparent": false,
        "displayMode": "regular",
        "width": "100%",
        "height": "400",
        "colorTheme": "light",
        "locale": "id"
    })
    const container5 = document.getElementById('tradingview-market-news')
    if (container5) container5.appendChild(script5)
}

onUnmounted(() => {
    // Clean up all widgets
    const containers = [
        'tradingview-watchlist-bluechip',
        'tradingview-watchlist-banking', 
        'tradingview-watchlist-tech',
        'tradingview-watchlist-consumer',
        'tradingview-market-news'
    ]
    
    containers.forEach(containerId => {
        const container = document.getElementById(containerId)
        if (container) {
            container.innerHTML = ''
        }
    })
})
</script>