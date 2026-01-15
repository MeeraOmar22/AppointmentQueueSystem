@extends('layouts.staff')

@section('title', 'Reports Dashboard - Clinic Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-4"><i class="fas fa-chart-line me-2"></i>Reports & Analytics Dashboard</h1>
            
            <!-- Date Filter -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="{{ route('reports.export') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Total Appointments</h6>
                    <h2 class="mb-0">{{ $totalAppointments }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Completed</h6>
                    <h2 class="mb-0">{{ $completedAppointments }}</h2>
                    <small>Completion Rate: {{ $appointmentCompletionRate }}%</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-danger text-white shadow-sm">
                <div class="card-body">
                    <h6 class="card-title opacity-75">Cancelled</h6>
                    <h2 class="mb-0">{{ $cancelledAppointments }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <h6 class="card-title opacity-75">No Shows</h6>
                    <h2 class="mb-0">{{ $noShowAppointments }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue & Feedback Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Total Revenue</h5>
                </div>
                <div class="card-body">
                    <h2 class="text-success mb-3">RM {{ number_format($totalRevenue, 2) }}</h2>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Count</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueData as $revenue)
                                    <tr>
                                        <td>{{ $revenue->name }}</td>
                                        <td>{{ $revenue->count }}</td>
                                        <td>RM {{ number_format($revenue->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i>Patient Feedback</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <h2 class="text-warning">{{ number_format($averageRating, 1) }} / 5.0</h2>
                        <p class="text-muted mb-0">Average Rating</p>
                    </div>
                    <p class="mb-0">
                        <strong>{{ $totalFeedback }}</strong> Feedback Submissions
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tables -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>Dentist Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Dentist Name</th>
                                    <th class="text-center">Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dentistPerformance as $dentist)
                                    <tr>
                                        <td>{{ $dentist->name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $dentist->appointments_completed }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Popular Services</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th class="text-center">Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicePopularity as $service)
                                    <tr>
                                        <td>{{ $service->name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $service->count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Detailed Reports</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('reports.appointments') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-calendar-check me-2"></i>Appointment Analysis
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('reports.revenue') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-chart-bar me-2"></i>Revenue Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 8px;
    }
    
    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endsection
