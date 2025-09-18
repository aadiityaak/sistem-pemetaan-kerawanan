<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { indonesiaProvinces, type ProvincePathData } from '@/data/indonesiaProvinces';

// Import route helper
declare global {
    function route(name: string, params?: any): string;
}

interface MonitoringData {
    id: number;
    provinsi_id: number;
    kabupaten_kota_id: number;
    kecamatan_id: number;
    category_id: number;
    sub_category_id: number;
    latitude: number;
    longitude: number;
    title: string;
    description: string;
    jumlah_terdampak: number | null;
    severity_level: string;
    status: string;
    incident_date: string;
    created_at: string;
    updated_at: string;
    provinsi: { id: number; nama: string };
    kabupaten_kota: { id: number; nama: string; provinsi_id: number };
    kecamatan: { id: number; nama: string; kabupaten_kota_id: number };
    category: { id: number; name: string; slug: string; color: string; icon?: string; image_url?: string };
    sub_category: { id: number; name: string; slug: string; icon?: string; image_url?: string };
}

interface Category {
    id: number;
    name: string;
    slug: string;
    color: string;
    icon?: string;
    image_url?: string;
}

interface SubCategory {
    id: number;
    category_id: number;
    name: string;
    slug: string;
    icon?: string;
    image_url?: string;
}

interface UserProvinsi {
    id: number;
    nama: string;
    latitude?: number | null;
    longitude?: number | null;
}

interface Statistics {
    totalData: number;
    totalProvinsi: number;
    totalKabupatenKota: number;
    totalKecamatan: number;
    totalTerdampak: number;
    totalSubCategories: number;
    dataBySubCategory: Record<string, { name: string; icon: string; image_url?: string; count: number }>;
    allSubCategoriesData: Record<string, { name: string; icon: string; image_url?: string; count: number }>;
    dataByProvinsi: Record<string, number>;
    dataBySeverity: Record<string, number>;
    dataByStatus: Record<string, number>;
}

interface KabupatenKota {
    id: number;
    nama: string;
    provinsi_id: number;
}

const props = defineProps<{
    monitoringData: MonitoringData[];
    selectedCategory?: Category | null;
    selectedSubCategory?: SubCategory | null;
    startDate?: string | null;
    endDate?: string | null;
    categories: Category[];
    subCategories: SubCategory[];
    userProvinsi?: UserProvinsi | null;
    statistics: Statistics;
    recentActivities: MonitoringData[];
    provinsiList?: UserProvinsi[];
    selectedProvinsi?: UserProvinsi | null;
    kabupatenKotaList?: KabupatenKota[];
    selectedKabupatenKota?: KabupatenKota | null;
}>();

// Get current user and check edit permissions
const page = usePage();
const currentUser = computed(() => page.props.auth.user as any);
const canEdit = computed(() => ['super_admin', 'admin'].includes(currentUser.value.role));

// Dynamic breadcrumbs based on selected category and subcategory
const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const base = [{ title: 'Dashboard', href: '/dashboard' }];
    if (props.selectedCategory) {
        base.push({
            title: props.selectedCategory.name,
            href: `/dashboard?category=${props.selectedCategory.slug}`,
        });
        if (props.selectedSubCategory) {
            base.push({
                title: props.selectedSubCategory.name,
                href: `/dashboard?category=${props.selectedCategory.slug}&subcategory=${props.selectedSubCategory.slug}`,
            });
        }
    }
    return base;
});

// Map related refs
let map: any;
const mapContainer = ref();
const mapMarkers = ref<any[]>([]);
const isLeafletMap = ref(true); // Toggle between Leaflet and SVG map
const selectedProvince = ref<MonitoringData | null>(null); // For SVG map province selection

// Category color mapping for theming
const categoryThemes: Record<string, { color: string; icon: string; bgColor: string }> = {
    keamanan: { color: '#DC2626', icon: '🛡️', bgColor: 'bg-red-100 dark:bg-red-900' },
    ideologi: { color: '#7C3AED', icon: '🏛️', bgColor: 'bg-purple-100 dark:bg-purple-900' },
    politik: { color: '#059669', icon: '🗳️', bgColor: 'bg-green-100 dark:bg-green-900' },
    ekonomi: { color: '#D97706', icon: '💰', bgColor: 'bg-orange-100 dark:bg-orange-900' },
    'sosial-budaya': { color: '#0EA5E9', icon: '🤝', bgColor: 'bg-blue-100 dark:bg-blue-900' },
};

// Get current theme based on selected category
const currentTheme = computed(() => {
    if (props.selectedCategory) {
        return categoryThemes[props.selectedCategory.slug] || categoryThemes['keamanan'];
    }
    return { color: '#6B7280', icon: '📊', bgColor: 'bg-gray-100 dark:bg-gray-900' };
});

// Check if current category is kategori-indas
const isKategoriIndas = computed(() => {
    return props.selectedCategory?.slug === 'kategori-indas';
});

// Icon mapping untuk setiap severity level
const severityIcons: Record<string, { color: string; icon: string }> = {
    low: { color: '#10B981', icon: '🟢' },
    medium: { color: '#F59E0B', icon: '🟡' },
    high: { color: '#EF4444', icon: '🟠' },
    critical: { color: '#DC2626', icon: '🔴' },
};

// Computed untuk menghitung top data
const topDataBySubCategory = computed(() => {
    return Object.entries(props.statistics.dataBySubCategory)
        .sort(([, a], [, b]) => (b.count as number) - (a.count as number))
        .slice(0, 5);
});

// Computed untuk menampilkan semua sub kategori dalam category (untuk analytics card)
const allSubCategoriesDisplay = computed(() => {
    return Object.entries(props.statistics.allSubCategoriesData)
        .sort(([, a], [, b]) => (b.count as number) - (a.count as number))
        .slice(0, 8); // Show up to 8 subcategories
});

const topDataByProvinsi = computed(() => {
    return Object.entries(props.statistics.dataByProvinsi)
        .sort(([, a], [, b]) => (b as number) - (a as number))
        .slice(0, 5);
});

// Create mapping from provinsi name to ID
const provinsiNameToId = computed(() => {
    const mapping: Record<string, number> = {};
    props.monitoringData.forEach((data) => {
        if (data.provinsi && data.provinsi.nama && data.provinsi.id) {
            mapping[data.provinsi.nama] = data.provinsi.id;
        }
    });
    return mapping;
});

// Computed untuk top 3 isu menonjol (berdasarkan jumlah terdampak)
const topIssues = computed(() => {
    return props.monitoringData
        .filter((data) => data.jumlah_terdampak && data.jumlah_terdampak > 0)
        .sort((a, b) => (b.jumlah_terdampak || 0) - (a.jumlah_terdampak || 0))
        .slice(0, 3);
});

// Helper functions
const getSeverityLabel = (severity: string): string => {
    const labels: Record<string, string> = {
        low: 'Rendah',
        medium: 'Sedang',
        high: 'Tinggi',
        critical: 'Kritis',
    };
    return labels[severity] || severity;
};

