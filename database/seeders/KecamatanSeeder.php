<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('data/kecamatan');
        $files = glob($dir . '/*.json');
        $kecamatan = [];
        foreach ($files as $file) {
            // Format file: kec-<kode_provinsi>-<kode_kabupaten_kota>.json
            if (preg_match('/kec-(\d+)-(\d+)\.json$/', basename($file), $m)) {
                $kode_provinsi = $m[1];
                $kode_kabupaten_kota = $m[2];
                $json = file_get_contents($file);
                $data = json_decode($json, true);
                foreach ($data as $kode => $nama) {
                    $kecamatan[] = [
                        'kode_provinsi' => $kode_provinsi,
                        'kode_kabupaten_kota' => $kode_kabupaten_kota,
                        'kode' => $kode,
                        'nama' => $nama,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        foreach (array_chunk($kecamatan, 200) as $chunk) {
          DB::table('kecamatan')->insert($chunk);
        }
    }
} 