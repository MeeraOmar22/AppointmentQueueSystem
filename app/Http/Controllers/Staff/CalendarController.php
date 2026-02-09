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
        $today = Carbon::today();
        // Get active dentists who are NOT on leave today
        $dentists = Dentist::where('status', 1)
            ->whereDoesntHave('leaves', function($query) use ($today) {
                $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->get(['id','name']);
        return view('staff.calendar.index', compact('dentists'));
    }

    public function events(Request $request)
    {
        $start = Carbon::parse($request->query('start'));
        $end = Carbon::parse($request->query('end'));
        $dentistId = $request->query('dentist_id');
        $status = $request->query('status');

        $startDate = $start->toDateString();
        $endDate = $end->toDateString();
        
        \Log::info('Calendar events requested', [
            'start' => $startDate,
            'end' => $endDate,
            'start_datetime' => $start->toDateTimeString(),
            'end_datetime' => $end->toDateTimeString(),
            'dentist_id' => $dentistId,
            'status' => $status
        ]);

        // Build query with simpler logic
        $query = Appointment::with(['service','dentist','queue.room']);
        
        if ($dentistId) {
            $query->where('dentist_id', $dentistId);
        }
        
        // Filter by status if provided (comma-separated statuses)
        if ($status) {
            $statuses = array_map('trim', explode(',', $status));
            $query->whereIn('status', $statuses);
        }
        
        // Filter by date range - include any appointment on the specified dates
        $query->whereBetween('appointment_date', [$startDate, $endDate]);

        $appointments = $query->get();
        
        \Log::info('Calendar appointments found', [
            'count' => $appointments->count(),
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $events = [];
        foreach ($appointments as $a) {
            try {
                $duration = max((int) ($a->service?->estimated_duration ?? 0), 15);
                
                // Simple title: Just patient name
                $title = $a->patient_name;
                
                // Store full details in extendedProps for tooltip/modal later
                $fullDetails = implode(' • ', array_filter([
                    $a->patient_name,
                    $a->dentist?->name,
                    $a->service?->name,
                    ($a->room ? 'Room ' . $a->room : null),
                ]));

                // Compute start/end robustly
                $appointmentDate = $a->appointment_date instanceof \Carbon\Carbon 
                    ? $a->appointment_date->toDateString() 
                    : ($a->appointment_date ?? Carbon::now()->toDateString());
                    
                $appointmentTime = $a->appointment_time instanceof \Carbon\Carbon 
                    ? $a->appointment_time->toTimeString() 
                    : ($a->appointment_time ?? '09:00:00');
                
                $startAt = $a->start_at 
                    ? Carbon::parse($a->start_at) 
                    : Carbon::parse($appointmentDate . ' ' . $appointmentTime);
                    
                // For completed appointments, use the actual end time (when treatment actually finished)
                // For other appointments, use the scheduled end time
                if (in_array($status, ['completed', 'feedback_scheduled', 'feedback_sent'])) {
                    // Use actual treatment end time if available, otherwise fall back to calculated end time
                    $endAt = $a->treatment_ended_at 
                        ? Carbon::parse($a->treatment_ended_at)
                        : ($a->actual_end_time 
                            ? Carbon::parse($a->actual_end_time)
                            : (clone $startAt)->addMinutes($duration));
                } else {
                    // For non-completed appointments, use scheduled end time
                    $endAt = $a->end_at 
                        ? Carbon::parse($a->end_at) 
                        : (clone $startAt)->addMinutes($duration);
                }

                // Status color mapping
                // Convert Enum to string if needed
                $statusEnum = $a->status ?? 'booked';
                $status = $statusEnum instanceof \BackedEnum ? $statusEnum->value : (string)$statusEnum;
                $color = '#6c757d'; // grey default
                
                \Log::info('Processing appointment status', [
                    'appointment_id' => $a->id,
                    'status' => $status,
                    'status_length' => strlen($status),
                    'status_bytes' => bin2hex($status),
                ]);
                
                // Status to display label mapping (for legend consistency)
                $statusLabel = match($status) {
                    'booked', 'confirmed', 'checked_in' => 'Confirmed',
                    'waiting' => 'Waiting',
                    'in_treatment' => 'In Treatment',
                    'completed', 'feedback_scheduled', 'feedback_sent' => 'Completed',
                    'cancelled', 'no_show' => 'Cancelled',
                    default => ucfirst(str_replace('_', ' ', $status))
                };
                
                // Color mapping (standardized with legend)
                // Green: Confirmed (booked/confirmed/checked_in ready for treatment)
                // Yellow: Waiting (in queue, waiting to be called)
                // Blue: In Treatment (actively in treatment)
                // Green: Completed (finished treatment)
                // Red: Cancelled (appointment cancelled/no-show)
                if (in_array($status, ['booked', 'confirmed', 'checked_in'])) {
                    $color = '#198754'; // green - confirmed/ready
                } elseif ($status === 'waiting') {
                    $color = '#ffc107'; // yellow - waiting to be called
                } elseif ($status === 'in_treatment') {
                    $color = '#0d6efd'; // blue - currently being treated
                } elseif (in_array($status, ['completed', 'feedback_scheduled', 'feedback_sent'])) {
                    $color = '#28a745'; // green - completed
                } elseif (in_array($status, ['cancelled', 'no_show'])) {
                    $color = '#dc3545'; // red - cancelled/no show
                } else {
                    \Log::warning('No color condition matched!', ['status' => $status, 'color' => $color]);
                }

                // Conflict detection
                if ($a->dentist_id) {
                    $overlap = Appointment::where('dentist_id', $a->dentist_id)
                        ->where('id', '!=', $a->id)
                        ->where('appointment_date', $a->appointment_date)
                        ->whereNotIn('status', ['completed', 'feedback_sent', 'cancelled', 'no_show'])
                        ->exists();
                    
                    if ($overlap && !in_array($status, ['in_treatment', 'completed', 'feedback_sent'])) {
                        $color = '#dc3545'; // red for conflicts
                    }
                }

                $events[] = [
                    'id' => $a->id,
                    'title' => $title,
                    'start' => $startAt->toIso8601String(),
                    'end' => $endAt->toIso8601String(),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    // NOTE: Deliberately omitting 'url' to prevent automatic navigation
                    // Calendar click shows modal instead (via eventClick handler)
                    // User can click "Edit" button in modal if they want to edit
                    'extendedProps' => [
                        'dentist' => $a->dentist?->name ?? 'Unassigned',
                        'service' => $a->service?->name ?? 'N/A',
                        'room' => $a->queue?->room?->room_number ?? 'N/A',
                        'status' => $status,
                        'duration' => $duration . ' min',
                        'fullDetails' => $fullDetails,
                    ]
                ];
            } catch (\Exception $e) {
                \Log::error('Error processing appointment for calendar', [
                    'appointment_id' => $a->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        \Log::info('Calendar events prepared', ['event_count' => count($events)]);

        return response()->json($events);
    }
}
