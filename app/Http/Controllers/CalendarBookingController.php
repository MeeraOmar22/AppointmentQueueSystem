<?php

namespace App\Http\Controllers;

use App\Models\OperatingHour;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Services\AppointmentStateService;
use App\Events\AppointmentStateChanged;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarBookingController extends Controller
{
    private AvailabilityService $availabilityService;
    private AppointmentStateService $stateService;

    // Time slot constants
    private const SLOT_INTERVAL = 30;  // Minutes between slots
    private const LUNCH_START = '13:00';  // Lunch break start
    private const LUNCH_END = '14:00';    // Lunch break end

    public function __construct(AvailabilityService $availabilityService, AppointmentStateService $stateService)
    {
        $this->availabilityService = $availabilityService;
        $this->stateService = $stateService;
    }

    /**
     * Show booking form page (GET /book)
     * Single-clinic implementation - no clinic selection required
     */
    public function showForm()
    {
        // Status: 1 = active, 0 = inactive (stored as integer in DB)
        $services = Service::where('status', 1)->get();
        
        // Fetch actual operating hours from database for display
        $operatingHours = OperatingHour::where('clinic_location', 'seremban')
            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();
        
        return view('booking.calendar', [
            'services' => $services,
            'operatingHours' => $operatingHours,
            'currentDate' => now()->format('Y-m-d')
        ]);
    }

    /**
     * Get available time slots for selected date and service (AJAX)
     * GET /api/booking/slots?date=2025-01-21&service_id=1&clinic_location=seremban
     * 
     * Returns time slots accounting for service duration:
     * - Slots are generated at 30-minute intervals
     * - Each slot checks if the ENTIRE service duration is available
     * - Blocks all overlapping appointments (not just start time)
     * - Respects lunch break and operating hours
     * - Excludes past slots for today
     * - Filters by clinic location
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'service_id' => 'required|integer|exists:services,id',
            'clinic_location' => 'nullable|string|in:seremban,kuala_pilah',
        ]);

        $date = $request->input('date');
        $serviceId = $request->input('service_id');
        $clinicLocation = $request->input('clinic_location', 'seremban'); // Default to seremban if not provided

        try {
            $service = Service::find($serviceId);
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            // Get service duration (CRITICAL FIX: use actual duration, not just 30 min)
            $serviceDuration = (int)($service->estimated_duration ?? 30);

            // Get available slots from AvailabilityService with clinic location
            // This now accounts for the full service duration, not just 30-min slots
            // AND filters by clinic location
            $slots = $this->availabilityService->getAvailableSlots($date, $serviceDuration, $clinicLocation);

            return response()->json([
                'success' => true,
                'date' => $date,
                'service_id' => $serviceId,
                'clinic_location' => $clinicLocation,
                'service_name' => $service->name,
                'service_duration' => $serviceDuration,
                'status' => count($slots) > 0 ? 'open' : 'closed',
                'slots' => $slots,
                'message' => count($slots) === 0 ? 'No available slots for this date' : null
            ]);
        } catch (\Exception $e) {
            Log::error('Booking slots error: ' . $e->getMessage(), [
                'date' => $date,
                'service_id' => $serviceId,
                'clinic_location' => $clinicLocation,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available slots'
            ], 500);
        }
    }

    /**
     * Generate available time slots for a given date
     * 
     * Algorithm:
     * 1. Get operating hours for the day of week
     * 2. Return empty if clinic is closed
     * 3. Generate 30-minute slots within operating hours
     * 4. Exclude lunch break (13:00-14:00)
     * 5. Mark booked slots as unavailable
     * 6. Mark past time slots as unavailable (if today)
     * 
     * @param string $date Format: Y-m-d
     * @return array Array of available time slots with metadata
     */
    private function generateTimeSlots(string $date): array
    {
        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayOfWeek = $carbonDate->dayOfWeek; // 0=Sunday, 1=Monday, ..., 6=Saturday
        $isToday = $carbonDate->isToday();
        $currentTime = $isToday ? now() : null;
        
        // Retrieve operating hours for this day
        $operatingHour = OperatingHour::where('day_of_week', $dayOfWeek)->first();
        
        // Return empty if clinic is closed or no hours defined
        if (!$operatingHour || $operatingHour->is_closed) {
            return [];
        }
        
        // Handle nullable time fields
        if (!$operatingHour->start_time || !$operatingHour->end_time) {
            return [];
        }
        
        $slots = [];
        $openTime = Carbon::createFromTimeString($operatingHour->start_time);
        $closeTime = Carbon::createFromTimeString($operatingHour->end_time);
        $currentSlot = $openTime->copy();
        
        // Generate slots in 30-minute intervals
        while ($currentSlot->copy()->addMinutes(self::SLOT_INTERVAL)->lessThanOrEqualTo($closeTime)) {
            $slotTime = $currentSlot->format('H:i');
            
            // Skip lunch break (13:00-14:00)
            if ($this->isLunchBreak($slotTime)) {
                $currentSlot->addMinutes(self::SLOT_INTERVAL);
                continue;
            }
            
            // Determine if slot is available
            $isPast = $isToday && $currentSlot->lessThan($currentTime);
            $isBooked = $this->isSlotBooked($date, $slotTime);
            $isAvailable = !$isPast && !$isBooked;
            
            $slots[] = [
                'time' => $slotTime,
                'displayTime' => $currentSlot->format('h:i A'),
                'available' => $isAvailable,
                'booked' => $isBooked,
                'isPast' => $isPast,
                'disabled' => !$isAvailable,
                'status' => $isAvailable ? 'available' : ($isBooked ? 'booked' : 'unavailable')
            ];
            
            $currentSlot->addMinutes(self::SLOT_INTERVAL);
        }
        
        return $slots;
    }
    
    /**
     * Check if given time is within lunch break (13:00-14:00)
     * 
     * @param string $time Format: H:i (e.g., "13:00", "13:30")
     * @return bool True if time is in lunch break
     */
    private function isLunchBreak(string $time): bool
    {
        return $time >= self::LUNCH_START && $time < self::LUNCH_END;
    }
    
    /**
     * Check if a specific time slot is already booked
     * Ignores cancelled and no-show appointments
     * 
     * @param string $date Format: Y-m-d
     * @param string $time Format: H:i
     * @return bool True if slot is booked
     */
    private function isSlotBooked(string $date, string $time): bool
    {
        return Appointment::where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();
    }

    /**
     * Submit booking (POST /book/submit)
     * 
     * Validates form data and creates appointment with:
     * - Duration-aware slot validation (checks full service duration fits)
     * - Race condition prevention (revalidates at submission)
     * - Clinic location filtering for operating hours
     * - Automatic start_time and end_time calculation
     * - Server-side validation
     * 
     * NEW: Creates appointment with clinic_location and start_time/end_time
     */
    public function submitBooking(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|regex:/^[0-9+\-\(\)\s]{10,15}$/',
            'patient_email' => 'nullable|email',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'clinic_location' => 'nullable|string|in:seremban,kuala_pilah',
            'notes' => 'nullable|string',
        ]);

        try {
            $service = Service::find($validated['service_id']);
            $serviceDuration = (int)($service->estimated_duration ?? 30);
            $clinicLocation = $validated['clinic_location'] ?? 'seremban'; // Default to seremban

            // CRITICAL FIX: Use database transaction to prevent race conditions
            // This ensures that validation and creation are atomic
            $appointment = DB::transaction(function () use ($validated, $serviceDuration, $clinicLocation, $service) {
                // CRITICAL: Validate that the ENTIRE service duration is available
                // with clinic location filtering
                $validationResult = $this->availabilityService->validateBookingRequest(
                    $validated['appointment_date'],
                    $validated['appointment_time'],
                    $serviceDuration,
                    $clinicLocation
                );

                if (!$validationResult['valid']) {
                    throw new \Exception($validationResult['message']);
                }

                // CRITICAL FIX: Calculate end_time = start_time + duration
                $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', 
                    "{$validated['appointment_date']} {$validated['appointment_time']}"
                );
                $endDateTime = $appointmentDateTime->copy()->addMinutes($serviceDuration);

                // CRITICAL: Re-validate inside transaction in case another request booked same slot
                // This double-check prevents race conditions
                $revalidateResult = $this->availabilityService->validateBookingRequest(
                    $validated['appointment_date'],
                    $validated['appointment_time'],
                    $serviceDuration,
                    $clinicLocation
                );

                if (!$revalidateResult['valid']) {
                    throw new \Exception('Time slot was just booked by another customer. Please select a different time.');
                }

                // Create appointment with clinic location, start_time and end_time
                $apt = Appointment::create([
                    'patient_name' => $validated['patient_name'],
                    'patient_phone' => $validated['patient_phone'],
                    'patient_email' => $validated['patient_email'] ?? null,
                    'service_id' => $validated['service_id'],
                    'appointment_date' => $validated['appointment_date'],
                    'appointment_time' => $validated['appointment_time'],
                    'start_at' => $appointmentDateTime,
                    'end_at' => $endDateTime,  // NEW: Store calculated end time
                    'clinic_location' => $clinicLocation,
                    'status' => 'booked',
                    'booking_source' => 'public',
                ]);

                return $apt;
            }, $attempts = 5);  // Retry up to 5 times if transaction fails

            // Ensure visit_code and visit_token are generated (triggered by model events)
            $appointment->refresh();
            
            // Send Email confirmation (only if email provided)
            if (!empty($appointment->patient_email)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($appointment->patient_email)
                        ->send(new \App\Mail\AppointmentConfirmation($appointment, $validated['patient_name']));
                } catch (\Throwable $e) {
                    // Log error but don't block booking
                    Log::warning('Failed to send appointment confirmation email: ' . $e->getMessage());
                }
            }

            // Send WhatsApp confirmation (non-blocking best-effort)
            try {
                app(\App\Services\WhatsAppSender::class)->sendAppointmentConfirmation($appointment);
            } catch (\Throwable $e) {
                // Log the error for debugging
                Log::warning('WhatsApp confirmation failed during booking', [
                    'appointment_id' => $appointment->id,
                    'patient_phone' => $appointment->patient_phone,
                    'error' => $e->getMessage()
                ]);
            }
            
            if (!$appointment->visit_code) {
                Log::warning('Visit code not generated for appointment', ['appointment_id' => $appointment->id]);
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Booking saved but confirmation code generation failed. Please contact support.']);
            }

            return redirect("/track/{$appointment->visit_code}")
                ->with('success', "Appointment booked! Your visit code: {$appointment->visit_code}");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Booking submission error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel a public appointment
     * POST /appointment/cancel/{visit_code}
     * 
     * Uses AppointmentStateService to ensure proper state machine validation
     * and that all automation (dentist updates, logs, etc) is triggered
     */
    public function cancelAppointment(string $visitCode)
    {
        try {
            // Find appointment by visit_code (public identifier)
            $appointment = Appointment::where('visit_code', $visitCode)->firstOrFail();

            // Only allow cancellation if status is not already completed, cancelled, or no_show
            $nonCancellableStatuses = ['completed', 'cancelled', 'no_show', 'feedback_sent'];
            if (in_array($appointment->status->value, $nonCancellableStatuses)) {
                return back()->withErrors(['error' => 'This appointment cannot be cancelled.']);
            }

            // Use state service to transition to cancelled (includes validation and automation)
            $result = $this->stateService->transitionTo(
                $appointment,
                'cancelled',
                'Appointment cancelled by patient via public booking portal'
            );

            if (!$result) {
                return back()->withErrors(['error' => 'Cannot cancel this appointment due to its current status.']);
            }

            Log::info('Appointment cancelled by user', ['appointment_id' => $appointment->id, 'visit_code' => $visitCode]);

            return back()->with('success', "Appointment cancelled successfully. The time slot is now available for others to book.");

        } catch (\Exception $e) {
            Log::error('Appointment cancellation error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to cancel appointment. Please try again.']);
        }
    }

    /**
     * Show rescheduling form
     * GET /appointment/reschedule/{code}
     * 
     * Allows patients to reschedule booked/confirmed appointments
     * Enforces 24-hour advance notice requirement
     */
    public function showRescheduleForm(string $code)
    {
        try {
            $appointment = Appointment::with(['service', 'dentist'])
                ->where('visit_code', $code)
                ->orWhere('visit_token', $code)
                ->firstOrFail();

            // Only allow reschedule for confirmed appointments (not checked in, in treatment, etc.)
            $allowedStatuses = ['booked', 'confirmed'];
            if (!in_array($appointment->status->value, $allowedStatuses)) {
                return back()->with('error', 
                    "Cannot reschedule {$appointment->status->value} appointment. "
                    . "Please contact the clinic if you need assistance.");
            }

            // Check if appointment is in the future
            $appointmentDateTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time);
            if ($appointmentDateTime->isPast()) {
                return back()->with('error', 'Cannot reschedule past appointments.');
            }

            // Check 24-hour advance notice requirement
            $hoursUntilAppointment = now()->diffInHours($appointmentDateTime, false);
            if ($hoursUntilAppointment < 24) {
                return view('public.reschedule-blocked', [
                    'appointment' => $appointment,
                    'hoursRemaining' => max(0, (int)ceil($hoursUntilAppointment)),
                    'message' => 'Rescheduling requires 24 hours advance notice.',
                ]);
            }

            // Get available slots for the next 30 days
            $availableSlots = [];
            for ($i = 1; $i <= 30; $i++) {
                $date = now()->addDays($i)->format('Y-m-d');
                $daySlots = $this->availabilityService->getAvailableSlots(
                    $date,
                    $appointment->service->estimated_duration ?? 30,
                    $appointment->clinic_location
                );
                if (!empty($daySlots)) {
                    $availableSlots[] = [
                        'date' => $date,
                        'slots' => $daySlots,
                        'formattedDate' => Carbon::parse($date)->format('M d, Y (D)'),
                    ];
                }
            }

            return view('public.reschedule-form', [
                'appointment' => $appointment,
                'service' => $appointment->service,
                'dentist' => $appointment->dentist,
                'availableSlots' => $availableSlots,
                'currentDateTime' => $appointmentDateTime->format('M d, Y - H:i A'),
            ]);

        } catch (\Exception $e) {
            Log::error('Reschedule form error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load rescheduling form. Please try again.');
        }
    }

    /**
     * Submit rescheduling
     * POST /appointment/reschedule/{code}
     * 
     * Updates appointment with new date/time and sends confirmation
     */
    public function submitReschedule(Request $request, string $code)
    {
        try {
            $appointment = Appointment::where('visit_code', $code)
                ->orWhere('visit_token', $code)
                ->firstOrFail();

            // Validate request
            $validated = $request->validate([
                'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i',
            ]);

            // Re-check 24-hour requirement
            $currentDateTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time);
            $hoursUntilAppointment = now()->diffInHours($currentDateTime, false);
            if ($hoursUntilAppointment < 24) {
                return back()->with('error', 
                    'Rescheduling window has closed. Please call the clinic to reschedule.'
                )->withInput();
            }

            $newDateTime = Carbon::parse($validated['appointment_date'] . ' ' . $validated['appointment_time']);

            // Validate new slot is available
            $isAvailable = $this->availabilityService->isSlotAvailable(
                $validated['appointment_date'],
                $validated['appointment_time'],
                $appointment->service->estimated_duration ?? 30,
                $appointment->clinic_location,
                $appointment->id  // Exclude current appointment from conflict check
            );

            if (!$isAvailable) {
                return back()->with('error', 
                    'Selected slot is no longer available. Please choose another time.'
                )->withInput();
            }

            // Check operating hours for clinic at new time
            $operatingHour = OperatingHour::where('clinic_location', $appointment->clinic_location)
                ->whereRaw("DAYOFWEEK(?) = DAYOFWEEK(NOW())", [$newDateTime])
                ->first();

            if (!$operatingHour || $operatingHour->is_closed) {
                return back()->with('error', 
                    'Clinic is closed on ' . $newDateTime->format('l') . '. Please select another date.'
                )->withInput();
            }

            // Verify time is within operating hours
            $appointmentTimeObj = Carbon::createFromFormat('H:i', $validated['appointment_time']);
            $startTime = Carbon::createFromFormat('H:i', $operatingHour->start_time);
            $endTime = Carbon::createFromFormat('H:i', $operatingHour->end_time);

            if ($appointmentTimeObj->lt($startTime) || $appointmentTimeObj->gt($endTime)) {
                return back()->with('error', 
                    'Selected time is outside clinic operating hours (' . 
                    $startTime->format('H:i A') . ' - ' . $endTime->format('H:i A') . ').'
                )->withInput();
            }

            // Update appointment
            $oldDateTime = $appointment->appointment_date . ' ' . $appointment->appointment_time;
            $appointment->update([
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
            ]);

            // Log activity
            \App\Services\ActivityLogger::log(
                'appointment_rescheduled',
                'Appointment',
                $appointment->id,
                "Appointment rescheduled from {$oldDateTime} to {$newDateTime->format('Y-m-d H:i')}",
                ['old_time' => $oldDateTime],
                ['new_time' => $newDateTime->format('Y-m-d H:i')]
            );

            // Send WhatsApp notification to patient
            try {
                $whatsAppSender = app(\App\Services\WhatsAppSender::class);
                $whatsAppSender->sendRescheduleConfirmation($appointment);
            } catch (\Throwable $e) {
                logger()->error('Failed to send reschedule WhatsApp', ['error' => $e->getMessage()]);
                // Don't fail the reschedule - just log the error
            }

            return redirect('/track/' . $appointment->visit_code)
                ->with('success', 'Appointment rescheduled to ' . $newDateTime->format('M d, Y - H:i A'));

        } catch (\Exception $e) {
            Log::error('Reschedule submission error: ' . $e->getMessage());
            return back()->with('error', 
                'Failed to reschedule appointment. Please try again or contact the clinic.'
            )->withInput();
        }
    }
}
