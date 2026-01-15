@extends('layouts.staff')

@section('title', 'Revenue Report - Clinic Management')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <a href="{{ route('reports.dashboard') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <i class="fas fa-money-bill-wave me-2"></i>Revenue Analysis
            </h1>
            
            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from', $dateFrom) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to', $dateTo) }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                            <a href="{{ route('reports.revenue') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Revenue</h6>
                    <h2 class="text-success mb-0">RM {{ number_format($totalRevenue, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Appointments</h6>
                    <h2 class="text-primary mb-0">{{ $totalAppointments }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Average Per Appointment</h6>
                    <h2 class="text-info mb-0">RM {{ number_format($averagePerAppointment, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Service -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>Revenue Breakdown by Service
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Service Name</th>
                            <th class="text-center">Number of Bookings</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Total Revenue</th>
                            <th class="text-center">% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenueByService as $revenue)
                            <tr>
                                <td>
                                    <strong>{{ $revenue->name }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $revenue->count }}</span>
                                </td>
                                <td class="text-right">RM {{ number_format($revenue->price, 2) }}</td>
                                <td class="text-right">
                                    <strong>RM {{ number_format($revenue->total_revenue, 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">
                                        {{ number_format(($revenue->total_revenue / $totalRevenue) * 100, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No revenue data available for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($revenueByService->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3">Total</th>
                                <th class="text-right"><strong>RM {{ number_format($totalRevenue, 2) }}</strong></th>
                                <th class="text-center"><strong>100%</strong></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Revenue by Dentist -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-user-md me-2"></i>Revenue Contribution by Dentist
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Dentist Name</th>
                            <th class="text-center">Appointments Completed</th>
                            <th class="text-right">Total Revenue</th>
                            <th class="text-center">% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($revenueByDentist as $dentist)
                            <tr>
                                <td>
                                    <strong>{{ $dentist->name }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $dentist->completed_appointments }}</span>
                                </td>
                                <td class="text-right">
                                    <strong>RM {{ number_format($dentist->total_revenue, 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">
                                        {{ number_format(($dentist->total_revenue / $totalRevenue) * 100, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No dentist data available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($revenueByDentist->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-right"><strong>RM {{ number_format($totalRevenue, 2) }}</strong></th>
                                <th class="text-center"><strong>100%</strong></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
