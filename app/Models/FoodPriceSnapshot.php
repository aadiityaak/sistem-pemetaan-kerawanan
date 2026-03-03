<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodPriceSnapshot extends Model
{
    protected $table = 'food_price_snapshots';

    protected $fillable = [
        'endpoint',
        'params',
        'params_hash',
        'payload',
        'fetched_at',
        'date_key',
    ];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'payload' => 'array',
            'fetched_at' => 'datetime',
            'date_key' => 'date',
        ];
    }
}
