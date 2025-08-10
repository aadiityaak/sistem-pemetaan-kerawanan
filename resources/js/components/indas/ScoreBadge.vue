<template>
  <span 
    :class="badgeClass"
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
  >
    {{ formatScore(score) }}
  </span>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  score: number
}>()

const formatScore = (score: number) => {
  return score ? score.toFixed(1) : '0.0'
}

const badgeClass = computed(() => {
  if (props.score >= 80) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
  } else if (props.score >= 60) {
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
  } else if (props.score >= 40) {
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
  } else if (props.score >= 20) {
    return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
  } else {
    return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
})
</script>