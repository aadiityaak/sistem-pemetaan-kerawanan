<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    Database,
    DollarSign,
    FileText,
    Globe,
    Heart,
    Landmark,
    LayoutGrid,
    Map,
    MapPin,
    Settings,
    Shield,
    ShieldAlert,
    Tags,
    Users,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const mainNavItems = ref<NavItem[]>([
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        items: [], // Will be populated dynamically with categories and subcategories
    },
    {
        title: 'Data Monitoring',
        href: '/monitoring-data',
        icon: ShieldAlert,
        items: [
            {
                title: 'Data Monitoring',
                href: '/monitoring-data',
                icon: Database,
            },
            {
                title: 'Kategori',
                href: '/categories',
                icon: Tags,
            },
            {
                title: 'Sub Kategori',
                href: '/sub-categories',
                icon: FileText,
            },
        ],
    },
    {
        title: 'Kalender Kamtipmas',
        href: '/kamtipmas-calendar',
        icon: Calendar,
    },
    {
        title: 'Wilayah',
        href: '/provinsi',
        icon: Globe,
        items: [
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
        ],
    },
    {
        title: 'Pengaturan',
        href: '/settings',
        icon: Settings,
    },
]);

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

// Load categories and subcategories dynamically
const loadCategoriesMenu = async () => {
    try {
        const response = await fetch('/api/categories-with-subcategories');
        const categories: Category[] = await response.json();
        
        // Map categories to navigation items for Dashboard submenu
        const dashboardItems: NavItem[] = categories.map(category => ({
            title: category.name,
            href: `/dashboard?category=${category.slug}`,
            icon: getIconForCategory(category.slug),
            items: category.sub_categories.map(subCategory => ({
                title: subCategory.name,
                href: `/dashboard?category=${category.slug}&subcategory=${subCategory.slug}`,
                icon: Tags, // Default icon for subcategories
            }))
        }));

        // Update the Dashboard menu items
        const dashboardMenuIndex = mainNavItems.value.findIndex(item => item.title === 'Dashboard');
        if (dashboardMenuIndex !== -1) {
            mainNavItems.value[dashboardMenuIndex].items = dashboardItems;
        }
    } catch (error) {
        console.error('Failed to load categories menu:', error);
        // Fallback to static menu if API fails
        const dashboardMenuIndex = mainNavItems.value.findIndex(item => item.title === 'Dashboard');
        if (dashboardMenuIndex !== -1) {
            mainNavItems.value[dashboardMenuIndex].items = [
                {
                    title: 'Keamanan',
                    href: '/dashboard?category=keamanan',
                    icon: Shield,
                },
            ];
        }
    }
};

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

// Load categories on component mount
onMounted(() => {
    loadCategoriesMenu();
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
