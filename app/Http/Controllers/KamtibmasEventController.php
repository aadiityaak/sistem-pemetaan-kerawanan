<?php

namespace App\Http\Controllers;

use App\Models\KamtibmasEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

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
        
        $events = KamtibmasEvent::active()
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
        $monthlyEvents = KamtibmasEvent::active()
            ->whereMonth('start_date', $date->month)
            ->whereYear('start_date', $date->year)
            ->get();

        $statistics = [
            'totalEvents' => $monthlyEvents->count(),
            'upcomingEvents' => $monthlyEvents->where('start_date', '>=', now()->format('Y-m-d'))->count(),
            'activeEvents' => $monthlyEvents->where('start_date', '<=', now()->format('Y-m-d'))
                                          ->where('end_date', '>=', now()->format('Y-m-d'))->count(),
        ];

        return Inertia::render('KamtibmasCalendar/Index', [
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

        $event = KamtibmasEvent::create($validated);

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
    public function show(KamtibmasEvent $kamtibmasEvent)
    {
        return response()->json([
            'event' => [
                'id' => $kamtibmasEvent->id,
                'title' => $kamtibmasEvent->title,
                'start_date' => $kamtibmasEvent->start_date->format('Y-m-d'),
                'end_date' => $kamtibmasEvent->end_date->format('Y-m-d'),
                'description' => $kamtibmasEvent->description,
                'color' => $kamtibmasEvent->color,
                'is_active' => $kamtibmasEvent->is_active,
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KamtibmasEvent $kamtibmasEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ]);

        $kamtibmasEvent->update($validated);

        return response()->json([
            'message' => 'Event berhasil diperbarui.',
            'event' => [
                'id' => $kamtibmasEvent->id,
                'title' => $kamtibmasEvent->title,
                'start' => $kamtibmasEvent->start_date->format('Y-m-d'),
                'end' => $kamtibmasEvent->end_date->format('Y-m-d'),
                'description' => $kamtibmasEvent->description,
                'color' => $kamtibmasEvent->color,
                'isMultiDay' => $kamtibmasEvent->is_multi_day,
                'duration' => $kamtibmasEvent->duration,
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KamtibmasEvent $kamtibmasEvent)
    {
        $kamtibmasEvent->delete();

        return response()->json([
            'message' => 'Event berhasil dihapus.'
        ]);
    }

    /**
     * Toggle event status
     */
    public function toggleStatus(KamtibmasEvent $kamtibmasEvent)
    {
        $kamtibmasEvent->update(['is_active' => !$kamtibmasEvent->is_active]);

        $status = $kamtibmasEvent->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Event berhasil {$status}."
        ]);
    }
}
