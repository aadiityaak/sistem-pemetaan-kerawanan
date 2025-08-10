<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

interface KamtibmasEvent {
    id: number;
    title: string;
    start: string;
    end: string;
    description?: string;
    category: string;
    color: string;
    isMultiDay: boolean;
    duration: number;
}

interface Statistics {
    totalEvents: number;
    upcomingEvents: number;
    activeEvents: number;
}

const props = defineProps<{
    events: KamtibmasEvent[];
    statistics: Statistics;
    currentDate: string;
    holidays: any[];
    categories: Record<string, string>;
    eventFilter?: string;
    categoryFilter?: string;
    agendaType?: string;
}>();


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: getPageTitle(),
        href: getCurrentHref(),
    },
];

function getPageTitle() {
    if (props.categoryFilter === 'Agenda Internal Korp Brimob POLRI') {
        return 'Agenda Internal Korp Brimob POLRI';
    }
    return props.eventFilter === 'agenda' ? 'Agenda' : 'Kalender Kamtibmas';
}

function getCurrentHref() {
    if (props.categoryFilter === 'Agenda Internal Korp Brimob POLRI') {
        return '/agenda-internal-korp-brimob';
    }
    return props.eventFilter === 'agenda' ? '/event?event=agenda' : '/event?event=kamtibmas';
}

function getPageDescription() {
    if (props.categoryFilter === 'Agenda Internal Korp Brimob POLRI') {
        return 'Kelola agenda dan kegiatan internal Korps Brimob POLRI';
    }
    return props.eventFilter === 'agenda' ? 'Kelola agenda dan event organisasi' : 'Jadwal kegiatan Keamanan dan Ketertiban Masyarakat';
}

function getDefaultCategory() {
    if (props.categoryFilter === 'Agenda Internal Korp Brimob POLRI') {
        return 'Agenda Internal Korp Brimob POLRI';
    }
    return props.eventFilter === 'agenda' ? 'Agenda Nasional' : 'Kamtibmas';
}

// Calendar state
const currentDate = ref(new Date(props.currentDate));
const selectedDate = ref<Date | null>(null);
const showEventModal = ref(false);
const showDayModal = ref(false);
const editingEvent = ref<KamtibmasEvent | null>(null);
const selectedDayEvents = ref<KamtibmasEvent[]>([]);
const selectedDayHolidays = ref<any[]>([]);

