@extends('layouts.staff')

@section('title', 'Developer Dashboard')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: #091E3E;">
            <i class="bi bi-code-slash text-primary me-2"></i>Developer Tools
        </h2>
        <p class="text-muted mb-0">
            <i class="bi bi-shield-check me-1"></i>Authenticated Developer Access
        </p>
    </div>
    <div>
        <a href="/staff/developer/logout" class="btn btn-outline-danger">
            <i class="bi bi-box-arrow-right me-2"></i>Logout from Developer Mode
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm hover-card" style="border-radius: 16px; cursor: pointer;" onclick="window.location.href='/staff/developer/api-test'">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">API Testing</h4>
                        <p class="text-muted mb-0 small">Test all API endpoints</p>
                    </div>
                </div>
                <p class="text-muted mb-3">
                    Interactive API testing tool for all endpoints including track, appointments, and queue management.
                </p>
                <a href="/staff/developer/api-test" class="btn btn-primary btn-sm">
                    <i class="bi bi-play-fill me-2"></i>Launch API Tester
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-database-fill text-success" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Database Info</h4>
                        <p class="text-muted mb-0 small">Connection & statistics</p>
                    </div>
                </div>
                <p class="text-muted mb-3">
                    View database connection details, table counts, and system information.
                </p>
                <div class="bg-light p-3 rounded">
                    <small class="d-block mb-1">
                        <strong>Connection:</strong> 
                        <span class="badge bg-success">{{ config('database.default') }}</span>
                    </small>
                    <small class="d-block mb-1">
                        <strong>Driver:</strong> {{ config('database.connections.'.config('database.default').'.driver') }}
                    </small>
                    <small class="d-block">
                        <strong>Laravel Version:</strong> {{ app()->version() }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-terminal-fill text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">System Info</h4>
                        <p class="text-muted mb-0 small">Server & environment</p>
                    </div>
                </div>
                <div class="bg-light p-3 rounded">
                    <small class="d-block mb-1">
                        <strong>PHP Version:</strong> {{ PHP_VERSION }}
                    </small>
                    <small class="d-block mb-1">
                        <strong>Environment:</strong> {{ app()->environment() }}
                    </small>
                    <small class="d-block mb-1">
                        <strong>Debug Mode:</strong> 
                        @if(config('app.debug'))
                            <span class="badge bg-danger">Enabled</span>
                        @else
                            <span class="badge bg-success">Disabled</span>
                        @endif
                    </small>
                    <small class="d-block">
                        <strong>Timezone:</strong> {{ config('app.timezone') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-diagram-3-fill text-info" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">Routes Overview</h4>
                        <p class="text-muted mb-0 small">Available endpoints</p>
                    </div>
                </div>
                <p class="text-muted mb-3">
                    Key routes available in the system:
                </p>
                <div class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">
                    <h6 class="fw-bold text-primary mb-2"><i class="bi bi-globe"></i> Public Routes</h6>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/</code> - Home</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/book</code> - Booking Form</small>
                    <small class="d-block mb-1"><span class="badge bg-success">POST</span> <code>/book</code> - Submit Booking</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/track/{code}</code> - Track Appointment</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/visit/{token}</code> - Visit Status</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/checkin</code> - Check-in Form</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/queue-board</code> - Queue Display</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/feedback</code> - Feedback Form</small>
                    
                    <hr class="my-2">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-shield-check"></i> Staff Routes</h6>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/appointments</code> - Queue Dashboard</small>
                    <small class="d-block mb-1"><span class="badge bg-success">POST</span> <code>/staff/checkin/{id}</code> - Check-in Patient</small>
                    <small class="d-block mb-1"><span class="badge bg-success">POST</span> <code>/staff/queue/{queue}/status</code> - Update Queue</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/quick-edit</code> - Quick Edit Dashboard</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/calendar</code> - Calendar View</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/activity-logs</code> - Activity Logs</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/feedback</code> - Feedback Management</small>
                    
                    <hr class="my-2">
                    <h6 class="fw-bold text-warning mb-2"><i class="bi bi-cloud"></i> API Routes</h6>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/api/track/{code}</code> - Track API</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/api/staff/appointments</code> - Appointments API</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/calendar/events</code> - Calendar Events</small>
                    
                    <hr class="my-2">
                    <h6 class="fw-bold text-secondary mb-2"><i class="bi bi-gear"></i> Management Routes</h6>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/dentists</code> - Manage Dentists</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/services</code> - Manage Services</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/operating-hours</code> - Operating Hours</small>
                    <small class="d-block mb-1"><span class="badge bg-primary">GET</span> <code>/staff/dentist-schedules</code> - Dentist Schedules</small>
                    <small class="d-block mb-1"><span class="badge bg-warning">PATCH</span> <code>/staff/dentists/{id}/status</code> - Toggle Status</small>
                    <small class="d-block mb-1"><span class="badge bg-danger">DELETE</span> <code>/staff/dentists/{id}</code> - Delete Dentist</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: all 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(9, 30, 62, 0.15) !important;
    }
</style>
@endsection
