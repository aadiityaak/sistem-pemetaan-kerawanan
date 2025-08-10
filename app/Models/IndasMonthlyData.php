<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndasMonthlyData extends Model
{
    protected $fillable = [
        'kabupaten_kota_id',
        'indicator_type_id',
        'value',
        'month',
        'year',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }

    public function indicatorType(): BelongsTo
    {
        return $this->belongsTo(IndasIndicatorType::class, 'indicator_type_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForPeriod($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeForKabupatenKota($query, $kabupatenKotaId)
    {
        return $query->where('kabupaten_kota_id', $kabupatenKotaId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->whereHas('indicatorType', function ($q) use ($category) {
            $q->where('category', $category);
        });
    }

    // Get previous month data for trend calculation
    public function getPreviousMonthData()
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;
        
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear = $this->year - 1;
        }

        return static::where('kabupaten_kota_id', $this->kabupaten_kota_id)
            ->where('indicator_type_id', $this->indicator_type_id)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->first();
    }
}
