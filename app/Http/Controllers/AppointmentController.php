<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Appointment;
use App\Models\OperatingHour;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Services\WhatsAppSender;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Facades\Mail;
use App\Services\ActivityLogger;

class AppointmentController extends Controller
{
    public function create()
    {
        $services = Service::all();
        $dentists = Dentist::where('status', 1)->get();
        $operatingHours = OperatingHour::all();

        return view('public.book', compact('services', 'dentists', 'operatingHours'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_name' => 'required|string',
            'patient_phone' => 'required|string',
            'patient_email' => 'nullable|email',
            'clinic_location' => 'required|in:seremban,kuala_pilah',
            'service_id' => 'required|exists:services,id',
            'dentist_preference' => 'required|in:any,specific',
            'dentist_id' => 'nullable|exists:dentists,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        // Validate that dentist_id is provided when preference is 'specific'
        if ($data['dentist_preference'] === 'specific' && empty($data['dentist_id'])) {
            return back()
                ->withInput()
                ->withErrors(['dentist_id' => 'Please select a dentist for your preferred appointment.']);
        }

        $service = Service::findOrFail($data['service_id']);
        $serviceDuration = max((int) ($service->estimated_duration ?? 0), 15);

        $startAt = Carbon::parse($data['appointment_date'] . ' ' . $data['appointment_time']);
        $endAt = (clone $startAt)->addMinutes($serviceDuration);

        // If preference is 'specific', verify dentist availability
        $dentistId = null;
        if ($data['dentist_preference'] === 'specific') {
            $dentist = Dentist::findOrFail($data['dentist_id']);
            
            if (!$this->dentistIsAvailable($dentist->id, $startAt, $endAt)) {
                return back()
                    ->withInput()
                    ->withErrors(['dentist_id' => 'Selected dentist is not available for that time.']);
            }
            
            $dentistId = $dentist->id;
        }
        // If preference is 'any', dentist_id will be NULL and assigned during queue execution

        $appointment = Appointment::create([
            'patient_name' => $data['patient_name'],
            'patient_phone' => $data['patient_phone'],
            'patient_email' => $data['patient_email'] ?? null,
            'clinic_location' => $data['clinic_location'],
            'service_id' => $service->id,
            'dentist_id' => $dentistId,
            'appointment_date' => $startAt->toDateString(),
            'appointment_time' => $startAt->format('H:i:s'),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'booked',
            'booking_source' => 'public',
        ]);

        $queueNumber = null;
        $etaMinutes = null;

        if ($startAt->isToday()) {
            $queueNumber = Queue::nextNumberForDate($startAt);
            $etaMinutes = ($queueNumber - 1) * $serviceDuration;

            Queue::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'queue_number' => $queueNumber,
                    'queue_status' => 'waiting',
                ]
            );
        }

        // Send Email confirmation (only if email provided)
        if (!empty($appointment->patient_email)) {
            try {
                Mail::to($appointment->patient_email)->send(new AppointmentConfirmation($appointment, $data['patient_name']));
            } catch (\Throwable $e) {
                // Log error but don't block booking
                \Log::warning('Failed to send appointment confirmation email: ' . $e->getMessage());
            }
        }

        // Send WhatsApp confirmation (non-blocking best-effort)
        try {
            app(WhatsAppSender::class)->sendAppointmentConfirmation($appointment);
        } catch (\Throwable $e) {
            // ignore
        }

        // Log the public booking
        ActivityLogger::log(
            'booked',
            'Appointment',
            $appointment->id,
            'Public booking by ' . $data['patient_name'] . ' for ' . $service->name,
            null,
            $appointment->toArray()
        );

        // For testing/API consistency, redirect to visit status page
        return redirect()->to('/visit/' . $appointment->visit_token)
            ->with('success', 'Appointment booked successfully!')
            ->with('queue_number', $queueNumber)
            ->with('eta_minutes', $etaMinutes);
    }

    public function visitStatus(string $token)
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_token', $token)
            ->firstOrFail();

        $queueNumber = $appointment->queue?->queue_number;
        $queueStatus = $appointment->queue?->queue_status ?? 'not-queued';

        $serviceDuration = max((int) ($appointment->service->estimated_duration ?? 0), 15);
        $etaMinutes = null;

        if ($queueNumber && $queueStatus !== 'completed') {
            $etaMinutes = ($queueNumber - 1) * $serviceDuration;
        }

