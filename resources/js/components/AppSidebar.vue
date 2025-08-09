<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Brain,
    Building2,
    Calendar,
    CalendarDays,
    Database,
    DollarSign,
    FileText,
    Globe,
    Heart,
    Landmark,
    LayoutGrid,
    Map,
    MapPin,
    ScrollText,
    Settings,
    Shield,
    ShieldAlert,
    Tags,
    Users,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const mainNavItems = ref<NavItem[]>([
    {
        title: 'IPOLEKSOSBUDKAM',
        href: '/dashboard',
        icon: LayoutGrid,
        items: [], // Will be populated dynamically with categories and subcategories
    },
    {
        title: 'ISU NEGATIF ANGGOTA BRIMOB',
        href: '/dashboard?category=keamanan&subcategory=isu-negatif-anggota-brimob',
        icon: ShieldAlert,
    },
    {
        title: 'KALENDER KAMTIBMAS',
        href: '/event?event=kamtibmas',
        icon: Calendar,
    },
    {
        title: 'AGENDA',
        href: '/event?event=agenda',
        icon: CalendarDays,
    },
    {
        title: 'INDAS',
        href: '/indas',
        icon: ScrollText,
    },
    {
        title: 'PREDIKSI AI',
        href: '/ai-prediction',
        icon: Brain,
    },
    {
        title: 'DATA CENTER',
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
]);

// Settings nav items for sidebar footer
const settingsNavItems = ref<NavItem[]>([
    {
        title: 'PENGATURAN',
        href: '/settings',
        icon: Settings,
        items: [
            {
                title: 'Pengaturan Aplikasi',
                href: '/settings',
                icon: Settings,
            },
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

        // Update the Dashboard menu items (search by href instead of title)
        const dashboardMenuIndex = mainNavItems.value.findIndex(item => item.href === '/dashboard');
        if (dashboardMenuIndex !== -1) {
            mainNavItems.value[dashboardMenuIndex].items = dashboardItems;
        }
    } catch (error) {
        console.error('Failed to load categories menu:', error);
        // Fallback to static menu if API fails (search by href instead of title)
        const dashboardMenuIndex = mainNavItems.value.findIndex(item => item.href === '/dashboard');
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
            <NavMain :items="settingsNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