// Helper functions for table display
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getLevelBadgeClass = (level: string): string => {
    const classes: Record<string, string> = {
        low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return classes[level] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getStatusBadgeClass = (status: string): string => {
    const classes: Record<string, string> = {
        active: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        monitoring: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        archived: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    };
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getStatusLabel = (status: string): string => {
    const labels: Record<string, string> = {
        active: 'Aktif',
        resolved: 'Selesai',
        monitoring: 'Dalam Proses',
        archived: 'Arsip',
    };
    return labels[status] || status;
};

// Helper function to build URL with all filters
const buildFilterUrl = (params: Record<string, string | null>) => {
    const urlParams = new URLSearchParams();

    Object.entries(params).forEach(([key, value]) => {
        if (value) {
            urlParams.append(key, value);
        }
    });

    const queryString = urlParams.toString();
    return `/dashboard${queryString ? `?${queryString}` : ''}`;
};

// Helper function to build monitoring data URL with current filters
const buildMonitoringDataUrl = () => {
    const urlParams = new URLSearchParams();

    if (props.selectedCategory) {
        urlParams.append('category', props.selectedCategory.slug);
    }

    if (props.selectedSubCategory) {
        urlParams.append('subcategory', props.selectedSubCategory.slug);
    }

    if (props.startDate) {
        urlParams.append('start_date', props.startDate);
    }

    if (props.endDate) {
        urlParams.append('end_date', props.endDate);
    }

    if (props.selectedProvinsi) {
        urlParams.append('provinsi_id', props.selectedProvinsi.id.toString());
    }

    if (props.selectedKabupatenKota) {
        urlParams.append('kabupaten_kota_id', props.selectedKabupatenKota.id.toString());
    }

    const queryString = urlParams.toString();
    return `/monitoring-data${queryString ? `?${queryString}` : ''}`;
};

// Local filter change functions (don't trigger immediate search)
const selectLocalCategory = (categorySlug: string | null) => {
    localFilters.value.category = categorySlug;
    // Clear subcategory when category changes
    if (!categorySlug) {
        localFilters.value.subCategory = null;
    }
};

const selectLocalSubCategory = (subCategorySlug: string | null) => {
    localFilters.value.subCategory = subCategorySlug;
};

const selectLocalProvinsi = (provinsiId: number | null) => {
    localFilters.value.provinsi = provinsiId;
    // Clear kabupaten/kota when province changes
    localFilters.value.kabupatenKota = null;
};

const selectLocalKabupatenKota = (kabupatenKotaId: number | null) => {
    localFilters.value.kabupatenKota = kabupatenKotaId;
};

const selectLocalStartDate = (startDate: string | null) => {
    localFilters.value.startDate = startDate;
};

const selectLocalEndDate = (endDate: string | null) => {
    localFilters.value.endDate = endDate;
};

// Function to apply all filters at once
const applyFilters = () => {
    // Reset pagination to first page when applying filters
    currentPage.value = 1;
    
    // Update search query for filtering
    searchQuery.value = localFilters.value.search;
    
    router.get(
        buildFilterUrl({
            category: localFilters.value.category,
            subcategory: localFilters.value.subCategory,
            start_date: localFilters.value.startDate,
            end_date: localFilters.value.endDate,
            provinsi_id: localFilters.value.provinsi?.toString() || null,
            kabupaten_kota_id: localFilters.value.kabupatenKota?.toString() || null,
        }),
    );
};

// Function to reset all filters
const resetFilters = () => {
    // Reset pagination to first page when resetting filters
    currentPage.value = 1;
    
    localFilters.value = {
        category: null,
        subCategory: null,
        startDate: null,
        endDate: null,
        provinsi: null,
        kabupatenKota: null,
        search: ''
    };
    searchQuery.value = '';
    
    // Navigate to base dashboard URL (clear all filters)
    router.get('/dashboard');
};

// Original filter functions (kept for backward compatibility)
const selectCategory = (categorySlug: string | null) => {
    router.get(
        buildFilterUrl({
            category: categorySlug,
            subcategory: categorySlug ? props.selectedSubCategory?.slug || null : null,
            start_date: props.startDate || null,
            end_date: props.endDate || null,
            provinsi_id: props.selectedProvinsi?.id?.toString() || null,
            kabupaten_kota_id: props.selectedKabupatenKota?.id?.toString() || null,
        }),
    );
};

// Function to change subcategory filter
const selectSubCategory = (subCategorySlug: string | null) => {
    router.get(
        buildFilterUrl({
            category: props.selectedCategory?.slug || null,
            subcategory: subCategorySlug,
            start_date: props.startDate || null,
            end_date: props.endDate || null,
            provinsi_id: props.selectedProvinsi?.id?.toString() || null,
            kabupaten_kota_id: props.selectedKabupatenKota?.id?.toString() || null,
        }),
    );
};

// Function to change province filter
const selectProvinsi = (provinsiId: number | null) => {
    router.get(
        buildFilterUrl({
            category: props.selectedCategory?.slug || null,
            subcategory: props.selectedSubCategory?.slug || null,
            start_date: props.startDate || null,
            end_date: props.endDate || null,
            provinsi_id: provinsiId?.toString() || null,
            kabupaten_kota_id: null, // Reset kabupaten/kota when province changes
        }),
    );
};

// Function to change kabupaten/kota filter
const selectKabupatenKota = (kabupatenKotaId: number | null) => {
    router.get(
        buildFilterUrl({
            category: props.selectedCategory?.slug || null,
            subcategory: props.selectedSubCategory?.slug || null,
            start_date: props.startDate || null,
            end_date: props.endDate || null,
            provinsi_id: props.selectedProvinsi?.id?.toString() || null,
            kabupaten_kota_id: kabupatenKotaId?.toString() || null,
        }),
    );
};

// Function to change start date filter
const selectStartDate = (startDate: string | null) => {
    router.get(
        buildFilterUrl({
            category: props.selectedCategory?.slug || null,
            subcategory: props.selectedSubCategory?.slug || null,
            start_date: startDate,
            end_date: props.endDate || null,
            provinsi_id: props.selectedProvinsi?.id?.toString() || null,
            kabupaten_kota_id: props.selectedKabupatenKota?.id?.toString() || null,
        }),
    );
};

// Function to change end date filter
const selectEndDate = (endDate: string | null) => {
    router.get(
        buildFilterUrl({
            category: props.selectedCategory?.slug || null,
            subcategory: props.selectedSubCategory?.slug || null,
            start_date: props.startDate || null,
            end_date: endDate,
            provinsi_id: props.selectedProvinsi?.id?.toString() || null,
            kabupaten_kota_id: props.selectedKabupatenKota?.id?.toString() || null,
        }),
    );
};

// Function to navigate to monitoring data with provinsi filter
const goToMonitoringDataByProvinsi = (provinsiName: string) => {
    const provinsiId = provinsiNameToId.value[provinsiName];
    if (provinsiId) {
        router.get(route('monitoring-data.index'), {
            provinsi_id: provinsiId,
        });
    }
};

// Function to build create URL with pre-filled category and subcategory
const buildCreateUrl = () => {
    const params = new URLSearchParams();

    if (props.selectedCategory) {
        params.append('category', props.selectedCategory.slug);
    }

    if (props.selectedSubCategory) {
        params.append('subcategory', props.selectedSubCategory.slug);
    }

    const queryString = params.toString();
    return `/monitoring-data/create${queryString ? `?${queryString}` : ''}`;
};

// Pagination navigation methods
const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        validCurrentPage.value = page;
    }
};

const goToNextPage = () => {
    if (validCurrentPage.value < totalPages.value) {
        validCurrentPage.value++;
    }
};

const goToPreviousPage = () => {
    if (validCurrentPage.value > 1) {
        validCurrentPage.value--;
    }
};

// Custom dropdown state
const categoryDropdownOpen = ref(false);
const subCategoryDropdownOpen = ref(false);
const provinsiDropdownOpen = ref(false);
const kabupatenKotaDropdownOpen = ref(false);
const showFilters = ref(false);

// Initialize filter visibility based on screen size
const initializeFilterVisibility = () => {
    if (typeof window !== 'undefined') {
        const isLargeScreen = window.innerWidth >= 1024; // lg breakpoint (desktop)
        showFilters.value = isLargeScreen; // Show on desktop, hide on mobile
    }
};

// Watch for window resize to adjust filter visibility
const handleResize = () => {
    if (typeof window !== 'undefined') {
        const isLargeScreen = window.innerWidth >= 1024;
        // Auto-show filters when switching to desktop, auto-hide when switching to mobile
        showFilters.value = isLargeScreen;
    }
};

// Search functionality
const searchQuery = ref('');

// Local filter state (temporary values before applying)
const localFilters = ref({
    category: props.selectedCategory?.slug || null,
    subCategory: props.selectedSubCategory?.slug || null,
    startDate: props.startDate || null,
    endDate: props.endDate || null,
    provinsi: props.selectedProvinsi?.id || null,
    kabupatenKota: props.selectedKabupatenKota?.id || null,
    search: ''
});

// Pagination functionality
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Computed properties for local filter display
const selectedLocalCategory = computed(() => {
    return props.categories.find(cat => cat.slug === localFilters.value.category) || null;
});

const selectedLocalSubCategory = computed(() => {
    return props.subCategories.find(sub => sub.slug === localFilters.value.subCategory) || null;
});

const selectedLocalProvinsi = computed(() => {
    return props.provinsiList?.find(prov => prov.id === localFilters.value.provinsi) || null;
});

const selectedLocalKabupatenKota = computed(() => {
    return props.kabupatenKotaList?.find(kab => kab.id === localFilters.value.kabupatenKota) || null;
});

// Computed property for filtered kabupaten/kota based on selected province (for local filters)
const filteredLocalKabupatenKotaList = computed(() => {
    if (!localFilters.value.provinsi || !props.kabupatenKotaList) {
        return props.kabupatenKotaList || [];
    }
    return props.kabupatenKotaList.filter(kabupatenKota => 
        kabupatenKota.provinsi_id === localFilters.value.provinsi
    );
});

// Computed property for filtered kabupaten/kota based on selected province (original)
const filteredKabupatenKotaList = computed(() => {
    if (!props.selectedProvinsi || !props.kabupatenKotaList) {
        return props.kabupatenKotaList || [];
    }
    return props.kabupatenKotaList.filter(kabupatenKota => 
        kabupatenKota.provinsi_id === props.selectedProvinsi!.id
    );
});

// Computed property for filtered monitoring data
const filteredMonitoringData = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.monitoringData;
    }
    
    return props.monitoringData.filter(data =>
        data.title.toLowerCase().includes(searchQuery.value.toLowerCase().trim())
    );
});

