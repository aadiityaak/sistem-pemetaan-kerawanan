<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Menu Items</h1>
                    <p class="text-muted-foreground">Kelola menu navigasi dengan drag & drop seperti WordPress</p>
                </div>
                <Button as-child>
                    <Link :href="route('admin.menu-items.create')">
                        <Plus class="w-4 h-4 mr-2" />
                        Tambah Menu
                    </Link>
                </Button>
            </div>

            <!-- Menu Items Tree -->
            <Card>
                <CardHeader>
                    <CardTitle>Daftar Menu Items</CardTitle>
                    <CardDescription>
                        Seret menu untuk mengatur urutan. Sub menu menjorok seperti WordPress Admin.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-1">
                        <!-- Draggable Menu Tree -->
                        <div 
                            ref="sortableContainer" 
                            class="space-y-1"
                        >
                            <div 
                                v-for="(item, index) in flatMenuItems" 
                                :key="item.id" 
                                :data-id="item.id"
                                :data-index="index"
                                :class="[
                                    'group border-l-4 bg-background hover:bg-muted/30 transition-colors cursor-move',
                                    getMenuItemClasses(item),
                                    'sortable-item'
                                ]"
                            >
                                <div class="flex items-center justify-between p-3">
                                    <div class="flex items-center space-x-3 flex-1">
                                        <!-- Drag Handle -->
                                        <div class="drag-handle text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity cursor-grab active:cursor-grabbing">
                                            <GripVertical class="w-4 h-4" />
                                        </div>

                                        <!-- WordPress-style Hierarchy Lines -->
                                        <div class="flex items-center">
                                            <!-- Indentation spacer -->
                                            <div v-if="item.level > 0" class="flex items-center">
                                                <div 
                                                    v-for="i in item.level" 
                                                    :key="i" 
                                                    class="w-6 flex justify-center"
                                                >
                                                    <div class="w-px h-8 bg-gray-300"></div>
                                                </div>
                                                <div class="w-4 h-px bg-gray-300 relative">
                                                    <div class="absolute -top-4 right-0 w-px h-4 bg-gray-300"></div>
                                                </div>
                                            </div>
                                            
                                            <!-- Status Badge -->
                                            <span 
                                                :class="[
                                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ml-2',
                                                    item.is_active 
                                                        ? 'bg-green-100 text-green-800 border border-green-200' 
                                                        : 'bg-gray-100 text-gray-800 border border-gray-200'
                                                ]"
                                            >
                                                {{ item.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        
                                        <!-- Menu Info -->
                                        <div class="flex-1 min-w-0">
                                            <h3 :class="[
                                                'font-medium truncate',
                                                item.level === 0 ? 'text-base text-gray-900' : 'text-sm text-gray-700',
                                                item.admin_only ? 'text-orange-700' : ''
                                            ]">
                                                {{ item.title }}
                                                <span v-if="item.admin_only" class="text-xs text-orange-600 ml-1 font-normal">(Admin Only)</span>
                                            </h3>
                                            <div class="mt-1 space-y-1">
                                                <p class="text-xs text-gray-500 truncate">
                                                    <strong>Path:</strong> 
                                                    <code class="bg-gray-100 px-1 rounded text-xs">{{ item.path || 'No path set' }}</code>
                                                </p>
                                                <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                    <span v-if="item.icon">
                                                        <strong>Icon:</strong> {{ item.icon }}
                                                    </span>
                                                    <span>
                                                        <strong>Order:</strong> {{ item.sort_order }}
                                                    </span>
                                                    <span v-if="item.parent_id">
                                                        <strong>Parent ID:</strong> {{ item.parent_id }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            @click="toggleStatus(item)"
                                            :disabled="loading"
                                            class="text-xs"
                                        >
                                            {{ item.is_active ? 'OFF' : 'ON' }}
                                        </Button>
                                        <Button size="sm" variant="outline" as-child>
                                            <Link :href="route('admin.menu-items.edit', item.id)">
                                                <Edit class="w-3 h-3" />
                                            </Link>
                                        </Button>
                                        <Button 
                                            size="sm" 
                                            variant="destructive"
                                            @click="deleteItem(item)"
                                            :disabled="loading"
                                        >
                                            <Trash class="w-3 h-3" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Drag & Drop Instructions -->
            <Card class="bg-blue-50 border-blue-200">
                <CardContent class="pt-6">
                    <div class="flex items-start space-x-3">
                        <Info class="w-5 h-5 text-blue-600 mt-0.5" />
                        <div class="text-sm text-blue-800">
                            <h4 class="font-medium mb-2">Cara Menggunakan Interface:</h4>
                            <ul class="space-y-1 text-xs">
                                <li>• <strong>Drag & Drop:</strong> Seret handle <GripVertical class="w-3 h-3 inline mx-1" /> untuk mengatur urutan menu</li>
                                <li>• <strong>Hierarki WordPress:</strong> Level 0 (border biru), Level 1 (border hijau + indentasi)</li>
                                <li>• <strong>Toggle Status:</strong> Gunakan tombol ON/OFF untuk mengatur visibilitas menu</li>
                                <li>• <strong>Admin Only:</strong> Menu dengan label oranye hanya tampil untuk administrator</li>
                                <li>• <strong>Custom Path:</strong> Path redirect ditampilkan dalam format code</li>
                            </ul>
                            <div v-if="dragStatus" class="mt-2 p-2 bg-blue-100 rounded text-xs">
                                <strong>Status:</strong> {{ dragStatus }}
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
import { Edit, Plus, Trash, Info, GripVertical } from 'lucide-vue-next';
import { computed, ref, onMounted, nextTick } from 'vue';
import Sortable from 'sortablejs';

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
    level?: number; // For indentation
}

interface Props {
    menuItems: MenuItem[];
}

const props = defineProps<Props>();
const loading = ref(false);
const sortableContainer = ref<HTMLElement>();
const dragStatus = ref('');

const breadcrumbs = [
    { label: 'Admin', href: '#' },
    { label: 'Menu Items', href: route('admin.menu-items.index') },
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
                level
            });
            
            // Add children recursively
            if (item.children && item.children.length > 0) {
                result.push(...flattenMenu(item.children, level + 1));
            }
        }
        
        return result;
    };
    
    // Get main menu items (no parent) with their children
    const mainItems = props.menuItems.filter(item => !item.parent_id);
    flatMenuItems.value = flattenMenu(mainItems);
};

