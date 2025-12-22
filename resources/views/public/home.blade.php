@extends('layouts.public')

@section('title', 'Home - Klinik Pergigian Helmy')

@section('content')
<!-- Carousel Start -->
<div class="container-fluid p-0 mb-5">
    <div id="header-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="w-100" src="{{ asset('pergigianhelmy/img/carousel-1.jpg') }}" alt="Image">
                <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                    <div class="p-3" style="max-width: 1100px;">
                        <h5 class="text-white text-uppercase mb-3 animated slideInDown">Keep Your Teeth Healthy</h5>
                        <h1 class="display-1 text-white mb-md-4 animated zoomIn">Take The Best Quality Dental Treatment</h1>
                        
                        <!-- Quick Actions Cards Inside Carousel -->
                        <div class="row g-4 justify-content-center mt-5" style="max-width: 1000px; margin: 0 auto;">
                            <!-- Book Appointment Card -->
                            <div class="col-lg-5 col-md-6">
                                <a href="{{ url('/book') }}" class="text-decoration-none">
                                    <div class="card border-0 h-100 shadow-lg position-relative overflow-hidden action-card" style="border-radius: 20px; transition: all 0.4s ease;">
                                        <!-- Gradient Background Accent -->
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(6, 163, 218, 0.05) 0%, rgba(6, 163, 218, 0.02) 100%);"></div>
                                        
                                        <!-- Decorative Background Icon -->
                                        <div class="position-absolute" style="top: -20px; right: -20px; opacity: 0.08;">
                                            <i class="bi bi-calendar-heart" style="font-size: 180px; color: #06A3DA;"></i>
                                        </div>

                                        <div class="card-body p-4 position-relative d-flex flex-column">
                                            <!-- Icon Badge -->
                                            <div class="d-inline-flex align-items-center justify-content-center mb-4 position-relative" style="width: 80px; height: 80px;">
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #06A3DA 0%, #0584b7 100%); opacity: 0.1;"></div>
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #06A3DA 0%, #0584b7 100%); opacity: 0.2; transform: scale(0.8);"></div>
                                                <i class="bi bi-calendar-plus" style="font-size: 42px; color: #06A3DA; position: relative; z-index: 1;"></i>
                                            </div>

                                            <!-- Content -->
                                            <h3 class="fw-bold mb-3 text-dark" style="font-size: 1.5rem;">Book Appointment</h3>
                                            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                                                Schedule your visit online in minutes. Select your preferred date, time, dentist, and service.
                                            </p>

                                            <!-- Features List -->
                                            <ul class="list-unstyled mb-4">
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    <small class="text-muted">Choose preferred dentist</small>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    <small class="text-muted">Instant confirmation</small>
                                                </li>
                                                <li>
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    <small class="text-muted">24/7 online booking</small>
                                                </li>
                                            </ul>

                                            <!-- Action Button -->
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3" style="border-top: 1px solid rgba(0,0,0,0.05);">
                                                <span class="text-primary fw-bold" style="font-size: 1.05rem;">Book Now</span>
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-arrow-right text-white" style="font-size: 1.2rem;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Find My Booking Card -->
                            <div class="col-lg-5 col-md-6">
                                <a href="{{ url('/find-my-booking') }}" class="text-decoration-none">
                                    <div class="card border-0 h-100 shadow-lg position-relative overflow-hidden action-card" style="border-radius: 20px; transition: all 0.4s ease;">
                                        <!-- Gradient Background Accent -->
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.02) 100%);"></div>
                                        
                                        <!-- Decorative Background Icon -->
                                        <div class="position-absolute" style="top: -20px; right: -20px; opacity: 0.08;">
                                            <i class="bi bi-search" style="font-size: 180px; color: #28a745;"></i>
                                        </div>

                                        <div class="card-body p-4 position-relative d-flex flex-column">
                                            <!-- Icon Badge -->
                                            <div class="d-inline-flex align-items-center justify-content-center mb-4 position-relative" style="width: 80px; height: 80px;">
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); opacity: 0.1;"></div>
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); opacity: 0.2; transform: scale(0.8);"></div>
                                                <i class="bi bi-search" style="font-size: 42px; color: #28a745; position: relative; z-index: 1;"></i>
                                            </div>

                                            <!-- Content -->
                                            <h3 class="fw-bold mb-3 text-dark" style="font-size: 1.5rem;">Find My Booking</h3>
                                            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                                                Quickly locate your appointment details using your phone number. Track and manage your booking.
                                            </p>

                                            <!-- Features List -->
                                            <ul class="list-unstyled mb-4">
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small class="text-muted">Search by phone number</small>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small class="text-muted">Real-time queue tracking</small>
                                                </li>
                                                <li>
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small class="text-muted">Check-in online</small>
                                                </li>
                                            </ul>

                                            <!-- Action Button -->
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3" style="border-top: 1px solid rgba(0,0,0,0.05);">
                                                <span class="text-success fw-bold" style="font-size: 1.05rem;">Find Now</span>
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-success" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-arrow-right text-white" style="font-size: 1.2rem;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <img class="w-100" src="{{ asset('pergigianhelmy/img/carousel-2.jpg') }}" alt="Image">
                <div class="carousel-caption d-flex flex-column align-items-center justify-content-center">
                    <div class="p-3" style="max-width: 1100px;">
                        <h5 class="text-white text-uppercase mb-3 animated slideInDown">Keep Your Teeth Healthy</h5>
                        <h1 class="display-1 text-white mb-md-4 animated zoomIn">Take The Best Quality Dental Treatment</h1>
                        
                        <!-- Quick Actions Cards Inside Carousel -->
                        <div class="row g-4 justify-content-center mt-5" style="max-width: 1000px; margin: 0 auto;">
                            <!-- Book Appointment Card -->
                            <div class="col-lg-5 col-md-6">
                                <a href="{{ url('/book') }}" class="text-decoration-none">
                                    <div class="card border-0 h-100 shadow-lg position-relative overflow-hidden action-card" style="border-radius: 20px; transition: all 0.4s ease;">
                                        <!-- Gradient Background Accent -->
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(6, 163, 218, 0.05) 0%, rgba(6, 163, 218, 0.02) 100%);"></div>
                                        
                                        <!-- Decorative Background Icon -->
                                        <div class="position-absolute" style="top: -20px; right: -20px; opacity: 0.08;">
                                            <i class="bi bi-calendar-heart" style="font-size: 180px; color: #06A3DA;"></i>
                                        </div>

                                        <div class="card-body p-4 position-relative d-flex flex-column">
                                            <!-- Icon Badge -->
                                            <div class="d-inline-flex align-items-center justify-content-center mb-4 position-relative" style="width: 80px; height: 80px;">
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #06A3DA 0%, #0584b7 100%); opacity: 0.1;"></div>
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #06A3DA 0%, #0584b7 100%); opacity: 0.2; transform: scale(0.8);"></div>
                                                <i class="bi bi-calendar-plus" style="font-size: 42px; color: #06A3DA; position: relative; z-index: 1;"></i>
                                            </div>

                                            <!-- Content -->
                                            <h3 class="fw-bold mb-3 text-dark" style="font-size: 1.5rem;">Book Appointment</h3>
                                            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                                                Schedule your visit online in minutes. Select your preferred date, time, dentist, and service.
                                            </p>

                                            <!-- Features List -->
                                            <ul class="list-unstyled mb-4">
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    <small class="text-muted">Choose preferred dentist</small>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    <small class="text-muted">Instant confirmation</small>
                                                </li>
                                                <li>
                                                    <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                                    <small class="text-muted">24/7 online booking</small>
                                                </li>
                                            </ul>

                                            <!-- Action Button -->
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3" style="border-top: 1px solid rgba(0,0,0,0.05);">
                                                <span class="text-primary fw-bold" style="font-size: 1.05rem;">Book Now</span>
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-arrow-right text-white" style="font-size: 1.2rem;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Find My Booking Card -->
                            <div class="col-lg-5 col-md-6">
                                <a href="{{ url('/find-my-booking') }}" class="text-decoration-none">
                                    <div class="card border-0 h-100 shadow-lg position-relative overflow-hidden action-card" style="border-radius: 20px; transition: all 0.4s ease;">
                                        <!-- Gradient Background Accent -->
                                        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.02) 100%);"></div>
                                        
                                        <!-- Decorative Background Icon -->
                                        <div class="position-absolute" style="top: -20px; right: -20px; opacity: 0.08;">
                                            <i class="bi bi-search" style="font-size: 180px; color: #28a745;"></i>
                                        </div>

                                        <div class="card-body p-4 position-relative d-flex flex-column">
                                            <!-- Icon Badge -->
                                            <div class="d-inline-flex align-items-center justify-content-center mb-4 position-relative" style="width: 80px; height: 80px;">
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); opacity: 0.1;"></div>
                                                <div class="position-absolute w-100 h-100 rounded-circle" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); opacity: 0.2; transform: scale(0.8);"></div>
                                                <i class="bi bi-search" style="font-size: 42px; color: #28a745; position: relative; z-index: 1;"></i>
                                            </div>

                                            <!-- Content -->
                                            <h3 class="fw-bold mb-3 text-dark" style="font-size: 1.5rem;">Find My Booking</h3>
                                            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 0.95rem;">
                                                Quickly locate your appointment details using your phone number. Track and manage your booking.
                                            </p>

                                            <!-- Features List -->
                                            <ul class="list-unstyled mb-4">
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small class="text-muted">Search by phone number</small>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small class="text-muted">Real-time queue tracking</small>
                                                </li>
                                                <li>
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                                    <small class="text-muted">Check-in online</small>
                                                </li>
                                            </ul>

                                            <!-- Action Button -->
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-3" style="border-top: 1px solid rgba(0,0,0,0.05);">
                                                <span class="text-success fw-bold" style="font-size: 1.05rem;">Find Now</span>
                                                <div class="d-flex align-items-center justify-content-center rounded-circle bg-success" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-arrow-right text-white" style="font-size: 1.2rem;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>
