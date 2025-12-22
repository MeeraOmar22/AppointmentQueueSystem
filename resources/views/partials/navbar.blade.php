<!-- Navbar Start -->
<nav class="navbar navbar-expand-lg bg-white navbar-light shadow-sm px-5 py-3 py-lg-0">
    <a href="{{ url('/') }}" class="navbar-brand p-0">
        <h1 class="m-0 text-primary"><i class="fa fa-tooth me-2"></i>Helmy Dental Clinic</h1>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <div class="navbar-nav ms-auto py-0">
            <a href="{{ url('/') }}" class="nav-item nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
            <a href="{{ url('/about') }}" class="nav-item nav-link {{ request()->is('about') ? 'active' : '' }}">About</a>
            <a href="{{ url('/services') }}" class="nav-item nav-link {{ request()->is('services') ? 'active' : '' }}">Service</a>
            <a href="{{ url('/dentists') }}" class="nav-item nav-link {{ request()->is('dentists') ? 'active' : '' }}">Dentist</a>
            <a href="{{ url('/hours') }}" class="nav-item nav-link {{ request()->is('hours') ? 'active' : '' }}">Hours</a>
            <a href="{{ url('/contact') }}" class="nav-item nav-link {{ request()->is('contact') ? 'active' : '' }}">Contact</a>
        </div>
        <a href="{{ url('/book') }}" class="btn btn-primary py-2 px-4 ms-3">Book Appointment</a>
    </div>
</nav>
<!-- Navbar End -->
