@extends('layouts.app')

@section('title', 'My Appointments - Patient Portal')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <i class="fas fa-calendar-check me-2"></i>My Appointments
            </h1>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-primary mb-1">{{ $totalAppointments }}</h4>
                    <p class="mb-0 text-muted small">Total Appointments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-success mb-1">{{ $completedAppointments }}</h4>
                    <p class="mb-0 text-muted small">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-danger mb-1">{{ $cancelledAppointments }}</h4>
                    <p class="mb-0 text-muted small">Cancelled</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h4 class="text-warning mb-1">{{ $pendingAppointments }}</h4>
                    <p class="mb-0 text-muted small">Upcoming</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Appointment History</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('patient.reports.export-records') }}" class="btn btn-sm btn-light">
                    <i class="fas fa-download me-2"></i>Export CSV
                </a>
                <a href="{{ route('patient.reports.export-pdf') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    You don't have any appointments yet.
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

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('patient.reports.treatments') }}" class="btn btn-outline-primary w-100">
                <i class="fas fa-prescription-bottle me-2"></i>View Treatment History
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('patient.reports.feedback') }}" class="btn btn-outline-info w-100">
                <i class="fas fa-star me-2"></i>View My Feedback
            </a>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
