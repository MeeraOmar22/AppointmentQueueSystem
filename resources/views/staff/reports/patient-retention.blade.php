@extends('layouts.staff')

@section('title', 'Patient Retention Analytics')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">
                    <i class="fas fa-users-shield me-2"></i>Patient Retention & At-Risk Analysis
                </h1>
                <a href="{{ route('staff.reports.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Total Patients</h6>
                    <h2 class="mb-0">{{ $totalPatients }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">At-Risk Patients</h6>
                    <h2 class="mb-0">{{ $atRiskCount }}</h2>
                    <small>{{ $riskPercentage }}% of total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Loyal Patients</h6>
                    <h2 class="mb-0">{{ $loyalCount }}</h2>
                    <small>{{ 100 - $riskPercentage }}% of total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="card-title text-white-50">Retention Rate</h6>
                    <h2 class="mb-0">{{ 100 - $riskPercentage }}%</h2>
                    <small>Healthy patient base</small>
                </div>
            </div>
        </div>
    </div>

    <!-- At-Risk Patients Section -->
    @if($atRiskPatients->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>At-Risk Patients ({{ $atRiskCount }})
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Contact</th>
                                    <th>Last Visit</th>
                                    <th>Days Since Visit</th>
                                    <th>Total Visits</th>
                                    <th>Completion Rate</th>
                                    <th>Cancel Rate</th>
                                    <th>Risk Factors</th>
                                    <th>Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($atRiskPatients as $patient)
                                <tr>
                                    <td>
                                        <strong>{{ $patient['name'] }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $patient['phone'] }}</small><br>
                                        <small class="text-muted">{{ $patient['email'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $patient['lastVisit'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $patient['daysSinceLastVisit'] > 180 ? 'danger' : 'warning' }}">
                                            {{ $patient['daysSinceLastVisit'] }} days
                                        </span>
                                    </td>
                                    <td>{{ $patient['totalAppointments'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $patient['completionRate'] >= 60 ? 'success' : 'danger' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $patient['completionRate'] }}%;"
                                                 aria-valuenow="{{ $patient['completionRate'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $patient['completionRate'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $patient['cancelRate'] > 30 ? 'danger' : 'warning' }}">
                                            {{ $patient['cancelRate'] }}%
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 200px; font-size: 0.85rem;">
                                            @foreach($patient['riskFactors'] as $factor)
                                                <div class="text-muted">• {{ $factor }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $patient['riskLevel'] === 'High' ? 'danger' : 'warning' }}">
                                            {{ $patient['riskLevel'] }} Risk
                                            <br>
                                            <small>(Score: {{ $patient['riskScore'] }}/100)</small>
                                        </span>
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
    @else
    <div class="alert alert-success" role="alert">
        <i class="fas fa-check-circle me-2"></i>Great news! No at-risk patients detected. All patients show healthy engagement patterns.
    </div>
    @endif

    <!-- Loyal Patients Section -->
    @if($loyalCount > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>Loyal Patients ({{ $loyalCount }})
                    </h5>
                    <small>Stable, consistent appointment patterns with low cancellation rates</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Contact</th>
                                    <th>Last Visit</th>
                                    <th>Total Visits</th>
                                    <th>Completion Rate</th>
                                    <th>Cancel Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loyalPatients as $patient)
                                <tr>
                                    <td>
                                        <strong>{{ $patient['name'] }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $patient['phone'] }}</small><br>
                                        <small class="text-muted">{{ $patient['email'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $patient['lastVisit'] }}</td>
                                    <td>{{ $patient['totalAppointments'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar" 
                                                 style="width: {{ $patient['completionRate'] }}%;"
                                                 aria-valuenow="{{ $patient['completionRate'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $patient['completionRate'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $patient['cancelRate'] }}%</span>
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

    <!-- Risk Assessment Methodology -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Risk Assessment Methodology
                    </h5>
                </div>
                <div class="card-body">
                    <p>Patients are flagged as at-risk based on the following metrics:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Visit Frequency:</strong> No appointments for more than 90 days is considered a warning sign. Beyond 180 days is high risk.
                        </li>
                        <li class="list-group-item">
                            <strong>Cancellation Rate:</strong> More than 30% of appointments cancelled/no-show indicates potential disengagement.
                        </li>
                        <li class="list-group-item">
                            <strong>Frequency Decline:</strong> Appointment frequency decreasing over time suggests declining interest.
                        </li>
                        <li class="list-group-item">
                            <strong>Completion Rate:</strong> Low completion rates (<60%) may indicate scheduling issues or patient dissatisfaction.
                        </li>
                    </ul>
                    <p class="mt-3 text-muted"><small><strong>Risk Score:</strong> Calculated from 0-100 based on weighted factors. Score ≥ 50 = High Risk, ≥ 25 = Medium Risk, < 25 = Low Risk</small></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
