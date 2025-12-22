@extends('layouts.public')

@section('title', 'Find Your Appointment - Klinik Pergigian Helmy')

@section('content')
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-4 text-white">Find Your Visit</h1>
            <span class="h6 text-white">Search for your appointment using your phone number and date</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if($found)
                <!-- Appointment Found -->
                <div class="bg-light p-5 rounded shadow-sm mb-4 wow fadeInUp">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 60px;"></i>
                        <h4 class="text-success mt-3">Appointment Found!</h4>
                    </div>
                    
                    <div class="mb-4 p-4 bg-white rounded border border-success border-opacity-25">
                        <div class="row mb-3">
                            <div class="col-sm-5 text-muted small">Patient Name</div>
                            <div class="col-sm-7 fw-semibold">{{ $appointment->patient_name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5 text-muted small">Phone Number</div>
                            <div class="col-sm-7 fw-semibold">{{ $appointment->patient_phone }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5 text-muted small">Appointment Date</div>
                            <div class="col-sm-7 fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-5 text-muted small">Appointment Time</div>
                            <div class="col-sm-7 fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 text-muted small">Service</div>
                            <div class="col-sm-7 fw-semibold">{{ $appointment->service->name ?? $appointment->service->service_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ url('/visit/' . $appointment->visit_token) }}" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i>View My Visit Status
                        </a>
                    </div>
                    
                    <div class="alert alert-info border-0" role="alert">
                        <small>
                            <i class="bi bi-lightbulb me-2"></i>
                            Your booking code has been saved to your browser. You can use "Track My Visit" button anytime to access this status.
                        </small>
                    </div>
                </div>
            @else
                <!-- No Appointment Found -->
                <div class="bg-light p-5 rounded shadow-sm mb-4 wow fadeInUp">
                    <div class="text-center mb-4">
                        <i class="bi bi-question-circle text-warning" style="font-size: 60px;"></i>
                        <h4 class="text-warning mt-3">Appointment Not Found</h4>
                    </div>
                    
                    <p class="text-muted mb-4 text-center">
                        We couldn't find an appointment matching the phone number and date you provided.
                    </p>
                    
                    <form method="GET" action="{{ url('/visit-lookup') }}" class="mb-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="tel" class="form-control form-control-lg" name="phone" value="{{ request('phone') }}" placeholder="Your phone number" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Appointment Date</label>
                            <input type="date" class="form-control form-control-lg" name="date" value="{{ request('date') }}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-search me-2"></i>Search Again
                        </button>
                    </form>
                    
                    <div class="bg-white p-4 rounded border border-warning border-opacity-25 mb-4">
                        <h6 class="fw-bold mb-3">Why can't we find your appointment?</h6>
                        <ul class="small text-muted mb-0">
                            <li class="mb-2">The phone number or date might be slightly different</li>
                            <li class="mb-2">Your appointment may not have been saved yet</li>
                            <li>Double-check the information and try again</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning border-0" role="alert">
                        <strong>Need help?</strong> Call us at <strong>06-677 1940</strong> or email <strong>klinikgigihelmy@gmail.com</strong>
                    </div>
                </div>
            @endif
            
            <!-- Back to Home -->
            <div class="text-center">
                <a href="{{ url('/') }}" class="btn btn-outline-primary">
                    <i class="bi bi-house me-2"></i>Back to Home
                </a>
            </div>

            <!-- Operating Hours Section -->
            <div class="bg-light p-4 rounded shadow-sm mt-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Today's Operating Hours</h6>
                @if($operatingHours && $operatingHours->isNotEmpty())
                    @php
                        $today = now()->format('l');
                        $todayHours = $operatingHours->where('day_of_week', $today);
                    @endphp
                    @if($todayHours->isNotEmpty())
                        @foreach($todayHours as $hour)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-muted">{{ $today }}</span>
                                @if($hour->is_closed)
                                    <span class="badge bg-danger">Closed</span>
                                @else
                                    <span class="text-muted small">
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

<!-- Auto-Save Token Script -->
@if($found)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const visitToken = '{{ $appointment->visit_token }}';
        localStorage.setItem('visit_token', visitToken);
        localStorage.setItem('appointment_data_' + visitToken, JSON.stringify({
            token: visitToken,
            patientName: '{{ $appointment->patient_name }}',
            patientPhone: '{{ $appointment->patient_phone }}',
            appointmentDate: '{{ $appointment->appointment_date }}',
            appointmentTime: '{{ $appointment->appointment_time }}',
            retrievedAt: new Date().toISOString()
        }));
    });
</script>
@endif
@endsection
