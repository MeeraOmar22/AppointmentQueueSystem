<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistLeave extends Model
{
    protected $fillable = [
        'dentist_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }
}
