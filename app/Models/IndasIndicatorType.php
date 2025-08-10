<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndasIndicatorType extends Model
{
    protected $fillable = [
        'name',
        'category',
        'unit',
        'weight_factor',
        'description',
        'is_active',
    ];

    protected $casts = [
        'weight_factor' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function monthlyData(): HasMany
    {
        return $this->hasMany(IndasMonthlyData::class, 'indicator_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Get category-specific indicators
    public static function getEconomicIndicators()
    {
        return static::active()->byCategory('ekonomi')->get();
    }

    public static function getTourismIndicators()
    {
        return static::active()->byCategory('pariwisata')->get();
    }

    public static function getSocialIndicators()
    {
        return static::active()->byCategory('sosial')->get();
    }
}
