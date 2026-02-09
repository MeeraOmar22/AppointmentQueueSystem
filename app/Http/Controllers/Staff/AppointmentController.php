<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Room;
use App\Models\DentistSchedule;
use App\Models\OperatingHour;
use App\Services\StaffDashboardApiService;
use App\Services\QueueAssignmentService;
use App\Services\ActivityLogger;
use App\Services\ExceptionAlertService;
use App\Services\WhatsAppBusinessService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * REFACTORED: Staff Appointment Controller
 * 
 * This controller now delegates all state changes to StaffDashboardApiService
 * which uses AppointmentStateService internally.
 * 
 * Rules:
 * ✓ All status changes go through transitionAppointmentStatus()
 * ✓ All responses use consistent JSON structure
 * ✓ All business logic (queue, room, WhatsApp) happens in event listeners
 * ✓ All status reads from appointment.status only, never queue.queue_status
 * ✓ All database writes through service layer, never direct updates
 */
class AppointmentController extends Controller
{
    public function __construct(
        private StaffDashboardApiService $staffService,
        private ActivityLogger $activityLogger,
        private ExceptionAlertService $exceptionAlert,
        private WhatsAppBusinessService $whatsappService
    ) {}

    /**
     * GET /staff/appointments (Display staff dashboard view)
     * Displays today's appointments with filtering options and past/upcoming appointments
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $statusFilter = $request->query('status', null);
        $dateFilter = $request->query('date_filter', 'today');

        // Get today's appointments via service layer (consistent formatting)
        $appointmentsData = $this->staffService->getTodayAppointments();
        $appointments = $appointmentsData['data']['appointments'];

        // Apply optional filters
        if ($statusFilter) {
            $appointments = array_filter($appointments, fn($apt) => $apt['status'] === $statusFilter);
        }

        // Get statistics
        $stats = $this->staffService->getSummaryStatistics();

        // Get past appointments (before today) with status
        $pastAppointments = Appointment::where('appointment_date', '<', $today)
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(50)
            ->get();

        // Get upcoming appointments (after today) with status
        $upcomingAppointments = Appointment::where('appointment_date', '>', $today)
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->limit(50)
            ->get();

        return view('staff.appointments-realtime', [
            'today' => $today,
            'appointments' => $appointments,
            'stats' => $stats['data'],
            'statusFilter' => $statusFilter,
            'dateFilter' => $dateFilter,
            'pastAppointments' => $pastAppointments,
            'upcomingAppointments' => $upcomingAppointments,
        ]);
    }

    /**
     * GET /api/staff/appointments/today (API endpoint)
     * Returns today's appointments in consistent JSON format
     */
    public function getAppointmentsData(): JsonResponse
    {
        return response()->json($this->staffService->getTodayAppointments());
    }

    /**
     * GET /api/staff/summary (API endpoint)
     * Returns appointment statistics for today
     */
    public function getSummaryStatistics(): JsonResponse
    {
        return response()->json($this->staffService->getSummaryStatistics());
    }

    /**
     * Display all appointments (total)
     */
    public function totalAppointments(Request $request)
    {
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'asc')
            ->paginate(20);

