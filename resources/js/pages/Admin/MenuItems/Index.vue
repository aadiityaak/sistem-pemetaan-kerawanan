<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Menu Items</h1>
                    <p class="text-muted-foreground">Kelola menu navigasi dengan drag & drop seperti WordPress</p>
                </div>
                <div class="flex items-center space-x-3">
                    <Button variant="outline" @click="resetMenu" :disabled="loading" class="border-orange-300 text-orange-700 hover:bg-orange-50">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        Reset Menu
                    </Button>
                    <Button as-child>
                        <Link :href="route('admin.menu-items.create')">
                            <Plus class="mr-2 h-4 w-4" />
                            Tambah Menu
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Menu Items Tree -->
            <Card>
                <CardHeader>
                    <CardTitle>Daftar Menu Items</CardTitle>
                    <CardDescription> Seret menu untuk mengatur urutan. Sub menu menjorok seperti WordPress Admin. </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-1">
                        <!-- Draggable Menu Tree -->
                        <div ref="sortableContainer" class="relative space-y-1">
                            <div
                                v-for="(item, index) in flatMenuItems"
                                :key="item.id"
                                :data-id="item.id"
                                :data-index="index"
                                :class="[
                                    'group cursor-move border-l-4 bg-background transition-colors hover:bg-muted/30',
                                    getMenuItemClasses(item),
                                    'sortable-item',
                                ]"
                            >
                                <div class="flex items-center justify-between p-3">
                                    <div class="flex flex-1 items-center space-x-3">
                                        <!-- Drag Handle -->
                                        <div
                                            class="drag-handle cursor-grab text-gray-400 opacity-100 transition-opacity hover:text-blue-500 active:cursor-grabbing"
                                        >
                                            <GripVertical class="h-4 w-4" />
                                        </div>

                                        <!-- WordPress-style Hierarchy Lines -->
                                        <div class="flex items-center">
                                            <!-- Indentation spacer -->
                                            <div v-if="(item.level || 0) > 0" class="flex items-center">
                                                <div v-for="i in item.level || 0" :key="i" class="flex w-6 justify-center">
                                                    <div class="h-8 w-px bg-gray-300"></div>
                                                </div>
                                                <div class="relative h-px w-4 bg-gray-300">
                                                    <div class="absolute -top-4 right-0 h-4 w-px bg-gray-300"></div>
                                                </div>
                                            </div>

                                            <!-- Status Badge -->
                                            <span
                                                :class="[
                                                    'ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                                    item.is_active
                                                        ? 'border border-green-200 bg-green-100 text-green-800'
                                                        : 'border border-gray-200 bg-gray-100 text-gray-800',
                                                ]"
                                            >
                                                {{ item.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>

                                        <!-- Menu Info -->
                                        <div class="min-w-0 flex-1">
                                            <h3
                                                :class="[
                                                    'truncate font-medium',
                                                    item.level === 0 ? 'text-base text-gray-900' : 'text-sm text-gray-700'
                                                ]"
                                            >
                                                {{ item.title }}
                                            </h3>
                                            <div class="mt-1 space-y-1">
                                                <p class="truncate text-xs text-gray-500">
                                                    <strong>Path:</strong>
                                                    <code class="rounded bg-gray-100 px-1 text-xs">{{ item.path || 'No path set' }}</code>
                                                </p>
                                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                    <span v-if="item.icon"> <strong>Icon:</strong> {{ item.icon }} </span>
                                                    <span> <strong>Order:</strong> {{ item.sort_order }} </span>
                                                    <span v-if="item.parent_id"> <strong>Parent ID:</strong> {{ item.parent_id }} </span>
                                                </div>
                                                
                                                <!-- Role Visibility Badges -->
                                                <div class="flex items-center gap-1 mt-2">
                                                    <span class="text-xs text-gray-500 mr-1"><strong>Tampil untuk:</strong></span>
                                                    <div v-if="getRoleVisibility(item).length === 0" class="flex items-center">
                                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                            Semua User
                                                        </span>
                                                    </div>
                                                    <div v-else class="flex flex-wrap gap-1">
                                                        <span
                                                            v-for="role in getRoleVisibility(item)"
                                                            :key="role"
                                                            :class="getRoleBadgeClass(role)"
                                                            class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium"
                                                        >
                                                            {{ getRoleLabel(role) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2 opacity-0 transition-opacity group-hover:opacity-100">
                                        <!-- Hierarchy Controls -->
                                        <div class="flex items-center space-x-1 border-r border-gray-300 pr-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="outdentMenu(item)"
                                                :disabled="loading || (item.level || 0) === 0"
                                                :title="(item.level || 0) === 0 ? 'Sudah di level tertinggi' : 'Pindah ke kiri (kurangi level)'"
                                                class="px-2 py-1 text-xs"
                                            >
                                                <ChevronLeft class="h-3 w-3" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="indentMenu(item)"
                                                :disabled="loading || !canIndent(item)"
                                                :title="
                                                    !canIndent(item)
                                                        ? 'Tidak ada menu sebelumnya untuk dijadikan parent'
                                                        : 'Pindah ke kanan (jadi sub menu)'
                                                "
                                                class="px-2 py-1 text-xs"
                                            >
                                                <ChevronRight class="h-3 w-3" />
                                            </Button>
                                        </div>

                                        <!-- Status & Edit Controls -->
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click.stop="toggleStatus(item)"
                                            :disabled="loading"
                                            class="text-xs"
                                            type="button"
                                        >
                                            {{ item.is_active ? 'OFF' : 'ON' }}
                                        </Button>
                                        <Button size="sm" variant="outline" as-child>
                                            <Link :href="route('admin.menu-items.edit', item.id)">
                                                <Edit class="h-3 w-3" />
                                            </Link>
                                        </Button>
                                        <Button size="sm" variant="destructive" @click.stop="deleteItem(item)" :disabled="loading" type="button">
                                            <Trash class="h-3 w-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Drag & Drop Instructions -->
            <Card class="border-blue-200 bg-blue-50">
                <CardContent class="pt-6">
                    <div class="flex items-start space-x-3">
                        <Info class="mt-0.5 h-5 w-5 text-blue-600" />
                        <div class="text-sm text-blue-800">
                            <h4 class="mb-2 font-medium">Cara Menggunakan Interface:</h4>
                            <ul class="space-y-1 text-xs">
                                <li>
                                    • <strong>Drag & Drop:</strong> Seret handle <GripVertical class="mx-1 inline h-3 w-3" /> untuk mengatur urutan
                                    menu (parent menu akan membawa semua sub-menu)
                                </li>
                                <li>
                                    • <strong>Tombol Panah Kanan <ChevronRight class="mx-1 inline h-3 w-3" />:</strong> Jadikan sub-menu dari menu
                                    sebelumnya
                                </li>
                                <li>
                                    • <strong>Tombol Panah Kiri <ChevronLeft class="mx-1 inline h-3 w-3" />:</strong> Pindahkan ke level yang lebih
                                    tinggi
                                </li>
                                <li>
                                    • <strong>Visual Hierarki:</strong> Level 0 (border biru), Level 1 (border hijau), Level 2 (border kuning), Level
                                    3 (border ungu), Level 4 (border pink), Level 5+ (border abu-abu) - Support unlimited depth
                                </li>
                                <li>• <strong>Toggle Status:</strong> Gunakan tombol ON/OFF untuk mengatur visibilitas menu</li>
                                <li>• <strong>Role Badges:</strong> Badge berwarna menunjukkan siapa yang dapat melihat menu (Super Admin, Admin VIP, Admin)</li>
                                <li>• <strong>Kontrol Terpisah:</strong> Drag untuk urutan, tombol panah untuk hierarki</li>
                                <li>• <strong>Reset Menu:</strong> Tombol "Reset Menu" untuk mengembalikan ke pengaturan default sistem</li>
                            </ul>
                            <div v-if="dragStatus || hierarchyStatus" class="mt-2 rounded bg-blue-100 p-2 text-xs">
                                <strong>Status:</strong> {{ dragStatus || hierarchyStatus }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Edit, GripVertical, Info, Plus, RotateCcw, Trash } from 'lucide-vue-next';
import Sortable from 'sortablejs';
import { nextTick, onMounted, ref, watch } from 'vue';

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
    allChildren?: MenuItem[]; // For admin interface - includes inactive children
    level?: number; // For indentation
}

interface Props {
    menuItems: MenuItem[];
}

const props = defineProps<Props>();
const loading = ref(false);
const sortableContainer = ref<HTMLElement>();
const dragStatus = ref('');
const hierarchyStatus = ref('');

// Expose debug function to window for console testing
if (typeof window !== 'undefined') {
    (window as any).debugMenuHierarchy = () => {
        console.log('Current menu hierarchy:');
        flatMenuItems.value.forEach((item, index) => {
            console.log(`${index}: ${item.title} (level ${item.level}, parent: ${item.parent_id})`);
        });
    };
}

const breadcrumbs = [
    { title: 'Admin', label: 'Admin', href: '#' },
    { title: 'Menu Items', label: 'Menu Items', href: route('admin.menu-items.index') },
];

// Convert hierarchical menu to flat list with level indicators (WordPress style)
const flatMenuItems = ref<MenuItem[]>([]);

const updateFlatMenuItems = () => {
    const flattenMenu = (items: MenuItem[], level = 0): MenuItem[] => {
        const result: MenuItem[] = [];

        // Sort items by sort_order
        const sortedItems = [...items].sort((a, b) => a.sort_order - b.sort_order);

        for (const item of sortedItems) {
            // Add current item with level
            result.push({
                ...item,
                level,
            });

            // Add children recursively - supports unlimited depth
            if (item.children && item.children.length > 0) {
                result.push(...flattenMenu(item.children, level + 1));
            }
        }

        return result;
    };

    // Get main menu items (no parent) with their children
    const mainItems = props.menuItems.filter((item) => !item.parent_id);
    flatMenuItems.value = flattenMenu(mainItems);
};

// Initialize flat menu items
updateFlatMenuItems();

// Watch for changes in props to update flat menu items
watch(
    () => props.menuItems,
    () => {
        updateFlatMenuItems();
    },
    { deep: true },
);

// Get menu item classes based on level (WordPress style with unlimited depth)
const getMenuItemClasses = (item: MenuItem) => {
    const level = item.level || 0;

    // Base classes
    let classes = 'relative ';

    // Level-specific styling with dynamic indentation
    const indentPixels = level * 24; // 24px per level

    // Color scheme based on level
    const levelColors = [
        'border-l-blue-500 bg-blue-50/30', // Level 0 - Main menu
        'border-l-green-500 bg-green-50/20', // Level 1 - Sub menu
        'border-l-yellow-500 bg-yellow-50/20', // Level 2 - Sub-sub menu
        'border-l-purple-500 bg-purple-50/20', // Level 3
        'border-l-pink-500 bg-pink-50/20', // Level 4
        'border-l-indigo-500 bg-indigo-50/20', // Level 5
    ];

    // Use appropriate color or fallback to gray for deeper levels
    if (level < levelColors.length) {
        classes += levelColors[level] + ' ';
    } else {
        classes += 'border-l-gray-400 bg-gray-50/20 ';
    }

    // Add dynamic margin-left for indentation
    if (level > 0) {
        classes += `ml-[${indentPixels}px] `;
    }

    // Admin-only styling
    if (item.admin_only) {
        classes += 'border-r-4 border-r-orange-400 ';
    }

    // Inactive styling
    if (!item.is_active) {
        classes += 'opacity-60 ';
    }

    return classes;
};

// Role visibility helper functions
const getRoleVisibility = (item: MenuItem): string[] => {
    // Priority: use permissions field if available, otherwise fall back to admin_only
    if (item.permissions && item.permissions.length > 0) {
        return item.permissions;
    }
    
    // Fallback for backward compatibility with admin_only field
    if (item.admin_only) {
        return ['super_admin', 'admin'];
    }
    
    return [];
};

const getRoleLabel = (role: string): string => {
    const labels: Record<string, string> = {
        'super_admin': 'Super Admin',
        'admin_vip': 'Admin VIP',
        'admin': 'Admin'
    };
    return labels[role] || role;
};

const getRoleBadgeClass = (role: string): string => {
    const classes: Record<string, string> = {
        'super_admin': 'bg-purple-100 text-purple-800 border border-purple-200',
        'admin_vip': 'bg-blue-100 text-blue-800 border border-blue-200',
        'admin': 'bg-orange-100 text-orange-800 border border-orange-200'
    };
    return classes[role] || 'bg-gray-100 text-gray-800 border border-gray-200';
};

// Initialize Sortable drag & drop
onMounted(() => {
    nextTick(() => {
        if (sortableContainer.value) {
            console.log('Initializing Sortable on:', sortableContainer.value);
            Sortable.create(sortableContainer.value, {
                handle: '.drag-handle',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                fallbackTolerance: 3,
                forceFallback: false,
                scrollSensitivity: 100,
                scrollSpeed: 10,

                onStart: (evt: any) => {
                    console.log('Drag started:', evt);
                    const draggedElement = evt.item;
                    const draggedIndex = parseInt(draggedElement.dataset.index);
                    const draggedItem = flatMenuItems.value[draggedIndex];

                    // Check if dragging a parent with children
                    if (draggedItem.level === 0) {
                        const children = flatMenuItems.value.filter((item) => item.parent_id === draggedItem.id);
                        if (children.length > 0) {
                            dragStatus.value = `Menyeret "${draggedItem.title}" dengan ${children.length} sub-menu...`;
                        } else {
                            dragStatus.value = `Menyeret menu "${draggedItem.title}"...`;
                        }
                    } else {
                        dragStatus.value = `Menyeret menu "${draggedItem.title}"...`;
                    }
                },

                onEnd: async (evt: any) => {
                    const { oldIndex, newIndex } = evt;

                    if (oldIndex === newIndex) {
                        dragStatus.value = '';
                        return;
                    }

                    dragStatus.value = 'Menyimpan urutan menu...';

                    try {
                        const items = [...flatMenuItems.value];
                        const movedItem = items[oldIndex!];

                        // Find all children that belong to this parent (recursive)
                        const childrenToMove: MenuItem[] = [];
                        const findChildren = (parentId: number, startIndex: number) => {
                            for (let i = startIndex; i < items.length; i++) {
                                const item = items[i];
                                // Stop when we hit another top-level menu
                                if (item.level === 0 && i !== oldIndex!) break;

                                // If this item belongs to our parent hierarchy
                                if (item.parent_id === parentId) {
                                    childrenToMove.push(item);
                                    // Recursively find children of this child
                                    findChildren(item.id, i + 1);
                                }
                            }
                        };

                        // Only find children if this is a parent menu
                        if (movedItem.level === 0) {
                            findChildren(movedItem.id, oldIndex! + 1);
                        }

                        console.log(
                            'Moving parent:',
                            movedItem.title,
                            'with children:',
                            childrenToMove.map((c) => c.title),
                        );

                        // Remove parent and all its children from original positions
                        const itemsToMove = [movedItem, ...childrenToMove];
                        const remainingItems = items.filter((item) => !itemsToMove.some((moved) => moved.id === item.id));

                        // Calculate new position considering the group size
                        let insertIndex = newIndex!;
                        if (newIndex! > oldIndex!) {
                            // Moving down, adjust for removed items
                            insertIndex = Math.max(0, newIndex! - itemsToMove.length + 1);
                        }

                        // Insert parent and children at new position
                        const newItems = [...remainingItems.slice(0, insertIndex), ...itemsToMove, ...remainingItems.slice(insertIndex)];

                        // Update only sort order (no hierarchy changes during drag)
                        const updateData = newItems.map((item, index) => ({
                            id: item.id,
                            sort_order: index + 1,
                        }));

                        console.log('Update data:', updateData);

                        // Send update to backend
                        await router.post(
                            route('admin.menu-items.reorder'),
                            {
                                items: updateData,
                            },
                            {
                                preserveScroll: true,
                                onSuccess: () => {
                                    dragStatus.value =
                                        itemsToMove.length > 1
                                            ? `Menu "${movedItem.title}" dan ${childrenToMove.length} sub-menu berhasil dipindahkan!`
                                            : `Menu "${movedItem.title}" berhasil dipindahkan!`;
                                    setTimeout(() => {
                                        dragStatus.value = '';
                                    }, 2000);
                                    // Update local state
                                    flatMenuItems.value = newItems;
                                },
                                onError: () => {
                                    dragStatus.value = 'Gagal menyimpan urutan';
                                    setTimeout(() => {
                                        dragStatus.value = '';
                                    }, 3000);
                                    // Reload to revert changes
                                    router.reload({ only: ['menuItems'] });
                                },
                            },
                        );
                    } catch (error) {
                        console.error('Failed to reorder menu items:', error);
                        dragStatus.value = 'Terjadi kesalahan';
                        setTimeout(() => {
                            dragStatus.value = '';
                        }, 3000);
                        router.reload({ only: ['menuItems'] });
                    }
                },
            });
        }
    });
});

const toggleStatus = async (item: MenuItem) => {
    if (loading.value) return;

    loading.value = true;
    try {
        await router.post(
            route('admin.menu-items.toggle-status', item.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    router.reload();
                },
                onError: (errors) => {
                    console.error('Toggle status error:', errors);
                },
            },
        );
    } finally {
        loading.value = false;
    }
};

const deleteItem = async (item: MenuItem) => {
    if (loading.value) return;

    if (confirm(`Apakah Anda yakin ingin menghapus menu "${item.title}"?`)) {
        loading.value = true;
        try {
            await router.delete(route('admin.menu-items.destroy', item.id), {
                preserveScroll: true,
            });
        } finally {
            loading.value = false;
        }
    }
};

// Check if menu item can be indented (needs previous sibling)
const canIndent = (item: MenuItem): boolean => {
    const currentIndex = flatMenuItems.value.findIndex((m) => m.id === item.id);
    if (currentIndex <= 0) return false;

    const prevItem = flatMenuItems.value[currentIndex - 1];
    // Can indent if previous item is at same or higher level
    return prevItem && prevItem.level !== undefined && prevItem.level >= (item.level || 0);
};

// Indent menu (move to right, become sub-menu)
const indentMenu = async (item: MenuItem) => {
    if (loading.value || !canIndent(item)) return;

    loading.value = true;
    hierarchyStatus.value = `Mengindent menu "${item.title}"...`;

    try {
        await router.post(
            route('admin.menu-items.indent', item.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Update local state reactively
                    updateMenuHierarchy(item.id, 'indent');
                    hierarchyStatus.value = `Menu "${item.title}" berhasil dijadikan sub-menu!`;
                    setTimeout(() => {
                        hierarchyStatus.value = '';
                    }, 2000);
                },
                onError: () => {
                    hierarchyStatus.value = 'Gagal mengindent menu';
                    setTimeout(() => {
                        hierarchyStatus.value = '';
                    }, 3000);
                },
            },
        );
    } finally {
        loading.value = false;
    }
};

