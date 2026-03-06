<script setup lang="ts">
import { SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { usePWA } from '@/composables/usePWA';

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
const { installPWA, isInstallable } = usePWA();

const handleItemClick = (e: MouseEvent, item: NavItem) => {
    if (item.title === 'Install App') {
        e.preventDefault();
        installPWA();
    }
};

const isOpen = (title: string) => {
    return props.openItems.has(title);
};
</script>

<template>
    <!-- Item with sub-items (recursive) -->
    <SidebarMenuItem v-if="item.items && item.items.length > 0">
        <!-- Top level uses SidebarMenuButton, nested levels use SidebarMenuSubButton -->
        <SidebarMenuButton v-if="level === 0" @click="handleToggle(item.title)" :tooltip="item.title" class="cursor-pointer sidebar-item-button">
            <component :is="item.icon" class="shrink-0" />
            <span class="truncate min-w-0 flex-1 whitespace-nowrap">{{ item.title }}</span>
            <ChevronRight class="ml-auto shrink-0 transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
            <div class="sparkle-container">
                <div class="sparkle" style="top: 20%; left: 30%; animation-delay: 0.5s;"></div>
                <div class="sparkle" style="top: 50%; left: 80%; animation-delay: 1s;"></div>
                <div class="sparkle" style="top: 80%; left: 10%; animation-delay: 1.5s;"></div>
            </div>
            <!-- Hacker Corner Accents -->
            <div class="corner-accent corner-top-left hidden dark:block"></div>
            <div class="corner-accent corner-top-right hidden dark:block"></div>
            <div class="corner-accent corner-bottom-left hidden dark:block"></div>
            <div class="corner-accent corner-bottom-right hidden dark:block"></div>
        </SidebarMenuButton>

        <SidebarMenuSubButton v-else @click="handleToggle(item.title)" class="cursor-pointer sidebar-item-button">
            <component :is="item.icon" class="shrink-0" />
            <span class="truncate min-w-0 flex-1 whitespace-nowrap">{{ item.title }}</span>
            <ChevronRight class="ml-auto shrink-0 transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
            <div class="sparkle-container">
                <div class="sparkle" style="top: 20%; left: 30%; animation-delay: 0.5s;"></div>
                <div class="sparkle" style="top: 50%; left: 80%; animation-delay: 1s;"></div>
                <div class="sparkle" style="top: 80%; left: 10%; animation-delay: 1.5s;"></div>
            </div>
            <!-- Hacker Corner Accents -->
            <div class="corner-accent corner-top-left hidden dark:block"></div>
            <div class="corner-accent corner-top-right hidden dark:block"></div>
            <div class="corner-accent corner-bottom-left hidden dark:block"></div>
            <div class="corner-accent corner-bottom-right hidden dark:block"></div>
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
        <SidebarMenuSubButton as-child :is-active="item.href === page.url" class="sidebar-item-button" @click="(e: MouseEvent) => handleItemClick(e, item)">
            <Link :href="item.href">
                <component :is="item.icon" class="shrink-0" />
                <span class="truncate min-w-0 flex-1 whitespace-nowrap">{{ item.title }}</span>
                <div class="sparkle-container">
                    <div class="sparkle" style="top: 20%; left: 30%; animation-delay: 0.5s;"></div>
                    <div class="sparkle" style="top: 50%; left: 80%; animation-delay: 1s;"></div>
                    <div class="sparkle" style="top: 80%; left: 10%; animation-delay: 1.5s;"></div>
                </div>
                <!-- Hacker Corner Accents -->
                <div class="corner-accent corner-top-left hidden dark:block"></div>
                <div class="corner-accent corner-top-right hidden dark:block"></div>
                <div class="corner-accent corner-bottom-left hidden dark:block"></div>
                <div class="corner-accent corner-bottom-right hidden dark:block"></div>
            </Link>
        </SidebarMenuSubButton>
    </SidebarMenuSubItem>

    <SidebarMenuItem v-else>
        <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title" class="sidebar-item-button" @click="(e: MouseEvent) => handleItemClick(e, item)">
            <Link :href="item.href">
                <component :is="item.icon" class="shrink-0" />
                <span class="truncate min-w-0 flex-1 whitespace-nowrap">{{ item.title }}</span>
                <div class="sparkle-container">
                    <div class="sparkle" style="top: 20%; left: 30%; animation-delay: 0.5s;"></div>
                    <div class="sparkle" style="top: 50%; left: 80%; animation-delay: 1s;"></div>
                    <div class="sparkle" style="top: 80%; left: 10%; animation-delay: 1.5s;"></div>
                </div>
                <!-- Hacker Corner Accents -->
                <div class="corner-accent corner-top-left hidden dark:block"></div>
                <div class="corner-accent corner-top-right hidden dark:block"></div>
                <div class="corner-accent corner-bottom-left hidden dark:block"></div>
                <div class="corner-accent corner-bottom-right hidden dark:block"></div>
            </Link>
        </SidebarMenuButton>
    </SidebarMenuItem>
</template>