<!-- Carousel End -->

<!-- About Start -->
<div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="section-title mb-4">
                    <h5 class="position-relative d-inline-block text-primary text-uppercase">About Us</h5>
                    <h1 class="display-5 mb-0">The Best Dental Clinic That You Can Trust</h1>
                </div>
                <h4 class="text-body fst-italic mb-4">At our dental clinic, we are committed to provide professional, safe, and comfortable dental care for patient of all ages.</h4>
                <p class="mb-4">From routine dental check-ups and cleanings to advanced dental treatments, we focus on your oral health, comfort, and long-term smile. We believe great dental care starts with trust, clear communication, and personalized treatment plans tailored to your needs.</p>
                <div class="row g-3">
                    <div class="col-sm-6 wow zoomIn" data-wow-delay="0.3s">
                        <h5 class="mb-3"><i class="fa fa-check-circle text-primary me-3"></i>Award Winning</h5>
                        <h5 class="mb-3"><i class="fa fa-check-circle text-primary me-3"></i>Professional Staff</h5>
                    </div>
                    <div class="col-sm-6 wow zoomIn" data-wow-delay="0.6s">
                        <h5 class="mb-3"><i class="fa fa-check-circle text-primary me-3"></i>24/7 Opened</h5>
                        <h5 class="mb-3"><i class="fa fa-check-circle text-primary me-3"></i>Fair Prices</h5>
                    </div>
                </div>
                <a href="{{ url('/book') }}" class="btn btn-primary py-3 px-5 mt-4 wow zoomIn" data-wow-delay="0.6s">Make Appointment</a>
            </div>
            <div class="col-lg-5" style="min-height: 500px;">
                <div class="position-relative h-100">
                    <img class="position-absolute w-100 h-100 rounded wow zoomIn" data-wow-delay="0.9s" src="{{ asset('pergigianhelmy/img/about.jpg') }}" style="object-fit: cover;">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- About End -->

