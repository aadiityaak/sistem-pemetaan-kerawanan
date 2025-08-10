<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="sidebar-content"
    data-sidebar="content"
    :class="cn('flex min-h-0 flex-1 flex-col gap-2 overflow-auto group-data-[collapsible=icon]:overflow-hidden custom-scrollbar', props.class)"
  >
    <slot />
  </div>
</template>

<style scoped>
.custom-scrollbar {
  /* Webkit browsers (Chrome, Safari, Edge) */
  scrollbar-width: thin;
  scrollbar-color: rgb(156 163 175 / 0.3) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(156 163 175 / 0.3);
  border-radius: 3px;
  transition: background 0.2s ease;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(156 163 175 / 0.5);
}

.custom-scrollbar::-webkit-scrollbar-thumb:active {
  background: rgb(156 163 175 / 0.7);
}

/* Dark mode support */
:global(.dark) .custom-scrollbar {
  scrollbar-color: rgb(75 85 99 / 0.4) transparent;
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(75 85 99 / 0.4);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(75 85 99 / 0.6);
}

:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb:active {
  background: rgb(75 85 99 / 0.8);
}
</style>
