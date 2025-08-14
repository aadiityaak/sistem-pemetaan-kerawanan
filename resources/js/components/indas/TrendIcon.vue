<template>
    <div v-if="numericTrend !== null" class="inline-flex items-center">
        <ArrowTrendingUpIcon v-if="numericTrend > 0" class="h-4 w-4 text-green-500" aria-hidden="true" />
        <ArrowTrendingDownIcon v-else-if="numericTrend < 0" class="h-4 w-4 text-red-500" aria-hidden="true" />
        <MinusIcon v-else class="h-4 w-4 text-gray-400" aria-hidden="true" />
    </div>
    <div v-else class="inline-flex items-center">
        <MinusIcon class="h-4 w-4 text-gray-300" aria-hidden="true" />
    </div>
</template>

<script setup lang="ts">
import { ArrowTrendingDownIcon, ArrowTrendingUpIcon, MinusIcon } from '@heroicons/vue/24/outline';
import { computed } from 'vue';

const props = defineProps<{
    trend: number | string | null | undefined;
}>();

const numericTrend = computed(() => {
    if (props.trend === null || props.trend === undefined || props.trend === '') return null;
    const numTrend = typeof props.trend === 'string' ? parseFloat(props.trend) : props.trend;
    return isNaN(numTrend) ? null : numTrend;
});
</script>
