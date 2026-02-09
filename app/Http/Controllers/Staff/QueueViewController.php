<?php

namespace App\Http\Controllers\Staff;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Live Queue Board View Controller
 * 
 * WIRING ONLY - No business logic
 * Returns view for /staff/queue
 */
class QueueViewController
{
    /**
     * GET /staff/queue
     * Display Live Queue Board
     * 
     * The view handles:
     * - Fetching data from /api/staff/queue/data (or existing endpoints)
     * - Real-time updates via Echo
     * - All JavaScript interactions
     */
    public function index()
    {
        return view('staff.queue');
    }

    /**
     * Display appointments in treatment
     */
    public function inTreatment(Request $request)
    {
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', 'in_treatment')
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'asc')
            ->paginate(20);

        return view('staff.queue-filtered', [
            'appointments' => $appointments,
            'filter' => 'in_treatment',
            'title' => 'In Treatment',
            'subtitle' => 'Patients currently being treated',
        ]);
    }

    /**
     * Display appointments waiting in queue
     */
    public function waiting(Request $request)
    {
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['checked_in', 'waiting'])
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'asc')
            ->paginate(20);

        return view('staff.queue-filtered', [
            'appointments' => $appointments,
            'filter' => 'waiting',
            'title' => 'Waiting in Queue',
            'subtitle' => 'Patients waiting for their turn',
        ]);
    }

    /**
     * Display completed appointments today
     * NOTE: Counts appointments where treatment_ended_at is set (not 'completed' status)
     * because 'completed' status is transient - transitions to feedback_scheduled/feedback_sent
     */
    public function completed(Request $request)
    {
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereNotNull('treatment_ended_at')
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        return view('staff.queue-filtered', [
            'appointments' => $appointments,
            'filter' => 'completed',
            'title' => 'Completed Today',
            'subtitle' => 'Patients who completed treatment',
        ]);
    }

    /**
     * Display available dentists
     */
    public function availableDentists(Request $request)
    {
        $dentists = Appointment::whereDate('appointment_date', Carbon::today())
            ->with('dentist')
            ->get()
            ->groupBy('dentist.id')
            ->map(function ($appointments) {
                $dentist = $appointments->first()->dentist;
                $inTreatmentCount = $appointments->where('status', 'in_treatment')->count();
                $nextAppointment = $appointments->where('status', '!=', 'completed')
                    ->sortBy('appointment_time')
                    ->first();

                return [
                    'dentist' => $dentist,
                    'in_treatment_count' => $inTreatmentCount,
                    'next_appointment' => $nextAppointment,
                ];
            })
            ->values();

        return view('staff.dentists-available', [
            'dentists' => $dentists,
            'title' => 'Available Dentists',
            'subtitle' => 'Dentists and their workload today',
        ]);
    }
}