// Computed properties for pagination
const totalPages = computed(() => Math.ceil(filteredMonitoringData.value.length / itemsPerPage.value));

const paginatedMonitoringData = computed(() => {
    const start = (validCurrentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredMonitoringData.value.slice(start, end);
});

// Computed property to validate current page
const validCurrentPage = computed({
    get() {
        return Math.min(Math.max(1, currentPage.value), totalPages.value || 1);
    },
    set(value) {
        currentPage.value = value;
    }
});

// Function to update map markers
const updateMapMarkers = async () => {
    if (!map || !isLeafletMap.value) return;
    
    // Clear existing markers
    mapMarkers.value.forEach(marker => map.removeLayer(marker));
    mapMarkers.value = [];
    
    // Dynamic import Leaflet
    const L = await import('leaflet');
    
    // Add markers for filtered data
    filteredMonitoringData.value.forEach((data: MonitoringData) => {
        const severityConfig = severityIcons[data.severity_level] || severityIcons['medium'];

        // Create custom HTML marker with theme color if category specific
        const markerColor = props.selectedCategory ? currentTheme.value.color : severityConfig.color;

        // Determine marker icon: sub category image > sub category icon > category theme icon > severity icon
        let markerContent = '';
        let hasCustomImage = false;

        if (data.sub_category.image_url) {
            // Use actual custom image in marker
            markerContent = `<img src="${data.sub_category.image_url}" style="width: 16px; height: 16px; object-fit: contain; border-radius: 2px;">`;
            hasCustomImage = true;
        } else if (data.sub_category.icon) {
            markerContent = `<span style="font-size: 12px;">${data.sub_category.icon}</span>`;
        } else if (props.selectedCategory) {
            markerContent = `<span style="font-size: 12px;">${currentTheme.value.icon}</span>`;
        } else {
            markerContent = `<span style="font-size: 12px;">${severityConfig.icon}</span>`;
        }

        const customIcon = L.divIcon({
            html: `<div style="
                background-color: ${hasCustomImage ? '#ffffff' : markerColor}; 
                border: 2px solid ${markerColor}; 
                border-radius: 50%; 
                width: 24px; 
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
                overflow: hidden;
            ">${markerContent}</div>`,
            className: 'custom-marker',
            iconSize: [24, 24],
            iconAnchor: [12, 12],
        });

        const marker = L.marker([data.latitude, data.longitude], {
            icon: customIcon,
        }).addTo(map);

        // Add popup with sub category icon or image if available
        const popupIcon = data.sub_category.image_url
            ? `<img src="${data.sub_category.image_url}" alt="Subcategory" style="width: 16px; height: 16px; object-fit: contain; border-radius: 2px;">`
            : `<span style="font-size: 16px;">${data.sub_category.icon || '📊'}</span>`;
        marker.bindPopup(`
            <div class="p-3">
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex items-center justify-center" style="min-width: 20px;">${popupIcon}</div>
                    <div class="font-semibold text-sm" style="color: ${markerColor}">${data.title}</div>
                </div>
                <div class="text-xs text-gray-600 mt-1">
                    <strong>Kategori:</strong> ${data.category.name} - ${data.sub_category.name}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                    <strong>Lokasi:</strong> ${data.provinsi?.nama || 'N/A'}, ${data.kabupaten_kota?.nama || 'N/A'}
                </div>
                ${
                    data.jumlah_terdampak
                        ? `
                <div class="text-xs text-gray-600 mt-1">
                    <strong>Terdampak:</strong> ${data.jumlah_terdampak.toLocaleString()} orang
                </div>
                `
                        : ''
                }
                <div class="text-xs mt-2">
                    <span class="px-2 py-1 bg-gray-100 rounded text-gray-700 text-xs">
                        Level: ${getSeverityLabel(data.severity_level)}
                    </span>
                </div>
                <div class="mt-3 pt-2 border-t border-gray-200">
                    <a href="/monitoring-data/${data.id}" 
                       class="!text-white inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors duration-200"
                       style="text-decoration: none;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Lihat Detail
                    </a>
                </div>
            </div>
        `);
        
        mapMarkers.value.push(marker);
    });
};

// Initialize map and responsive filter behavior
onMounted(async () => {
    if (typeof window !== 'undefined') {
        // Initialize filter visibility based on screen size
        initializeFilterVisibility();
        
        // Add resize event listener
        window.addEventListener('resize', handleResize);
        
        // Initialize map only if in Leaflet mode
        if (isLeafletMap.value) {
            // Dynamic import Leaflet
            const L = await import('leaflet');

            // Initialize map
            if (mapContainer.value) {
                // Use user's province coordinates if available, otherwise use Indonesia center
                let mapCenter: [number, number] = [-2.5489, 118.0149]; // Default: Indonesia center
                let zoomLevel = 5; // Default zoom for Indonesia

                if (props.userProvinsi && props.userProvinsi.latitude && props.userProvinsi.longitude) {
                    mapCenter = [props.userProvinsi.latitude, props.userProvinsi.longitude];
                    zoomLevel = 8; // Closer zoom for province view
                }

                map = L.map(mapContainer.value).setView(mapCenter, zoomLevel);

                // Add tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                }).addTo(map);

                // Add initial markers
                updateMapMarkers();
            }
        }
    }
});

// Cleanup on unmount
onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('resize', handleResize);
    }

    // Cleanup map
    if (map) {
        map.remove();
        map = null;
        mapMarkers.value = [];
    }
});

