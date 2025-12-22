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
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Appointments</p>
                        <h4 class="text-primary fw-bold mb-0" data-stat="total">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Queued</p>
                        <h4 class="text-info fw-bold mb-0" data-stat="queued">{{ $stats['queued'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-muted small mb-1">In Treatment</p>
                        <h4 class="text-warning fw-bold mb-0" data-stat="in_service">{{ $stats['in_service'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Completed</p>
                        <h4 class="text-success fw-bold mb-0" data-stat="completed">{{ $stats['completed'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
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

<div class="tab-content" id="apptTabsContent">
    <div class="tab-pane fade show active" id="today" role="tabpanel">
        <div class="card table-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Today’s Queue</h5>
                    <span class="text-muted small"><i class="bi bi-arrow-repeat me-1"></i>Live updates every 5 seconds</span>
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
                                    $queueNumber = $queue?->queue_number ?? '—';
                                    $queueStatus = $queue?->queue_status ?? 'not-queued';
                                    $eta = $queue && isset($waitingTimeMap[$appointment->id]) ? $waitingTimeMap[$appointment->id] : '—';

                                    $badgeClass = match($queueStatus) {
                                        'waiting' => 'badge-soft badge-waiting',
                                        'in_service' => 'badge-soft badge-inservice',
                                        'completed' => 'badge-soft badge-completed',
                                        default => 'badge bg-light text-muted'
                                    };

                                    $badgeText = match($queueStatus) {
                                        'waiting' => 'Waiting',
                                        'in_service' => 'In Treatment',
                                        'completed' => 'Done',
                                        default => 'Not Queued'
                                    };
                                @endphp
                                <tr>
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
                                    <td>
                                        <div class="small text-muted">{{ $appointment->appointment_date }}</div>
                                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                                    </td>
                                    <td class="fw-bold">{{ $queueNumber }}</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
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
                                            <form method="POST" action="/staff/checkin/{{ $appointment->id }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-primary">Check In</button>
                                            </form>
                                        @else
                                            <div class="btn-group" role="group">
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}/status" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="waiting">
                                                    <button class="btn btn-sm btn-ghost" {{ $queueStatus === 'waiting' || $queueStatus === 'completed' ? 'disabled' : '' }}>Waiting</button>
                                                </form>
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}/status" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="in_service">
                                                    <button class="btn btn-sm btn-ghost" {{ $queueStatus === 'in_service' || $queueStatus === 'completed' ? 'disabled' : '' }}>Start</button>
                                                </form>
                                                <form method="POST" action="/staff/queue/{{ $queue->id }}/status" class="d-inline appointment-done-form">
                                                    @csrf
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="button" class="btn btn-sm btn-success done-btn" data-appointment-id="{{ $appointment->id }}" {{ $queueStatus === 'completed' ? 'disabled' : '' }}>Done</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $digits = preg_replace('/[^0-9]/', '', $appointment->patient_phone ?? '');
                                            if (str_starts_with($digits, '0')) {
                                                $waNumber = '60' . substr($digits, 1);
                                            } elseif (str_starts_with($digits, '60')) {
                                                $waNumber = $digits;
                                            } else {
                                                $waNumber = $digits;
                                            }
                                            $msg = "🦷 Klinik Pergigian Helmy - Appointment Reminder\n\n" .
                                                "Hi {$appointment->patient_name},\n\n" .
                                                "Your appointment is today at " . \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') . ".\n\n" .
                                                "📋 Visit Code: {$appointment->visit_code}\n\n" .
                                                "Please use these links when you arrive:\n" .
                                                "✅ Check In: " . url('/checkin') . "\n" .
                                                "📍 Track Visit: " . url('/track/' . $appointment->visit_code) . "\n\n" .
                                                "See you soon! 😊";
                                            $encodedMsg = rawurlencode($msg);
                                            $waLink = 'https://api.whatsapp.com/send?phone=' . $waNumber . '&text=' . $encodedMsg;
                                        @endphp
                                        <a href="{{ $waLink }}" class="btn btn-sm btn-success" target="_blank" rel="noopener" title="Send reminder via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
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
                                        @php
                                            $digits = preg_replace('/[^0-9]/', '', $appointment->patient_phone ?? '');
                                            if (str_starts_with($digits, '0')) {
                                                $waNumber = '60' . substr($digits, 1);
                                            } elseif (str_starts_with($digits, '60')) {
                                                $waNumber = $digits;
                                            } else {
                                                $waNumber = $digits;
                                            }
                                            $msg = "🦷 Klinik Pergigian Helmy - Appointment Confirmation\n\n" .
                                                "Hi {$appointment->patient_name},\n\n" .
                                                "This is a confirmation of your upcoming appointment:\n\n" .
                                                "📅 Date: " . \Carbon\Carbon::parse($appointment->appointment_date)->format('l, d M Y') . "\n" .
                                                "🕐 Time: " . \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') . "\n" .
                                                "👨‍⚕️ Dentist: " . ($appointment->dentist->name ?? 'To be assigned') . "\n" .
                                                "🦷 Service: " . ($appointment->service->name ?? 'N/A') . "\n\n" .
                                                "📋 Visit Code: {$appointment->visit_code}\n\n" .
                                                "When you arrive at the clinic:\n" .
                                                "✅ Check In: " . url('/checkin') . "\n" .
                                                "📍 Track Visit: " . url('/track/' . $appointment->visit_code) . "\n\n" .
                                                "Please arrive 10-15 minutes early.\n\n" .
                                                "We look forward to seeing you! 😊\n\n" .
                                                "Klinik Pergigian Helmy\n" .
                                                "📞 06-677 1940";
                                            $encodedMsg = rawurlencode($msg);
                                            $waLink = 'https://api.whatsapp.com/send?phone=' . $waNumber . '&text=' . $encodedMsg;
                                        @endphp
                                        <a href="{{ $waLink }}" class="btn btn-sm btn-success" target="_blank" rel="noopener" title="Send reminder via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
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
                                        @php
                                            $digits = preg_replace('/[^0-9]/', '', $appointment->patient_phone ?? '');
                                            if (str_starts_with($digits, '0')) {
                                                $waNumber = '60' . substr($digits, 1);
                                            } elseif (str_starts_with($digits, '60')) {
                                                $waNumber = $digits;
                                            } else {
                                                $waNumber = $digits;
                                            }
                                            $msg = "🦷 Klinik Pergigian Helmy - We'd Love Your Feedback!\n\n" .
                                                "Hi {$appointment->patient_name},\n\n" .
                                                "Thank you for choosing us for your dental care on " . \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') . ".\n\n" .
                                                "We hope you had a great experience! 😊\n\n" .
                                                "Your feedback helps us serve you better. Please take a moment to share your thoughts:\n\n" .
                                                "📝 Feedback Form: " . url('/feedback?code=' . $appointment->visit_code) . "\n\n" .
                                                "📋 Visit Code: {$appointment->visit_code}\n" .
                                                "📍 View Visit Details: " . url('/track/' . $appointment->visit_code) . "\n\n" .
                                                "We appreciate your time and look forward to seeing you again!\n\n" .
                                                "Best regards,\n" .
                                                "Klinik Pergigian Helmy Team\n" .
                                                "📞 06-677 1940";
                                            $encodedMsg = rawurlencode($msg);
                                            $waLink = 'https://api.whatsapp.com/send?phone=' . $waNumber . '&text=' . $encodedMsg;
                                        @endphp
                                        <a href="{{ $waLink }}" class="btn btn-sm btn-success" target="_blank" rel="noopener" title="Send follow-up via WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    </td>
                                    <td class="text-end">
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

    // Real-time updates - auto refresh every 5 seconds
    function startAutoRefresh() {
        setInterval(function() {
            location.reload();
        }, 5000);
    }
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
</style>
@endsection