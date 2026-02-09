<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Queue Board - Klinik Pergigian Helmy')</title>
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

    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .queue-display-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .queue-header {
            background: linear-gradient(135deg, #06A3DA 0%, #004a7d 100%);
            color: white;
            padding: 20px;
            flex-shrink: 0;
        }

        .queue-content {
            flex-grow: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f4f6fb;
        }
    </style>
</head>

<body>
    <div class="queue-display-container">
        <!-- Header -->
        <div class="queue-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">🏥 Klinik Pergigian Helmy</h2>
                </div>
                <div class="text-end">
                    <div class="h5 mb-0"><span id="lastUpdate">Loading...</span></div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="queue-content">
            @yield('content')
        </div>
    </div>

    @include('partials.scripts')

    @stack('scripts')
</body>

</html>
