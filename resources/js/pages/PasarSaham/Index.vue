<template>
    <AppLayout title="Kurs Mata Uang">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Kurs Mata Uang</h1>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Nilai tukar Rupiah terhadap mata uang asing utama</p>
            </div>

            <!-- Currency Exchange Rates Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                <!-- USD/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">USD/IDR - Dollar Amerika</h2>
                    <div id="tradingview-usd-idr" style="height: 300px"></div>
                </div>

                <!-- EUR/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">EUR/IDR - Euro</h2>
                    <div id="tradingview-eur-idr" style="height: 300px"></div>
                </div>

                <!-- JPY/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">JPY/IDR - Yen Jepang</h2>
                    <div id="tradingview-jpy-idr" style="height: 300px"></div>
                </div>

                <!-- RUB/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">RUB/IDR - Ruble Rusia</h2>
                    <div id="tradingview-rub-idr" style="height: 300px"></div>
                </div>

                <!-- GBP/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">GBP/IDR - Pound Sterling</h2>
                    <div id="tradingview-gbp-idr" style="height: 300px"></div>
                </div>

                <!-- CNY/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">CNY/IDR - Yuan China</h2>
                    <div id="tradingview-cny-idr" style="height: 300px"></div>
                </div>

                <!-- AUD/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">AUD/IDR - Dollar Australia</h2>
                    <div id="tradingview-aud-idr" style="height: 300px"></div>
                </div>

                <!-- CAD/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">CAD/IDR - Dollar Kanada</h2>
                    <div id="tradingview-cad-idr" style="height: 300px"></div>
                </div>

                <!-- CHF/IDR -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">CHF/IDR - Franc Swiss</h2>
                    <div id="tradingview-chf-idr" style="height: 300px"></div>
                </div>
            </div>

            <!-- Major Currency Rates Summary -->
            <div class="mt-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Ringkasan Kurs Mata Uang Utama</h2>
                <div id="tradingview-currency-overview" style="height: 400px"></div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    initCurrencyWidgets();
});

const initCurrencyWidgets = () => {
    // Currency pairs configuration
    const currencies = [
        { id: 'usd', symbol: 'FX_IDC:USDIDR' },
        { id: 'eur', symbol: 'FX_IDC:EURIDR' },
        { id: 'jpy', symbol: 'FX_IDC:JPYIDR' },
        { id: 'rub', symbol: 'FX_IDC:RUBIDR' },
        { id: 'gbp', symbol: 'FX_IDC:GBPIDR' },
        { id: 'cny', symbol: 'FX_IDC:CNYIDR' },
        { id: 'aud', symbol: 'FX_IDC:AUDIDR' },
        { id: 'cad', symbol: 'FX_IDC:CADIDR' },
        { id: 'chf', symbol: 'FX_IDC:CHFIDR' },
    ];

    // Create mini chart widgets for each currency
    currencies.forEach((currency) => {
        const script = document.createElement('script');
        script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-mini-symbol-overview.js';
        script.async = true;
        script.innerHTML = JSON.stringify({
            symbol: currency.symbol,
            width: '100%',
            height: '300',
            locale: 'id',
            dateRange: '12M',
            colorTheme: 'light',
            isTransparent: false,
            autosize: true,
            largeChartUrl: '',
        });
        const container = document.getElementById(`tradingview-${currency.id}-idr`);
        if (container) container.appendChild(script);
    });

    // Currency Overview Widget
    const scriptOverview = document.createElement('script');
    scriptOverview.src = 'https://s3.tradingview.com/external-embedding/embed-widget-forex-cross-rates.js';
    scriptOverview.async = true;
    scriptOverview.innerHTML = JSON.stringify({
        width: '100%',
        height: '400',
        currencies: ['IDR', 'USD', 'EUR', 'JPY', 'RUB', 'GBP', 'CNY', 'AUD', 'CAD', 'CHF'],
        isTransparent: false,
        colorTheme: 'light',
        locale: 'id',
    });
    const containerOverview = document.getElementById('tradingview-currency-overview');
    if (containerOverview) containerOverview.appendChild(scriptOverview);
};

onUnmounted(() => {
    // Clean up all widgets
    const containers = [
        'tradingview-usd-idr',
        'tradingview-eur-idr',
        'tradingview-jpy-idr',
        'tradingview-rub-idr',
        'tradingview-gbp-idr',
        'tradingview-cny-idr',
        'tradingview-aud-idr',
        'tradingview-cad-idr',
        'tradingview-chf-idr',
        'tradingview-currency-overview',
    ];

    containers.forEach((containerId) => {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = '';
        }
    });
});
</script>
