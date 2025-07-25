<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CrimeData;
use App\Models\Provinsi;
use App\Models\KabupatenKota;
use App\Models\Kecamatan;

class CrimeDataSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Data sample kriminal dengan koordinat realistis di berbagai daerah Indonesia
    $crimeDataSample = [
      // Jakarta
      [
        'provinsi' => 'DKI JAKARTA',
        'kabupaten_kota' => 'KOTA JAKARTA PUSAT',
        'kecamatan' => 'MENTENG',
        'jenis_kriminal' => 'pencurian',
        'deskripsi' => 'Pencurian tas di area CFD Bundaran HI',
        'latitude' => -6.1944,
        'longitude' => 106.8229,
      ],
      [
        'provinsi' => 'DKI JAKARTA',
        'kabupaten_kota' => 'KOTA JAKARTA SELATAN',
        'kecamatan' => 'KEBAYORAN BARU',
        'jenis_kriminal' => 'penipuan',
        'deskripsi' => 'Penipuan online berkedok investasi bodong',
        'latitude' => -6.2297,
        'longitude' => 106.7834,
      ],
      [
        'provinsi' => 'DKI JAKARTA',
        'kabupaten_kota' => 'KOTA JAKARTA BARAT',
        'kecamatan' => 'GROGOL PETAMBURAN',
        'jenis_kriminal' => 'narkoba',
        'deskripsi' => 'Penangkapan pengedar narkoba di area pasar',
        'latitude' => -6.1754,
        'longitude' => 106.7900,
      ],

      // Jawa Barat
      [
        'provinsi' => 'JAWA BARAT',
        'kabupaten_kota' => 'KOTA BANDUNG',
        'kecamatan' => 'COBLONG',
        'jenis_kriminal' => 'perampokan',
        'deskripsi' => 'Perampokan di ATM pada malam hari',
        'latitude' => -6.8650,
        'longitude' => 107.5956,
      ],
      [
        'provinsi' => 'JAWA BARAT',
        'kabupaten_kota' => 'KOTA BEKASI',
        'kecamatan' => 'BEKASI TIMUR',
        'jenis_kriminal' => 'penganiayaan',
        'deskripsi' => 'Penganiayaan akibat konflik lalu lintas',
        'latitude' => -6.2383,
        'longitude' => 107.0342,
      ],

      // Jawa Tengah
      [
        'provinsi' => 'JAWA TENGAH',
        'kabupaten_kota' => 'KOTA SEMARANG',
        'kecamatan' => 'SEMARANG TENGAH',
        'jenis_kriminal' => 'cyber_crime',
        'deskripsi' => 'Hacking sistem perbankan online',
        'latitude' => -6.9667,
        'longitude' => 110.4167,
      ],
      [
        'provinsi' => 'JAWA TENGAH',
        'kabupaten_kota' => 'KAB. KLATEN',
        'kecamatan' => 'KLATEN TENGAH',
        'jenis_kriminal' => 'korupsi',
        'deskripsi' => 'Korupsi dana bantuan sosial desa',
        'latitude' => -7.7056,
        'longitude' => 110.6064,
      ],

      // Jawa Timur
      [
        'provinsi' => 'JAWA TIMUR',
        'kabupaten_kota' => 'KOTA SURABAYA',
        'kecamatan' => 'GUBENG',
        'jenis_kriminal' => 'pencurian',
        'deskripsi' => 'Pencurian sepeda motor di parkiran mall',
        'latitude' => -7.2669,
        'longitude' => 112.7492,
      ],
      [
        'provinsi' => 'JAWA TIMUR',
        'kabupaten_kota' => 'KOTA MALANG',
        'kecamatan' => 'KLOJEN',
        'jenis_kriminal' => 'vandalisme',
        'deskripsi' => 'Vandalisme di fasilitas umum taman kota',
        'latitude' => -7.9797,
        'longitude' => 112.6304,
      ],

      // Bali
      [
        'provinsi' => 'BALI',
        'kabupaten_kota' => 'KOTA DENPASAR',
        'kecamatan' => 'DENPASAR SELATAN',
        'jenis_kriminal' => 'penipuan',
        'deskripsi' => 'Penipuan travel gelap di area wisata',
        'latitude' => -8.6705,
        'longitude' => 115.2126,
      ],

      // Sumatera Utara
      [
        'provinsi' => 'SUMATERA UTARA',
        'kabupaten_kota' => 'KOTA MEDAN',
        'kecamatan' => 'MEDAN KOTA',
        'jenis_kriminal' => 'perjudian',
        'deskripsi' => 'Penggerebekan bandar judi online',
        'latitude' => 3.5952,
        'longitude' => 98.6722,
      ],

      // Sulawesi Selatan
      [
        'provinsi' => 'SULAWESI SELATAN',
        'kabupaten_kota' => 'KOTA MAKASSAR',
        'kecamatan' => 'MAKASSAR',
        'jenis_kriminal' => 'trafficking',
        'deskripsi' => 'Kasus human trafficking lintas negara',
        'latitude' => -5.1477,
        'longitude' => 119.4327,
      ],

      // Kalimantan Timur
      [
        'provinsi' => 'KALIMANTAN TIMUR',
        'kabupaten_kota' => 'KOTA SAMARINDA',
        'kecamatan' => 'SAMARINDA ULU',
        'jenis_kriminal' => 'pencucian_uang',
        'deskripsi' => 'Pencucian uang hasil illegal mining',
        'latitude' => 0.5022,
        'longitude' => 117.1537,
      ],

      // Papua
      [
        'provinsi' => 'PAPUA',
        'kabupaten_kota' => 'KOTA JAYAPURA',
        'kecamatan' => 'JAYAPURA UTARA',
        'jenis_kriminal' => 'pembunuhan',
        'deskripsi' => 'Pembunuhan akibat konflik antar kelompok',
        'latitude' => -2.5337,
        'longitude' => 140.7181,
      ],

      // Aceh
      [
        'provinsi' => 'ACEH',
        'kabupaten_kota' => 'KOTA BANDA ACEH',
        'kecamatan' => 'BAITURRAHMAN',
        'jenis_kriminal' => 'kekerasan_dalam_rumah_tangga',
        'deskripsi' => 'KDRT dengan kekerasan fisik',
        'latitude' => 5.5483,
        'longitude' => 95.3238,
      ],

      // Riau
      [
        'provinsi' => 'RIAU',
        'kabupaten_kota' => 'KOTA PEKANBARU',
        'kecamatan' => 'SUKAJADI',
        'jenis_kriminal' => 'pemerkosaan',
        'deskripsi' => 'Kasus pemerkosaan di area sepi',
        'latitude' => 0.5071,
        'longitude' => 101.4478,
      ],

      // Data tambahan untuk variasi
      [
        'provinsi' => 'DKI JAKARTA',
        'kabupaten_kota' => 'KOTA JAKARTA TIMUR',
        'kecamatan' => 'CAKUNG',
        'jenis_kriminal' => 'narkoba',
        'deskripsi' => 'Pabrik sabu-sabu rumahan',
        'latitude' => -6.1840,
        'longitude' => 106.9446,
      ],
      [
        'provinsi' => 'JAWA BARAT',
        'kabupaten_kota' => 'KOTA BOGOR',
        'kecamatan' => 'BOGOR TENGAH',
        'jenis_kriminal' => 'terorisme',
        'deskripsi' => 'Ancaman bom di fasilitas umum',
        'latitude' => -6.5950,
        'longitude' => 106.8166,
      ],
      [
        'provinsi' => 'JAWA TENGAH',
        'kabupaten_kota' => 'KOTA SOLO',
        'kecamatan' => 'LAWEYAN',
        'jenis_kriminal' => 'pencurian',
        'deskripsi' => 'Pencurian dengan pemberatan di rumah kosong',
        'latitude' => -7.5650,
        'longitude' => 110.8208,
      ],
      [
        'provinsi' => 'DI YOGYAKARTA',
        'kabupaten_kota' => 'KOTA YOGYAKARTA',
        'kecamatan' => 'GONDOKUSUMAN',
        'jenis_kriminal' => 'lainnya',
        'deskripsi' => 'Penculikan anak di bawah umur',
        'latitude' => -7.7831,
        'longitude' => 110.3668,
      ],
      [
        'provinsi' => 'BANTEN',
        'kabupaten_kota' => 'KOTA TANGERANG',
        'kecamatan' => 'TANGERANG',
        'jenis_kriminal' => 'penganiayaan',
        'deskripsi' => 'Tawuran antar geng motor',
        'latitude' => -6.1783,
        'longitude' => 106.6319,
      ],
    ];

    // Loop untuk setiap data dan insert ke database
    foreach ($crimeDataSample as $crimeData) {
      try {
        // Cari provinsi berdasarkan nama
        $provinsi = Provinsi::where('nama', $crimeData['provinsi'])->first();
        if (!$provinsi) {
          echo "Provinsi {$crimeData['provinsi']} tidak ditemukan\n";
          continue;
        }

        // Cari kabupaten/kota berdasarkan nama dan provinsi_id
        $kabupatenKota = KabupatenKota::where('nama', $crimeData['kabupaten_kota'])
          ->where('provinsi_id', $provinsi->id)
          ->first();
        if (!$kabupatenKota) {
          echo "Kabupaten/Kota {$crimeData['kabupaten_kota']} tidak ditemukan\n";
          continue;
        }

        // Cari kecamatan berdasarkan nama dan kabupaten_kota_id
        $kecamatan = Kecamatan::where('nama', $crimeData['kecamatan'])
          ->where('kabupaten_kota_id', $kabupatenKota->id)
          ->first();
        if (!$kecamatan) {
          echo "Kecamatan {$crimeData['kecamatan']} tidak ditemukan\n";
          continue;
        }

        // Create crime data
        CrimeData::create([
          'provinsi_id' => $provinsi->id,
          'kabupaten_kota_id' => $kabupatenKota->id,
          'kecamatan_id' => $kecamatan->id,
          'jenis_kriminal' => $crimeData['jenis_kriminal'],
          'deskripsi' => $crimeData['deskripsi'],
          'latitude' => $crimeData['latitude'],
          'longitude' => $crimeData['longitude'],
        ]);

        echo "Data kriminal di {$crimeData['provinsi']}, {$crimeData['kabupaten_kota']}, {$crimeData['kecamatan']} berhasil ditambahkan\n";
      } catch (\Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        continue;
      }
    }

    echo "CrimeDataSeeder selesai dijalankan\n";
  }
}