        return view('public.visit-status', [
            'appointment' => $appointment,
            'queueNumber' => $queueNumber,
            'queueStatus' => $queueStatus,
            'etaMinutes' => $etaMinutes,
            'operatingHours' => OperatingHour::all(),
        ]);
    }

    public function trackByCode(string $code)
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_code', $code)
            ->firstOrFail();

        [$queueNumber, $queueStatus, $etaMinutes, $currentServing] = $this->computeQueueInfo($appointment);
        $room = $appointment->room ?? '—';

        return view('public.track', [
            'appointment' => $appointment,
            'queueNumber' => $queueNumber,
            'queueStatus' => $queueStatus,
            'etaMinutes' => $etaMinutes,
            'currentServing' => $currentServing,
            'room' => $room,
        ]);
    }

    public function checkinForm()
    {
        return view('public.checkin', [
            'operatingHours' => OperatingHour::all(),
        ]);
    }

    public function checkinSubmit(Request $request)
    {
        $data = $request->validate([
            'visit_code' => 'nullable|string',
            'token' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        // Support both visit_code and token+phone check-in methods
        if (!empty($data['visit_code'])) {
            // Original flow: check-in by visit code only
            $code = strtoupper(trim($data['visit_code']));
            $appointment = Appointment::with(['service', 'queue', 'dentist'])
                ->where('visit_code', $code)
                ->first();

            if (!$appointment) {
                return back()->withErrors(['visit_code' => 'Visit code not found. Please check and try again.'])->withInput();
            }
        } elseif (!empty($data['token']) && !empty($data['phone'])) {
            // New flow: check-in by token + phone verification
            $appointment = Appointment::with(['service', 'queue', 'dentist'])
                ->where('visit_token', $data['token'])
                ->first();

            if (!$appointment) {
                return back()->withErrors(['token' => 'Invalid visit token.'])->withInput();
            }

            // Verify phone number matches
            if ($appointment->patient_phone !== $data['phone']) {
                return back()->withErrors(['phone' => 'Phone number does not match our records.'])->withInput();
            }
        } elseif (!empty($data['token']) && empty($data['phone'])) {
            return back()->withErrors(['phone' => 'Phone number is required when using token.'])->withInput();
        } elseif (empty($data['token']) && !empty($data['phone'])) {
            return back()->withErrors(['token' => 'Visit token is required when providing phone.'])->withInput();
        } else {
            return back()->withErrors(['visit_code' => 'Please provide either a visit code or both token and phone number.'])->withInput();
        }

        $this->assignQueueAndRoom($appointment);

        // Log the check-in
        ActivityLogger::log(
            'checked_in',
            'Appointment',
            $appointment->id,
            'Patient ' . $appointment->patient_name . ' checked in via public portal',
            ['status' => $appointment->getOriginal('status')],
            ['status' => 'arrived', 'check_in_time' => now()]
        );

        return redirect()->to('/track/' . $appointment->visit_code)
            ->with('status', 'Checked in successfully.');
    }

    public function queueBoard()
    {
        $today = Carbon::today();

        $inService = Queue::with(['appointment.dentist'])
            ->whereHas('appointment', function ($q) use ($today) {
                $q->whereDate('appointment_date', $today);
            })
            ->where('queue_status', 'in_service')
            ->orderBy('queue_number')
            ->get();

        $waiting = Queue::with(['appointment.dentist'])
            ->whereHas('appointment', function ($q) use ($today) {
                $q->whereDate('appointment_date', $today);
            })
            ->where('queue_status', 'waiting')
            ->orderBy('queue_number')
            ->get();

        $currentNumber = optional($inService->first())->queue_number;

        return view('public.queue-board', [
            'inService' => $inService,
            'waiting' => $waiting,
            'currentNumber' => $currentNumber,
        ]);
    }

    public function findMyBookingForm()
    {
        return view('public.find-my-booking', [
            'operatingHours' => OperatingHour::all(),
        ]);
    }

    public function findMyBookingSubmit(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $data['phone']);
        $today = Carbon::today();

        // Search for today's or upcoming appointments only (safety: no past data)
        $appointment = Appointment::where(function ($q) use ($phone) {
            $q->where('patient_phone', 'LIKE', '%' . $phone . '%')
              ->orWhere('patient_phone', 'LIKE', '%' . substr($phone, -8) . '%'); // match last 8 digits
        })
        ->where(function ($q) use ($today) {
            $q->whereDate('appointment_date', '>=', $today);
        })
        ->orderBy('appointment_date')
        ->orderBy('appointment_time')
        ->first();

        if (!$appointment) {
            return back()
                ->with('error', 'No upcoming booking found for this phone number. Please check and try again.')
                ->withInput();
        }

        // Redirect to track page
        return redirect()->to('/track/' . $appointment->visit_code)
            ->with('status', 'Booking found! Here is your appointment.');
    }

    private function dentistIsAvailable(int $dentistId, Carbon $startAt, Carbon $endAt): bool
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
            ->whereIn('status', ['booked'])
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

    private function assignQueueAndRoom(Appointment $appointment): void
    {
        $now = Carbon::now();

        if (!$appointment->checked_in_at) {
            $appointment->checked_in_at = $now;
        }

        $queue = Queue::firstOrNew(['appointment_id' => $appointment->id]);
        if (!$queue->queue_number) {
            $queue->queue_number = Queue::nextNumberForDate($appointment->appointment_date);
        }
        
        // Check if anyone is currently in service today
        $inServiceCount = Queue::whereHas('appointment', function ($q) use ($appointment) {
            $q->whereDate('appointment_date', $appointment->appointment_date);
        })->where('queue_status', 'in_service')->count();
        
        // Auto-start service if no one is currently being served
        $queue->queue_status = $inServiceCount === 0 ? 'in_service' : 'waiting';
        $queue->check_in_time = $now;
        $queue->save();

        if (!$appointment->room) {
            $appointment->room = $this->chooseRoomForToday($appointment->appointment_date);
        }

        $appointment->status = 'checked_in';
        $appointment->save();
    }

    private function chooseRoomForToday($date): string
    {
        $todayQueues = Queue::whereHas('appointment', function ($q) use ($date) {
            $q->whereDate('appointment_date', $date);
        })->with('appointment')->get();

        $room1 = $todayQueues->filter(fn($q) => optional($q->appointment)->room == '1')->count();
        $room2 = $todayQueues->filter(fn($q) => optional($q->appointment)->room == '2')->count();

        return $room1 <= $room2 ? '1' : '2';
    }

    private function computeQueueInfo(Appointment $appointment): array
    {
        $queueNumber = $appointment->queue?->queue_number;
        $queueStatus = $appointment->queue?->queue_status ?? 'not-queued';

        $serviceDuration = max((int) ($appointment->service->estimated_duration ?? 0), 15);

        // Current serving is lowest in_service queue today
        $currentServing = Queue::whereHas('appointment', function ($q) use ($appointment) {
            $q->whereDate('appointment_date', $appointment->appointment_date);
        })->where('queue_status', 'in_service')->orderBy('queue_number')->first()?->queue_number;

        $etaMinutes = null;
        if ($queueNumber) {
            $ahead = Queue::whereHas('appointment', function ($q) use ($appointment) {
                $q->whereDate('appointment_date', $appointment->appointment_date);
            })
                ->where('queue_status', 'waiting')
                ->where('queue_number', '<', $queueNumber)
                ->count();

            $etaMinutes = $ahead * $serviceDuration;
        }

        return [$queueNumber, $queueStatus, $etaMinutes, $currentServing];
    }

    public function visitLookup(Request $request)
    {
        $phone = $request->query('phone');
        $date = $request->query('date');

        $found = false;
        $appointment = null;

        // Search for appointment matching phone and date
        if ($phone && $date) {
            $appointment = Appointment::where('patient_phone', $phone)
                ->whereDate('appointment_date', $date)
                ->first();

            if ($appointment) {
                $found = true;
            }
        }

        return view('public.visit-lookup', [
            'found' => $found,
            'appointment' => $appointment,
            'operatingHours' => OperatingHour::all(),
        ]);
    }

    public function publicCheckIn(string $token)
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_token', $token)
            ->firstOrFail();

        $now = Carbon::now();

        // Mark checked in
        if (!$appointment->checked_in_at) {
            $appointment->checked_in_at = $now;
            $appointment->save();
        }

        // Ensure queue exists and is active for today
        if (Carbon::parse($appointment->appointment_date)->isToday()) {
            if (!$appointment->queue) {
                $serviceDuration = max((int) ($appointment->service->estimated_duration ?? 0), 15);
                $queueNumber = Queue::nextNumberForDate($appointment->appointment_date);
                Queue::updateOrCreate(
                    ['appointment_id' => $appointment->id],
                    [
                        'queue_number' => $queueNumber,
                        'queue_status' => 'waiting',
                        'check_in_time' => $now,
                    ]
                );
            }
        }

        return redirect()->to('/visit/' . $appointment->visit_token)
            ->with('status', 'You have been checked in.');
    }

    public function trackByCodeApi(string $code)
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist'])
            ->where('visit_code', $code)
            ->firstOrFail();

        [$queueNumber, $queueStatus, $etaMinutes, $currentServing] = $this->computeQueueInfo($appointment);
        $room = $appointment->room ?? '—';

        return response()->json([
            'appointment' => [
                'patient_name' => $appointment->patient_name,
                'patient_phone' => $appointment->patient_phone,
                'service' => $appointment->service?->name ?? $appointment->service?->service_name ?? '—',
                'dentist' => $appointment->dentist?->name ?? 'TBD',
                'checked_in_at' => $appointment->checked_in_at?->format('Y-m-d H:i:s'),
            ],
            'queueNumber' => $queueNumber,
            'queueStatus' => $queueStatus,
            'etaMinutes' => $etaMinutes,
            'currentServing' => $currentServing,
            'room' => $room,
        ]);
    }
}
