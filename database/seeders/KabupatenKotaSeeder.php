<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Provinsi;

class KabupatenKotaSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('data/kabupaten_kota');
        $files = glob($dir . '/*.json');
        $kabupatenKota = [];

        // Mapping kode provinsi ke nama provinsi (dari JSON)
        $provinsiJson = file_get_contents(database_path('data/provinsi/provinsi.json'));
        $provinsiData = json_decode($provinsiJson, true);

        foreach ($files as $file) {
            $kode_provinsi = preg_replace('/[^0-9]/', '', basename($file, '.json'));

            // Cari nama provinsi berdasarkan kode
            $namaProvinsi = $provinsiData[$kode_provinsi] ?? null;
            if (!$namaProvinsi) continue;

            // Cari provinsi berdasarkan nama
            $provinsi = Provinsi::where('nama', $namaProvinsi)->first();
            if (!$provinsi) continue;

            $json = file_get_contents($file);
            $data = json_decode($json, true);

            foreach ($data as $kode => $nama) {
                $kabupatenKota[] = [
                    'provinsi_id' => $provinsi->id,
                    'nama' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('kabupaten_kota')->insert($kabupatenKota);
    }
}
