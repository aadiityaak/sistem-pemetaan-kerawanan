<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
  /**
   * Handle file upload for settings
   */
  public function handleFileUpload(UploadedFile $file, string $type): string
  {
    $filename = time() . '_' . $file->getClientOriginalName();
    $path = $file->storeAs('settings', $filename, 'public');

    return '/storage/' . $path;
  }

  /**
   * Delete old file if exists
   */
  public function deleteOldFile(?string $value): void
  {
    if ($value && str_starts_with($value, '/storage/')) {
      $oldPath = str_replace('/storage/', '', $value);
      Storage::disk('public')->delete($oldPath);
    }
  }

  /**
   * Create or update setting with file handling
   */
  public function createOrUpdateSetting(array $data, ?UploadedFile $file = null, ?AppSetting $setting = null): AppSetting
  {
    // Handle file upload for image/file types
    if ($file && in_array($data['type'], ['image', 'file'])) {
      // Delete old file if updating
      if ($setting) {
        $this->deleteOldFile($setting->value);
      }

      $data['value'] = $this->handleFileUpload($file, $data['type']);
    }

    if ($setting) {
      $setting->update($data);
      return $setting;
    } else {
      return AppSetting::create($data);
    }
  }

  /**
   * Get all settings grouped by group
   */
  public function getAllSettingsGrouped()
  {
    return AppSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
  }

  /**
   * Update multiple settings at once
   */
  public function updateBatch(array $settings): void
  {
    foreach ($settings as $key => $value) {
      AppSetting::where('key', $key)->update(['value' => $value]);
    }

    // Clear all cache after batch update
    AppSetting::clearCache();
  }
}