// Event form state
const eventForm = ref({
    title: '',
    start_date: '',
    end_date: '',
    description: '',
    category: getDefaultCategory(),
    color: '#3B82F6',
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);

// Filter categories based on current view
const availableCategories = computed(() => {
    const allCategories = Object.values(props.categories);
    
    if (props.eventFilter === 'agenda') {
        // For agenda view, show all categories except Kamtibmas
        return allCategories.filter(category => category !== 'Kamtibmas');
    } else if (props.eventFilter === 'kamtibmas') {
        // For kamtibmas view, only show Kamtibmas
        return ['Kamtibmas'];
    } else {
        // For all events view, show all categories
        return allCategories;
    }
});

// Calendar navigation
const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Filter state - initialize from current date
const selectedMonth = ref(currentDate.value.getMonth() + 1);
const selectedYear = ref(currentDate.value.getFullYear());

// Agenda type filter (only for agenda view)
const selectedAgendaType = ref(props.eventFilter === 'agenda' ? (props.agendaType || 'all') : '');

// Agenda type options for filtering
const agendaTypeOptions = computed(() => [
    { value: 'all', label: 'Semua Agenda' },
    { value: 'nasional', label: 'Agenda Nasional' },
    { value: 'internasional', label: 'Agenda Internasional' },
]);

// Navigation state to prevent infinite loops
let isNavigating = false;

// Sync filter state when currentDate changes (from props)
watch(() => props.currentDate, (newCurrentDate) => {
    try {
        isNavigating = true;
        const date = new Date(newCurrentDate);
        
        // Validate date
        if (isNaN(date.getTime())) {
            console.warn('Invalid date received from props:', newCurrentDate);
            return;
        }
        
        currentDate.value = date;
        selectedMonth.value = date.getMonth() + 1;
        selectedYear.value = date.getFullYear();
        
        // Reset flag after state updates
        nextTick(() => {
            isNavigating = false;
        });
    } catch (error) {
        console.error('Error in currentDate watcher:', error);
        isNavigating = false;
    }
}, { immediate: true });

// Generate year options (current year ± 5 years)
const yearOptions = computed(() => {
    const currentYearValue = new Date().getFullYear();
    const years = [];
    for (let i = currentYearValue - 5; i <= currentYearValue + 5; i++) {
        years.push(i);
    }
    return years;
});

// Generate month options
const monthOptions = computed(() => {
    return monthNames.map((name, index) => ({
        value: index + 1,
        label: name
    }));
});

const currentMonth = computed(() => monthNames[currentDate.value.getMonth()]);
const currentYear = computed(() => currentDate.value.getFullYear());

// Calendar grid generation
const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    
    // Adjust to start from Monday (1) instead of Sunday (0)
    const startDayOfWeek = firstDay.getDay() === 0 ? 6 : firstDay.getDay() - 1;
    startDate.setDate(startDate.getDate() - startDayOfWeek);
    
    const days = [];
    const today = new Date();
    
    for (let i = 0; i < 42; i++) { // 6 weeks * 7 days
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dayEvents = props.events.filter(event => {
            const eventStart = new Date(event.start + 'T00:00:00');
            const eventEnd = new Date(event.end + 'T23:59:59');
            return date >= eventStart && date <= eventEnd;
        });

        const dayHolidays = props.holidays.filter(holiday => {
            const holidayDate = new Date(holiday.start);
            return date.toDateString() === holidayDate.toDateString();
        });

        // Combine events and holidays
        const allDayItems = [...dayEvents, ...dayHolidays];
        
        days.push({
            date: new Date(date),
            day: date.getDate(),
            isCurrentMonth: date.getMonth() === month,
            isToday: date.toDateString() === today.toDateString(),
            events: dayEvents,
            holidays: dayHolidays,
            allItems: allDayItems,
        });
    }
    
    return days;
});

// Navigation functions
const previousMonth = () => {
    selectedMonth.value = selectedMonth.value === 1 ? 12 : selectedMonth.value - 1;
    if (selectedMonth.value === 12) {
        selectedYear.value = selectedYear.value - 1;
    }
};

const nextMonth = () => {
    selectedMonth.value = selectedMonth.value === 12 ? 1 : selectedMonth.value + 1;
    if (selectedMonth.value === 1) {
        selectedYear.value = selectedYear.value + 1;
    }
};

const goToToday = () => {
    const today = new Date();
    selectedMonth.value = today.getMonth() + 1;
    selectedYear.value = today.getFullYear();
};

const navigateToMonth = () => {
    const dateParam = currentDate.value.toISOString().slice(0, 7); // YYYY-MM
    const params: any = { date: dateParam };
    
    if (props.eventFilter) {
        params.event = props.eventFilter;
    }
    
    if (props.categoryFilter) {
        params.category = props.categoryFilter;
    }
    
    // Add agenda type filter if in agenda view
    if (props.eventFilter === 'agenda' && selectedAgendaType.value && selectedAgendaType.value !== 'all') {
        params.agenda_type = selectedAgendaType.value;
    }
    
    router.get(route('event.index'), params, {
        preserveState: true,
        replace: true,
    });
};

// Filter functions
const applyDateFilter = () => {
    try {
        const newDate = new Date(selectedYear.value, selectedMonth.value - 1, 1);
        
        // Validate the constructed date
        if (isNaN(newDate.getTime())) {
            console.warn('Invalid date in applyDateFilter:', {
                year: selectedYear.value,
                month: selectedMonth.value
            });
            return;
        }
        
        currentDate.value = newDate;
        navigateToMonth();
    } catch (error) {
        console.error('Error in applyDateFilter:', error);
    }
};

// Watch for filter changes (only when user changes dropdowns, not navigation buttons)
watch([selectedMonth, selectedYear], () => {
    try {
        if (!isNavigating && selectedMonth.value && selectedYear.value) {
            applyDateFilter();
        }
    } catch (error) {
        console.error('Error in filter watcher:', error);
    }
}, { flush: 'post' });

