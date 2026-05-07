<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AppSetting;

class AppSettingSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $settings = [
      // General Settings
      [
        'key' => 'app_favicon',
        'value' => 'fav.png',
        'type' => 'image',
        'group' => 'appearance',
        'label' => 'Favicon',
        'description' => 'Small icon displayed in browser tab',
      ],
      [
        'key' => 'app_logo',
        'value' => 'Logo.webp',
        'type' => 'image',
        'group' => 'appearance',
        'label' => 'Site Logo',
        'description' => 'Logo displayed in the header',
      ],
      [
        'key' => 'app_name',
        'value' => 'Pemetaan Kerawanan',
        'type' => 'text',
        'group' => 'general',
        'label' => 'App Name',
        'description' => 'The name of the website displayed in the header and title',
      ],
      [
        'key' => 'app_description',
        'value' => 'Sistem Pemetaan Kerawanan - Platform pemetaan data kerawanan untuk analisis dan monitoring keamanan wilayah',
        'type' => 'text',
        'group' => 'general',
        'label' => 'App Description',
        'description' => 'Brief description of the website',
      ],
      [
        'key' => 'monitoring_video_enabled',
        'value' => 'false',
        'type' => 'boolean',
        'group' => 'general',
        'label' => 'Fitur Video Monitoring',
        'description' => 'Aktifkan/nonaktifkan upload dan tampilan video pada data monitoring',
      ],

      // Map Settings
      [
        'key' => 'default_map_center_lat',
        'value' => '-6.200000',
        'type' => 'number',
        'group' => 'map',
        'label' => 'Default Map Center Latitude',
        'description' => 'Default latitude for map center (Indonesia)',
      ],
      [
        'key' => 'default_map_center_lng',
        'value' => '106.816666',
        'type' => 'number',
        'group' => 'map',
        'label' => 'Default Map Center Longitude',
        'description' => 'Default longitude for map center (Jakarta)',
      ],
      [
        'key' => 'default_map_zoom',
        'value' => '10',
        'type' => 'number',
        'group' => 'map',
        'label' => 'Default Map Zoom',
        'description' => 'Default zoom level for the map',
      ],

      // Data Settings
      [
        'key' => 'max_data_points_per_request',
        'value' => '1000',
        'type' => 'number',
        'group' => 'data',
        'label' => 'Max Data Points Per Request',
        'description' => 'Maximum number of data points to load in a single request',
      ],
      [
        'key' => 'data_refresh_interval',
        'value' => '300',
        'type' => 'number',
        'group' => 'data',
        'label' => 'Data Refresh Interval (seconds)',
        'description' => 'How often to refresh data on the map (in seconds)',
      ],

      // AI Settings
      [
        'key' => 'ai_provider',
        'value' => 'gemini',
        'type' => 'text',
        'group' => 'ai',
        'label' => 'AI Provider',
        'description' => 'Provider AI yang digunakan untuk analisa (gemini/openai)',
      ],
      [
        'key' => 'gemini_api_endpoint',
        'value' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
        'type' => 'text',
        'group' => 'ai',
        'label' => 'Gemini API Endpoint',
        'description' => 'Google Gemini API endpoint URL for AI processing',
      ],
      [
        'key' => 'gemini_api_key',
        'value' => '',
        'type' => 'password',
        'group' => 'ai',
        'label' => 'Gemini API Key',
        'description' => 'Google Gemini API key for authentication',
      ],
      [
        'key' => 'gemini_enabled',
        'value' => 'false',
        'type' => 'boolean',
        'group' => 'ai',
        'label' => 'Enable Gemini AI',
        'description' => 'Enable/disable Gemini AI features',
      ],
      [
        'key' => 'openai_enabled',
        'value' => 'false',
        'type' => 'boolean',
        'group' => 'ai',
        'label' => 'Enable OpenAI (ChatGPT)',
        'description' => 'Enable/disable OpenAI ChatGPT features',
      ],
      [
        'key' => 'openai_api_base_url',
        'value' => 'https://api.openai.com',
        'type' => 'text',
        'group' => 'ai',
        'label' => 'OpenAI Base URL',
        'description' => 'Base URL untuk OpenAI API',
      ],
      [
        'key' => 'openai_api_key',
        'value' => '',
        'type' => 'password',
        'group' => 'ai',
        'label' => 'OpenAI API Key',
        'description' => 'API Key untuk autentikasi OpenAI',
      ],
      [
        'key' => 'openai_model',
        'value' => 'gpt-4o-mini',
        'type' => 'text',
        'group' => 'ai',
        'label' => 'OpenAI Model',
        'description' => 'Model OpenAI untuk analisa (mis. gpt-4o-mini)',
      ],
    ];

    foreach ($settings as $setting) {
      AppSetting::updateOrCreate(
        ['key' => $setting['key']],
        $setting
      );
    }
  }
}
