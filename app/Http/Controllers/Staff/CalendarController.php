<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Dentist;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $dentists = Dentist::where('status', 1)->get(['id','name']);
        return view('staff.calendar.index', compact('dentists'));
    }

    public function events(Request $request)
    {
        $start = Carbon::parse($request->query('start'));
        $end = Carbon::parse($request->query('end'));
        $dentistId = $request->query('dentist_id');

        $query = Appointment::with(['service','dentist','queue']);
        if ($dentistId) {
            $query->where('dentist_id', $dentistId);
        }
        // Include appointments that OVERLAP the requested window
        $query->where(function ($q) use ($start, $end) {
            $q->where(function ($q1) use ($start, $end) {
                $q1->whereNotNull('start_at')
                   ->where('start_at', '<', $end)
                   ->where('end_at', '>', $start);
            })->orWhere(function ($q2) use ($start, $end) {
                $q2->whereNull('start_at')
                   ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()]);
            });
        });

        $appointments = $query->get();

        $events = [];
        foreach ($appointments as $a) {
            $duration = max((int) ($a->service->estimated_duration ?? 0), 15);
            $titleParts = [
                $a->patient_name,
                $a->dentist?->name,
                $a->service?->name,
                ($duration ? $duration . ' min' : null),
                ($a->room ? 'Room ' . $a->room : null),
            ];
            $title = implode(' • ', array_filter($titleParts));

            // Compute start/end robustly
            $startAt = $a->start_at ? Carbon::parse($a->start_at) : Carbon::parse(($a->appointment_date ?? Carbon::now()->toDateString()) . ' ' . ($a->appointment_time ?? '00:00:00'));
            $endAt = $a->end_at ? Carbon::parse($a->end_at) : (clone $startAt)->addMinutes($duration);

            // Status color mapping
            $color = '#198754'; // green confirmed by default
            if ($a->queue) {
                if ($a->queue->queue_status === 'waiting') {
                    $color = '#ffc107'; // yellow checked-in
                } elseif ($a->queue->queue_status === 'in_service') {
                    $color = '#0d6efd'; // blue in progress
                } elseif ($a->queue->queue_status === 'completed') {
                    $color = '#6c757d'; // grey done
                }
            }
            // Conflict: overlapping same dentist appointment
            $overlap = Appointment::where('dentist_id', $a->dentist_id)
                ->where('id', '!=', $a->id)
                ->whereDate('appointment_date', $a->appointment_date)
                ->where(function ($q) use ($startAt, $endAt) {
                    $q->where('start_at', '<', $endAt)->where('end_at', '>', $startAt);
                })
                ->exists();
            if ($overlap && (!$a->queue || $a->queue->queue_status !== 'in_service')) {
                $color = '#dc3545'; // red delayed/conflict
            }

            $events[] = [
                'id' => $a->id,
                'title' => $title,
                'start' => $startAt->toIso8601String(),
                'end' => $endAt->toIso8601String(),
                'color' => $color,
                'url' => url('/staff/appointments/'.$a->id.'/edit'),
            ];
        }

        return response()->json($events);
    }
}
