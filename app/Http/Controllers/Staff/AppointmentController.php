<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\OperatingHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use App\Services\WhatsAppSender;


class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        
        // Get filter parameters
        $statusFilter = $request->query('status', null);
        $dateFilter = $request->query('date_filter', 'today'); // today, upcoming, past
        
        // Today's appointments with optional status filter
        $todayQuery = Appointment::with(['service', 'queue', 'dentist'])
            ->whereDate('appointment_date', $today);
        
        if ($statusFilter) {
            $todayQuery->where('status', $statusFilter);
        }
        
        $todayAppointments = $todayQuery
            ->orderByRaw('CASE WHEN queues.queue_number IS NULL THEN 1 ELSE 0 END')
            ->leftJoin('queues', 'appointments.id', '=', 'queues.appointment_id')
            ->select('appointments.*')
            ->get();

        // Upcoming appointments
        $upcomingQuery = Appointment::with(['service', 'dentist'])
            ->whereDate('appointment_date', '>', $today);
        
        if ($statusFilter) {
            $upcomingQuery->where('status', $statusFilter);
        }
        
        $upcomingAppointments = $upcomingQuery
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(50)
            ->get();

        // Past appointments
        $pastQuery = Appointment::with(['service', 'dentist'])
            ->whereDate('appointment_date', '<', $today);
        
        if ($statusFilter) {
            $pastQuery->where('status', $statusFilter);
        }
        
        $pastAppointments = $pastQuery
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->limit(50)
            ->get();

        $baseDurations = Service::pluck('estimated_duration', 'id')->map(function ($val) {
            return max((int) ($val ?? 0), 15);
        });

        $historical = DB::table('appointments')
            ->join('queues', 'appointments.id', '=', 'queues.appointment_id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->where('queues.queue_status', 'completed')
            ->select('appointments.service_id', DB::raw('AVG(services.estimated_duration) as avg_duration'))
            ->groupBy('appointments.service_id')
            ->pluck('avg_duration', 'appointments.service_id');

        $averageDurations = $baseDurations->map(function ($val, $key) use ($historical) {
            return (int) round($historical[$key] ?? $val);
        });

        $queueEntries = Queue::with(['appointment.service'])
            ->whereHas('appointment', function ($query) use ($today) {
                $query->whereDate('appointment_date', $today);
            })
            ->whereNotNull('queue_number')
            ->orderBy('queue_number')
            ->get();

        $waitingTimeMap = [];
        $runningTotal = 0;
        $inServiceCount = 0;
        
        foreach ($queueEntries as $entry) {
            // For waiting patients, add cumulative time from all waiting + in_service ahead
            // For in_service patients, add only time from waiting patients ahead
            if ($entry->queue_status === 'waiting') {
                $waitingTimeMap[$entry->appointment_id] = $runningTotal;
                
                $duration = $entry->appointment && $entry->appointment->service
                    ? max((int) ($entry->appointment->service->estimated_duration ?? 0), 15)
                    : 15;
                $runningTotal += $duration;
            } elseif ($entry->queue_status === 'in_treatment') {
                $waitingTimeMap[$entry->appointment_id] = 0; // Currently being served
                $inServiceCount++;
            }
        }

        $stats = [
            'total' => $todayAppointments->count(),
            'queued' => $queueEntries->count(),
            'waiting' => $queueEntries->where('queue_status', 'waiting')->count(),
            'in_service' => $queueEntries->where('queue_status', 'in_treatment')->count(),
            'completed' => $queueEntries->where('queue_status', 'completed')->count(),
        ];

        // Get all available statuses for filter dropdown
        $availableStatuses = [
            'booked' => 'Booked',
            'arrived' => 'Arrived',
            'in_queue' => 'In Queue',
            'in_treatment' => 'In Treatment',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'late' => 'Late',
        ];

        return view('staff.appointments', [
            'appointments' => $todayAppointments,
            'averageDurations' => $averageDurations,
            'waitingTimeMap' => $waitingTimeMap,
            'stats' => $stats,
            'today' => $today,
            'upcomingAppointments' => $upcomingAppointments,
            'pastAppointments' => $pastAppointments,
            'statusFilter' => $statusFilter,
            'dateFilter' => $dateFilter,
            'availableStatuses' => $availableStatuses,
        ]);

    }

    public function storeWalkIn(Request $request)
    {
        $data = $request->validate([
            'patient_name' => 'required|string',
            'patient_phone' => 'required|string',
            'patient_email' => 'nullable|email',
            'clinic_location' => 'required|in:seremban,kuala_pilah',
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::findOrFail($data['service_id']);
        $serviceDuration = max((int) ($service->estimated_duration ?? 0), 15);

        $today = Carbon::today();
        $now = Carbon::now();

        $hours = OperatingHour::where('day_of_week', $today->format('l'))->first();
        $dayStart = $hours ? Carbon::parse($today->toDateString() . ' ' . $hours->start_time) : $today->copy()->startOfDay();
        $dayEnd = $hours ? Carbon::parse($today->toDateString() . ' ' . $hours->end_time) : $today->copy()->endOfDay();

        $slotStart = $now->greaterThan($dayStart) ? $now->copy() : $dayStart->copy();
        $slotStart->setSecond(0);

        [$startAt, $endAt, $dentist] = $this->findNearestSlot($slotStart, $dayEnd, $serviceDuration);

        if (!$dentist || !$startAt || !$endAt) {
            return back()->withErrors(['service_id' => 'No available slot found for today.']);
        }

        $appointment = Appointment::create([
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'] ?? null,
            'clinic_location' => $data['clinic_location'],
            'service_id' => $service->id,
            'dentist_id' => $dentist->id,
            'appointment_date' => $startAt->toDateString(),
            'appointment_time' => $startAt->format('H:i:s'),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'booked',
            'booking_source' => 'walk-in',
        ]);

        $queueNumber = Queue::nextNumberForDate($today);

        Queue::updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'queue_number' => $queueNumber,
                'queue_status' => 'waiting',
                'check_in_time' => Carbon::now(),
            ]
        );

        return redirect()->back()->with('status', 'Walk-in appointment confirmed and queued.');
    }

    public function checkIn($id)
    {
        $appointment = Appointment::findOrFail($id);

        $queue = Queue::firstOrNew(['appointment_id' => $appointment->id]);

        if (!$queue->queue_number) {
            $queue->queue_number = Queue::nextNumberForDate($appointment->appointment_date);
        }

        $queue->queue_status = 'checked_in';
        $queue->check_in_time = now();
        $queue->save();

        $appointment->update(['status' => 'booked']);

        return redirect()->back();
    }

    public function updateQueueStatus(Request $request, $queueId)
    {
        $data = $request->validate([
            'status' => 'required|in:waiting,checked_in,in_treatment,completed',
        ]);

        $queue = Queue::with('appointment')->findOrFail($queueId);
        $queue->queue_status = $data['status'];
        $queue->save();

        if ($queue->appointment) {
            // Update appointment status to match queue status
            $queue->appointment->update([
                'status' => $data['status'],
            ]);
        }

        return redirect()->back();
    }

    public function edit($id)
    {
        $appointment = Appointment::with(['service', 'dentist', 'queue'])->findOrFail($id);
        $services = Service::where('status', 1)->get();
        $dentists = Dentist::where('status', 1)->get();
        
        return view('staff.appointments-edit', compact('appointment', 'services', 'dentists'));
    }

    public function create()
    {
        $services = Service::where('status', 1)->get();
        $dentists = Dentist::where('status', 1)->get();
        
        return view('staff.appointments-create', compact('services', 'dentists'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_name' => 'required|string',
            'patient_phone' => 'required|string',
            'patient_email' => 'nullable|email',
            'clinic_location' => 'required|in:seremban,kuala_pilah',
            'service_id' => 'required|exists:services,id',
            'dentist_id' => 'required|exists:dentists,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status' => 'required|in:booked,checked_in,in_treatment,completed,cancelled',
        ]);

        $service = Service::findOrFail($data['service_id']);
        $serviceDuration = max((int) ($service->estimated_duration ?? 0), 15);

        $startAt = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
        $endAt = $startAt->copy()->addMinutes($serviceDuration);

        if (!$this->dentistIsAvailable($data['dentist_id'], $startAt, $endAt)) {
            return back()->withInput()->withErrors(['dentist_id' => 'Selected dentist is not available for that time.']);
        }

        $appointment = Appointment::create([
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'] ?? null,
            'clinic_location' => $data['clinic_location'],
            'service_id' => $data['service_id'],
            'dentist_id' => $data['dentist_id'],
            'room' => $request->input('room'),
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => $data['status'],
            'booking_source' => 'staff',
            'visit_token' => \Illuminate\Support\Str::uuid()->toString(),
        ]);

        ActivityLogger::log('created', 'Appointment', $appointment->id, "Created appointment for {$data['patient_name']}", null, $appointment->toArray());

        return redirect('/staff/appointments')->with('success', 'Appointment created successfully.');
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $oldValues = $appointment->toArray();
        
        $data = $request->validate([
            'patient_name' => 'required|string',
            'patient_phone' => 'required|string',
            'patient_email' => 'nullable|email',
            'clinic_location' => 'required|in:seremban,kuala_pilah',
            'service_id' => 'required|exists:services,id',
            'dentist_id' => 'required|exists:dentists,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'status' => 'required|in:booked,checked_in,in_treatment,completed,cancelled',
        ]);

        $service = Service::findOrFail($data['service_id']);
        $serviceDuration = max((int) ($service->estimated_duration ?? 0), 15);

        $startAt = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
        $endAt = $startAt->copy()->addMinutes($serviceDuration);

        $appointment->update([
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'] ?? null,
            'clinic_location' => $data['clinic_location'],
            'service_id' => $data['service_id'],
            'dentist_id' => $data['dentist_id'],
            'room' => $request->input('room'),
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => $data['status'],
        ]);

        ActivityLogger::log('updated', 'Appointment', $appointment->id, "Updated appointment for {$appointment->patient_name}", $oldValues, $appointment->fresh()->toArray());

        return redirect('/staff/appointments')->with('success', 'Appointment updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $patientName = $appointment->patient_name;
        
        // Delete associated queue entry if exists
        if ($appointment->queue) {
            $appointment->queue->delete();
        }
        
        ActivityLogger::log('deleted', 'Appointment', $id, "Deleted appointment for {$patientName}", $appointment->toArray(), null);
        
        $appointment->delete();

        // Preserve the active tab
        $tab = $request->input('tab', 'today');
        return redirect('/staff/appointments?tab=' . $tab)->with('success', 'Appointment deleted successfully.');
    }

    private function dentistIsAvailable(int $dentistId, Carbon $startAt, Carbon $endAt, ?int $ignoreAppointmentId = null): bool
    {
        $schedule = DentistSchedule::where('dentist_id', $dentistId)
            ->where('day_of_week', $startAt->format('l'))
            ->first();

        if (!$schedule || !$schedule->is_available) {
            return false;
        }

        if ($schedule->start_time && $schedule->end_time) {
            $dayStart = Carbon::parse($startAt->toDateString() . ' ' . $schedule->start_time);
            $dayEnd = Carbon::parse($startAt->toDateString() . ' ' . $schedule->end_time);
            if ($startAt->lt($dayStart) || $endAt->gt($dayEnd)) {
                return false;
            }
        }

        $overlap = Appointment::where('dentist_id', $dentistId)
            ->whereDate('appointment_date', $startAt->toDateString())
            ->when($ignoreAppointmentId, function ($query) use ($ignoreAppointmentId) {
                $query->where('id', '!=', $ignoreAppointmentId);
            })
            ->whereIn('status', ['pending', 'booked'])
            ->where(function ($query) use ($startAt, $endAt) {
                $query->where(function ($q) use ($startAt, $endAt) {
                    $q->where('start_at', '<', $endAt)->where('end_at', '>', $startAt);
                })->orWhere(function ($q) use ($startAt, $endAt) {
                    $q->whereNull('start_at')
                        ->whereTime('appointment_time', '<', $endAt->format('H:i:s'))
                        ->whereTime('appointment_time', '>', $startAt->format('H:i:s'));
                });
            })
            ->exists();

        return !$overlap;
    }

    private function findNearestSlot(Carbon $startFrom, Carbon $dayEnd, int $serviceDuration): array
    {
        $cursor = $startFrom->copy();
        $stepMinutes = 5;

        while ($cursor->lt($dayEnd)) {
            $slotStart = $cursor->copy();
            $slotEnd = $slotStart->copy()->addMinutes($serviceDuration);

            if ($slotEnd->gt($dayEnd)) {
                break;
            }

            $dentist = $this->findAvailableDentist($slotStart, $slotEnd);
            if ($dentist) {
                return [$slotStart, $slotEnd, $dentist];
            }

            $cursor->addMinutes($stepMinutes);
        }

        return [null, null, null];
    }

    private function findAvailableDentist(Carbon $startAt, Carbon $endAt): ?Dentist
    {
        $dentists = Dentist::where('status', 1)->get();

        foreach ($dentists as $dentist) {
            if ($this->isDentistFree($dentist, $startAt, $endAt)) {
                return $dentist;
            }
        }

        return null;
    }

    private function isDentistFree(Dentist $dentist, Carbon $startAt, Carbon $endAt): bool
    {
        $appointments = $dentist->appointments()
            ->with('service')
            ->whereDate('appointment_date', $startAt->toDateString())
            ->whereIn('status', ['booked'])
            ->get();

        foreach ($appointments as $appointment) {
            $existingStart = $appointment->start_at
                ? Carbon::parse($appointment->start_at)
                : Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time);

            $duration = max((int) ($appointment->service->estimated_duration ?? 0), 15);
            $existingEnd = $appointment->end_at
                ? Carbon::parse($appointment->end_at)
                : (clone $existingStart)->addMinutes($duration);

            $overlaps = $startAt->lt($existingEnd) && $endAt->gt($existingStart);

            if ($overlaps) {
                return false;
            }
        }

        return true;
    }

    public function appointmentsApi()
    {
        $today = Carbon::today();
        $todayAppointments = Appointment::with(['service', 'queue', 'dentist'])
            ->whereDate('appointment_date', $today)
            ->orderByRaw('CASE WHEN queues.queue_number IS NULL THEN 1 ELSE 0 END')
            ->leftJoin('queues', 'appointments.id', '=', 'queues.appointment_id')
            ->select('appointments.*')
            ->get();

        $queueEntries = Queue::with(['appointment.service'])
            ->whereHas('appointment', function ($query) use ($today) {
                $query->whereDate('appointment_date', $today);
            })
            ->whereNotNull('queue_number')
            ->orderBy('queue_number')
            ->get();

        $waitingTimeMap = [];
        $runningTotal = 0;
        $inServiceCount = 0;
        
        foreach ($queueEntries as $entry) {
            // For waiting patients, add cumulative time from all waiting + in_service ahead
            // For in_service patients, add only time from waiting patients ahead
            if ($entry->queue_status === 'waiting') {
                $waitingTimeMap[$entry->appointment_id] = $runningTotal;
                
                $duration = $entry->appointment && $entry->appointment->service
                    ? max((int) ($entry->appointment->service->estimated_duration ?? 0), 15)
                    : 15;
                $runningTotal += $duration;
            } elseif ($entry->queue_status === 'in_treatment') {
                $waitingTimeMap[$entry->appointment_id] = 0; // Currently being served
                $inServiceCount++;
            }
        }

        $stats = [
            'total' => $todayAppointments->count(),
            'queued' => $queueEntries->count(),
            'waiting' => $queueEntries->where('queue_status', 'waiting')->count(),
            'in_service' => $queueEntries->where('queue_status', 'in_treatment')->count(),
            'completed' => $queueEntries->where('queue_status', 'completed')->count(),
        ];

        $appointmentsData = $todayAppointments->map(function ($appointment) use ($waitingTimeMap) {
            $queue = $appointment->queue;
            $queueNumber = $queue?->queue_number ?? '—';
            $queueStatus = $queue?->queue_status ?? 'not-queued';
            $eta = $queue && isset($waitingTimeMap[$appointment->id]) ? $waitingTimeMap[$appointment->id] : '—';

            return [
                'id' => $appointment->id,
                'patient_name' => $appointment->patient_name,
                'visit_code' => $appointment->visit_code,
                'service' => $appointment->service?->name ?? $appointment->service?->service_name ?? '—',
                'dentist' => $appointment->dentist?->name ?? 'Auto-assign',
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'queue_number' => $queueNumber,
                'queue_status' => $queueStatus,
                'eta' => is_numeric($eta) ? $eta : '—',
                'checked_in_at' => $appointment->checked_in_at?->format('H:i'),
            ];
        });

        return response()->json([
            'stats' => $stats,
            'appointments' => $appointmentsData,
        ]);
    }

    /**
     * Show treatment completion page for dentist
     */
    public function completionPage()
    {
        $appointments = Appointment::with(['queue', 'service', 'dentist'])
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_time')
            ->get();

        // Get queue settings (pause/resume status)
        $queueSettings = DB::table('queue_settings')->first();
        $isPaused = $queueSettings->is_paused ?? false;
        $autoTransitionSeconds = $queueSettings->auto_transition_seconds ?? 30;

        // Get treatment rooms
        $treatmentRooms = DB::table('treatment_rooms')
            ->where('is_active', true)
            ->get();

        // Get current patient in treatment
        $currentPatient = Appointment::with(['queue', 'service', 'dentist'])
            ->whereDate('appointment_date', Carbon::today())
            ->where('status', 'in_treatment')
            ->first();

        // Get next patient (called or checked_in)
        $nextPatient = Appointment::with(['queue', 'service', 'dentist'])
            ->whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['called', 'checked_in'])
            ->orderByRaw("CASE WHEN status = 'called' THEN 1 WHEN status = 'checked_in' THEN 2 END ASC")
            ->first();

        // Get waiting count (patients waiting to be called)
        $waitingCount = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', 'checked_in')
            ->count();

        return view('staff.treatment-completion', compact(
            'appointments',
            'isPaused',
            'autoTransitionSeconds',
            'treatmentRooms',
            'currentPatient',
            'nextPatient',
            'waitingCount'
        ));
    }

    /**
     * Complete treatment for a patient (auto-progression)
     */
    public function completeTreatment(Request $request, $appointmentId)
    {
        $appointment = Appointment::with('queue')->findOrFail($appointmentId);
        
        if (!$appointment->queue) {
            return response()->json(['error' => 'Queue not found'], 404);
        }

        $request->validate([
            'treatment_room_id' => 'nullable|exists:treatment_rooms,id',
        ]);

        // Update room assignment if provided
        if ($request->treatment_room_id) {
            $appointment->queue->update(['treatment_room_id' => $request->treatment_room_id]);
        }

        // Mark treatment as completed
        $appointment->queue->update(['queue_status' => 'completed']);
        $appointment->update(['status' => 'completed']);

        ActivityLogger::log(
            'treatment_completed',
            'Appointment',
            $appointment->id,
            'Treatment completed for ' . $appointment->patient_name,
            null,
            ['dentist' => auth()->user()->name, 'completion_time' => now()]
        );

        // Get queue settings to check if paused
        $queueSettings = DB::table('queue_settings')->first();
        
        // Auto-call next patient if queue is not paused
        if (!$queueSettings->is_paused) {
            $this->callNextPatient();
        }

        return response()->json([
            'success' => true,
            'message' => 'Treatment marked as completed for ' . $appointment->patient_name
        ]);
    }

    /**
     * Auto-call next waiting patient
     */
    private function callNextPatient()
    {
        $today = Carbon::today();
        
        // Find next patient in checked_in status
        $nextPatient = Appointment::with(['queue', 'service'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'checked_in')
            ->orderBy('appointment_time')
            ->first();

        if ($nextPatient && $nextPatient->queue) {
            // Update status to 'called'
            $nextPatient->queue->update(['queue_status' => 'called', 'called_at' => now()]);
            $nextPatient->update(['status' => 'called']);

            // Send WhatsApp notification
            try {
                $whatsApp = new WhatsAppSender();
                $room = $nextPatient->queue->treatment_room_id 
                    ? DB::table('treatment_rooms')->find($nextPatient->queue->treatment_room_id)->room_code
                    : 'Waiting Area';
                
                $message = "Your turn! Please proceed to " . $room . ". Thank you!";
                $whatsApp->sendMessage($nextPatient->phone, $message);
            } catch (\Exception $e) {
                // Log error but don't fail
                logger()->error('WhatsApp notification failed: ' . $e->getMessage());
            }

            ActivityLogger::log(
                'patient_called',
                'Appointment',
                $nextPatient->id,
                'Patient ' . $nextPatient->patient_name . ' automatically called',
                null,
                ['called_time' => now()]
            );
        }
    }

    /**
     * Pause queue - stop auto-calling new patients
     */
    public function pauseQueue()
    {
        DB::table('queue_settings')->update([
            'is_paused' => true,
            'paused_at' => now()
        ]);

        ActivityLogger::log(
            'queue_paused',
            'Queue',
            1,
            'Queue processing paused',
            null,
            ['paused_by' => auth()->user()->name]
        );

        return response()->json(['success' => true, 'message' => 'Queue paused']);
    }

    /**
     * Resume queue - restart auto-calling
     */
    public function resumeQueue()
    {
        DB::table('queue_settings')->update([
            'is_paused' => false,
            'resumed_at' => now()
        ]);

        ActivityLogger::log(
            'queue_resumed',
            'Queue',
            1,
            'Queue processing resumed',
            null,
            ['resumed_by' => auth()->user()->name]
        );

        // Call next patient if any are waiting
        $this->callNextPatient();

        return response()->json(['success' => true, 'message' => 'Queue resumed']);
    }

    /**
     * Get current queue status via API
     */
    public function getQueueStatus()
    {
        $today = Carbon::today();
        $queueSettings = DB::table('queue_settings')->first();

        $currentPatient = Appointment::with(['queue', 'service'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'in_treatment')
            ->first();

        $calledPatient = Appointment::with(['queue', 'service'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'called')
            ->first();

        $waitingCount = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'checked_in')
            ->count();

        // Get next patients (called status patients)
        $nextPatients = Appointment::with(['queue', 'service'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'called')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient_name,
                    'queue_number' => $appointment->queue?->queue_number,
                    'phone' => $appointment->patient_phone,
                    'service_name' => $appointment->service->name ?? 'N/A',
                    'appointment_time' => $appointment->appointment_time
                ];
            });

        $totalCount = Appointment::whereDate('appointment_date', $today)
            ->whereIn('status', ['checked_in', 'called', 'in_treatment'])
            ->count();

        $status = $queueSettings->is_paused ? 'paused' : 'queue_active';
        if ($totalCount === 0 && !$currentPatient && !$calledPatient) {
            $status = 'queue_empty';
        }

        return response()->json([
            'status' => $status,
            'is_paused' => $queueSettings->is_paused,
            'current_patient' => $currentPatient ? [
                'id' => $currentPatient->id,
                'patient_name' => $currentPatient->patient_name,
                'phone' => $currentPatient->patient_phone,
                'service' => $currentPatient->service->name ?? 'N/A',
                'appointment_status' => $currentPatient->status,
                'room' => $currentPatient->queue && $currentPatient->queue->treatment_room_id 
                    ? DB::table('treatment_rooms')->find($currentPatient->queue->treatment_room_id)->room_code
                    : null
            ] : null,
            'called_patient' => $calledPatient ? [
                'id' => $calledPatient->id,
                'patient_name' => $calledPatient->patient_name,
                'phone' => $calledPatient->patient_phone,
                'service' => $calledPatient->service->name ?? 'N/A',
                'appointment_status' => $calledPatient->status,
                'room' => $calledPatient->queue && $calledPatient->queue->treatment_room_id 
                    ? DB::table('treatment_rooms')->find($calledPatient->queue->treatment_room_id)->room_code
                    : 'Waiting'
            ] : null,
            'waiting_count' => $waitingCount,
            'total_count' => $totalCount,
            'next_patients' => $nextPatients
        ]);
    }
}

