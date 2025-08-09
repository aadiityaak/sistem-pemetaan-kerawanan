<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class KamtibmasEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get current month and year or from request
        $currentDate = $request->get('date', now()->format('Y-m'));
        $date = Carbon::createFromFormat('Y-m', $currentDate)->startOfMonth();
        
        // Get event filter parameter
        $eventFilter = $request->get('event'); // kamtibmas, agenda, etc.
        $agendaType = $request->get('agenda_type'); // nasional, internasional (for agenda filter)
        
        // Get events for the current view (month view - get events for 6 weeks around the month)
        $startDate = $date->copy()->startOfMonth()->startOfWeek();
        $endDate = $date->copy()->endOfMonth()->endOfWeek();
        
        $eventsQuery = Event::active()->inDateRange($startDate, $endDate);
        
        // Filter by event type/category if specified
        if ($eventFilter) {
            switch ($eventFilter) {
                case 'kamtibmas':
                    $eventsQuery->where('category', 'Kamtibmas');
                    break;
                case 'agenda':
                    if ($agendaType) {
                        switch ($agendaType) {
                            case 'nasional':
                                $eventsQuery->where('category', 'Agenda Nasional');
                                break;
                            case 'internasional':
                                $eventsQuery->where('category', 'Agenda Internasional');
                                break;
                            default:
                                $eventsQuery->whereIn('category', ['Agenda Nasional', 'Agenda Internasional']);
                                break;
                        }
                    } else {
                        $eventsQuery->whereIn('category', ['Agenda Nasional', 'Agenda Internasional']);
                    }
                    break;
                default:
                    // Allow specific category filter
                    $eventsQuery->where('category', $eventFilter);
                    break;
            }
        }
        
        $events = $eventsQuery->orderBy('start_date')->get();
        
        // Debug: Log the query details
        \Log::info('Calendar Events Query:', [
            'current_date' => $currentDate,
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'events_found' => $events->count(),
            'all_active_events' => Event::active()->count()
        ]);
        
        $events = $events
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date->format('Y-m-d'),
                    'end' => $event->end_date->format('Y-m-d'),
                    'description' => $event->description,
                    'category' => $event->category,
                    'color' => $event->color,
                    'isMultiDay' => $event->is_multi_day,
                    'duration' => $event->duration,
                ];
            });

        // Get all events for the month (for statistics) with same filter
        $monthlyEventsQuery = Event::active()
            ->whereMonth('start_date', $date->month)
            ->whereYear('start_date', $date->year);
            
        // Apply same filter for statistics
        if ($eventFilter) {
            switch ($eventFilter) {
                case 'kamtibmas':
                    $monthlyEventsQuery->where('category', 'Kamtibmas');
                    break;
                case 'agenda':
                    if ($agendaType) {
                        switch ($agendaType) {
                            case 'nasional':
                                $monthlyEventsQuery->where('category', 'Agenda Nasional');
                                break;
                            case 'internasional':
                                $monthlyEventsQuery->where('category', 'Agenda Internasional');
                                break;
                            default:
                                $monthlyEventsQuery->whereIn('category', ['Agenda Nasional', 'Agenda Internasional']);
                                break;
                        }
                    } else {
                        $monthlyEventsQuery->whereIn('category', ['Agenda Nasional', 'Agenda Internasional']);
                    }
                    break;
                default:
                    $monthlyEventsQuery->where('category', $eventFilter);
                    break;
            }
        }
        
        $monthlyEvents = $monthlyEventsQuery->get();

        $statistics = [
            'totalEvents' => $monthlyEvents->count(),
            'upcomingEvents' => $monthlyEvents->where('start_date', '>=', now()->format('Y-m-d'))->count(),
            'activeEvents' => $monthlyEvents->where('start_date', '<=', now()->format('Y-m-d'))
                                          ->where('end_date', '>=', now()->format('Y-m-d'))->count(),
        ];

        // Get Indonesian holidays (only for Kamtibmas view)
        $holidays = [];
        if ($eventFilter === 'kamtibmas') {
            $holidays = $this->getIndonesianHolidays($date->year);
        }

        return Inertia::render('KamtibmasCalendar/Index', [
            'events' => $events,
            'statistics' => $statistics,
            'currentDate' => $date->format('Y-m-d'),
            'holidays' => $holidays,
            'categories' => Event::getCategories(),
            'eventFilter' => $eventFilter,
            'agendaType' => $agendaType,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'in:' . implode(',', Event::getCategories())],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
        ]);

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event berhasil ditambahkan.',
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d'),
                'end' => $event->end_date->format('Y-m-d'),
                'description' => $event->description,
                'category' => $event->category,
                'color' => $event->color,
                'isMultiDay' => $event->is_multi_day,
                'duration' => $event->duration,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start_date' => $event->start_date->format('Y-m-d'),
                'end_date' => $event->end_date->format('Y-m-d'),
                'description' => $event->description,
                'category' => $event->category,
                'color' => $event->color,
                'is_active' => $event->is_active,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'string', 'in:' . implode(',', Event::getCategories())],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{3,6}$/'],
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event berhasil diperbarui.',
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d'),
                'end' => $event->end_date->format('Y-m-d'),
                'description' => $event->description,
                'category' => $event->category,
                'color' => $event->color,
                'isMultiDay' => $event->is_multi_day,
                'duration' => $event->duration,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event berhasil dihapus.'
        ]);
    }

    /**
     * Toggle event status
     */
    public function toggleStatus(Event $event)
    {
        $event->update(['is_active' => !$event->is_active]);

        $status = $event->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Event berhasil {$status}."
        ]);
    }

    /**
     * Get Indonesian holidays from API
     */
    private function getIndonesianHolidays($year = null)
    {
        $year = $year ?? now()->year;
        $cacheKey = "indonesian_holidays_{$year}";
        
        // Cache holidays for 1 day to avoid excessive API calls
        return Cache::remember($cacheKey, now()->addDay(), function () use ($year) {
            try {
                $response = Http::timeout(10)->get('https://api-harilibur.vercel.app/api');
                
                if ($response->successful()) {
                    $holidays = collect($response->json())
                        ->filter(function ($holiday) use ($year) {
                            // Filter holidays for the specified year
                            return Carbon::parse($holiday['holiday_date'])->year === $year;
                        })
                        ->map(function ($holiday) {
                            $date = Carbon::parse($holiday['holiday_date']);
                            return [
                                'id' => 'holiday_' . $date->format('Y_m_d'),
                                'title' => $holiday['holiday_name'],
                                'start' => $date->format('Y-m-d'),
                                'end' => $date->format('Y-m-d'),
                                'description' => $holiday['is_national_holiday'] 
                                    ? 'Hari Libur Nasional' 
                                    : 'Hari Besar Agama',
                                'color' => $holiday['is_national_holiday'] 
                                    ? '#DC2626' // Red for national holidays
                                    : '#F59E0B', // Orange for religious holidays
                                'isHoliday' => true,
                                'isNationalHoliday' => $holiday['is_national_holiday'],
                                'isMultiDay' => false,
                                'duration' => 1,
                            ];
                        })
                        ->values()
                        ->toArray();
                    
                    return $holidays;
                }
                
                // Return empty array if API fails
                return [];
                
            } catch (\Exception $e) {
                // Log the error and return empty array
                \Log::warning('Failed to fetch Indonesian holidays: ' . $e->getMessage());
                return [];
            }
        });
    }
}
