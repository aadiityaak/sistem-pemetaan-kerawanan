<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kecamatan extends Model
{
    protected $table = 'kecamatan';
    public $incrementing = false;
    protected $primaryKey = null;
    public $timestamps = true;
    protected $fillable = ['kode_provinsi', 'kode_kabupaten_kota', 'kode', 'nama'];

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, ['kode_provinsi', 'kode_kabupaten_kota'], ['kode_provinsi', 'kode']);
    }
} 