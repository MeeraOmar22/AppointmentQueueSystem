<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Room;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class ExceptionAlertService
{
    public static function roomDisabled(Room $room, ?bool $wasActive = null): void
    {
        $oldValues = ['is_active' => $wasActive ?? true];
        $newValues = ['is_active' => false];

        ActivityLogger::log(
            'room_disabled',
            'Room',
            $room->id,
            'Room ' . $room->room_number . ' was disabled for maintenance or reconfiguration.',
            $oldValues,
            $newValues
        );

        Log::warning('ExceptionAlert: Room disabled', [
            'room_id' => $room->id,
            'room_number' => $room->room_number,
            'clinic_location' => $room->clinic_location,
            'status' => $room->status,
        ]);
    }

    public static function appointmentCancelled(Appointment $appointment, ?string $previousStatus = null, ?int $queueNumber = null): void
    {
        ActivityLogger::log(
            'queue_exception',
            'Appointment',
            $appointment->id,
            'Appointment ' . $appointment->visit_code . ' was cancelled, impacting the queue.',
            ['status' => $previousStatus],
            ['status' => 'cancelled']
        );

        Log::warning('ExceptionAlert: Appointment cancelled', [
            'appointment_id' => $appointment->id,
            'visit_code' => $appointment->visit_code,
            'patient_name' => $appointment->patient_name,
            'clinic_location' => $appointment->clinic_location,
            'previous_status' => $previousStatus,
            'queue_number' => $queueNumber,
        ]);
    }
}
