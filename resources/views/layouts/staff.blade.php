<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Dashboard')</title>
    
    <!-- Force refresh -->
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Staff Dashboard Inline Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            width: 100%;
            height: 100%;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f8fafc !important;
            color: #333;
            line-height: 1.6;
        }
        
        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            width: 100%;
            min-height: 60px;
            padding: 0.75rem 0 !important;
            border: none !important;
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 700;
        }
        
        .navbar-brand span {
            color: white !important;
        }
        
        .navbar-text {
            color: #ddd !important;
        }
        
        .navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .navbar .nav-link:hover {
            color: white !important;
        }
        
        .navbar-toggler {
            border-color: rgba(255,255,255,0.3) !important;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 280px;
            background: #f8fafc !important;
            border-right: 1px solid #ddd;
            overflow-y: auto;
            z-index: 1020;
            padding: 1.5rem 0 !important;
            transition: transform 0.3s ease, width 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-280px);
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 70px;
            left: 290px;
            z-index: 1021;
            background: white;
            border: 1px solid #ddd;
            border-radius: 0 6px 6px 0;
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
            transition: left 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            background: #f8f9fa;
        }
        
        .sidebar-toggle.collapsed {
            left: 10px;
        }
        
        .sidebar .nav-link {
            color: #555 !important;
            padding: 0.75rem 1.5rem !important;
            border-left: 3px solid transparent !important;
            margin: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .sidebar .nav-link i {
            font-size: 1.1rem;
            min-width: 24px;
        }
        
        .sidebar .nav-link:hover {
            background-color: #e8f1f7 !important;
            color: #06A3DA !important;
            border-left-color: #06A3DA !important;
        }
        
        .sidebar .nav-link.active {
            border-left-color: #06A3DA !important;
            background-color: #e8f1f7 !important;
            color: #06A3DA !important;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            margin-top: 60px;
            padding: 2.5rem 2rem;
            min-height: calc(100vh - 60px);
            background-color: #f8fafc !important;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Typography */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
            color: #091E3E;
            margin-bottom: 1rem;
        }
        
        .fw-bold {
            font-weight: 700 !important;
        }
        
        .fw-semibold {
            font-weight: 600 !important;
        }
        
        /* Cards */
        .card {
            border: none !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
            margin-bottom: 1.5rem;
            background: white !important;
        }
        
        .card-header {
            background: linear-gradient(135deg, #06A3DA 0%, #0582b4 100%) !important;
            color: white !important;
            border: none !important;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .stats-card {
            transition: transform 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12) !important;
        }
        
        /* Tables */
        .table-responsive {
            border-radius: 8px;
            overflow-x: auto;
            overflow-y: visible;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table-sm {
            font-size: 0.85rem;
        }
        
        .table-sm thead th {
            padding: 0.5rem 0.35rem;
            white-space: nowrap;
        }
        
        .table-sm tbody td {
            padding: 0.5rem 0.35rem;
            font-size: 0.82rem;
        }
        
        .table thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            font-size: 0.8rem;
            color: #555;
            padding: 0.6rem 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 0.6rem 0.4rem;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Button Groups in Tables */
        .btn-group {
            display: inline-flex;
            gap: 1px;
            flex-wrap: nowrap;
        }
        
        .btn-group .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
            white-space: nowrap;
            line-height: 1.2;
        }
        
        .btn-ghost {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
        }
        
        .btn-ghost:hover:not(:disabled) {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }
        
        .btn-ghost:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Compact badge in tables */
        .table .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }
        
        /* Buttons */
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background-color: #06A3DA !important;
            border-color: #06A3DA !important;
            color: white !important;
        }
        
        .btn-primary:hover {
            background-color: #0582b4 !important;
            border-color: #0582b4 !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(6, 163, 218, 0.3);
        }
        
        .btn-outline-primary {
            color: #06A3DA !important;
            border-color: #06A3DA !important;
        }
        
        .btn-outline-primary:hover {
            background-color: #06A3DA !important;
            color: white !important;
        }
        
        .btn-success {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }
        
        .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        /* Badges */
        .badge {
            font-weight: 600 !important;
            padding: 0.35rem 0.65rem !important;
            border-radius: 4px;
        }
        
        .badge.bg-success, .badge-success {
            background-color: #28a745 !important;
        }
        
        .badge.bg-warning, .badge-warning {
            background-color: #ffc107 !important;
            color: #333 !important;
        }
        
        .badge.bg-danger, .badge-danger {
            background-color: #dc3545 !important;
        }
        
        .badge.bg-info, .badge-info {
            background-color: #06A3DA !important;
        }
        
        .badge.bg-secondary {
            background-color: #6c757d !important;
        }
        
        /* Forms */
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid #dee2e6;
            padding: 0.625rem 0.875rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #06A3DA;
            box-shadow: 0 0 0 0.2rem rgba(6, 163, 218, 0.15);
        }
        
        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Nav Pills */
        .nav-pills .nav-link {
            border-radius: 6px;
            padding: 0.625rem 1.25rem;
            color: #555;
            font-weight: 500;
        }
        
        .nav-pills .nav-link.active {
            background-color: #06A3DA !important;
            color: white !important;
        }

        /* Nav Tabs */
        .nav-tabs .nav-item {
            margin-right: 0.75rem;
        }

        .nav-tabs .nav-link {
            padding: 0.75rem 1.5rem !important;
            border: none !important;
            border-bottom: 2px solid transparent !important;
            color: #555 !important;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            border-bottom-color: #06A3DA !important;
            color: #06A3DA !important;
            background-color: transparent !important;
        }
        
        /* Utilities */
        .text-primary {
            color: #06A3DA !important;
        }
        
        .text-success {
            color: #28a745 !important;
        }
        
        .text-danger {
            color: #dc3545 !important;
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        /* Pagination arrows - HIDE THE SVG COMPLETELY */
        .pagination svg {
            display: none !important;
        }
        
        /* Pagination styling */
        .pagination .page-link {
            font-size: 0.875rem !important;
            padding: 0.5rem 0.75rem !important;
        }
        
        /* Spacing */
        .mb-4 {
            margin-bottom: 1.5rem !important;
        }
        
        .gap-2 {
            gap: 0.5rem !important;
        }
        
        .gap-3 {
            gap: 1rem !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1.5rem 1rem;
            }
            
            .navbar {
                min-height: 50px;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="/staff/appointments">
            <i class="fa fa-tooth me-2" style="font-size: 1.5rem;"></i>
            <div class="d-flex flex-column align-items-start">
                <span style="font-size: 1.25rem; line-height: 1.1;">Helmy Dental Clinic</span>
                <small style="font-size: 0.7rem; opacity: 0.85; font-weight: 400; margin-top: -2px;">Staff Dashboard</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#staffNav" aria-controls="staffNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="staffNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <li class="nav-item">
                    <span class="navbar-text small d-flex align-items-center">
                        <i class="bi bi-calendar3 me-2"></i>{{ now()->format('D, d M Y') }}
                    </span>
                </li>
                <li class="nav-item">
                    <span class="navbar-text small d-flex align-items-center">
                        <i class="bi bi-clock me-2"></i><span id="realTimeClock">--:--:--</span>
                    </span>
                </li>
                <li class="nav-item">
                    <span class="navbar-text d-flex align-items-center">
                        <i class="bi bi-person-circle me-2"></i>{{ Auth::user()->name }}
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-sm btn-outline-light" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
    <i class="bi bi-chevron-left"></i>
</button>

<div class="sidebar" id="sidebar">
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->is('staff/quick-edit') ? 'active' : '' }}" href="/staff/quick-edit">
            <i class="bi bi-lightning-fill"></i> Quick Edit
        </a>
        <hr class="my-2" style="opacity: 0.1;">
        <a class="nav-link {{ request()->is('staff/appointments') ? 'active' : '' }}" href="/staff/appointments">
            <i class="bi bi-calendar-check"></i> Appointments & Queue
        </a>
        <a class="nav-link {{ request()->is('staff/treatment-completion') ? 'active' : '' }}" href="/staff/treatment-completion">
            <i class="bi bi-clipboard-check"></i> Treatment Completion
        </a>
        <a class="nav-link {{ request()->is('staff/operating-hours*') ? 'active' : '' }}" href="/staff/operating-hours">
            <i class="bi bi-clock"></i> Operating Hours
        </a>
        <a class="nav-link {{ request()->is('staff/dentist-schedules*') ? 'active' : '' }}" href="/staff/dentist-schedules">
            <i class="bi bi-calendar-week"></i> Dentist Schedules
        </a>
        <a class="nav-link {{ request()->is('staff/dentists*') ? 'active' : '' }}" href="/staff/dentists">
            <i class="bi bi-person-badge"></i> Dentists
        </a>
        <a class="nav-link {{ request()->is('staff/rooms*') ? 'active' : '' }}" href="/staff/rooms">
            <i class="bi bi-door-open"></i> Treatment Rooms
        </a>
        <a class="nav-link {{ request()->is('staff/services*') ? 'active' : '' }}" href="/staff/services">
            <i class="bi bi-grid-3x3-gap-fill"></i> Services
        </a>
        <a class="nav-link {{ request()->is('staff/feedback*') ? 'active' : '' }}" href="/staff/feedback">
            <i class="bi bi-chat-heart"></i> Patient Feedback
        </a>
        <a class="nav-link {{ request()->is('staff/reports*') ? 'active' : '' }}" href="/staff/reports/dashboard">
            <i class="bi bi-bar-chart"></i> Reports & Analytics
        </a>
        @if(Auth::user()->role === 'developer')
            <hr class="my-2" style="opacity: 0.1;">
            <a class="nav-link" href="/developer/dashboard" target="_blank">
                <i class="bi bi-code-slash"></i> Developer Tools <i class="bi bi-box-arrow-up-right ms-2" style="font-size: 0.8rem;"></i>
            </a>
        @endif
        <hr class="my-2" style="opacity: 0.1;">
        <a class="nav-link {{ request()->is('staff/past*') ? 'active' : '' }}" href="/staff/past">
            <i class="bi bi-trash"></i> Past Records
        </a>
    </nav>
</div>

<div class="main-content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Real-time clock
    function updateClock() {
        const clockEl = document.getElementById('realTimeClock');
        if (!clockEl) return; // Exit if element doesn't exist
        
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        clockEl.textContent = `${hours}:${minutes}:${seconds}`;
    }
    
    // Update clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
    
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Check localStorage for saved state
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isSidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
        sidebarToggle.classList.add('collapsed');
        sidebarToggle.querySelector('i').classList.remove('bi-chevron-left');
        sidebarToggle.querySelector('i').classList.add('bi-chevron-right');
    }
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        sidebarToggle.classList.toggle('collapsed');
        
        const icon = sidebarToggle.querySelector('i');
        if (sidebar.classList.contains('collapsed')) {
            icon.classList.remove('bi-chevron-left');
            icon.classList.add('bi-chevron-right');
            localStorage.setItem('sidebarCollapsed', 'true');
        } else {
            icon.classList.remove('bi-chevron-right');
            icon.classList.add('bi-chevron-left');
            localStorage.setItem('sidebarCollapsed', 'false');
        }
    });
</script>
@stack('scripts')
</body>
</html>
