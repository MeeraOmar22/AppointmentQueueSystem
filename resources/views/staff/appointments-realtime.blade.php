@extends('layouts.staff')

@section('title', 'Appointments & Queue')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="bi bi-calendar-check me-2"></i>Appointments & Queue
        </h3>
        <p class="text-muted mb-0">
            Today: <span id="todayDate">Loading...</span>
            <span class="realtime-indicator ms-2">
                <span></span> Live Updates
            </span>
        </p>
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

<!-- Statistics Cards -->
<div class="row g-3 mb-4" id="statsContainer">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">Total Appointments</p>
                        <h4 class="text-primary fw-bold mb-0" data-stat="total">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-calendar-check text-primary" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">Checked In</p>
                        <h4 class="text-success fw-bold mb-0" data-stat="queued">{{ $stats['queued'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-check-circle text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">In Treatment</p>
                        <h4 class="text-warning fw-bold mb-0" data-stat="in_service">{{ $stats['in_service'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-clock text-warning" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">Completed</p>
                        <h4 class="text-success fw-bold mb-0" data-stat="completed">{{ $stats['completed'] ?? 0 }}</h4>
                    </div>
                    <i class="bi bi-check-all text-success" style="font-size: 2rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4" id="apptTabs" role="tablist" style="border-bottom: 2px solid #e9ecef; padding-bottom: 0;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="appointments-tab" data-bs-toggle="pill" 
                data-bs-target="#appointmentsPanel" type="button" role="tab">
            <i class="bi bi-calendar-event me-2"></i>Today
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="upcoming-tab" data-bs-toggle="pill" 
                data-bs-target="#upcomingPanel" type="button" role="tab">
            <i class="bi bi-arrow-right-circle me-2"></i>Upcoming
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="past-tab" data-bs-toggle="pill" 
                data-bs-target="#pastPanel" type="button" role="tab">
            <i class="bi bi-arrow-left-circle me-2"></i>Past
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="queue-overview-tab" data-bs-toggle="pill" 
                data-bs-target="#queuePanel" type="button" role="tab">
            <i class="bi bi-diagram-3 me-2"></i>Queue Overview
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="apptTabsContent">
    <!-- Appointments Tab -->
    <div class="tab-pane fade show active" id="appointmentsPanel" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check me-2"></i>Today's Appointments
                    </h5>
                    <div class="filter-section">
                        <label for="statusFilter" class="form-label small text-white mb-0 me-2">Filter by Status:</label>
                        <select id="statusFilter" class="form-select form-select-sm" style="width: 220px; display: inline-block;">
                            <option value="">All Statuses</option>
                            <option value="booked">booked</option>
                            <option value="waiting">waiting</option>
                            <option value="in_treatment">in_treatment</option>
                            <option value="completed">completed</option>
                            <option value="feedback_sent">feedback_sent</option>
                            <option value="cancelled">cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="appointmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Patient</th>
                            <th style="width: 12%;">Visit Code</th>
                            <th style="width: 12%;">Time</th>
                            <th style="width: 15%;">Service</th>
                            <th style="width: 15%;">Dentist</th>
                            <th style="width: 8%;">Queue #</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 8%;" class="text-center">Notify</th>
                            <th style="width: 5%;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsTableBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Loading appointments...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Appointments Tab -->
    <div class="tab-pane fade" id="upcomingPanel" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-plus me-2"></i>Upcoming Appointments
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 12%;">Patient</th>
                            <th style="width: 10%;">Visit Code</th>
                            <th style="width: 10%;">Date</th>
                            <th style="width: 10%;">Time</th>
                            <th style="width: 13%;">Service</th>
                            <th style="width: 13%;">Dentist</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 8%;" class="text-center">Notify</th>
                            <th style="width: 8%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingAppointments as $appointment)
                            @php
                                $statusBadgeClass = match($appointment->status) {
                                    'booked' => 'bg-primary',
                                    'waiting' => 'bg-info',
                                    'in_treatment' => 'bg-warning text-dark',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    'feedback_sent' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $appointment->patient_name }}</td>
                                <td>
                                    <span class="badge bg-primary text-white">
                                        {{ $appointment->visit_code ?: 'APT-' . $appointment->id }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
                                <td>{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</td>
                                <td>{{ $appointment->dentist->name ?? 'Auto-assign' }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $rawPhone = $appointment->patient_phone ?? '';
                                        $digits = preg_replace('/[^0-9]/', '', $rawPhone);
                                        
                                        if (empty($digits)) {
                                            $waNumber = null;
                                        } elseif (str_starts_with($digits, '0')) {
                                            $waNumber = '60' . substr($digits, 1);
                                        } elseif (str_starts_with($digits, '60')) {
                                            $waNumber = $digits;
                                        } else {
                                            $waNumber = $digits;
                                        }
                                    @endphp
                                    
                                    @if($waNumber)
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-bell"></i> Auto-notifying
                                        </span>
                                    @else
                                        <span class="badge bg-danger" title="No phone number available">
                                            <i class="bi bi-exclamation-circle"></i> No Phone
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="/staff/appointments/{{ $appointment->id }}/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="/staff/appointments/{{ $appointment->id }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this appointment?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No upcoming appointments scheduled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Past Appointments Tab -->
    <div class="tab-pane fade" id="pastPanel" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>Past Appointments
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 12%;">Patient</th>
                            <th style="width: 10%;">Visit Code</th>
                            <th style="width: 10%;">Date</th>
                            <th style="width: 10%;">Time</th>
                            <th style="width: 13%;">Service</th>
                            <th style="width: 13%;">Dentist</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 8%;" class="text-center">Notify</th>
                            <th style="width: 10%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pastAppointments as $appointment)
                            @php
                                $statusBadgeClass = match($appointment->status) {
                                    'booked' => 'bg-primary',
                                    'waiting' => 'bg-info',
                                    'in_treatment' => 'bg-warning text-dark',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-danger',
                                    'feedback_sent' => 'bg-secondary',
                                    default => 'bg-secondary'
                                };
                                // Check if appointment is incomplete
                                $isIncomplete = in_array($appointment->status->value, ['booked', 'waiting', 'in_treatment']);
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $appointment->patient_name }}</td>
                                <td>
                                    <span class="badge bg-primary text-white">
                                        {{ $appointment->visit_code ?: 'APT-' . $appointment->id }}
                                    </span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</td>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
                                <td>{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</td>
                                <td>{{ $appointment->dentist->name ?? 'Auto-assign' }}</td>
                                <td>
                                    <span class="badge {{ $statusBadgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->status->value)) }}
                                    </span>
                                    @if($isIncomplete)
                                        <br>
                                        <small class="text-danger fw-bold">⚠️ Incomplete</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $rawPhone = $appointment->patient_phone ?? '';
                                        $digits = preg_replace('/[^0-9]/', '', $rawPhone);
                                        if (empty($digits)) {
                                            $waNumber = null;
                                        } elseif (str_starts_with($digits, '0')) {
                                            $waNumber = '60' . substr($digits, 1);
                                        } elseif (str_starts_with($digits, '60')) {
                                            $waNumber = $digits;
                                        } else {
                                            $waNumber = $digits;
                                        }
                                    @endphp
                                    
                                    @if($waNumber)
                                        <span class="badge bg-info text-dark">
                                            <i class="bi bi-bell"></i> Auto-notifying
                                        </span>
                                    @else
                                        <span class="badge bg-danger" title="No phone number available">
                                            <i class="bi bi-exclamation-circle"></i> No Phone
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($isIncomplete)
                                        <form method="POST" action="/staff/appointments/{{ $appointment->id }}/complete-treatment" class="d-inline complete-treatment-form" data-appointment-id="{{ $appointment->id }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Mark as Completed" onclick="return confirm('Mark this appointment as completed?')">
                                                <i class="bi bi-check-circle me-1"></i>Complete
                                            </button>
                                        </form>
                                    @else
                                        <a href="/staff/appointments/{{ $appointment->id }}/edit" class="btn btn-sm btn-outline-info" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No past appointments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Queue Overview Tab -->
    <div class="tab-pane fade" id="queuePanel" role="tabpanel">
        <!-- In Treatment Section -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0 fw-bold">
                    <span class="badge bg-danger me-2" id="inTreatmentCount">0</span>
                    In Treatment
                </h5>
            </div>
            <div id="inTreatmentSection" class="row g-3">
                <div class="col-12">
                    <p class="text-muted text-center py-4">No patients in treatment</p>
                </div>
            </div>
        </div>

        <!-- Waiting Section -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0 fw-bold">
                    <span class="badge bg-warning me-2" id="waitingCount">0</span>
                    Waiting
                </h5>
            </div>
            <div id="waitingSection" class="row g-3">
                <div class="col-12">
                    <p class="text-muted text-center py-4">No waiting patients</p>
                </div>
            </div>
        </div>

        <!-- Completed Section -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0 fw-bold">
                    <span class="badge bg-success me-2" id="completedCount">0</span>
                    Completed Today
                </h5>
            </div>
            <div id="completedSection" class="row g-3">
                <div class="col-12">
                    <p class="text-muted text-center py-4">No completed appointments</p>
                </div>
            </div>
        </div>

        <!-- Feedback Sent Section -->
        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0 fw-bold">
                    <span class="badge bg-info me-2" id="feedbackCount">0</span>
                    Feedback Sent
                </h5>
            </div>
            <div id="feedbackSection" class="row g-3">
                <div class="col-12">
                    <p class="text-muted text-center py-4">No feedback sent</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
    <style>
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(6, 163, 218, 0.05);
        }
        
        .realtime-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .realtime-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #28a745;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .nav-pills .nav-link {
            color: #6c757d;
            border-bottom: 3px solid transparent;
            border-radius: 0;
            padding-bottom: 12px;
            margin-bottom: -2px;
        }

        .nav-pills .nav-link:hover {
            color: #495057;
            background-color: transparent;
        }

        .nav-pills .nav-link.active {
            background-color: transparent;
            color: #0d6efd;
            border-bottom-color: #0d6efd;
        }

        .queue-card {
            border-left: 4px solid #0d6efd;
            transition: all 0.3s ease;
        }

        .queue-card.in-treatment {
            border-left-color: #dc3545;
        }

        .queue-card.waiting {
            border-left-color: #ffc107;
        }

        .queue-card.completed {
            border-left-color: #28a745;
        }

        .queue-card.feedback {
            border-left-color: #0dcaf0;
        }

        .queue-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .status-badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.waiting { background-color: #fff3e0; color: #e65100; }
        .status-badge.in-treatment { background-color: #ffebee; color: #c62828; }
        .status-badge.completed { background-color: #e8f5e9; color: #1b5e20; }
        .status-badge.feedback { background-color: #e0f7fa; color: #00838f; }
        .status-badge.checked-in { background-color: #f3e5f5; color: #6a1b9a; }
        .status-badge.booked { background-color: #ede7f6; color: #512da8; }

        .filter-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-section .form-select-sm {
            border-color: #dee2e6;
            font-size: 0.875rem;
        }

        .filter-section .form-select-sm:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        class AppointmentsDashboard {
            constructor() {
                this.init();
            }

            init() {
                console.log('[Appointments Dashboard] Initializing...');
                this.loadData();
                // Refresh every 2 seconds for real-time responsiveness when patients check in via tracking link
                // This ensures staff dashboard updates immediately when public check-in happens
                setInterval(() => this.loadData(), 2000);
                
                // Setup filter event listener
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) {
                    statusFilter.addEventListener('change', (e) => {
                        this.filterAppointmentsByStatus(e.target.value);
                    });
                }
            }

            async loadData() {
                try {
                    const response = await fetch('/api/staff/appointments/today', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to fetch appointments');
                    }

                    const data = await response.json();
                    const appointments = data.data?.appointments || [];
                    
                    this.renderAppointmentsTable(appointments);
                    this.renderQueueOverview(appointments);
                    this.updateStats(appointments);
                    
                } catch (error) {
                    console.error('[Appointments Dashboard] Error:', error);
                }
            }

            renderAppointmentsTable(appointments) {
                const tbody = document.getElementById('appointmentsTableBody');
                if (!tbody) return;

                if (appointments.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No appointments today</td></tr>';
                    return;
                }

                tbody.innerHTML = appointments.map(apt => {
                    const patientName = apt.patientName || apt.patient_name || 'Unknown';
                    const visitCode = apt.visitCode || apt.visit_code || '—';
                    const appointmentTime = apt.appointmentTime || apt.appointment_time || '-';
                    const serviceName = apt.service || apt.service_name || '-';
                    const dentistName = apt.dentist || apt.dentist_name || 'Unassigned';
                    const queueNumber = apt.queueNumber || '—';
                    const status = apt.status || 'booked';
                    const aptId = apt.id;
                    
                    // Only show Check In button if appointment is booked
                    let actionButton = '';
                    if (status === 'booked') {
                        actionButton = `<button class="btn btn-sm btn-primary checkin-btn" data-appointment-id="${aptId}" title="Check in patient"><i class="bi bi-check-circle"></i></button>`;
                    }
                    
                    return `
                        <tr data-appointment-id="${aptId}" data-status="${status}">
                            <td class="fw-semibold">${patientName}</td>
                            <td><code class="text-primary">${visitCode}</code></td>
                            <td>${appointmentTime}</td>
                            <td>${serviceName}</td>
                            <td>${dentistName}</td>
                            <td>${queueNumber}</td>
                            <td><span class="status-badge ${status.toLowerCase().replace('_', '-')}">${status}</span></td>
                            <td class="text-center">${actionButton}</td>
                        </tr>
                    `;
                }).join('');

                // Attach Check In button handlers
                document.querySelectorAll('.checkin-btn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.preventDefault();
                        const aptId = btn.getAttribute('data-appointment-id');
                        await this.handleCheckIn(aptId);
                    });
                });

                // Reapply filter after rendering
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter && statusFilter.value) {
                    this.filterAppointmentsByStatus(statusFilter.value);
                }
            }

            renderQueueOverview(appointments) {
                // Group appointments by status
                const inTreatment = appointments.filter(a => a.status === 'in_treatment');
                const waiting = appointments.filter(a => a.status === 'waiting');
                // FIXED: Include feedback_sent and feedback_scheduled as "completed"
                // because status transitions immediately after treatment_ended_at is set
                // Completed = all items that finished treatment (any final status)
                const completed = appointments.filter(a => ['completed', 'feedback_scheduled', 'feedback_sent'].includes(a.status));
                // Feedback = only those that already sent feedback
                const feedback = appointments.filter(a => a.status === 'feedback_sent');

                this.renderQueueSection('inTreatmentSection', inTreatment, 'in-treatment');
                this.renderQueueSection('waitingSection', waiting, 'waiting');
                this.renderQueueSection('completedSection', completed, 'completed');
                this.renderQueueSection('feedbackSection', feedback, 'feedback');

                // Update counts
                document.getElementById('inTreatmentCount').textContent = inTreatment.length;
                document.getElementById('waitingCount').textContent = waiting.length;
                document.getElementById('completedCount').textContent = completed.length;
                document.getElementById('feedbackCount').textContent = feedback.length;
            }

            renderQueueSection(sectionId, appointments, statusType) {
                const section = document.getElementById(sectionId);
                if (!section) return;

                if (appointments.length === 0) {
                    section.innerHTML = `<div class="col-12"><p class="text-muted text-center py-4">No patients</p></div>`;
                    return;
                }

                section.innerHTML = appointments.map(apt => {
                    const patientName = apt.patientName || apt.patient_name || 'Unknown';
                    const queueNumber = apt.queueNumber || '—';
                    const serviceName = apt.service || apt.service_name || '-';
                    const dentistName = apt.dentist || apt.dentist_name || 'Unassigned';
                    const roomNumber = apt.room || apt.roomNumber || '—';

                    if (statusType === 'in-treatment') {
                        return `
                            <div class="col-md-6 col-lg-4">
                                <div class="card queue-card in-treatment shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="card-title mb-0">🚪 ${roomNumber}</h6>
                                            <span class="badge bg-danger">In Treatment</span>
                                        </div>
                                        <p class="card-text fw-semibold mb-2">${patientName}</p>
                                        <small class="text-muted d-block mb-1">Service: ${serviceName}</small>
                                        <small class="text-muted d-block">Dentist: ${dentistName}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (statusType === 'waiting') {
                        return `
                            <div class="col-md-6 col-lg-4">
                                <div class="card queue-card waiting shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="card-title mb-0">
                                                <i class="bi bi-hash me-1"></i>${queueNumber}
                                            </h6>
                                            <span class="badge bg-warning">Waiting</span>
                                        </div>
                                        <p class="card-text fw-semibold mb-2">${patientName}</p>
                                        <small class="text-muted d-block mb-1">Service: ${serviceName}</small>
                                        <small class="text-muted d-block">Dentist: ${dentistName}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (statusType === 'completed') {
                        return `
                            <div class="col-md-6 col-lg-4">
                                <div class="card queue-card completed shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="card-title mb-0">✓ Completed</h6>
                                        </div>
                                        <p class="card-text fw-semibold mb-2">${patientName}</p>
                                        <small class="text-muted d-block mb-1">Service: ${serviceName}</small>
                                        <small class="text-muted d-block">Dentist: ${dentistName}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (statusType === 'feedback') {
                        return `
                            <div class="col-md-6 col-lg-4">
                                <div class="card queue-card feedback shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="card-title mb-0">📝 Feedback</h6>
                                        </div>
                                        <p class="card-text fw-semibold mb-2">${patientName}</p>
                                        <small class="text-muted d-block mb-1">Service: ${serviceName}</small>
                                        <small class="text-muted d-block">Dentist: ${dentistName}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }).join('');
            }

            updateStats(appointments) {
                const total = appointments.length;
                const queued = appointments.filter(a => a.status !== 'booked' && a.status !== 'confirmed').length;
                const inService = appointments.filter(a => a.status === 'in_treatment').length;
                const completed = appointments.filter(a => a.status === 'completed' || a.status === 'feedback_sent' || a.status === 'feedback_scheduled').length;

                document.querySelector('[data-stat="total"]').textContent = total;
                document.querySelector('[data-stat="queued"]').textContent = queued;
                document.querySelector('[data-stat="in_service"]').textContent = inService;
                document.querySelector('[data-stat="completed"]').textContent = completed;
            }

            filterAppointmentsByStatus(selectedStatus) {
                console.log('[Appointments Dashboard] Filtering by status:', selectedStatus);
                
                const tbody = document.getElementById('appointmentsTableBody');
                if (!tbody) return;

                const rows = tbody.querySelectorAll('tr[data-status]');
                
                // If no rows exist (empty table message), skip filtering
                if (rows.length === 0) return;

                let visibleCount = 0;

                rows.forEach(row => {
                    const rowStatus = row.getAttribute('data-status');
                    
                    if (selectedStatus === '' || rowStatus === selectedStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show "No appointments" message if all rows are hidden
                if (visibleCount === 0 && rows.length > 0) {
                    // Add a message row
                    const tbody = document.getElementById('appointmentsTableBody');
                    const existingMessage = tbody.querySelector('tr.filter-no-results');
                    if (!existingMessage) {
                        const messageRow = document.createElement('tr');
                        messageRow.className = 'filter-no-results';
                        messageRow.innerHTML = '<td colspan="7" class="text-center text-muted py-4">No appointments match selected filter</td>';
                        tbody.appendChild(messageRow);
                    }
                } else {
                    // Remove the message row if it exists
                    const messageRow = tbody.querySelector('tr.filter-no-results');
                    if (messageRow) {
                        messageRow.remove();
                    }
                }
            }

            async handleCheckIn(appointmentId) {
                try {
                    console.log('[Appointments Dashboard] Checking in appointment:', appointmentId);
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    const token = csrfToken ? csrfToken.getAttribute('content') : '';
                    
                    if (!token) {
                        alert('Error: CSRF token not found');
                        return;
                    }

                    const response = await fetch(`/staff/checkin/${appointmentId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    console.log('[Appointments Dashboard] Check-in response status:', response.status);

                    const responseText = await response.text();
                    let result;

                    try {
                        result = JSON.parse(responseText);
                    } catch (e) {
                        console.error('[Appointments Dashboard] Failed to parse response:', e);
                        alert('Error: Invalid server response');
                        return;
                    }

                    console.log('[Appointments Dashboard] Check-in result:', result);

                    if (result.success) {
                        console.log('[Appointments Dashboard] Check-in successful');
                        // Reload data to show queue number and updated status
                        await this.loadData();
                        alert(`✓ ${result.patient_name || 'Patient'} checked in successfully`);
                    } else {
                        const errorMsg = result.error || 'Check-in failed';
                        console.error('[Appointments Dashboard] Check-in error:', errorMsg);
                        alert(`Error: ${errorMsg}`);
                    }
                } catch (error) {
                    console.error('[Appointments Dashboard] Check-in exception:', error);
                    alert(`Error: ${error.message}`);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            window.dashboard = new AppointmentsDashboard();
        });
    </script>

    <script>
        function displayTodayDate() {
            const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
            const today = new Date().toLocaleDateString('en-US', options);
            const todayElement = document.getElementById('todayDate');
            if (todayElement) {
                todayElement.textContent = today;
            }
        }
        
        displayTodayDate();

        // Preserve active tab on page reload
        document.addEventListener('DOMContentLoaded', function() {
            // Small delay to ensure Bootstrap is initialized
            setTimeout(() => {
                const savedTab = localStorage.getItem('appointmentActiveTab');
                console.log('Saved tab:', savedTab);
                
                if (savedTab) {
                    const tabButton = document.getElementById(savedTab);
                    console.log('Tab button found:', tabButton);
                    
                    if (tabButton) {
                        // Use Bootstrap's Tab API if available
                        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                            const tab = new bootstrap.Tab(tabButton);
                            tab.show();
                            console.log('Tab activated:', savedTab);
                        } else {
                            // Fallback: manually trigger click
                            tabButton.click();
                        }
                    }
                }
            }, 100);
        });

        // Save active tab when clicking tabs
        document.addEventListener('click', function(e) {
            const tabButton = e.target.closest('[role="tab"]');
            if (tabButton && tabButton.id) {
                localStorage.setItem('appointmentActiveTab', tabButton.id);
                console.log('Saved tab on click:', tabButton.id);
            }
        });

        // Handle complete treatment form submission via AJAX
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.classList.contains('complete-treatment-form')) {
                e.preventDefault();
                
                const appointmentId = form.dataset.appointmentId;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
                
                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alert(`✓ ${data.patient_name} treatment marked as completed!`);
                        
                        // Remove the row from the table instead of full reload
                        const row = form.closest('tr');
                        if (row) {
                            row.style.opacity = '0.5';
                            // Remove after animation
                            setTimeout(() => {
                                row.remove();
                                // Check if table is now empty
                                const tbody = row.closest('tbody');
                                if (tbody && tbody.children.length === 0) {
                                    // Table is empty, reload page to show proper empty state
                                    window.location.reload();
                                }
                            }, 300);
                        }
                    } else {
                        alert(`Error: ${data.error || 'Failed to complete treatment'}`);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(`Error: ${error.message}`);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            }
        });
    </script>
@endpush

