@extends('layouts.public')

@section('title', 'Our Dentists - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-3">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Our Dentists</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/dentists') }}" class="h4 text-white">Dentists</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Team Start -->
<div class="container-fluid pt-3 pb-5">
    <div class="container">
        <div class="section-title text-center position-relative pb-3 mb-5 mx-auto">
            <h5 class="fw-bold text-primary text-uppercase" style="position: relative;">
                Over 20 Years of Clinical Experience
                <style>
                    .section-title h5::before,
                    .section-title h5::after {
                        display: none !important;
                    }
                </style>
            </h5>
            <h1 class="mb-0">Meet Our Certified & Experienced Dentists</h1>
        </div>
        <div class="row g-5">
            @forelse($dentists as $dentist)
            <div class="col-lg-4 wow slideInUp" data-wow-delay="{{ 0.1 * ($loop->index + 1) }}s">
                <div class="team-item">
                    <div class="position-relative rounded-top" style="z-index: 1;">
                        @if($dentist->photo)
                            <img class="img-fluid rounded-top w-100" src="{{ asset($dentist->photo) }}" alt="{{ $dentist->name }}" style="object-fit: cover; height: 350px;">
                        @else
                            <img class="img-fluid rounded-top w-100" src="{{ asset('pergigianhelmy/img/team-' . (($loop->index % 3) + 1) . '.jpg') }}" alt="{{ $dentist->name }}">
                        @endif
                        @php
                            $links = [
                                'twitter' => $dentist->twitter_url ?? null,
                                'facebook' => $dentist->facebook_url ?? null,
                                'linkedin' => $dentist->linkedin_url ?? null,
                                'instagram' => $dentist->instagram_url ?? null,
                            ];
                            $hasAnyLink = collect($links)->filter()->isNotEmpty();
                        @endphp
                        @if($hasAnyLink)
                            <div class="position-absolute top-100 start-50 translate-middle bg-light rounded p-2 d-flex">
                                @if($links['twitter'])
                                    <a class="btn btn-primary btn-square m-1" href="{{ $links['twitter'] }}" target="_blank" rel="noopener"><i class="fab fa-twitter fw-normal"></i></a>
                                @endif
                                @if($links['facebook'])
                                    <a class="btn btn-primary btn-square m-1" href="{{ $links['facebook'] }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f fw-normal"></i></a>
                                @endif
                                @if($links['linkedin'])
                                    <a class="btn btn-primary btn-square m-1" href="{{ $links['linkedin'] }}" target="_blank" rel="noopener"><i class="fab fa-linkedin-in fw-normal"></i></a>
                                @endif
                                @if($links['instagram'])
                                    <a class="btn btn-primary btn-square m-1" href="{{ $links['instagram'] }}" target="_blank" rel="noopener"><i class="fab fa-instagram fw-normal"></i></a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="team-text position-relative bg-light text-center rounded-bottom p-4 pt-5">
                        <h4 class="mb-2">{{ $dentist->name }}</h4>
                        <p class="text-primary mb-1">{{ $dentist->specialization ?? 'General Dentist' }}</p>
                        @if($dentist->years_of_experience)
                            <p class="text-muted small mb-2"><i class="bi bi-award"></i> {{ $dentist->years_of_experience }} years of experience</p>
                        @endif
                        @if($dentist->bio)
                            <p class="text-muted small">{{ Str::limit($dentist->bio, 100) }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p>No dentists available at the moment.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
<!-- Team End -->
@endsection
