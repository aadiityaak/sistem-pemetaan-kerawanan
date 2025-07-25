<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrimeData extends Model
{
    protected $table = 'crime_data';
    protected $fillable = [
        'kode_provinsi',
        'kode_kabupaten_kota',
        'kode_kecamatan',
        'latitude',
        'longitude',
        'jenis_kriminal',
        'deskripsi',
    ];

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'kode_provinsi', 'kode');
    }

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, ['kode_provinsi', 'kode_kabupaten_kota'], ['kode_provinsi', 'kode']);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, ['kode_provinsi', 'kode_kabupaten_kota', 'kode_kecamatan'], ['kode_provinsi', 'kode_kabupaten_kota', 'kode']);
    }
} 