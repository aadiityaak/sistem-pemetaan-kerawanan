<template>
    <AppLayout title="Trading">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Trading</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Platform trading saham dan instrumen keuangan</p>
            </div>

            <!-- Trading Interface -->
            <div class="space-y-6">
                <!-- Main Trading Chart -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Grafik Trading</h2>
                    <div id="tradingview-main-chart" style="height: 500px"></div>
                </div>

                <!-- Trading Tools Grid -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Watchlist -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Watchlist</h2>
                        <div id="tradingview-watchlist" style="height: 400px"></div>
                    </div>

                    <!-- Market Overview -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Ikhtisar Pasar</h2>
                        <div id="tradingview-market-overview" style="height: 400px"></div>
                    </div>
                </div>

                <!-- Indonesian Stock Market Focus -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <!-- IDX Composite -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">IHSG - Indeks Harga Saham Gabungan</h2>
                        <div id="tradingview-ihsg" style="height: 350px"></div>
                    </div>

                    <!-- Top Indonesian Stocks -->
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Saham Pilihan Indonesia</h2>
                        <div id="tradingview-top-stocks" style="height: 350px"></div>
                    </div>
                </div>

                <!-- Economic Calendar -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Kalender Ekonomi</h2>
                    <div id="tradingview-economic-calendar" style="height: 400px"></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    initTradingWidgets();
});

