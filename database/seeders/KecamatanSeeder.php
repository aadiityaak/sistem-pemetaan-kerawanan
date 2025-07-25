<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Provinsi;
use App\Models\KabupatenKota;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('data/kecamatan');
        $files = glob($dir . '/*.json');
        $kecamatan = [];

        // Load mapping data
        $provinsiJson = file_get_contents(database_path('data/provinsi/provinsi.json'));
        $provinsiData = json_decode($provinsiJson, true);

        foreach ($files as $file) {
            // Format file: kec-<kode_provinsi>-<kode_kabupaten_kota>.json
            if (preg_match('/kec-(\d+)-(\d+)\.json$/', basename($file), $m)) {
                $kode_provinsi = $m[1];
                $kode_kabupaten_kota = $m[2];

                // Cari nama provinsi berdasarkan kode
                $namaProvinsi = $provinsiData[$kode_provinsi] ?? null;
                if (!$namaProvinsi) continue;

                // Cari provinsi berdasarkan nama
                $provinsi = Provinsi::where('nama', $namaProvinsi)->first();
                if (!$provinsi) continue;

                // Load kabupaten data untuk mencari nama kabupaten
                $kabupatenFile = database_path("data/kabupaten_kota/kab-{$kode_provinsi}.json");
                if (!file_exists($kabupatenFile)) continue;

                $kabupatenJson = file_get_contents($kabupatenFile);
                $kabupatenData = json_decode($kabupatenJson, true);
                $namaKabupaten = $kabupatenData[$kode_kabupaten_kota] ?? null;
                if (!$namaKabupaten) continue;

                // Cari kabupaten berdasarkan nama dan provinsi_id
                $kabupatenKota = KabupatenKota::where('provinsi_id', $provinsi->id)
                    ->where('nama', $namaKabupaten)->first();

                if (!$kabupatenKota) continue;

                $json = file_get_contents($file);
                $data = json_decode($json, true);

                foreach ($data as $kode => $nama) {
                    $kecamatan[] = [
                        'kabupaten_kota_id' => $kabupatenKota->id,
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
