@extends('layouts.staff')

@section('title', 'Appointment Analysis Report - Clinic Management')

@section('content')
<style>
    .reports-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: white;
        padding: 2rem 0;
        margin: -1.5rem -1rem 1.5rem -1rem;
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
        border-radius: 0 0 12px 12px;
    }

    .page-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .page-header p {
        font-size: 1rem;
        opacity: 0.95;
    }

    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        margin-bottom: 0;
    }

    .card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        border-bottom: 1.5px solid #f0f0f0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 0.85rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .card-body {
        padding: 1rem;
    }

    .report-nav {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .report-nav .btn {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 0.65rem 0.9rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    .table tbody td {
        padding: 0.85rem 0.6rem;
        font-size: 0.9rem;
    }

    .table thead th {
        padding: 0.85rem 0.6rem;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <div class="container-fluid">
            <h1 class="mb-2"><i class="bi bi-calendar-check me-3"></i>Appointment Analysis</h1>
            <p class="mb-0">Detailed breakdown of all appointments with filtering capabilities</p>
        </div>
    </div>

    <div class="container-fluid" style="padding-top: 1.5rem; padding-bottom: 2rem;">

    <!-- Quick Navigation to Reports -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-bookmark-fill me-2"></i>Quick Access</h6>
                </div>
                <div class="card-body">
                    <div class="report-nav">
                        <a href="{{ route('reports.dashboard') }}" class="btn btn-outline-primary">
                            <i class="bi bi-house-door me-2"></i>Dashboard
                        </a>
                        <a href="{{ route('reports.appointments') }}" class="btn btn-primary active">
                            <i class="bi bi-calendar-check me-2"></i>Appointments
                        </a>
                        <a href="{{ route('reports.queue-analytics') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>Queue Analytics
                        </a>
                        <a href="{{ route('reports.revenue') }}" class="btn btn-outline-primary">
                            <i class="bi bi-cash-coin me-2"></i>Revenue
                        </a>
                        <a href="{{ route('reports.patient-retention') }}" class="btn btn-outline-primary">
                            <i class="bi bi-people me-2"></i>Patient Retention
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter & Export</h6>
                </div>
                <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Dentist</label>
                    <select name="dentist_id" class="form-select">
                        <option value="">All Dentists</option>
                        @foreach($dentists as $dentist)
                            <option value="{{ $dentist->id }}" {{ request('dentist_id') == $dentist->id ? 'selected' : '' }}>
                                {{ $dentist->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-2"></i>Apply Filter
                    </button>
                    <a href="{{ route('reports.appointments') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Bar -->
    <div class="row mb-3 g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-primary mb-1">{{ $appointments->total() }}</h4>
                    <p class="mb-0 text-muted small">Total Appointments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-success mb-1">{{ $completedCount }}</h4>
                    <p class="mb-0 text-muted small">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-danger mb-1">{{ $cancelledCount }}</h4>
                    <p class="mb-0 text-muted small">Cancelled</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-warning mb-1">{{ $noShowCount }}</h4>
                    <p class="mb-0 text-muted small">No Shows</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #f0f0f0 0%, #e8e8e8 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: #333; font-weight: 700;">Appointment Details</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('reports.export') }}?date_from={{ request('date_from', $dateFrom) }}&date_to={{ request('date_to', $dateTo) }}" class="btn btn-sm" style="background: #0066cc; color: white; border: none; font-weight: 600; padding: 0.5rem 1rem;">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </a>
                    <a href="{{ route('reports.export-pdf') }}?date_from={{ request('date_from', $dateFrom) }}&date_to={{ request('date_to', $dateTo) }}" class="btn btn-sm" style="background: #dc3545; color: white; border: none; font-weight: 600; padding: 0.5rem 1rem;">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Dentist</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td>
                                    <strong>{{ $appointment->appointment_date->format('d M Y') }}</strong>
                                </td>
                                <td>{{ $appointment->appointment_time }}</td>
                                <td>
                                    {{ $appointment->patient_name ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $appointment->patient_email }}</small>
                                </td>
                                <td>{{ $appointment->service->name ?? 'N/A' }}</td>
                                <td>{{ $appointment->dentist->name ?? 'N/A' }}</td>
                                <td>
                                    @if($appointment->status->value === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($appointment->status->value === 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @elseif($appointment->status->value === 'no_show')
                                        <span class="badge bg-warning text-dark">No Show</span>
                                    @else
                                        <span class="badge bg-info">{{ ucfirst($appointment->status->value) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->notes)
                                        <small>{{ Str::limit($appointment->notes, 30) }}</small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No appointments found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($appointments->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $appointments->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .card-body {
        padding: 1.25rem;
    }

    .card-header {
        padding: 1rem;
        border-bottom: 1.5px solid #f0f0f0;
    }

    .form-control, .form-select {
        padding: 0.65rem 0.9rem;
        font-size: 0.95rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .form-label {
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    .table tbody td {
        padding: 0.85rem 0.6rem;
        font-size: 0.9rem;
    }

    .table thead th {
        padding: 0.85rem 0.6rem;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }
</style>
@endsection
