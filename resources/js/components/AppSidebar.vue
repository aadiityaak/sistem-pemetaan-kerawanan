<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Building2,
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

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        items: [
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
        ],
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
