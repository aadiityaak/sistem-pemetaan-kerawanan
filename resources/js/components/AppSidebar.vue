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
import { computed, onMounted, ref } from 'vue';

const page = usePage<AppPageProps>();

// Check if current user is admin
const isAdmin = computed(() => {
    return page.props.auth?.user?.role === 'admin';
});

// Main nav items (computed to handle admin-only items)
const mainNavItems = computed<NavItem[]>(() => {
    const baseItems: NavItem[] = [
    {
        title: 'IPOLEKSOSBUDKAM',
        href: '/dashboard',
        icon: LayoutGrid,
        items: dashboardItems.value, // Use computed dashboard items
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
        title: 'AGENDA INTERNAL KORP BRIMOB POLRI',
        href: '/agenda-internal-korp-brimob',
        icon: Calendar,
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
    ];

    // Add DATA CENTER menu with role-based items
    const dataCenterItems = [
        {
            title: 'Data Monitoring',
            href: '/monitoring-data',
            icon: Database,
        },
    ];

    // Add admin-only category management items
    if (isAdmin.value) {
        dataCenterItems.push(
            {
                title: 'Kategori',
                href: '/categories',
                icon: Tags,
            },
            {
                title: 'Sub Kategori',
                href: '/sub-categories',
                icon: FileText,
            }
        );
    }

    baseItems.push({
        title: 'DATA CENTER',
        href: '/monitoring-data',
        icon: ShieldAlert,
        items: dataCenterItems,
    });

    return baseItems;
});

// Settings nav items for sidebar footer (computed to include admin-only items)
const settingsNavItems = computed<NavItem[]>(() => {
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
