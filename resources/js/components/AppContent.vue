<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="cn('relative overflow-hidden', className)">
        <div class="relative z-10">
            <slot />
        </div>
    </SidebarInset>
    <main v-else :class="cn('mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl relative overflow-hidden', className)">
        <div class="relative z-10">
            <slot />
        </div>
    </main>
</template>

<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>
