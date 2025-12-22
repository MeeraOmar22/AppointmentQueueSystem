@extends('layouts.public')

@section('title', 'Find My Booking')

@section('content')
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-4 text-white">Find My Booking</h1>
            <span class="h6 text-white">Search using your phone number</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if(session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            <div class="bg-light p-5 rounded shadow-sm mb-4">
                <div class="text-center mb-4">
                    <i class="bi bi-search text-primary" style="font-size: 48px;"></i>
                    <h5 class="fw-bold mt-3">Search Your Appointment</h5>
                    <p class="text-muted small">Enter the phone number you used when booking</p>
                </div>

                <form method="POST" action="{{ url('/find-my-booking') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control form-control-lg" placeholder="012-345-6789 or 60123456789" required>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="bi bi-search me-2"></i>Find My Booking
                    </button>
                </form>
            </div>

            <div class="bg-white p-4 rounded border mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-shield-check text-success me-2"></i>Safe & Secure</h6>
                <ul class="small text-muted mb-0">
                    <li class="mb-2">Only shows today's and upcoming appointments</li>
                    <li class="mb-2">No login or password required</li>
                    <li>Your information is protected</li>
                </ul>
            </div>

            <div class="alert alert-info border-0" role="alert">
                <i class="bi bi-lightbulb me-2"></i>
                <strong>Can't find your booking?</strong> Make sure you're using the same phone number you provided when booking. Contact us at <strong>06-677 1940</strong> for assistance.
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
@endsection
