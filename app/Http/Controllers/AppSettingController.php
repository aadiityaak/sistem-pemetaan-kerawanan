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

    private function getAiSettings(): array
    {
        return [
            [
                'key' => 'ai_provider',
                'label' => 'AI Provider',
                'description' => 'Pilih provider AI untuk analisis data',
                'type' => 'select',
                'options' => [
                    ['value' => 'gemini', 'label' => 'Gemini (Google)'],
                    ['value' => 'openai', 'label' => 'ChatGPT (OpenAI)'],
                ],
                'value' => $this->settingsService->getSetting('ai_provider', 'gemini'),
            ],
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
                'value' => $this->settingsService->getSetting(
                    'gemini_api_endpoint',
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent'
                ),
            ],
            [
                'key' => 'gemini_api_key',
                'label' => 'Gemini API Key',
                'description' => 'API Key untuk autentikasi Google Gemini',
                'type' => 'password',
                'value' => $this->settingsService->getSetting('gemini_api_key', '') !== '' ? '********' : '',
            ],
            [
                'key' => 'openai_enabled',
                'label' => 'Aktifkan OpenAI (ChatGPT)',
                'description' => 'Enable/disable fitur OpenAI untuk analisis data',
                'type' => 'boolean',
                'value' => $this->settingsService->getSetting('openai_enabled', 'false'),
            ],
            [
                'key' => 'openai_api_base_url',
                'label' => 'OpenAI Base URL',
                'description' => 'Base URL untuk OpenAI API (default: https://api.openai.com)',
                'type' => 'text',
                'value' => $this->settingsService->getSetting('openai_api_base_url', 'https://api.openai.com'),
            ],
            [
                'key' => 'openai_model',
                'label' => 'OpenAI Model',
                'description' => 'Model OpenAI untuk analisis (mis. gpt-4o-mini)',
                'type' => 'text',
                'value' => $this->settingsService->getSetting('openai_model', 'gpt-4o-mini'),
            ],
            [
                'key' => 'openai_api_key',
                'label' => 'OpenAI API Key',
                'description' => 'API Key untuk autentikasi OpenAI',
                'type' => 'password',
                'value' => $this->settingsService->getSetting('openai_api_key', '') !== '' ? '********' : '',
            ],
        ];
    }

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
                [
                    'key' => 'monitoring_video_enabled',
                    'label' => 'Fitur Video Monitoring',
                    'description' => 'Aktifkan/nonaktifkan upload dan tampilan video pada data monitoring',
                    'type' => 'boolean',
                    'value' => $this->settingsService->getSetting('monitoring_video_enabled', 'false'),
                ],
            ],
            'license' => [
                [
                    'key' => 'license_key',
                    'label' => 'API Key / License Key',
                    'description' => 'Masukkan API Key yang valid untuk akses data eksternal.',
                    'type' => 'password',
                    'value' => $this->settingsService->getSetting('license_key', '') !== '' ? '********' : '',
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
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $fixedSettings,
        ]);
    }

    public function ai()
    {
        return Inertia::render('Settings/Ai', [
            'settings' => [
                'ai' => $this->getAiSettings(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $key)
    {
        $maskedKeys = ['gemini_api_key', 'openai_api_key', 'license_key'];
        $safeRequestData = $request->all();
        if (in_array($key, $maskedKeys, true) && array_key_exists('value', $safeRequestData)) {
            $safeRequestData['value'] = $safeRequestData['value'] ? '***' : '';
        }

        Log::info('AppSettingController update called', [
            'key' => $key,
            'request_data' => $safeRequestData,
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
            'monitoring_video_enabled',
            'ai_provider',
            'gemini_enabled',
            'gemini_api_endpoint',
            'gemini_api_key',
            'openai_enabled',
            'openai_api_base_url',
            'openai_model',
            'openai_api_key',
            'license_key',
            'license_expires_at',
        ];

        if (! in_array($key, $allowedKeys)) {
            Log::warning('Invalid setting key attempted', ['key' => $key]);

            return redirect()->back()->with('error', 'Setting tidak valid');
        }

        $rules = ['value' => 'nullable|string'];
        if ($key === 'ai_provider') {
            $rules['value'] = 'required|string|in:gemini,openai';
        }
        if ($key === 'openai_api_base_url') {
            $rules['value'] = 'required|string';
        }

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
            'monitoring_video_enabled' => 'Fitur Video Monitoring',
            'ai_provider' => 'AI Provider',
            'gemini_enabled' => 'Aktifkan Gemini AI',
            'gemini_api_endpoint' => 'Gemini API Endpoint',
            'gemini_api_key' => 'Gemini API Key',
            'openai_enabled' => 'Aktifkan OpenAI (ChatGPT)',
            'openai_api_base_url' => 'OpenAI Base URL',
            'openai_model' => 'OpenAI Model',
            'openai_api_key' => 'OpenAI API Key',
            'license_key' => 'API Key / License Key',
            'license_expires_at' => 'Tanggal Expired Lisensi',
        ];

        // Determine setting type
        $settingType = 'text';
        if (in_array($key, ['app_favicon', 'app_logo', 'login_logo'])) {
            $settingType = 'image';
        } elseif (in_array($key, ['gemini_enabled', 'monitoring_video_enabled', 'openai_enabled'])) {
            $settingType = 'boolean';
        } elseif (in_array($key, ['gemini_api_key', 'openai_api_key', 'license_key'])) {
            $settingType = 'password';
        }

        // Determine setting group
        $settingGroup = 'general';
        if (in_array($key, ['app_favicon', 'app_logo', 'login_logo'])) {
            $settingGroup = 'appearance';
        } elseif (in_array($key, ['ai_provider', 'gemini_enabled', 'gemini_api_endpoint', 'gemini_api_key', 'openai_enabled', 'openai_api_base_url', 'openai_model', 'openai_api_key'])) {
            $settingGroup = 'ai';
        } elseif ($key === 'license_expires_at' || $key === 'license_key') {
            $settingGroup = 'license';
        }

        $setting = AppSetting::where('key', $key)->first();

        $value = $request->get('value');
        if (is_string($value)) {
            $value = trim($value);
        }

        if (in_array($key, ['gemini_api_key', 'openai_api_key', 'license_key'], true)) {
            if ($value === null || $value === '' || $value === '********') {
                $value = $setting?->value;
            }
        }

        if ($key === 'license_key') {
            $token = (string) ($value ?? '');
            $envKey = config('app.license_key') ?: env('KEY_API');

            // Simpan license_key asli (sesuai permintaan user)
            // Namun tetap perbarui license_expires_at jika cocok dengan KEY_API
            if ($token !== '' && $envKey && $token === $envKey) {
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

        // Find existing setting or create new
        $updatedSetting = $this->settingsService->createOrUpdateSetting($data, $file, $setting);

        Log::info('Setting updated', ['key' => $key]);

        return redirect()->back()->with('success', 'Setting berhasil diperbarui');
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
