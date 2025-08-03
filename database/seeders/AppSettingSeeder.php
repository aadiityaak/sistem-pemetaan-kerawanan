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
        'key' => 'site_name',
        'value' => 'Crime Map',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Site Name',
        'description' => 'The name of the website displayed in the header and title',
      ],
      [
        'key' => 'site_description',
        'value' => 'Platform pemetaan data kriminalitas untuk analisis dan monitoring keamanan wilayah',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Site Description',
        'description' => 'Brief description of the website',
      ],
      [
        'key' => 'contact_email',
        'value' => 'admin@crimemap.com',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Contact Email',
        'description' => 'Main contact email for the site',
      ],
      [
        'key' => 'maintenance_mode',
        'value' => 'false',
        'type' => 'boolean',
        'group' => 'general',
        'label' => 'Maintenance Mode',
        'description' => 'Enable/disable maintenance mode',
      ],

      // Appearance Settings
      [
        'key' => 'primary_color',
        'value' => '#3b82f6',
        'type' => 'text',
        'group' => 'appearance',
        'label' => 'Primary Color',
        'description' => 'Main theme color used throughout the site',
      ],
      [
        'key' => 'logo_url',
        'value' => null,
        'type' => 'image',
        'group' => 'appearance',
        'label' => 'Site Logo',
        'description' => 'Logo displayed in the header',
      ],
      [
        'key' => 'favicon_url',
        'value' => null,
        'type' => 'image',
        'group' => 'appearance',
        'label' => 'Favicon',
        'description' => 'Small icon displayed in browser tab',
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
    ];

    foreach ($settings as $setting) {
      AppSetting::updateOrCreate(
        ['key' => $setting['key']],
        $setting
      );
    }
  }
}
