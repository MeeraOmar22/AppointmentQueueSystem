<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;
use App\Services\QueueAssignmentService;
use App\Services\AppointmentStateService;
use App\Services\WhatsAppSender;
use App\Services\EstimatedWaitTimeService;

class AppointmentController extends Controller
{
    // ⚠️ REMOVED: create() and store() methods - Use CalendarBookingController instead
    // This controller now focuses on tracking and queue operations
    // Public booking is now handled by CalendarBookingController (modern system)
    // See routes/web.php: GET /book → CalendarBookingController::showForm()

    public function trackSearch()
    {
        return view('public.track-search');
    }

    public function trackByCode(string $code)
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_code', $code)
            ->firstOrFail();

        [$queueNumber, $etaMinutes, $currentServing] = $this->computeQueueInfo($appointment);

        return view('public.track', [
            'appointment' => $appointment,
            'status' => $appointment->status,
            'queueNumber' => $queueNumber,
            'etaMinutes' => $etaMinutes,
            'currentServing' => $currentServing,
        ]);
    }

    public function queueBoard()
    {
        $data = $this->buildQueueBoardData();

        return view('public.queue-board', $data);
    }

    public function getQueueBoardData()
    {
        $data = $this->buildQueueBoardData();
        
        // Get queue pause status
        $queueSettings = DB::table('queue_settings')->first();
        $isPaused = $queueSettings?->is_paused ?? false;
        
        // Convert Collections to arrays for JSON response
        // This ensures proper serialization and prevents model relationship issues
        return response()->json([
            'inService' => $data['inService']->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'queue_status' => $queue->queue_status,
                    'appointment_id' => $queue->appointment?->id,  // CRITICAL: For callPatient API
                    'appointment' => [
                        'id' => $queue->appointment?->id,
                        'patient_name' => $queue->appointment?->patient_name,
                        'visit_code' => $queue->appointment?->visit_code,
                        'service' => $queue->appointment?->service?->name,
                    ],
                    'room' => [
                        'id' => $queue->room?->id,
                        'room_number' => $queue->room?->room_number,
                    ],
                    'dentist' => [
                        'id' => $queue->dentist?->id,
                        'name' => $queue->dentist?->name,
                    ],
                ];
            })->values()->all(),  // values() to reindex array
            'waiting' => $data['waiting']->map(function ($queue) {
                return [
                    'id' => $queue->id,
                    'queue_number' => $queue->queue_number,
                    'queue_status' => $queue->queue_status,
                    'appointment_id' => $queue->appointment?->id,  // CRITICAL: For callPatient API
                    'appointment' => [
                        'id' => $queue->appointment?->id,
                        'patient_name' => $queue->appointment?->patient_name,
                        'visit_code' => $queue->appointment?->visit_code,
                        'service' => $queue->appointment?->service?->name,
                    ],
                    'dentist' => [
                        'id' => $queue->dentist?->id,
                        'name' => $queue->dentist?->name,
                    ],
                ];
            })->values()->all(),  // values() to reindex array
            'dentists' => $data['dentists'],
            'rooms' => $data['rooms'],
            'currentNumber' => $data['currentNumber'],
            'stats' => $data['stats'],
            'exceptions' => $data['exceptions'],
            'timestamp' => $data['timestamp'],
            'isPaused' => $isPaused,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
         ->header('Pragma', 'no-cache')
         ->header('Expires', '0');
    }

    private function buildQueueBoardData(): array
    {
        $today = Carbon::today();
        $clinicLocation = config('clinic.location', 'seremban');

        // CRITICAL FIX: Ensure fresh data is loaded from database, not from model cache
        // When patient moves from waiting → in_treatment, we must query fresh state
        // The Queue and Appointment must BOTH be synchronized to the same status
        
        // Patients in treatment - Load complete relationships for dentist and room display
        // FIXED: Added explicit synchronization check to ensure queue_status and appointment.status match
        // FIX: Removed select() restrictions that were preventing relationship hydration
        // Foreign keys (dentist_id, room_id) must be loaded for Eloquent to resolve relationships
        // FIX #7: Filter by clinic_location to prevent cross-clinic data leaks
        // CRITICAL: Use fresh query (no cached models) to avoid stale data when status changes
        // FIX #8: Order by DESC to show the LATEST (highest queue number) patient in treatment first
        $inService = Queue::with(['appointment', 'room', 'dentist'])
            ->where('queue_status', 'in_treatment')  // Check queue status first (fastest)
            ->whereHas('appointment', function ($q) use ($today, $clinicLocation) {
                $q->whereDate('appointment_date', $today)
                  ->where('clinic_location', $clinicLocation)
                  ->where('status', 'in_treatment');  // Verify appointment is also in_treatment
            })
            ->orderBy('queue_number', 'desc')  // DESC: Show latest queue first (highest number = most recent)
            ->get();

        // Waiting patients - Load complete appointment and dentist relationships
        // FIXED: Query waiting status from queue first, then verify appointment status
        // FIX: Removed select() restrictions to ensure dentist relationship loads correctly
        // FIX #7: Filter by clinic_location to prevent cross-clinic data leaks
        // CRITICAL: Only show patients with status='waiting', not 'checked_in'
        // checked_in is a transient state before joining the queue - must transition to waiting first
        $waiting = Queue::with(['appointment.service', 'dentist'])
            ->where('queue_status', 'waiting')  // Primary filter: queue status
            ->whereHas('appointment', function ($q) use ($today, $clinicLocation) {
                $q->whereDate('appointment_date', $today)
                  ->where('clinic_location', $clinicLocation)
                  ->where('status', 'waiting');  // ONLY waiting status, not checked_in
            })
            ->orderBy('queue_number')
            ->get();

        // CRITICAL SYNC FIX: Handle any out-of-sync records


        // CRITICAL DEDUPLICATION: Ensure no queue appears in both lists
        // If a queue somehow ended up in both inService and waiting, remove from waiting
        $inServiceIds = $inService->pluck('id')->toArray();
        $waiting = $waiting->filter(function ($queue) use ($inServiceIds) {
            return !in_array($queue->id, $inServiceIds);
        })->values();  // Re-index after filtering

        // Completed today - Count appointments where treatment has been COMPLETED
        // NOTE: 'completed' status is transient - appointments transition through
        // completed → feedback_scheduled → feedback_sent
        // Use treatment_ended_at to identify completed appointments instead
        $completed = Appointment::whereDate('appointment_date', $today)
            ->where('clinic_location', $clinicLocation)
            ->whereNotNull('treatment_ended_at')
            ->count();

        // Get all dentists with their status
        // CRITICAL: Dentist availability is derived from active appointments, not from dentists.status
        $busyDentistIds = $inService->pluck('dentist_id')->unique();
        
        $dentists = collect(Dentist::where('status', 1)
            ->select('id', 'name', 'status')
            ->get()
            ->map(function ($dentist) use ($busyDentistIds) {
                // CRITICAL: A dentist is "busy" ONLY if they have an active in_treatment appointment
                $isInTreatment = $busyDentistIds->contains($dentist->id);
                return [
                    'id' => $dentist->id,
                    'name' => $dentist->name,
                    'status' => $isInTreatment ? 'busy' : 'available',
                ];
            })->toArray());
        
        // ENHANCEMENT: Also include dentists from waiting/in-treatment appointments who may not be in the dentist table
        // This ensures real-time accuracy when staff members appear in appointments but not in main dentist list
        $appointmentDentistIds = collect()
            ->merge($inService->pluck('dentist_id'))
            ->merge($waiting->pluck('dentist_id'))
            ->filter()
            ->unique();
        
        // Add missing dentists from appointments
        $missingDentistIds = $appointmentDentistIds->diff($dentists->pluck('id'));
        if ($missingDentistIds->isNotEmpty()) {
            $appointmentDentists = Appointment::whereIn('dentist_id', $missingDentistIds)
                ->with('dentist')
                ->get()
                ->pluck('dentist')
                ->unique('id')
                ->map(function ($dentist) use ($busyDentistIds) {
                    return [
                        'id' => $dentist->id,
                        'name' => $dentist->name,
                        'status' => $busyDentistIds->contains($dentist->id) ? 'busy' : 'available',
                    ];
                });
            
            $dentists = $dentists->merge($appointmentDentists)->unique('id');
        }

        $exceptions = $this->buildQueueExceptions($clinicLocation, $today);

        // Get all active rooms with their status
        $rooms = Room::where('clinic_location', $clinicLocation)
            ->where('is_active', true)
            ->select('id', 'room_number', 'status')
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) use ($inService) {
                // Check if room is currently occupied
                $isOccupied = $inService->where('room_id', $room->id)->isNotEmpty();
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'status' => $isOccupied ? 'busy' : 'available',
                ];
            });

        $currentNumber = optional($inService->first())->queue_number;

        // Keep as Collections for Blade views to use ->first() and other collection methods
        // The JSON response endpoints will handle conversion as needed
        return [
            'inService' => $inService,
            'waiting' => $waiting,
            'dentists' => $dentists,
            'rooms' => $rooms,
            'currentNumber' => $currentNumber,
            'stats' => [
                'waitingCount' => $waiting->count(),
                'inServiceCount' => $inService->count(),
                'disabledRoomCount' => $exceptions['disabledRooms']->count(),
                'completedCount' => $completed,
                'available_dentists' => $dentists->where('status', 'available')->count(),
            ],
            'exceptions' => $exceptions,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];
    }

    private function buildQueueExceptions(string $clinicLocation, Carbon $today): array
    {
        $disabledRooms = Room::where('clinic_location', $clinicLocation)
            ->where('is_active', false)
            ->orderBy('room_number')
            ->get(['id', 'room_number', 'status']);

        $recentCancellations = Appointment::with('queue')
            ->whereDate('appointment_date', $today)
            ->where('status', 'cancelled')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function (Appointment $appointment) {
                return [
                    'patient_name' => $appointment->patient_name,
                    'queue_number' => $appointment->queue?->queue_number,
                    'cancelled_at' => optional($appointment->updated_at)->format('H:i'),
                    'visit_code' => $appointment->visit_code,
                ];
            });

        return [
            'disabledRooms' => $disabledRooms,
            'recentCancellations' => $recentCancellations,
        ];
    }


    // ⚠️ REMOVED: dentistIsAvailable() - Now handled by AvailabilityService
    // Use CalendarBookingController and AvailabilityService for availability checks

    // ⚠️ REMOVED: assignQueueAndRoom() and chooseRoomForToday() 
    // These are handled by QueueAssignmentService in the modern system

    /**
     * READONLY METHOD - Computes queue info from database WITHOUT modifying state
     * 
     * Returns:
     * - Queue number (only if CHECKED_IN or later)
     * - ETA minutes (dynamic, based on consolidated EstimatedWaitTimeService)
     * - Current serving queue number (lowest queue IN_TREATMENT today)
     */
    private function computeQueueInfo(Appointment $appointment): array
    {
        // Only show queue number if appointment has been checked in (status >= checked_in)
        $hasCheckedIn = in_array($appointment->status->value, [
            Appointment::STATE_CHECKED_IN,
            Appointment::STATE_WAITING,
            Appointment::STATE_IN_TREATMENT,
            Appointment::STATE_COMPLETED,
        ]);
        $queueNumber = $hasCheckedIn ? $appointment->queue?->queue_number : null;

        // "Now Serving" = Lowest queue number currently IN_TREATMENT today
        // Format with "A-" prefix for display (e.g., "A-02")
        $currentServingNum = Queue::whereHas('appointment', function ($q) use ($appointment) {
            $q->whereDate('appointment_date', $appointment->appointment_date)
              // Exclude completed patients (they already finished)
              ->where('status', '!=', Appointment::STATE_COMPLETED)
              ->where('status', '!=', Appointment::STATE_FEEDBACK_SENT);
        })
            ->where('queue_status', 'in_treatment')
            ->orderBy('queue_number')
            ->first()?->queue_number;

        // Format for display
        $currentServing = $currentServingNum ? 'A-' . str_pad($currentServingNum, 2, '0', STR_PAD_LEFT) : null;

        // ✅ CONSOLIDATED: Use EstimatedWaitTimeService for all ETA calculations
        // This ensures CONSISTENCY across staff dashboard, public queue board, and patient tracking
        // Service handles all cases: waiting, in_treatment, booked, etc.
        $etaService = new EstimatedWaitTimeService();
        $etaMinutes = $etaService->getETAForAppointment($appointment);

        return [$queueNumber, $etaMinutes, $currentServing];
    }

    /**
     * Validate that check-in is within allowed time window
     * Allow check-in: 30 minutes before to 2 hours after appointment time
     */
    private function isWithinCheckInWindow(Appointment $appointment): array
    {
        $now = Carbon::now();
        
        // Ensure appointment is for today
        $appointmentDateString = $appointment->appointment_date instanceof \DateTime 
            ? $appointment->appointment_date->format('Y-m-d')
            : $appointment->appointment_date;
        
        Log::info('📅 Checking appointment date', [
            'appointmentDate' => $appointmentDateString,
            'today' => Carbon::today()->toDateString(),
            'match' => $appointmentDateString === Carbon::today()->toDateString()
        ]);
        
        if (!$appointmentDateString || $appointmentDateString !== Carbon::today()->toDateString()) {
            Log::info('❌ Appointment not for today', ['appointmentDate' => $appointmentDateString]);
            return [
                'valid' => false,
                'message' => 'Check-in is only available on the day of your appointment.'
            ];
        }
        
        // Parse appointment time - ensure appointment_time is just the time part (H:i:s format)
        $appointmentTime = Carbon::parse($appointmentDateString . ' ' . $appointment->appointment_time);
        
        // Calculate check-in window: 30 minutes before to 2 hours after
        $checkInWindowStart = (clone $appointmentTime)->subMinutes(30);
        $checkInWindowEnd = (clone $appointmentTime)->addHours(2);
        
        Log::info('⏰ Check-in window calculated', [
            'now' => $now->format('Y-m-d H:i:s'),
            'appointmentTime' => $appointmentTime->format('Y-m-d H:i:s'),
            'windowStart' => $checkInWindowStart->format('Y-m-d H:i:s'),
            'windowEnd' => $checkInWindowEnd->format('Y-m-d H:i:s'),
            'tooEarly' => $now->lt($checkInWindowStart),
            'tooLate' => $now->gt($checkInWindowEnd)
        ]);
        
        // Check if current time is within window
        if ($now->lt($checkInWindowStart)) {
            // Too early - generate user-friendly message
            $windowOpenTime = $checkInWindowStart->format('g:i A');
            $appointmentTimeFormatted = $appointmentTime->format('g:i A');
            $message = "Check-in opens 30 minutes before your appointment. Your appointment is at {$appointmentTimeFormatted}. Please check in after {$windowOpenTime}.";
            Log::info('⏳ Check-in window not open (too early)', ['message' => $message]);
            return [
                'valid' => false,
                'message' => $message
            ];
        }
        
        if ($now->gt($checkInWindowEnd)) {
            $message = 'Check-in window has closed. Your appointment was at ' . $appointmentTime->format('g:i A') . '. Please contact the clinic.';
            Log::info('⏳ Check-in window closed (too late)', ['message' => $message]);
            return [
                'valid' => false,
                'message' => $message
            ];
        }
        
        Log::info('✅ Check-in window is OPEN - check-in allowed');
        return ['valid' => true, 'message' => null];
    }

    /**
     * Show check-in confirmation page
     * GET /appointment/check-in/{code}
     */
    public function showCheckInConfirmation(string $code)
    {
        $appointment = Appointment::with(['service', 'dentist'])
            ->where('visit_code', $code)
            ->orWhere('visit_token', $code)
            ->firstOrFail();

        // If already checked in, redirect to tracking page
        if ($appointment->checked_in_at !== null) {
            return redirect("/track/{$appointment->visit_code}")
                ->with('info', 'You have already checked in.');
        }

        return view('public.check-in-confirm', [
            'appointment' => $appointment,
        ]);
    }

    public function publicCheckIn(string $code)
    {
        Log::info('🔘 Check-in request received', ['code' => $code, 'ip' => request()->ip()]);

        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_code', $code)
            ->orWhere('visit_token', $code)
            ->firstOrFail();

        Log::info('📋 Appointment found', ['id' => $appointment->id, 'patient' => $appointment->patient_name, 'status' => $appointment->status->value]);

        // ❌ PREVENTION: Check if already checked in
        if ($appointment->checked_in_at !== null) {
            Log::info('⚠️  Already checked in', ['appointment_id' => $appointment->id]);
            return redirect()->to('/track/' . $appointment->visit_code)
                ->with('warning', 'You have already checked in. Thank you!');
        }

        // ❌ PREVENTION: Check if queue is paused - patients cannot check in when paused
        $queueSettings = DB::table('queue_settings')->first();
        if ($queueSettings && $queueSettings->is_paused) {
            Log::info('⏸️  Queue paused, blocking check-in', ['appointment_id' => $appointment->id]);
            return redirect()->to('/track/' . $appointment->visit_code)
                ->with('error', 'Queue is currently paused. Please wait for it to resume before checking in.');
        }

        // ✅ VALIDATION: Ensure check-in is within allowed time window
        $windowCheck = $this->isWithinCheckInWindow($appointment);
        if (!$windowCheck['valid']) {
            Log::info('❌ Check-in window closed', [
                'appointment_id' => $appointment->id,
                'message' => $windowCheck['message']
            ]);
            return redirect()->to('/track/' . $appointment->visit_code)
                ->with('error', $windowCheck['message']);
        }

        Log::info('✅ All validations passed, proceeding with check-in', ['appointment_id' => $appointment->id]);

        $oldStatus = $appointment->status;

        // ✅ Perform check-in via state machine
        // This transitions: BOOKED/CONFIRMED/CHECKED_IN → CHECKED_IN → WAITING
        // onCheckedIn() handler automatically creates queue and transitions to WAITING
        $stateService = app(AppointmentStateService::class);
        $success = $stateService->transitionTo($appointment, 'checked_in', 'Checked in via public portal');
        
        if (!$success) {
            Log::error('❌ Check-in state transition failed', ['appointment_id' => $appointment->id]);
            return redirect()->to('/track/' . $appointment->visit_code)
                ->with('error', 'Check-in failed. Please try again or contact the clinic.');
        }

        // CRITICAL: Refresh appointment from database to get updated status and queue
        $appointment->refresh();

        ActivityLogger::log(
            'checked_in',
            'Appointment',
            $appointment->id,
            'Patient ' . $appointment->patient_name . ' checked in via public portal',
            ['status' => $oldStatus->value],
            ['status' => $appointment->status->value, 'check_in_time' => $appointment->checked_in_at]
        );

        // Check if queue is paused and flash pause message if needed
        $queueSettings = DB::table('queue_settings')->first();
        if ($queueSettings && $queueSettings->is_paused && $appointment->queue) {
            return redirect()->to('/track/' . $appointment->visit_code)
                ->with('pause_alert', "Queue is currently paused. You are #{$appointment->queue->queue_number} in queue. Staff will call you when queue resumes.");
        }

        return redirect()->to('/track/' . $appointment->visit_code);
    }

    public function trackByCodeApi(string $code)
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_code', $code)
            ->firstOrFail();

        [$queueNumber, $etaMinutes, $currentServing] = $this->computeQueueInfo($appointment);

        // Only show queue number if CHECKED_IN or later
        $showQueueNumber = in_array($appointment->status->value, [
            Appointment::STATE_CHECKED_IN,
            Appointment::STATE_WAITING,
            Appointment::STATE_IN_TREATMENT,
            Appointment::STATE_COMPLETED,
        ]);

        // Only show room/dentist if IN_TREATMENT
        $showTreatmentInfo = $appointment->status->value === Appointment::STATE_IN_TREATMENT;

        // Check queue pause status
        $queueSettings = DB::table('queue_settings')->first();
        $isPaused = $queueSettings?->is_paused ?? false;
        $pauseMessage = null;
        
        if ($isPaused && $showQueueNumber && $queueNumber) {
            $pauseMessage = "Queue is currently paused. You are #{$queueNumber} in queue. Staff will call you when queue resumes.";
        }

        return response()->json([
            'patient_name' => $appointment->patient_name,
            'service' => $appointment->service?->name ?? $appointment->service?->service_name ?? '—',
            'dentist' => $showTreatmentInfo ? ($appointment->dentist?->name ?? 'TBD') : null,
            'status' => $appointment->status->value,  // ← FIX: Return string value, not Enum object
            'queue_number' => $showQueueNumber ? $queueNumber : null,
            'eta_minutes' => $etaMinutes,
            'current_serving' => $currentServing,
            'room' => $showTreatmentInfo ? ($appointment->room ?? '—') : null,
            'queue_paused' => $isPaused,
            'pause_message' => $pauseMessage,
        ]);
    }

    /**
     * TEST: Create a test appointment for feedback testing
     */
    public function testFeedbackSetup(Request $request)
    {
        $phone = $request->validate(['phone' => 'required|string'])['phone'];
        
        $service = Service::where('status', 1)->first();
        $dentist = Dentist::where('status', 1)->first();
        
        if (!$service || !$dentist) {
            return back()->with('error', 'No services or dentists available. Please set up clinic data first.');
        }
        
        $appointment = Appointment::create([
            'patient_name' => 'Test Patient ' . now()->format('Hi'),
            'patient_phone' => $phone,
            'patient_email' => 'test-' . now()->timestamp . '@example.com',
            'service_id' => $service->id,
            'dentist_id' => $dentist->id,
            'appointment_date' => now(),
            'appointment_time' => now(),
            'clinic_location' => 'seremban',
            'status' => 'in_treatment',
            'visit_code' => 'VTEST' . strtoupper(substr(uniqid(), -6)),
        ]);
        
        return back()->with('success', "Test appointment created: {$appointment->visit_code}. Click 'Complete' to send feedback link.");
    }

    /**
     * TEST: Mark appointment as completed and send feedback link
     */
    public function testFeedbackComplete(Request $request)
    {
        $appointmentId = $request->validate(['appointment_id' => 'required|exists:appointments,id'])['appointment_id'];
        
        $appointment = Appointment::findOrFail($appointmentId);
        
        // Update status to completed (this triggers the state machine)
        $appointment->update(['status' => 'completed']);
        
        // The AppointmentStateService should handle sending the feedback link
        // But for testing, we'll send it directly
        try {
            $whatsAppSender = new \App\Services\WhatsAppSender();
            $whatsAppSender->sendFeedbackLink($appointment);
            
            $message = "✅ WhatsApp feedback link sent to {$appointment->patient_phone}! ";
            $message .= "You should receive it in WhatsApp within 10 seconds.";
        } catch (\Exception $e) {
            $message = "⚠️ WhatsApp not configured. But feedback form is still accessible via direct link. ";
            $message .= "Visit code: {$appointment->visit_code}";
        }
        
        return back()->with('success', $message);
    }

    /**
     * POST /api/queue-board/reassign-dentist
     * Reassign dentist to a waiting patient (emergency/manual override)
     * 
     * Only allows reassignment for waiting patients
     * Updates both Appointment.dentist_id and Queue.dentist_id
     * Logs the change for audit trail
     */
    public function reassignDentist(Request $request)
    {
        try {
            $appointmentId = $request->input('appointment_id');
            $dentistId = $request->input('dentist_id');

            // Validate inputs
            if (!$appointmentId || !$dentistId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing appointment_id or dentist_id'
                ], 400);
            }

            // Get the appointment
            $appointment = Appointment::findOrFail($appointmentId);

            // CRITICAL: Only allow reassignment for waiting patients
            if ($appointment->status->value !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only reassign waiting patients. Current status: ' . $appointment->status->value
                ], 403);
            }

            // Verify clinic location matches
            $clinicLocation = config('clinic.location', 'seremban');
            if ($appointment->clinic_location !== $clinicLocation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reassign patient from different clinic'
                ], 403);
            }

            // Verify dentist exists
            $dentist = Dentist::findOrFail($dentistId);

            // Get the queue record
            $queue = Queue::where('appointment_id', $appointmentId)->first();
            if (!$queue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue record not found for appointment'
                ], 404);
            }

            // Update appointment dentist
            $oldDentistId = $appointment->dentist_id;
            $appointment->update(['dentist_id' => $dentistId]);

            // Update queue dentist
            $queue->update(['dentist_id' => $dentistId]);

            // Log the change for audit trail
            if (class_exists(ActivityLogger::class)) {
                $logger = app(ActivityLogger::class);
                $logger->log(
                    auth()->user()->id,
                    'dentist_reassignment',
                    'appointment',
                    $appointmentId,
                    [
                        'old_dentist_id' => $oldDentistId,
                        'new_dentist_id' => $dentistId,
                        'old_dentist_name' => optional(Dentist::find($oldDentistId))->name ?? 'Unassigned',
                        'new_dentist_name' => $dentist->name,
                        'patient_name' => $appointment->patient_name,
                        'reason' => $request->input('reason', 'Manual reassignment'),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "✅ Reassigned to {$dentist->name}",
                'appointment_id' => $appointmentId,
                'dentist_id' => $dentistId,
                'dentist_name' => $dentist->name
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment or Dentist not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error reassigning dentist', [
                'appointment_id' => $appointmentId ?? null,
                'dentist_id' => $dentistId ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reassigning dentist: ' . $e->getMessage()
            ], 500);
        }
    }
}
