<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndasRegion extends Model
{
    protected $fillable = [
        'kabupaten_kota_id',
        'nama',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }

    public function monthlyData(): HasMany
    {
        return $this->hasMany(IndasMonthlyData::class, 'region_id');
    }

    public function analysisResults(): HasMany
    {
        return $this->hasMany(IndasAnalysisResult::class, 'region_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get provinsi through kabupaten_kota relationship
    public function getProvinsiAttribute()
    {
        return $this->kabupatenKota?->provinsi;
    }
}
