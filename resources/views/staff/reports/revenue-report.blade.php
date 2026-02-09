@extends('layouts.staff')

@section('title', 'Revenue Report - Clinic Management')

@section('content')
<style>
    /* Layout & Container */
    .reports-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 100vh;
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
        padding: 2rem 0;
        margin: -1.5rem -1rem 1.5rem -1rem;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.15);
        border-radius: 0 0 12px 12px;
    }

    .page-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }

    .page-header p {
        font-size: 1rem;
        opacity: 0.95;
    }

    /* Cards */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    }

    .card-header {
        border-bottom: 1.5px solid #f0f0f0;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 1rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 0.95rem;
    }

    /* Summary Cards */
    .summary-card {
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .summary-card.bg-success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
    }

    .summary-card.bg-primary {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: white;
    }

    .summary-card.bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0ba5d8 100%);
        color: white;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .summary-card h6 {
        font-size: 0.8rem;
        opacity: 0.95;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .summary-card h2 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0;
        font-family: 'Courier New', monospace;
    }

    /* Report Navigation */
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

    .report-nav .btn:hover {
        transform: translateY(-2px);
    }

    /* Forms & Filters */
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 0.65rem 0.9rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #198754;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
    }

    .form-label {
        color: #333;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    /* Table Styling */
    .table-responsive {
        border-radius: 8px;
        max-height: 450px;
        overflow-y: auto;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #f0f0f0 0%, #e8e8e8 100%);
        border: none;
        font-weight: 700;
        color: #333;
        padding: 0.85rem 0.6rem;
        font-size: 0.9rem;
    }

    .table tbody td {
        padding: 0.85rem 0.6rem;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        border: none;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.55rem 1.2rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 102, 204, 0.3);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        border: none;
        font-weight: 600;
        padding: 0.55rem 1.2rem;
        font-size: 0.9rem;
    }

    .btn-danger:hover {
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
    }

    .btn-outline-secondary {
        font-weight: 600;
        border-radius: 8px;
        padding: 0.55rem 1.2rem;
        font-size: 0.9rem;
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <div class="container-fluid">
            <h1 class="mb-2"><i class="bi bi-cash-coin me-3"></i>Revenue Analysis</h1>
            <p class="mb-0">Detailed revenue breakdown by service and dentist</p>
        </div>
    </div>

    <div class="container-fluid py-4">

    <!-- Quick Navigation to Other Reports -->
    <div class="row mb-4">
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
                        <a href="{{ route('reports.appointments') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-check me-2"></i>Appointments
                        </a>
                        <a href="{{ route('reports.queue-analytics') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>Queue Analytics
                        </a>
                        <a href="{{ route('reports.revenue') }}" class="btn btn-primary active">
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
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter & Export</h6>
        </div>
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
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-check-circle me-2"></i>Apply Filter
                    </button>
                    <a href="{{ route('reports.revenue') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                    <a href="{{ route('reports.export-revenue-pdf') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="summary-card bg-success">
                <h6><i class="bi bi-currency-dollar me-2"></i>Total Revenue</h6>
                <h2>RM {{ number_format($totalRevenue, 2) }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card bg-primary">
                <h6><i class="bi bi-calendar-check me-2"></i>Total Appointments</h6>
                <h2>{{ $totalAppointments }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card bg-info">
                <h6><i class="bi bi-calculator me-2"></i>Average Per Appointment</h6>
                <h2>RM {{ number_format($averagePerAppointment, 2) }}</h2>
            </div>
        </div>
    </div>

    <!-- Revenue by Service -->
    <div class="card shadow-sm mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white;">
            <h5 class="mb-0">
                <i class="bi bi-pie-chart me-2"></i>Revenue Breakdown by Service
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><i class="bi bi-gear me-2"></i>Service Name</th>
                            <th class="text-center"><i class="bi bi-calendar-check me-2"></i>Bookings</th>
                            <th class="text-end"><i class="bi bi-cash me-2"></i>Unit Price</th>
                            <th class="text-end"><i class="bi bi-currency-dollar me-2"></i>Total Revenue</th>
                            <th class="text-center"><i class="bi bi-percent me-2"></i>% of Total</th>
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
</div> <!-- Close .container-fluid -->
</div> <!-- Close .reports-container -->
@endsection