// Watch for agenda type filter changes
watch(selectedAgendaType, () => {
    try {
        if (!isNavigating && props.eventFilter === 'agenda') {
            applyDateFilter();
        }
    } catch (error) {
        console.error('Error in agenda type filter watcher:', error);
    }
}, { flush: 'post' });

// Event management
const openDayModal = (date: Date) => {
    selectedDate.value = date;
    
    // Get events for this day
    const dayEvents = props.events.filter(event => {
        const eventStart = new Date(event.start + 'T00:00:00');
        const eventEnd = new Date(event.end + 'T23:59:59');
        return date >= eventStart && date <= eventEnd;
    });
    
    // Get holidays for this day
    const dayHolidays = props.holidays.filter(holiday => {
        const holidayDate = new Date(holiday.start);
        return date.toDateString() === holidayDate.toDateString();
    });
    
    selectedDayEvents.value = dayEvents;
    selectedDayHolidays.value = dayHolidays;
    showDayModal.value = true;
};

const openEventModal = (date?: Date) => {
    // Reset form and state
    resetForm();
    
    // Determine the best date to use:
    let targetDate: Date;
    
    if (date) {
        // Explicitly provided date (from day modal buttons)
        targetDate = date;
    } else if (selectedDate.value) {
        // Use currently selected date from day modal
        targetDate = selectedDate.value;
    } else {
        // Fallback: use today if viewing current month, otherwise first day of viewed month
        const today = new Date();
        const viewingCurrentMonth = currentDate.value.getMonth() === today.getMonth() && 
                                   currentDate.value.getFullYear() === today.getFullYear();
        
        if (viewingCurrentMonth) {
            targetDate = today;
        } else {
            // Use first day of currently viewed month
            targetDate = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1);
        }
    }
    
    selectedDate.value = targetDate;
    // Format date in local timezone to avoid timezone conversion issues
    eventForm.value.start_date = formatDateLocal(targetDate);
    eventForm.value.end_date = formatDateLocal(targetDate);
    
    editingEvent.value = null;
    showDayModal.value = false;
    showEventModal.value = true;
    
    // Focus on title field after modal opens
    setTimeout(() => {
        const titleInput = document.querySelector('#event-title-input') as HTMLInputElement;
        if (titleInput) {
            titleInput.focus();
        }
    }, 100);
};

