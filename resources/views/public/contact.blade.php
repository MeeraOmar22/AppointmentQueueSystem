@extends('layouts.public')

@section('title', 'Contact Us - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-4">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Our Team & Contact</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/contact') }}" class="h4 text-white">Contact</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Contact Start -->
<div class="container-fluid py-5">
    <div class="container">
        <!-- Contact Info Start (Top) -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light rounded p-4 h-100 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-geo-alt fs-2 text-primary me-3"></i>
                        <div>
                            <h5 class="mb-2">Our Office</h5>
                            <span>No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, Taman Sri Mawar Fasa 2, 70450 Seremban, Negeri Sembilan</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light rounded p-4 h-100 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-envelope-open fs-2 text-primary me-3"></i>
                        <div>
                            <h5 class="mb-2">Email Us</h5>
                            <span>klinikgigihelmy@gmail.com</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-start mt-3">
                        <i class="bi bi-whatsapp fs-2 text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">WhatsApp</h6>
                            <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="text-decoration-none">Message us on WhatsApp</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-light rounded p-4 h-100 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-phone-vibrate fs-2 text-primary me-3"></i>
                        <div>
                            <h5 class="mb-2">Call Us</h5>
                            <span>06-677 1940</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Contact Info End (Top) -->
        
        <!-- Staff Team Start -->
        @if(isset($staff) && $staff->count())
        <!-- <div class="text-center pb-1 mb-3 mx-auto" style="max-width: 700px;">
            <h1 class="mb-0">Meet Our Friendly Team</h1>
        </div> -->
        <div class="row g-3">
            @foreach($staff as $member)
                <div class="col-lg-4 col-md-6">
                    <div class="team-item shadow-sm border rounded">
                        <div class="position-relative rounded-top" style="z-index: 1;">
                            @php
                                $photo = method_exists($member, 'getAttribute') ? ($member->photo ?? null) : null;
                            @endphp
                            @if(!empty($photo))
                                <img class="img-fluid rounded-top w-100" src="{{ asset($photo) }}" alt="{{ $member->name }}" style="object-fit: cover; height: 350px;">
                            @else
                                <img class="img-fluid rounded-top w-100" src="{{ asset('pergigianhelmy/img/team-' . (($loop->index % 3) + 1) . '.jpg') }}" alt="{{ $member->name }}" style="object-fit: cover; height: 350px;">
                            @endif
                        </div>
                        <div class="team-text position-relative bg-light text-center rounded-bottom p-4">
                            <h4 class="mb-2">{{ $member->name }}</h4>
                            @if(!empty($member->position))
                                <p class="text-primary mb-1">{{ $member->position }}</p>
                            @endif
                            @if(!empty($member->email))
                                <p class="text-muted small mb-0"><i class="bi bi-envelope"></i> {{ $member->email }}</p>
                            @endif
                            @if(!empty($member->phone))
                                <p class="text-muted small"><i class="bi bi-telephone"></i> {{ $member->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
        <!-- Staff Team End -->

        
    </div>
</div>
<!-- Contact End -->
@endsection
