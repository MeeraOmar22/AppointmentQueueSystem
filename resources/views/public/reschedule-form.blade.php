@extends('layouts.public')

@section('title', 'Reschedule Appointment')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="mb-5">
                <h1 class="h2 fw-bold mb-3">
                    <i class="bi bi-calendar-check text-primary me-2"></i>
                    Reschedule Your Appointment
                </h1>
                <p class="text-muted">Select a new date and time for your appointment</p>
            </div>

            <!-- Current Appointment Card -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title text-muted mb-3">Current Appointment</h5>
                            <div class="appointment-detail mb-3">
                                <strong>Date & Time:</strong><br>
                                <span class="h6 text-primary">{{ $currentDateTime }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title text-muted mb-3">Service Details</h5>
                            <div class="appointment-detail mb-3">
                                <strong>Service:</strong><br>
                                <span>{{ $service->name ?? 'N/A' }}</span>
                            </div>
                            @if($dentist)
                            <div class="appointment-detail">
                                <strong>Dentist:</strong><br>
                                <span>Dr. {{ $dentist->name ?? 'TBD' }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reschedule Form -->
            <form action="{{ route('appointment.reschedule', $appointment->visit_code) }}" method="POST" class="needs-validation" novalidate>
                @csrf

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bi bi-arrow-right text-success me-2"></i>
                            New Appointment
                        </h5>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <h6 class="alert-heading">Unable to Reschedule</h6>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Date Selection -->
                        <div class="mb-4">
                            <label for="appointment_date" class="form-label fw-semibold">
                                <i class="bi bi-calendar me-2 text-primary"></i>
                                Select Date
                            </label>
                            
                            @if(count($availableSlots) > 0)
                                <select class="form-select form-select-lg" id="appointment_date" name="appointment_date" required onchange="updateTimeSlots()">
                                    <option value="">-- Choose a date --</option>
                                    @foreach($availableSlots as $daySlots)
                                        <option value="{{ $daySlots['date'] }}" data-slots="{{ json_encode($daySlots['slots']) }}">
                                            {{ $daySlots['formattedDate'] }} ({{ count($daySlots['slots']) }} slots available)
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Available dates in the next 30 days</div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No available slots in the next 30 days. Please contact the clinic.
                                </div>
                            @endif

                            <div class="invalid-feedback">
                                Please select a date.
                            </div>
                        </div>

                        <!-- Time Selection -->
                        <div class="mb-4">
                            <label for="appointment_time" class="form-label fw-semibold">
                                <i class="bi bi-clock me-2 text-primary"></i>
                                Select Time
                            </label>
                            
                            <select class="form-select form-select-lg" id="appointment_time" name="appointment_time" required disabled>
                                <option value="">-- Select a date first --</option>
                            </select>
                            <div class="form-text">Available time slots for the selected date</div>

                            <div class="invalid-feedback">
                                Please select a time.
                            </div>
                        </div>

                        <!-- Time Display -->
                        <div id="timeDisplay" class="mb-4" style="display: none;">
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Your new appointment will be on:</strong><br>
                                <span class="h6 text-primary" id="selectedDateTime"></span>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>

                        <!-- Important Notice -->
                        <div class="alert alert-light border mb-4" style="background-color: #f8f9fa;">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle text-info me-2"></i>
                                Important Information
                            </h6>
                            <ul class="mb-0 small">
                                <li>Your appointment has been rescheduled successfully</li>
                                <li>A confirmation message will be sent to your phone</li>
                                <li>Please arrive 5-10 minutes early</li>
                                <li>You can track your appointment anytime via the tracking link</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="card-footer bg-light border-top d-flex gap-2">
                        <a href="/track/{{ $appointment->visit_code }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Back to Tracking
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg ms-auto" id="submitBtn">
                            <i class="bi bi-check-circle me-2"></i>
                            Confirm Reschedule
                        </button>
                    </div>
                </div>
            </form>

            <!-- Help Text -->
            <div class="mt-4 text-center text-muted small">
                <p>
                    <i class="bi bi-question-circle me-2"></i>
                    Having trouble rescheduling?
                    <a href="/contact" class="text-primary">Contact us for assistance</a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    .appointment-detail {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .appointment-detail:last-child {
        border-bottom: none;
    }

    .form-select-lg {
        padding: 12px 16px;
        font-size: 16px;
    }

    .card {
        border-radius: 12px;
    }

    .card-body {
        padding: 2rem;
    }
</style>

<script>
    function updateTimeSlots() {
        const dateSelect = document.getElementById('appointment_date');
        const timeSelect = document.getElementById('appointment_time');
        const timeDisplay = document.getElementById('timeDisplay');
        const selectedDateTime = document.getElementById('selectedDateTime');

        if (!dateSelect.value) {
            timeSelect.disabled = true;
            timeSelect.innerHTML = '<option value="">-- Select a date first --</option>';
            timeDisplay.style.display = 'none';
            return;
        }

        // Get slots from selected option
        const selectedOption = dateSelect.options[dateSelect.selectedIndex];
        const slots = JSON.parse(selectedOption.dataset.slots || '[]');

        // Update time select options
        timeSelect.innerHTML = '<option value="">-- Choose a time --</option>';
        slots.forEach(slot => {
            const option = document.createElement('option');
            option.value = slot;
            option.textContent = slot;
            timeSelect.appendChild(option);
        });

        timeSelect.disabled = false;
        timeDisplay.style.display = 'none';
    }

    // Update time display when time is selected
    document.getElementById('appointment_time').addEventListener('change', function() {
        const dateValue = document.getElementById('appointment_date').value;
        const timeValue = this.value;

        if (dateValue && timeValue) {
            const dateObj = new Date(dateValue);
            const dateFormatted = dateObj.toLocaleDateString('en-US', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            document.getElementById('selectedDateTime').textContent = dateFormatted + ' at ' + timeValue;
            document.getElementById('timeDisplay').style.display = 'block';
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        this.classList.add('was-validated');
    });
</script>
@endsection
