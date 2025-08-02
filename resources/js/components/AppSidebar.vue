<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    ShieldAlert, 
    Map, 
    Building2, 
    MapPin,
    Users,
    Landmark,
    DollarSign,
    Heart,
    Shield,
    Database,
    Tags,
    FileText
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        items: [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Ideologi',
                href: '/dashboard/ideologi',
                icon: Users,
            },
            {
                title: 'Politik',
                href: '/dashboard/politik',
                icon: Landmark,
            },
            {
                title: 'Ekonomi',
                href: '/dashboard/ekonomi',
                icon: DollarSign,
            },
            {
                title: 'Sosial Budaya',
                href: '/dashboard/sosial-budaya',
                icon: Heart,
            },
            {
                title: 'Keamanan',
                href: '/dashboard/keamanan',
                icon: Shield,
            },
        ]
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
        ]
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
];
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
