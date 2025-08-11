<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sembako extends Model
{
    protected $table = 'sembako';

    protected $fillable = [
        'nama_komoditas',
        'satuan',
        'harga',
        'kabupaten_kota_id',
        'tanggal_pencatatan',
        'keterangan',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'tanggal_pencatatan' => 'date',
    ];

    public function kabupatenKota(): BelongsTo
    {
        return $this->belongsTo(KabupatenKota::class, 'kabupaten_kota_id');
    }

    // Format harga untuk display
    public function getFormattedHargaAttribute(): string
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    protected $appends = ['formatted_harga'];
}
