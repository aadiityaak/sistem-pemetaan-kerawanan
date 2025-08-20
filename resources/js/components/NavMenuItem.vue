<script setup lang="ts">
import { SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

interface Props {
    item: NavItem;
    level: number;
    openItems: Set<string>;
    siblings?: NavItem[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    toggle: [title: string, siblings: NavItem[], level: number];
}>();

// Helper function to handle toggle with sibling information
const handleToggle = (title: string) => {
    emit('toggle', title, props.siblings || [], props.level);
};

const page = usePage();

const isOpen = (title: string) => {
    return props.openItems.has(title);
};
</script>

<template>
    <!-- Item with sub-items (recursive) -->
    <SidebarMenuItem v-if="item.items && item.items.length > 0">
        <!-- Top level uses SidebarMenuButton, nested levels use SidebarMenuSubButton -->
        <SidebarMenuButton v-if="level === 0" @click="handleToggle(item.title)" :tooltip="item.title" class="cursor-pointer">
            <component :is="item.icon" />
            <span>{{ item.title }}</span>
            <ChevronRight class="ml-auto transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
        </SidebarMenuButton>

        <SidebarMenuSubButton v-else @click="handleToggle(item.title)" class="cursor-pointer">
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
                    :siblings="item.items"
                    @toggle="(title, siblings, level) => emit('toggle', title, siblings, level)"
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
