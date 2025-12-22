@extends('layouts.public')

@section('title', 'Thank You - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Thank You!</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="#" class="h4 text-white">Feedback</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Thank You Message Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="bg-light rounded-lg p-5 text-center wow fadeInUp" data-wow-delay="0.1s" style="box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                    <div class="mb-4 animated bounceIn">
                        <i class="bi bi-check-circle text-success" style="font-size: 100px;"></i>
                    </div>
                    
                    <h1 class="display-5 mb-3 text-success fw-bold">Feedback Received!</h1>
                    
                    <h4 class="mb-4 text-body fw-normal">
                        Thank you for taking the time to share your experience
                    </h4>
                    
                    <p class="fs-5 text-muted mb-4 lh-lg">
                        Your feedback helps us improve our services and provide better care for all our patients.
                        @if($feedback && $feedback->rating >= 4)
                            <br><strong>We're delighted that you had a great experience! ⭐</strong>
                        @endif
                    </p>

                    @if($feedback)
                    <div class="alert alert-info border-0 rounded-lg mb-4" style="background-color: #e7f3ff;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-star-fill text-warning fs-4 me-3 mt-1"></i>
                            <div class="text-start">
                                <h6 class="alert-heading text-info mb-2">Your Rating</h6>
                                <div class="mb-0">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $feedback->rating)
                                            <i class="bi bi-star-fill text-warning"></i>
                                        @else
                                            <i class="bi bi-star text-muted"></i>
                                        @endif
                                    @endfor
                                    <span class="ms-2 fw-bold">{{ $feedback->rating }}/5</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="alert alert-success border-0 rounded-lg mb-4" style="background-color: #dcfce7;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-heart-fill text-success fs-5 me-3 mt-1"></i>
                            <div class="text-start">
                                <h6 class="alert-heading text-success mb-2">What Happens Next?</h6>
                                <ul class="mb-0 small text-muted">
                                    <li>Our team will review your feedback carefully</li>
                                    <li>We use your input to improve our services</li>
                                    <li>If needed, we may reach out to you for follow-up</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary btn-lg me-2">
                            <i class="bi bi-house me-2"></i>Back to Home
                        </a>
                        <a href="{{ url('/book') }}" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-calendar-plus me-2"></i>Book Another Appointment
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <h5 class="mb-3">Stay Connected</h5>
                        <p class="text-muted mb-3">Follow us on social media for dental tips and updates</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="btn btn-outline-primary btn-sm"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-outline-info btn-sm"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="btn btn-outline-danger btn-sm"><i class="fab fa-instagram"></i></a>
                            <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="btn btn-outline-success btn-sm"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Thank You Message End -->
@endsection