// Initialize flat menu items
updateFlatMenuItems();

// Get menu item classes based on level (WordPress style)
const getMenuItemClasses = (item: MenuItem) => {
    const level = item.level || 0;
    
    // Base classes
    let classes = 'relative ';
    
    // Level-specific styling (WordPress admin style)
    switch (level) {
        case 0:
            // Main menu items - blue border, larger text
            classes += 'border-l-blue-500 bg-blue-50/30 ';
            break;
        case 1:
            // First level sub-menu - green border, indented
            classes += 'border-l-green-500 bg-green-50/20 ml-6 ';
            break;
        case 2:
            // Second level sub-menu - yellow border, more indented
            classes += 'border-l-yellow-500 bg-yellow-50/20 ml-12 ';
            break;
        default:
            // Deeper levels - gray border, heavily indented
            classes += 'border-l-gray-400 bg-gray-50/20 ml-18 ';
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

// Initialize Sortable drag & drop
onMounted(() => {
    nextTick(() => {
        if (sortableContainer.value) {
            const sortable = Sortable.create(sortableContainer.value, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                
                onStart: (evt) => {
                    dragStatus.value = 'Sedang menyeret menu...';
                },
                
                onEnd: async (evt) => {
                    const { oldIndex, newIndex } = evt;
                    
                    if (oldIndex === newIndex) {
                        dragStatus.value = '';
                        return;
                    }
                    
                    dragStatus.value = 'Menyimpan urutan baru...';
                    
                    try {
                        // Reorder items in local state
                        const items = [...flatMenuItems.value];
                        const [movedItem] = items.splice(oldIndex!, 1);
                        items.splice(newIndex!, 0, movedItem);
                        
                        // Update sort orders based on new positions
                        const updateData = items.map((item, index) => ({
                            id: item.id,
                            sort_order: index + 1,
                            parent_id: item.parent_id
                        }));
                        
                        // Send update to backend
                        await router.post(route('admin.menu-items.reorder'), {
                            items: updateData
                        }, {
                            preserveScroll: true,
                            onSuccess: () => {
                                dragStatus.value = 'Urutan berhasil disimpan!';
                                setTimeout(() => {
                                    dragStatus.value = '';
                                }, 2000);
                                // Update local state
                                flatMenuItems.value = items;
                            },
                            onError: () => {
                                dragStatus.value = 'Gagal menyimpan urutan';
                                setTimeout(() => {
                                    dragStatus.value = '';
                                }, 3000);
                            }
                        });
                    } catch (error) {
                        console.error('Failed to reorder menu items:', error);
                        dragStatus.value = 'Terjadi kesalahan';
                        setTimeout(() => {
                            dragStatus.value = '';
                        }, 3000);
                    }
                }
            });
        }
    });
});

const toggleStatus = async (item: MenuItem) => {
    if (loading.value) return;
    
    loading.value = true;
    try {
        await router.post(route('admin.menu-items.toggle-status', item.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            }
        });
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
</script>

<style scoped>
/* Sortable.js styling */
.sortable-ghost {
    opacity: 0.4;
    background: #e3f2fd;
    border: 2px dashed #2196f3;
}

.sortable-chosen {
    background: #f3e5f5;
    border-color: #9c27b0;
    transform: rotate(2deg);
}

.sortable-drag {
    transform: rotate(5deg);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.drag-handle:hover {
    color: #2196f3;
}

.drag-handle:active {
    cursor: grabbing;
}
</style>