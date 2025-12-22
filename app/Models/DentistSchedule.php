<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistSchedule extends Model
{
    protected $fillable = [
        'dentist_id',
        'day_of_week',
        'is_available',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }
}