// Province name mapping for SVG map
const provinceNameMapping: Record<string, string[]> = {
    'DAERAH ISTIMEWA YOGYAKARTA': ['D.I. YOGYAKARTA', 'YOGYAKARTA', 'DI YOGYAKARTA', 'JOGJA', 'D.I YOGYAKARTA', 'DAERAH ISTIMEWA YOGYAKARTA'],
    'BANGKA BELITUNG': ['KEP. BANGKA BELITUNG', 'KEPULAUAN BANGKA BELITUNG'],
    'SUMATRA BARAT': ['SUMATERA BARAT', 'SUMBAR', 'SUMATRA BARAT'],
    'SUMATRA UTARA': ['SUMATERA UTARA', 'SUMUT', 'SUMATRA UTARA'],
    'SUMATRA SELATAN': ['SUMATERA SELATAN', 'SUMSEL', 'SUMATRA SELATAN'],
    'NUSA TENGGARA BARAT': ['NTB', 'NUSA TENGGARA BARAT'],
    'NUSA TENGGARA TIMUR': ['NTT', 'NUSA TENGGARA TIMUR'],
    'KALIMANTAN BARAT': ['KALBAR', 'KALIMANTAN BARAT'],
    'KALIMANTAN TENGAH': ['KALTENG', 'KALIMANTAN TENGAH'],
    'KALIMANTAN SELATAN': ['KALSEL', 'KALIMANTAN SELATAN'],
    'KALIMANTAN TIMUR': ['KALTIM', 'KALIMANTAN TIMUR'],
    'KALIMANTAN UTARA': ['KALUT', 'KALIMANTAN UTARA'],
    'SULAWESI UTARA': ['SULUT', 'SULAWESI UTARA'],
    'SULAWESI TENGAH': ['SULTENG', 'SULAWESI TENGAH'],
    'SULAWESI SELATAN': ['SULSEL', 'SULAWESI SELATAN'],
    'SULAWESI TENGGARA': ['SULTRA', 'SULAWESI TENGGARA'],
    'SULAWESI BARAT': ['SULBAR', 'SULAWESI BARAT'],
    'GORONTALO': ['GORONTALO'],
    'MALUKU': ['MALUKU'],
    'MALUKU UTARA': ['MALUT', 'MALUKU UTARA'],
    'JAWA BARAT': ['JABAR', 'JAWA BARAT'],
    'JAWA TENGAH': ['JATENG', 'JAWA TENGAH'],
    'JAWA TIMUR': ['JATIM', 'JAWA TIMUR'],
    'BANTEN': ['BANTEN'],
    'DKI JAKARTA': ['JAKARTA', 'DKI JAKARTA'],
    'BALI': ['BALI'],
    'ACEH': ['ACEH', 'NANGGROE ACEH DARUSSALAM'],
    'RIAU': ['RIAU'],
    'KEPULAUAN RIAU': ['KEP. RIAU', 'KEPULAUAN RIAU'],
    'JAMBI': ['JAMBI'],
    'BENGKULU': ['BENGKULU'],
    'LAMPUNG': ['LAMPUNG'],
    'PAPUA': ['PAPUA'],
    'PAPUA BARAT': ['PAPUA BARAT'],
    'PAPUA SELATAN': ['PAPUA SELATAN'],
    'PAPUA TENGAH': ['PAPUA TENGAH'],
    'PAPUA PEGUNUNGAN': ['PAPUA PEGUNUNGAN'],
    'PAPUA BARAT DAYA': ['PAPUA BARAT DAYA']
};

// Get province color based on data count
const getProvinceColor = (provinceName: string): string => {
    const count = getProvinceDataCount(provinceName);

    if (count === 0) {
        return '#e5e7eb'; // Gray for no data
    } else if (count <= 5) {
        return '#10b981'; // Green for low count (1-5)
    } else if (count <= 10) {
        return '#f59e0b'; // Yellow for medium count (6-10)
    } else {
        return '#ef4444'; // Red for high count (11+)
    }
};

// Get data count for a province
const getProvinceDataCount = (provinceName: string): number => {
    // First try exact match
    let count = filteredMonitoringData.value.filter(data =>
        data.provinsi.nama.toUpperCase() === provinceName.toUpperCase()
    ).length;

    // If no exact match, try using mapping
    if (count === 0) {
        const svgProvinceName = provinceName.toUpperCase();
        const apiProvinceAlias = Object.entries(provinceNameMapping).find(([_, aliases]) =>
            aliases.some(alias => alias.toUpperCase() === svgProvinceName)
        );

        if (apiProvinceAlias) {
            const [mappedName] = apiProvinceAlias;
            count = filteredMonitoringData.value.filter(data => {
                const apiName = data.provinsi.nama.toUpperCase();
                return provinceNameMapping[mappedName]?.some(alias =>
                    apiName.includes(alias.toUpperCase()) || alias.toUpperCase().includes(apiName)
                );
            }).length;
        }
    }

    // If still no match, try partial matching
    if (count === 0) {
        count = filteredMonitoringData.value.filter(data => {
            const apiName = data.provinsi.nama.toUpperCase();
            const svgName = provinceName.toUpperCase();
            return apiName.includes(svgName) || svgName.includes(apiName) ||
                   apiName.replace(/\s+/g, '').includes(svgName.replace(/\s+/g, '')) ||
                   svgName.replace(/\s+/g, '').includes(apiName.replace(/\s+/g, ''));
        }).length;
    }

    return count;
};

// Show province detail for SVG map
const showProvinceDetail = (provinceName: string) => {
    // Find the first data item for this province
    let provinceData = filteredMonitoringData.value.find(data =>
        data.provinsi.nama.toUpperCase() === provinceName.toUpperCase()
    );

    // If not found, try using mapping
    if (!provinceData) {
        const svgProvinceName = provinceName.toUpperCase();
        const apiProvinceAlias = Object.entries(provinceNameMapping).find(([_, aliases]) =>
            aliases.some(alias => alias.toUpperCase() === svgProvinceName)
        );

        if (apiProvinceAlias) {
            const [mappedName] = apiProvinceAlias;
            provinceData = filteredMonitoringData.value.find(data => {
                const apiName = data.provinsi.nama.toUpperCase();
                return provinceNameMapping[mappedName]?.some(alias =>
                    apiName.includes(alias.toUpperCase()) || alias.toUpperCase().includes(apiName)
                );
            });
        }
    }

    // If still not found, try partial matching
    if (!provinceData) {
        provinceData = filteredMonitoringData.value.find(data => {
            const apiName = data.provinsi.nama.toUpperCase();
            const svgName = provinceName.toUpperCase();
            return apiName.includes(svgName) || svgName.includes(apiName) ||
                   apiName.replace(/\s+/g, '').includes(svgName.replace(/\s+/g, '')) ||
                   svgName.replace(/\s+/g, '').includes(apiName.replace(/\s+/g, ''));
        });
    }

    if (provinceData) {
        selectedProvince.value = provinceData;
    }
};

