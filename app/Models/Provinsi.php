<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provinsi extends Model
{
    protected $table = 'provinsi';
    protected $fillable = ['nama'];

    public function kabupatenKota(): HasMany
    {
        return $this->hasMany(KabupatenKota::class);
    }

    public function monitoringData(): HasMany
    {
        return $this->hasMany(MonitoringData::class);
    }
}
