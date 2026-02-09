@extends('layouts.public')

@section('title', 'Cannot Reschedule Appointment')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Error Card -->
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center py-5">
                    <!-- Error Icon -->
                    <div class="mb-4">
                        <i class="bi bi-calendar-x text-danger" style="font-size: 4rem;"></i>
                    </div>

                    <!-- Title -->
                    <h2 class="card-title h3 fw-bold mb-3">Rescheduling Window Closed</h2>

                    <!-- Message -->
                    <p class="card-text text-muted mb-4" style="font-size: 1.1rem;">
                        {{ $message }}
                    </p>

                    <!-- Time Remaining -->
                    <div class="alert alert-warning mb-4" role="alert">
                        <h6 class="alert-heading mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Only {{ $hoursRemaining }} hour{{ $hoursRemaining !== 1 ? 's' : '' }} until appointment
                        </h6>
                        <p class="mb-0 small">
                            Rescheduling requires at least 24 hours advance notice to ensure we have sufficient time to adjust our schedule.
                        </p>
                    </div>

                    <!-- Appointment Details -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">Your Appointment</h6>
                            <div class="row text-start">
                                <div class="col-6 mb-3">
                                    <strong>Date & Time:</strong><br>
                                    <span class="text-primary">
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                        <br>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i A') }}
                                    </span>
                                </div>
                                <div class="col-6 mb-3">
                                    <strong>Service:</strong><br>
                                    <span>{{ $appointment->service?->name ?? 'N/A' }}</span>
                                </div>
                                @if($appointment->dentist)
                                <div class="col-12">
                                    <strong>Dentist:</strong><br>
                                    <span>Dr. {{ $appointment->dentist->name }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Alternative Options -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading mb-3">
                            <i class="bi bi-lightbulb me-2"></i>
                            What You Can Do
                        </h6>
                        <ul class="mb-0 text-start small">
                            <li class="mb-2">
                                <strong>Contact the clinic directly</strong> - Our staff can help reschedule your appointment
                            </li>
                            <li class="mb-2">
                                <strong>Cancel & rebook</strong> - You can cancel this appointment and book a new one
                            </li>
                            <li>
                                <strong>Confirm attendance</strong> - If possible, please keep your appointment as scheduled
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="/contact" class="btn btn-primary btn-lg mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            Contact Clinic
                        </a>
                        <a href="/track/{{ $appointment->visit_code }}" class="btn btn-outline-primary btn-lg mb-2">
                            <i class="bi bi-arrow-left me-2"></i>
                            Back to Appointment
                        </a>
                        <a href="/book" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Book Another Appointment
                        </a>
                    </div>

                    <!-- Contact Info -->
                    <div class="mt-5 text-muted small">
                        <p class="mb-2">
                            <i class="bi bi-telephone me-2"></i>
                            <strong>Call us to reschedule:</strong> +60 6-761 2888
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-clock me-2"></i>
                            <strong>Clinic Hours:</strong> 9:00 AM - 5:00 PM
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    .alert {
        border-radius: 8px;
    }
</style>
@endsection
