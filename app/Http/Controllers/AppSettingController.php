<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AppSettingController extends Controller
{
    public function __construct(
        private SettingsService $settingsService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fixed settings configuration
        $fixedSettings = [
            'general' => [
                [
                    'key' => 'app_name',
                    'label' => 'Nama Aplikasi',
                    'description' => 'Nama aplikasi yang akan ditampilkan di header dan title',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('app_name', 'Peta Kriminal Indonesia'),
                ],
                [
                    'key' => 'app_description',
                    'label' => 'Deskripsi Aplikasi',
                    'description' => 'Deskripsi singkat tentang aplikasi',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('app_description', 'Sistem Informasi Pemetaan Data Kriminal Indonesia'),
                ],
                [
                    'key' => 'footer_text',
                    'label' => 'Teks Footer',
                    'description' => 'Teks yang ditampilkan di footer aplikasi',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('footer_text', 'Peta Kriminal Indonesia © 2024'),
                ],
            ],
            'appearance' => [
                [
                    'key' => 'app_favicon',
                    'label' => 'Favicon',
                    'description' => 'Icon yang ditampilkan di tab browser',
                    'type' => 'image',
                    'value' => $this->settingsService->getSetting('app_favicon', '/favicon.ico'),
                ],
                [
                    'key' => 'app_logo',
                    'label' => 'Logo Aplikasi',
                    'description' => 'Logo yang ditampilkan di header aplikasi',
                    'type' => 'image',
                    'value' => $this->settingsService->getSetting('app_logo', ''),
                ],
            ],
            'ai' => [
                [
                    'key' => 'gemini_enabled',
                    'label' => 'Aktifkan Gemini AI',
                    'description' => 'Enable/disable fitur Gemini AI untuk analisis data',
                    'type' => 'boolean',
                    'value' => $this->settingsService->getSetting('gemini_enabled', 'false'),
                ],
                [
                    'key' => 'gemini_api_endpoint',
                    'label' => 'Gemini API Endpoint',
                    'description' => 'URL endpoint Google Gemini API',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('gemini_api_endpoint', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent'),
                ],
                [
                    'key' => 'gemini_api_key',
                    'label' => 'Gemini API Key',
                    'description' => 'API Key untuk autentikasi Google Gemini',
                    'type' => 'password',
                    'value' => $this->settingsService->getSetting('gemini_api_key', ''),
                ],
            ],
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $fixedSettings,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $key)
    {
        Log::info('AppSettingController update called', [
            'key' => $key,
            'request_data' => $request->all(),
            'has_file' => $request->hasFile('file'),
            'file_info' => $request->hasFile('file') ? [
                'name' => $request->file('file')->getClientOriginalName(),
                'size' => $request->file('file')->getSize(),
                'mime' => $request->file('file')->getMimeType(),
            ] : null,
        ]);

        // Validate input based on setting type
        $allowedKeys = [
            'app_name', 'app_description', 'footer_text', 'app_favicon', 'app_logo',
            'gemini_enabled', 'gemini_api_endpoint', 'gemini_api_key'
        ];

        if (! in_array($key, $allowedKeys)) {
            Log::warning('Invalid setting key attempted', ['key' => $key]);

            return redirect()->route('settings.index')->with('error', 'Setting tidak valid');
        }

        $rules = ['value' => 'nullable|string'];

        // Add file validation for image types
        if (in_array($key, ['app_favicon', 'app_logo'])) {
            $rules['file'] = 'nullable|file|max:2048|mimes:jpg,jpeg,png,gif,ico,svg';
        }

        $request->validate($rules);

        // Get setting configuration with proper labels
        $settingLabels = [
            'app_name' => 'Nama Aplikasi',
            'app_description' => 'Deskripsi Aplikasi',
            'footer_text' => 'Teks Footer',
            'app_favicon' => 'Favicon',
            'app_logo' => 'Logo Aplikasi',
            'gemini_enabled' => 'Aktifkan Gemini AI',
            'gemini_api_endpoint' => 'Gemini API Endpoint',
            'gemini_api_key' => 'Gemini API Key',
        ];

        // Determine setting type
        $settingType = 'text';
        if (in_array($key, ['app_favicon', 'app_logo'])) {
            $settingType = 'image';
        } elseif ($key === 'gemini_enabled') {
            $settingType = 'boolean';
        } elseif ($key === 'gemini_api_key') {
            $settingType = 'password';
        }

        // Determine setting group
        $settingGroup = 'general';
        if (in_array($key, ['app_favicon', 'app_logo'])) {
            $settingGroup = 'appearance';
        } elseif (in_array($key, ['gemini_enabled', 'gemini_api_endpoint', 'gemini_api_key'])) {
            $settingGroup = 'ai';
        }

        $data = [
            'key' => $key,
            'value' => $request->get('value'),
            'type' => $settingType,
            'label' => $settingLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)),
            'group' => $settingGroup,
            'description' => null,
        ];

        $file = $request->hasFile('file') ? $request->file('file') : null;

        Log::info('Prepared data for update', [
            'data' => $data,
            'file_present' => $file !== null,
        ]);

        // Find existing setting or create new
        $setting = AppSetting::where('key', $key)->first();

        $updatedSetting = $this->settingsService->createOrUpdateSetting($data, $file, $setting);

        Log::info('Setting updated', [
            'key' => $key,
            'old_value' => $setting ? $setting->value : null,
            'new_value' => $updatedSetting->value,
        ]);

        return redirect()->route('settings.index')->with('success', 'Setting berhasil diperbarui');
    }
}
