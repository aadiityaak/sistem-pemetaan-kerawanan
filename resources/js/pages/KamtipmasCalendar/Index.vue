<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

interface KamtipmasEvent {
    id: number;
    title: string;
    start: string;
    end: string;
    description?: string;
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
    events: KamtipmasEvent[];
    statistics: Statistics;
    currentDate: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Kalender Kamtipmas',
        href: '/kamtipmas-calendar',
    },
];

// Calendar state
const currentDate = ref(new Date(props.currentDate));
const selectedDate = ref<Date | null>(null);
const showEventModal = ref(false);
const editingEvent = ref<KamtipmasEvent | null>(null);

// Event form state
const eventForm = ref({
    title: '',
    start_date: '',
    end_date: '',
    description: '',
    color: '#3B82F6',
});

const errors = ref<Record<string, string>>({});
const isLoading = ref(false);

// Calendar navigation
const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

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
            const eventStart = new Date(event.start);
            const eventEnd = new Date(event.end);
            return date >= eventStart && date <= eventEnd;
        });
        
        days.push({
            date: new Date(date),
            day: date.getDate(),
            isCurrentMonth: date.getMonth() === month,
            isToday: date.toDateString() === today.toDateString(),
            events: dayEvents,
        });
    }
    
    return days;
});

// Navigation functions
const previousMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() - 1);
    currentDate.value = newDate;
    navigateToMonth();
};

const nextMonth = () => {
    const newDate = new Date(currentDate.value);
    newDate.setMonth(newDate.getMonth() + 1);
    currentDate.value = newDate;
    navigateToMonth();
};

const goToToday = () => {
    currentDate.value = new Date();
    navigateToMonth();
};

const navigateToMonth = () => {
    const dateParam = currentDate.value.toISOString().slice(0, 7); // YYYY-MM
    router.get('/kamtipmas-calendar', { date: dateParam }, {
        preserveState: true,
        replace: true,
    });
};

// Event management
const openEventModal = (date?: Date) => {
    // Reset form and state
    resetForm();
    
    if (date) {
        selectedDate.value = date;
        eventForm.value.start_date = date.toISOString().slice(0, 10);
        eventForm.value.end_date = date.toISOString().slice(0, 10);
    }
    
    editingEvent.value = null;
    showEventModal.value = true;
    
    // Focus on title field after modal opens
    setTimeout(() => {
        const titleInput = document.querySelector('#event-title-input') as HTMLInputElement;
        if (titleInput) {
            titleInput.focus();
        }
    }, 100);
};

const editEvent = async (event: KamtipmasEvent) => {
    try {
        isLoading.value = true;
        resetForm();
        
        const response = await fetch(`/kamtipmas-events/${event.id}`, {
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
            color: data.event.color,
        };
        
        editingEvent.value = event;
        showEventModal.value = true;
        
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
    eventForm.value = {
        title: '',
        start_date: '',
        end_date: '',
        description: '',
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
        if (!eventForm.value.title.trim()) {
            errors.value.title = 'Judul event harus diisi';
            return;
        }
        
        if (!eventForm.value.start_date) {
            errors.value.start_date = 'Tanggal mulai harus diisi';
            return;
        }
        
        if (!eventForm.value.end_date) {
            errors.value.end_date = 'Tanggal selesai harus diisi';
            return;
        }
        
        if (new Date(eventForm.value.end_date) < new Date(eventForm.value.start_date)) {
            errors.value.end_date = 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai';
            return;
        }
        
        const url = editingEvent.value 
            ? `/kamtipmas-events/${editingEvent.value.id}`
            : '/kamtipmas-events';
        
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
            showEventModal.value = false;
            resetForm();
            // Refresh the page to show updated events
            router.reload({
                onFinish: () => {
                    // Show success message
                    const message = editingEvent.value ? 'Event berhasil diperbarui!' : 'Event berhasil ditambahkan!';
                    // You can integrate with a toast library here
                    console.log(message);
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

const deleteEvent = async (event: KamtipmasEvent) => {
    const confirmMessage = `Apakah Anda yakin ingin menghapus event "${event.title}"?\nTindakan ini tidak dapat dibatalkan.`;
    
    if (confirm(confirmMessage)) {
        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             (window as any).Laravel?.csrfToken || '';
            
            const response = await fetch(`/kamtipmas-events/${event.id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            const data = await response.json();
            
            if (response.ok) {
                router.reload({
                    onFinish: () => {
                        console.log('Event berhasil dihapus!');
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

const closeModal = () => {
    showEventModal.value = false;
    resetForm();
    selectedDate.value = null;
    editingEvent.value = null;
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
    <Head title="Kalender Kamtipmas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 rounded-xl p-6">
            <!-- Header -->
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kalender Kamtipmas</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Jadwal kegiatan Keamanan dan Ketertiban Masyarakat</p>
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
                <div class="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ currentMonth }} {{ currentYear }}
                        </h2>
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
                            }"
                            @click="openEventModal(day.date)"
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
                                <div v-if="day.events.length > 0" class="rounded-full bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                    {{ day.events.length }}
                                </div>
                            </div>

                            <!-- Events -->
                            <div class="mt-1 space-y-1">
                                <div
                                    v-for="(event, index) in day.events.slice(0, 3)"
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
                                <div v-if="day.events.length > 3" class="text-xs text-gray-500 dark:text-gray-400">
                                    +{{ day.events.length - 3 }} lainnya
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
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div 
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 backdrop-blur-sm transition-opacity" 
                    aria-hidden="true" 
                    @click="!isLoading && closeModal()"
                ></div>
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800"
                     @click.stop>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                            {{ editingEvent ? 'Edit Event' : 'Tambah Event Baru' }}
                        </h3>
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
                            @click="closeModal" 
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