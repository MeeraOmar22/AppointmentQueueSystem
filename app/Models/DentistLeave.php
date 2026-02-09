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

    /**
     * Boot the model.
     * Add validation to ensure end_date >= start_date
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->end_date < $model->start_date) {
                throw new \Exception('End date cannot be before start date');
            }
        });

        static::updating(function ($model) {
            if ($model->end_date < $model->start_date) {
                throw new \Exception('End date cannot be before start date');
            }
        });
    }
}
