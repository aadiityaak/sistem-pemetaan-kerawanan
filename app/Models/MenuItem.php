<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function allChildren(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
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

        // If user is not authenticated, only show items with no specific permissions
        if (! $user) {
            $query->where(function($q) {
                $q->whereNull('permissions')
                  ->orWhereJsonLength('permissions', 0);
            });
            return $query;
        }

        // If permissions field is populated, check against user's role
        $query->where(function($q) use ($user) {
            $q->where(function($subQ) use ($user) {
                // Items with specific permissions - check if user role is included
                $subQ->whereNotNull('permissions')
                     ->whereJsonLength('permissions', '>', 0)
                     ->whereJsonContains('permissions', $user->role);
            })->orWhere(function($subQ) {
                // Items with no specific permissions (available to all)
                $subQ->where(function($innerQ) {
                    $innerQ->whereNull('permissions')
                           ->orWhereJsonLength('permissions', 0);
                });
            });
        });

        // Fallback: also respect the old admin_only field for backward compatibility
        if (! in_array($user->role, ['super_admin', 'admin_vip', 'admin'])) {
            $query->where('admin_only', false);
        }

        return $query;
    }

    public static function getMenuTree($user = null)
    {
        // Get all active menu items that user can access
        $allMenuItems = self::forUser($user)->orderBy('sort_order')->get();

        // Build hierarchy recursively for unlimited depth
        $buildHierarchy = function ($items, $allItems, $parentId = null) use (&$buildHierarchy) {
            return $items->filter(function ($item) use ($parentId) {
                return $item->parent_id === $parentId;
            })->map(function ($item) use ($allItems, $buildHierarchy) {
                // Get children recursively
                $children = $buildHierarchy($allItems, $allItems, $item->id);
                if ($children->count() > 0) {
                    $item->children = $children->values(); // Ensure children are indexed arrays
                }

                return $item;
            });
        };

        // Return root items with their full hierarchy as values (not keys)
        return $buildHierarchy($allMenuItems, $allMenuItems)->values();
    }
}
