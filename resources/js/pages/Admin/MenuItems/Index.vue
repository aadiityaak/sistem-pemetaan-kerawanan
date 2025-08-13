<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Menu Items</h1>
                    <p class="text-muted-foreground">Kelola menu navigasi sistem</p>
                </div>
                <Button as-child>
                    <Link :href="route('admin.menu-items.create')">
                        <Plus class="w-4 h-4 mr-2" />
                        Tambah Menu
                    </Link>
                </Button>
            </div>

            <!-- Menu Items Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Daftar Menu Items</CardTitle>
                    <CardDescription>
                        Menu items yang terdaftar dalam sistem
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-4">
                        <!-- Main Menu Items -->
                        <div v-for="item in mainMenuItems" :key="item.id" class="border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span 
                                        :class="[
                                            'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                            item.is_active 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <div>
                                        <h3 class="font-medium">{{ item.title }}</h3>
                                        <p class="text-sm text-muted-foreground">
                                            Path: {{ item.path || 'No path set' }}
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            Icon: {{ item.icon || 'No icon' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Button 
                                        size="sm" 
                                        variant="outline"
                                        @click="toggleStatus(item)"
                                    >
                                        {{ item.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </Button>
                                    <Button size="sm" variant="outline" as-child>
                                        <Link :href="route('admin.menu-items.edit', item.id)">
                                            <Edit class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                    <Button 
                                        size="sm" 
                                        variant="destructive"
                                        @click="deleteItem(item)"
                                    >
                                        <Trash class="w-4 h-4" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Sub Menu Items -->
                            <div v-if="item.children && item.children.length > 0" class="mt-4 ml-6 space-y-2">
                                <h4 class="text-sm font-medium text-muted-foreground">Sub Menu:</h4>
                                <div v-for="child in item.children" :key="child.id" class="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <span 
                                            :class="[
                                                'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold',
                                                child.is_active 
                                                    ? 'bg-green-100 text-green-800' 
                                                    : 'bg-gray-100 text-gray-800'
                                            ]"
                                        >
                                            {{ child.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium">{{ child.title }}</p>
                                            <p class="text-xs text-muted-foreground">
                                                Path: {{ child.path || 'No path set' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            @click="toggleStatus(child)"
                                        >
                                            {{ child.is_active ? 'Off' : 'On' }}
                                        </Button>
                                        <Button size="sm" variant="outline" as-child>
                                            <Link :href="route('admin.menu-items.edit', child.id)">
                                                <Edit class="w-3 h-3" />
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppSidebarLayout>
</template>

<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Trash } from 'lucide-vue-next';
import { computed } from 'vue';

interface MenuItem {
    id: number;
    title: string;
    icon?: string;
    path?: string;
    is_active: boolean;
    sort_order: number;
    parent_id?: number;
    admin_only: boolean;
    description?: string;
    children?: MenuItem[];
}

interface Props {
    menuItems: MenuItem[];
}

const props = defineProps<Props>();

const breadcrumbs = [
    { label: 'Admin', href: '#' },
    { label: 'Menu Items', href: route('admin.menu-items.index') },
];

// Separate main menu items (no parent) from children
const mainMenuItems = computed(() => {
    return props.menuItems.filter(item => !item.parent_id);
});

const toggleStatus = (item: MenuItem) => {
    router.post(route('admin.menu-items.toggle-status', item.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh the page to update the menu state
            router.reload();
        }
    });
};

const deleteItem = (item: MenuItem) => {
    if (confirm(`Apakah Anda yakin ingin menghapus menu "${item.title}"?`)) {
        router.delete(route('admin.menu-items.destroy', item.id), {
            preserveScroll: true,
        });
    }
};
</script>