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
                    'value' => $this->settingsService->getSetting('app_name', 'Pemetaan Kerawanan'),
                ],
                [
                    'key' => 'app_description',
                    'label' => 'Deskripsi Aplikasi',
                    'description' => 'Deskripsi singkat tentang aplikasi',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('app_description', 'Sistem Informasi Pemetaan Kerawanan Indonesia'),
                ],
                [
                    'key' => 'footer_text',
                    'label' => 'Teks Footer',
                    'description' => 'Teks yang ditampilkan di footer aplikasi',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('footer_text', 'Pemetaan Kerawanan © 2024'),
                ],
            ],
            'license' => [
                [
                    'key' => 'license_key',
                    'label' => 'API Key / License Key',
                    'description' => 'Masukkan API Key yang valid untuk akses data eksternal.',
                    'type' => 'password',
                    'value' => $this->settingsService->getSetting('license_key', ''),
                ],
                [
                    'key' => 'license_expires_at',
                    'label' => 'Masa Berlaku Lisensi',
                    'description' => 'Tanggal kedaluwarsa lisensi (diperbarui otomatis saat API Key valid dimasukkan).',
                    'type' => 'text',
                    'value' => $this->settingsService->getSetting('license_expires_at', '2029-02-19'),
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
                [
                    'key' => 'login_logo',
                    'label' => 'Logo Login',
                    'description' => 'Logo yang ditampilkan di halaman login',
                    'type' => 'image',
                    'value' => $this->settingsService->getSetting('login_logo', ''),
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
                    'value' => $this->settingsService->getSetting('gemini_api_endpoint', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent'),
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
            'app_name',
            'app_description',
            'footer_text',
            'app_favicon',
            'app_logo',
            'login_logo',
            'gemini_enabled',
            'gemini_api_endpoint',
            'gemini_api_key',
            'license_key',
            'license_expires_at',
        ];

        if (! in_array($key, $allowedKeys)) {
            Log::warning('Invalid setting key attempted', ['key' => $key]);

            return redirect()->route('settings.index')->with('error', 'Setting tidak valid');
        }

        $rules = ['value' => 'nullable|string'];

        // Add file validation for image types
        if (in_array($key, ['app_favicon', 'app_logo', 'login_logo'])) {
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
            'login_logo' => 'Logo Login',
            'gemini_enabled' => 'Aktifkan Gemini AI',
            'gemini_api_endpoint' => 'Gemini API Endpoint',
            'gemini_api_key' => 'Gemini API Key',
            'license_key' => 'API Key / License Key',
            'license_expires_at' => 'Tanggal Expired Lisensi',
        ];

        // Determine setting type
        $settingType = 'text';
        if (in_array($key, ['app_favicon', 'app_logo', 'login_logo'])) {
            $settingType = 'image';
        } elseif ($key === 'gemini_enabled') {
            $settingType = 'boolean';
        } elseif ($key === 'gemini_api_key' || $key === 'license_key') {
            $settingType = 'password';
        }

        // Determine setting group
        $settingGroup = 'general';
        if (in_array($key, ['app_favicon', 'app_logo', 'login_logo'])) {
            $settingGroup = 'appearance';
        } elseif (in_array($key, ['gemini_enabled', 'gemini_api_endpoint', 'gemini_api_key'])) {
            $settingGroup = 'ai';
        } elseif ($key === 'license_expires_at' || $key === 'license_key') {
            $settingGroup = 'license';
        }

        $value = $request->get('value');
        if ($key === 'license_key') {
            $token = (string) ($value ?? '');
            $envKey = config('app.license_key') ?: env('KEY_API');

            // Simpan license_key asli (sesuai permintaan user)
            // Namun tetap perbarui license_expires_at jika cocok dengan KEY_API
            if ($envKey && $token === $envKey) {
                $envExpiry = config('app.license_expired_date') ?: env('EXPIRED_DATE');
                $expiryValue = $envExpiry ?: now()->addYears(3)->toDateString();

                AppSetting::set(
                    'license_expires_at',
                    $expiryValue,
                    'text',
                    'license',
                    'Tanggal Expired Lisensi'
                );

                AppSetting::set(
                    'license_key_hash',
                    hash('sha256', $envKey),
                    'text',
                    'license',
                    'License Key Hash'
                );
            }
        } elseif ($key === 'license_expires_at') {
            // Biarkan admin mengedit manual jika perlu, tapi biasanya otomatis
            $token = (string) ($value ?? '');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $token) && !empty($token)) {
                return redirect()->route('settings.index')->with('error', 'Format tanggal harus YYYY-MM-DD');
            }
        }

        $data = [
            'key' => $key,
            'value' => $value,
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

    public function getManifest()
    {
        $appName = $this->settingsService->getSetting('app_name', config('app.name', 'Pemetaan Kerawanan'));
        $appDescription = $this->settingsService->getSetting('app_description', 'Aplikasi Pemetaan Kerawanan');
        $favicon = $this->settingsService->getSetting('app_favicon', '/favicon.ico');

        $manifest = [
            'id' => '/',
            'name' => $appName,
            'short_name' => str_replace(' ', '', $appName),
            'description' => $appDescription,
            'theme_color' => '#3b82f6',
            'background_color' => '#000000',
            'display' => 'standalone',
            'start_url' => '/',
            'scope' => '/',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => '/img/icons/pwa-192x192.svg',
                    'sizes' => '192x192',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any'
                ],
                [
                    'src' => '/img/icons/pwa-512x512.svg',
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any'
                ],
                [
                    'src' => '/img/icons/pwa-192x192.svg',
                    'sizes' => '192x192',
                    'type' => 'image/svg+xml',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => '/img/icons/pwa-512x512.svg',
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'maskable'
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    public function getServiceWorker()
    {
        $swPath = public_path('build/sw.js');

        if (file_exists(public_path('hot'))) {
            $hotUrl = rtrim(file_get_contents(public_path('hot')), " \t\n\r\0\x0B/");
            try {
                $swContent = file_get_contents($hotUrl . '/sw.js');
                return response($swContent)->header('Content-Type', 'application/javascript');
            } catch (\Exception $e) {
                // Fallback if dev server is not serving sw.js
            }
        }

        if (file_exists($swPath)) {
            return response()->file($swPath, ['Content-Type' => 'application/javascript']);
        }

        return abort(404);
    }
}
