<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Klinik Pergigian Helmy')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Dental Clinic" name="keywords">
    <meta content="Klinik Pergigian Helmy" name="description">

    <!-- Favicon -->
    <link href="{{ asset('pergigianhelmy/img/favicon.ico') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('pergigianhelmy/lib/animate/animate.min.css') }}?v={{ time() }}" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('pergigianhelmy/css/bootstrap.min.css') }}?v={{ time() }}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{ asset('pergigianhelmy/css/style.css') }}?v={{ time() }}" rel="stylesheet">

    @stack('styles')
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-grow text-primary m-1" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="spinner-grow text-dark m-1" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="spinner-grow text-secondary m-1" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    @include('partials.topbar')

    @include('partials.navbar')

    @yield('content')

    @include('partials.footer')

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded back-to-top"><i class="bi bi-arrow-up"></i></a>

    @include('partials.scripts')

    @stack('scripts')
</body>

</html>