// Outdent menu (move to left, reduce level)
const outdentMenu = async (item: MenuItem) => {
    if (loading.value || (item.level || 0) === 0) return;

    loading.value = true;
    hierarchyStatus.value = `Mengoutdent menu "${item.title}"...`;

    try {
        await router.post(
            route('admin.menu-items.outdent', item.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Update local state reactively
                    updateMenuHierarchy(item.id, 'outdent');
                    hierarchyStatus.value = `Menu "${item.title}" berhasil dipindahkan ke level tinggi!`;
                    setTimeout(() => {
                        hierarchyStatus.value = '';
                    }, 2000);
                },
                onError: () => {
                    hierarchyStatus.value = 'Gagal mengoutdent menu';
                    setTimeout(() => {
                        hierarchyStatus.value = '';
                    }, 3000);
                },
            },
        );
    } finally {
        loading.value = false;
    }
};

// Update menu hierarchy in local state for immediate UI feedback
const updateMenuHierarchy = (menuId: number, action: 'indent' | 'outdent') => {
    const currentIndex = flatMenuItems.value.findIndex((m) => m.id === menuId);
    if (currentIndex === -1) return;

    const item = flatMenuItems.value[currentIndex];
    const newItems = [...flatMenuItems.value];

    if (action === 'indent') {
        // Find previous sibling to become parent
        const prevItem = currentIndex > 0 ? newItems[currentIndex - 1] : null;
        if (prevItem) {
            newItems[currentIndex] = {
                ...item,
                parent_id: prevItem.id,
                level: (prevItem.level || 0) + 1,
            };
        }
    } else if (action === 'outdent') {
        // Move up one level and position after parent
        const currentParent = newItems.find((m) => m.id === item.parent_id);
        if (currentParent) {
            // Remove item from current position
            const movedItem = newItems.splice(currentIndex, 1)[0];

            // Find parent's position in flat list
            const parentIndex = newItems.findIndex((m) => m.id === currentParent.id);

            // Insert right after parent
            const updatedItem = {
                ...movedItem,
                parent_id: currentParent.parent_id || undefined,
                level: Math.max(0, (movedItem.level || 0) - 1),
            };

            newItems.splice(parentIndex + 1, 0, updatedItem);
        }
    }

    // Update the reactive state
    flatMenuItems.value = newItems;
};