<!-- Service Start -->
<div class="container-fluid py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container">
        <div class="row g-5 mb-5">
            <div class="col-lg-5 wow zoomIn" data-wow-delay="0.3s" style="min-height: 400px;">
                <div class="h-100 position-relative">
                    <img class="position-absolute w-100 h-100" src="{{ asset('pergigianhelmy/img/about.jpg') }}" style="object-fit: cover;">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="section-title mb-5">
                    <h5 class="position-relative d-inline-block text-primary text-uppercase">Our Services</h5>
                    <h1 class="display-5 mb-0">We Offer The Best Quality Dental Services</h1>
                </div>
                <div class="row g-5">
                    <div class="col-md-6 service-item wow zoomIn" data-wow-delay="0.6s">
                        <div class="rounded-top overflow-hidden">
                            <img class="img-fluid" src="{{ asset('pergigianhelmy/img/service-1.jpg') }}" alt="">
                        </div>
                        <div class="position-relative bg-light rounded-bottom text-center p-4">
                            <h5 class="m-0">Cosmetic Dentistry</h5>
                        </div>
                    </div>
                    <div class="col-md-6 service-item wow zoomIn" data-wow-delay="0.9s">
                        <div class="rounded-top overflow-hidden">
                            <img class="img-fluid" src="{{ asset('pergigianhelmy/img/service-2.jpg') }}" alt="">
                        </div>
                        <div class="position-relative bg-light rounded-bottom text-center p-4">
                            <h5 class="m-0">Dental Implants</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="col-lg-7">
                <div class="row g-5">
                    <div class="col-md-6 service-item wow zoomIn" data-wow-delay="0.3s">
                        <div class="rounded-top overflow-hidden">
                            <img class="img-fluid" src="{{ asset('pergigianhelmy/img/service-3.jpg') }}" alt="">
                        </div>
                        <div class="position-relative bg-light rounded-bottom text-center p-4">
                            <h5 class="m-0">Dental Bridges</h5>
                        </div>
                    </div>
                    <div class="col-md-6 service-item wow zoomIn" data-wow-delay="0.6s">
                        <div class="rounded-top overflow-hidden">
                            <img class="img-fluid" src="{{ asset('pergigianhelmy/img/service-4.jpg') }}" alt="">
                        </div>
                        <div class="position-relative bg-light rounded-bottom text-center p-4">
                            <h5 class="m-0">Teeth Whitening</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 service-item wow zoomIn" data-wow-delay="0.9s">
                <div class="position-relative bg-primary rounded h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                    <h3 class="text-white mb-3">Make Appointment</h3>
                    <p class="text-white mb-3">Clita ipsum magna kasd rebum at ipsum amet dolor justo dolor est magna stet eirmod</p>
                    <h2 class="text-white mb-3">06-677 1940</h2>
                    <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="btn btn-light btn-lg">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Service End -->

