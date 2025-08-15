<template>
    <div class="space-y-2">
        <label :for="id" class="text-sm font-medium">{{ label }}</label>
        
        <!-- Selected Icon Display & Search Input -->
        <div class="relative">
            <div class="flex items-center space-x-2 rounded-md border border-input bg-background px-3 py-2">
                <!-- Preview of selected icon -->
                <div class="flex h-5 w-5 items-center justify-center">
                    <component :is="getIconComponent(modelValue)" v-if="modelValue" class="h-4 w-4 text-gray-600" />
                    <div v-else class="h-4 w-4 rounded border border-dashed border-gray-300"></div>
                </div>
                
                <!-- Search input -->
                <Input
                    :id="id"
                    v-model="searchQuery"
                    :placeholder="placeholder"
                    class="border-0 p-0 focus-visible:ring-0"
                    @focus="showDropdown = true"
                    @blur="handleBlur"
                    @input="filterIcons"
                />
                
                <!-- Clear button -->
                <Button
                    v-if="modelValue"
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-auto p-1"
                    @click="clearSelection"
                >
                    <X class="h-3 w-3" />
                </Button>
            </div>
            
            <!-- Dropdown with icon options -->
            <div
                v-if="showDropdown && filteredIcons.length > 0"
                class="absolute top-full z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border border-input bg-background shadow-md"
            >
                <div class="grid grid-cols-6 gap-1 p-2">
                    <button
                        v-for="icon in filteredIcons"
                        :key="icon.name"
                        type="button"
                        :class="[
                            'flex flex-col items-center rounded p-2 text-xs transition-colors hover:bg-muted',
                            modelValue === icon.name ? 'bg-primary text-primary-foreground' : 'text-gray-600'
                        ]"
                        :title="icon.name"
                        @click="selectIcon(icon.name)"
                    >
                        <component :is="icon.component" class="h-4 w-4 mb-1" />
                        <span class="truncate text-[10px]">{{ icon.name }}</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Error message -->
        <div v-if="error" class="text-sm text-destructive">
            {{ error }}
        </div>
        
        <!-- Help text -->
        <p v-if="helpText" class="text-xs text-muted-foreground">
            {{ helpText }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
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
    X,
    // Additional icons
    Home,
    Activity,
    BarChart3,
    Briefcase,
    Camera,
    Car,
    Clock,
    Cloud,
    Code,
    CreditCard,
    Download,
    Edit,
    Eye,
    Filter,
    Folder,
    Gift,
    Hash,
    Image,
    Info,
    Key,
    Lock,
    Mail,
    MessageSquare,
    Minus,
    Monitor,
    Music,
    Navigation,
    Package,
    Palette,
    Phone,
    Play,
    Plus,
    Printer,
    Search,
    Send,
    Share,
    Star,
    Trash,
    Upload,
    User,
    Video,
    Wifi,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface IconOption {
    name: string;
    component: any;
    category?: string;
}

interface Props {
    id?: string;
    label?: string;
    modelValue?: string;
    placeholder?: string;
    error?: string;
    helpText?: string;
}

interface Emits {
    (e: 'update:modelValue', value: string): void;
}

const props = withDefaults(defineProps<Props>(), {
    id: 'icon-selector',
    label: 'Icon',
    placeholder: 'Cari atau pilih icon...',
    helpText: 'Ketik untuk mencari icon atau klik pada icon yang tersedia',
});

const emit = defineEmits<Emits>();

// Available icons
const availableIcons: IconOption[] = [
    // Navigation & Layout
    { name: 'LayoutGrid', component: LayoutGrid, category: 'Navigation' },
    { name: 'Menu', component: Menu, category: 'Navigation' },
    { name: 'Home', component: Home, category: 'Navigation' },
    { name: 'Navigation', component: Navigation, category: 'Navigation' },
    
    // Dashboard & Analytics
    { name: 'BarChart3', component: BarChart3, category: 'Analytics' },
    { name: 'TrendingUp', component: TrendingUp, category: 'Analytics' },
    { name: 'Activity', component: Activity, category: 'Analytics' },
    { name: 'Monitor', component: Monitor, category: 'Analytics' },
    
    // Security & Safety
    { name: 'Shield', component: Shield, category: 'Security' },
    { name: 'ShieldAlert', component: ShieldAlert, category: 'Security' },
    { name: 'Lock', component: Lock, category: 'Security' },
    { name: 'Key', component: Key, category: 'Security' },
    
    // Date & Time
    { name: 'Calendar', component: Calendar, category: 'Time' },
    { name: 'CalendarDays', component: CalendarDays, category: 'Time' },
    { name: 'Clock', component: Clock, category: 'Time' },
    
    // Data & Documents
    { name: 'Database', component: Database, category: 'Data' },
    { name: 'FileText', component: FileText, category: 'Data' },
    { name: 'ScrollText', component: ScrollText, category: 'Data' },
    { name: 'Folder', component: Folder, category: 'Data' },
    { name: 'Package', component: Package, category: 'Data' },
    
    // Finance & Business
    { name: 'DollarSign', component: DollarSign, category: 'Finance' },
    { name: 'CreditCard', component: CreditCard, category: 'Finance' },
    { name: 'Briefcase', component: Briefcase, category: 'Finance' },
    { name: 'ShoppingCart', component: ShoppingCart, category: 'Finance' },
    { name: 'Landmark', component: Landmark, category: 'Finance' },
    
    // Location & Maps
    { name: 'Map', component: Map, category: 'Location' },
    { name: 'MapPin', component: MapPin, category: 'Location' },
    { name: 'Building2', component: Building2, category: 'Location' },
    { name: 'Globe', component: Globe, category: 'Location' },
    { name: 'Car', component: Car, category: 'Location' },
    
    // System & Settings
    { name: 'Settings', component: Settings, category: 'System' },
    { name: 'Code', component: Code, category: 'System' },
    { name: 'Cloud', component: Cloud, category: 'System' },
    { name: 'Wifi', component: Wifi, category: 'System' },
    { name: 'Zap', component: Zap, category: 'System' },
    
    // Users & Social
    { name: 'Users', component: Users, category: 'Social' },
    { name: 'User', component: User, category: 'Social' },
    { name: 'Heart', component: Heart, category: 'Social' },
    { name: 'MessageSquare', component: MessageSquare, category: 'Social' },
    { name: 'Share', component: Share, category: 'Social' },
    
    // Politics & Government
    { name: 'Flag', component: Flag, category: 'Politics' },
    { name: 'Brain', component: Brain, category: 'Politics' },
    
    // Media & Content
    { name: 'Image', component: Image, category: 'Media' },
    { name: 'Camera', component: Camera, category: 'Media' },
    { name: 'Video', component: Video, category: 'Media' },
    { name: 'Music', component: Music, category: 'Media' },
    { name: 'Play', component: Play, category: 'Media' },
    
    // Actions
    { name: 'Edit', component: Edit, category: 'Actions' },
    { name: 'Eye', component: Eye, category: 'Actions' },
    { name: 'Search', component: Search, category: 'Actions' },
    { name: 'Filter', component: Filter, category: 'Actions' },
    { name: 'Download', component: Download, category: 'Actions' },
    { name: 'Upload', component: Upload, category: 'Actions' },
    { name: 'Send', component: Send, category: 'Actions' },
    { name: 'Plus', component: Plus, category: 'Actions' },
    { name: 'Minus', component: Minus, category: 'Actions' },
    { name: 'Trash', component: Trash, category: 'Actions' },
    
    // Misc
    { name: 'Tags', component: Tags, category: 'Misc' },
    { name: 'Star', component: Star, category: 'Misc' },
    { name: 'Gift', component: Gift, category: 'Misc' },
    { name: 'Hash', component: Hash, category: 'Misc' },
    { name: 'Info', component: Info, category: 'Misc' },
    { name: 'Mail', component: Mail, category: 'Misc' },
    { name: 'Phone', component: Phone, category: 'Misc' },
    { name: 'Printer', component: Printer, category: 'Misc' },
    { name: 'Palette', component: Palette, category: 'Misc' },
];

// Reactive state
const showDropdown = ref(false);
const searchQuery = ref(props.modelValue || '');

// Update search query when modelValue changes
watch(() => props.modelValue, (newValue) => {
    searchQuery.value = newValue || '';
});

// Filtered icons based on search
const filteredIcons = computed(() => {
    if (!searchQuery.value.trim()) {
        return availableIcons;
    }
    
    const query = searchQuery.value.toLowerCase();
    return availableIcons.filter(icon => 
        icon.name.toLowerCase().includes(query) ||
        (icon.category && icon.category.toLowerCase().includes(query))
    );
});

// Get icon component by name
const getIconComponent = (iconName?: string) => {
    if (!iconName) return null;
    const icon = availableIcons.find(i => i.name === iconName);
    return icon?.component || Tags; // Default to Tags if not found
};

// Methods
const selectIcon = (iconName: string) => {
    searchQuery.value = iconName;
    emit('update:modelValue', iconName);
    showDropdown.value = false;
};

const clearSelection = () => {
    searchQuery.value = '';
    emit('update:modelValue', '');
    showDropdown.value = false;
};

const filterIcons = () => {
    showDropdown.value = true;
    // If user types exact match, auto-select it
    const exactMatch = availableIcons.find(icon => 
        icon.name.toLowerCase() === searchQuery.value.toLowerCase()
    );
    if (exactMatch) {
        emit('update:modelValue', exactMatch.name);
    } else {
        emit('update:modelValue', searchQuery.value);
    }
};

const handleBlur = () => {
    // Delay hiding dropdown to allow click events
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
};
</script>