// Reset menu to default configuration
const resetMenu = async () => {
    if (loading.value) return;

    const confirmed = confirm(
        '⚠️ PERINGATAN!\n\n' +
            'Apakah Anda yakin ingin mereset semua menu ke pengaturan default?\n\n' +
            '• Semua perubahan urutan menu akan hilang\n' +
            '• Semua perubahan hierarki menu akan hilang\n' +
            '• Menu akan kembali ke struktur awal sistem\n' +
            '• Menu custom yang ditambahkan mungkin akan hilang\n\n' +
            'Tindakan ini TIDAK DAPAT DIBATALKAN!',
    );

    if (!confirmed) return;

    // Double confirmation for safety
    const doubleConfirmed = confirm(
        '🔴 KONFIRMASI TERAKHIR!\n\n' + 'Anda yakin 100% ingin mereset menu?\n' + 'Semua pengaturan menu saat ini akan hilang permanen!',
    );

    if (!doubleConfirmed) return;

    loading.value = true;
    hierarchyStatus.value = 'Sedang mereset menu ke pengaturan default...';

    try {
        await router.post(
            route('admin.menu-items.reset'),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    hierarchyStatus.value = 'Menu berhasil direset ke pengaturan default!';
                    setTimeout(() => {
                        hierarchyStatus.value = '';
                    }, 3000);
                    // Reload to get fresh data
                    router.reload({ only: ['menuItems'] });
                },
                onError: () => {
                    hierarchyStatus.value = 'Gagal mereset menu. Silakan coba lagi.';
                    setTimeout(() => {
                        hierarchyStatus.value = '';
                    }, 5000);
                },
            },
        );
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
/* Sortable.js styling */
.sortable-ghost {
    opacity: 0.5;
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border: 2px dashed #2196f3;
    border-radius: 8px;
    transform: scale(1.02);
}

