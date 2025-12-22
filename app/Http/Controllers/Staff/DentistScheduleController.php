<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\DentistLeave;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DentistScheduleController extends Controller
{
    public function index()
    {
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $dentists = Dentist::where('status', 1)
            ->with(['schedules', 'leaves'])
            ->get();

        foreach ($dentists as $dentist) {
            foreach ($days as $day) {
                DentistSchedule::firstOrCreate(
                    ['dentist_id' => $dentist->id, 'day_of_week' => $day],
                    ['is_available' => true, 'start_time' => '09:00', 'end_time' => '17:00']
                );
            }
        }

        return view('staff.dentist-schedules.index', compact('dentists', 'days'));
    }

    public function calendar()
    {
        return view('staff.dentist-schedules.calendar');
    }

    public function events(Request $request)
    {
        try {
            $startDate = $request->input('start') ? Carbon::parse($request->input('start'))->startOfDay() : Carbon::now()->startOfMonth();
            $endDate = $request->input('end') ? Carbon::parse($request->input('end'))->endOfDay() : Carbon::now()->endOfMonth()->endOfDay();
            $dentistId = $request->input('dentist_id');

            $events = [];

            // Get dentists
            $dentistsQuery = Dentist::where('status', 1)
                ->with(['schedules', 'leaves' => function ($q) use ($startDate, $endDate) {
                    $q->whereDate('end_date', '>=', $startDate)->whereDate('start_date', '<=', $endDate);
                }]);
            
            if ($dentistId) {
                $dentistsQuery->where('id', $dentistId);
            }
            
            $dentists = $dentistsQuery->get();

            // Get appointments
            $appointmentsQuery = Appointment::with(['dentist', 'service'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNotNull('start_at')
                           ->whereNotNull('end_at')
                           ->where('start_at', '<', $endDate)
                           ->where('end_at', '>', $startDate);
                    })->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNull('start_at')
                           ->whereNotNull('appointment_date')
                           ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
                });
            
            if ($dentistId) {
                $appointmentsQuery->where('dentist_id', $dentistId);
            }
            
            $appointments = $appointmentsQuery->get();

            // Add appointment events
            foreach ($appointments as $apt) {
                if ($apt->start_at && $apt->end_at) {
                    $startTime = $apt->start_at instanceof Carbon ? $apt->start_at : Carbon::parse($apt->start_at);
                    $endTime = $apt->end_at instanceof Carbon ? $apt->end_at : Carbon::parse($apt->end_at);
                    $events[] = [
                        'title' => ($apt->dentist ? $apt->dentist->name : 'Unknown') . ': ' . ($apt->service ? $apt->service->name : 'Appt'),
                        'start' => $startTime->toIso8601String(),
                        'end' => $endTime->toIso8601String(),
                        'color' => '#0d6efd',
                        'textColor' => '#fff',
                        'extendedProps' => ['type' => 'appointment']
                    ];
                } else if ($apt->appointment_date) {
                    $events[] = [
                        'title' => ($apt->dentist ? $apt->dentist->name : 'Unknown') . ': ' . ($apt->service ? $apt->service->name : 'Appt'),
                        'start' => $apt->appointment_date->toDateString(),
                        'allDay' => true,
                        'color' => '#0d6efd',
                        'textColor' => '#fff',
                        'extendedProps' => ['type' => 'appointment']
                    ];
                }
            }

            // Add dentist events
            foreach ($dentists as $dentist) {
                // Add leave events
                foreach ($dentist->leaves as $leave) {
                    $events[] = [
                        'title' => '✕ ' . $dentist->name . ' On Leave',
                        'start' => $leave->start_date->toDateString(),
                        'end' => $leave->end_date->copy()->addDay()->toDateString(),
                        'allDay' => true,
                        'color' => '#dc3545',
                        'textColor' => '#fff',
                        'extendedProps' => ['type' => 'leave']
                    ];
                }

                // Add availability events
                $scheduleByDay = $dentist->schedules->keyBy('day_of_week');
                $currentDate = $startDate->copy();
                
                while ($currentDate->lte($endDate)) {
                    $dayName = $currentDate->format('l');
                    $schedule = $scheduleByDay->get($dayName);

                    $isOnLeave = $dentist->leaves->first(function ($leave) use ($currentDate) {
                        return $currentDate->gte($leave->start_date) && $currentDate->lte($leave->end_date);
                    });

                    if ($isOnLeave) {
                        // Already handled in leave events section
                    } else if ($schedule && !$schedule->is_available) {
                        // Unavailable day (yellow)
                        $events[] = [
                            'title' => '✕ ' . $dentist->name . ' Unavailable',
                            'start' => $currentDate->toDateString(),
                            'allDay' => true,
                            'color' => '#ffc107',
                            'textColor' => '#000',
                            'extendedProps' => ['type' => 'unavailable']
                        ];
                    } else if ($schedule && $schedule->is_available) {
                        // Available day (green)
                        if ($schedule->start_time && $schedule->end_time) {
                            try {
                                $dayStr = $currentDate->toDateString();
                                $startDayTime = Carbon::createFromFormat('Y-m-d H:i:s', $dayStr . ' ' . $schedule->start_time);
                                $endDayTime = Carbon::createFromFormat('Y-m-d H:i:s', $dayStr . ' ' . $schedule->end_time);
                                
                                if ($startDayTime && $endDayTime) {
                                    $events[] = [
                                        'title' => '✓ ' . $dentist->name . ' (' . Carbon::parse($schedule->start_time)->format('H:i') . '-' . Carbon::parse($schedule->end_time)->format('H:i') . ')',
                                        'start' => $startDayTime->toIso8601String(),
                                        'end' => $endDayTime->toIso8601String(),
                                        'color' => '#198754',
                                        'textColor' => '#fff',
                                        'extendedProps' => ['type' => 'available']
                                    ];
                                }
                            } catch (\Exception $e) {
                                \Log::error('Time parsing error for ' . $dentist->name . ': ' . $e->getMessage());
                            }
                        }
                    }

                    $currentDate->addDay();
                }
            }

            return response()->json($events);
        } catch (\Exception $e) {
            \Log::error('DentistScheduleController events error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, DentistSchedule $dentistSchedule)
    {
        $data = $request->validate([
            'is_available' => 'nullable|boolean',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ]);

        $isAvailable = $request->boolean('is_available', false);
        $data['is_available'] = $isAvailable;

        if (!$isAvailable) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        $dentistSchedule->update($data);

        return back()->with('success', 'Schedule updated');
    }
}
