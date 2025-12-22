<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingHour extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'day_of_week',
        'session_label',
        'start_time',
        'end_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];
}
