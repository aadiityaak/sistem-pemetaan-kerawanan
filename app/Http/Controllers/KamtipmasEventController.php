<?php

namespace App\Http\Controllers;

use App\Models\KamtipmasEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class KamtipmasEventController extends Controller
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
        
        $events = KamtipmasEvent::active()
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
                    'color' => $event->color,
                    'isMultiDay' => $event->is_multi_day,
                    'duration' => $event->duration,
                ];
            });

        // Get all events for the month (for statistics)
        $monthlyEvents = KamtipmasEvent::active()
            ->whereMonth('start_date', $date->month)
            ->whereYear('start_date', $date->year)
            ->get();

        $statistics = [
            'totalEvents' => $monthlyEvents->count(),
            'upcomingEvents' => $monthlyEvents->where('start_date', '>=', now()->format('Y-m-d'))->count(),
            'activeEvents' => $monthlyEvents->where('start_date', '<=', now()->format('Y-m-d'))
                                          ->where('end_date', '>=', now()->format('Y-m-d'))->count(),
        ];

        return Inertia::render('KamtipmasCalendar/Index', [
            'events' => $events,
            'statistics' => $statistics,
            'currentDate' => $date->format('Y-m-d'),
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
            'color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        $event = KamtipmasEvent::create($validated);

        return response()->json([
            'message' => 'Event berhasil ditambahkan.',
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->format('Y-m-d'),
                'end' => $event->end_date->format('Y-m-d'),
                'description' => $event->description,
                'color' => $event->color,
                'isMultiDay' => $event->is_multi_day,
                'duration' => $event->duration,
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(KamtipmasEvent $kamtipmasEvent)
    {
        return response()->json([
            'event' => [
                'id' => $kamtipmasEvent->id,
                'title' => $kamtipmasEvent->title,
                'start_date' => $kamtipmasEvent->start_date->format('Y-m-d'),
                'end_date' => $kamtipmasEvent->end_date->format('Y-m-d'),
                'description' => $kamtipmasEvent->description,
                'color' => $kamtipmasEvent->color,
                'is_active' => $kamtipmasEvent->is_active,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KamtipmasEvent $kamtipmasEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        $kamtipmasEvent->update($validated);

        return response()->json([
            'message' => 'Event berhasil diperbarui.',
            'event' => [
                'id' => $kamtipmasEvent->id,
                'title' => $kamtipmasEvent->title,
                'start' => $kamtipmasEvent->start_date->format('Y-m-d'),
                'end' => $kamtipmasEvent->end_date->format('Y-m-d'),
                'description' => $kamtipmasEvent->description,
                'color' => $kamtipmasEvent->color,
                'isMultiDay' => $kamtipmasEvent->is_multi_day,
                'duration' => $kamtipmasEvent->duration,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KamtipmasEvent $kamtipmasEvent)
    {
        $kamtipmasEvent->delete();

        return response()->json([
            'message' => 'Event berhasil dihapus.'
        ]);
    }

    /**
     * Toggle event status
     */
    public function toggleStatus(KamtipmasEvent $kamtipmasEvent)
    {
        $kamtipmasEvent->update(['is_active' => !$kamtipmasEvent->is_active]);

        $status = $kamtipmasEvent->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Event berhasil {$status}."
        ]);
    }
}
