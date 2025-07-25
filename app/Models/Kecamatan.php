<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    protected $fillable = ['kabupaten_kota_id', 'nama'];

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class);
    }

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    public function crimeData(): HasMany
    {
        return $this->hasMany(CrimeData::class);
    }
}
