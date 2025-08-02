<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    /**
     * Handle file upload for settings
     */
    public function handleFileUpload(UploadedFile $file, string $type): string
    {
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('settings', $filename, 'public');

        return '/storage/'.$path;
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
        Log::info('SettingsService createOrUpdateSetting called', [
            'data' => $data,
            'has_file' => $file !== null,
            'setting_exists' => $setting !== null,
            'setting_id' => $setting?->id,
        ]);

        // Handle file upload for image/file types
        if ($file && in_array($data['type'], ['image', 'file'])) {
            Log::info('Processing file upload', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'data_type' => $data['type'],
            ]);

            // Delete old file if updating
            if ($setting) {
                Log::info('Deleting old file', ['old_value' => $setting->value]);
                $this->deleteOldFile($setting->value);
            }

            $data['value'] = $this->handleFileUpload($file, $data['type']);
            Log::info('File uploaded', ['new_path' => $data['value']]);
        }

        if ($setting) {
            Log::info('Updating existing setting', [
                'setting_id' => $setting->id,
                'old_value' => $setting->value,
                'new_data' => $data,
            ]);

            $setting->update($data);

            // Clear cache when updating settings that affect views
            if (in_array($setting->key, ['app_favicon', 'app_name', 'app_logo'])) {
                Log::info('Clearing cache for important setting', ['key' => $setting->key]);
                AppSetting::clearCache();
            }

            Log::info('Setting updated successfully', [
                'setting_id' => $setting->id,
                'final_value' => $setting->fresh()->value,
            ]);

            return $setting;
        } else {
            Log::info('Creating new setting', ['data' => $data]);

            $createdSetting = AppSetting::create($data);

            // Clear cache when creating settings that affect views
            if (in_array($createdSetting->key, ['app_favicon', 'app_name', 'app_logo'])) {
                Log::info('Clearing cache for new important setting', ['key' => $createdSetting->key]);
                AppSetting::clearCache();
            }

            Log::info('Setting created successfully', [
                'setting_id' => $createdSetting->id,
                'final_value' => $createdSetting->value,
            ]);

            return $createdSetting;
        }
    }

    /**
     * Get setting value with default fallback
     */
    public function getSetting(string $key, string $default = ''): string
    {
        $value = AppSetting::get($key, $default);

        return $value ?? $default;
    }
}