const initTradingWidgets = () => {
    // Main Advanced Chart for trading
    const scriptMainChart = document.createElement('script');
    scriptMainChart.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
    scriptMainChart.async = true;
    scriptMainChart.innerHTML = JSON.stringify({
        autosize: true,
        symbol: 'IDX:IHSG',
        interval: 'D',
        timezone: 'Asia/Jakarta',
        theme: 'light',
        style: '1',
        locale: 'id',
        toolbar_bg: '#f1f3f6',
        enable_publishing: false,
        allow_symbol_change: true,
        container_id: 'tradingview-main-chart',
        studies: ['MACD@tv-basicstudies', 'RSI@tv-basicstudies'],
        show_popup_button: true,
        popup_width: '1000',
        popup_height: '650',
    });
    const containerMainChart = document.getElementById('tradingview-main-chart');
    if (containerMainChart) containerMainChart.appendChild(scriptMainChart);

    // Watchlist Widget
    const scriptWatchlist = document.createElement('script');
    scriptWatchlist.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-quotes.js';
    scriptWatchlist.async = true;
    scriptWatchlist.innerHTML = JSON.stringify({
        width: '100%',
        height: '400',
        symbolsGroups: [
            {
                name: 'Saham Pilihan',
                originalName: 'Indices',
                symbols: [
                    { name: 'IDX:IHSG', displayName: 'IHSG' },
                    { name: 'IDX:BBCA', displayName: 'Bank BCA' },
                    { name: 'IDX:BBRI', displayName: 'Bank BRI' },
                    { name: 'IDX:BMRI', displayName: 'Bank Mandiri' },
                    { name: 'IDX:BBNI', displayName: 'Bank BNI' },
                    { name: 'IDX:ASII', displayName: 'Astra International' },
                    { name: 'IDX:UNVR', displayName: 'Unilever Indonesia' },
                    { name: 'IDX:TLKM', displayName: 'Telkom Indonesia' },
                ],
            },
        ],
        showSymbolLogo: true,
        colorTheme: 'light',
        isTransparent: false,
        locale: 'id',
    });
    const containerWatchlist = document.getElementById('tradingview-watchlist');
    if (containerWatchlist) containerWatchlist.appendChild(scriptWatchlist);

    // Market Overview Widget
    const scriptMarketOverview = document.createElement('script');
    scriptMarketOverview.src = 'https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js';
    scriptMarketOverview.async = true;
    scriptMarketOverview.innerHTML = JSON.stringify({
        colorTheme: 'light',
        dateRange: '12M',
        showChart: true,
        locale: 'id',
        width: '100%',
        height: '400',
        largeChartUrl: '',
        isTransparent: false,
        showSymbolLogo: true,
        showFloatingTooltip: false,
        plotLineColorGrowing: 'rgba(41, 98, 255, 1)',
        plotLineColorFalling: 'rgba(41, 98, 255, 1)',
        gridLineColor: 'rgba(240, 243, 250, 0)',
        scaleFontColor: 'rgba(106, 109, 120, 1)',
        belowLineFillColorGrowing: 'rgba(41, 98, 255, 0.12)',
        belowLineFillColorFalling: 'rgba(41, 98, 255, 0.12)',
        belowLineFillColorGrowingBottom: 'rgba(41, 98, 255, 0)',
        belowLineFillColorFallingBottom: 'rgba(41, 98, 255, 0)',
        symbolActiveColor: 'rgba(41, 98, 255, 0.12)',
        tabs: [
            {
                title: 'Indices',
                symbols: [
                    { s: 'IDX:IHSG', d: 'IHSG' },
                    { s: 'IDX:LQ45', d: 'LQ45' },
                    { s: 'IDX:JII', d: 'Jakarta Islamic Index' },
                ],
                originalTitle: 'Indices',
            },
            {
                title: 'Forex',
                symbols: [
                    { s: 'FX_IDC:USDIDR', d: 'USD/IDR' },
                    { s: 'FX_IDC:EURIDR', d: 'EUR/IDR' },
                    { s: 'FX_IDC:JPYIDR', d: 'JPY/IDR' },
                ],
                originalTitle: 'Forex',
            },
        ],
    });
    const containerMarketOverview = document.getElementById('tradingview-market-overview');
    if (containerMarketOverview) containerMarketOverview.appendChild(scriptMarketOverview);

    // IHSG Chart
    const scriptIHSG = document.createElement('script');
    scriptIHSG.src = 'https://s3.tradingview.com/external-embedding/embed-widget-mini-symbol-overview.js';
    scriptIHSG.async = true;
    scriptIHSG.innerHTML = JSON.stringify({
        symbol: 'IDX:IHSG',
        width: '100%',
        height: '350',
        locale: 'id',
        dateRange: '12M',
        colorTheme: 'light',
        isTransparent: false,
        autosize: true,
        largeChartUrl: '',
    });
    const containerIHSG = document.getElementById('tradingview-ihsg');
    if (containerIHSG) containerIHSG.appendChild(scriptIHSG);

    // Top Indonesian Stocks
    const scriptTopStocks = document.createElement('script');
    scriptTopStocks.src = 'https://s3.tradingview.com/external-embedding/embed-widget-screener.js';
    scriptTopStocks.async = true;
    scriptTopStocks.innerHTML = JSON.stringify({
        width: '100%',
        height: '350',
        defaultColumn: 'overview',
        defaultScreen: 'most_capitalized',
        market: 'indonesia',
        showToolbar: true,
        colorTheme: 'light',
        locale: 'id',
        isTransparent: false,
    });
    const containerTopStocks = document.getElementById('tradingview-top-stocks');
    if (containerTopStocks) containerTopStocks.appendChild(scriptTopStocks);

    // Economic Calendar
    const scriptEconomicCalendar = document.createElement('script');
    scriptEconomicCalendar.src = 'https://s3.tradingview.com/external-embedding/embed-widget-events.js';
    scriptEconomicCalendar.async = true;
    scriptEconomicCalendar.innerHTML = JSON.stringify({
        colorTheme: 'light',
        isTransparent: false,
        width: '100%',
        height: '400',
        locale: 'id',
        importanceFilter: '-1,0,1',
        countryFilter: 'id,us,eu,jp,gb,au,ca,ch,cn',
    });
    const containerEconomicCalendar = document.getElementById('tradingview-economic-calendar');
    if (containerEconomicCalendar) containerEconomicCalendar.appendChild(scriptEconomicCalendar);
};

onUnmounted(() => {
    // Clean up all widgets
    const containers = [
        'tradingview-main-chart',
        'tradingview-watchlist',
        'tradingview-market-overview',
        'tradingview-ihsg',
        'tradingview-top-stocks',
        'tradingview-economic-calendar',
    ];

    containers.forEach((containerId) => {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
        }
    });
});
</script>
