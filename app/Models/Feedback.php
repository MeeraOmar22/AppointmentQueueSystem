<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'appointment_id',
        'patient_name',
        'patient_phone',
        'rating',
        'comments',
        'service_quality',
        'staff_friendliness',
        'cleanliness',
        'would_recommend',
    ];

    protected $casts = [
        'would_recommend' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the appointment that this feedback is for.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
