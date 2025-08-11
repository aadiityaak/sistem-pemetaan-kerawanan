<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JumlahSuara extends Model
{
    protected $table = 'jumlah_suara';

    protected $fillable = [
        'partai_id',
        'tahun_pemilu',
        'jumlah_suara'
    ];

    protected $casts = [
        'jumlah_suara' => 'integer',
        'tahun_pemilu' => 'integer'
    ];

    public function partaiPolitik(): BelongsTo
    {
        return $this->belongsTo(PartaiPolitik::class, 'partai_id');
    }
}
