@extends('layouts.staff')

@section('title', 'Patient Retention Analytics')

@section('content')
<style>
    .reports-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 100vh;
    }

    .page-header {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa8c7 100%);
        color: white;
        padding: 2rem 0;
        margin: -1.5rem -1rem 1.5rem -1rem;
        box-shadow: 0 4px 12px rgba(13, 202, 240, 0.15);
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

    .reports-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        min-height: 100vh;
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

    .chart-container {
        position: relative;
        height: 350px;
        margin-bottom: 1.5rem;
    }
    
    .metric-badge {
        font-size: 2.2rem;
        font-weight: bold;
        display: inline-block;
    }
    
    .risk-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
    
    .risk-high { background-color: #dc3545; }
    .risk-medium { background-color: #ffc107; }
    .risk-low { background-color: #198754; }
    
    .patient-card {
        transition: all 0.3s ease;
        border-left: 4px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    
    .patient-card.risk-high {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }
    
    .patient-card.risk-medium {
        border-left-color: #ffc107;
        background-color: #fffbf0;
    }
    
    .patient-card.risk-low {
        border-left-color: #198754;
        background-color: #f5fff7;
    }
    
    .patient-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-box {
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-box.primary {
        background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
        color: white;
    }

    .stat-box.danger {
        background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
        color: white;
    }

    .stat-box.success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
    }

    .stat-box.info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa8c7 100%);
        color: white;
    }

    .stat-box h6 {
        font-size: 0.8rem;
        opacity: 0.9;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-box .number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin: 0.5rem 0;
    }

    .stat-box .subtext {
        font-size: 0.8rem;
        opacity: 0.85;
    }
</style>

<div class="reports-container">
    <div class="page-header">
        <div class="container-fluid">
            <h1 class="mb-2"><i class="bi bi-people me-3"></i>Patient Retention Analytics</h1>
            <p class="mb-0">Monitor patient engagement and identify at-risk patients for proactive retention</p>
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
                        <a href="{{ route('reports.queue-analytics') }}" class="btn btn-outline-primary">
                            <i class="bi bi-graph-up me-2"></i>Queue Analytics
                        </a>
                        <a href="{{ route('reports.revenue') }}" class="btn btn-outline-primary">
                            <i class="bi bi-cash-coin me-2"></i>Revenue
                        </a>
                        <a href="{{ route('reports.patient-retention') }}" class="btn btn-primary active">
                            <i class="bi bi-people me-2"></i>Patient Retention
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4 g-3">
        <div class="col-lg-3 col-md-6">
            <div class="stat-box primary">
                <h6>Total Patients</h6>
                <div class="number">{{ $totalPatients }}</div>
                <div class="subtext">Active patient base</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-box danger">
                <h6>At-Risk Patients</h6>
                <div class="number">{{ $atRiskCount }}</div>
                <div class="subtext">{{ $riskPercentage }}% of total</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-box success">
                <h6>Loyal Patients</h6>
                <div class="number">{{ $loyalCount }}</div>
                <div class="subtext">{{ 100 - $riskPercentage }}% of total</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-box info">
                <h6>Retention Rate</h6>
                <div class="number">{{ 100 - $riskPercentage }}%</div>
                <div class="subtext">Healthy engagement</div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4 g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2" style="color: #0066cc;"></i>Patient Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="patientStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2" style="color: #0066cc;"></i>Risk Level Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="riskLevelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- At-Risk Patients Section -->
    @if(count($atRiskPatients) > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>At-Risk Patients - {{ $atRiskCount }} Patients
                    </h5>
                    <small>These patients show signs of disengagement and need immediate attention</small>
                </div>
                <div class="card-body p-0">
                    <div class="row">
                        @foreach($atRiskPatients as $patient)
                        <div class="col-lg-6 mb-3 p-3">
                            <div class="patient-card risk-{{ strtolower($patient['riskLevel']) }} card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $patient['name'] }}</h6>
                                            <small class="text-muted">{{ $patient['phone'] }}</small>
                                        </div>
                                        <span class="badge bg-{{ $patient['riskLevel'] === 'High' ? 'danger' : 'warning' }}">
                                            {{ $patient['riskLevel'] }} Risk
                                        </span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="p-2 rounded bg-light">
                                                <small class="text-muted">Last Visit</small>
                                                <div class="fw-bold small">{{ $patient['lastVisit'] }}</div>
                                                <small class="text-danger">{{ $patient['daysSinceLastVisit'] }} days ago</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 rounded bg-light">
                                                <small class="text-muted">Total Visits</small>
                                                <div class="fw-bold small">{{ $patient['totalAppointments'] }}</div>
                                                <small class="text-success">Completed: {{ $patient['completedAppointments'] }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metrics -->
                                    <div class="mb-3">
                                        <small class="d-block text-muted mb-1">Completion Rate: {{ $patient['completionRate'] }}%</small>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $patient['completionRate'] >= 60 ? 'success' : 'danger' }}" 
                                                 style="width: {{ $patient['completionRate'] }}%;"></div>
                                        </div>

                                        <small class="d-block text-muted mb-1">Cancellation Rate: {{ $patient['cancelRate'] }}%</small>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $patient['cancelRate'] > 30 ? 'danger' : 'warning' }}" 
                                                 style="width: {{ $patient['cancelRate'] }}%;"></div>
                                        </div>
                                    </div>

                                    <!-- Risk Factors -->
                                    <div class="alert alert-{{ $patient['riskLevel'] === 'High' ? 'danger' : 'warning' }} py-2 px-3 mb-0">
                                        <small><strong>Risk Factors:</strong></small>
                                        <ul class="mb-0 mt-1 ps-3">
                                            @foreach($patient['riskFactors'] as $factor)
                                                <li class="small">{{ $factor }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Excellent!</strong> No at-risk patients detected. All patients show healthy engagement patterns.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Loyal Patients Section -->
    @if($loyalCount > 0)
    <div class="row mb-5">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>Loyal Patients - {{ $loyalCount }} Patients
                    </h5>
                    <small>These patients demonstrate consistent engagement and satisfaction</small>
                </div>
                <div class="card-body p-0">
                    <div class="row">
                        @foreach($loyalPatients as $patient)
                        <div class="col-lg-6 mb-3 p-3">
                            <div class="patient-card risk-low card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-1">{{ $patient['name'] }}</h6>
                                            <small class="text-muted">{{ $patient['phone'] }}</small>
                                        </div>
                                        <span class="badge bg-success">Loyal</span>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="p-2 rounded bg-light">
                                                <small class="text-muted">Last Visit</small>
                                                <div class="fw-bold small">{{ $patient['lastVisit'] }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 rounded bg-light">
                                                <small class="text-muted">Total Visits</small>
                                                <div class="fw-bold small">{{ $patient['totalAppointments'] }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Metrics -->
                                    <small class="d-block text-muted mb-1">Completion Rate: {{ $patient['completionRate'] }}%</small>
                                    <div class="progress mb-3" style="height: 6px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $patient['completionRate'] }}%;"></div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <small class="text-success"><i class="fas fa-check-circle me-1"></i>Stable engagement</small>
                                        <small class="text-muted">Cancel rate: {{ $patient['cancelRate'] }}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Methodology -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-book me-2" style="color: #0066cc;"></i>How Risk Scoring Works
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="badge bg-danger p-2" style="font-size: 1.2rem;"><i class="fas fa-calendar-times"></i></span>
                                </div>
                                <div>
                                    <strong>Visit Frequency (40 pts)</strong>
                                    <p class="text-muted small mb-0">No appointments for 90+ days = warning sign. 180+ days = high risk.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="badge bg-warning p-2" style="font-size: 1.2rem;"><i class="fas fa-times-circle"></i></span>
                                </div>
                                <div>
                                    <strong>Cancellation Rate (25 pts)</strong>
                                    <p class="text-muted small mb-0">More than 30% cancellations/no-shows indicates disengagement.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="badge bg-danger p-2" style="font-size: 1.2rem;"><i class="fas fa-arrow-trend-down"></i></span>
                                </div>
                                <div>
                                    <strong>Frequency Decline (25 pts)</strong>
                                    <p class="text-muted small mb-0">Appointment frequency declining over time suggests waning interest.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-3">
                                    <span class="badge bg-info p-2" style="font-size: 1.2rem;"><i class="fas fa-chart-line"></i></span>
                                </div>
                                <div>
                                    <strong>Completion Rate (10 pts)</strong>
                                    <p class="text-muted small mb-0">Low completion rates (<60%) may indicate scheduling or satisfaction issues.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="alert alert-info mb-0">
                        <small><strong>Risk Levels:</strong> 
                        <span class="risk-indicator risk-high"></span>High Risk (≥50 points) · 
                        <span class="risk-indicator risk-medium"></span>Medium Risk (25-49 points) · 
                        <span class="risk-indicator risk-low"></span>Low Risk (<25 points)</small>
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
        // Patient Status Chart
        const statusCtx = document.getElementById('patientStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Loyal Patients', 'At-Risk Patients'],
                datasets: [{
                    data: [{{ $loyalCount }}, {{ $atRiskCount }}],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderColor: ['#fff', '#fff'],
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
                            font: { size: 14, weight: '500' },
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Risk Level Chart
        @php
            $highRisk = count(array_filter($atRiskPatients, fn($p) => $p['riskLevel'] === 'High'));
            $mediumRisk = count(array_filter($atRiskPatients, fn($p) => $p['riskLevel'] === 'Medium'));
            $lowRisk = count(array_filter($loyalPatients, fn($p) => $p['riskLevel'] === 'Low'));
        @endphp

        const riskCtx = document.getElementById('riskLevelChart').getContext('2d');
        new Chart(riskCtx, {
            type: 'bar',
            data: {
                labels: ['High Risk', 'Medium Risk', 'Low Risk'],
                datasets: [{
                    label: 'Number of Patients',
                    data: [{{ $highRisk }}, {{ $mediumRisk }}, {{ $lowRisk }}],
                    backgroundColor: ['#dc3545', '#ffc107', '#198754'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: Math.max({{ $highRisk }}, {{ $mediumRisk }}, {{ $lowRisk }}) + 5
                    }
                }
            }
        });
    });
</script>
@endsection
