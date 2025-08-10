<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndasAnalysisResult extends Model
{
    protected $fillable = [
        'kabupaten_kota_id',
        'month',
        'year',
        'economic_score',
        'tourism_score',
        'social_score',
        'total_score',
        'economic_trend',
        'tourism_trend',
        'social_trend',
        'total_trend',
        'calculation_details',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'economic_score' => 'decimal:2',
        'tourism_score' => 'decimal:2',
        'social_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'economic_trend' => 'decimal:2',
        'tourism_trend' => 'decimal:2',
        'social_trend' => 'decimal:2',
        'total_trend' => 'decimal:2',
        'calculation_details' => 'json',
    ];

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }

    public function scopeForPeriod($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('year', 'desc')->orderBy('month', 'desc');
    }

    public function scopeRankingByTotalScore($query)
    {
        return $query->orderBy('total_score', 'desc');
    }

    // Get previous month result for trend calculation
    public function getPreviousMonthResult()
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;
        
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear = $this->year - 1;
        }

        return static::where('kabupaten_kota_id', $this->kabupaten_kota_id)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->first();
    }

    // Helper methods for trend status
    public function getEconomicTrendStatusAttribute()
    {
        if ($this->economic_trend === null) return 'stable';
        return $this->economic_trend > 0 ? 'up' : ($this->economic_trend < 0 ? 'down' : 'stable');
    }

    public function getTourismTrendStatusAttribute()
    {
        if ($this->tourism_trend === null) return 'stable';
        return $this->tourism_trend > 0 ? 'up' : ($this->tourism_trend < 0 ? 'down' : 'stable');
    }

    public function getSocialTrendStatusAttribute()
    {
        if ($this->social_trend === null) return 'stable';
        return $this->social_trend > 0 ? 'up' : ($this->social_trend < 0 ? 'down' : 'stable');
    }

    public function getTotalTrendStatusAttribute()
    {
        if ($this->total_trend === null) return 'stable';
        return $this->total_trend > 0 ? 'up' : ($this->total_trend < 0 ? 'down' : 'stable');
    }
}
