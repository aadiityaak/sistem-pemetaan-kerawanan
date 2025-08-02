<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
  use HasFactory;

  protected $fillable = [
    'key',
    'value',
    'type',
    'group',
    'label',
    'description'
  ];

  /**
   * Get setting value by key with improved caching for Laravel 12
   */
  public static function get($key, $default = null)
  {
    return Cache::remember("app_setting_{$key}", now()->addHours(24), function () use ($key, $default) {
      $setting = static::where('key', $key)->first();
      $value = $setting ? $setting->value : $default;
      // Ensure we return the default if value is null
      return $value ?? $default;
    });
  }
  /**
   * Set setting value by key
   */
  public static function set($key, $value, $type = 'text', $group = 'general', $label = null, $description = null)
  {
    $setting = static::updateOrCreate(
      ['key' => $key],
      [
        'value' => $value,
        'type' => $type,
        'group' => $group,
        'label' => $label ?: $key,
        'description' => $description
      ]
    );

    Cache::forget("app_setting_{$key}");

    return $setting;
  }

  /**
   * Get settings by group
   */
  public static function getByGroup($group)
  {
    return static::where('group', $group)->get()->pluck('value', 'key');
  }

  /**
   * Clear all settings cache
   */
  public static function clearCache()
  {
    $keys = static::pluck('key');
    foreach ($keys as $key) {
      Cache::forget("app_setting_{$key}");
    }
  }

  /**
   * Boot method to clear cache on save/delete
   */
  protected static function boot()
  {
    parent::boot();

    static::saved(function ($setting) {
      Cache::forget("app_setting_{$setting->key}");
    });

    static::deleted(function ($setting) {
      Cache::forget("app_setting_{$setting->key}");
    });
  }
}
