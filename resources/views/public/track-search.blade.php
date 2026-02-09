@extends('layouts.public')

@section('title', 'Track Your Appointment - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Track Your Appointment</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="#" class="h4 text-white">Track</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Track Search Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="bg-light rounded-lg p-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="text-center mb-5">
                        <i class="bi bi-search text-primary" style="font-size: 80px;"></i>
                        <h2 class="mt-3 mb-2">Find Your Appointment</h2>
                        <p class="text-muted">Enter your visit code to check your appointment status</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="bi bi-exclamation-circle me-2"></i>Not Found</strong>
                            <p class="mb-0">We couldn't find an appointment with that visit code. Please check and try again.</p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="GET" action="{{ url('/track') }}" onsubmit="return handleSearch(event)">
                        <!-- Visit Code Input -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Visit Code <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary border-primary">
                                    <i class="bi bi-ticket text-white"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg border-primary" 
                                    id="visitCode"
                                    name="code" 
                                    placeholder="Enter your visit code (e.g., DNT-20260119-001)" 
                                    required
                                    autocomplete="off"
                                    style="font-size: 1.1rem; letter-spacing: 0.1em;"
                                >
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Your visit code was provided when you booked your appointment
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="searchBtn">
                                <i class="bi bi-search me-2"></i>Track My Appointment
                            </button>
                        </div>

                        <!-- Alternative: Quick Guide -->
                        <div class="alert alert-info border-0 rounded-lg mb-0" style="background-color: #e7f3ff;">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-lightbulb text-info fs-5 me-3 mt-1"></i>
                                <div class="text-start">
                                    <h6 class="alert-heading text-info mb-2">Your Visit Code Looks Like: <code>DNT-20260119-001</code></h6>
                                    <p class="mb-2 small text-dark"><strong>📱 Check These Places:</strong></p>
                                    <ul class="mb-0 small">
                                        <li><strong>WhatsApp</strong> - The link we sent you has your code in the URL</li>
                                        <li><strong>Email</strong> - Your booking confirmation message</li>
                                        <li><strong>SMS</strong> - Your appointment confirmation text message</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Divider -->
                    <hr class="my-5">

                    <!-- Quick Actions -->
                    <div class="text-center">
                        <p class="text-muted mb-3">Don't have a visit code?</p>
                        <a href="{{ url('/book') }}" class="btn btn-outline-primary btn-lg w-100">
                            <i class="bi bi-calendar-plus me-2"></i>Book New Appointment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Track Search End -->

<script>
    function handleSearch(event) {
        const visitCode = document.getElementById('visitCode').value.trim().toUpperCase();
        
        // Validate visit code format (should be like DNT-20260119-001 or V...)
        if (!visitCode) {
            alert('Please enter your visit code');
            return false;
        }

        // Accept both DNT-YYYYMMDD-### format and V... format
        if (!/^(DNT-\d{8}-\d{3}|V.+)$/i.test(visitCode)) {
            alert('Please enter a valid visit code (e.g., DNT-20260119-001)');
            document.getElementById('visitCode').focus();
            return false;
        }

        // Redirect to track page with code
        window.location.href = `/track/${visitCode}`;
        return false;
    }

    // Auto-focus on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('visitCode').focus();
        
        // Allow Enter key to submit
        document.getElementById('visitCode').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSearch(e);
            }
        });
    });
</script>

<style>
    .input-group-text {
        border-width: 2px;
    }

    .form-control:focus {
        border-color: #06A3DA !important;
        box-shadow: 0 0 0 0.2rem rgba(6, 163, 218, 0.25) !important;
    }

    .form-control.border-primary {
        border-width: 2px;
    }

    .btn-outline-primary:hover {
        background-color: #06A3DA;
        border-color: #06A3DA;
    }
</style>
@endsection
