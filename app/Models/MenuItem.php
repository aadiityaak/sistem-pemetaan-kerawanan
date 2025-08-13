<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    protected $fillable = [
        'title',
        'icon',
        'path',
        'is_active',
        'sort_order',
        'parent_id',
        'admin_only',
        'permissions',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'admin_only' => 'boolean',
        'permissions' => 'array',
    ];

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootItems($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForUser($query, $user)
    {
        $query->where('is_active', true);
        
        // If not admin, exclude admin-only items
        if (!$user || $user->role !== 'admin') {
            $query->where('admin_only', false);
        }
        
        return $query;
    }

    public static function getMenuTree($user = null)
    {
        return self::rootItems()
            ->forUser($user)
            ->with(['children' => function ($query) use ($user) {
                $query->forUser($user);
            }])
            ->orderBy('sort_order')
            ->get();
    }
}
