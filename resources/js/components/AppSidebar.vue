<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type AppPageProps, type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BarChart3,
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
    Activity,
    Crosshair,
    Download,
} from 'lucide-vue-next';
import { usePWA } from '@/composables/usePWA';
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
    permissions?: string[];
    description?: string;
    children?: MenuItem[];
}

const page = usePage<AppPageProps>();
const { isInstallable } = usePWA();

const isSuperAdmin = computed(() => page.props.auth?.user?.role === 'super_admin');
const hasAdminAccess = computed(() => {
    const role = page.props.auth?.user?.role;
    return role === 'super_admin' || role === 'admin_vip' || role === 'admin';
});

// Convert database menu items to NavItem format (recursive for unlimited depth)
const convertMenuItemsToNavItems = (menuItems: MenuItem[]): NavItem[] => {
    return menuItems
        .filter((item) => {
            // Hide "Install App" if not installable
            if (item.title === 'Install App') {
                return isInstallable.value;
            }
            
            // Hide admin-only items from users without admin access
            if (item.admin_only && !hasAdminAccess.value) {
                return false;
            }
            
            return true;
        })
        .map((item) => {
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
        AlertTriangle: AlertTriangle,
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
        BarChart3: BarChart3,
        Activity: Activity,
        Crosshair: Crosshair,
        Download: Download,
    };
    return iconMap[iconName || ''] || Tags;
};

// Main nav items from shared props
const mainNavItems = computed<NavItem[]>(() => {
    const menuItems = page.props.menuItems || [];
    const mainItems = menuItems.filter((item) => item.title !== 'PENGATURAN');
    return convertMenuItemsToNavItems(mainItems);
});

// Settings nav items for sidebar footer
const settingsNavItems = computed<NavItem[]>(() => {
    const menuItems = page.props.menuItems || [];
    const pengaturanMenu = menuItems.find((item) => item.title === 'PENGATURAN');
    return pengaturanMenu ? convertMenuItemsToNavItems([pengaturanMenu]) : [];
});

// Check if current user can manage settings (Super Admin and Admin only, not Admin VIP)
const canManageSettings = computed(() => {
    const userRole = page.props.auth?.user?.role;
    return userRole === 'super_admin' || userRole === 'admin';
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="sidebar-item-button">
                        <Link :href="route('dashboard')">
                            <AppLogo />
                            <div class="sparkle-container">
                                <div class="sparkle" style="top: 20%; left: 30%; animation-delay: 0.5s;"></div>
                                <div class="sparkle" style="top: 50%; left: 80%; animation-delay: 1s;"></div>
                                <div class="sparkle" style="top: 80%; left: 10%; animation-delay: 1.5s;"></div>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavMain v-if="canManageSettings" :items="settingsNavItems" :collapse-on-inactive="true" :hide-sub-items="true" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
