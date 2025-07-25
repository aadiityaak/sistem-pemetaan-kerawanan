<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KabupatenKotaSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('data/kabupaten_kota');
        $files = glob($dir . '/*.json');
        $kabupatenKota = [];
        foreach ($files as $file) {
            $kode_provinsi = preg_replace('/[^0-9]/', '', basename($file, '.json'));
            $json = file_get_contents($file);
            $data = json_decode($json, true);
            foreach ($data as $kode => $nama) {
                $kabupatenKota[] = [
                    'kode_provinsi' => $kode_provinsi,
                    'kode' => $kode,
                    'nama' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('kabupaten_kota')->insert($kabupatenKota);
    }
} 