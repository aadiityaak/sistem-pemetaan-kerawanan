<script setup lang="ts">
import { 
    SidebarGroup, 
    SidebarGroupLabel, 
    SidebarMenu, 
    SidebarMenuButton, 
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();
const openItems = ref<Set<string>>(new Set());

const toggleItem = (title: string) => {
    if (openItems.value.has(title)) {
        openItems.value.delete(title);
    } else {
        openItems.value.add(title);
    }
};

const isOpen = (title: string) => openItems.value.has(title);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <!-- Item with sub-items -->
                <SidebarMenuItem v-if="item.items && item.items.length > 0">
                    <SidebarMenuButton 
                        @click="toggleItem(item.title)"
                        :tooltip="item.title"
                        class="cursor-pointer"
                    >
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <ChevronRight 
                            class="ml-auto transition-transform" 
                            :class="{ 'rotate-90': isOpen(item.title) }"
                        />
                    </SidebarMenuButton>
                    
                    <div v-show="isOpen(item.title)" class="ml-4 mt-1">
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
