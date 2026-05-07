<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPredictionHistory extends Model
{
    protected $fillable = [
        'user_id',
        'ai_provider_key',
        'ai_provider_name',
        'category_id',
        'category_name',
        'sub_category_id',
        'sub_category_name',
        'time_period',
        'data_period',
        'start_date',
        'end_date',
        'total_analyzed',
        'data_fingerprint',
        'analysis',
        'statistics',
    ];

    protected $casts = [
        'statistics' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}

