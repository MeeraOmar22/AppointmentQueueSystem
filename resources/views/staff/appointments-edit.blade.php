@extends('layouts.staff')

@section('title', 'Edit Appointment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Edit Appointment</h3>
        <p class="text-muted mb-0">Update appointment details</p>
    </div>
    <a href="/staff/appointments" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="/staff/appointments/{{ $appointment->id }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Patient Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('patient_name') is-invalid @enderror" 
                                   name="patient_name" value="{{ old('patient_name', $appointment->patient_name) }}" required>
                            @error('patient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Patient Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('patient_phone') is-invalid @enderror" 
                                   name="patient_phone" value="{{ old('patient_phone', $appointment->patient_phone) }}" required>
                            @error('patient_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Patient Email</label>
                        <input type="email" class="form-control @error('patient_email') is-invalid @enderror" 
                               name="patient_email" value="{{ old('patient_email', $appointment->patient_email) }}">
                        @error('patient_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Clinic Location <span class="text-danger">*</span></label>
                        <select class="form-select @error('clinic_location') is-invalid @enderror" name="clinic_location" required>
                            <option value="">Select Location</option>
                            <option value="seremban" {{ old('clinic_location', $appointment->clinic_location) == 'seremban' ? 'selected' : '' }}>
                                Seremban - No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, 70450 Seremban
                            </option>
                            <option value="kuala_pilah" {{ old('clinic_location', $appointment->clinic_location) == 'kuala_pilah' ? 'selected' : '' }}>
                                Kuala Pilah - No. 902, Jalan Raja Melewar, 72000 Kuala Pilah
                            </option>
                        </select>
                        @error('clinic_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                            <select class="form-select @error('service_id') is-invalid @enderror" name="service_id" required>
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" 
                                            {{ old('service_id', $appointment->service_id) == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }} ({{ $service->estimated_duration }} min - RM {{ number_format($service->price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dentist <span class="text-danger">*</span></label>
                            <select class="form-select @error('dentist_id') is-invalid @enderror" name="dentist_id" id="dentistSelect" required>
                                <option value="">Select Dentist</option>
                                @foreach($dentists as $dentist)
                                    <option value="{{ $dentist->id }}" 
                                            {{ old('dentist_id', $appointment->queue?->dentist_id) == $dentist->id ? 'selected' : '' }}>
                                        {{ $dentist->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dentist_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                System will validate dentist availability when you save changes
                            </small>
                        </div>
                    </div>

                    <!-- Availability Info Message (outside grid to prevent layout shifts) -->
                    <div id="availabilityInfo" style="display: none;"></div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned Room (Optional)</label>
                            <select class="form-select @error('room_id') is-invalid @enderror" name="room_id" id="roomSelect">
                                <option value="">No Room Assignment</option>
                                @if(isset($rooms))
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" 
                                                {{ old('room_id', optional($appointment->queue)->room_id) == $room->id ? 'selected' : '' }}>
                                            {{ $room->room_number }} - {{ $room->capacity }} seats
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-1">
                                <i class="bi bi-info-circle me-1"></i>
                                Rooms are validated for conflicts when you save changes
                            </small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                   name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date?->format('Y-m-d')) }}" required>
                            @error('appointment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                                   name="appointment_time" value="{{ old('appointment_time', \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i')) }}" required>
                            @error('appointment_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Status: </strong> <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}</span>
                            <br>
                            <small class="d-block mt-2">To change appointment status, use the action buttons below or the Queue Board.</small>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @if($appointment->status->value !== 'completed')
                            <form method="POST" action="/staff/appointments/{{ $appointment->id }}/complete-treatment" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" title="Mark treatment as completed">
                                    <i class="bi bi-check-circle me-1"></i>Mark Complete
                                </button>
                            </form>
                        @endif
                        
                        @if($appointment->status->value !== 'in_treatment')
                            <form method="POST" action="/staff/appointments/{{ $appointment->id }}/update-status" style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="in_treatment">
                                <button type="submit" class="btn btn-warning btn-sm" title="Move to in treatment">
                                    <i class="bi bi-play-circle me-1"></i>Start Treatment
                                </button>
                            </form>
                        @endif
                        
                        @if($appointment->status->value === 'booked')
                            <form method="POST" action="/staff/appointments/{{ $appointment->id }}/update-status" style="display:inline;">
                                @csrf
                                <input type="hidden" name="status" value="checked_in">
                                <button type="submit" class="btn btn-info btn-sm" title="Mark patient as checked in">
                                    <i class="bi bi-check me-1"></i>Check In
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Appointment
                        </button>
                        <a href="/staff/appointments" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-shield-check text-success me-2"></i>Resource Validation
                </h6>
                <div class="alert alert-info alert-sm mb-0">
                    <small>
                        <strong>Dentist Availability:</strong> The system will verify that the selected dentist has no conflicting appointments at the chosen date and time.
                    </small>
                </div>
                <div class="alert alert-info alert-sm mb-0 mt-2">
                    <small>
                        <strong>Room Availability:</strong> If a room is selected, the system will check for scheduling conflicts with other appointments.
                    </small>
                </div>
                <div class="alert alert-warning alert-sm mb-0 mt-2">
                    <small>
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        If conflicts exist, the system will reject your changes with a clear error message.
                    </small>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Appointment Info</h5>
                <div class="mb-3">
                    <small class="text-muted d-block">Clinic Location</small>
                    <strong>{{ $appointment->clinic_location === 'seremban' ? 'Seremban' : 'Kuala Pilah' }}</strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Booking Source</small>
                    <span class="badge {{ $appointment->booking_source === 'public' ? 'bg-info' : 'bg-warning' }}">
                        {{ ucfirst($appointment->booking_source ?? 'N/A') }}
                    </span>
                </div>
                @if($appointment->queue)
                    <div class="mb-3">
                        <small class="text-muted d-block">Queue Number</small>
                        <strong class="h5">A-{{ str_pad($appointment->queue->queue_number ?? 0, 2, '0', STR_PAD_LEFT) }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Queue Status</small>
                        <span class="badge 
                            @if($appointment->queue->queue_status === 'waiting') badge-soft badge-waiting
                            @elseif($appointment->queue->queue_status === 'called') badge-soft badge-called
                            @elseif($appointment->queue->queue_status === 'in_treatment') badge-soft badge-intreatment
                            @elseif($appointment->queue->queue_status === 'completed') badge-soft badge-completed
                            @else bg-secondary
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $appointment->queue->queue_status)) }}
                        </span>
                    </div>
                    @if($appointment->queue->check_in_time)
                        <div class="mb-3">
                            <small class="text-muted d-block">Check-in Time</small>
                            <strong>{{ \Carbon\Carbon::parse($appointment->queue->check_in_time)->format('H:i, d M Y') }}</strong>
                        </div>
                    @endif
                @endif
                <div class="mb-3">
                    <small class="text-muted d-block">Created</small>
                    <strong>{{ $appointment->created_at->format('H:i, d M Y') }}</strong>
                </div>
                <div>
                    <small class="text-muted d-block">Last Updated</small>
                    <strong>{{ $appointment->updated_at->format('H:i, d M Y') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="appointment_date"]');
    const timeInput = document.querySelector('input[name="appointment_time"]');
    const dentistSelect = document.getElementById('dentistSelect');
    
    // Store original dentist names
    const originalDentistOptions = {};
    Array.from(dentistSelect.options).forEach(option => {
        if (option.value) {
            originalDentistOptions[option.value] = option.textContent;
        }
    });

    const availabilityInfo = document.getElementById('availabilityInfo');

    // Check availability when date/time changes
    function checkDentistAvailability() {
        if (!dateInput.value || !timeInput.value) {
            availabilityInfo.style.display = 'none';
            resetDentistOptions();
            return;
        }

        const date = dateInput.value;
        const time = timeInput.value;
        const excludeAppointmentId = {{ $appointment->id }};

        // Show loading state
        availabilityInfo.innerHTML = '<small><i class="bi bi-hourglass-split me-2"></i>Checking availability...</small>';
        availabilityInfo.style.display = 'block';
        availabilityInfo.className = 'alert alert-info alert-sm mt-2';

        fetch(`/api/staff/available-dentists?date=${date}&time=${time}&exclude_appointment_id=${excludeAppointmentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const availableCount = data.data.dentists.length;
                    const availableIds = data.data.dentists.map(d => d.id);
                    
                    // Update dentist dropdown
                    Array.from(dentistSelect.options).forEach(option => {
                        if (option.value === '') return; // Keep the default option
                        const isAvailable = availableIds.includes(parseInt(option.value));
                        option.disabled = !isAvailable;
                        // Restore original text and add availability indicator
                        const originalText = originalDentistOptions[option.value];
                        option.textContent = isAvailable ? originalText : originalText + ' (Not available)';
                    });

                    const message = availableCount > 0 
                        ? `<small><i class="bi bi-check-circle text-success me-2"></i><strong>${availableCount} dentist(s) available</strong> at ${time} on ${date}</small>`
                        : '<small><i class="bi bi-x-circle text-danger me-2"></i><strong>No dentists available</strong> at this date and time</small>';
                    
                    availabilityInfo.innerHTML = message;
                    availabilityInfo.className = availableCount > 0 
                        ? 'alert alert-success alert-sm mt-1' 
                        : 'alert alert-danger alert-sm mt-1';
                } else {
                    const errorMsg = data.error || 'Unknown error';
                    console.warn('Availability check returned error:', errorMsg);
                    availabilityInfo.innerHTML = `<small><i class="bi bi-exclamation-circle me-2"></i>${errorMsg}</small>`;
                    availabilityInfo.className = 'alert alert-warning alert-sm mt-1';
                    resetDentistOptions();
                }
            })
            .catch(error => {
                console.error('Availability check network error:', error);
                // Show warning but don't block form submission
                availabilityInfo.innerHTML = '<small><i class="bi bi-exclamation-triangle me-2"></i>Could not verify availability (you can still submit)</small>';
                availabilityInfo.className = 'alert alert-warning alert-sm mt-1';
                resetDentistOptions();
            });
    }

    function resetDentistOptions() {
        Array.from(dentistSelect.options).forEach(option => {
            if (option.value) {
                option.disabled = false;
                option.textContent = originalDentistOptions[option.value];
            }
        });
    }

    // Listen for changes
    if (dateInput) dateInput.addEventListener('change', checkDentistAvailability);
    if (timeInput) timeInput.addEventListener('change', checkDentistAvailability);

    // Check on page load if date/time are already set
    checkDentistAvailability();
});
</script>
@endsection