// Watch for map type changes to reinitialize Leaflet map
watch(isLeafletMap, async (newValue) => {
    if (newValue && typeof window !== 'undefined') {
        // When switching to Leaflet map, wait for DOM update then initialize
        await nextTick();
        if (mapContainer.value) {
            // Clear existing map if any
            if (map) {
                map.remove();
                map = null;
            }

            // Dynamic import Leaflet
            const L = await import('leaflet');

            // Initialize map with proper center and zoom
            let mapCenter: [number, number] = [-2.5489, 118.0149]; // Default: Indonesia center
            let zoomLevel = 5; // Default zoom for Indonesia

            if (props.userProvinsi && props.userProvinsi.latitude && props.userProvinsi.longitude) {
                mapCenter = [props.userProvinsi.latitude, props.userProvinsi.longitude];
                zoomLevel = 8; // Closer zoom for province view
            }

            map = L.map(mapContainer.value).setView(mapCenter, zoomLevel);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
            }).addTo(map);

            // Add markers
            updateMapMarkers();
        }
    }
});

// Watch for changes in filtered data and update map markers
watch(filteredMonitoringData, () => {
    updateMapMarkers();
    // Reset to first page when data changes due to filtering
    currentPage.value = 1;
}, { deep: true });

// Watch for totalPages changes to ensure currentPage is valid
watch(totalPages, (newTotalPages) => {
    if (newTotalPages > 0 && currentPage.value > newTotalPages) {
        currentPage.value = 1;
    }
});

// Watch for search query changes to reset pagination
watch(searchQuery, () => {
    currentPage.value = 1;
});
</script>

<template>
    <Head
        :title="
            selectedSubCategory
                ? `Dashboard ${selectedCategory?.name} - ${selectedSubCategory.name}`
                : selectedCategory
                  ? `Dashboard ${selectedCategory.name}`
                  : 'Dashboard'
        "
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Icon & Title Section -->
                    <div class="flex items-center min-w-0 flex-1">
                        <div
                            class="mr-3 flex h-10 w-10 items-center justify-center rounded-lg sm:mr-4 sm:h-12 sm:w-12"
                            :class="
                                selectedCategory?.image_url
                                    ? 'bg-gray-50 dark:bg-gray-800'
                                    : selectedCategory
                                      ? currentTheme.bgColor
                                      : 'bg-gray-100 dark:bg-gray-900'
                            "
                        >
                            <img
                                v-if="selectedCategory?.image_url"
                                :src="selectedCategory.image_url"
                                alt="Category icon"
                                class="h-8 w-8 rounded object-contain sm:h-10 sm:w-10"
                            />
                            <span v-else class="text-lg sm:text-2xl">{{ selectedCategory ? selectedCategory.icon || currentTheme.icon : '📊' }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="flex flex-wrap items-center gap-1 text-lg font-bold text-gray-900 dark:text-white sm:gap-2 sm:text-2xl">
                                <span class="whitespace-nowrap">Dashboard</span>
                                <span v-if="selectedCategory" class="truncate">{{ selectedCategory.name }}</span>
                                <template v-if="selectedSubCategory">
                                    <span class="hidden sm:inline">/</span>
                                    <div class="flex items-center gap-1 sm:gap-2">
                                        <img
                                            v-if="selectedSubCategory.image_url"
                                            :src="selectedSubCategory.image_url"
                                            alt="Subcategory icon"
                                            class="h-4 w-4 rounded object-contain sm:h-6 sm:w-6"
                                        />
                                        <span class="truncate">{{ selectedSubCategory.name }}</span>
                                    </div>
                                </template>
                                <span v-if="!selectedCategory" class="whitespace-nowrap">Monitoring</span>
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 sm:text-base">
                                {{
                                    selectedSubCategory
                                        ? `Monitoring khusus subcategory ${selectedSubCategory.name}`
                                        : selectedCategory
                                          ? `Monitoring khusus kategori ${selectedCategory.name}`
                                          : 'Monitoring data komprehensif semua kategori'
                                }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 sm:flex-shrink-0">
                        <!-- Filter Toggle Button -->
                        <button
                            @click="showFilters = !showFilters"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800 sm:px-4"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span class="whitespace-nowrap">{{ showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}</span>
                        </button>
                        
                        <!-- Add Data Button -->
                        <Link
                            v-if="canEdit"
                            :href="buildCreateUrl()"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800 sm:px-4"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="whitespace-nowrap">Tambah Data</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div v-if="showFilters" class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="flex items-center gap-2 text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                                Filter & Pencarian Data
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Atur filter yang diinginkan, lalu terapkan untuk melihat hasil pencarian
                            </p>
                        </div>
                        <div v-if="selectedCategory || selectedSubCategory" class="rounded-full bg-white px-3 py-1 text-sm font-medium text-blue-700 shadow-sm dark:bg-gray-800 dark:text-blue-300">
                            {{ selectedSubCategory ? selectedSubCategory.name : selectedCategory ? selectedCategory.name : '' }}
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div :class="isKategoriIndas ? 'grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4' : 'grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5'">
                        <!-- Search Filter -->
                        <div class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Pencarian Judul
                            </label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <svg class="h-4 w-4 text-gray-400 group-focus-within:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="localFilters.search"
                                    type="text"
                                    placeholder="Ketik untuk mencari..."
                                    @keydown.enter="applyFilters"
                                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-11 text-sm text-gray-900 placeholder-gray-500 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400 dark:focus:border-blue-400"
                                />
                                <div v-if="localFilters.search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <kbd class="inline-flex items-center rounded border border-gray-200 px-1 text-xs font-sans text-gray-500 dark:border-gray-600 dark:text-gray-400">Enter</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Kategori Data
                            </label>
                            <div class="relative">
                                <button
                                    @click="categoryDropdownOpen = !categoryDropdownOpen"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:border-blue-400"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-5 w-5 items-center justify-center">
                                            <img
                                                v-if="selectedLocalCategory?.image_url"
                                                :src="selectedLocalCategory.image_url"
                                                alt="Category"
                                                class="h-5 w-5 rounded object-contain"
                                            />
                                            <span v-else-if="selectedLocalCategory?.icon" class="text-lg">{{ selectedLocalCategory.icon }}</span>
                                            <div v-else class="h-2 w-2 rounded-full bg-green-500"></div>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ selectedLocalCategory?.name || 'Pilih Kategori' }}</span>
                                    </div>
                                    <svg
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': categoryDropdownOpen }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    v-if="categoryDropdownOpen"
                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <button
                                        @click="
                                            selectLocalCategory(null);
                                            categoryDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <span class="text-sm">🟢</span>
                                        <span>Semua Kategori</span>
                                    </button>
                                    <button
                                        v-for="category in categories"
                                        :key="category.id"
                                        @click="
                                            selectLocalCategory(category.slug);
                                            categoryDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <div class="flex h-4 w-4 items-center justify-center">
                                            <img
                                                v-if="category.image_url"
                                                :src="category.image_url"
                                                alt="Category"
                                                class="h-4 w-4 rounded object-contain"
                                            />
                                            <span v-else-if="category.icon" class="text-sm">{{ category.icon }}</span>
                                        </div>
                                        <span>{{ category.name }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- SubCategory Filter -->
                        <div v-if="selectedLocalCategory && subCategories.length > 0" class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                Sub Kategori
                            </label>
                            <div class="relative">
                                <button
                                    @click="subCategoryDropdownOpen = !subCategoryDropdownOpen"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:border-blue-400"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-5 w-5 items-center justify-center">
                                            <img
                                                v-if="selectedLocalSubCategory?.image_url"
                                                :src="selectedLocalSubCategory.image_url"
                                                alt="Subcategory"
                                                class="h-5 w-5 rounded object-contain"
                                            />
                                            <span v-else-if="selectedLocalSubCategory?.icon" class="text-lg">{{ selectedLocalSubCategory.icon }}</span>
                                            <div v-else class="h-2 w-2 rounded-full bg-green-500"></div>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ selectedLocalSubCategory?.name || 'Pilih Sub Kategori' }}</span>
                                    </div>
                                    <svg
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': subCategoryDropdownOpen }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    v-if="subCategoryDropdownOpen"
                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <button
                                        @click="
                                            selectLocalSubCategory(null);
                                            subCategoryDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <span class="text-sm">🟢</span>
                                        <span>Semua Sub Kategori</span>
                                    </button>
                                    <button
                                        v-for="subCategory in subCategories"
                                        :key="subCategory.id"
                                        @click="
                                            selectLocalSubCategory(subCategory.slug);
                                            subCategoryDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <div class="flex h-4 w-4 items-center justify-center">
                                            <img
                                                v-if="subCategory.image_url"
                                                :src="subCategory.image_url"
                                                alt="Subcategory"
                                                class="h-4 w-4 rounded object-contain"
                                            />
                                            <span v-else-if="subCategory.icon" class="text-sm">{{ subCategory.icon }}</span>
                                        </div>
                                        <span>{{ subCategory.name }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Start Date Filter - Hidden for kategori-indas -->
                        <div v-if="!isKategoriIndas" class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Tanggal Mulai
                            </label>
                            <input
                                type="date"
                                @change="selectLocalStartDate(($event.target as HTMLInputElement).value || null)"
                                :value="localFilters.startDate || ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400"
                            />
                        </div>

                        <!-- End Date Filter - Hidden for kategori-indas -->
                        <div v-if="!isKategoriIndas" class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Tanggal Akhir
                            </label>
                            <input
                                type="date"
                                @change="selectLocalEndDate(($event.target as HTMLInputElement).value || null)"
                                :value="localFilters.endDate || ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-colors focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-400"
                            />
                        </div>

                        <!-- Province Filter - Only for kategori-indas -->
                        <div v-if="isKategoriIndas" class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Provinsi
                            </label>
                            <div class="relative">
                                <button
                                    @click="provinsiDropdownOpen = !provinsiDropdownOpen"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:border-blue-400"
                                >
                                    <span class="font-medium text-gray-900 dark:text-white">{{ selectedLocalProvinsi?.nama || 'Pilih Provinsi' }}</span>
                                    <svg
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': provinsiDropdownOpen }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    v-if="provinsiDropdownOpen"
                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <button
                                        @click="
                                            selectLocalProvinsi(null);
                                            provinsiDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <span>Semua Provinsi</span>
                                    </button>
                                    <button
                                        v-for="provinsi in (provinsiList || [])"
                                        :key="provinsi.id"
                                        @click="
                                            selectLocalProvinsi(provinsi.id);
                                            provinsiDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <span>{{ provinsi.nama }}</span>
                                    </button>
                                    <div
                                        v-if="!provinsiList || provinsiList.length === 0"
                                        class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Data provinsi tidak tersedia
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kabupaten/Kota Filter - Only for kategori-indas when province is selected -->
                        <div v-if="isKategoriIndas && selectedLocalProvinsi && filteredLocalKabupatenKotaList.length > 0" class="group">
                            <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Kabupaten/Kota
                            </label>
                            <div class="relative">
                                <button
                                    @click="kabupatenKotaDropdownOpen = !kabupatenKotaDropdownOpen"
                                    type="button"
                                    class="flex w-full items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-left text-sm transition-colors hover:bg-gray-50 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:border-blue-400"
                                >
                                    <span class="font-medium text-gray-900 dark:text-white">{{ selectedLocalKabupatenKota?.nama || 'Pilih Kabupaten/Kota' }}</span>
                                    <svg
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': kabupatenKotaDropdownOpen }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <div
                                    v-if="kabupatenKotaDropdownOpen"
                                    class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-300 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <button
                                        @click="
                                            selectLocalKabupatenKota(null);
                                            kabupatenKotaDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <span>Semua Kabupaten/Kota</span>
                                    </button>
                                    <button
                                        v-for="kabupatenKota in filteredLocalKabupatenKotaList"
                                        :key="kabupatenKota.id"
                                        @click="
                                            selectLocalKabupatenKota(kabupatenKota.id);
                                            kabupatenKotaDropdownOpen = false;
                                        "
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-gray-900 hover:bg-gray-100 dark:text-white dark:hover:bg-gray-600"
                                    >
                                        <span>{{ kabupatenKota.nama }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Filter Action Buttons -->
                    <div class="mt-5 border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                    <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Siap untuk menerapkan filter?
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        Ubah filter di atas, lalu klik "Terapkan Filter" untuk melihat hasil pencarian
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button
                                    @click="resetFilters"
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-all duration-200 hover:bg-gray-50 hover:border-gray-400 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset Semua
                                </button>
                                <button
                                    @click="applyFilters"
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-200 hover:from-blue-700 hover:to-indigo-700 hover:shadow-xl focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none dark:focus:ring-offset-gray-800"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Terapkan Filter
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-3">
                <!-- Total Data -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg"
                            :class="selectedCategory ? currentTheme.bgColor : 'bg-blue-100 dark:bg-blue-900'"
                        >
                            <svg
                                class="h-5 w-5"
                                :class="selectedCategory ? 'text-white' : 'text-blue-600 dark:text-blue-400'"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalData }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Total Data</p>
                    </div>
                </div>

                <!-- Total Terdampak -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900">
                            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalTerdampak.toLocaleString() }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Komentar</p>
                    </div>
                </div>

                <!-- Total Sub Kategori / Kategori -->
                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900">
                            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                />
                            </svg>
                        </div>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalSubCategories }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ selectedCategory ? 'Sub Kategori' : 'Kategori' }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Map -->
                <div class="lg:col-span-2">
                    <div class="mb-7 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                🗺️
                                {{
                                    selectedSubCategory
                                        ? `${selectedSubCategory.name}`
                                        : selectedCategory
                                          ? `${selectedCategory.name}`
                                          : 'Monitoring Data'
                                }}
                            </h3>
                            <div class="flex items-center gap-4">
                                <!-- Map Toggle Button -->
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="isLeafletMap = !isLeafletMap"
                                        class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                        :title="isLeafletMap ? 'Beralih ke Peta SVG' : 'Beralih ke Peta Leaflet'"
                                    >
                                        <svg v-if="isLeafletMap" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ isLeafletMap ? 'SVG' : 'Leaflet' }}
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <span
                                        class="h-3 w-3 rounded-full"
                                        :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                    ></span>
                                    <span>{{
                                        selectedSubCategory ? selectedSubCategory.name : selectedCategory ? selectedCategory.name : 'Semua Data'
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Leaflet Map -->
                        <div v-if="isLeafletMap" ref="mapContainer" class="relative z-0 h-96 rounded-lg border border-gray-200 dark:border-gray-700"></div>

                        <!-- SVG Map Indonesia -->
                        <div v-else class="relative h-96 rounded-lg border border-gray-200 bg-gradient-to-b from-blue-50 to-green-50 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                            <!-- Indonesia SVG Map -->
                            <svg
                                viewBox="0 0 792.54596 316.66394"
                                class="absolute inset-0 h-full w-full"
                                xmlns="http://www.w3.org/2000/svg"
                                preserveAspectRatio="xMinYMin"
                            >
                                <path
                                    v-for="provinceCode in indonesiaProvinces"
                                    :key="provinceCode.id"
                                    :d="provinceCode.path"
                                    :fill="getProvinceColor(provinceCode.name)"
                                    stroke="#ffffff"
                                    stroke-width="1"
                                    stroke-linejoin="round"
                                    class="cursor-pointer transition-all hover:stroke-gray-800"
                                    @click="showProvinceDetail(provinceCode.name)"
                                >
                                    <title>{{ provinceCode.name }}</title>
                                </path>
                            </svg>

                            <!-- Selected Province Detail -->
                            <div
                                v-if="selectedProvince"
                                class="absolute right-4 top-4 z-30 w-72 rounded-lg bg-white p-4 shadow-lg dark:bg-gray-800"
                            >
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ selectedProvince.provinsi.nama }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Kategori: <span class="font-medium text-blue-600">{{ selectedProvince.category.name }}</span>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Sub Kategori: <span class="font-medium text-green-600">{{ selectedProvince.sub_category.name }}</span>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Jumlah Data: <span class="font-medium text-red-600">{{ getProvinceDataCount(selectedProvince.provinsi.nama) }}</span>
                                </p>
                                <button
                                    @click="selectedProvince = null"
                                    class="mt-2 text-xs text-blue-600 hover:text-blue-800"
                                >
                                    Tutup
                                </button>
                            </div>

                            <!-- Legend -->
                            <div class="absolute bottom-4 left-4 z-30 rounded-lg bg-white/90 p-4 shadow-lg dark:bg-gray-800/90">
                                <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Legend Tingkat Data</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="flex items-center">
                                        <div class="mr-2 h-3 w-3 rounded-full bg-green-500"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Rendah (1-5)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="mr-2 h-3 w-3 rounded-full bg-yellow-500"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Sedang (6-10)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="mr-2 h-3 w-3 rounded-full bg-red-500"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Tinggi (11+)</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="mr-2 h-3 w-3 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                        <span class="text-gray-700 dark:text-gray-300">Tidak Ada Data</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Statistics Cards -->
                    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <!-- Total Provinsi -->
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex flex-col items-center text-center">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalProvinsi }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Provinsi</p>
                            </div>
                        </div>

                        <!-- Total Kabupaten/Kota -->
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex flex-col items-center text-center">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900">
                                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        />
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKabupatenKota }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Kab/Kota</p>
                            </div>
                        </div>

                        <!-- Total Kecamatan -->
                        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="flex flex-col items-center text-center">
                                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                        />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ statistics.totalKecamatan }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Kecamatan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Monitoring Data Table -->
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        📋
                                        {{
                                            selectedSubCategory
                                                ? `Data ${selectedSubCategory.name}`
                                                : selectedCategory
                                                  ? `Data ${selectedCategory.name}`
                                                  : 'Daftar Data Monitoring'
                                        }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Data monitoring yang ditampilkan pada peta di atas</p>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ filteredMonitoringData.length }} data</div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                        >
                                            Judul & Lokasi
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                        >
                                            Kategori
                                        </th>
                                        <th
                                            v-if="!isKategoriIndas"
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                        >
                                            Tanggal Kejadian
                                        </th>
                                        <th
                                            v-if="!isKategoriIndas"
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                        >
                                            Level
                                        </th>
                                        <th
                                            v-if="!isKategoriIndas"
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                        >
                                            Status
                                        </th>
                                        <th
                                            v-if="!isKategoriIndas"
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                        >
                                            Komentar
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-if="filteredMonitoringData.length === 0">
                                        <td :colspan="isKategoriIndas ? '3' : '7'" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <svg
                                                    class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>
                                                <p class="font-medium">Tidak ada data monitoring</p>
                                                <p class="text-sm">
                                                    {{
                                                        searchQuery.trim()
                                                            ? `Tidak ada data yang ditemukan untuk pencarian "${searchQuery}"`
                                                            : selectedCategory
                                                            ? `Tidak ada data untuk kategori ${selectedCategory.name}`
                                                            : 'Tidak ada data yang sesuai dengan filter yang dipilih'
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr
                                        v-else
                                        v-for="data in paginatedMonitoringData"
                                        :key="data.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ data.title }}</div>
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    {{ data.kecamatan?.nama || 'N/A' }}, {{ data.kabupaten_kota?.nama || 'N/A' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="space-y-1 text-sm">
                                                <div>
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ data.category.name }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ data.sub_category.name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td v-if="!isKategoriIndas" class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ formatDate(data.incident_date) }}
                                        </td>
                                        <td v-if="!isKategoriIndas" class="px-6 py-4">
                                            <span
                                                :class="getLevelBadgeClass(data.severity_level)"
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            >
                                                {{ getSeverityLabel(data.severity_level) }}
                                            </span>
                                        </td>
                                        <td v-if="!isKategoriIndas" class="px-6 py-4">
                                            <span
                                                :class="getStatusBadgeClass(data.status)"
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            >
                                                {{ getStatusLabel(data.status) }}
                                            </span>
                                        </td>
                                        <td v-if="!isKategoriIndas" class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                            {{ data.jumlah_terdampak?.toLocaleString() || '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="`/monitoring-data/${data.id}`"
                                                    class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        />
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        />
                                                    </svg>
                                                    Detail
                                                </Link>
                                                <Link
                                                    v-if="canEdit"
                                                    :href="`/monitoring-data/${data.id}/edit`"
                                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                                >
                                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                        />
                                                    </svg>
                                                    Edit
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div
                            v-if="totalPages > 1"
                            class="border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-700"
                        >
                            <div class="flex items-center justify-between">
                                <!-- Results Info -->
                                <div class="text-sm text-gray-700 dark:text-gray-300">
                                    Menampilkan {{ (validCurrentPage - 1) * itemsPerPage + 1 }}-{{ Math.min(validCurrentPage * itemsPerPage, filteredMonitoringData.length) }} dari {{ filteredMonitoringData.length }} data
                                </div>

                                <!-- Pagination Navigation -->
                                <div class="flex items-center space-x-2">
                                    <!-- Previous Button -->
                                    <button
                                        @click="goToPreviousPage"
                                        :disabled="validCurrentPage <= 1"
                                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="validCurrentPage <= 1 
                                            ? 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' 
                                            : 'bg-white text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700'"
                                    >
                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        Sebelumnya
                                    </button>

                                    <!-- Page Numbers -->
                                    <div class="flex items-center space-x-1">
                                        <!-- First 3 pages -->
                                        <template v-for="page in Math.min(3, totalPages)" :key="`start-${page}`">
                                            <button
                                                @click="goToPage(page)"
                                                :class="validCurrentPage === page 
                                                    ? 'bg-blue-600 text-white shadow-sm' 
                                                    : 'bg-white text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700'"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-sm font-medium transition-colors"
                                            >
                                                {{ page }}
                                            </button>
                                        </template>

                                        <!-- Left ellipsis -->
                                        <span v-if="validCurrentPage > 6" class="px-2 text-gray-500 dark:text-gray-400">...</span>

                                        <!-- Pages around current page -->
                                        <template v-for="page in [validCurrentPage - 1, validCurrentPage, validCurrentPage + 1]" :key="`middle-${page}`">
                                            <button
                                                v-if="page > 3 && page <= totalPages - 3 && page > 0"
                                                @click="goToPage(page)"
                                                :class="validCurrentPage === page 
                                                    ? 'bg-blue-600 text-white shadow-sm' 
                                                    : 'bg-white text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700'"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-sm font-medium transition-colors"
                                            >
                                                {{ page }}
                                            </button>
                                        </template>

                                        <!-- Right ellipsis -->
                                        <span v-if="validCurrentPage < totalPages - 5" class="px-2 text-gray-500 dark:text-gray-400">...</span>

                                        <!-- Last 3 pages -->
                                        <template v-for="page in [totalPages - 2, totalPages - 1, totalPages]" :key="`end-${page}`">
                                            <button
                                                v-if="page > 3 && page > 0 && totalPages > 6"
                                                @click="goToPage(page)"
                                                :class="validCurrentPage === page 
                                                    ? 'bg-blue-600 text-white shadow-sm' 
                                                    : 'bg-white text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700'"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-sm font-medium transition-colors"
                                            >
                                                {{ page }}
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Next Button -->
                                    <button
                                        @click="goToNextPage"
                                        :disabled="validCurrentPage >= totalPages"
                                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="validCurrentPage >= totalPages 
                                            ? 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500' 
                                            : 'bg-white text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700'"
                                    >
                                        Selanjutnya
                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Analytics -->
                <div class="space-y-6">
                    <!-- Top Sub Categories / Categories -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                            🎯 {{ selectedCategory ? `Sub Kategori ${selectedCategory.name}` : 'Top Kategori' }}
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="[subCategoryId, subCategoryData] in allSubCategoriesDisplay"
                                :key="subCategoryId"
                                class="flex items-center justify-between"
                                :class="
                                    selectedSubCategory && subCategoryData.name === selectedSubCategory.name
                                        ? 'mb-2 rounded-lg bg-gray-200 p-2 dark:bg-gray-700'
                                        : ''
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <div class="flex h-5 w-5 items-center justify-center">
                                        <img
                                            v-if="subCategoryData.image_url"
                                            :src="subCategoryData.image_url"
                                            alt="Subcategory"
                                            class="h-4 w-4 rounded object-contain"
                                        />
                                        <span v-else class="text-lg">{{ subCategoryData.icon }}</span>
                                    </div>
                                    <span
                                        class="text-sm font-medium"
                                        :class="
                                            selectedSubCategory && subCategoryData.name === selectedSubCategory.name
                                                ? 'font-semibold text-gray-900 dark:text-white'
                                                : 'text-gray-900 dark:text-white'
                                        "
                                    >
                                        {{ subCategoryData.name }}
                                    </span>
                                </div>
                                <span
                                    class="rounded px-2 py-1 text-xs font-medium text-white"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                >
                                    {{ subCategoryData.count }} data
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Top 3 Isu Menonjol -->
                    <div
                        v-if="topIssues.length > 0"
                        class="w-full max-w-full overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">🔥 Top 3 Isu Menonjol</h3>
                        <div class="space-y-4">
                            <div
                                v-for="(issue, index) in topIssues"
                                :key="issue.id"
                                class="flex cursor-pointer items-start gap-3 overflow-hidden rounded-lg bg-gray-50 p-3 transition-colors hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700"
                                @click="$inertia.visit(`/monitoring-data/${issue.id}`)"
                            >
                                <div
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                                    :class="index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : 'bg-orange-600'"
                                >
                                    {{ index + 1 }}
                                </div>
                                <div class="min-w-0 flex-1 overflow-hidden">
                                    <!-- Title with proper wrapping and line limit -->
                                    <div class="w-full overflow-hidden">
                                        <p
                                            class="overflow-hidden text-sm leading-tight font-medium break-all text-gray-900 dark:text-white"
                                            :title="issue.title"
                                            style="
                                                display: -webkit-box;
                                                -webkit-line-clamp: 2;
                                                -webkit-box-orient: vertical;
                                                overflow: hidden;
                                                word-break: break-all;
                                                line-height: 1.4;
                                                max-height: 2.8em;
                                                width: 100%;
                                                max-width: 100%;
                                            "
                                        >
                                            {{ issue.title }}
                                        </p>
                                    </div>

                                    <!-- Content area with proper spacing -->
                                    <div class="mt-2 flex items-start justify-between gap-3 overflow-hidden">
                                        <div class="min-w-0 flex-1 overflow-hidden">
                                            <!-- Category info -->
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-4 w-4 flex-shrink-0 items-center justify-center">
                                                    <img
                                                        v-if="issue.sub_category.image_url"
                                                        :src="issue.sub_category.image_url"
                                                        alt="Subcategory"
                                                        class="h-3 w-3 rounded object-contain"
                                                    />
                                                    <span v-else-if="issue.sub_category.icon" class="text-xs">{{ issue.sub_category.icon }}</span>
                                                </div>
                                                <span class="truncate text-xs text-gray-500 dark:text-gray-400">{{ issue.sub_category.name }}</span>
                                            </div>
                                            <!-- Location info -->
                                            <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">
                                                {{ issue.provinsi?.nama || 'N/A' }}, {{ issue.kabupaten_kota?.nama || 'N/A' }}
                                            </p>
                                        </div>

                                        <!-- Impact count - always visible on right -->
                                        <div class="flex-shrink-0 text-right">
                                            <div class="flex items-center gap-1">
                                                <svg class="h-3 w-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                                    />
                                                </svg>
                                                <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                                    {{ issue.jumlah_terdampak?.toLocaleString() }}
                                                </span>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">komentar</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Severity Level Distribution - Hidden for kategori-indas -->
                    <div v-if="!isKategoriIndas" class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">📊 Tingkat Resiko</h3>
                        <div class="space-y-3">
                            <div v-for="(config, severity) in severityIcons" :key="severity" class="flex items-center gap-3">
                                <span class="text-lg">{{ config.icon }}</span>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ getSeverityLabel(severity) }}</span>
                                    <div class="mt-1 h-2 w-full rounded-full bg-gray-200">
                                        <div
                                            class="h-2 rounded-full"
                                            :style="{
                                                backgroundColor: config.color,
                                                width: `${((statistics.dataBySeverity[severity] || 0) / Math.max(...Object.values(statistics.dataBySeverity))) * 100}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ statistics.dataBySeverity[severity] || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Provinces -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">🏛️ Top Provinsi</h3>
                        <div class="space-y-2">
                            <div
                                v-for="[provinsi, count] in topDataByProvinsi"
                                :key="provinsi"
                                @click="goToMonitoringDataByProvinsi(provinsi)"
                                class="flex cursor-pointer items-center justify-between rounded p-2 text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <span class="text-gray-900 dark:text-white">{{ provinsi }}</span>
                                <span
                                    class="rounded px-2 py-1 text-xs text-white"
                                    :style="{ backgroundColor: selectedCategory ? currentTheme.color : '#6B7280' }"
                                >
                                    {{ count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
