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
        
        // Get events for the current view (month view - get events for 6 weeks around the month)
        $startDate = $date->copy()->startOfMonth()->startOfWeek();
        $endDate = $date->copy()->endOfMonth()->endOfWeek();
        
        $events = Event::active()
            ->inDateRange($startDate, $endDate)
            ->orderBy('start_date')
            ->get()
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

        // Get all events for the month (for statistics)
        $monthlyEvents = Event::active()
            ->whereMonth('start_date', $date->month)
            ->whereYear('start_date', $date->year)
            ->get();

        $statistics = [
            'totalEvents' => $monthlyEvents->count(),
            'upcomingEvents' => $monthlyEvents->where('start_date', '>=', now()->format('Y-m-d'))->count(),
            'activeEvents' => $monthlyEvents->where('start_date', '<=', now()->format('Y-m-d'))
                                          ->where('end_date', '>=', now()->format('Y-m-d'))->count(),
        ];

        // Get Indonesian holidays
        $holidays = $this->getIndonesianHolidays($date->year);

        return Inertia::render('KamtibmasCalendar/Index', [
            'events' => $events,
            'statistics' => $statistics,
            'currentDate' => $date->format('Y-m-d'),
            'holidays' => $holidays,
            'categories' => Event::getCategories(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'category' => 'required|string|in:agenda_nasional,agenda_internasional,kamtibmas',
            'color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
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
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'category' => 'required|string|in:agenda_nasional,agenda_internasional,kamtibmas',
            'color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
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
