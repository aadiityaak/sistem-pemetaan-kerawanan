<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Menu } from 'lucide-vue-next'
import { useSidebar } from './utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()

const { toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    :class="cn('!flex !items-center !justify-center gap-2 h-9 px-3 min-w-fit sm:h-7 sm:w-7 sm:px-0 sm:gap-0', props.class)"
    @click="toggleSidebar"
  >
    <span class="text-sm font-medium whitespace-nowrap sm:sr-only">Menu</span>
    <Menu class="h-6 w-6 sm:h-4 sm:w-4" />
  </Button>
</template>
