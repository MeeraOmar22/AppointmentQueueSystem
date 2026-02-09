@extends('layouts.public')

@section('title', 'Book Appointment - Klinik Pergigian Helmy')

@section('content')
<!-- Quick inline CSS to hide inactive steps immediately -->
<style>
    .booking-step { display: none !important; }
    .booking-step.active { display: block !important; }
</style>

<!-- Booking Section Start -->
<div class="container-fluid py-5 bg-light min-vh-100">
    <div class="container-xl">
        <div class="row">
            <!-- LEFT COLUMN: Main Booking Form (70%) -->
            <div class="col-lg-8">
                <!-- Page Title -->
                <div class="mb-4">
                    <h1 class="h2 fw-bold text-dark">Book Appointment</h1>
                    <p class="text-muted">Complete your details in simple steps</p>
                </div>

                <!-- Progress Indicator -->
                <div class="booking-progress d-flex justify-content-between mb-5">
                    <div class="progress-step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Your Info</div>
                    </div>
                    <div class="progress-step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Service</div>
                    </div>
                    <div class="progress-step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Date & Time</div>
                    </div>
                    <div class="progress-step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>

                <!-- Main Form Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">

                        <!-- Display Errors -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex">
                                    <i class="bi bi-exclamation-triangle-fill me-3 flex-shrink-0"></i>
                                    <div>
                                        <strong>Booking Error</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Display Success Message --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                <div class="d-flex">
                                    <i class="bi bi-check-circle-fill me-3 flex-shrink-0"></i>
                                    <div>
                                        <strong>Booking Confirmed!</strong>
                                        <p class="mb-0 mt-1">{{ session('success') }}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('booking.submit') }}" class="booking-form" id="bookingForm">
                            @csrf

                            <!-- STEP 1: Patient Information -->
                            <div class="booking-step active" data-step="1">
                                <h4 class="mb-4 text-dark">
                                    <span class="step-badge">1</span> Your Information
                                </h4>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="patient_name" 
                                               name="patient_name" 
                                               class="form-control @error('patient_name') is-invalid @enderror"
                                               value="{{ old('patient_name') }}"
                                               placeholder="John Doe"
                                               required>
                                        @error('patient_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" 
                                               id="patient_phone" 
                                               name="patient_phone" 
                                               class="form-control @error('patient_phone') is-invalid @enderror"
                                               value="{{ old('patient_phone') }}"
                                               placeholder="0167775940"
                                               required>
                                        @error('patient_phone')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">Email <span class="text-muted">(Optional)</span></label>
                                        <input type="email" 
                                               id="patient_email" 
                                               name="patient_email" 
                                               class="form-control @error('patient_email') is-invalid @enderror"
                                               value="{{ old('patient_email') }}"
                                               placeholder="your.email@example.com">
                                        <small class="text-muted d-block mt-1">We'll send confirmation to your email</small>
                                        @error('patient_email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex gap-md-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-primary" onclick="goToStep(2)">
                                        Next <i class="bi bi-chevron-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2: Service Selection -->
                            <div class="booking-step" data-step="2">
                                <h4 class="mb-4 text-dark">
                                    <span class="step-badge">2</span> Select Service
                                </h4>

                                <div class="row g-3">
                                    @forelse($services as $service)
                                        <div class="col-md-6">
                                            <input type="radio" 
                                                   name="service_id" 
                                                   id="service_{{ $service->id }}" 
                                                   value="{{ $service->id }}" 
                                                   class="form-check-input service-radio"
                                                   data-service-name="{{ $service->name }}"
                                                   data-service-duration="{{ $service->duration_minutes ?? $service->estimated_duration }}"
                                                   @checked(old('service_id') == $service->id)
                                                   required
                                                   onchange="updateSummary()">
                                            <label class="form-check-label service-card" 
                                                   for="service_{{ $service->id }}">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <strong>{{ $service->name }}</strong>
                                                    <span class="badge bg-primary fs-7">{{ $service->duration_minutes ?? $service->estimated_duration }} min</span>
                                                </div>
                                                <small class="text-muted">Professional dental care</small>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-danger">No services available</p>
                                    @endforelse
                                </div>

                                @error('service_id')
                                    <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>
                                @enderror

                                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary" onclick="goToStep(1)">
                                        <i class="bi bi-chevron-left me-2"></i> Back
                                    </button>
                                    <button type="button" class="btn btn-primary ms-auto" onclick="goToStep(3)">
                                        Next <i class="bi bi-chevron-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 2.5: Clinic Location (Optional - for future multi-clinic support) -->
                            <!-- Currently hidden as form is single-clinic (Seremban only) -->
                            <!-- Clinic location is automatically set to 'seremban' via hidden field -->
                            <input type="hidden" id="clinic_location" name="clinic_location" value="seremban">

                            <!-- STEP 3: Date & Time Selection -->
                            <div class="booking-step" data-step="3">
                                <h4 class="mb-4 text-dark">
                                    <span class="step-badge">3</span> Choose Date & Time
                                </h4>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Appointment Date <span class="text-danger">*</span></label>
                                        <input type="date" 
                                               class="form-control @error('appointment_date') is-invalid @enderror"
                                               id="appointment_date"
                                               name="appointment_date"
                                               value="{{ old('appointment_date') }}"
                                               min="{{ now()->format('Y-m-d') }}"
                                               max="{{ now()->addDays(30)->format('Y-m-d') }}"
                                               required
                                               onchange="handleDateChange(this.value)">
                                        <small class="text-muted d-block mt-1">Next 30 days available</small>
                                        @error('appointment_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Selected Time <span class="text-danger">*</span></label>
                                        <input type="text" 
                                               id="selected-time-display" 
                                               class="form-control" 
                                               placeholder="Select from slots below"
                                               readonly>
                                    </div>
                                </div>

                                <!-- Loading Indicator -->
                                <div id="slots-loading" class="text-center py-4" style="display: none;">
                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                    <span class="text-muted">Loading available slots...</span>
                                </div>

                                <!-- Messages Box -->
                                <div id="slots-message" style="display: none;">
                                    <div id="outside-hours-message" class="alert alert-warning d-none">
                                        <i class="bi bi-calendar-x me-2"></i>
                                        <strong>Outside Working Hours</strong> - The clinic is now closed for today.
                                        <br>
                                        <small class="d-block mt-2">
                                            💡 Would you like to book for <a href="javascript:void(0)" class="btn-change-date alert-link fw-bold">tomorrow instead?</a>
                                        </small>
                                    </div>
                                    <div id="clinic-closed-message" class="alert alert-warning d-none">
                                        <i class="bi bi-calendar-x me-2"></i>
                                        <strong>Clinic Closed</strong> - Please select another date
                                    </div>
                                    <div id="no-slots-message" class="alert alert-info d-none">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <strong>No Available Slots</strong> - All slots booked for this date
                                    </div>
                                </div>

                                <!-- Time Slots Grid -->
                                <div id="slots-grid-container">
                                    <label class="fw-semibold small mb-3 d-block">Available Time Slots</label>
                                    <div id="slots-grid" class="slots-grid"></div>
                                </div>

                                <!-- Lunch Break Info -->
                                <div class="alert alert-light border mt-4 mb-0">
                                    <small class="text-muted">
                                        <i class="bi bi-clock-history me-2"></i>
                                        <strong>Lunch break:</strong> 1:00 PM - 2:00 PM (Closed)
                                    </small>
                                </div>

                                <!-- Hidden Inputs -->
                                <input type="hidden" id="appointment_time" name="appointment_time" value="">

                                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary" onclick="goToStep(2)">
                                        <i class="bi bi-chevron-left me-2"></i> Back
                                    </button>
                                    <button type="button" class="btn btn-primary ms-auto" onclick="goToStep(4)">
                                        Next <i class="bi bi-chevron-right ms-2"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- STEP 4: Review & Confirm -->
                            <div class="booking-step" data-step="4">
                                <h4 class="mb-4 text-dark">
                                    <span class="step-badge">4</span> Review Booking
                                </h4>

                                <!-- Summary Table -->
                                <table class="table table-borderless mb-4">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted fw-semibold small">Name</td>
                                            <td class="text-end"><strong id="summary-name">—</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold small">Phone</td>
                                            <td class="text-end"><strong id="summary-phone">—</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold small">Email</td>
                                            <td class="text-end"><strong id="summary-email">—</strong></td>
                                        </tr>
                                        <tr class="border-bottom">
                                            <td class="text-muted fw-semibold small">Service</td>
                                            <td class="text-end"><strong id="summary-service">—</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold small">Duration</td>
                                            <td class="text-end"><strong id="summary-duration">—</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold small">Date</td>
                                            <td class="text-end"><strong id="summary-date">—</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted fw-semibold small">Time</td>
                                            <td class="text-end"><strong id="summary-time">—</strong></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>Please review your information before confirming. You'll receive a confirmation with your appointment details.</small>
                                </div>

                                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary" onclick="goToStep(3)">
                                        <i class="bi bi-chevron-left me-2"></i> Back
                                    </button>
                                    <button type="submit" class="btn btn-success ms-auto">
                                        <i class="bi bi-check-circle me-2"></i> Confirm Booking
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Sticky Information Panel (30%) -->
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <!-- Contact Info Card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 fw-bold">Contact Us</h5>

                            <div class="mb-4">
                                <small class="text-muted d-block mb-1">📍 Address</small>
                                <p class="small mb-0">No. 25A, Lorong Sri Mawar 12/2,<br>Taman Sri Mawar Fasa 2<br>70450 Seremban</p>
                            </div>

                            <div class="mb-4 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">☎️ Phone</small>
                                <p class="small mb-0"><strong class="text-dark">06-677 1940</strong></p>
                            </div>

                            <div class="mb-4 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">📧 Email</small>
                                <p class="small mb-0"><strong class="text-dark">klinikgigihelmy@gmail.com</strong></p>
                            </div>

                            <div>
                                <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="btn btn-sm btn-success w-100">
                                    <i class="bi bi-whatsapp me-2"></i> Chat WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 fw-bold">Opening Hours</h5>

                            @if($operatingHours && $operatingHours->isNotEmpty())
                                @foreach($operatingHours as $hour)
                                    <div class="small mb-3 pb-2 border-bottom">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">{{ $hour->day_of_week }}</span>
                                            @if($hour->is_closed)
                                                <strong class="text-muted">Closed</strong>
                                            @else
                                                <strong>{{ date('g:i A', strtotime($hour->start_time)) }} - {{ date('g:i A', strtotime($hour->end_time)) }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted">Operating hours not available</p>
                            @endif

                            <div class="alert alert-light border border-warning small mb-0">
                                <i class="bi bi-clock me-2"></i>
                                <strong>Lunch break:</strong> 1:00 PM - 2:00 PM
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Booking Section End -->


@endsection

@section('css')
<style>
    /* Container & Layout */
    .container-xl {
        max-width: 1100px;
    }

    .sticky-sidebar {
        position: sticky;
        top: 20px;
    }

    /* Page Title */
    .page-title {
        margin-bottom: 2rem;
    }

    /* Progress Indicator */
    .booking-progress {
        display: flex;
        justify-content: space-between;
        position: relative;
        padding: 15px 0;
        margin-bottom: 2rem;
    }

    .booking-progress::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 50px;
        right: 50px;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }

    .progress-step {
        position: relative;
        z-index: 1;
        text-align: center;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .step-number {
        width: 50px;
        height: 50px;
        margin: 0 auto 6px;
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: bold;
        color: #6c757d;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .progress-step.active .step-number {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
        box-shadow: 0 3px 8px rgba(13, 110, 253, 0.2);
    }

    .step-label {
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 500;
        white-space: nowrap;
    }

    .progress-step.active .step-label {
        color: #0d6efd;
        font-weight: 700;
        font-size: 0.85rem;
    }

    /* Booking Steps */
    .booking-step {
        display: none !important;
        animation: fadeIn 0.3s ease-in;
    }

    .booking-step.active {
        display: block !important;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Step Badge */
    .step-badge {
        width: 45px;
        height: 45px;
        background: #0d6efd;
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
        margin-right: 10px;
    }

    /* Service Cards */
    .service-radio {
        position: absolute;
        opacity: 0;
    }

    .service-card {
        display: block;
        padding: 1.25rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .service-card:hover {
        border-color: #0d6efd;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
    }

    .service-radio:checked + .service-card {
        background: #e7f5ff;
        border-color: #0d6efd;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }

    .service-card strong {
        display: block;
        margin-bottom: 4px;
        color: #212529;
    }

    /* Time Slots Grid */
    .slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(85px, 1fr));
        gap: 10px;
        margin-bottom: 0;
    }

    .slot-btn {
        padding: 12px 8px;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        background: white;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 600;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        color: #495057;
    }

    .slot-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .slot-btn.available {
        background: #d4edda;
        border-color: #28a745;
        color: #155724;
    }

    .slot-btn.available:hover {
        background: #28a745;
        color: white;
    }

    .slot-btn.selected {
        background: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }

    .slot-btn.booked {
        background: #f8d7da;
        border-color: #dc3545;
        color: #721c24;
        cursor: not-allowed;
        opacity: 0.5;
    }

    .slot-btn.unavailable {
        background: #e2e3e5;
        border-color: #d3d3d3;
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.5;
    }

    /* Summary Table */
    .table {
        font-size: 0.95rem;
        margin-bottom: 0;
    }

    .table td {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Form Controls */
    .form-control,
    .form-select {
        border-color: #e9ecef;
        font-size: 0.95rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .form-label {
        color: #212529;
        margin-bottom: 0.5rem;
    }

    /* Buttons */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0b5ed7;
    }

    .btn-success {
        background-color: #198754;
        border-color: #198754;
    }

    .btn-success:hover {
        background-color: #157347;
        border-color: #157347;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #dee2e6;
    }

    .btn-outline-secondary:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #495057;
    }

    /* Cards */
    .card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .card-body {
        color: #212529;
    }

    .card-title {
        color: #212529;
        font-size: 1rem;
    }

    /* Alerts */
    .alert {
        border-radius: 6px;
        border: 1px solid;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    .alert-light {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #212529;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .sticky-sidebar {
            position: static;
            margin-top: 2rem;
        }

        .booking-progress::before {
            left: 40px;
            right: 40px;
        }
    }

    @media (max-width: 768px) {
        .step-number {
            width: 50px;
            height: 50px;
            font-size: 1.1rem;
        }

        .step-label {
            font-size: 0.75rem;
        }

        .step-badge {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .booking-progress {
            padding: 0 5px;
        }

        .booking-progress::before {
            top: 25px;
            left: 30px;
            right: 30px;
        }

        .slots-grid {
            grid-template-columns: repeat(auto-fill, minmax(75px, 1fr));
            gap: 8px;
        }

        .slot-btn {
            padding: 10px 6px;
            font-size: 0.8rem;
            min-height: 44px;
        }

        .card-body {
            padding: 1.25rem !important;
        }

        h4 {
            font-size: 1.1rem;
        }

        .btn {
            font-size: 0.9rem;
        }

        .table {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 576px) {
        .page-title h1 {
            font-size: 1.5rem;
        }

        .page-title p {
            font-size: 0.9rem;
        }

        .step-number {
            width: 44px;
            height: 44px;
            font-size: 1rem;
        }

        .booking-progress {
            padding: 0;
        }

        .booking-progress::before {
            top: 22px;
            left: 22px;
            right: 22px;
        }

        .step-badge {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
            margin-right: 8px;
        }

        .slots-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .slot-btn {
            padding: 8px 4px;
            font-size: 0.75rem;
            min-height: 40px;
        }

        .service-card {
            padding: 1rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .d-flex.gap-2 {
            flex-direction: column !important;
        }

        .d-flex.gap-2 button {
            width: 100% !important;
            margin-left: 0 !important;
        }

        h4 {
            font-size: 1rem;
        }

        .btn {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
        }

        .table {
            font-size: 0.8rem;
        }
    }
</style>
@endsection

@push('scripts')
<script src="{{ asset('js/booking-slots.js') }}"></script>

<script>
    // Global function that will be called on date change
    function handleDateChange(dateString) {
        if (dateString && window.bookingSlots) {
            window.bookingSlots.resetSelection();
            window.bookingSlots.fetchSlots(dateString);
            updateSummary();
        }
    }

    // Step navigation
    function goToStep(stepNumber) {
        // ✅ VALIDATE FIRST before making any changes
        // This ensures user stays on current step if validation fails
        const currentStepNum = document.querySelector('.booking-step.active')?.getAttribute('data-step');
        
        if (stepNumber === 2 && !validateStep(1)) {
            console.log('Step 1 validation failed - staying on step 1');
            return; // Exit before changing DOM
        }
        if (stepNumber === 3 && !validateStep(2)) {
            console.log('Step 2 validation failed - staying on step 2');
            return; // Exit before changing DOM
        }
        if (stepNumber === 4 && !validateStep(3)) {
            console.log('Step 3 validation failed - staying on step 3');
            return; // Exit before changing DOM
        }

        // ✅ ONLY CHANGE STEPS IF VALIDATION PASSED
        // Hide all steps
        document.querySelectorAll('.booking-step').forEach(step => {
            step.classList.remove('active');
        });

        // Update progress indicator
        document.querySelectorAll('.progress-step').forEach(step => {
            step.classList.remove('active');
        });

        // Show selected step - be specific with .booking-step selector
        const stepElement = document.querySelector(`.booking-step[data-step="${stepNumber}"]`);
        const progressElement = document.querySelector(`.progress-step[data-step="${stepNumber}"]`);
        
        if (stepElement) stepElement.classList.add('active');
        if (progressElement) progressElement.classList.add('active');

        // Scroll page title into view so user sees: Title + Progress Indicator + Form
        const pageTitle = document.querySelector('.col-lg-8 > .mb-4');
        if (pageTitle && stepNumber > 1) {
            // Only scroll if not on first step
            pageTitle.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        console.log(`Successfully moved to step ${stepNumber}`);
    }

    function validateStep(stepNumber) {
        if (stepNumber === 1) {
            const name = document.getElementById('patient_name').value.trim();
            const phone = document.getElementById('patient_phone').value.trim();
            
            // ✅ Check name
            if (!name) {
                alert('❌ Please enter your full name');
                document.getElementById('patient_name').focus();
                return false;
            }
            
            // ✅ Check phone
            if (!phone) {
                alert('❌ Please enter your phone number');
                document.getElementById('patient_phone').focus();
                return false;
            }
            
            // ✅ Validate phone format (Malaysian number)
            const phoneRegex = /^(\+?6?01)[0-9]{8,9}$/;
            if (!phoneRegex.test(phone)) {
                alert('❌ Please enter a valid Malaysian phone number (e.g., 0167775940 or +60167775940)');
                document.getElementById('patient_phone').focus();
                return false;
            }
            
            console.log('✅ Step 1 validation passed: Name and phone valid');
            return true;
        }
        
        if (stepNumber === 2) {
            const service = document.querySelector('input[name="service_id"]:checked');
            if (!service) {
                alert('❌ Please select a service');
                return false;
            }
            console.log('✅ Step 2 validation passed: Service selected');
            return true;
        }
        
        if (stepNumber === 3) {
            const date = document.getElementById('appointment_date').value;
            const time = document.getElementById('appointment_time').value;
            
            if (!date) {
                alert('❌ Please select a date');
                return false;
            }
            if (!time) {
                alert('❌ Please select a time');
                return false;
            }
            
            console.log('✅ Step 3 validation passed: Date and time selected');
            return true;
        }
        
        return true;
    }

    // Update summary dynamically
    function updateSummary() {
        // Name
        const name = document.getElementById('patient_name').value || 'Not provided';
        document.getElementById('summary-name').textContent = name;

        // Phone
        const phone = document.getElementById('patient_phone').value || 'Not provided';
        document.getElementById('summary-phone').textContent = phone;

        // Email
        const email = document.getElementById('patient_email').value || 'Not provided';
        document.getElementById('summary-email').textContent = email;

        // Service
        const serviceRadio = document.querySelector('input[name="service_id"]:checked');
        if (serviceRadio) {
            const serviceName = serviceRadio.dataset.serviceName || 'Not selected';
            const duration = serviceRadio.dataset.serviceDuration || '';
            document.getElementById('summary-service').textContent = serviceName;
            document.getElementById('summary-duration').textContent = `${duration} minutes`;
        }

        // Date
        const dateInput = document.getElementById('appointment_date').value;
        if (dateInput) {
            const dateObj = new Date(dateInput + 'T00:00:00');
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('summary-date').textContent = dateObj.toLocaleDateString('en-US', options);
        } else {
            document.getElementById('summary-date').textContent = 'Not selected';
        }

        // Time
        const timeDisplay = document.getElementById('selected-time-display').value || 'Not selected';
        document.getElementById('summary-time').textContent = timeDisplay;
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing booking form');
        
        // Initialize BookingSlotManager
        window.bookingSlots = new BookingSlotManager();

        // Attach event listeners for summary updates
        document.getElementById('patient_name').addEventListener('change', updateSummary);
        document.getElementById('patient_phone').addEventListener('change', updateSummary);
        document.getElementById('patient_email').addEventListener('change', updateSummary);

        document.querySelectorAll('input[name="service_id"]').forEach(radio => {
            radio.addEventListener('change', updateSummary);
        });

        document.getElementById('appointment_date').addEventListener('change', updateSummary);

        // Set up form validation on submit
        const form = document.querySelector('.booking-form');
        form.addEventListener('submit', function(e) {
            if (!validateStep(3)) {
                e.preventDefault();
                alert('Please complete all required fields');
                goToStep(3);
            }
        });

        // Set step 1 as active initially with small delay to ensure DOM is ready
        setTimeout(function() {
            // Check if there are validation errors from a previous submission
            const hasErrors = @json($errors->any());
            const hasAppointmentTimeError = @json($errors->has('appointment_time'));
            
            if (hasErrors && hasAppointmentTimeError) {
                // If there's an appointment_time error, go to step 3
                goToStep(3);
                console.log('Validation error detected - going to Step 3');
                
                // If date field already has a value, fetch slots for it
                const dateInput = document.getElementById('appointment_date');
                if (dateInput && dateInput.value) {
                    console.log('Loading slots for date:', dateInput.value);
                    handleDateChange(dateInput.value);
                }
                
                // Scroll to the error message
                setTimeout(function() {
                    const errorAlert = document.querySelector('.alert-danger');
                    if (errorAlert) {
                        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 200);
            } else {
                // Otherwise start at step 1
                goToStep(1);
                console.log('Initialized to Step 1');
            }
        }, 100);
    });

    // Override BookingSlotManager's selectSlot to also update time display
    const originalSelectSlot = BookingSlotManager.prototype.selectSlot;
    BookingSlotManager.prototype.selectSlot = function(buttonEl, time) {
        originalSelectSlot.call(this, buttonEl, time);
        document.getElementById('selected-time-display').value = buttonEl.textContent;
        updateSummary();
    };
</script>
@endpush