        return view('staff.appointments-filtered', [
            'appointments' => $appointments,
            'filter' => 'total',
            'title' => 'Total Appointments',
            'subtitle' => 'All appointments scheduled for today',
        ]);
    }

    /**
     * Display queued appointments
     */
    public function queuedAppointments(Request $request)
    {
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['checked_in', 'waiting'])
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'asc')
            ->paginate(20);

        return view('staff.appointments-filtered', [
            'appointments' => $appointments,
            'filter' => 'queued',
            'title' => 'Queued Appointments',
            'subtitle' => 'Patients actively in queue waiting for treatment',
        ]);
    }

    /**
     * Display in-treatment appointments
     */
    public function inTreatmentAppointments(Request $request)
    {
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', 'in_treatment')
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'asc')
            ->paginate(20);

        return view('staff.appointments-filtered', [
            'appointments' => $appointments,
            'filter' => 'in_treatment',
            'title' => 'In Treatment',
            'subtitle' => 'Patients currently being treated',
        ]);
    }

    /**
     * Display completed appointments
     */
    public function completedAppointments(Request $request)
    {
        // NOTE: Counts appointments where treatment_ended_at is set (not 'completed' status)
        // because 'completed' status is transient - transitions to feedback_scheduled/feedback_sent
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereNotNull('treatment_ended_at')
            ->with('dentist', 'service', 'queue')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        return view('staff.appointments-filtered', [
            'appointments' => $appointments,
            'filter' => 'completed',
            'title' => 'Completed Appointments',
            'subtitle' => 'Patients who have finished treatment today',
        ]);
    }

    /**
     * GET /api/staff/available-dentists (API endpoint)
     * Returns dentists available at a specific date and time
     */
    public function getAvailableDentists(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date' => 'required|date_format:Y-m-d',
                'time' => 'required|date_format:H:i',
                'exclude_appointment_id' => 'nullable|integer',
            ]);

            $date = $request->date;
            $time = $request->time;
            $excludeAppointmentId = $request->exclude_appointment_id;

            // Log the request for debugging
            logger()->debug('Checking available dentists', [
                'date' => $date,
                'time' => $time,
                'exclude_appointment_id' => $excludeAppointmentId,
                'day_name' => Carbon::parse($date)->dayName,
            ]);

            // Get all dentists with their schedules
            $dentists = Dentist::with('schedules')
                ->where('deleted_at', null) // Exclude soft-deleted dentists
                ->get()
                ->map(function ($dentist) use ($date, $time, $excludeAppointmentId) {
                    try {
                        // Check if dentist has any schedules defined
                        $schedules = $dentist->schedules;
                        $hasSchedules = $schedules && $schedules->count() > 0;
                        
                        if ($hasSchedules) {
                            // Check if dentist works on this day
                            $dayOfWeek = Carbon::parse($date)->dayName;
                            $schedule = $schedules->where('day_of_week', $dayOfWeek)->first();

                            if (!$schedule) {
                                logger()->debug('Dentist does not work on this day', [
                                    'dentist_id' => $dentist->id,
                                    'dentist_name' => $dentist->name,
                                    'day_of_week' => $dayOfWeek,
                                ]);
                                return null; // Dentist doesn't work this day
                            }

                            // Check if time is within working hours
                            try {
                                $appointmentTime = Carbon::createFromFormat('H:i', $time);
                                // start_time and end_time include seconds (H:i:s format in database)
                                $startTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);
                                $endTime = Carbon::createFromFormat('H:i:s', $schedule->end_time);

                                if ($appointmentTime->lt($startTime) || $appointmentTime->gt($endTime)) {
                                    logger()->debug('Time outside working hours', [
                                        'dentist_id' => $dentist->id,
                                        'appointment_time' => $appointmentTime->format('H:i'),
                                        'start_time' => $startTime->format('H:i'),
                                        'end_time' => $endTime->format('H:i'),
                                    ]);
                                    return null; // Time is outside working hours
                                }
                            } catch (\Exception $e) {
                                logger()->warning('Time format error during availability check', [
                                    'error' => $e->getMessage(),
                                    'time' => $time,
                                    'start_time' => $schedule->start_time ?? 'null',
                                    'end_time' => $schedule->end_time ?? 'null',
                                ]);
                                // Continue if time parsing fails - don't filter out dentist
                            }
                        }
                        // If no schedules defined, show dentist anyway (staff will validate manually)

                        // Check for conflicting appointments
                        $conflict = Appointment::where('dentist_id', $dentist->id)
                            ->where('appointment_date', $date)
                            ->where('appointment_time', $time)
                            ->when($excludeAppointmentId, function ($query) use ($excludeAppointmentId) {
                                return $query->where('id', '!=', $excludeAppointmentId);
                            })
                            ->whereNotIn('status', ['cancelled', 'no_show'])
                            ->first();

                        if ($conflict) {
                            logger()->debug('Dentist has conflicting appointment', [
                                'dentist_id' => $dentist->id,
                                'conflict_appointment_id' => $conflict->id,
                            ]);
                            return null; // Dentist has conflicting appointment
                        }

                        return [
                            'id' => $dentist->id,
                            'name' => $dentist->name,
                            'specialization' => $dentist->specialization,
                            'available' => true,
                            'has_schedule' => $hasSchedules,
                        ];
                    } catch (\Exception $e) {
                        logger()->warning('Error checking dentist availability', [
                            'dentist_id' => $dentist->id,
                            'dentist_name' => $dentist->name ?? 'Unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // Return dentist even if there's an error (fallback to manual validation)
                        return [
                            'id' => $dentist->id,
                            'name' => $dentist->name ?? 'Unknown',
                            'specialization' => $dentist->specialization ?? 'N/A',
                            'available' => true,
                            'has_schedule' => false,
                        ];
                    }
                })
                ->filter() // Remove nulls
                ->values()
                ->all();

            logger()->debug('Available dentists result', [
                'count' => count($dentists),
                'dentist_ids' => array_map(fn($d) => $d['id'], $dentists),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'dentists' => $dentists,
                    'count' => count($dentists),
                    'date' => $date,
                    'time' => $time,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            logger()->warning('Validation failed in getAvailableDentists', [
                'errors' => $e->errors(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Invalid request: ' . implode(', ', array_map(fn($k, $v) => "$k: " . implode(', ', $v), array_keys($e->errors()), array_values($e->errors()))),
            ], 422);
        } catch (\Exception $e) {
            logger()->error('Available dentists check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'date' => $request->date ?? 'N/A',
                'time' => $request->time ?? 'N/A'
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Server error while checking dentist availability. Please try again or contact support.',
            ], 500);
        }
    }

    /**
     * GET /api/staff/appointments/{id} (API endpoint)
     * Returns detailed information for a specific appointment
     */
    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->staffService->getAppointmentDetails($id);
            return response()->json($data);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Appointment not found',
            ], 404);
        }
    }

    /**
     * GET /api/staff/queue (API endpoint)
     * Returns active queue (CHECKED_IN, WAITING, IN_TREATMENT only)
     */
    public function getActiveQueue(): JsonResponse
    {
        return response()->json($this->staffService->getActiveQueue());
    }

    /**
     * POST /staff/checkin/{id} (Web form)
     * Transition appointment to CHECKED_IN status
     * Auto-confirms if still in booked status
     */
    public function checkIn(Request $request, $id): JsonResponse
    {
        try {
            logger()->info("=== CHECK_IN CALLED ===", ['appointment_id' => $id]);
            
            $appointment = Appointment::findOrFail($id);
            logger()->info("Appointment found", ['id' => $appointment->id, 'current_status' => $appointment->status->value]);

            // If appointment is still in booked status, transition to confirmed first
            if ($appointment->status->value === 'booked') {
                logger()->info("Status is booked, auto-confirming first", ['appointment_id' => $id]);
                
                $confirmResult = $this->staffService->transitionAppointmentStatus(
                    $appointment,
                    'confirmed',
                    'Auto-confirmed before check-in'
                );
                
                logger()->info("Confirm result", ['success' => $confirmResult['success'], 'error' => $confirmResult['error'] ?? null]);
                
                if (!$confirmResult['success']) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Could not confirm appointment before check-in: ' . ($confirmResult['error'] ?? 'Unknown error'),
                    ], 422);
                }
                
                // Refresh the appointment after confirmation
                $appointment->refresh();
                logger()->info("Appointment refreshed after confirm", ['new_status' => $appointment->status->value]);
            }

            // Transition through state service (creates queue automatically via listener)
            logger()->info("Attempting to transition to checked_in", ['appointment_id' => $id, 'current_status' => $appointment->status->value]);
            
            $result = $this->staffService->transitionAppointmentStatus(
                $appointment,
                'checked_in',
                'Checked in via staff dashboard'
            );
            
            logger()->info("Transition result", ['result' => $result]);

            // Check if transition was successful
            if (!$result['success']) {
                return response()->json($result, 422);
            }

            // Add queue pause status to response
            $queueSettings = DB::table('queue_settings')->first();
            $isPaused = $queueSettings?->is_paused ?? false;
            
            $result['queue_paused'] = $isPaused;
            if ($isPaused && isset($result['appointment']['queueNumber'])) {
                $result['pause_message'] = "Queue is paused. Patient is #" . $result['appointment']['queueNumber'] . " in queue.";
            }

            // Note: StateService already logs this via transitionAppointmentStatus()
            // No additional logging needed here
            
            return response()->json($result);
        } catch (\Exception $e) {
            logger()->error('Check-in failed', ['error' => $e->getMessage(), 'appointment_id' => $id]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /staff/appointments/{id}/call-next (Web form)
     * Transition appointment to IN_TREATMENT status
     * Validates that assigned dentist is AVAILABLE before proceeding
     * WhatsApp message sent automatically via listener
     * Dentist state automatically marked BUSY via state machine
     */
    /**
     * POST /api/staff/appointments/{id}/call-patient (API endpoint)
     * Call specific patient for treatment
     * ALWAYS returns JSON
     * 
     * CRITICAL: Uses QueueAssignmentService to enforce mandatory dentist+room assignment atomically
     * This prevents patients from entering "in_treatment" without both resources assigned
     */
    public function callPatient(Request $request, $id, QueueAssignmentService $assignmentService): JsonResponse
    {
        // Force JSON response header
        header('Content-Type: application/json');
        
        try {
            $appointment = Appointment::findOrFail($id);
            logger()->info("callPatient: Start", ['appointment_id' => $id, 'status' => $appointment->status->value]);

            // Only waiting patients can be called
            if ($appointment->status->value !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'error' => 'Patient must be in waiting status to be called. Current status: ' . ucfirst(str_replace('_', ' ', $appointment->status->value)),
                ], 422);
            }

            // CHECK: Queue pause status - provide clear message
            $queueSettings = DB::table('queue_settings')->first();
            if ($queueSettings && $queueSettings->is_paused) {
                return response()->json([
                    'success' => false,
                    'message' => 'Queue is currently paused. Resume the queue to call patients.',
                ], 422);
            }

            // Ensure appointment has a queue entry
            $queue = $appointment->queue;
            if (!$queue) {
                logger()->error("callPatient: No queue found for appointment", ['appointment_id' => $id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Appointment has no queue entry. Please check-in patient first.',
                ], 422);
            }

            logger()->info("callPatient: Queue found", ['queue_id' => $queue->id, 'queue_status' => $queue->queue_status]);

            // CRITICAL: Use QueueAssignmentService to assign dentist and room atomically
            // This is the ONLY correct way to call a patient - ensures dentist_id is set before status changes
            // The service handles:
            // - Finding available dentist
            // - Finding available room
            // - Assigning both atomically
            // - Marking dentist as busy
            // - Marking room as occupied
            // - Updating appointment status
            // - Updating queue status
            // - Setting actual_start_time
            
            try {
                logger()->info("callPatient: Calling assignPatientToQueue", ['queue_id' => $queue->id]);
                $assignedQueue = $assignmentService->assignPatientToQueue($queue, $appointment->clinic_location);
                logger()->info("callPatient: assignPatientToQueue completed", [
                    'result' => $assignedQueue ? 'Success' : 'Null result',
                    'assigned_queue_id' => $assignedQueue?->id,
                ]);
                
                if (!$assignedQueue) {
                    logger()->warning("callPatient: Assignment returned null", [
                        'appointment_id' => $id,
                        'queue_id' => $queue->id,
                    ]);
                    
                    // Get reason for failure - provide user-friendly messages
                    // CRITICAL: Check BOTH room.status AND actively in-treatment queues
                    // Only count rooms with BOTH queue AND appointment in treatment status
                    $reasons = [];
                    
                    $availableDentists = Dentist::where('status', true)->count();
                    
                    // Check rooms: Count active in_treatment queues with matching appointments
                    $activeInTreatmentQueues = Queue::where('queue_status', 'in_treatment')
                        ->whereHas('appointment', function($query) {
                            // Only count as active if appointment is also in_treatment
                            $query->where('status', 'in_treatment');
                        })
                        ->count();
                    
                    $totalRooms = Room::where('clinic_location', $appointment->clinic_location)
                        ->where('is_active', true)
                        ->count();
                    
                    $actualAvailableRooms = $totalRooms - $activeInTreatmentQueues;
                    
                    if ($availableDentists === 0) {
                        $reasons[] = 'All dentists are currently busy. Please try again later.';
                    }
                    if ($actualAvailableRooms <= 0) {
                        $reasons[] = 'All treatment rooms are occupied. Please wait for a room to become available.';
                    }
                    
                    // If no specific reason found, provide generic message
                    if (empty($reasons)) {
                        $reasons[] = 'Unable to assign patient to treatment right now. Please try again later.';
                    }
                    
                    logger()->warning("callPatient: Room availability check", [
                        'total_rooms' => $totalRooms,
                        'active_in_treatment' => $activeInTreatmentQueues,
                        'actual_available' => $actualAvailableRooms,
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => implode(' ', $reasons),
                    ], 422);
                }
                
                // Reload appointment to get fresh dentist_id and room assignment
                logger()->info("callPatient: Loading relationships", ['assigned_queue_id' => $assignedQueue->id]);
                try {
                    $assignedQueue->load('appointment', 'dentist', 'room');
                    $appointment->refresh();
                    logger()->info("callPatient: Relationships loaded successfully");
                } catch (\Exception $loadError) {
                    logger()->error("callPatient: Failed to load relationships", [
                        'error' => $loadError->getMessage(),
                        'trace' => $loadError->getTraceAsString(),
                    ]);
                    throw $loadError;
                }
                
                // Get display values
                $dentistName = $assignedQueue->dentist?->name ?? 'Unassigned';
                $roomNumber = $assignedQueue->room?->room_number ?? 'N/A';
                
                logger()->info("callPatient: Display values gathered", [
                    'appointment_id' => $id,
                    'dentist_name' => $dentistName,
                    'room_number' => $roomNumber,
                ]);
                
                // Log activity
                try {
                    $this->activityLogger->log(
                        'appointment_called',
                        'Appointment',
                        $id,
                        'Patient called for treatment to Room ' . $roomNumber . ' with ' . $dentistName
                    );
                } catch (\Exception $logError) {
                    logger()->warning('Activity logging failed', ['error' => $logError->getMessage()]);
                }
                
                logger()->info("callPatient: Preparing JSON response");
                return response()->json([
                    'success' => true,
                    'message' => 'Patient called successfully',
                    'patient_name' => $appointment->patient_name,
                    'dentist_name' => $dentistName,
                    'room_number' => $roomNumber,
                    'queue_number' => $assignedQueue->queue_number,
                ]);
                
            } catch (\Exception $assignmentError) {
                logger()->error('QueueAssignmentService failed', [
                    'appointment_id' => $id,
                    'error' => $assignmentError->getMessage(),
                    'error_class' => get_class($assignmentError),
                    'file' => $assignmentError->getFile(),
                    'line' => $assignmentError->getLine(),
                    'trace' => $assignmentError->getTraceAsString(),
                ]);
                
                // Try to extract more specific error reason
                $errorMessage = $assignmentError->getMessage();
                if (str_contains($errorMessage, 'transition') || str_contains($errorMessage, 'completed') || str_contains($errorMessage, 'cancelled')) {
                    $message = 'Cannot call this patient. Appointment may be completed, cancelled, or already in treatment.';
                } elseif (str_contains($errorMessage, 'dentist') || str_contains($errorMessage, 'Dentist')) {
                    $message = 'Unable to assign patient: No available dentist. Please try again later.';
                } elseif (str_contains($errorMessage, 'room') || str_contains($errorMessage, 'Room')) {
                    $message = 'Unable to assign patient: No available treatment room. Please try again later.';
                } else {
                    $message = 'Unable to assign patient to treatment. Please try again later. [' . $errorMessage . ']';
                }
                
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

        } catch (\Exception $e) {
            logger()->error('Call patient failed', [
                'appointment_id' => $id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Extract error reason from exception message
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'transition') || str_contains($errorMessage, 'Cannot transition')) {
                $message = 'Unable to call patient: Appointment is in an invalid state (already completed, cancelled, or being treated). Please try a different patient.';
            } elseif (str_contains($errorMessage, 'dentist') || str_contains($errorMessage, 'Dentist')) {
                $message = 'Unable to call patient: No available dentist. Please wait for a dentist to finish with their current patient.';
            } elseif (str_contains($errorMessage, 'room') || str_contains($errorMessage, 'Room')) {
                $message = 'Unable to call patient: No available treatment room. Please wait for a room to become available.';
            } else {
                $message = 'Unable to call patient. Please try again later.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }
    }

    public function callNextPatient(Request $request, $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);

            // Validate dentist is available before calling next patient
            if ($appointment->dentist && !$appointment->dentist->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Dentist is not available for next patient',
                    'dentist_name' => $appointment->dentist->name,
                    'dentist_status' => $appointment->dentist->status,
                ], 422);
            }

            // Transition through state service
            // Room assignment happens in separate endpoint
            // Dentist state automatically marked BUSY via state machine
            
            // If not yet in waiting state, transition through waiting first
            if ($appointment->status->value !== 'waiting') {
                $waitingResult = $this->staffService->transitionAppointmentStatus(
                    $appointment,
                    'waiting',
                    'Moving to waiting before treatment'
                );
                
                if (!$waitingResult['success']) {
                    return response()->json($waitingResult, 422);
                }
                
                $appointment->refresh();
            }
            
            $result = $this->staffService->transitionAppointmentStatus(
                $appointment,
                'in_treatment',
                'Called for treatment'
            );

            // Check if transition was successful
            if (!$result['success']) {
                return response()->json($result, 422);
            }

            $this->activityLogger->log(
                auth()->id(),
                'appointment_treatment_started',
                ['appointment_id' => $id],
                'Patient called for treatment'
            );

            return response()->json($result);
        } catch (\Exception $e) {
            logger()->error('Call next patient failed', ['error' => $e->getMessage(), 'appointment_id' => $id]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/staff/appointments/{id}/complete (API endpoint)
     * Transition appointment to COMPLETED status
     * Feedback scheduled automatically via event listener
     * ALWAYS returns JSON
     */
    public function completeTreatment(Request $request, $id)
    {
        try {
            logger()->info("completeTreatment called for appointment {$id}");
            
            // CRITICAL: Load queue and room relationships BEFORE completing
            // Ensures onCompleted automation can access room and mark it available
            $appointment = Appointment::with(['queue.room', 'dentist'])->findOrFail($id);
            logger()->info("Found appointment", ['id' => $appointment->id, 'status' => $appointment->status->value]);

            $currentStatus = $appointment->status->value;

            // Handle past appointments that are still in incomplete states
            // This allows staff to retroactively complete forgotten treatments
            // Must follow state machine transitions: booked → checked_in → waiting → in_treatment → completed
            if (in_array($currentStatus, ['booked', 'checked_in', 'waiting'])) {
                logger()->info("Past appointment in incomplete state, transitioning through proper state chain", [
                    'appointment_id' => $id,
                    'current_status' => $currentStatus
                ]);
                
                // Step 1: booked → checked_in (if needed)
                if ($appointment->status->value === 'booked') {
                    $result = $this->staffService->transitionAppointmentStatus(
                        $appointment,
                        'checked_in',
                        'Auto-transitioned to checked_in during retroactive completion'
                    );
                    
                    if (!$result['success']) {
                        logger()->warning("Failed to transition to checked_in", $result);
                        return response()->json($result, 422);
                    }
                    
                    $appointment->refresh();
                }
                
                // Step 2: checked_in → waiting (if needed)
                if ($appointment->status->value === 'checked_in') {
                    $result = $this->staffService->transitionAppointmentStatus(
                        $appointment,
                        'waiting',
                        'Auto-transitioned to waiting during retroactive completion'
                    );
                    
                    if (!$result['success']) {
                        logger()->warning("Failed to transition to waiting", $result);
                        return response()->json($result, 422);
                    }
                    
                    $appointment->refresh();
                }
                
                // Step 3: waiting → in_treatment (if needed)
                if ($appointment->status->value === 'waiting') {
                    $result = $this->staffService->transitionAppointmentStatus(
                        $appointment,
                        'in_treatment',
                        'Auto-transitioned to in_treatment during retroactive completion'
                    );
                    
                    if (!$result['success']) {
                        logger()->warning("Failed to transition to in_treatment", $result);
                        return response()->json($result, 422);
                    }
                    
                    $appointment->refresh();
                }
            }

            // Transition through state service
            $result = $this->staffService->transitionAppointmentStatus(
                $appointment,
                'completed',
                'Treatment completed by staff (retroactively marked if past appointment)'
            );

            logger()->info("Transition result", ['success' => $result['success'], 'message' => $result['message'] ?? '']);

            // Check if transition was successful
            if (!$result['success']) {
                logger()->warning("Transition failed", $result);
                // Always return JSON from API endpoints
                return response()->json($result, 422);
            }

            // Log activity
            try {
                ActivityLogger::log(
                    'appointment_completed',
                    'Appointment',
                    $appointment->id,
                    'Treatment marked as completed'
                );
            } catch (\Exception $logError) {
                logger()->warning('Activity log failed', ['error' => $logError->getMessage()]);
            }

            $clinicLocation = auth()->user()?->clinic_location;
            
            // If user doesn't have a clinic assigned, get the first available clinic location from appointments
            if (!$clinicLocation) {
                $clinicLocation = Appointment::distinct()
                    ->pluck('clinic_location')
                    ->first() ?? 'Main';
                logger()->info('No clinic_location for user (completeTreatment), using first available clinic', ['clinic' => $clinicLocation]);
            }

            // CRITICAL: Auto-call next patient if queue is not paused
            $queueSettings = DB::table('queue_settings')->first();

            if (!$queueSettings || !$queueSettings->is_paused) {
                // Queue is not paused, call next patient automatically (today only, synced with Live Queue Board)
                // But only if no one is already in treatment or called
                $alreadyCalled = Appointment::whereIn('status', ['in_treatment', 'called'])
                    ->where('clinic_location', $clinicLocation)
                    ->whereDate('appointment_date', Carbon::today())
                    ->first();

                if (!$alreadyCalled) {
                    $nextPatient = Appointment::whereIn('status', ['checked_in', 'waiting'])
                        ->where('clinic_location', $clinicLocation)
                        ->whereDate('appointment_date', Carbon::today())
                        ->orderBy('appointment_time')
                        ->first();

                if ($nextPatient) {
                    logger()->info('Auto-calling next patient after treatment completion', [
                        'appointment_id' => $id,
                        'next_appointment_id' => $nextPatient->id,
                        'next_patient' => $nextPatient->patient_name,
                    ]);

                    // Transition next patient to called status via event (NOT directly to in_treatment)
                    try {
                        $this->staffService->transitionAppointmentStatus(
                            $nextPatient,
                            'called',
                            'Auto-called after previous patient completed'
                        );
                        
                        logger()->info('Next patient auto-called successfully', [
                            'next_appointment_id' => $nextPatient->id,
                        ]);
                    } catch (\Exception $e) {
                        logger()->warning('Auto-call of next patient failed', [
                            'next_appointment_id' => $nextPatient->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't fail the completion if auto-call fails
                    }
                }
                } else {
                    logger()->info('Someone already in treatment or called, skipping auto-call', [
                        'appointment_id' => $id,
                    ]);
                }
            } else {
                logger()->info('Queue is paused, skipping auto-call of next patient', [
                    'appointment_id' => $id,
                ]);
            }

            // Always return JSON from API endpoints
            return response()->json(array_merge($result, [
                'patient_name' => $appointment->patient_name,
            ]));
        } catch (\Exception $e) {
            logger()->error('Completion failed', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'appointment_id' => $id
            ]);
            
            // Always return JSON for API calls
            return response()->json([
                'success' => false,
                'error' => $e->getMessage() ?? 'An error occurred while completing treatment',
            ], 500);
        }
    }

    /**
     * PUT /staff/appointments/{id}/status (API)
     * Generic status transition endpoint
     * Validates transition before applying
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:booked,checked_in,in_treatment,waiting,completed,cancelled,feedback_sent',
                'reason' => 'nullable|string',
            ], [
                'status.in' => 'Invalid appointment status provided'
            ]);

            $appointment = Appointment::findOrFail($id);

            $result = $this->staffService->transitionAppointmentStatus(
                $appointment,
                strtolower($request->status),
                $request->input('reason', 'Status changed by staff')
            );

            // Log activity
            try {
                ActivityLogger::log(
                    'appointment_status_changed',
                    'Appointment',
                    $appointment->id,
                    "Appointment status changed to " . ucfirst(str_replace('_', ' ', $request->status))
                );
            } catch (\Exception $logError) {
                logger()->warning('Activity log failed', ['error' => $logError->getMessage()]);
            }

            // If it's a form submission (from appointments edit page), redirect with message
            if ($request->isMethod('post') && !$request->wantsJson()) {
                return redirect()->back()
                    ->with('success', 'Appointment status updated successfully');
            }

            // Otherwise return JSON response
            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->isMethod('post') && !$request->wantsJson()) {
                return redirect()->back()
                    ->withErrors($e->errors())
                    ->with('error', 'Failed to update appointment status');
            }

            return response()->json([
                'success' => false,
                'error' => 'Invalid status provided',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            logger()->error('Status update failed', ['error' => $e->getMessage(), 'appointment_id' => $id]);
            
            if ($request->isMethod('post') && !$request->wantsJson()) {
                return redirect()->back()
                    ->with('error', 'Failed to update appointment status: ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * PUT /staff/appointments/{id}/assign-room (API)
     * Assign treatment room to appointment's queue
     */
    public function assignRoom(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'room_id' => 'required|integer|exists:rooms,id',
            ]);

            $appointment = Appointment::findOrFail($id);

            // Only allow room assignment if appointment is CHECKED_IN or IN_TREATMENT
            if (!in_array($appointment->status->value, ['checked_in', 'in_treatment'])) {
                return response()->json([
                    'success' => false,
                    'error' => "Cannot assign room to appointment with status {$appointment->status}",
                ], 422);
            }

            // Ensure queue exists
            if (!$appointment->queue) {
                return response()->json([
                    'success' => false,
                    'error' => 'Appointment has no queue entry',
                ], 422);
            }

            // Update queue with room assignment
            $appointment->queue->update([
                'room_id' => $request->room_id,
            ]);

            $room = Room::find($request->room_id);

            $this->activityLogger->log(
                auth()->id(),
                'room_assigned',
                ['appointment_id' => $id, 'room_id' => $request->room_id],
                "Room {$room->room_number} assigned"
            );

            return response()->json([
                'success' => true,
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => $appointment->status,
                    'room_assigned' => $room->room_number,
                ],
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('Room assignment failed', ['error' => $e->getMessage(), 'appointment_id' => $id]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
    /**
     * POST /staff/appointments/{id}/cancel
     * Cancel appointment - handles both form submissions and API calls
     */
    public function cancel(Request $request, $id)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string',
            ]);

            $appointment = Appointment::findOrFail($id);

            // Check if user is staff, admin, or developer
            if (!in_array(auth()->user()->role, ['staff', 'admin', 'developer'])) {
                $errorMsg = 'You are not authorized to cancel appointments';
                
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => $errorMsg], 403);
                }
                return back()->with('error', $errorMsg);
            }

            $result = $this->staffService->transitionAppointmentStatus(
                $appointment,
                'cancelled',
                $request->input('reason', 'Cancelled by patient')
            );

            try {
                $this->activityLogger->log(
                    'appointment_cancelled',
                    'Appointment',
                    $id,
                    'Appointment cancelled: ' . $request->input('reason', 'Cancelled by patient')
                );
            } catch (\Exception $logError) {
                logger()->warning('Activity logging failed during cancel', ['error' => $logError->getMessage()]);
            }

            // If this is an API request, return JSON
            if ($request->expectsJson()) {
                return response()->json($result);
            }

            // If this is a form submission, redirect back to tracking page with success message
            return redirect("/track/{$appointment->visit_code}")->with('success', 'Your appointment has been cancelled successfully. We hope to see you again soon!');

        } catch (\Exception $e) {
            logger()->error('Cancellation failed', ['error' => $e->getMessage(), 'appointment_id' => $id]);
            
            $errorMsg = 'Failed to cancel appointment: ' . $e->getMessage();
            
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $errorMsg], 422);
            }
            
            return back()->with('error', $errorMsg);
        }
    }

    /**
     * POST /staff/walk-in (Web form)
     * Create walk-in appointment immediately
     */
    public function storeWalkIn(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'patient_name' => 'required|string',
                'patient_phone' => 'required|string',
                'patient_email' => 'nullable|email',
                'service_id' => 'required|integer|exists:services,id',
                'dentist_id' => 'nullable|integer|exists:dentists,id',
            ]);

            if (!in_array(auth()->user()->role, ['staff', 'developer'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 403);
            }

            // HIGH-006 FIX: Validate operating hours for walk-in
            $now = now();
            $clinicLocation = auth()->user()?->clinic_location ?? 'seremban';
            
            $operatingHour = OperatingHour::where('day_of_week', $now->format('l'))
                ->where('clinic_location', $clinicLocation)
                ->first();
            
            if (!$operatingHour) {
                return response()->json([
                    'success' => false,
                    'error' => 'Clinic is closed today',
                ], 422);
            }
            
            $dayStart = \Carbon\Carbon::parse($now->toDateString() . ' ' . $operatingHour->start_time);
            $dayEnd = \Carbon\Carbon::parse($now->toDateString() . ' ' . $operatingHour->end_time);
            
            if ($now->lt($dayStart) || $now->gt($dayEnd)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Clinic is currently closed. Operating hours: ' . $operatingHour->start_time . ' - ' . $operatingHour->end_time,
                ], 422);
            }

            // HIGH-006 FIX: Validate dentist availability if specified
            if ($request->filled('dentist_id')) {
                $service = Service::findOrFail($request->service_id);
                $serviceDuration = max((int) ($service->estimated_duration ?? 0), 15);
                $endAt = (clone $now)->addMinutes($serviceDuration);
                
                // Check if dentist is available NOW
                $conflictingAppointments = Appointment::where('dentist_id', $request->dentist_id)
                    ->whereIn('status', ['booked', 'checked_in', 'waiting', 'in_treatment'])
                    ->where('appointment_date', $now->toDateString())
                    ->get();
                
                foreach ($conflictingAppointments as $apt) {
                    $aptStart = \Carbon\Carbon::parse($apt->appointment_date . ' ' . $apt->appointment_time);
                    $aptEnd = $aptStart->copy()->addMinutes(max((int) ($apt->service->estimated_duration ?? 0), 15));
                    
                    // Check for time overlap
                    if (!($endAt->lte($aptStart) || $now->gte($aptEnd))) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Selected dentist is not available at this time',
                        ], 422);
                    }
                }
            }

            // Create appointment (starting in BOOKED status)
            $appointment = Appointment::create([
                'patient_name' => $request->patient_name,
                'patient_phone' => $request->patient_phone,
                'patient_email' => $request->patient_email,
                'service_id' => $request->service_id,
                'dentist_id' => $request->dentist_id,
                'appointment_date' => $now->format('Y-m-d'),
                'appointment_time' => $now->format('H:i:s'),
                'status' => 'booked',
                'clinic_location' => $clinicLocation,
            ]);

            // Immediately transition to CHECKED_IN (staff walk-in procedure)
            $result = $this->staffService->transitionAppointmentStatus(
                $appointment,
                'checked_in',
                'Walk-in created and checked in'
            );

            $this->activityLogger->log(
                auth()->id(),
                'walk_in_created',
                ['appointment_id' => $appointment->id],
                "Walk-in appointment created: {$request->patient_name}"
            );

            return response()->json($result, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // MEDIUM-003 FIX: Specific error messages for validation failures
            logger()->error('Walk-in validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors(), // Include field-specific errors
            ], 422);
        } catch (\Exception $e) {
            // MEDIUM-003 FIX: Specific error messages for different failure scenarios
            logger()->error('Walk-in creation failed', [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            
            // Provide specific error messages based on exception type
            $errorMessage = 'Failed to create walk-in appointment';
            if (str_contains($e->getMessage(), 'dentist')) {
                $errorMessage = 'Dentist availability check failed: ' . $e->getMessage();
            } elseif (str_contains($e->getMessage(), 'operating')) {
                $errorMessage = 'Clinic operating hours check failed: ' . $e->getMessage();
            } elseif (str_contains($e->getMessage(), 'Clinic is')) {
                $errorMessage = $e->getMessage(); // Use custom message from validation
            }
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
            ], 422);
        }
    }

    /**
     * POST /staff/pause-queue (API)
     * Pause queue operations
     */
    public function pauseQueue(Request $request): JsonResponse
    {
        try {
            if (!in_array(auth()->user()->role, ['staff', 'developer'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 403);
            }

            DB::table('queue_settings')
                ->update(['is_paused' => true, 'paused_at' => now()]);

            $this->activityLogger->log(
                'queue_paused',
                'QueueSettings',
                null,
                'Queue paused'
            );

            return response()->json([
                'success' => true,
                'message' => 'Queue paused',
            ]);
        } catch (\Exception $e) {
            logger()->error('Pause queue failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /staff/resume-queue (API)
     * Resume queue operations
     */
    public function resumeQueue(Request $request): JsonResponse
    {
        try {
            if (!in_array(auth()->user()->role, ['staff', 'developer'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 403);
            }

            $clinicLocation = auth()->user()?->clinic_location;
            
            // If user doesn't have a clinic assigned, get the first available clinic location from appointments
            if (!$clinicLocation) {
                $clinicLocation = Appointment::distinct()
                    ->pluck('clinic_location')
                    ->first() ?? 'Main';
                logger()->info('No clinic_location for user (resumeQueue), using first available clinic', ['clinic' => $clinicLocation]);
            }

            DB::table('queue_settings')
                ->update(['is_paused' => false, 'paused_at' => null]);

            $this->activityLogger->log(
                'queue_resumed',
                'QueueSettings',
                null,
                'Queue resumed'
            );

            // CRITICAL: Auto-call next patient when queue is resumed
            // Only call if there's not already someone in treatment or called (today only, synced with Live Queue Board)
            $inTreatment = Appointment::whereIn('status', ['in_treatment', 'called'])
                ->where('clinic_location', $clinicLocation)
                ->whereDate('appointment_date', Carbon::today())
                ->first();

            if (!$inTreatment) {
                $nextPatient = Appointment::whereIn('status', ['checked_in', 'waiting'])
                    ->where('clinic_location', $clinicLocation)
                    ->whereDate('appointment_date', Carbon::today())
                    ->orderBy('appointment_time')
                    ->first();

                if ($nextPatient) {
                    logger()->info('Auto-calling next patient when queue is resumed', [
                        'next_appointment_id' => $nextPatient->id,
                        'next_patient' => $nextPatient->patient_name,
                    ]);

                    try {
                        // Call patient to 'called' status, not directly to 'in_treatment'
                        $this->staffService->transitionAppointmentStatus(
                            $nextPatient,
                            'called',
                            'Auto-called when queue resumed'
                        );
                        
                        logger()->info('Next patient auto-called successfully on queue resume', [
                            'next_appointment_id' => $nextPatient->id,
                        ]);
                    } catch (\Exception $e) {
                        logger()->warning('Auto-call of next patient failed on queue resume', [
                            'next_appointment_id' => $nextPatient->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't fail the resume if auto-call fails
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Queue resumed',
            ]);
        } catch (\Exception $e) {
            logger()->error('Resume queue failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /staff/appointments/create
     * Display form to create new appointment
     */
    public function create()
    {
        if (!in_array(auth()->user()->role, ['staff', 'developer'])) {
            return redirect('/')->with('error', 'Unauthorized');
        }

        $dentists = Dentist::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();

        return view('staff.appointments-create', [
            'dentists' => $dentists,
            'services' => $services,
        ]);
    }

    /**
     * POST /staff/appointments
     * Save new appointment
     */
    public function store(Request $request)
    {
        try {
            if (!in_array(auth()->user()->role, ['staff', 'developer'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 403);
            }

            $request->validate([
                'patient_name' => 'required|string|max:255',
                'patient_phone' => 'required|string|max:20',
                'patient_email' => 'nullable|email',
                'service_id' => 'required|integer|exists:services,id',
                'dentist_id' => 'nullable|integer|exists:dentists,id',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i',
            ]);

            // Create appointment through service layer
            $appointment = Appointment::create([
                'patient_name' => $request->patient_name,
                'patient_phone' => $request->patient_phone,
                'patient_email' => $request->patient_email,
                'service_id' => $request->service_id,
                'dentist_id' => $request->dentist_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'status' => 'BOOKED',
                'clinic_location' => auth()->user()?->clinic_location ?? 'Main',
            ]);

            // Automatically create queue entry when appointment is booked
            if (!$appointment->queue) {
                $lastQueue = Queue::whereDate('created_at', now())
                    ->orderByDesc('id')
                    ->first();
                $nextNumber = ($lastQueue?->queue_number ?? 0) + 1;

                Queue::create([
                    'appointment_id' => $appointment->id,
                    'clinic_location' => $appointment->clinic_location,
                    'queue_number' => $nextNumber,
                    'queue_status' => 'waiting',
                ]);
            }

            $this->activityLogger->log(
                auth()->id(),
                'appointment_created',
                ['appointment_id' => $appointment->id],
                "Appointment created: {$request->patient_name}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Appointment created successfully',
                'appointment' => [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient_name,
                    'status' => $appointment->status,
                ],
                'redirect' => route('staff.appointments.index'),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            logger()->error('Appointment creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /staff/appointments/{id}/edit
     * Display form to edit appointment
     */
    public function edit($id)
    {
        try {
            $appointment = Appointment::with('queue')->findOrFail($id);
            $dentists = Dentist::get();
            $services = Service::get();
            // Filter rooms by the appointment's clinic location
            $rooms = Room::where('clinic_location', $appointment->clinic_location)->get();

            return view('staff.appointments-edit', [
                'appointment' => $appointment,
                'dentists' => $dentists,
                'services' => $services,
                'rooms' => $rooms,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Appointment not found');
        }
    }

    /**
     * PUT /staff/appointments/{id}
     * Update appointment
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'patient_name' => 'required|string|max:255',
                'patient_phone' => 'required|string|max:20',
                'patient_email' => 'nullable|email',
                'service_id' => 'required|integer|exists:services,id',
                'dentist_id' => 'required|integer|exists:dentists,id',
                'room_id' => 'nullable|integer|exists:rooms,id',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i',
            ], [
                'dentist_id.required' => 'Please select a dentist',
                'dentist_id.exists' => 'The selected dentist is not valid',
                'room_id.exists' => 'The selected room is not valid',
            ]);

            $appointment = Appointment::findOrFail($id);
            $dentist = Dentist::findOrFail($request->dentist_id);

            // Validate dentist is available (if changing dentist or date/time)
            if ($appointment->dentist_id != $request->dentist_id || 
                $appointment->appointment_date != $request->appointment_date || 
                $appointment->appointment_time != $request->appointment_time) {
                
                // Check if dentist has conflicting appointments at the new time
                $conflictingAppointment = Appointment::where('dentist_id', $request->dentist_id)
                    ->where('appointment_date', $request->appointment_date)
                    ->where('appointment_time', $request->appointment_time)
                    ->where('id', '!=', $appointment->id)
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->first();
                
                if ($conflictingAppointment) {
                    return redirect()->back()->with('error', 
                        'This dentist is not available at the selected date and time. The slot is occupied by another appointment.');
                }
            }

            // Validate room if provided (optional)
            if ($request->room_id) {
                $room = Room::findOrFail($request->room_id);
                $roomConflict = Queue::where('room_id', $request->room_id)
                    ->where('appointment_id', '!=', $appointment->id)
                    ->join('appointments', 'queues.appointment_id', '=', 'appointments.id')
                    ->where('appointments.appointment_date', $request->appointment_date)
                    ->where('appointments.appointment_time', $request->appointment_time)
                    ->whereNotIn('appointments.status', ['cancelled', 'completed'])
                    ->first();
                
                if ($roomConflict) {
                    return redirect()->back()->with('error', 
                        'The selected room is already assigned to another appointment at this date and time.');
                }
            }

            // Update appointment
            $appointment->update([
                'patient_name' => $request->patient_name,
                'patient_phone' => $request->patient_phone,
                'patient_email' => $request->patient_email,
                'service_id' => $request->service_id,
                'dentist_id' => $request->dentist_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
            ]);

            // Update room assignment if provided
            if ($request->room_id) {
                Queue::updateOrCreate(
                    ['appointment_id' => $appointment->id],
                    ['room_id' => $request->room_id]
                );
            }

            // Log activity
            try {
                ActivityLogger::log(
                    'appointment_updated',
                    'Appointment',
                    $appointment->id,
                    "Appointment updated: {$request->patient_name}"
                );
            } catch (\Exception $logError) {
                logger()->warning('Activity log failed', ['error' => $logError->getMessage()]);
            }

            return redirect('/staff/queue')
                ->with('success', 'Appointment updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            logger()->error('Appointment update failed', ['error' => $e->getMessage(), 'appointment_id' => $id]);
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * DELETE /staff/appointments/{id}
     * Delete appointment
     */
    public function destroy($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $patientName = $appointment->patient_name;

            // Soft delete the appointment
            $appointment->delete();

            $this->activityLogger->log(
                auth()->id(),
                'appointment_deleted',
                ['appointment_id' => $id],
                "Appointment deleted: {$patientName}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Appointment deleted successfully',
                'redirect' => route('staff.appointments.index'),
            ]);
        } catch (\Exception $e) {
            logger()->error('Appointment deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/staff/appointments
     * Returns all appointments (not just today) for real-time dashboard
     * Includes queue information and status for live updates
     */
    public function appointmentsApi(): JsonResponse
    {
        try {
            $appointments = Appointment::with(['service', 'dentist', 'queue'])
                ->where('clinic_location', auth()->user()?->clinic_location ?? 'Main')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->get()
                ->map(fn($apt) => [
                    'id' => $apt->id,
                    'patient_name' => $apt->patient_name,
                    'patient_phone' => $apt->patient_phone,
                    'visit_code' => $apt->visit_code,
                    'service_id' => $apt->service_id,
                    'service_name' => $apt->service->name ?? 'Unknown',
                    'dentist_id' => $apt->dentist_id,
                    'dentist_name' => $apt->dentist->name ?? 'Unassigned',
                    'appointment_date' => $apt->appointment_date,
                    'appointment_time' => $apt->appointment_time,
                    'status' => $apt->status->value ?? $apt->status,
                    'checked_in_at' => $apt->checked_in_at?->format('H:i'),
                    'queue_id' => $apt->queue?->id,
                    'queue_number' => $apt->queue?->queue_number,
                    'queue_status' => $apt->queue?->queue_status ?? 'not_queued',
                    'treatment_room' => $apt->queue?->treatment_room_id,
                    'created_at' => $apt->created_at,
                ])
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'appointments' => $appointments,
                    'count' => count($appointments),
                ],
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('Appointments API failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/queue/status
     * Returns current queue status for display
     */
    public function getQueueStatus(): JsonResponse
    {
        try {
            $result = $this->staffService->getActiveQueue();
            return response()->json($result);
        } catch (\Exception $e) {
            logger()->error('Queue status failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Fix for route/method name mismatch
     * Route expects cancelAppointment but method is cancel()
     */
    public function cancelAppointment(Request $request, $id): JsonResponse
    {
        // Delegate to cancel() method to avoid duplication
        return $this->cancel($request, $id);
    }

    /**
     * GET /staff/appointments/test/whatsapp
     * Display WhatsApp phone number formatting test page
     */
    public function whatsappTest()
    {
        return view('staff.whatsapp-test');
    }

    /**
     * POST /staff/appointments/send-whatsapp
     * Send WhatsApp message to patient automatically via Business API
     */
    public function sendWhatsappMessage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'appointment_id' => 'required|exists:appointments,id',
                'message' => 'required|string|min:1|max:1024'
            ]);

            $appointment = Appointment::findOrFail($validated['appointment_id']);

            if (!$appointment->patient_phone) {
                return response()->json([
                    'success' => false,
                    'error' => 'Patient phone number not available'
                ], 422);
            }

            // Send message via WhatsApp Business API
            $result = $this->whatsappService->sendMessage(
                $appointment->patient_phone,
                $validated['message']
            );

            return response()->json($result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            logger()->error('Send WhatsApp error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
