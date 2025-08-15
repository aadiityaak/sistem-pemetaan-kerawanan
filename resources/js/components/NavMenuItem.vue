<script setup lang="ts">
import {
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

interface Props {
    item: NavItem;
    level: number;
    openItems: Set<string>;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    toggle: [title: string];
}>();

const page = usePage();

const isOpen = (title: string) => {
    return props.openItems.has(title);
};
</script>

<template>
    <!-- Item with sub-items (recursive) -->
    <SidebarMenuItem v-if="item.items && item.items.length > 0">
        <!-- Top level uses SidebarMenuButton, nested levels use SidebarMenuSubButton -->
        <SidebarMenuButton 
            v-if="level === 0"
            @click="emit('toggle', item.title)" 
            :tooltip="item.title" 
            class="cursor-pointer"
        >
            <component :is="item.icon" />
            <span>{{ item.title }}</span>
            <ChevronRight class="ml-auto transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
        </SidebarMenuButton>

        <SidebarMenuSubButton 
            v-else
            @click="emit('toggle', item.title)" 
            class="cursor-pointer"
        >
            <component :is="item.icon" />
            <span>{{ item.title }}</span>
            <ChevronRight class="ml-auto transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
        </SidebarMenuSubButton>

        <div v-show="isOpen(item.title)" class="mt-1">
            <SidebarMenuSub>
                <NavMenuItem
                    v-for="subItem in item.items"
                    :key="subItem.title"
                    :item="subItem"
                    :level="level + 1"
                    :open-items="openItems"
                    @toggle="emit('toggle', $event)"
                />
            </SidebarMenuSub>
        </div>
    </SidebarMenuItem>

    <!-- Regular item without sub-items -->
    <SidebarMenuSubItem v-else-if="level > 0">
        <SidebarMenuSubButton as-child :is-active="item.href === page.url">
            <Link :href="item.href">
                <component :is="item.icon" />
                <span>{{ item.title }}</span>
            </Link>
        </SidebarMenuSubButton>
    </SidebarMenuSubItem>

    <!-- Top level item without sub-items -->
    <SidebarMenuItem v-else>
        <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
            <Link :href="item.href">
                <component :is="item.icon" />
                <span>{{ item.title }}</span>
            </Link>
        </SidebarMenuButton>
    </SidebarMenuItem>
</template>