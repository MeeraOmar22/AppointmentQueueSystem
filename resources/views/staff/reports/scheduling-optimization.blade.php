@extends('layouts.staff')

@section('title', 'Scheduling Optimization Analytics')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-calendar-check me-2"></i>Scheduling Optimization & Duration Analytics
                </h1>
                <a href="{{ route('staff.reports.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <!-- Date Range Filter -->
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Total Completed Appointments</h6>
                    <h2 class="mb-0">{{ $totalAppointments }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Average Duration</h6>
                    <h2 class="mb-0">{{ $durationStats['averageDuration'] }} <small>min</small></h2>
                    <small>Range: {{ $durationStats['minDuration'] }} - {{ $durationStats['maxDuration'] }} min</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <h6 class="card-title">Peak Hour Time Slot</h6>
                    <h2 class="mb-0">{{ $hourlyDistribution[array_search(max(array_column($hourlyDistribution, 'count')), array_column($hourlyDistribution, 'count'))]['hour'] }}</h2>
                    <small>Highest appointment concentration</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Active Dentists</h6>
                    <h2 class="mb-0">{{ $dentistUtilization->count() }}</h2>
                    <small>Tracked in period</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Optimization Recommendations -->
    @if($recommendations->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Optimization Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($recommendations as $rec)
                    <div class="alert alert-{{ $rec['priority'] === 'high' ? 'danger' : 'warning' }} mb-2" role="alert">
                        <strong>{{ $rec['type'] }}</strong>
                        <br>
                        {{ $rec['message'] }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Appointment Duration by Service -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-hourglass-half me-2"></i>Treatment Duration by Service
                    </h5>
                </div>
                <div class="card-body">
                    @if($durationStats['byService']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Appointments</th>
                                    <th>Average Duration</th>
                                    <th>Min Duration</th>
                                    <th>Max Duration</th>
                                    <th>Consistency (Std Dev)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($durationStats['byService'] as $service)
                                <tr>
                                    <td><strong>{{ $service['name'] }}</strong></td>
                                    <td>{{ $service['count'] }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $service['averageDuration'] }} min
                                        </span>
                                    </td>
                                    <td>{{ $service['minDuration'] }} min</td>
                                    <td>{{ $service['maxDuration'] }} min</td>
                                    <td>
                                        <span class="badge bg-{{ $service['variance'] < 5 ? 'success' : 'warning' }}">
                                            {{ $service['variance'] }}
                                        </span>
                                        <small class="text-muted">{{ $service['variance'] < 5 ? 'High consistency' : 'Variable times' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No completed appointments with duration tracking in this period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Duration by Dentist -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md me-2"></i>Treatment Duration by Dentist
                    </h5>
                </div>
                <div class="card-body">
                    @if($durationStats['byDentist']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Dentist</th>
                                    <th>Appointments</th>
                                    <th>Average Duration</th>
                                    <th>Min Duration</th>
                                    <th>Max Duration</th>
                                    <th>Efficiency (Std Dev)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($durationStats['byDentist'] as $dentist)
                                <tr>
                                    <td><strong>{{ $dentist['name'] }}</strong></td>
                                    <td>{{ $dentist['count'] }}</td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{ $dentist['averageDuration'] }} min
                                        </span>
                                    </td>
                                    <td>{{ $dentist['minDuration'] }} min</td>
                                    <td>{{ $dentist['maxDuration'] }} min</td>
                                    <td>
                                        <span class="badge bg-{{ $dentist['variance'] < 8 ? 'success' : 'warning' }}">
                                            {{ $dentist['variance'] }}
                                        </span>
                                        <small class="text-muted">{{ $dentist['variance'] < 8 ? 'Efficient' : 'Variable pace' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No completed appointments with duration tracking in this period.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Distribution -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Hourly Appointment Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Time Slot</th>
                                    <th>Appointments</th>
                                    <th>Percentage</th>
                                    <th>Visual Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hourlyDistribution as $hour)
                                <tr>
                                    <td><strong>{{ $hour['hour'] }}</strong></td>
                                    <td>{{ $hour['count'] }}</td>
                                    <td>{{ $hour['percentage'] }}%</td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-{{ $hour['count'] > 5 ? 'danger' : ($hour['count'] > 2 ? 'warning' : 'success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ max(5, $hour['percentage']) }}%;"
                                                 aria-valuenow="{{ $hour['percentage'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $hour['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Distribution -->
    @if($dailyDistribution->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Daily Distribution (by Day of Week)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Day of Week</th>
                                    <th>Appointments</th>
                                    <th>Visual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyDistribution as $day)
                                <tr>
                                    <td><strong>{{ $day->day }}</strong></td>
                                    <td>{{ $day->count }}</td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-info" 
                                                 role="progressbar" 
                                                 style="width: {{ max(5, ($day->count / $dailyDistribution->max('count')) * 100) }}%;"
                                                 aria-valuenow="{{ $day->count }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="{{ $dailyDistribution->max('count') }}">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Dentist Utilization -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Dentist Utilization & Load Balancing
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Dentist</th>
                                    <th>Completed Appointments</th>
                                    <th>Utilization %</th>
                                    <th>Workload Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dentistUtilization as $dentist)
                                <tr>
                                    <td><strong>{{ $dentist->name }}</strong></td>
                                    <td>{{ $dentist->appointments_count }}</td>
                                    <td>{{ $dentist->utilization_percentage }}%</td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-{{ $dentist->utilization_percentage > 30 ? 'danger' : ($dentist->utilization_percentage > 15 ? 'warning' : 'success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $dentist->utilization_percentage }}%;"
                                                 aria-valuenow="{{ $dentist->utilization_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $dentist->utilization_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Understanding the Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Duration Consistency (Standard Deviation)</h6>
                            <p class="text-muted small">Lower values indicate more consistent treatment times. High consistency helps with better scheduling and staff planning.</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Dentist Utilization</h6>
                            <p class="text-muted small">Shows the percentage of appointments handled by each dentist. Imbalanced load (>20% difference) may indicate need for workload adjustment.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
