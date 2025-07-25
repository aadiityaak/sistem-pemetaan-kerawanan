<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrimeData extends Model
{
    protected $table = 'crime_data';
    protected $fillable = [
        'provinsi_id',
        'kabupaten_kota_id',
        'kecamatan_id',
        'latitude',
        'longitude',
        'jenis_kriminal',
        'deskripsi',
    ];

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }
}