.sortable-chosen {
    background: #f3e5f5;
    border-color: #9c27b0;
    transform: rotate(1deg) scale(1.01);
    box-shadow: 0 4px 12px rgba(156, 39, 176, 0.2);
}

.sortable-drag {
    transform: rotate(3deg) scale(1.05);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    background: white;
    border-radius: 8px;
}

/* Drop zone indicators */
.drop-zone-indicator {
    position: absolute;
    left: 0;
    right: 0;
    height: 2px;
    background: #2196f3;
    z-index: 10;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.drop-zone-indicator.active {
    opacity: 1;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
}

/* Hierarchy change indicators */
.hierarchy-change-hint {
    position: relative;
}

.hierarchy-change-hint::before {
    content: '';
    position: absolute;
    left: -10px;
    top: 50%;
    width: 6px;
    height: 6px;
    background: #ff9800;
    border-radius: 50%;
    transform: translateY(-50%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.hierarchy-change-hint.will-indent::before {
    opacity: 1;
    animation: blink 0.8s infinite;
}

@keyframes blink {
    0%,
    50% {
        opacity: 1;
    }
    51%,
    100% {
        opacity: 0.3;
    }
}

.drag-handle:hover {
    color: #2196f3;
}

.drag-handle:active {
    cursor: grabbing;
}
</style>
