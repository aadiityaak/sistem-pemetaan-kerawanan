<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const openItems = ref<Set<string>>(new Set());

// Save open state to localStorage
const saveOpenState = () => {
    localStorage.setItem('sidebar-open-items', JSON.stringify([...openItems.value]));
};

// Load open state from localStorage
const loadOpenState = () => {
    const saved = localStorage.getItem('sidebar-open-items');
    if (saved) {
        try {
            const items = JSON.parse(saved);
            openItems.value = new Set(items);
        } catch {
            console.warn('Failed to parse saved sidebar state');
        }
    }
};

// Check if current URL matches any sub-item and auto-open parent
const autoOpenBasedOnUrl = () => {
    const currentUrl = page.url;

    props.items.forEach((item) => {
        if (item.items && item.items.length > 0) {
            // Check if current URL matches any sub-item
            const hasActiveSubItem = item.items.some((subItem) => {
                // Check exact match first
                if (subItem.href === currentUrl) return true;

                // Check if current URL starts with sub-item href (for nested routes)
                if (currentUrl.startsWith(subItem.href) && subItem.href !== '/') {
                    return true;
                }

                return false;
            });

            if (hasActiveSubItem) {
                openItems.value.add(item.title);
            }
        }
    });
};

const toggleItem = (title: string) => {
    if (openItems.value.has(title)) {
        openItems.value.delete(title);
    } else {
        openItems.value.add(title);
    }
    saveOpenState();
};

const isOpen = (title: string) => openItems.value.has(title);

// Initialize component
onMounted(() => {
    loadOpenState();
    autoOpenBasedOnUrl();
    saveOpenState();
});

// Watch for URL changes and auto-open relevant menus
watch(
    () => page.url,
    () => {
        autoOpenBasedOnUrl();
        saveOpenState();
    },
);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Item with sub-items -->
                <SidebarMenuItem v-if="item.items && item.items.length > 0">
                    <SidebarMenuButton @click="toggleItem(item.title)" :tooltip="item.title" class="cursor-pointer">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <ChevronRight class="ml-auto transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
                    </SidebarMenuButton>

                    <div v-show="isOpen(item.title)" class="mt-1 ml-4">
                        <SidebarMenuSub>
                            <SidebarMenuSubItem v-for="subItem in item.items" :key="subItem.title">
                                <SidebarMenuSubButton as-child :is-active="subItem.href === page.url">
                                    <Link :href="subItem.href">
                                        <component :is="subItem.icon" />
                                        <span>{{ subItem.title }}</span>
                                    </Link>
                                </SidebarMenuSubButton>
                            </SidebarMenuSubItem>
                        </SidebarMenuSub>
                    </div>
                </SidebarMenuItem>

                <!-- Regular item without sub-items -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
