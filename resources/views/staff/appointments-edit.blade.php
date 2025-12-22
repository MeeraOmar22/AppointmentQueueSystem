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
                            <select class="form-select @error('dentist_id') is-invalid @enderror" name="dentist_id" required>
                                <option value="">Select Dentist</option>
                                @foreach($dentists as $dentist)
                                    <option value="{{ $dentist->id }}" 
                                            {{ old('dentist_id', $appointment->dentist_id) == $dentist->id ? 'selected' : '' }}>
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
                            <input type="text" class="form-control @error('room') is-invalid @enderror" name="room" value="{{ old('room', $appointment->room) }}" placeholder="e.g., Room 1">
                            @error('room')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                   name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date) }}" required>
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
                        <select class="form-select @error('status') is-invalid @enderror" name="status" required>
                            <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="booked" {{ old('status', $appointment->status) == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
                        <strong class="h5">{{ $appointment->queue->queue_number ?? 'Not Assigned' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Queue Status</small>
                        <span class="badge 
                            @if($appointment->queue->queue_status === 'waiting') badge-soft badge-waiting
                            @elseif($appointment->queue->queue_status === 'in_service') badge-soft badge-inservice
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
@endsection
