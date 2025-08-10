<template>
  <div v-if="trend !== null" class="inline-flex items-center">
    <ArrowTrendingUpIcon 
      v-if="trend > 0" 
      class="h-4 w-4 text-green-500" 
      aria-hidden="true" 
    />
    <ArrowTrendingDownIcon 
      v-else-if="trend < 0" 
      class="h-4 w-4 text-red-500" 
      aria-hidden="true" 
    />
    <MinusIcon 
      v-else 
      class="h-4 w-4 text-gray-400" 
      aria-hidden="true" 
    />
  </div>
  <div v-else class="inline-flex items-center">
    <MinusIcon class="h-4 w-4 text-gray-300" aria-hidden="true" />
  </div>
</template>

<script setup lang="ts">
import { 
  ArrowTrendingUpIcon, 
  ArrowTrendingDownIcon, 
  MinusIcon 
} from '@heroicons/vue/24/outline'

defineProps<{
  trend: number | null
}>()
</script>