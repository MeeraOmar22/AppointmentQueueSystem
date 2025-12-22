@extends('layouts.public')

@section('title', 'Share Your Feedback - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Share Your Feedback</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="#" class="h4 text-white">Feedback</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Feedback Form Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="bg-light rounded-lg p-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="text-center mb-4">
                        <i class="bi bi-chat-heart text-primary" style="font-size: 80px;"></i>
                        <h2 class="mt-3 mb-2">We Value Your Opinion!</h2>
                        <p class="text-muted">Help us improve our service by sharing your experience</p>
                    </div>

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

                    <!-- Appointment Info -->
                    <div class="alert alert-info border-0 rounded-lg mb-4" style="background-color: #e7f3ff;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle text-info fs-5 me-3 mt-1"></i>
                            <div class="text-start">
                                <h6 class="alert-heading text-info mb-2">Your Appointment</h6>
                                <p class="mb-1"><strong>Visit Code:</strong> {{ $appointment->visit_code }}</p>
                                <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</p>
                                <p class="mb-0"><strong>Service:</strong> {{ $appointment->service->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('feedback.store') }}">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                        <input type="hidden" name="patient_name" value="{{ $appointment->patient_name }}">
                        <input type="hidden" name="patient_phone" value="{{ $appointment->patient_phone }}">

                        <!-- Overall Rating -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Overall Experience <span class="text-danger">*</span></label>
                            <div class="d-flex justify-content-center gap-2 mb-2">
                                <div class="star-rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                                        <label for="star{{ $i }}" class="star">
                                            <i class="bi bi-star-fill"></i>
                                        </label>
                                    @endfor
                                </div>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">Click the stars to rate (1 = Poor, 5 = Excellent)</small>
                            </div>
                            @error('rating')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Detailed Ratings -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Service Quality</label>
                                <select class="form-select" name="service_quality">
                                    <option value="">Select</option>
                                    <option value="excellent" {{ old('service_quality') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('service_quality') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('service_quality') == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('service_quality') == 'poor' ? 'selected' : '' }}>Poor</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Staff Friendliness</label>
                                <select class="form-select" name="staff_friendliness">
                                    <option value="">Select</option>
                                    <option value="excellent" {{ old('staff_friendliness') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('staff_friendliness') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('staff_friendliness') == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('staff_friendliness') == 'poor' ? 'selected' : '' }}>Poor</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Cleanliness</label>
                                <select class="form-select" name="cleanliness">
                                    <option value="">Select</option>
                                    <option value="excellent" {{ old('cleanliness') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('cleanliness') == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('cleanliness') == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('cleanliness') == 'poor' ? 'selected' : '' }}>Poor</option>
                                </select>
                            </div>
                        </div>

                        <!-- Comments -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Your Comments</label>
                            <textarea class="form-control" name="comments" rows="5" placeholder="Tell us more about your experience...">{{ old('comments') }}</textarea>
                            <small class="text-muted">Optional: Share any additional thoughts or suggestions</small>
                        </div>

                        <!-- Recommendation -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Would you recommend us to others? <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="would_recommend" id="recommend_yes" value="1" {{ old('would_recommend', '1') == '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="recommend_yes">
                                    <i class="bi bi-emoji-smile text-success"></i> Yes, definitely!
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="would_recommend" id="recommend_no" value="0" {{ old('would_recommend') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="recommend_no">
                                    <i class="bi bi-emoji-frown text-warning"></i> Not sure
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-send me-2"></i>Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Feedback Form End -->

<style>
    /* Star Rating CSS */
    .star-rating {
        display: inline-flex;
        flex-direction: row-reverse;
        font-size: 3rem;
        justify-content: center;
        padding: 0;
        text-align: center;
        width: auto;
    }

    .star-rating input {
        display: none;
    }

    .star-rating label {
        color: #ddd;
        cursor: pointer;
        padding: 0 5px;
        transition: color 0.2s;
    }

    .star-rating input:checked ~ label,
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #ffc107;
    }

    .star-rating input:checked ~ label {
        color: #ffc107;
    }
</style>

<script>
    // Star rating interaction
    const stars = document.querySelectorAll('.star-rating label');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.previousElementSibling.value;
            console.log('Rating selected:', rating);
        });
    });
</script>
@endsection
