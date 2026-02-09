@extends('layouts.public')

@section('title', 'Check In')

@section('content')
<div class="container-fluid bg-primary py-3 mb-4">
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="text-white mb-0">Check In</h2>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="text-center mb-4">Ready to Check In?</h4>

                    <div class="bg-light p-4 rounded mb-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="text-muted small">Patient Name</div>
                                <div class="fw-semibold fs-5">{{ $appointment->patient_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Service</div>
                                <div class="fw-semibold">{{ $appointment->service->name ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small">Appointment Time</div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">Dentist</div>
                                <div class="fw-semibold">{{ $appointment->dentist?->name ?? 'Any Available' }}</div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('appointment.checkin', $appointment->visit_code) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-semibold">
                            <i class="bi bi-check-circle me-2"></i>Confirm Check In
                        </button>
                    </form>

                    <a href="{{ url('/track') }}" class="btn btn-outline-secondary btn-lg w-100">
                        <i class="bi bi-arrow-left me-2"></i>Go Back
                    </a>

                    <div class="alert alert-info small mt-4 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Clicking "Confirm Check In" will notify the clinic staff that you have arrived.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
