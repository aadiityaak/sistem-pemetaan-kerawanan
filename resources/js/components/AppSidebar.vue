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

// Check if current user is super admin only (for sidebar footer)
const isSuperAdmin = computed(() => {
    const userRole = page.props.auth?.user?.role;
    return userRole === 'super_admin';
});

// Reactive menu items from database
const dbMenuItems = ref<MenuItem[]>([]);
const isLoading = ref(true);
const { isInstallable } = usePWA();

// Convert database menu items to NavItem format (recursive for unlimited depth)
const convertMenuItemsToNavItems = (menuItems: MenuItem[]): NavItem[] => {
    return menuItems
        .filter((item) => {
            // Hide "Install App" if not installable
            if (item.title === 'Install App') {
                return isInstallable.value;
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
        // Fallback items when database is not available
        return [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Peta Bencana',
                href: '/peta-bencana',
                icon: AlertTriangle,
            },
            {
                title: 'Peta Kriminalitas',
                href: '/peta-kriminalitas',
                icon: Shield,
            },
        ];
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
            title: 'Pengaturan Aplikasi',
            href: '/settings',
            icon: Settings,
            adminOnly: true,
        },
        {
            title: 'User Management',
            href: '/users',
            icon: Users,
            adminOnly: true,
        },
        {
            title: 'Provinsi',
            href: '/provinsi',
            icon: Map,
            adminOnly: false,
        },
        {
            title: 'Kabupaten/Kota',
            href: '/kabupaten-kota',
            icon: Building2,
            adminOnly: false,
        },
        {
            title: 'Kecamatan',
            href: '/kecamatan',
            icon: MapPin,
            adminOnly: false,
        },
        {
            title: 'Install App',
            href: '#',
            icon: Download,
            adminOnly: false,
        },
    ];

    // Filter fallback items by role and installability
    const filteredBaseItems = baseItems.filter(item => {
        if (item.adminOnly && !isSuperAdmin.value) return false;
        if (item.title === 'Install App' && !isInstallable.value) return false;
        return true;
    });

    return [
        {
            title: 'PENGATURAN',
            href: '/settings',
            icon: Settings,
            items: filteredBaseItems,
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
            <NavMain v-if="isSuperAdmin" :items="settingsNavItems" :collapse-on-inactive="true" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
