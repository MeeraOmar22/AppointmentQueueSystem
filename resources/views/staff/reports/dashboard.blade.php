@extends('layouts.staff')

@section('title', 'Reports Dashboard - Clinic Management')

@section('content')
<style>
    /* Layout & Container */
    .reports-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 100vh;
    }

    /* Header Styling */
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

    /* Stat Cards */
    .stat-card {
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        transition: left 0.3s ease;
        z-index: 1;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
    }

    .stat-card:hover::before {
        left: 100%;
    }
    
    .stat-card-content {
        position: relative;
        z-index: 2;
    }
    
    .stat-card.primary {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: white;
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        color: white;
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: #222;
    }
    
    .stat-card h6 {
        font-size: 0.75rem;
        opacity: 0.9;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 600;
    }
    
    .stat-card .number {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin: 0.5rem 0;
        font-family: 'Courier New', monospace;
    }
    
    .stat-card .subtext {
        font-size: 0.85rem;
        opacity: 0.85;
    }

    /* Cards */
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
        transform: translateY(-1px);
    }

    /* Chart Container */
    .chart-container {
        position: relative;
        height: 350px;
        margin-bottom: 1.5rem;
    }
    
    /* Forms & Filters */
    .form-control, .form-select {
        border-radius: 8px;
        border: 1.5px solid #e0e0e0;
        padding: 0.65rem 0.9rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
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
        max-height: 400px;
        overflow-y: auto;
    }

    .table {
        margin-bottom: 0;
        font-size: 0.9rem;
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
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 102, 204, 0.25);
    }

    .btn-success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        border: none;
        font-weight: 600;
        padding: 0.55rem 1.2rem;
        font-size: 0.9rem;
    }

    .btn-success:hover {
        box-shadow: 0 4px 10px rgba(25, 135, 84, 0.25);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        border: none;
        font-weight: 600;
        padding: 0.55rem 1.2rem;
        font-size: 0.9rem;
    }

    .btn-danger:hover {
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.25);
    }

    .btn-outline-primary {
        color: #0066cc;
        border: 1.5px solid #0066cc;
        font-weight: 600;
    }

    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        border-color: #0066cc;
        color: white;
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <div class="container-fluid">
            <h1 class="mb-2"><i class="bi bi-graph-up me-3"></i>Analytics & Reports</h1>
            <p class="mb-0">Real-time clinic performance metrics and detailed analytics</p>
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
                        <a href="{{ route('reports.dashboard') }}" class="btn btn-primary active">
                            <i class="bi bi-house-door me-2"></i>Dashboard
                        </a>
                        <a href="{{ route('reports.appointments') }}" class="btn btn-outline-primary">
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

    <!-- Date Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter & Export</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-check-circle me-2"></i>Apply Filter
                            </button>
                            <a href="{{ route('reports.export') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" class="btn btn-success">
                                <i class="bi bi-download me-2"></i>CSV
                            </a>
                            <a href="{{ route('reports.export-pdf') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" class="btn btn-danger">
                                <i class="bi bi-file-pdf me-2"></i>PDF
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-2 g-2">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card primary">
                <div class="stat-card-content">
                    <h6><i class="bi bi-calendar-check me-2"></i>Total Appointments</h6>
                    <div class="number">{{ $totalAppointments }}</div>
                    <div class="subtext">In selected period</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success">
                <div class="stat-card-content">
                    <h6><i class="bi bi-check-circle me-2"></i>Completed</h6>
                    <div class="number">{{ $completedAppointments }}</div>
                    <div class="subtext">{{ $appointmentCompletionRate }}% success rate</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card danger">
                <div class="stat-card-content">
                    <h6><i class="bi bi-x-circle me-2"></i>Cancelled</h6>
                    <div class="number">{{ $cancelledAppointments }}</div>
                    <div class="subtext">Cancelled appointments</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning">
                <div class="stat-card-content">
                    <h6><i class="bi bi-exclamation-circle me-2"></i>No Shows</h6>
                    <div class="number">{{ $noShowAppointments }}</div>
                    <div class="subtext">No-show appointments</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-2 g-2">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2" style="color: #0066cc;"></i>Revenue by Service
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2" style="color: #0066cc;"></i>Appointment Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue & Feedback Cards -->
    <div class="row mb-2">
        <div class="col-lg-6 mb-2">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2" style="color: #28a745;"></i>Total Revenue
                    </h5>
                </div>
                <div class="card-body">
                    <h2 class="text-success mb-3" style="font-size: 2.5rem; font-weight: 700;">RM {{ number_format($totalRevenue, 2) }}</h2>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th class="text-end">Count</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($revenueData as $revenue)
                                    <tr>
                                        <td>
                                            <span class="fw-600">{{ $revenue->name }}</span>
                                        </td>
                                        <td class="text-end">{{ $revenue->count }}</td>
                                        <td class="text-end fw-600">RM {{ number_format($revenue->total_revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2" style="color: #ffc107;"></i>Patient Feedback Summary
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="display-4 text-warning mb-2" style="font-weight: 700;">{{ number_format($averageRating, 1) }}<small>/5.0</small></div>
                        <p class="text-muted">Average Patient Rating</p>
                    </div>
                    <div class="alert alert-info">
                        <strong>{{ $totalFeedback }}</strong> Total Feedback Submissions
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tables -->
    <div class="row mb-2 g-2">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md me-2" style="color: #0066cc;"></i>Dentist Performance
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Dentist Name</th>
                                    <th class="text-center">Completed Appointments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dentistPerformance as $dentist)
                                    <tr>
                                        <td class="ps-3">
                                            <strong>{{ $dentist->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6">{{ $dentist->appointments_completed }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2" style="color: #28a745;"></i>Popular Services
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Service</th>
                                    <th class="text-center">Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicePopularity as $service)
                                    <tr>
                                        <td class="ps-3">
                                            <strong>{{ $service->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6">{{ $service->count }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach($revenueData as $revenue)
                        '{{ $revenue->name }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Revenue (RM)',
                    data: [
                        @foreach($revenueData as $revenue)
                            {{ $revenue->total_revenue }},
                        @endforeach
                    ],
                    backgroundColor: '#0066cc',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'RM ' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Cancelled', 'No Show'],
                datasets: [{
                    data: [{{ $completedAppointments }}, {{ $cancelledAppointments }}, {{ $noShowAppointments }}],
                    backgroundColor: ['#198754', '#dc3545', '#ffc107'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 13, weight: '500' },
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    });
</script>
</div> <!-- Close .container-fluid -->
</div> <!-- Close .reports-container -->
@endsection
