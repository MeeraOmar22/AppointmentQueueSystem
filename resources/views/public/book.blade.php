@extends('layouts.public')

@section('title', 'Book Appointment - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Book Appointment</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/book') }}" class="h4 text-white">Booking</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Booking Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.1s">
                <div class="bg-light rounded h-100 p-5">
                    <div class="section-title">
                        <h5 class="position-relative d-inline-block text-primary text-uppercase">Quick Info</h5>
                        <h1 class="display-6 mb-4">Why Choose Us?</h1>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle text-primary fs-4 me-3"></i>
                        <span>Professional Dentists</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle text-primary fs-4 me-3"></i>
                        <span>Modern Equipment</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle text-primary fs-4 me-3"></i>
                        <span>Affordable Prices</span>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle text-primary fs-4 me-3"></i>
                        <span>Comfortable Environment</span>
                    </div>
                    <hr class="my-4">
                    <h5 class="mb-3 text-primary">Need Help?</h5>
                    <p class="mb-2"><i class="bi bi-telephone text-primary me-2"></i><strong>06-677 1940</strong></p>
                    <p class="mb-2"><i class="bi bi-envelope text-primary me-2"></i><strong>klinikgigihelmy@gmail.com</strong></p>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="col-lg-8 wow slideInUp" data-wow-delay="0.3s">
                <div class="bg-light rounded p-5">
                    <h3 class="mb-4"><i class="bi bi-calendar-plus me-2 text-primary"></i>Book Your Appointment</h3>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ url('/book') }}" novalidate>
                        @csrf

                        <!-- STEP 1: Service Selection -->
                        <div class="mb-5 pb-4 border-bottom">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary me-2">Step 1</span>What Do You Need Today?
                            </h5>
                            <p class="text-muted small mb-3">Select the service you need. This helps us estimate duration and assign the right dentist.</p>
                            
                            <div class="row g-3">
                                @forelse($services as $service)
                                    <div class="col-md-6">
                                        <div class="form-check custom-service-check">
                                            <input class="form-check-input" type="radio" name="service_id" id="service_{{ $service->id }}" value="{{ $service->id }}" @checked(old('service_id') == $service->id) required>
                                            <label class="form-check-label w-100 p-3 border rounded cursor-pointer" for="service_{{ $service->id }}">
                                                <strong>{{ $service->name }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>{{ $service->estimated_duration }} min
                                                    <i class="bi bi-cash me-1 ms-2"></i>RM {{ number_format($service->price, 2) }}
                                                </small>
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-danger">No services available</p>
                                @endforelse
                            </div>
                            @error('service_id')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- STEP 2: Date & Time Selection -->
                        <div class="mb-5 pb-4 border-bottom">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary me-2">Step 2</span>Choose Date & Time
                            </h5>
                            <p class="text-muted small mb-3">Select your preferred date and time range. We'll accommodate you within the chosen window.</p>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="appointment_date" class="form-label fw-semibold">Appointment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                           id="appointment_date" name="appointment_date" value="{{ old('appointment_date') }}" required>
                                    @error('appointment_date')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="appointment_time" class="form-label fw-semibold">Preferred Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('appointment_time') is-invalid @enderror" 
                                           id="appointment_time" name="appointment_time" value="{{ old('appointment_time') }}" required>
                                    @error('appointment_time')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-info-circle me-1"></i>Your appointment time may vary based on clinic schedule.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: Clinic & Dentist Preference -->
                        <div class="mb-5 pb-4 border-bottom">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary me-2">Step 3</span>Clinic & Dentist Preference
                            </h5>

                            <div class="mb-4">
                                <label for="clinic_location" class="form-label fw-semibold">Select Clinic Location <span class="text-danger">*</span></label>
                                <select class="form-select @error('clinic_location') is-invalid @enderror" id="clinic_location" name="clinic_location" required>
                                    <option value="">-- Choose a location --</option>
                                    <option value="seremban" @selected(old('clinic_location') == 'seremban')>
                                        Seremban - No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, 70450 Seremban
                                    </option>
                                    <option value="kuala_pilah" @selected(old('clinic_location') == 'kuala_pilah')>
                                        Kuala Pilah - No. 902, Jalan Raja Melewar, 72000 Kuala Pilah
                                    </option>
                                </select>
                                @error('clinic_location')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="bi bi-lightbulb me-1 text-warning"></i>
                                <strong>Tip:</strong> Choosing "any available dentist" typically reduces your waiting time.
                            </p>

                            <label class="fw-semibold d-block mb-3">Dentist Preference <span class="text-danger">*</span></label>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="dentist_preference" id="dentist_any" value="any" @checked(old('dentist_preference') != 'specific') required>
                                <label class="form-check-label" for="dentist_any">
                                    <strong>Any Available Dentist</strong> (Recommended)
                                    <br>
                                    <small class="text-muted">We'll assign the next available qualified dentist. Reduces waiting time.</small>
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="dentist_preference" id="dentist_specific" value="specific" @checked(old('dentist_preference') == 'specific') required>
                                <label class="form-check-label" for="dentist_specific">
                                    <strong>I Have a Preferred Dentist</strong>
                                    <br>
                                    <small class="text-muted">You may wait longer, but we'll prioritize your chosen dentist.</small>
                                </label>
                            </div>

                            <div id="dentist_select_wrapper" class="mt-3" style="display: {{ old('dentist_preference') == 'specific' ? 'block' : 'none' }};">
                                <label for="dentist_id" class="form-label fw-semibold">Choose Your Dentist</label>
                                <select class="form-select @error('dentist_id') is-invalid @enderror" id="dentist_id" name="dentist_id">
                                    <option value="">-- Choose a dentist --</option>
                                    @forelse($dentists as $dentist)
                                        @if($dentist->status)
                                            <option value="{{ $dentist->id }}" @selected(old('dentist_id') == $dentist->id)>
                                                {{ $dentist->name }} 
                                                @if($dentist->specialization)
                                                    ({{ $dentist->specialization }})
                                                @endif
                                            </option>
                                        @endif
                                    @empty
                                        <option disabled>No dentists available</option>
                                    @endforelse
                                </select>
                                @error('dentist_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- STEP 4: Contact Information -->
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">
                                <span class="badge bg-primary me-2">Step 4</span>Your Contact Information
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="patient_name" class="form-label fw-semibold">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('patient_name') is-invalid @enderror" 
                                           id="patient_name" name="patient_name" placeholder="Enter your full name" 
                                           value="{{ old('patient_name') }}" required>
                                    @error('patient_name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="patient_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('patient_phone') is-invalid @enderror" 
                                           id="patient_phone" name="patient_phone" placeholder="e.g., 0167775940" 
                                           value="{{ old('patient_phone') }}" required>
                                    @error('patient_phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">We'll use this for appointment reminders & updates.</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="patient_email" class="form-label fw-semibold">Email Address <span class="text-muted">(Optional)</span></label>
                                    <input type="email" class="form-control @error('patient_email') is-invalid @enderror" 
                                           id="patient_email" name="patient_email" placeholder="your.email@example.com" 
                                           value="{{ old('patient_email') }}">
                                    @error('patient_email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">For booking confirmation & tracking link (optional).</small>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notice About Queue Logic -->
                        <div class="alert alert-info mb-4" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>How We Prioritize Treatment:</strong>
                            <br>
                            <small>
                                Treatment order is determined by <strong>arrival time</strong> and <strong>dentist availability</strong>, not booking time. 
                                This ensures fairness for all patients. Your appointment time is a target — actual treatment begins when it's your turn in the queue.
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-3 fw-bold">
                                <i class="bi bi-calendar-check me-2"></i>Complete Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Operating Hours Info -->
        <div class="row g-5 mt-5">
            <div class="col-12">
                <div class="bg-primary rounded p-5 text-white wow fadeInUp" data-wow-delay="0.5s">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <h4 class="mb-3"><i class="bi bi-clock-history me-2"></i>Operating Hours</h4>
                            @if($operatingHours->isNotEmpty())
                                <div class="row">
                                    @foreach($operatingHours as $day)
                                    <div class="col-6 mb-2">
                                        <strong>{{ $day->day_of_week }}:</strong> {{ date('g:i a', strtotime($day->start_time)) }} - {{ date('g:i a', strtotime($day->end_time)) }}
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <p>Please check back for operating hours information.</p>
                            @endif
                        </div>
                        <div class="col-lg-6">
                            <h4 class="mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Important Notes</h4>
                            <ul class="mb-0">
                                <li>Please arrive 10 minutes before your appointment</li>
                                <li>Bring your ID or insurance card if applicable</li>
                                <li>Call us if you need to cancel or reschedule</li>
                                <li>Children should be accompanied by a parent/guardian</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Booking End -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dentist preference toggle
    const dentistAnyRadio = document.getElementById('dentist_any');
    const dentistSpecificRadio = document.getElementById('dentist_specific');
    const dentistSelectWrapper = document.getElementById('dentist_select_wrapper');
    const dentistSelect = document.getElementById('dentist_id');
    
    function updateDentistSelectVisibility() {
        if (!dentistSelectWrapper || !dentistSelect) return;
        
        if (dentistSpecificRadio && dentistSpecificRadio.checked) {
            dentistSelectWrapper.style.display = 'block';
            dentistSelect.setAttribute('required', 'required');
        } else {
            dentistSelectWrapper.style.display = 'none';
            dentistSelect.removeAttribute('required');
            dentistSelect.value = '';
        }
    }
    
    if (dentistAnyRadio) {
        dentistAnyRadio.addEventListener('change', updateDentistSelectVisibility);
    }
    if (dentistSpecificRadio) {
        dentistSpecificRadio.addEventListener('change', updateDentistSelectVisibility);
    }
    
    // Initial visibility check
    updateDentistSelectVisibility();
});
</script>

<style>
    /* Custom service selection styling */
    .custom-service-check .form-check-input {
        cursor: pointer;
    }

    .custom-service-check .form-check-input:checked ~ label {
        background-color: #e7f1ff !important;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .custom-service-check label {
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 0;
    }

    .custom-service-check label:hover {
        background-color: #f8f9fa;
    }

    /* Better form group styling */
    .form-label {
        margin-bottom: 0.75rem;
    }

    /* Step badges styling */
    .badge {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }

    /* Dentist preference styling */
    .form-check {
        padding: 1rem;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .form-check:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .form-check .form-check-input:checked ~ label {
        font-weight: 600;
    }

    /* Smooth show/hide animation */
    #dentist_select_wrapper {
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Alert styling for messaging */
    .alert-info {
        background-color: #e7f1ff;
        border-color: #0d6efd;
        color: #084298;
    }

    .alert-info small {
        color: #0c5460;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .badge {
            display: block;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection
