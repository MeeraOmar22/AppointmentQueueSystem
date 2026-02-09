@extends('layouts.staff')

@section('title', 'Queue Analytics Report - Clinic Management')

@section('content')
<style>
    /* Layout & Container */
    .reports-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 100vh;
    }

    /* Page Header */
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
        padding: 1rem;
    }

    .card-header h5, .card-header h6 {
        margin-bottom: 0;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .card-body {
        padding: 1.5rem;
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
        border-color: #0066cc;
        box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
    }

    .form-label {
        color: #333;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    /* Metrics */
    .metric-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .metric-card h5 {
        color: #333;
        font-weight: 600;
        margin-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 0.75rem;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .small-metric {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #0066cc;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        text-align: center;
    }

    .small-metric .value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0066cc;
        margin-bottom: 0.5rem;
    }

    .small-metric .label {
        font-size: 0.85rem;
        color: #666;
        margin-top: 0.25rem;
    }

    .stat-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .stat-table thead th {
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        padding: 0.85rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
    }

    .stat-table tbody td {
        padding: 0.85rem;
        border: 1px solid #f0f0f0;
        font-size: 0.9rem;
    }

    .stat-table tbody tr:hover {
        background: #fafafa;
    }

    .badge-success {
        background: #d4edda;
        color: #155724;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-warning {
        background: #fff3cd;
        color: #856404;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-danger {
        background: #f8d7da;
        color: #721c24;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .distribution-bar {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        gap: 1rem;
    }

    .distribution-label {
        min-width: 60px;
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
    }

    .distribution-bar-fill {
        flex: 1;
        height: 24px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }

    .distribution-bar-value {
        background: linear-gradient(90deg, #0066cc, #0052a3);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding-right: 0.5rem;
        color: white;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .distribution-count {
        min-width: 40px;
        text-align: right;
        color: #666;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .no-data {
        padding: 2rem;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
        color: #666;
    }

    .no-data i {
        font-size: 2rem;
        color: #ccc;
        display: block;
        margin-bottom: 1rem;
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <div class="container-fluid">
            <h1 class="mb-2"><i class="bi bi-graph-up me-3"></i>Queue Analytics</h1>
            <p class="mb-0">Performance metrics and analysis of queue operations</p>
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
                        <a href="{{ route('reports.appointments') }}" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-check me-2"></i>Appointments
                        </a>
                        <a href="{{ route('reports.queue-analytics') }}" class="btn btn-primary active">
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
                    <a href="{{ route('reports.queue-analytics') }}" class="btn btn-outline-secondary" title="Reset filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                    <a href="{{ route('reports.export-queue-analytics-pdf') }}?date_from={{ $dateFrom }}&date_to={{ $dateTo }}" class="btn btn-danger">
                        <i class="bi bi-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Wait Time Analysis -->
    <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Wait Time Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="metric-grid">
                        <div class="small-metric">
                            <div class="value">{{ $waitTimeAnalysis['average_wait_time'] }} min</div>
                            <div class="label">Average Wait Time</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $waitTimeAnalysis['median_wait_time'] }} min</div>
                            <div class="label">Median Wait Time</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $waitTimeAnalysis['min_wait_time'] }}-{{ $waitTimeAnalysis['max_wait_time'] }} min</div>
                            <div class="label">Range (Min-Max)</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $waitTimeAnalysis['total_appointments'] }}</div>
                            <div class="label">Total Appointments</div>
                        </div>
                    </div>

                    <h6 class="mt-4">Wait Time Distribution</h6>
                    @foreach($waitTimeAnalysis['wait_time_distribution'] as $range => $count)
                        <div class="distribution-bar">
                            <div class="distribution-label">{{ $range }} min</div>
                            <div class="distribution-bar-fill">
                                <div class="distribution-bar-value" style="width: {{ ($count / $waitTimeAnalysis['total_appointments']) * 100 }}%">
                                    @if(($count / $waitTimeAnalysis['total_appointments']) * 100 > 10)
                                        {{ $count }}
                                    @endif
                                </div>
                            </div>
                            <div class="distribution-count">{{ $count }}</div>
                        </div>
                    @endforeach

                    <h6 class="mt-4">By Service</h6>
                    <div class="table-responsive">
                        <table class="stat-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Count</th>
                                    <th>Average Wait</th>
                                    <th>Min</th>
                                    <th>Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($waitTimeAnalysis['appointments_by_service'] as $service)
                                    <tr>
                                        <td>{{ $service['service_name'] }}</td>
                                        <td>{{ $service['count'] }}</td>
                                        <td><strong>{{ $service['avg_wait'] }} min</strong></td>
                                        <td>{{ $service['min_wait'] }} min</td>
                                        <td>{{ $service['max_wait'] }} min</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>

    <!-- Treatment Duration Analysis -->
    <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Treatment Duration Analysis</h5>
                </div>
                <div class="card-body">
                    @if($treatmentAnalysis['total_completed'] > 0)
                        <div class="metric-grid">
                            <div class="small-metric">
                                <div class="value">{{ $treatmentAnalysis['average_actual'] }} min</div>
                                <div class="label">Average Actual Duration</div>
                            </div>
                            <div class="small-metric">
                                <div class="value">{{ $treatmentAnalysis['average_estimated'] }} min</div>
                                <div class="label">Average Estimated Duration</div>
                            </div>
                            <div class="small-metric">
                                <div class="value">{{ $treatmentAnalysis['average_variance'] }} min</div>
                                <div class="label">Average Variance</div>
                                <div style="margin-top: 0.5rem;">
                                    @if($treatmentAnalysis['average_variance'] > 0)
                                        <span class="badge-danger">Underestimated</span>
                                    @else
                                        <span class="badge-warning">Overestimated</span>
                                    @endif
                                </div>
                            </div>
                            <div class="small-metric">
                                <div class="value">{{ $treatmentAnalysis['accuracy_metrics']['accurate_percent'] }}%</div>
                                <div class="label">Accurate Estimates</div>
                            </div>
                        </div>

                        <h6 class="mt-4">Estimation Accuracy</h6>
                        <div class="table-responsive">
                            <table class="stat-table">
                                <thead>
                                    <tr>
                                        <th>Metric</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge-success">Accurate (±5 min)</span></td>
                                        <td>{{ $treatmentAnalysis['accuracy_metrics']['accurate_estimates'] }}</td>
                                        <td><strong>{{ $treatmentAnalysis['accuracy_metrics']['accurate_percent'] }}%</strong></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge-danger">Underestimated (>5 min)</span></td>
                                        <td>{{ $treatmentAnalysis['accuracy_metrics']['underestimated'] }}</td>
                                        <td><strong>{{ $treatmentAnalysis['accuracy_metrics']['underestimated_percent'] }}%</strong></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge-warning">Overestimated (<-5 min)</span></td>
                                        <td>{{ $treatmentAnalysis['accuracy_metrics']['overestimated'] }}</td>
                                        <td><strong>{{ $treatmentAnalysis['accuracy_metrics']['overestimated_percent'] }}%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="mt-4">By Service</h6>
                        <div class="table-responsive">
                            <table class="stat-table">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Count</th>
                                        <th>Estimated Avg</th>
                                        <th>Actual Avg</th>
                                        <th>Variance</th>
                                        <th>Range</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($treatmentAnalysis['durations_by_service'] as $service)
                                        <tr>
                                            <td>{{ $service['service_name'] }}</td>
                                            <td>{{ $service['count'] }}</td>
                                            <td>{{ $service['estimated_avg'] }} min</td>
                                            <td><strong>{{ $service['actual_avg'] }} min</strong></td>
                                            <td>
                                                @if($service['variance_avg'] > 0)
                                                    <span class="badge-danger">+{{ $service['variance_avg'] }} min</span>
                                                @else
                                                    <span class="badge-warning">{{ $service['variance_avg'] }} min</span>
                                                @endif
                                            </td>
                                            <td>{{ $service['min_actual'] }} - {{ $service['max_actual'] }} min</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="no-data">
                            <i class="bi bi-info-circle"></i>
                            <p>No completed appointments with recorded treatment times in this period.</p>
                            <p style="font-size: 0.9rem; color: #999; margin: 0;">Data will appear once appointments are completed.</p>
                        </div>
                    @endif
                </div>
            </div>
    </div>

    <!-- Room Utilization -->
    <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-door-closed me-2"></i>Room Utilization</h5>
                </div>
                <div class="card-body">
                    <div class="metric-grid">
                        <div class="small-metric">
                            <div class="value">{{ $roomUtilization['overall_utilization_percent'] }}%</div>
                            <div class="label">Overall Utilization</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $roomUtilization['total_rooms'] }}</div>
                            <div class="label">Total Active Rooms</div>
                        </div>
                    </div>

                    <h6 class="mt-4">By Room</h6>
                    @foreach($roomUtilization['utilization_by_room'] as $room)
                        <div class="distribution-bar">
                            <div class="distribution-label">Room {{ $room['room_number'] }}</div>
                            <div class="distribution-bar-fill">
                                <div class="distribution-bar-value" style="width: {{ $room['utilization_percent'] }}%">
                                    @if($room['utilization_percent'] > 10)
                                        {{ $room['utilization_percent'] }}%
                                    @endif
                                </div>
                            </div>
                            <div class="distribution-count">{{ $room['appointments_count'] }} appts</div>
                        </div>
                    @endforeach
                </div>
            </div>
    </div>

    <!-- Queue Efficiency -->
    <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i>Queue Efficiency</h5>
                </div>
                <div class="card-body">
                    <div class="metric-grid">
                        <div class="small-metric">
                            <div class="value">{{ $queueEfficiency['on_time_percent'] }}%</div>
                            <div class="label">On-Time Completion</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $queueEfficiency['early_percent'] }}%</div>
                            <div class="label">Early Completion</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $queueEfficiency['late_percent'] }}%</div>
                            <div class="label">Late Completion</div>
                        </div>
                        <div class="small-metric">
                            <div class="value">{{ $queueEfficiency['total_completed'] }}</div>
                            <div class="label">Total Completed</div>
                        </div>
                    </div>

                    <h6 class="mt-4">Completion Status</h6>
                    <div class="table-responsive">
                        <table class="stat-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge-success">On Time</span></td>
                                    <td>{{ $queueEfficiency['on_time_count'] }}</td>
                                    <td><strong>{{ $queueEfficiency['on_time_percent'] }}%</strong></td>
                                </tr>
                                <tr>
                                    <td><span class="badge-success">Early</span></td>
                                    <td>{{ $queueEfficiency['early_completions'] }}</td>
                                    <td><strong>{{ $queueEfficiency['early_percent'] }}%</strong></td>
                                </tr>
                                <tr>
                                    <td><span class="badge-danger">Late</span></td>
                                    <td>{{ $queueEfficiency['late_completions'] }}</td>
                                    <td><strong>{{ $queueEfficiency['late_percent'] }}%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>

    <!-- Peak Hours -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock me-2"></i>Peak Hours Analysis</h5>
        </div>
        <div class="card-body">
            <div class="metric-grid">
                <div class="small-metric">
                    <div class="value">{{ $peakHours['peak_hour'] }}</div>
                    <div class="label">Peak Hour</div>
                </div>
                <div class="small-metric">
                    <div class="value">{{ $peakHours['peak_hour_appointments'] }}</div>
                    <div class="label">Peak Hour Appointments</div>
                </div>
                <div class="small-metric">
                    <div class="value">{{ $peakHours['busiest_day'] }}</div>
                    <div class="label">Busiest Day</div>
                </div>
                <div class="small-metric">
                    <div class="value">{{ $peakHours['busiest_day_appointments'] }}</div>
                    <div class="label">Appointments</div>
                </div>
            </div>

            <h6 class="mt-4">Hourly Distribution</h6>
            @foreach($peakHours['hourly_distribution'] as $hour => $count)
                <div class="distribution-bar">
                    <div class="distribution-label">{{ $hour }}:00</div>
                    <div class="distribution-bar-fill">
                        <div class="distribution-bar-value" style="width: {{ ($count / ($peakHours['peak_hour_appointments'] ?? 1)) * 100 }}%">
                            @if(($count / ($peakHours['peak_hour_appointments'] ?? 1)) * 100 > 10)
                                {{ $count }}
                            @endif
                        </div>
                    </div>
                    <div class="distribution-count">{{ $count }}</div>
                </div>
            @endforeach

            <h6 class="mt-4">Daily Distribution</h6>
            <div class="table-responsive">
                <table class="stat-table">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th class="number">Appointments</th>
                            <th class="number">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($peakHours['daily_distribution'] as $day => $count)
                            <tr>
                                <td>{{ $day }}</td>
                                <td class="number">{{ $count }}</td>
                                <td class="number">{{ $peakHours['total_appointments'] > 0 ? number_format(($count / $peakHours['total_appointments']) * 100, 1) : 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
