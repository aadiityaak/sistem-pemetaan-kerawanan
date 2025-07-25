<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provinsi extends Model
{
    protected $table = 'provinsi';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['kode', 'nama'];

    public function kabupatenKota(): HasMany
    {
        return $this->hasMany(KabupatenKota::class, 'kode_provinsi', 'kode');
    }
} 