@extends('layouts.public')

@section('title', 'Booking Successful - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Booking Confirmation</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/book') }}" class="h4 text-white">Success</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Success Message Start -->
<div class="container-fluid py-5">
    <div class="container">
        <!-- Main Success Message -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8">
                <div class="bg-light rounded-lg p-5 text-center wow fadeInUp" data-wow-delay="0.1s" style="box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div class="mb-4 animated bounceIn">
                        <i class="bi bi-check-circle text-success" style="font-size: 100px;"></i>
                    </div>
                    
                    <h1 class="display-5 mb-3 text-success fw-bold">Appointment Confirmed!</h1>
                    
                    <h4 class="mb-4 text-body fw-normal">
                        Thank you, <span class="fw-bold text-primary">{{ $name }}</span>
                    </h4>
                    
                    <p class="fs-5 text-muted mb-4 lh-lg">
                        Your appointment has been successfully booked.
                        <br><strong>Visit Code:</strong> <span class="text-primary fw-bold">{{ $appointment->visit_code }}</span>
                        <br><small class="text-muted">Please save this code for future reference.</small>
                    </p>

                    <!-- Email Sent Notice (Only if email provided) -->
                    @if(!empty($appointment->patient_email))
                    <div class="alert alert-success border-0 rounded-lg mb-4" style="background-color: #dcfce7;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-envelope-check text-success fs-5 me-3 mt-1"></i>
                            <div class="text-start">
                                <h6 class="alert-heading text-success mb-2">✓ Confirmation Email Sent</h6>
                                <p class="text-muted small mb-2">
                                    A confirmation email with your visit code and tracking link has been sent to <strong>{{ $appointment->patient_email }}</strong>.
                                </p>
                                <p class="text-muted small mb-0">
                                    Click the link in the email to track your appointment and check-in when you arrive at the clinic.
                                </p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info border-0 rounded-lg mb-4" style="background-color: #e7f3ff;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-info fs-5 me-3 mt-1"></i>
                            <div class="text-start">
                                <h6 class="alert-heading text-info mb-2">ℹ️ Save Your Visit Code</h6>
                                <p class="text-muted small mb-0">
                                    You didn't provide an email, so no confirmation email was sent. Please save your visit code <strong>{{ $appointment->visit_code }}</strong> to track your appointment later.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mb-4">
                        <a href="{{ url('/track/' . $appointment->visit_code) }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-link-45deg me-2"></i>View Tracking Page
                        </a>
                    </div>

                    <!-- Visit Code & Token Storage Notice -->
                    <div class="alert alert-info border-0 rounded-lg" style="background-color: #e7f3ff;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-shield-check text-info fs-5 me-3 mt-1"></i>
                            <div class="text-start">
                                <h6 class="alert-heading text-info mb-2">✓ Your Visit Code Saved</h6>
                                <p class="text-muted small mb-0">
                                    Your visit code is saved to your browser. You can access your booking anytime without login using code: <code style="background: rgba(0,0,0,0.05); padding: 4px 8px; border-radius: 4px;">{{ $appointment->visit_code }}</code>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 rounded-lg" role="alert" style="background-color: #e7f3ff;">
                        <div class="row g-0">
                            <div class="col-auto">
                                <i class="bi bi-info-circle text-info fs-4 me-3"></i>
                            </div>
                            <div class="col text-start">
                                <h5 class="alert-heading mb-2">What Happens Next?</h5>
                                <ul class="mb-0 small">
                                    <li>Our staff will call you within 24 hours to confirm your appointment</li>
                                    <li>A confirmation email will be sent to your registered email address</li>
                                    <li>Please save our phone number for easy reference: <strong>06-677 1940</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preparation Guide -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.2s">
                <div class="bg-light rounded-lg p-4 h-100" style="border-left: 5px solid #2563eb;">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-history text-primary fs-4 me-3 mt-1"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">Arrive Early</h5>
                            <p class="text-muted mb-0">
                                Please arrive <strong>10-15 minutes</strong> before your scheduled appointment time to complete any necessary formalities.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.4s">
                <div class="bg-light rounded-lg p-4 h-100" style="border-left: 5px solid #7c3aed;">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="bi bi-credit-card text-primary fs-4 me-3 mt-1"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">Bring Documents</h5>
                            <p class="text-muted mb-0">
                                Please bring your <strong>ID/Passport</strong> and any <strong>insurance card</strong> if applicable for verification purposes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.6s">
                <div class="bg-light rounded-lg p-4 h-100" style="border-left: 5px solid #f59e0b;">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="bi bi-chat-left-text text-primary fs-4 me-3 mt-1"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-2">Mention Issues</h5>
                            <p class="text-muted mb-0">
                                Come prepared to discuss any <strong>dental concerns</strong> or <strong>health conditions</strong> with our dentist.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancellation & Rescheduling Policy -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6 wow slideInLeft" data-wow-delay="0.3s">
                <div class="bg-danger bg-opacity-10 rounded-lg p-4" style="border-left: 5px solid #dc2626;">
                    <h5 class="fw-bold text-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>Cancellation Policy
                    </h5>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Cancel or reschedule <strong>at least 24 hours</strong> before your appointment</li>
                        <li class="mb-2">Call us at <strong>06-677 1940</strong> to make changes</li>
                        <li>Late cancellations may incur a <strong>cancellation fee</strong></li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6 wow slideInRight" data-wow-delay="0.3s">
                <div class="bg-success bg-opacity-10 rounded-lg p-4" style="border-left: 5px solid #16a34a;">
                    <h5 class="fw-bold text-success mb-3">
                        <i class="bi bi-star-fill me-2"></i>Why Book With Us?
                    </h5>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2"><strong>Experienced Team</strong> - Certified dental professionals</li>
                        <li class="mb-2"><strong>Modern Equipment</strong> - Latest dental technology</li>
                        <li><strong>Affordable Rates</strong> - Quality care at reasonable prices</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Contact Support -->
        <div class="row g-4 mb-5">
            <div class="col-lg-12">
                <h4 class="text-center mb-4 fw-bold">Get In Touch With Us</h4>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="bg-light rounded-lg p-4 text-center wow slideInUp" data-wow-delay="0.2s" style="transition: all 0.3s ease;">
                    <div class="mb-3">
                        <i class="bi bi-telephone text-primary" style="font-size: 40px;"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Call Us</h6>
                    <p class="text-muted small mb-3">Available during working hours</p>
                    <a href="tel:06-677 1940" class="btn btn-sm btn-primary">06-677 1940</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="bg-light rounded-lg p-4 text-center wow slideInUp" data-wow-delay="0.4s">
                    <div class="mb-3">
                        <i class="bi bi-envelope text-primary" style="font-size: 40px;"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Email Us</h6>
                    <p class="text-muted small mb-3">We reply within 24 hours</p>
                    <a href="mailto:klinikgigihelmy@gmail.com" class="btn btn-sm btn-primary">Send Email</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="bg-light rounded-lg p-4 text-center wow slideInUp" data-wow-delay="0.6s">
                    <div class="mb-3">
                        <i class="bi bi-geo-alt text-primary" style="font-size: 40px;"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Visit Us</h6>
                    <p class="text-muted small mb-3">123 Street, New York, USA</p>
                    <a href="{{ url('/contact') }}" class="btn btn-sm btn-primary">Get Directions</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="bg-light rounded-lg p-4 text-center wow slideInUp" data-wow-delay="0.8s">
                    <div class="mb-3">
                        <i class="bi bi-question-circle text-primary" style="font-size: 40px;"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Need Help?</h6>
                    <p class="text-muted small mb-3">Chat with our support team</p>
                    <a href="{{ url('/contact') }}" class="btn btn-sm btn-primary">Contact Us</a>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center wow fadeInUp" data-wow-delay="0.5s">
                    <h5 class="mb-4 text-muted">What would you like to do next?</h5>
                    <div class="d-flex flex-wrap gap-3 justify-content-center">
                        <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                        <a href="{{ url('/services') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-hospital me-2"></i>View All Services
                        </a>
                        <a href="{{ url('/dentists') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-person-check me-2"></i>Meet Our Team
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Success Message End -->

<!-- Store Visit Token in Browser -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Store the visit token in localStorage for stateless tracking
        const visitToken = '{{ $appointment->visit_token }}';
        const visitCode = '{{ $appointment->visit_code }}';
        const appointmentData = {
            token: visitToken,
            code: visitCode,
            patientName: '{{ $name }}',
            patientPhone: '{{ $appointment->patient_phone }}',
            appointmentDate: '{{ $appointment->appointment_date }}',
            appointmentTime: '{{ $appointment->appointment_time }}',
            savedAt: new Date().toISOString()
        };
        
        // Save to localStorage
        localStorage.setItem('visit_token', visitToken);
        localStorage.setItem('visit_code', visitCode);
        localStorage.setItem('appointment_data_' + visitToken, JSON.stringify(appointmentData));
        localStorage.setItem('appointment_data_' + visitCode, JSON.stringify(appointmentData));
        
        console.log('✓ Visit token saved to browser storage. Stateless tracking enabled.');
    });
</script>
@endsection