const editEvent = async (event: KamtibmasEvent) => {
    try {
        isLoading.value = true;
        resetForm();
        
        const response = await fetch(route('event.show', { event: event.id }), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        eventForm.value = {
            title: data.event.title,
            start_date: data.event.start_date,
            end_date: data.event.end_date,
            description: data.event.description || '',
            category: data.event.category || 'kamtibmas',
            color: data.event.color,
        };
        
        editingEvent.value = event;
        showDayModal.value = false; // Close day modal first
        showEventModal.value = true; // Then open event modal
        
        // Focus on title field after modal opens
        setTimeout(() => {
            const titleInput = document.querySelector('#event-title-input') as HTMLInputElement;
            if (titleInput) {
                titleInput.focus();
                titleInput.select();
            }
        }, 100);
        
    } catch (error) {
        console.error('Error loading event:', error);
        alert('Gagal memuat data event. Silakan coba lagi.');
    } finally {
        isLoading.value = false;
    }
};

const resetForm = () => {
    // Set default category based on current filter
    let defaultCategory = getDefaultCategory();
    if (!availableCategories.value.includes(defaultCategory) && availableCategories.value.length > 0) {
        defaultCategory = availableCategories.value[0];
    }
    
    eventForm.value = {
        title: '',
        start_date: '',
        end_date: '',
        description: '',
        category: defaultCategory,
        color: '#3B82F6',
    };
    errors.value = {};
    isLoading.value = false;
};

const saveEvent = async () => {
    if (isLoading.value) return;
    
    try {
        isLoading.value = true;
        errors.value = {};
        
        // Client-side validation
        let hasErrors = false;
        
        if (!eventForm.value.title.trim()) {
            errors.value.title = 'Judul event harus diisi';
            hasErrors = true;
        }
        
        if (!eventForm.value.start_date) {
            errors.value.start_date = 'Tanggal mulai harus diisi';
            hasErrors = true;
        }
        
        if (!eventForm.value.end_date) {
            errors.value.end_date = 'Tanggal selesai harus diisi';
            hasErrors = true;
        }
        
        if (!eventForm.value.category) {
            errors.value.category = 'Kategori harus dipilih';
            hasErrors = true;
        }
        
        if (eventForm.value.start_date && eventForm.value.end_date && 
            new Date(eventForm.value.end_date) < new Date(eventForm.value.start_date)) {
            errors.value.end_date = 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai';
            hasErrors = true;
        }
        
        if (hasErrors) {
            return;
        }
        
        const url = editingEvent.value 
            ? route('event.update', { event: editingEvent.value.id })
            : route('event.store');
        
        const method = editingEvent.value ? 'PUT' : 'POST';
        
        // Get CSRF token from meta tag or page props
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         (window as any).Laravel?.csrfToken || '';
        
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(eventForm.value),
        });
        
        const data = await response.json();
        
        if (response.ok) {
            // Force close without confirmation since data was saved
            closeModal(true);
            
            // Show success message
            const message = editingEvent.value ? 'Event berhasil diperbarui!' : 'Event berhasil ditambahkan!';
            
            // Refresh the page with current date parameters to show updated events
            const currentDateParam = currentDate.value.toISOString().slice(0, 7); // YYYY-MM
            const params: any = { date: currentDateParam };
            
            if (props.eventFilter) {
                params.event = props.eventFilter;
            }
            
            if (props.categoryFilter) {
                params.category = props.categoryFilter;
            }
            
            // Add agenda type filter if in agenda view
            if (props.eventFilter === 'agenda' && selectedAgendaType.value && selectedAgendaType.value !== 'all') {
                params.agenda_type = selectedAgendaType.value;
            }
            
            router.get(route('event.index'), params, {
                preserveState: false,
                replace: false,
                onFinish: () => {
                    // Re-open day modal if it was open
                    if (selectedDate.value) {
                        setTimeout(() => {
                            openDayModal(selectedDate.value!);
                        }, 200);
                    }
                }
            });
        } else {
            // Handle validation errors
            if (data.errors) {
                errors.value = data.errors;
            } else if (data.message) {
                alert(data.message);
            } else {
                alert('Terjadi kesalahan saat menyimpan event. Silakan coba lagi.');
            }
        }
    } catch (error) {
        console.error('Error saving event:', error);
        alert('Terjadi kesalahan jaringan. Silakan periksa koneksi internet Anda.');
    } finally {
        isLoading.value = false;
    }
};

