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
            let hasActiveSubItem = false;
            
            item.items.forEach((subItem) => {
                // Check exact match first
                if (subItem.href === currentUrl) {
                    hasActiveSubItem = true;
                    return;
                }

                // Check if current URL starts with sub-item href (for nested routes)
                if (currentUrl.startsWith(subItem.href) && subItem.href !== '/') {
                    hasActiveSubItem = true;
                    return;
                }

                // Check 3rd tier items
                if (subItem.items && subItem.items.length > 0) {
                    const hasActiveSubSubItem = subItem.items.some((subSubItem) => {
                        if (subSubItem.href === currentUrl) return true;
                        // Check for URL with query parameters (more flexible matching)
                        if (currentUrl.includes('?') && subSubItem.href.includes('?')) {
                            const currentBase = currentUrl.split('?')[0];
                            const itemBase = subSubItem.href.split('?')[0];
                            if (currentBase === itemBase) {
                                // Check if query parameters match
                                const currentParams = new URLSearchParams(currentUrl.split('?')[1] || '');
                                const itemParams = new URLSearchParams(subSubItem.href.split('?')[1] || '');
                                const categoryMatch = currentParams.get('category') === itemParams.get('category');
                                const subcategoryMatch = currentParams.get('subcategory') === itemParams.get('subcategory');
                                return categoryMatch && subcategoryMatch;
                            }
                        }
                        if (currentUrl.startsWith(subSubItem.href) && subSubItem.href !== '/') {
                            return true;
                        }
                        return false;
                    });

                    if (hasActiveSubSubItem) {
                        hasActiveSubItem = true;
                        openItems.value.add(subItem.title); // Open the sub-item too
                    }
                }
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
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Item with sub-items -->
                <SidebarMenuItem v-if="item.items && item.items.length > 0">
                    <SidebarMenuButton @click="toggleItem(item.title)" :tooltip="item.title" class="cursor-pointer">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <ChevronRight class="ml-auto transition-transform" :class="{ 'rotate-90': isOpen(item.title) }" />
                    </SidebarMenuButton>

                    <div v-show="isOpen(item.title)" class="mt-1">
                        <SidebarMenuSub>
                            <template v-for="subItem in item.items" :key="subItem.title">
                                <!-- Sub-item with sub-sub-items (3rd tier) -->
                                <SidebarMenuSubItem v-if="subItem.items && subItem.items.length > 0">
                                    <SidebarMenuSubButton @click="toggleItem(subItem.title)" class="cursor-pointer">
                                        <component :is="subItem.icon" />
                                        <span>{{ subItem.title }}</span>
                                        <ChevronRight class="ml-auto transition-transform" :class="{ 'rotate-90': isOpen(subItem.title) }" />
                                    </SidebarMenuSubButton>
                                    
                                    <div v-show="isOpen(subItem.title)" class="mt-1">
                                        <SidebarMenuSub>
                                            <SidebarMenuSubItem v-for="subSubItem in subItem.items" :key="subSubItem.title">
                                                <SidebarMenuSubButton as-child :is-active="subSubItem.href === page.url">
                                                    <Link :href="subSubItem.href">
                                                        <component :is="subSubItem.icon" />
                                                        <span>{{ subSubItem.title }}</span>
                                                    </Link>
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                        </SidebarMenuSub>
                                    </div>
                                </SidebarMenuSubItem>

                                <!-- Regular sub-item without sub-sub-items -->
                                <SidebarMenuSubItem v-else>
                                    <SidebarMenuSubButton as-child :is-active="subItem.href === page.url">
                                        <Link :href="subItem.href">
                                            <component :is="subItem.icon" />
                                            <span>{{ subItem.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </template>
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
