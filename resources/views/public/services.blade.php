@extends('layouts.public')

@section('title', 'Our Services - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-3">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Services</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/services') }}" class="h4 text-white">Services</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Service Start -->
<div class="container-fluid pt-3 pb-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="section-title text-center position-relative pb-3 mb-4 mx-auto" style="max-width: 700px;">
            <h5 class="fw-bold text-primary text-uppercase" style="position: relative;">
                Best quality services
                <style>
                    .section-title h5::before,
                    .section-title h5::after {
                        display: none !important;
                    }
                </style>
            </h5>
            <h1 class="mb-0" style="white-space: nowrap;">Dental Care by People, for People</h1>
        </div>
        <div class="row g-4">
            @foreach($services as $service)
                <div class="col-lg-4 col-md-6 wow slideInUp" data-wow-delay="{{ 0.1 * ($loop->index + 1) }}s">
                    <div class="blog-item bg-light rounded overflow-hidden h-100">
                        <div class="blog-img position-relative overflow-hidden">
                            @if($service->image)
                                <img class="img-fluid" src="{{ asset($service->image) }}" alt="{{ $service->name }}" style="object-fit: cover; height: 250px; width: 100%;">
                            @else
                                <img class="img-fluid" src="{{ asset('pergigianhelmy/img/service-' . (($loop->index % 6) + 1) . '.jpg') }}" alt="{{ $service->name }}" style="object-fit: cover; height: 250px; width: 100%;">
                            @endif
                        </div>
                        <div class="p-4">
                            <h4 class="mb-3">{{ $service->name }}</h4>
                            <p>{{ Str::limit($service->description, 100) }}</p>
                            <div class="d-flex justify-content-between">
                                <span class="text-primary fw-bold">RM {{ number_format($service->price, 2) }}</span>
                                <a href="{{ url('/book') }}" class="text-uppercase">Book Now <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<!-- Service End -->
@endsection
