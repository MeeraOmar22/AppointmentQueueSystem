@extends('layouts.public')

@section('title', 'Register - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-4 text-white animated zoomIn">Create Account</h1>
            <a href="{{ url('/') }}" class="h5 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <span class="h5 text-white">Register</span>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Register Form Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-light rounded-lg p-5 shadow-sm" style="border-top: 4px solid #06A3DA;">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus text-primary" style="font-size: 3.5rem;"></i>
                            <h3 class="fw-bold mt-3 mb-2" style="color: #091E3E;">Create Your Account</h3>
                            <p class="text-muted">Join us to access our services</p>
                        </div>

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="name" class="form-label fw-semibold" style="color: #091E3E;">
                                    <i class="bi bi-person me-2"></i>Full Name
                                </label>
                                <input id="name" 
                                       type="text" 
                                       class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required 
                                       autocomplete="name" 
                                       autofocus
                                       placeholder="Enter your full name"
                                       style="border-radius: 8px; border: 2px solid #e5e7eb;">

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold" style="color: #091E3E;">
                                    <i class="bi bi-envelope me-2"></i>Email Address
                                </label>
                                <input id="email" 
                                       type="email" 
                                       class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autocomplete="email"
                                       placeholder="Enter your email"
                                       style="border-radius: 8px; border: 2px solid #e5e7eb;">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold" style="color: #091E3E;">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <input id="password" 
                                       type="password" 
                                       class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                       name="password" 
                                       required 
                                       autocomplete="new-password"
                                       placeholder="Create a strong password"
                                       style="border-radius: 8px; border: 2px solid #e5e7eb;">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-semibold" style="color: #091E3E;">
                                    <i class="bi bi-lock-check me-2"></i>Confirm Password
                                </label>
                                <input id="password-confirm" 
                                       type="password" 
                                       class="form-control form-control-lg" 
                                       name="password_confirmation" 
                                       required 
                                       autocomplete="new-password"
                                       placeholder="Confirm your password"
                                       style="border-radius: 8px; border: 2px solid #e5e7eb;">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-check-circle me-2"></i>Create Account
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-3">Already have an account? <a href="{{ route('login') }}" class="text-primary text-decoration-none fw-semibold">Sign in here</a></p>
                        <a href="{{ url('/') }}" class="text-primary text-decoration-none fw-semibold">
                            <i class="bi bi-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Register Form End -->
@endsection
