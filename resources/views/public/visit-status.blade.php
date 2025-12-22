@extends('layouts.public')

@section('title', 'Visit Status')

@section('content')
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-4 text-white">Your Visit Status</h1>
            <span class="h6 text-white">Appointment for {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }} @ {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Main Status Card -->
            <div class="bg-light p-4 rounded shadow-sm mb-4 wow fadeInUp">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
                    <div>
                        <div class="text-muted small">Patient Name</div>
                        <div class="fw-bold h5 mb-0">{{ $appointment->patient_name }}</div>
                        <div class="text-muted small">{{ $appointment->patient_phone }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted small">Service</div>
                        <div class="fw-semibold">{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</div>
                        <div class="text-muted small">Dentist: <strong>{{ $appointment->dentist->name ?? 'To be assigned' }}</strong></div>
                    </div>
                </div>
                <hr>
                
                <!-- Queue Status -->
                <div class="row text-center">
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <div class="text-muted small mb-2">Queue Number</div>
                        <div class="display-6 fw-bold text-primary">{{ $queueNumber ?? '—' }}</div>
                    </div>
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <div class="text-muted small mb-2">Queue Status</div>
                        <div class="fw-semibold">
                            <span class="badge bg-info text-dark text-capitalize" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                {{ str_replace('_', ' ', $queueStatus) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="text-muted small mb-2">Estimated Wait</div>
                        <div class="fw-semibold text-warning">{{ $etaMinutes !== null ? $etaMinutes . ' min' : 'TBD' }}</div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="alert alert-info border-0 shadow-sm mb-4 wow fadeInUp" data-wow-delay="0.1s" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle fs-4 me-3 text-primary flex-shrink-0 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-2">How does this work?</h6>
                        <p class="small mb-0">
                            <strong>No login needed.</strong> Your visit status is tracked using a unique booking code stored securely in your browser. 
                            You can check your status anytime from this device without creating an account.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Queue Timeline -->
            <div class="bg-white p-4 rounded shadow-sm mb-4 wow fadeInUp" data-wow-delay="0.2s">
                <h6 class="fw-bold mb-4">Visit Timeline</h6>
                <div class="row g-0">
                    <div class="col-12">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold">Appointment Booked</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->created_at)->format('d M Y, H:i') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($queueNumber && $queueStatus !== 'not-queued')
                    <div class="row g-0 mt-2">
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">Queued</div>
                                    <small class="text-muted">Queue #{{ $queueNumber }} {{ $etaMinutes !== null ? '- Est. wait: ' . $etaMinutes . ' min' : '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($appointment->checked_in_at)
                    <div class="row g-0 mt-2">
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-check-square"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-semibold">Checked In</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->checked_in_at)->format('d M Y, H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Stateless Tracking Feature -->
            <div class="bg-success bg-opacity-10 p-4 rounded shadow-sm mb-4 wow fadeInUp" data-wow-delay="0.3s" style="border-left: 4px solid #28a745;">
                <div class="d-flex align-items-start">
                    <i class="bi bi-shield-check text-success fs-4 me-3 flex-shrink-0 mt-1"></i>
                    <div>
                        <h6 class="fw-bold text-success mb-2">✓ Stateless Tracking Enabled</h6>
                        <p class="small text-muted mb-2">
                            Your booking code is stored in your browser's local storage. This means:
                        </p>
                        <ul class="small text-muted mb-0">
                            <li class="mb-1">📱 Works on any device - just use the "Track My Visit" button</li>
                            <li class="mb-1">🔄 Survives browser refresh - your token persists</li>
                            <li class="mb-1">🚫 No account required - completely anonymous</li>
                            <li>🔒 Secure - only you can access your appointment details</li>
                        </ul>
                    </div>
                </div>
            </div>

            @if(\Carbon\Carbon::parse($appointment->appointment_date)->isToday() && !$appointment->checked_in_at)
            <!-- I've Arrived Button -->
            <div class="bg-white p-4 rounded shadow-sm mb-4 wow fadeInUp" data-wow-delay="0.35s">
                <h6 class="fw-bold mb-3">Arrived at the clinic?</h6>
                <form method="POST" action="{{ url('/visit/' . $appointment->visit_token . '/check-in') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-2"></i>I've arrived
                    </button>
                </form>
                <p class="small text-muted mt-2 mb-0">Tap to notify the system and activate your queue status.</p>
            </div>
            @endif

            <!-- Different Phone Alert -->
            <div class="alert alert-warning border-0 mb-4 wow fadeInUp" data-wow-delay="0.4s">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle fs-4 me-3 text-warning flex-shrink-0 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Using a different device?</h6>
                        <p class="small mb-2">
                            If you're on a different phone or device, use the "Can't find your visit?" option on the homepage. 
                            Just provide your phone number and appointment date to find your status.
                        </p>
                        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-warning mt-2">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Support Section -->
            <div class="bg-light p-4 rounded shadow-sm text-center wow fadeInUp" data-wow-delay="0.5s">
                <h6 class="fw-bold mb-3">Need Help?</h6>
                <p class="text-muted small mb-3">If you have any questions, feel free to contact us:</p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <a href="tel:06-677 1940" class="btn btn-sm btn-primary">
                        <i class="bi bi-telephone me-2"></i>Call Us
                    </a>
                    <a href="mailto:klinikgigihelmy@gmail.com" class="btn btn-sm btn-primary">
                        <i class="bi bi-envelope me-2"></i>Email Us
                    </a>
                    <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="btn btn-sm btn-primary">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a>
                </div>
            </div>

            <!-- Operating Hours Section -->
            <div class="bg-white p-4 rounded shadow-sm mb-4 wow fadeInUp" data-wow-delay="0.55s">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Today's Operating Hours</h6>
                @if($operatingHours && $operatingHours->isNotEmpty())
                    @php
                        $today = now()->format('l');
                        $todayHours = $operatingHours->where('day_of_week', $today);
                    @endphp
                    @if($todayHours->isNotEmpty())
                        @foreach($todayHours as $hour)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold">{{ $today }}</span>
                                @if($hour->is_closed)
                                    <span class="badge bg-danger">Closed</span>
                                @else
                                    <span class="text-muted">
                                        {{ date('g:i a', strtotime($hour->start_time)) }} - {{ date('g:i a', strtotime($hour->end_time)) }}
                                        @if($hour->session_label)
                                            <span class="badge bg-light text-dark ms-2">{{ $hour->session_label }}</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">Hours not configured</p>
                    @endif
                @else
                    <p class="text-muted small mb-0">Hours not available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Store Token & Add Auto-Refresh -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Store token in localStorage for stateless tracking
        const token = window.location.pathname.split('/').pop();
        localStorage.setItem('visit_token', token);
        
        // Optional: Auto-refresh every 30 seconds to get latest queue status
        setInterval(function() {
            location.reload();
        }, 30000);
    });
</script>
@endsection
