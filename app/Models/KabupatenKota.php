<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KabupatenKota extends Model
{
    protected $table = 'kabupaten_kota';
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = true;
    protected $fillable = ['kode_provinsi', 'kode', 'nama'];

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'kode_provinsi', 'kode');
    }

    public function kecamatan(): HasMany
    {
        return $this->hasMany(Kecamatan::class, ['kode_provinsi', 'kode_kabupaten_kota'], ['kode_provinsi', 'kode']);
    }
} 