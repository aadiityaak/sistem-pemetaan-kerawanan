<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'data_source',
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

    protected static function booted(): void
    {
        static::deleting(function (self $monitoringData) {
            $paths = [];

            $gallery = $monitoringData->gallery;
            if (is_string($gallery)) {
                $decoded = json_decode($gallery, true);
                if (is_array($decoded)) {
                    $gallery = $decoded;
                }
            }

            if (is_array($gallery)) {
                foreach ($gallery as $item) {
                    if (is_string($item)) {
                        $paths[] = $item;
                        continue;
                    }
                    if (is_array($item) && isset($item['path']) && is_string($item['path'])) {
                        $paths[] = $item['path'];
                    }
                }
            }

            if (is_string($monitoringData->video_path) && $monitoringData->video_path !== '') {
                $paths[] = $monitoringData->video_path;
            }

            $normalized = [];
            foreach ($paths as $path) {
                $normalizedPath = self::normalizePublicPath($path);
                if ($normalizedPath === null) {
                    continue;
                }
                if (!str_starts_with($normalizedPath, 'monitoring-data/')) {
                    continue;
                }
                $normalized[$normalizedPath] = true;
            }

            foreach (array_keys($normalized) as $path) {
                Storage::disk('public')->delete($path);
            }
        });
    }

    private static function normalizePublicPath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        if (preg_match('~^https?://~i', $path) === 1) {
            $parsed = parse_url($path);
            if (isset($parsed['path']) && is_string($parsed['path'])) {
                $path = $parsed['path'];
            }
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if (str_contains($path, '..')) {
            return null;
        }

        return $path !== '' ? $path : null;
    }

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
