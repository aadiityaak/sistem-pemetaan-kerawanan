<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringData extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provinsi_id',
        'kabupaten_kota_id',
        'kecamatan_id',
        'category_id',
        'sub_category_id',
        'latitude',
        'longitude',
        'title',
        'description',
        'sumber_berita',
        'jumlah_terdampak',
        'additional_data',
        'severity_level',
        'status',
        'incident_date',
        'source',
        'gallery',
        'video_path',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'additional_data' => 'array',
        'gallery' => 'array',
        'incident_date' => 'datetime',
    ];

    /**
     * Get the provinsi
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    /**
     * Get the kabupaten/kota
     */
    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class);
    }

    /**
     * Get the kecamatan
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the sub category
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Get the user who created this monitoring data
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for specific category
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for specific sub category
     */
    public function scopeForSubCategory($query, $subCategoryId)
    {
        return $query->where('sub_category_id', $subCategoryId);
    }

    /**
     * Scope for active status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific severity level
     */
    public function scopeSeverity($query, $level)
    {
        return $query->where('severity_level', $level);
    }
}
