@extends('layouts.staff')

@section('title', 'Appointment Analysis Report - Clinic Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <a href="{{ route('reports.dashboard') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <i class="fas fa-calendar-check me-2"></i>Appointment Analysis
            </h1>
            
            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="no_show" {{ request('status') === 'no_show' ? 'selected' : '' }}>No Show</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dentist</label>
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
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="{{ route('reports.appointments') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Bar -->
    <div class="row mb-4">
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
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Appointment Details</h5>
            <a href="{{ route('reports.export') }}?date_from={{ request('date_from', $dateFrom) }}&date_to={{ request('date_to', $dateTo) }}" class="btn btn-sm btn-light">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
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
                                    @if($appointment->status === 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @elseif($appointment->status === 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @elseif($appointment->status === 'no_show')
                                        <span class="badge bg-warning text-dark">No Show</span>
                                    @else
                                        <span class="badge bg-info">{{ ucfirst($appointment->status) }}</span>
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
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
