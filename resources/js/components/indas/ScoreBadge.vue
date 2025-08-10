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
  score: number | string | null | undefined
}>()

const formatScore = (score: number | string | null | undefined) => {
  if (score === null || score === undefined || score === '') return '0.0'
  const numScore = typeof score === 'string' ? parseFloat(score) : score
  return isNaN(numScore) ? '0.0' : numScore.toFixed(1)
}

const numericScore = computed(() => {
  if (props.score === null || props.score === undefined || props.score === '') return 0
  const numScore = typeof props.score === 'string' ? parseFloat(props.score) : props.score
  return isNaN(numScore) ? 0 : numScore
})

const badgeClass = computed(() => {
  const score = numericScore.value
  if (score >= 80) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
  } else if (score >= 60) {
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
  } else if (score >= 40) {
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
  } else if (score >= 20) {
    return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
  } else {
    return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
})
</script>