<!-- Footer Start -->
<div class="container-fluid bg-dark text-light py-5 wow fadeInUp" data-wow-delay="0.1s">
    <div class="container pt-5">
        <div class="row g-5 pt-4">
            <div class="col-lg-3 col-md-6">
                <h3 class="text-white mb-4">Quick Links</h3>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-light mb-2" href="{{ url('/') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Home</a>
                    <a class="text-light mb-2" href="{{ url('/about') }}"><i class="bi bi-arrow-right text-primary me-2"></i>About Us</a>
                    <a class="text-light mb-2" href="{{ url('/services') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Our Services</a>
                    <a class="text-light mb-2" href="{{ url('/dentists') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Our Dentists</a>
                    <a class="text-light" href="{{ url('/contact') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Contact Us</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <h3 class="text-white mb-4">Popular Links</h3>
                <div class="d-flex flex-column justify-content-start">
                    <a class="text-light mb-2" href="{{ url('/book') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Book Appointment</a>
                    <a class="text-light mb-2" href="{{ url('/find-my-booking') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Find My Booking</a>
                    <a class="text-light mb-2" href="{{ url('/services') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Our Services</a>
                    <a class="text-light mb-2" href="{{ url('/about') }}"><i class="bi bi-arrow-right text-primary me-2"></i>About Us</a>
                    <a class="text-light" href="{{ url('/contact') }}"><i class="bi bi-arrow-right text-primary me-2"></i>Contact Us</a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <h3 class="text-white mb-4">Get In Touch</h3>
                <p class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i>No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, Taman Sri Mawar Fasa 2, 70450 Seremban, Negeri Sembilan</p>
                <p class="mb-2"><i class="bi bi-envelope-open text-primary me-2"></i>klinikgigihelmy@gmail.com</p>
                <p class="mb-2"><i class="bi bi-telephone text-primary me-2"></i>06-677 1940</p>
                <p class="mb-0">
                    <i class="bi bi-whatsapp text-primary me-2"></i>
                    <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="text-light text-decoration-none">
                        WhatsApp Us
                    </a>
                </p>
            </div>
            <div class="col-lg-3 col-md-6">
                <h3 class="text-white mb-4">Follow Us</h3>
                <div class="d-flex">
                    <a class="btn btn-lg btn-primary btn-lg-square rounded me-2" href="#"><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-lg btn-primary btn-lg-square rounded me-2" href="#"><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-lg btn-primary btn-lg-square rounded me-2" href="#"><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-lg btn-primary btn-lg-square rounded" href="#"><i class="fab fa-instagram fw-normal"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid text-light py-4" style="background: #051225;">
    <div class="container">
        <div class="row g-0">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-md-0">&copy; <a class="text-white border-bottom" href="#">Klinik Pergigian Helmy</a>. All Rights Reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0">
                    <span class="text-white-50">Designed by <a class="text-white border-bottom" href="https://www.instagram.com/meeraomar__?igsh=MXRoNG1zN2N4dms1Nw%3D%3D&utm_source=qr" target="_blank">Ameera Omar</a></span>
                    <span class="text-white-50 ms-2">
                        <a href="{{ auth()->check() ? url('/staff/appointments') : '/login' }}" class="text-white-50 text-decoration-none" title="Staff Access" style="font-size: 0.9rem; cursor: pointer;">
                            <i class="bi bi-shield-lock"></i>
                        </a>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>
<!-- Footer End -->
