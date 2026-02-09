@extends('layouts.public')

@section('title', 'Track Your Visit')

@section('content')
<div class="container-fluid bg-primary py-3 mb-4">
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="text-white mb-1">Track Your Visit</h2>
            <span class="small text-white-50">Code: {{ $appointment->visit_code }}</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <style>
        .track-card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .track-stat-label { color: #6c757d; font-size: .75rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
        .track-stat-value { font-weight: 700; font-size: 1.3rem; color: #212529; }
        .track-badge { font-size: .9rem; }
        .track-section { border-top: 1px solid #e9ecef; padding-top: 1.5rem; margin-top: 1.5rem; }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; background: #0d6efd; animation: pulse 1.2s infinite; margin-right: .4rem; }
        @keyframes pulse { 0% { opacity: .2; } 50% { opacity: 1; } 100% { opacity: .2; } }
        
        .state-confirmed .treatment-info,
        .state-confirmed .queue-info { display: none; }
        
        .state-waiting .treatment-info,
        .state-in_treatment .treatment-info { display: block; }
        
        .state-confirmed .confirmation-info { display: block; }
        .state-waiting .confirmation-info,
        .state-in_treatment .confirmation-info { display: none; }
        
        .state-checked_in .queue-info,
        .state-waiting .queue-info { display: block; }
        
        .state-completed { text-align: center; padding: 2rem 0; }
        .state-completed .track-card { background: #d4edda; border: 1px solid #c3e6cb; }
        .state-completed h3 { color: #155724; margin-bottom: 1rem; }
        .state-completed .completion-message { font-size: 1.1rem; color: #155724; }
        
        .state-feedback_sent { text-align: center; padding: 2rem 0; }
        .state-feedback_sent .track-card { background: #d1ecf1; border: 1px solid #bee5eb; }
        .state-feedback_sent h3 { color: #0c5460; margin-bottom: 1rem; }
        .state-feedback_sent .feedback-message { font-size: 1.1rem; color: #0c5460; }
    </style>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #198754; padding: 1.5rem; border-radius: 8px; font-size: 1rem;">
                    <strong style="font-size: 1.1rem;">✓ Success!</strong>
                    <div style="margin-top: 0.5rem;">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @elseif(session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #198754; padding: 1.5rem; border-radius: 8px; font-size: 1rem;">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #dc3545; padding: 1.5rem; border-radius: 8px; font-size: 1rem;">
                    <strong style="font-size: 1.1rem;">✗ Cannot Check In</strong>
                    <div style="margin-top: 0.5rem; line-height: 1.6;">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #ffc107; padding: 1.5rem; border-radius: 8px; font-size: 1rem;">
                    <strong style="font-size: 1.1rem;">⚠ Warning!</strong>
                    <div style="margin-top: 0.5rem;">
                        {{ session('warning') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #0dcaf0; padding: 1.5rem; border-radius: 8px; font-size: 1rem;">
                    <strong style="font-size: 1.1rem;">ℹ Information</strong>
                    <div style="margin-top: 0.5rem;">
                        {{ session('info') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('pause_alert'))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-left: 5px solid #ffc107; padding: 1.5rem; border-radius: 8px; font-size: 1rem;">
                    <strong style="font-size: 1.1rem;">⏸ Queue Paused!</strong>
                    <div style="margin-top: 0.5rem;">
                        {{ session('pause_alert') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- DEBUG: Show actual appointment status -->
            <div class="alert alert-info small mb-3" style="display: none;">
                Status: {{ $appointment->status->value }} | Patient: {{ $appointment->patient_name }} | Service: {{ $appointment->service->name ?? 'N/A' }}
            </div>

            <div class="bg-light p-4 rounded shadow-sm mb-4 track-card state-{{ $appointment->status->value }}">
                
                <!-- CONFIRMED STATE: Before Arrival -->
                @if($appointment->status->value === 'confirmed')
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <div class="track-stat-label">Patient</div>
                            <div class="h5 mb-0">{{ $appointment->patient_name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="track-stat-label">Status</div>
                            <span class="badge bg-secondary">Awaiting Check-in</span>
                        </div>
                    </div>

                    <div class="confirmation-info">
                        <div class="row text-center track-section">
                            <div class="col-md-6 mb-3">
                                <div class="track-stat-label">Appointment Time</div>
                                <div class="track-stat-value">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="track-stat-label">Service</div>
                                <div class="fw-semibold">{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-12 mb-3">
                                <div class="track-stat-label">Dentist</div>
                                <div class="fw-semibold">{{ $appointment->dentist?->name ?? 'Any Dentist' }}</div>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-white rounded" style="border-left: 4px solid #0d6efd;">
                            <p class="mb-0 small">Please arrive <strong>5-10 minutes early</strong> and tap the check-in link when you arrive at the clinic.</p>
                        </div>
                    </div>
                @endif

                <!-- BOOKED STATE: Appointment confirmed -->
                @if($appointment->status->value === 'booked')
                    <!-- APPOINTMENT DETAILS SECTION -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3"><i class="bi bi-calendar-check"></i> Your Appointment</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="track-stat-label">Service</div>
                                <div class="fw-semibold fs-5">
                                    @if($appointment->service)
                                        {{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="track-stat-label">Dentist</div>
                                <div class="fw-semibold fs-5">{{ $appointment->dentist?->name ?? 'Any Dentist' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="track-stat-label">Appointment Date</div>
                                <div class="fw-semibold fs-5">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="track-stat-label">Appointment Time</div>
                                <div class="fw-semibold fs-5">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- STATUS SECTION -->
                    <div class="mb-4">
                        <h5 class="text-primary mb-3"><i class="bi bi-info-circle"></i> Current Status</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="track-stat-label">Status</div>
                                <span class="badge bg-warning fs-6 px-3 py-2">📋 Appointment Scheduled</span>
                            </div>
                            <div class="col-md-6">
                                <div class="track-stat-label">Queue Number</div>
                                <div class="fw-semibold fs-5">{{ $queueNumber ? sprintf('A-%02d', $queueNumber) : '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="track-stat-label">Estimated Wait Time</div>
                                <div class="fw-semibold fs-5">
                                    @if($etaMinutes !== null)
                                        {{ $etaMinutes . ' minute' . ($etaMinutes !== 1 ? 's' : '') }}
                                    @else
                                        <span class="text-muted">Check in to see accurate wait time</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="track-stat-label">Now Serving</div>
                                <div class="fw-semibold fs-5">
                                    @if($currentServing)
                                        {{ $currentServing }} in Room
                                    @else
                                        — (clinic not in service)
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info small border-0" style="background-color: #e7f3ff;">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Ready to Check In?</strong> Please arrive 5-10 minutes early and click the Check In button when you arrive at the clinic.
                    </div>
                @endif

                <!-- CHECKED_IN / WAITING STATE -->
                @if($appointment->status->value === 'checked_in' || $appointment->status->value === 'waiting')
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <div class="track-stat-label">Patient</div>
                            <div class="h5 mb-0">{{ $appointment->patient_name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="track-stat-label">Service</div>
                            <div class="fw-semibold">
                                @if($appointment->service)
                                    {{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="queue-info">
                        <div class="row text-center track-section">
                            <div class="col-6 col-md-3 mb-3">
                                <div class="track-stat-label">Queue No</div>
                                <div class="track-stat-value" data-update="queueNumber">{{ $queueNumber ? sprintf('A-%02d', $queueNumber) : '—' }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="track-stat-label">Status</div>
                                <span class="badge bg-primary track-badge" data-update="status">
                                    @if($appointment->status->value === 'waiting') Waiting
                                    @else Checked In @endif
                                </span>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="track-stat-label">Now Serving</div>
                                <div class="track-stat-value" data-update="serving">
                                    @if($currentServing)
                                        <span data-current-serving="{{ $currentServing }}">
                                            @php
                                                $formattedQueue = $queueNumber ? sprintf('A-%02d', $queueNumber) : null;
                                            @endphp
                                            @if($currentServing === $formattedQueue)
                                                {{ $currentServing }} (You)
                                            @else
                                                {{ $currentServing }}
                                            @endif
                                        </span>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-3 mb-3">
                                <div class="track-stat-label">Estimated Wait</div>
                                <div class="track-stat-value text-warning" data-update="eta">
                                    @if($etaMinutes !== null)
                                        {{ $etaMinutes . ' min' }}
                                    @else
                                        <span class="text-muted">Check in</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-white rounded" style="border-left: 4px solid #0d6efd;">
                            <p class="mb-0 small">Please remain in the waiting area. You will be called when it's your turn.</p>
                        </div>
                    </div>
                @endif

                <!-- IN_TREATMENT STATE -->
                @if($appointment->status->value === 'in_treatment')
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <div class="track-stat-label">Patient</div>
                            <div class="h5 mb-0">{{ $appointment->patient_name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="track-stat-label">Service</div>
                            <div class="fw-semibold">
                                @if($appointment->service)
                                    {{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="treatment-info">
                        <div class="row text-center track-section">
                            <div class="col-6 col-md-4 mb-3">
                                <div class="track-stat-label">Status</div>
                                <span class="badge bg-success track-badge" data-update="status">In Treatment</span>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="track-stat-label">Queue No</div>
                                <div class="track-stat-value" data-update="queueNumber">{{ $queueNumber ? sprintf('A-%02d', $queueNumber) : '—' }}</div>
                            </div>
                            <div class="col-6 col-md-4 mb-3">
                                <div class="track-stat-label">Dentist</div>
                                <div class="fw-semibold">{{ $appointment->dentist?->name ?? 'Any Dentist' }}</div>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-white rounded" style="border-left: 4px solid #198754;">
                            <p class="mb-0 small">Your treatment is in progress. Please wait in the treatment room.</p>
                        </div>
                    </div>
                @endif

                <!-- COMPLETED STATE -->
                @if($appointment->status->value === 'completed')
                    <div class="text-center py-4">
                        <h3 class="text-success mb-3">✓ Appointment Completed</h3>
                        <p class="text-muted mb-3">Thank you for visiting us!</p>
                        <div class="alert alert-success small">
                            Your appointment has been completed. If you'd like to provide feedback, please use the link below.
                        </div>
                    </div>
                @endif

                <!-- CANCELLED STATE -->
                @if($appointment->status->value === 'cancelled')
                    <div class="text-center py-4">
                        <h3 class="text-danger mb-3">✕ Appointment Cancelled</h3>
                        <p class="text-muted mb-3">Your appointment has been cancelled.</p>
                        <div class="alert alert-warning small">
                            If you would like to reschedule, please <a href="{{ route('booking.form') }}">book a new appointment</a>.
                        </div>
                    </div>
                @endif

                <!-- FEEDBACK_SENT STATE -->
                @if($appointment->status->value === 'feedback_sent')
                    <div class="text-center py-4">
                        <h3 class="text-info mb-3">✓ Thank You for Your Feedback</h3>
                        <p class="text-muted">We appreciate your valuable feedback.</p>
                    </div>
                @endif

                <!-- FALLBACK: Display appointment details if no status matches -->
                @if(!in_array($appointment->status->value, ['confirmed', 'booked', 'checked_in', 'waiting', 'in_treatment', 'completed', 'feedback_sent']))
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <div class="track-stat-label">Patient</div>
                            <div class="h5 mb-0">{{ $appointment->patient_name }}</div>
                        </div>
                        <div class="text-end">
                            <div class="track-stat-label">Status</div>
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</span>
                        </div>
                    </div>

                    <div class="row text-center track-section">
                        <div class="col-md-6 mb-3">
                            <div class="track-stat-label">Appointment Date</div>
                            <div class="track-stat-value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="track-stat-label">Appointment Time</div>
                            <div class="track-stat-value">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="row text-center track-section">
                        <div class="col-md-6 mb-3">
                            <div class="track-stat-label">Service</div>
                            <div class="fw-semibold">
                                @if($appointment->service)
                                    {{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="track-stat-label">Dentist</div>
                            <div class="fw-semibold">{{ $appointment->dentist?->name ?? 'Any Dentist' }}</div>
                        </div>
                    </div>
                @endif

                @if(!in_array($appointment->status->value, ['completed', 'feedback_sent', 'in_treatment', 'cancelled', 'no_show', 'checked_in', 'waiting']))
                    <div class="mt-4 pt-3 track-section">
                        <div class="d-flex flex-wrap gap-3 justify-content-center">
                            <!-- Check In Button -->
                            <form action="{{ route('appointment.checkin', $appointment->visit_code) }}" method="POST" style="display: inline;" onsubmit="handleCheckInSubmit(event)">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg px-4 fw-semibold" id="checkInBtn">
                                    <i class="bi bi-check-circle me-2"></i>Check In
                                </button>
                            </form>

                            <!-- Cancel Appointment Button -->
                            <button type="button" class="btn btn-outline-danger btn-lg px-4 fw-semibold" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </button>
                        </div>
                    </div>
                @elseif(in_array($appointment->status->value, ['checked_in', 'waiting']))
                    <div class="mt-4 pt-3 track-section">
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-2" style="font-size: 24px;"></i>
                            <div>
                                <strong>You're checked in!</strong><br>
                                <small>Please wait for your turn. Your queue number is shown above.</small>
                            </div>
                        </div>
                    </div>
                @elseif($appointment->status->value === 'completed')
                    <div class="mt-4 pt-3 track-section">
                        <div class="text-center">
                            <a href="{{ url('/feedback?code=' . urlencode($appointment->visit_code)) }}" class="btn btn-primary btn-lg px-5 fw-semibold">
                                <i class="bi bi-chat-dots me-2"></i>Share Your Feedback
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Cancel Modal -->
                <div class="modal fade" id="cancelModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancel Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to cancel this appointment?</p>
                                <p class="text-muted small mb-0">This action cannot be undone.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                                <form method="POST" action="{{ route('appointment.cancel', $appointment->visit_code) }}" style="display: inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function getStatusDisplayText(statusValue) {
            const statusMap = {
                'confirmed': 'Awaiting Check-in',
                'booked': 'Awaiting Check-in',
                'checked_in': 'Checked In',
                'waiting': 'Waiting',
                'in_treatment': 'In Treatment',
                'completed': 'Completed',
                'cancelled': 'Cancelled',
                'no_show': 'No Show',
                'feedback_sent': 'Feedback Sent'
            };
            return statusMap[statusValue] || statusValue;
        }

        function updateTrackingData() {
            fetch(`/api/track/{{ $appointment->visit_code }}`)
                .then(response => {
                    if (!response.ok) {
                        console.error('❌ API returned status:', response.status);
                        throw new Error('Failed to fetch tracking data');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) {
                        console.error('❌ No data received from API');
                        return;
                    }
                    
                    console.log('📡 API Response:', data);
                    
                    const statusBadges = document.querySelectorAll('[data-update="status"]');
                    const queueSpans = document.querySelectorAll('[data-update="queueNumber"]');
                    const servingSpans = document.querySelectorAll('[data-update="serving"]');
                    const etaSpans = document.querySelectorAll('[data-update="eta"]');

                    // Update status with user-friendly text
                    statusBadges.forEach(el => {
                        if (data.status) {
                            el.textContent = getStatusDisplayText(data.status);
                            console.log('✅ Status updated to:', el.textContent);
                        }
                    });
                    
                    // Update queue number
                    queueSpans.forEach(el => {
                        el.textContent = data.queue_number ? `A-${String(data.queue_number).padStart(2, '0')}` : '—';
                    });
                    
                    // Update now serving
                    if (data.current_serving !== undefined && data.current_serving !== null) {
                        const formattedQueue = data.queue_number ? `A-${String(data.queue_number).padStart(2, '0')}` : null;
                        servingSpans.forEach(el => {
                            el.innerHTML = data.current_serving === formattedQueue ?
                                `${data.current_serving} (You)` :
                                `${data.current_serving}`;
                        });
                    }

                    // Update ETA
                    etaSpans.forEach(el => {
                        if (data.eta_minutes !== null && data.eta_minutes !== undefined) {
                            el.innerHTML = `${data.eta_minutes} min`;
                            el.className = 'track-stat-value text-warning'; // Keep warning color for numbers
                        } else {
                            el.innerHTML = '<span class="text-muted">Check in</span>';
                            el.className = 'track-stat-value'; // Remove warning color for "Check in"
                        }
                    });
                    
                    // Handle pause message display
                    const pauseAlertId = 'dynamic-pause-alert';
                    let pauseAlert = document.getElementById(pauseAlertId);
                    
                    if (data.pause_message && data.queue_paused) {
                        // Show pause alert if not already visible
                        if (!pauseAlert) {
                            const alertDiv = document.createElement('div');
                            alertDiv.id = pauseAlertId;
                            alertDiv.className = 'alert alert-warning alert-dismissible fade show';
                            alertDiv.role = 'alert';
                            alertDiv.innerHTML = `
                                <strong>⏸ Queue Paused!</strong> ${data.pause_message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            // Insert after the last session alert
                            const sessionAlerts = document.querySelectorAll('[role="alert"]');
                            if (sessionAlerts.length > 0) {
                                sessionAlerts[sessionAlerts.length - 1].parentNode.insertBefore(alertDiv, sessionAlerts[sessionAlerts.length - 1].nextSibling);
                            } else {
                                // Fallback: insert after the debug alert
                                const debugAlert = document.querySelector('.alert.alert-info.small');
                                if (debugAlert) {
                                    debugAlert.parentNode.insertBefore(alertDiv, debugAlert.nextSibling);
                                }
                            }
                        } else {
                            // Update existing alert message
                            pauseAlert.querySelector('strong').nextSibling.textContent = ' ' + data.pause_message;
                        }
                    } else if (pauseAlert) {
                        // Remove pause alert if queue is no longer paused
                        pauseAlert.remove();
                    }
                    
                    console.log('✅ Tracking data updated:', { status: data.status, eta: data.eta_minutes, queue: data.queue_number, paused: data.queue_paused });
                })
                .catch(error => console.error('❌ Error updating tracking data:', error));
        }

        // Handle check-in form submission with logging
        function handleCheckInSubmit(event) {
            console.log('🔘 Check-In button clicked');
            console.log('📝 Form target:', event.target.action);
            console.log('📤 Preparing to submit form to:', event.target.action);
            
            // Get the hidden CSRF token
            const csrfToken = document.querySelector('input[name="_token"]');
            if (!csrfToken) {
                console.error('❌ CSRF token not found!');
            } else {
                console.log('✅ CSRF token found');
            }
            
            // Log form data
            const formData = new FormData(event.target);
            console.log('📋 Form data being sent:', {
                csrf: formData.get('_token') ? 'present' : 'missing',
                action: event.target.action
            });
            
            // Allow form to submit normally
            console.log('✅ Allowing form submission to proceed...');
            return true;
        }

        // Call immediately on page load for fresh data
        document.addEventListener('DOMContentLoaded', function() {
            updateTrackingData();
            
            // Update every 2 seconds for real-time sync with staff dashboard
            // This ensures patients see updates quickly when staff calls them
            setInterval(updateTrackingData, 2000);
        });
        
        // Also try to call it right away in case DOMContentLoaded already fired
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', updateTrackingData);
        } else {
            updateTrackingData();
        }
    </script>
</div>
@endsection
