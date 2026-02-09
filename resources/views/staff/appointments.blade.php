@extends('layouts.staff')

@section('title', 'Appointments & Queue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Appointments & Queue</h3>
        <p class="text-muted mb-0">Today: {{ $today->format('l, d M Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/staff/calendar" class="btn btn-outline-primary">
            <i class="bi bi-calendar3 me-2"></i>Calendar View
        </a>
        <a href="/staff/appointments/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Appointment
        </a>
    </div>
</div>

<div class="row g-3 mb-4" id="statsContainer">
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('staff.appointments.total') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                 onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Total Appointments</p>
                            <h4 class="text-primary fw-bold mb-0" data-stat="total">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('staff.appointments.queued') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                 onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-hourglass-split text-info" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Queued</p>
                            <h4 class="text-info fw-bold mb-0" data-stat="queued">{{ $stats['queued'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('staff.appointments.in-treatment') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                 onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-heart-pulse text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">In Treatment</p>
                            <h4 class="text-warning fw-bold mb-0" data-stat="in_service">{{ $stats['in_service'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('staff.appointments.completed') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                 onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                 onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted small mb-1">Completed</p>
                            <h4 class="text-success fw-bold mb-0" data-stat="completed">{{ $stats['completed'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<ul class="nav nav-pills mb-3" id="apptTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="today-tab" data-bs-toggle="pill" data-bs-target="#today" type="button" role="tab">Today</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="upcoming-tab" data-bs-toggle="pill" data-bs-target="#upcoming" type="button" role="tab">Upcoming</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="past-tab" data-bs-toggle="pill" data-bs-target="#past" type="button" role="tab">Past</button>
    </li>
</ul>

<!-- Filter Section -->
<div class="card mb-3 bg-light border-0">
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold mb-2">
                    <i class="bi bi-funnel me-2"></i>Filter by Status
                </label>
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach($availableStatuses as $status => $label)
                        <option value="{{ $status }}" {{ $statusFilter === $status ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold mb-2">
                    <i class="bi bi-calendar me-2"></i>Date Range
                </label>
                <select id="dateFilter" class="form-select form-select-sm">
                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="upcoming" {{ $dateFilter === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="past" {{ $dateFilter === 'past' ? 'selected' : '' }}>Past</option>
                </select>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button id="applyFilterBtn" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Apply Filter
                    </button>
                    <a href="/staff/appointments" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i>Reset
                    </a>
                </div>
            </div>
        </div>
        @if($statusFilter)
            <div class="mt-2">
                <span class="badge bg-primary">
                    Active Filter: {{ $availableStatuses[$statusFilter] ?? $statusFilter }}
                    <a href="/staff/appointments" class="text-white ms-2" style="cursor: pointer;">✕</a>
                </span>
            </div>
        @endif
    </div>
</div>

<div class="tab-content" id="apptTabsContent">
    <div class="tab-pane fade show active" id="today" role="tabpanel">
        <div class="card table-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Today’s Queue</h5>
                    <span class="text-muted small"><i class="bi bi-arrow-repeat me-1"></i>Live updates every 3 seconds</span>
                </div>

                <div class="table-responsive" id="appointmentsTableContainer">
                    <table class="table align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px; font-size: 0.8rem;">Patient</th>
                                <th style="width: 80px; font-size: 0.8rem;">Code</th>
                                <th style="width: 100px; font-size: 0.8rem;">Service</th>
                                <th style="width: 100px; font-size: 0.8rem;">Dentist</th>
                                <th style="width: 60px; font-size: 0.8rem;">Time</th>
                                <th style="width: 50px; font-size: 0.8rem;">Q#</th>
                                <th style="width: 90px; font-size: 0.8rem;">Status</th>
                                <th style="width: 50px; font-size: 0.8rem;">ETA</th>
                                <th style="width: 180px; font-size: 0.8rem;" class="text-center">Actions</th>
                                <th style="width: 70px; font-size: 0.8rem;" class="text-center">Notify</th>
                                <th style="width: 100px; font-size: 0.8rem;" class="text-end">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appointment)
                                @php
                                    $queue = $appointment->queue;
                                    $queueNumber = $queue?->queue_number ? 'A-' . str_pad($queue->queue_number, 2, '0', STR_PAD_LEFT) : '—';
                                    $queueStatus = $queue?->queue_status ?? 'not-queued';
                                    $eta = $queue && isset($waitingTimeMap[$appointment->id]) ? $waitingTimeMap[$appointment->id] : '—';

                                    $badgeStyles = match($queueStatus) {
                                        'waiting' => 'background-color: #FFF3E0; color: #E65100;',
                                        'called' => 'background-color: #FFEBEE; color: #C62828;',
                                        'in_treatment' => 'background-color: #E0F2F1; color: #004D40;',
                                        'completed' => 'background-color: #E8F5E9; color: #1B5E20;',
                                        default => 'background-color: #f0f0f0; color: #666;'
                                    };

                                    $badgeText = match($queueStatus) {
                                        'waiting' => 'Waiting',
                                        'called' => 'Called',
                                        'in_treatment' => 'In Treatment',
                                        'completed' => 'Done',
                                        default => 'Not Queued'
                                    };
                                @endphp
                                <tr data-appointment-id="{{ $appointment->id }}">
                                    <td class="fw-semibold">
                                        {{ $appointment->patient_name }}
                                        @if($appointment->checked_in_at)
                                            <br>
                                            <small class="text-success">
                                                <i class="bi bi-check-circle"></i> {{ $appointment->checked_in_at->format('H:i') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-white">
                                            {{ $appointment->visit_code ?: 'APT-' . $appointment->id }}
                                        </span>
                                    </td>
                                    <td>{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</td>
                                    <td>
                                        @if($appointment->dentist && $appointment->dentist->deleted_at)
                                            <span class="text-muted">{{ $appointment->dentist->name }}</span>
                                            <br>
                                            <small class="badge bg-danger">No longer in service</small>
                                        @else
                                            {{ $appointment->dentist->name ?? 'Auto-assign' }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ $appointment->appointment_date }}</div>
                                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                                    </td>
                                    <td class="fw-bold">{{ $queueNumber }}</td>
                                    <td>
                                        <span style="display: inline-block; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; {{ $badgeStyles }}">{{ $badgeText }}</span>
                                        @if($appointment->checked_in_at)
                                            <br>
                                            <small class="badge badge-success" title="Patient checked in at {{ $appointment->checked_in_at->format('H:i:s') }}">
                                                <i class="bi bi-check-circle-fill"></i> Auto-checked
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ is_numeric($eta) ? $eta : '—' }}</td>
                                    <td class="text-center">
                                        @if(!$queue)
                                            <form method="POST" action="/staff/checkin/{{ $appointment->id }}" class="d-inline checkin-form">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-primary checkin-btn" data-appointment-id="{{ $appointment->id }}">Check In</button>
                                            </form>
                                        @else
                                            <div class="btn-group" role="group">
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="waiting">
                                                    <button class="btn btn-sm btn-ghost" {{ $queueStatus === 'waiting' || $queueStatus === 'completed' ? 'disabled' : '' }}>Waiting</button>
                                                </form>
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="in_treatment">
                                                    <button class="btn btn-sm btn-ghost" {{ $queueStatus === 'in_treatment' || $queueStatus === 'completed' ? 'disabled' : '' }}>Start</button>
                                                </form>
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}" class="d-inline appointment-done-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="button" class="btn btn-sm btn-success done-btn" data-appointment-id="{{ $appointment->id }}" {{ $queueStatus === 'completed' ? 'disabled' : '' }}>Done</button>
                                                </form>
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}" class="d-inline appointment-complete-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="completed">
                                                    <button class="btn btn-sm btn-info" {{ $queueStatus === 'completed' ? 'disabled' : '' }}>Complete</button>
                                                </form>
                                                <form method="POST" action="/staff/appointments/{{ $appointment->id }}/cancel" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" {{ $queueStatus === 'completed' ? 'disabled' : '' }} onclick="return confirm('Cancel this appointment?')">Cancel</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <small class="text-muted">Automated</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="/staff/appointments/{{ $appointment->id }}/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="/staff/appointments/{{ $appointment->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="tab" value="today">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this appointment?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No appointments for today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="upcoming" role="tabpanel">
        <div class="card table-card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Upcoming Appointments</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Visit Code</th>
                                <th>Service</th>
                                <th>Dentist</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th class="text-center">Notify</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingAppointments as $appointment)
                                <tr>
                                    <td class="fw-semibold">{{ $appointment->patient_name }}</td>
                                    <td>
                                        <span class="badge bg-primary text-white">
                                            {{ $appointment->visit_code }}
                                        </span>
                                    </td>
                                    <td>{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</td>
                                    <td>
                                        @if($appointment->dentist && $appointment->dentist->deleted_at)
                                            <span class="text-muted">{{ $appointment->dentist->name }}</span>
                                            <br>
                                            <small class="badge bg-danger">No longer in service</small>
                                        @else
                                            {{ $appointment->dentist->name ?? 'Auto-assign' }}
                                        @endif
                                    </td>
                                    <td>{{ $appointment->appointment_date }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
                                    <td class="text-capitalize">{{ $appointment->status }}</td>
                                    <td class="text-center">
                                        <small class="text-muted">Automated</small>
                                    </td>
                                    <td class="text-end">
                                        <a href="/staff/appointments/{{ $appointment->id }}/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="/staff/appointments/{{ $appointment->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="tab" value="upcoming">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this appointment?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No upcoming appointments</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="past" role="tabpanel">
        <div class="card table-card">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Past Appointments</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Visit Code</th>
                                <th>Service</th>
                                <th>Dentist</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th class="text-center">Notify</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pastAppointments as $appointment)
                                <tr>
                                    <td class="fw-semibold">{{ $appointment->patient_name }}</td>
                                    <td>
                                        <span class="badge bg-primary text-white">
                                            {{ $appointment->visit_code }}
                                        </span>
                                    </td>
                                    <td>{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</td>
                                    <td>
                                        @if($appointment->dentist && $appointment->dentist->deleted_at)
                                            <span class="text-muted">{{ $appointment->dentist->name }}</span>
                                            <br>
                                            <small class="badge bg-danger">No longer in service</small>
                                        @else
                                            {{ $appointment->dentist->name ?? 'Auto-assign' }}
                                        @endif
                                    </td>
                                    <td>{{ $appointment->appointment_date }}</td>
                                    <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
                                    <td class="text-capitalize">{{ $appointment->status }}</td>
                                    <td class="text-center">
                                        <small class="text-muted">Automated</small>
                                    </td>
                                    <td class="text-end">
                                        @if($appointment->status !== 'completed' && $appointment->status !== 'cancelled')
                                        <button class="btn btn-sm btn-success complete-treatment-btn me-2" 
                                                data-appointment-id="{{ $appointment->id }}" 
                                                title="Mark this appointment as complete">
                                            <i class="bi bi-check-lg"></i> Complete
                                        </button>
                                        @endif
                                        <a href="/staff/appointments/{{ $appointment->id }}/edit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form method="POST" action="/staff/appointments/{{ $appointment->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="tab" value="past">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this appointment?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No past appointments</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Activate the correct tab based on query parameter
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab') || 'today';
        
        const tabButton = document.getElementById(tab + '-tab');
        if (tabButton) {
            const pill = new bootstrap.Tab(tabButton);
            pill.show();
        }

        // Handle Check In button clicks
        document.querySelectorAll('.checkin-btn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                const appointmentId = this.getAttribute('data-appointment-id');
                const form = this.closest('.checkin-form');
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        console.log('Check-in successful:', result);
                        // Reload the page to see updated status
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('Error: ' + (result.error || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Check-in error:', error);
                    alert('Error checking in appointment');
                }
            });
        });
        
        // Handle Done button click
        document.querySelectorAll('.done-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const appointmentId = this.dataset.appointmentId;
                const form = this.closest('.appointment-done-form');
                const row = this.closest('tr');
                
                // Disable button to prevent double-click
                this.disabled = true;
                
                // Add fade-out animation
                row.style.transition = 'opacity 0.8s ease-out';
                row.style.opacity = '0';
                
                // After animation completes, submit the form
                setTimeout(() => {
                    // Submit the form via fetch to update without full reload
                    const formData = new FormData(form);
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(() => {
                        // After form submission, switch to past tab
                        const pastTab = new bootstrap.Tab(document.getElementById('past-tab'));
                        pastTab.show();
                        
                        // Wait a moment, then reload to get fresh data
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    })
                    .catch(() => {
                        // If error, reload page
                        location.reload();
                    });
                }, 800);
            });
        });
        
        // Start real-time updates after tab is activated
        startAutoRefresh();
    });

    // Filter functionality
    document.getElementById('applyFilterBtn')?.addEventListener('click', function() {
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFilter = document.getElementById('dateFilter').value;
        
        const params = new URLSearchParams();
        if (statusFilter) {
            params.append('status', statusFilter);
        }
        if (dateFilter && dateFilter !== 'today') {
            params.append('date_filter', dateFilter);
        }
        
        // Navigate with filters
        window.location.href = '/staff/appointments' + (params.toString() ? '?' + params.toString() : '');
    });

    // Allow Enter key to apply filter
    document.getElementById('statusFilter')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilterBtn').click();
        }
    });

    // Real-time updates - auto refresh every 3 seconds using AJAX
    let appointmentsRefreshInterval;

    function startAutoRefresh() {
        appointmentsRefreshInterval = setInterval(function() {
            fetch('/api/staff/appointments/today')
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch');
                    return response.json();
                })
                .then(data => updateStatusBadges(data))
                .catch(error => console.error('Error fetching appointments:', error));
        }, 3000);
    }

    function updateStatusBadges(data) {
        if (!data.appointments) return;

        data.appointments.forEach(apt => {
            const badgeStyleMap = {
                'waiting': 'background-color: #FFF3E0; color: #E65100;',
                'called': 'background-color: #FFEBEE; color: #C62828;',
                'in_treatment': 'background-color: #E0F2F1; color: #004D40;',
                'completed': 'background-color: #E8F5E9; color: #1B5E20;',
                'not-queued': 'background-color: #f0f0f0; color: #666;'
            };

            const badgeTextMap = {
                'waiting': 'Waiting',
                'called': 'Called',
                'in_treatment': 'In Treatment',
                'completed': 'Done',
                'not-queued': 'Not Queued'
            };

            // Find row by appointment ID
            const row = document.querySelector(`tr[data-appointment-id="${apt.id}"]`);
            if (!row) return;

            // Update status badge (7th column)
            const statusCells = row.querySelectorAll('td:nth-child(7)');
            statusCells.forEach(cell => {
                const badgeStyle = badgeStyleMap[apt.queue_status] || 'background-color: #f0f0f0; color: #666;';
                const badgeText = badgeTextMap[apt.queue_status] || 'Not Queued';
                
                cell.innerHTML = `<span style="display: inline-block; padding: 0.35em 0.65em; border-radius: 0.25rem; font-weight: 500; ${badgeStyle}">${badgeText}</span>`;
                if (apt.checked_in_at) {
                    cell.innerHTML += `<br><small class="badge badge-success" title="Checked in at ${apt.checked_in_at}"><i class="bi bi-check-circle-fill"></i> ${apt.checked_in_at}</small>`;
                }
            });

            // Update queue number (6th column)
            const queueCells = row.querySelectorAll('td:nth-child(6)');
            queueCells.forEach(cell => {
                cell.textContent = apt.queue_number;
            });

            // Update Actions buttons (9th column) - if queue was just created
            const actionsCells = row.querySelectorAll('td:nth-child(9)');
            actionsCells.forEach(cell => {
                // Check if this row currently shows "Check In" button (no queue)
                const hasCheckInButton = cell.innerHTML.includes('Check In');
                
                // If we have a queue now but the buttons haven't been updated yet, update them
                if (apt.queue_id && hasCheckInButton) {
                    updateActionButtons(cell, apt);
                }
            });
        });
    }

    function updateActionButtons(cell, apt) {
        const queueStatus = apt.queue_status;
        
        // Build the queue buttons HTML
        let buttonsHTML = `
            <div class="btn-group" role="group">
                <form method="POST" action="/staff/queue/${apt.queue_id}" class="d-inline">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="status" value="waiting">
                    <button class="btn btn-sm btn-ghost" ${queueStatus === 'waiting' || queueStatus === 'completed' ? 'disabled' : ''}>Waiting</button>
                </form>
                <form method="POST" action="/staff/queue/${apt.queue_id}" class="d-inline">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="status" value="in_treatment">
                    <button class="btn btn-sm btn-ghost" ${queueStatus === 'in_treatment' || queueStatus === 'completed' ? 'disabled' : ''}>Start</button>
                </form>
                <form method="POST" action="/staff/queue/${apt.queue_id}" class="d-inline appointment-done-form">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="status" value="completed">
                    <button type="button" class="btn btn-sm btn-success done-btn" data-appointment-id="${apt.id}" ${queueStatus === 'completed' ? 'disabled' : ''}>Done</button>
                </form>
                <form method="POST" action="/staff/queue/${apt.queue_id}" class="d-inline appointment-complete-form">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="status" value="completed">
                    <button class="btn btn-sm btn-info" ${queueStatus === 'completed' ? 'disabled' : ''}>Complete</button>
                </form>
                <form method="POST" action="/staff/appointments/${apt.id}/cancel" class="d-inline">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <button type="submit" class="btn btn-sm btn-outline-danger" ${queueStatus === 'completed' ? 'disabled' : ''} onclick="return confirm('Cancel this appointment?')">Cancel</button>
                </form>
            </div>
        `;
        
        cell.innerHTML = buttonsHTML;
    }

    /**
     * Real-time polling to refresh appointment data every 3 seconds
     * This ensures the staff dashboard updates when patients check in via tracking link
     */
    let pollInterval = null;
    
    function startAppointmentPolling() {
        // Poll every 3 seconds
        pollInterval = setInterval(async () => {
            try {
                const response = await fetch('/api/staff/appointments', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    console.error('Failed to fetch appointments:', response.statusText);
                    return;
                }
                
                const data = await response.json();
                if (data.success && data.data && data.data.appointments) {
                    // Update each appointment row with fresh data
                    data.data.appointments.forEach(apt => {
                        const row = document.querySelector(`tr[data-appointment-id="${apt.id}"]`);
                        if (row) {
                            updateAppointmentRow(row, apt);
                        }
                    });
                    
                    // Update stats
                    updateStatistics(data.data.appointments);
                }
            } catch (error) {
                console.error('Error polling appointments:', error);
                // Don't stop polling on error, just continue trying
            }
        }, 3000);
    }

    function updateStatistics(appointments) {
        const total = appointments.length;
        const queued = appointments.filter(a => ['checked_in', 'waiting'].includes(a.status)).length;
        const inService = appointments.filter(a => a.status === 'in_treatment').length;
        const completed = appointments.filter(a => a.status === 'completed').length;
        
        document.querySelector('[data-stat="total"]').textContent = total;
        document.querySelector('[data-stat="queued"]').textContent = queued;
        document.querySelector('[data-stat="in_service"]').textContent = inService;
        document.querySelector('[data-stat="completed"]').textContent = completed;
    }

    // Start polling when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        startAppointmentPolling();
    });

    // Stop polling when page is unloaded
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
        }
    });

    // Handle complete treatment button for past appointments
    document.querySelectorAll('.complete-treatment-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const appointmentId = this.getAttribute('data-appointment-id');
            const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
            
            if (!confirm('Mark this appointment as complete?')) return;
            
            try {
                const response = await fetch(`/api/staff/appointments/${appointmentId}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                });

                if (!response.ok) {
                    const error = await response.json();
                    alert('Error: ' + (error.message || 'Failed to complete appointment'));
                    return;
                }

                // Success - update the UI without full reload
                if (row) {
                    // Find the status cell (7th column in past tab)
                    const statusCell = row.querySelector('td:nth-child(7)');
                    if (statusCell) {
                        statusCell.textContent = 'completed';
                        statusCell.classList.add('text-capitalize');
                    }
                    
                    // Hide the complete button
                    this.style.display = 'none';
                    
                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'alert alert-success alert-dismissible fade show';
                    successMsg.innerHTML = `
                        <i class="bi bi-check-circle"></i> Appointment marked as complete!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    row.parentElement.insertBefore(successMsg, row);
                }
            } catch (error) {
                console.error('Error completing appointment:', error);
                alert('Error: ' + error.message);
            }
        });
    });

</script>

<style>
    /* Smooth transition for appointment row */
    tr {
        transition: opacity 0.3s ease-in-out;
    }
    
    /* Fade out animation class */
    tr.fade-out {
        opacity: 0;
        transition: opacity 0.8s ease-out;
    }
    
    /* Filter card styling */
    #applyFilterBtn {
        font-size: 0.875rem;
    }
</style>
@endsection