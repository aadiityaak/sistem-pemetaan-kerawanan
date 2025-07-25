<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinsiSeeder extends Seeder
{
    public function run(): void
    {
        $json = file_get_contents(database_path('data/provinsi/provinsi.json'));
        $data = json_decode($json, true);
        $provinsi = [];
        foreach ($data as $kode => $nama) {
            $provinsi[] = [
                'nama' => $nama,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('provinsi')->insert($provinsi);
    }
}
