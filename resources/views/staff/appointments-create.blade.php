@extends('layouts.staff')

@section('title', 'Create Appointment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Create New Appointment</h3>
        <p class="text-muted mb-0">Schedule a new appointment manually</p>
    </div>
    <a href="/staff/appointments" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2" style="font-size: 0.9rem;"></i>Back
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
                <form method="POST" action="/staff/appointments">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Patient Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('patient_name') is-invalid @enderror" 
                                   name="patient_name" value="{{ old('patient_name') }}" required>
                            @error('patient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Patient Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('patient_phone') is-invalid @enderror" 
                                   name="patient_phone" value="{{ old('patient_phone') }}" required>
                            @error('patient_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Patient Email</label>
                        <input type="email" class="form-control @error('patient_email') is-invalid @enderror" 
                               name="patient_email" value="{{ old('patient_email') }}">
                        @error('patient_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Clinic Location <span class="text-danger">*</span></label>
                        <select class="form-select @error('clinic_location') is-invalid @enderror" name="clinic_location" required>
                            <option value="">Select Location</option>
                            <option value="seremban" {{ old('clinic_location') == 'seremban' ? 'selected' : '' }}>
                                Seremban - No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, 70450 Seremban
                            </option>
                            <option value="kuala_pilah" {{ old('clinic_location') == 'kuala_pilah' ? 'selected' : '' }}>
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
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
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
                            <select class="form-select @error('dentist_id') is-invalid @enderror" name="dentist_id" required>
                                <option value="">Select Dentist</option>
                                @foreach($dentists as $dentist)
                                    <option value="{{ $dentist->id }}" {{ old('dentist_id') == $dentist->id ? 'selected' : '' }}>
                                        {{ $dentist->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dentist_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned Room</label>
                            <input type="text" class="form-control @error('room') is-invalid @enderror" name="room" value="{{ old('room') }}" placeholder="e.g., Room 1">
                            @error('room')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                   name="appointment_date" value="{{ old('appointment_date') }}" required>
                            @error('appointment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                                   name="appointment_time" value="{{ old('appointment_time') }}" required>
                            @error('appointment_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="booked" {{ old('status', 'booked') == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2" style="font-size: 0.9rem;"></i>Create Appointment
                        </button>
                        <a href="/staff/appointments" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-info-circle text-primary me-2" style="font-size: 1rem;"></i>Information
                </h5>
                <p class="mb-2"><strong>Manual Booking</strong></p>
                <p class="text-muted small mb-3">
                    This form allows you to manually create appointments for patients. The appointment will be marked with "staff" as the booking source.
                </p>
                
                <p class="mb-2"><strong>Queue Assignment</strong></p>
                <p class="text-muted small mb-3">
                    Queue numbers are not automatically assigned. Use the "Check In" button on the appointments page to add the appointment to today's queue.
                </p>
                
                <p class="mb-2"><strong>Visit Token</strong></p>
                <p class="text-muted small mb-0">
                    A unique visit token will be automatically generated for patient status tracking.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
