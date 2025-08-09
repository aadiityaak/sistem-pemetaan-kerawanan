<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'icon',
        'image_path',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all monitoring data for this sub category
     */
    public function monitoringData(): HasMany
    {
        return $this->hasMany(MonitoringData::class);
    }

    /**
     * Scope for active sub categories only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered sub categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the effective color (from sub category or parent category)
     */
    public function getEffectiveColorAttribute()
    {
        return $this->color ?? $this->category->color ?? '#6B7280';
    }

    /**
     * Get the subcategory image URL or return null
     */
    public function getImageUrlAttribute()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * Get the effective icon (image, emoji icon, or parent category icon)
     */
    public function getEffectiveIconAttribute()
    {
        if ($this->image_path) {
            return $this->getImageUrlAttribute();
        }
        
        return $this->icon ?? $this->category->effective_icon ?? $this->category->icon;
    }

    /**
     * Check if subcategory has custom image
     */
    public function hasCustomImage()
    {
        return !empty($this->image_path);
    }
}
