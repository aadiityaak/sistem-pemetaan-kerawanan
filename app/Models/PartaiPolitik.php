<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartaiPolitik extends Model
{
    protected $table = 'partai_politik';

    protected $fillable = [
        'nama_partai',
        'singkatan',
        'nomor_urut',
        'logo_path',
        'status_aktif'
    ];

    protected $casts = [
        'status_aktif' => 'boolean'
    ];

    protected $appends = [
        'logo_url'
    ];

    public function jumlahSuara(): HasMany
    {
        return $this->hasMany(JumlahSuara::class, 'partai_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }
}