<!-- Team Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.1s">
                <div class="section-title bg-light rounded h-100 p-5">
                    <h5 class="position-relative d-inline-block text-primary text-uppercase">Our Dentist</h5>
                    <h1 class="display-6 mb-4">Meet Our Certified & Experienced Dentist</h1>
                    <a href="{{ url('/book') }}" class="btn btn-primary py-3 px-5">Appointment</a>
                </div>
            </div>
            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.3s">
                <div class="team-item">
                    <div class="position-relative rounded-top" style="z-index: 1;">
                        <img class="img-fluid rounded-top w-100" src="{{ asset('pergigianhelmy/img/team-1.jpg') }}" alt="">
                        <div class="position-absolute top-100 start-50 translate-middle bg-light rounded p-2 d-flex">
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-twitter fw-normal"></i></a>
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-facebook-f fw-normal"></i></a>
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-linkedin-in fw-normal"></i></a>
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-instagram fw-normal"></i></a>
                        </div>
                    </div>
                    <div class="team-text position-relative bg-light text-center rounded-bottom p-4 pt-5">
                        <h4 class="mb-2">Dr. John Doe</h4>
                        <p class="text-primary mb-0">Implant Surgeon</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 wow slideInUp" data-wow-delay="0.6s">
                <div class="team-item">
                    <div class="position-relative rounded-top" style="z-index: 1;">
                        <img class="img-fluid rounded-top w-100" src="{{ asset('pergigianhelmy/img/team-2.jpg') }}" alt="">
                        <div class="position-absolute top-100 start-50 translate-middle bg-light rounded p-2 d-flex">
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-twitter fw-normal"></i></a>
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-facebook-f fw-normal"></i></a>
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-linkedin-in fw-normal"></i></a>
                            <a class="btn btn-primary btn-square m-1" href="#"><i class="fab fa-instagram fw-normal"></i></a>
                        </div>
                    </div>
                    <div class="team-text position-relative bg-light text-center rounded-bottom p-4 pt-5">
                        <h4 class="mb-2">Dr. John Doe</h4>
                        <p class="text-primary mb-0">Implant Surgeon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Team End -->

