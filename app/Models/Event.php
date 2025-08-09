<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'start_date',
        'end_date',
        'color',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active events only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for events in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($query) use ($startDate, $endDate) {
                      $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                  });
        });
    }

    /**
     * Check if event is multi-day
     */
    public function getIsMultiDayAttribute()
    {
        return $this->start_date->ne($this->end_date);
    }

    /**
     * Get event duration in days
     */
    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Scope for events in specific month/year
     */
    public function scopeInMonth($query, $month, $year)
    {
        return $query->whereMonth('start_date', $month)
                     ->whereYear('start_date', $year);
    }

    /**
     * Scope for upcoming events
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->format('Y-m-d'));
    }

    /**
     * Scope for currently active events
     */
    public function scopeCurrentlyActive($query)
    {
        $today = now()->format('Y-m-d');
        return $query->where('start_date', '<=', $today)
                     ->where('end_date', '>=', $today);
    }

    /**
     * Scope for specific category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get available event categories
     */
    public static function getCategories()
    {
        return [
            'agenda_nasional' => 'Agenda Nasional',
            'agenda_internasional' => 'Agenda Internasional', 
            'kamtibmas' => 'Kamtibmas'
        ];
    }
}
