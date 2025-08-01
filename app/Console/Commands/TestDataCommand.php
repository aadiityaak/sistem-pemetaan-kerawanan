<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MonitoringData;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Provinsi;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;

class TestDataCommand extends Command
{
    protected $signature = 'test:data {count=10}';
    protected $description = 'Generate test monitoring data';

    public function handle()
    {
        $count = $this->argument('count');

        $this->info("Generating {$count} test monitoring data...");

        $categories = Category::all();
        $provinsis = Provinsi::all();

        if ($categories->isEmpty() || $provinsis->isEmpty()) {
            $this->error('No categories or provinsi found. Please run seeders first.');
            return;
        }

        for ($i = 1; $i <= $count; $i++) {
            $category = $categories->random();
            $subCategories = SubCategory::where('category_id', $category->id)->get();

            if ($subCategories->isEmpty()) {
                continue;
            }

            $subCategory = $subCategories->random();
            $provinsi = $provinsis->random();

            // Get random kabupaten/kota from selected provinsi
            $kabupatenKotas = KabupatenKota::where('provinsi_id', $provinsi->id)->get();
            $kabupatenKota = $kabupatenKotas->isNotEmpty() ? $kabupatenKotas->random() : null;

            // Get random kecamatan from selected kabupaten/kota
            $kecamatan = null;
            if ($kabupatenKota) {
                $kecamatans = Kecamatan::where('kabupaten_kota_id', $kabupatenKota->id)->get();
                $kecamatan = $kecamatans->isNotEmpty() ? $kecamatans->random() : null;
            }

            MonitoringData::create([
                'category_id' => $category->id,
                'sub_category_id' => $subCategory->id,
                'provinsi_id' => $provinsi->id,
                'kabupaten_kota_id' => $kabupatenKota?->id,
                'kecamatan_id' => $kecamatan?->id,
                'title' => "Test Data {$i} - {$subCategory->name}",
                'description' => "Deskripsi test untuk monitoring {$subCategory->name} di {$provinsi->nama}",
                'latitude' => rand(-1000, 600) / 100, // Random lat untuk Indonesia
                'longitude' => rand(9500, 14100) / 100, // Random lng untuk Indonesia  
                'severity_level' => collect(['low', 'medium', 'high', 'critical'])->random(),
                'status' => collect(['active', 'resolved', 'monitoring', 'archived'])->random(),
                'source' => collect(['Media', 'Laporan Masyarakat', 'Instansi', 'Social Media'])->random(),
                'verified_at' => rand(0, 1) ? now() : null,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            $this->info("Created: {$subCategory->name} ({$subCategory->icon}) in {$category->name}");
        }

        $this->info("Successfully generated {$count} test monitoring data!");
    }
}