<!-- Contact Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-xl-4 col-lg-6 wow slideInUp" data-wow-delay="0.1s">
                <div class="bg-light rounded h-100 p-5">
                    <div class="section-title">
                        <h5 class="position-relative d-inline-block text-primary text-uppercase">Contact Us</h5>
                        <h1 class="display-6 mb-4">Feel Free To Contact Us</h1>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-geo-alt fs-1 text-primary me-3"></i>
                        <div class="text-start">
                            <h5 class="mb-0">Our Office</h5>
                            <span>No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, Taman Sri Mawar Fasa 2, 70450 Seremban, Negeri Sembilan</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-envelope-open fs-1 text-primary me-3"></i>
                        <div class="text-start">
                            <h5 class="mb-0">Email Us</h5>
                            <span>klinikgigihelmy@gmail.com</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-phone-vibrate fs-1 text-primary me-3"></i>
                        <div class="text-start">
                            <h5 class="mb-0">Call Us</h5>
                            <span>06-677 1940</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-whatsapp fs-1 text-primary me-3"></i>
                        <div class="text-start">
                            <h5 class="mb-0">WhatsApp</h5>
                            <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="text-decoration-none">
                                <span>Message us on WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-6 wow slideInUp" data-wow-delay="0.3s">
                <form>
                    <div class="row g-3">
                        <div class="col-12">
                            <input type="text" class="form-control border-0 bg-light px-4" placeholder="Your Name" style="height: 55px;">
                        </div>
                        <div class="col-12">
                            <input type="email" class="form-control border-0 bg-light px-4" placeholder="Your Email" style="height: 55px;">
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control border-0 bg-light px-4" placeholder="Subject" style="height: 55px;">
                        </div>
                        <div class="col-12">
                            <textarea class="form-control border-0 bg-light px-4 py-3" rows="5" placeholder="Message"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary w-100 py-3" type="submit">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-xl-4 col-lg-12 wow slideInUp" data-wow-delay="0.6s">
                <iframe class="position-relative rounded w-100 h-100"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3001156.4288297426!2d-78.01371936852176!3d42.72876761954724!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4ccc4bf0f123a5a9%3A0xddcfc6c1de189567!2sNew%20York%2C%20USA!5e0!3m2!1sen!2sbd!4v1603794290143!5m2!1sen!2sbd"
                    frameborder="0" style="min-height: 400px; border:0;" allowfullscreen="" aria-hidden="false"
                    tabindex="0"></iframe>
            </div>
        </div>
    </div>
</div>
<!-- Contact End -->
@endsection
