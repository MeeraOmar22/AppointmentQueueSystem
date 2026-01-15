@extends('layouts.app')

@section('title', 'My Treatments - Patient Portal')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <a href="{{ route('patient.reports.appointments') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <i class="fas fa-prescription-bottle me-2"></i>My Treatment History
            </h1>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Total Treatments Completed</h6>
                    <h2 class="text-success mb-0">{{ $totalTreatments }}</h2>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Unique Services Received</h6>
                    <h2 class="text-primary mb-0">{{ $uniqueServices }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Treatment Records -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Treatment Records</h5>
            <a href="{{ route('patient.reports.export-records') }}" class="btn btn-sm btn-light">
                <i class="fas fa-download me-2"></i>Export Records
            </a>
        </div>
        <div class="card-body">
            @if($treatments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Service</th>
                                <th>Dentist</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($treatments as $treatment)
                                <tr>
                                    <td>
                                        <strong>{{ $treatment->appointment_date->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $treatment->appointment_date->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        {{ $treatment->service->name ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">
                                            RM {{ number_format($treatment->service->price ?? 0, 2) ?? '-' }}
                                        </small>
                                    </td>
                                    <td>{{ $treatment->dentist->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-success">Completed</span>
                                    </td>
                                    <td>
                                        @if($treatment->notes)
                                            <small>{{ Str::limit($treatment->notes, 40) }}</small>
                                            @if(strlen($treatment->notes) > 40)
                                                <button type="button" class="btn btn-link btn-sm p-0" data-bs-toggle="tooltip" 
                                                        title="{{ $treatment->notes }}">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            @endif
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($treatments->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $treatments->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    You don't have any completed treatments yet.
                </div>
            @endif
        </div>
    </div>

    <!-- Service Summary -->
    @if($treatments->count() > 0)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Services Received Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Service</th>
                                <th class="text-center">Times Received</th>
                                <th class="text-right">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicesSummary as $service)
                                <tr>
                                    <td>{{ $service['name'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $service['count'] }}</span>
                                    </td>
                                    <td class="text-right">
                                        RM {{ number_format($service['total_cost'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Navigation -->
    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('patient.reports.appointments') }}" class="btn btn-outline-primary w-100">
                <i class="fas fa-calendar-check me-2"></i>View Appointments
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