const deleteEvent = async (event: KamtibmasEvent) => {
    const confirmMessage = `Apakah Anda yakin ingin menghapus event "${event.title}"?\nTindakan ini tidak dapat dibatalkan.`;
    
    if (confirm(confirmMessage)) {
        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             (window as any).Laravel?.csrfToken || '';
            
            const response = await fetch(route('event.show', { event: event.id }), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            const data = await response.json();
            
            if (response.ok) {
                console.log('Event berhasil dihapus!');
                
                // Refresh the page with current date parameters to show updated events
                const currentDateParam = currentDate.value.toISOString().slice(0, 7); // YYYY-MM
                const params: any = { date: currentDateParam };
                
                if (props.eventFilter) {
                    params.event = props.eventFilter;
                }
                
                if (props.categoryFilter) {
                    params.category = props.categoryFilter;
                }
                
                // Add agenda type filter if in agenda view
                if (props.eventFilter === 'agenda' && selectedAgendaType.value && selectedAgendaType.value !== 'all') {
                    params.agenda_type = selectedAgendaType.value;
                }
                
                router.get(route('event.index'), params, {
                    preserveState: false,
                    replace: false,
                    onFinish: () => {
                        // Re-open day modal if it was open
                        if (selectedDate.value) {
                            setTimeout(() => {
                                openDayModal(selectedDate.value!);
                            }, 200);
                        }
                    }
                });
            } else {
                alert(data.message || 'Gagal menghapus event. Silakan coba lagi.');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            alert('Terjadi kesalahan jaringan. Silakan periksa koneksi internet Anda.');
        }
    }
};

// Check if there are unsaved changes
const hasUnsavedChanges = computed(() => {
    if (!showEventModal.value) return false;
    
    const form = eventForm.value;
    return form.title.trim() !== '' || 
           form.description.trim() !== '' || 
           form.start_date !== '' || 
           form.end_date !== '' || 
           form.color !== '#3B82F6';
});

const closeModal = (force = false) => {
    // If there are unsaved changes and not forced, ask for confirmation
    if (hasUnsavedChanges.value && !force && !isLoading.value) {
        const confirmMessage = 'Ada perubahan yang belum disimpan. Yakin ingin menutup modal?';
        if (!confirm(confirmMessage)) {
            return;
        }
    }
    
    showEventModal.value = false;
    resetForm();
    selectedDate.value = null;
    editingEvent.value = null;
};

const closeDayModal = () => {
    showDayModal.value = false;
    selectedDate.value = null;
    selectedDayEvents.value = [];
    selectedDayHolidays.value = [];
};

const formatDateDisplay = (date: Date) => {
    const options: Intl.DateTimeFormatOptions = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return new Intl.DateTimeFormat('id-ID', options).format(date);
};

// Helper function to format date as YYYY-MM-DD in local timezone
const formatDateLocal = (date: Date) => {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Color presets
const colorPresets = [
    '#3B82F6', // Blue
    '#EF4444', // Red  
    '#10B981', // Green
    '#F59E0B', // Yellow
    '#8B5CF6', // Purple
    '#EC4899', // Pink
    '#14B8A6', // Teal
    '#F97316', // Orange
];
</script>

<template>
    <Head :title="getPageTitle()" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ getPageTitle() }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ getPageDescription() }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <Button @click="goToToday" variant="outline" size="sm">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Hari Ini
                    </Button>
                    <Button @click="openEventModal()" size="sm">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Event
                    </Button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Event</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.totalEvents }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Event Mendatang</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.upcomingEvents }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900">
                            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Event Aktif</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ statistics.activeEvents }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <!-- Calendar Header -->
                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ currentMonth }} {{ currentYear }}
                            </h2>
                        </div>
                        
                        <!-- Month/Year Filters -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                            <!-- Agenda Type Filter (only for agenda view) -->
                            <div v-if="props.eventFilter === 'agenda'" class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jenis:</label>
                                <select
                                    v-model="selectedAgendaType"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option v-for="option in agendaTypeOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Bulan:</label>
                                <select
                                    v-model="selectedMonth"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option v-for="month in monthOptions" :key="month.value" :value="month.value">
                                        {{ month.label }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
                                <select
                                    v-model="selectedYear"
                                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option v-for="year in yearOptions" :key="year" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <Button @click="previousMonth" variant="outline" size="sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </Button>
                                <Button @click="nextMonth" variant="outline" size="sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="p-6">
                    <!-- Day Headers -->
                    <div class="mb-4 grid grid-cols-7 gap-1">
                        <div v-for="day in dayNames" :key="day" class="py-2 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-1">
                        <div
                            v-for="day in calendarDays"
                            :key="day.date.toISOString()"
                            class="group relative min-h-[120px] cursor-pointer rounded-lg border p-2 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                            :class="{
                                'border-gray-200 dark:border-gray-700': day.isCurrentMonth,
                                'border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800': !day.isCurrentMonth,
                                'ring-2 ring-blue-500': day.isToday,
                                'bg-red-50 border-red-200 dark:bg-red-900 dark:border-red-700': day.holidays.some(h => h.isNationalHoliday),
                                'bg-orange-50 border-orange-200 dark:bg-orange-900 dark:border-orange-700': day.holidays.some(h => !h.isNationalHoliday) && !day.holidays.some(h => h.isNationalHoliday),
                            }"
                            @click="openDayModal(day.date)"
                        >
                            <!-- Day Number -->
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-gray-900 dark:text-white': day.isCurrentMonth,
                                        'text-gray-400 dark:text-gray-600': !day.isCurrentMonth,
                                        'text-blue-600 dark:text-blue-400': day.isToday,
                                    }"
                                >
                                    {{ day.day }}
                                </span>
                                <div v-if="day.allItems.length > 0" class="flex items-center gap-1">
                                    <div v-if="day.events.length > 0" class="rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                        {{ day.events.length }}
                                    </div>
                                    <div v-if="day.holidays.length > 0" class="rounded-full px-1.5 py-0.5 text-xs font-medium"
                                         :class="day.holidays.some(h => h.isNationalHoliday) 
                                             ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400' 
                                             : 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-400'"
                                    >
                                        🏛️
                                    </div>
                                </div>
                            </div>

                            <!-- Events and Holidays -->
                            <div class="mt-1 space-y-1">
                                <!-- Display holidays first -->
                                <div
                                    v-for="holiday in day.holidays"
                                    :key="holiday.id"
                                    class="truncate rounded px-2 py-1 text-xs font-medium text-white cursor-default"
                                    :style="{ backgroundColor: holiday.color }"
                                    :title="`${holiday.title} - ${holiday.description}`"
                                >
                                    <span class="truncate block">🏛️ {{ holiday.title }}</span>
                                </div>
                                
                                <!-- Display events -->
                                <div
                                    v-for="(event, index) in day.events.slice(0, day.holidays.length > 0 ? 2 : 3)"
                                    :key="event.id"
                                    class="event-item group relative truncate rounded px-2 py-1 text-xs font-medium text-white cursor-pointer transition-all hover:shadow-md hover:scale-105"
                                    :style="{ backgroundColor: event.color }"
                                    :title="`${event.title}${event.description ? ' - ' + event.description : ''}`"
                                    @click.stop="editEvent(event)"
                                >
                                    <span class="truncate block pr-4">{{ event.title }}</span>
                                    <!-- Event actions on hover -->
                                    <div class="absolute right-0 top-0 h-full hidden group-hover:flex items-center">
                                        <button
                                            @click.stop="deleteEvent(event)"
                                            class="rounded-full bg-red-500 p-1 text-white hover:bg-red-600 shadow-sm transition-colors ml-1"
                                            :title="`Hapus event: ${event.title}`"
                                        >
                                            <svg class="h-2.5 w-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="day.allItems.length > (day.holidays.length > 0 ? 3 : 3)" class="text-xs text-gray-500 dark:text-gray-400">
                                    +{{ day.allItems.length - (day.holidays.length > 0 ? 3 : 3) }} lainnya
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Modal -->
        <div 
            v-if="showEventModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
            @keydown.escape="!isLoading && closeModal()"
        >
            <!-- Modal backdrop -->
            <div 
                class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity" 
                aria-hidden="true"
            ></div>
            
            <!-- Modal content container -->
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg dark:bg-gray-800"
                         @click.stop>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                                {{ editingEvent ? 'Edit Event' : 'Tambah Event Baru' }}
                            </h3>
                            <button
                                @click="closeModal(false)"
                                :disabled="isLoading"
                                class="rounded-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors"
                                :class="{ 'cursor-not-allowed opacity-50': isLoading }"
                                title="Tutup modal"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-4 space-y-4">
                            <!-- Title -->
                            <div>
                                <label for="event-title-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Judul Event <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="event-title-input"
                                    v-model="eventForm.title"
                                    type="text"
                                    :disabled="isLoading"
                                    class="mt-1 block w-full rounded-md border px-3 py-2 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500': errors.title,
                                        'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600': !errors.title,
                                        'bg-gray-100 cursor-not-allowed dark:bg-gray-600': isLoading,
                                        'bg-white dark:bg-gray-700': !isLoading
                                    }"
                                    placeholder="Masukkan judul event"
                                    @keydown.enter.prevent="saveEvent"
                                />
                                <div v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</div>
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="start-date-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Mulai <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="start-date-input"
                                        v-model="eventForm.start_date"
                                        type="date"
                                        :disabled="isLoading"
                                        class="mt-1 block w-full rounded-md border px-3 py-2 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.start_date,
                                            'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600': !errors.start_date,
                                            'bg-gray-100 cursor-not-allowed dark:bg-gray-600': isLoading,
                                            'bg-white dark:bg-gray-700': !isLoading
                                        }"
                                    />
                                    <div v-if="errors.start_date" class="mt-1 text-sm text-red-600">{{ errors.start_date }}</div>
                                </div>
                                <div>
                                    <label for="end-date-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Selesai <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="end-date-input"
                                        v-model="eventForm.end_date"
                                        type="date"
                                        :disabled="isLoading"
                                        :min="eventForm.start_date"
                                        class="mt-1 block w-full rounded-md border px-3 py-2 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                                        :class="{
                                            'border-red-300 focus:border-red-500 focus:ring-red-500': errors.end_date,
                                            'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600': !errors.end_date,
                                            'bg-gray-100 cursor-not-allowed dark:bg-gray-600': isLoading,
                                            'bg-white dark:bg-gray-700': !isLoading
                                        }"
                                    />
                                    <div v-if="errors.end_date" class="mt-1 text-sm text-red-600">{{ errors.end_date }}</div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deskripsi</label>
                                <textarea
                                    id="description-input"
                                    v-model="eventForm.description"
                                    rows="3"
                                    :disabled="isLoading"
                                    class="mt-1 block w-full rounded-md border px-3 py-2 shadow-sm focus:outline-none focus:ring-2 transition-colors resize-none"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500': errors.description,
                                        'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600': !errors.description,
                                        'bg-gray-100 cursor-not-allowed dark:bg-gray-600': isLoading,
                                        'bg-white dark:bg-gray-700': !isLoading
                                    }"
                                    placeholder="Masukkan deskripsi event (opsional)"
                                ></textarea>
                                <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</div>
                            </div>

                            <!-- Category -->
                            <div>
                                <label for="category-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="category-select"
                                    v-model="eventForm.category"
                                    :disabled="isLoading"
                                    class="mt-1 block w-full rounded-md border px-3 py-2 shadow-sm focus:outline-none focus:ring-2 transition-colors"
                                    :class="{
                                        'border-red-300 focus:border-red-500 focus:ring-red-500': errors.category,
                                        'border-gray-300 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600': !errors.category,
                                        'bg-gray-100 cursor-not-allowed dark:bg-gray-600': isLoading,
                                        'bg-white dark:bg-gray-700': !isLoading
                                    }"
                                >
                                    <option v-for="category in availableCategories" :key="category" :value="category">
                                        {{ category }}
                                    </option>
                                </select>
                                <div v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</div>
                            </div>

                            <!-- Color -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Warna Event</label>
                                <div class="mt-2 flex items-center gap-2 flex-wrap">
                                    <button
                                        v-for="color in colorPresets"
                                        :key="color"
                                        type="button"
                                        :disabled="isLoading"
                                        class="h-8 w-8 rounded-full border-2 border-white shadow-sm ring-2 transition-all hover:scale-110"
                                        :class="{
                                            'ring-gray-900 dark:ring-gray-100': eventForm.color === color,
                                            'ring-gray-300 dark:ring-gray-600': eventForm.color !== color,
                                            'cursor-not-allowed opacity-50': isLoading,
                                            'cursor-pointer hover:ring-gray-500': !isLoading
                                        }"
                                        :style="{ backgroundColor: color }"
                                        :title="`Pilih warna ${color}`"
                                        @click="!isLoading && (eventForm.color = color)"
                                    ></button>
                                    <input
                                        v-model="eventForm.color"
                                        type="color"
                                        :disabled="isLoading"
                                        class="h-8 w-8 rounded border border-gray-300 shadow-sm transition-opacity"
                                        :class="{
                                            'cursor-not-allowed opacity-50': isLoading,
                                            'cursor-pointer hover:shadow-md': !isLoading
                                        }"
                                        title="Pilih warna kustom"
                                    />
                                </div>
                                <div v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color }}</div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih warna yang akan ditampilkan pada kalender</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                        <Button 
                            @click="saveEvent" 
                            :disabled="isLoading"
                            class="w-full sm:ml-3 sm:w-auto"
                        >
                            <svg 
                                v-if="isLoading" 
                                class="mr-2 -ml-1 h-4 w-4 animate-spin" 
                                xmlns="http://www.w3.org/2000/svg" 
                                fill="none" 
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isLoading ? 'Menyimpan...' : (editingEvent ? 'Perbarui Event' : 'Simpan Event') }}
                        </Button>
                        <Button 
                            @click="closeModal(false)" 
                            variant="outline" 
                            :disabled="isLoading"
                            class="mt-3 w-full sm:mt-0 sm:w-auto"
                        >
                            Batal
                        </Button>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Day Events Modal -->
        <div 
            v-if="showDayModal && selectedDate" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="day-modal-title" 
            role="dialog" 
            aria-modal="true"
            @keydown.escape="closeDayModal()"
        >
            <!-- Modal backdrop -->
            <div 
                class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity" 
                aria-hidden="true" 
                @click="closeDayModal()"
            ></div>
            
            <!-- Modal content container -->
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl dark:bg-gray-800"
                         @click.stop>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="day-modal-title">
                                {{ formatDateDisplay(selectedDate) }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <Button @click="openEventModal(selectedDate)" size="sm">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Event
                                </Button>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <!-- Holidays Section -->
                            <div v-if="selectedDayHolidays.length > 0">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">🏛️ Hari Libur</h4>
                                <div class="space-y-2">
                                    <div
                                        v-for="holiday in selectedDayHolidays"
                                        :key="holiday.id"
                                        class="flex items-center justify-between p-3 rounded-lg border"
                                        :class="holiday.isNationalHoliday 
                                            ? 'bg-red-50 border-red-200 dark:bg-red-900 dark:border-red-700' 
                                            : 'bg-orange-50 border-orange-200 dark:bg-orange-900 dark:border-orange-700'"
                                    >
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-900 dark:text-white">{{ holiday.title }}</h5>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ holiday.description }}</p>
                                        </div>
                                        <div 
                                            class="w-4 h-4 rounded-full flex-shrink-0"
                                            :style="{ backgroundColor: holiday.color }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Events Section -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 flex items-center justify-between">
                                    📅 Event ({{ selectedDayEvents.length }})
                                </h4>
                                
                                <div v-if="selectedDayEvents.length === 0" class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Tidak ada event</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan membuat event baru untuk hari ini.</p>
                                    <div class="mt-6">
                                        <Button @click="openEventModal(selectedDate)" size="sm">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah Event
                                        </Button>
                                    </div>
                                </div>

                                <div v-else class="space-y-3">
                                    <div
                                        v-for="event in selectedDayEvents"
                                        :key="event.id"
                                        class="group flex items-center justify-between p-4 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <div class="flex items-center space-x-3 flex-1">
                                            <div 
                                                class="w-4 h-4 rounded-full flex-shrink-0"
                                                :style="{ backgroundColor: event.color }"
                                            ></div>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-medium text-gray-900 dark:text-white">{{ event.title }}</h5>
                                                <div class="flex items-center gap-4 mt-1">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ categories[event.category] || event.category }}
                                                    </span>
                                                    <span v-if="event.isMultiDay" class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ event.duration }} hari
                                                    </span>
                                                </div>
                                                <p v-if="event.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1 truncate">
                                                    {{ event.description }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Button 
                                                @click="editEvent(event)" 
                                                variant="outline" 
                                                size="sm"
                                                class="text-blue-600 hover:text-blue-700"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </Button>
                                            <Button 
                                                @click="deleteEvent(event)" 
                                                variant="outline" 
                                                size="sm"
                                                class="text-red-600 hover:text-red-700"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 dark:bg-gray-700">
                        <div class="flex justify-end">
                            <Button @click="closeDayModal()" variant="outline">
                                Tutup
                            </Button>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom animations for calendar events */
.event-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.event-item:hover {
    transform: translateY(-1px) scale(1.02);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Prevent text selection on calendar days */
.calendar-day {
    user-select: none;
}

/* Focus styles for better accessibility */
button:focus-visible {
    outline: 2px solid #3B82F6;
    outline-offset: 2px;
}

input:focus,
textarea:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Modal backdrop animation */
@media (prefers-reduced-motion: no-preference) {
    .modal-backdrop {
        animation: fadeIn 0.2s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
}
</style>