<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Developer Tools')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
    
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
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%) !important;
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
            font-size: 1.5rem !important;
        }
        
        .navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            margin-left: 0.5rem;
        }
        
        .navbar .nav-link:hover {
            color: white !important;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #1f2937 !important;
            border-right: 1px solid #374151;
            overflow-y: auto;
            z-index: 1020;
            padding: 1.5rem 0 !important;
            transition: transform 0.3s ease, width 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-250px);
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 70px;
            left: 260px;
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
            color: #d1d5db !important;
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
            background-color: rgba(59, 130, 246, 0.2) !important;
            color: #3b82f6 !important;
            border-left-color: #3b82f6 !important;
        }
        
        .sidebar .nav-link.active {
            border-left-color: #3b82f6 !important;
            color: #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.1) !important;
        }
        
        .sidebar .nav-title {
            color: #9ca3af;
            padding: 0.75rem 1.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }
        
        /* Main content */
        .main-content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 60px);
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-left: 4px solid #3b82f6;
        }
        
        .stat-card.success {
            border-left-color: #10b981;
        }
        
        .stat-card.warning {
            border-left-color: #f59e0b;
        }
        
        .stat-card.danger {
            border-left-color: #ef4444;
        }
        
        /* Table */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        /* Badge styles */
        .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        /* Page header */
        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: #6b7280;
            margin-bottom: 0;
        }
        
        /* Form */
        .form-control, .form-select {
            border-color: #d1d5db;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                left: 10px;
            }
            
            .sidebar.collapsed {
                transform: translateX(0);
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/developer/dashboard">
                <i class="fas fa-code"></i> Developer Tools
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text">
                            <i class="fas fa-user"></i> {{ Auth::user()->name }}
                        </span>
                    </li>
                    <li class="nav-item">
                        <form action="/developer/logout" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="nav-title">Dashboard</div>
        <a href="/developer/dashboard" class="nav-link {{ request()->is('developer/dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Overview
        </a>

        <div class="nav-title">Activity & Logs</div>
        <a href="/developer/activity-logs" class="nav-link {{ request()->is('developer/activity-logs*') ? 'active' : '' }}">
            <i class="fas fa-list"></i> Activity Logs
        </a>

        <div class="nav-title">Developer Tools</div>
        <a href="/developer/api-test" class="nav-link {{ request()->is('developer/api-test') ? 'active' : '' }}">
            <i class="fas fa-plug"></i> API Test
        </a>
        <a href="/developer/system-info" class="nav-link {{ request()->is('developer/system-info') ? 'active' : '' }}">
            <i class="fas fa-server"></i> System Info
        </a>
        <a href="/developer/database" class="nav-link {{ request()->is('developer/database') ? 'active' : '' }}">
            <i class="fas fa-database"></i> Database
        </a>

        <div class="nav-title">Back to Staff</div>
        <a href="/staff/appointments" class="nav-link">
            <i class="fas fa-arrow-left"></i> Staff Panel
        </a>
    </div>

    <!-- Sidebar Toggle -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">Error!</h4>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const toggle = this;
            
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            toggle.classList.toggle('collapsed');
        });
    </script>

    @yield('scripts')
</body>
</html>
