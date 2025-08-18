<script setup lang="ts">
import {
    SidebarGroup,
    SidebarMenu,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import NavMenuItem from '@/components/NavMenuItem.vue';

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

// Recursive function to check if URL matches any item in hierarchy
const checkItemMatch = (item: NavItem, currentUrl: string): boolean => {
    // Check exact match
    if (item.href === currentUrl) return true;
    
    // Check URL prefix match (for nested routes)
    if (currentUrl.startsWith(item.href) && item.href !== '/') return true;
    
    // Check for URL with query parameters
    if (currentUrl.includes('?') && item.href.includes('?')) {
        const currentBase = currentUrl.split('?')[0];
        const itemBase = item.href.split('?')[0];
        if (currentBase === itemBase) {
            const currentParams = new URLSearchParams(currentUrl.split('?')[1] || '');
            const itemParams = new URLSearchParams(item.href.split('?')[1] || '');
            const categoryMatch = currentParams.get('category') === itemParams.get('category');
            const subcategoryMatch = currentParams.get('subcategory') === itemParams.get('subcategory');
            return categoryMatch && subcategoryMatch;
        }
    }
    
    return false;
};

// Recursive function to find and open parent items of active URL
const findAndOpenActiveItems = (items: NavItem[], currentUrl: string): boolean => {
    for (const item of items) {
        // Check if this item matches current URL
        if (checkItemMatch(item, currentUrl)) {
            return true;
        }
        
        // Check children recursively
        if (item.items && item.items.length > 0) {
            const hasActiveChild = findAndOpenActiveItems(item.items, currentUrl);
            if (hasActiveChild) {
                openItems.value.add(item.title);
                return true;
            }
        }
    }
    return false;
};

// Check if current URL matches any item and auto-open parent items
const autoOpenBasedOnUrl = () => {
    const currentUrl = page.url;
    findAndOpenActiveItems(props.items, currentUrl);
};

const toggleItem = (title: string, siblings: NavItem[], level: number) => {
    if (openItems.value.has(title)) {
        openItems.value.delete(title);
    } else {
        // Only apply accordion behavior to items with siblings and that have sub-items
        if (siblings && siblings.length > 0) {
            siblings.forEach(sibling => {
                if (sibling.title !== title && sibling.items && sibling.items.length > 0) {
                    openItems.value.delete(sibling.title);
                }
            });
        }
        
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
            <NavMenuItem 
                v-for="item in items" 
                :key="item.title" 
                :item="item" 
                :level="0"
                :open-items="openItems"
                :siblings="items"
                @toggle="(title, siblings, level) => toggleItem(title, siblings, level)"
            />
        </SidebarMenu>
    </SidebarGroup>
</template>
