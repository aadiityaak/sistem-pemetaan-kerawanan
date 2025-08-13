<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type AppPageProps, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Brain,
    Building2,
    Calendar,
    CalendarDays,
    Database,
    DollarSign,
    FileText,
    Flag,
    Globe,
    Heart,
    Landmark,
    LayoutGrid,
    Map,
    MapPin,
    Menu,
    ScrollText,
    Settings,
    Shield,
    ShieldAlert,
    ShoppingCart,
    Tags,
    TrendingUp,
    Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Interface for database menu items
interface MenuItem {
    id: number;
    title: string;
    icon?: string;
    path?: string;
    is_active: boolean;
    sort_order: number;
    parent_id?: number;
    admin_only: boolean;
    children?: MenuItem[];
}

const page = usePage<AppPageProps>();

// Check if current user is admin
const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

// Reactive menu items from database
const dbMenuItems = ref<MenuItem[]>([]);
const isLoading = ref(true);

// Convert database menu items to NavItem format
const convertMenuItemsToNavItems = (menuItems: MenuItem[]): NavItem[] => {
    return menuItems.map(item => {
        const navItem: NavItem = {
            title: item.title,
            href: item.path || '#',
            icon: getIconComponent(item.icon),
        };
        
        if (item.children && item.children.length > 0) {
            navItem.items = convertMenuItemsToNavItems(item.children);
        }
        
        return navItem;
    });
};

// Get icon component from icon name
const getIconComponent = (iconName?: string) => {
    const iconMap: Record<string, any> = {
        'LayoutGrid': LayoutGrid,
        'ShieldAlert': ShieldAlert,
        'Calendar': Calendar,
        'CalendarDays': CalendarDays,
        'ScrollText': ScrollText,
        'Database': Database,
        'Settings': Settings,
        'Brain': Brain,
        'Flag': Flag,
        'ShoppingCart': ShoppingCart,
        'DollarSign': DollarSign,
        'Tags': Tags,
        'FileText': FileText,
        'Map': Map,
        'Building2': Building2,
        'MapPin': MapPin,
        'Menu': Menu,
        'Users': Users,
        'Heart': Heart,
        'Shield': Shield,
        'Landmark': Landmark,
        'Globe': Globe,
        'TrendingUp': TrendingUp,
    };
    return iconMap[iconName || ''] || Tags;
};

// Load menu items from database
const loadMenuItems = async () => {
    try {
        isLoading.value = true;
        const response = await fetch('/api/menu-items');
        if (!response.ok) {
            throw new Error('Failed to load menu items');
        }
        dbMenuItems.value = await response.json();
    } catch (error) {
        console.error('Error loading menu items:', error);
        // Set fallback menu items in case of error
        dbMenuItems.value = [];
    } finally {
        isLoading.value = false;
    }
};

// Main nav items from database
const mainNavItems = computed<NavItem[]>(() => {
    if (isLoading.value || !dbMenuItems.value.length) {
        return [];
    }
    
    // Get main menu items (excluding PENGATURAN which goes to footer)
    const mainItems = dbMenuItems.value.filter(item => 
        item.title !== 'PENGATURAN'
    );
    
    let navItems = convertMenuItemsToNavItems(mainItems);
    
    // Add dynamic dashboard items for IPOLEKSOSBUDKAM
    const ipoleksosbudkamIndex = navItems.findIndex(item => item.title === 'IPOLEKSOSBUDKAM');
    if (ipoleksosbudkamIndex !== -1) {
        navItems[ipoleksosbudkamIndex].items = dashboardItems.value;
    }
    
    return navItems;
});

// Settings nav items for sidebar footer
const settingsNavItems = computed<NavItem[]>(() => {
    if (isLoading.value || !dbMenuItems.value.length) {
        return [];
    }
    
    // Get PENGATURAN menu item from database
    const pengaturanMenu = dbMenuItems.value.find(item => item.title === 'PENGATURAN');
    
    if (pengaturanMenu) {
        return convertMenuItemsToNavItems([pengaturanMenu]);
    }
    
    // Fallback if PENGATURAN not found in database
    const baseItems = [
        {
            title: 'Provinsi',
            href: '/provinsi',
            icon: Map,
        },
        {
            title: 'Kabupaten/Kota',
            href: '/kabupaten-kota',
            icon: Building2,
        },
        {
            title: 'Kecamatan',
            href: '/kecamatan',
            icon: MapPin,
        },
    ];

    // Add admin-only items
    if (isAdmin.value) {
        baseItems.unshift({
            title: 'User Management',
            href: '/users',
            icon: Users,
        });
        baseItems.unshift({
            title: 'Pengaturan Aplikasi',
            href: '/settings',
            icon: Settings,
        });
    }

    return [{
        title: 'PENGATURAN',
        href: isAdmin.value ? '/settings' : '/provinsi',
        icon: Settings,
        items: baseItems,
    }];
});

// Interface for API response
interface Category {
    id: number;
    name: string;
    slug: string;
    sub_categories: SubCategory[];
}

interface SubCategory {
    id: number;
    category_id: number;
    name: string;
    slug: string;
}

// Reactive categories data
const categories = ref<Category[]>([]);

// Load categories and subcategories dynamically
const loadCategoriesMenu = async () => {
    try {
        const response = await fetch('/api/categories-with-subcategories');
        categories.value = await response.json();
    } catch (error) {
        console.error('Failed to load categories menu:', error);
        // Set fallback categories
        categories.value = [
            {
                id: 1,
                name: 'Keamanan',
                slug: 'keamanan',
                sub_categories: []
            }
        ];
    }
};

// Computed dashboard items based on loaded categories
const dashboardItems = computed<NavItem[]>(() => {
    return categories.value.map(category => ({
        title: category.name,
        href: `/dashboard?category=${category.slug}`,
        icon: getIconForCategory(category.slug),
        items: category.sub_categories.map(subCategory => ({
            title: subCategory.name,
            href: `/dashboard?category=${category.slug}&subcategory=${subCategory.slug}`,
            icon: Tags, // Default icon for subcategories
        }))
    }));
});

// Get appropriate icon for category based on slug
const getIconForCategory = (slug: string) => {
    const iconMap: Record<string, any> = {
        'ideologi': Users,
        'politik': Landmark,
        'ekonomi': DollarSign,
        'sosial-budaya': Heart,
        'keamanan': Shield,
    };
    return iconMap[slug] || Tags;
};

// Load data on component mount
onMounted(() => {
    loadCategoriesMenu();
    loadMenuItems();
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavMain :items="settingsNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
