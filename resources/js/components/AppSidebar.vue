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

// Check if current user can manage settings (Super Admin and Admin only, not Admin VIP)
const canManageSettings = computed(() => {
    const userRole = page.props.auth?.user?.role;
    return userRole === 'super_admin' || userRole === 'admin';
});

// Check if current user has admin access (includes all admin types)
const hasAdminAccess = computed(() => {
    const userRole = page.props.auth?.user?.role;
    return userRole === 'super_admin' || userRole === 'admin_vip' || userRole === 'admin';
});

// Reactive menu items from database
const dbMenuItems = ref<MenuItem[]>([]);
const isLoading = ref(true);

// Convert database menu items to NavItem format (recursive for unlimited depth)
const convertMenuItemsToNavItems = (menuItems: MenuItem[]): NavItem[] => {
    return menuItems.map((item) => {
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
        LayoutGrid: LayoutGrid,
        ShieldAlert: ShieldAlert,
        Calendar: Calendar,
        CalendarDays: CalendarDays,
        ScrollText: ScrollText,
        Database: Database,
        Settings: Settings,
        Brain: Brain,
        Flag: Flag,
        ShoppingCart: ShoppingCart,
        DollarSign: DollarSign,
        Tags: Tags,
        FileText: FileText,
        Map: Map,
        Building2: Building2,
        MapPin: MapPin,
        Menu: Menu,
        Users: Users,
        Heart: Heart,
        Shield: Shield,
        Landmark: Landmark,
        Globe: Globe,
        TrendingUp: TrendingUp,
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
    const mainItems = dbMenuItems.value.filter((item) => item.title !== 'PENGATURAN');

    return convertMenuItemsToNavItems(mainItems);
});

// Settings nav items for sidebar footer
const settingsNavItems = computed<NavItem[]>(() => {
    if (isLoading.value || !dbMenuItems.value.length) {
        return [];
    }

    // Get PENGATURAN menu item from database
    const pengaturanMenu = dbMenuItems.value.find((item) => item.title === 'PENGATURAN');

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

    // Add admin access items (User Management for Super Admin and Admin VIP)
    if (hasAdminAccess.value) {
        // User Management - visible to all admin types but only Super Admin can edit
        baseItems.unshift({
            title: 'User Management',
            href: '/users',
            icon: Users,
        });
    }
    
    // Add settings for users who can manage settings (Super Admin and Admin only)
    if (canManageSettings.value) {
        baseItems.unshift({
            title: 'Pengaturan Aplikasi',
            href: '/settings',
            icon: Settings,
        });
    }

    return [
        {
            title: 'PENGATURAN',
            href: hasAdminAccess.value ? (canManageSettings.value ? '/settings' : '/users') : '/provinsi',
            icon: Settings,
            items: baseItems,
        },
    ];
});

// Load data on component mount
onMounted(() => {
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
