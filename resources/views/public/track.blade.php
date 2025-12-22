@extends('layouts.public')

@section('title', 'Track Your Visit')

@section('content')
<div class="container-fluid bg-primary py-3 mb-4">
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="text-white mb-1">Track Your Visit</h2>
            <span class="small text-white-50">Code: {{ $appointment->visit_code }}</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <style>
        .track-card { border-radius: 10px; }
        .track-stat-label { color: #6c757d; font-size: .8rem; }
        .track-stat-value { font-weight: 700; }
        .track-badge { font-size: .9rem; }
        .track-section { border-top: 1px solid #eee; padding-top: 1rem; margin-top: .25rem; }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; background: #0d6efd; animation: pulse 1.2s infinite; margin-right: .4rem; }
        @keyframes pulse { 0% { opacity: .2; } 50% { opacity: 1; } 100% { opacity: .2; } }
    </style>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="bg-light p-4 rounded shadow-sm mb-4 track-card">
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <div class="track-stat-label">Patient</div>
                        <div class="h5 mb-0">{{ $appointment->patient_name }}</div>
                        <div class="text-muted small">{{ $appointment->patient_phone }}</div>
                    </div>
                    <div class="text-end">
                        <div class="track-stat-label">Service</div>
                        <div class="fw-semibold">{{ $appointment->service->name ?? $appointment->service->service_name ?? '—' }}</div>
                        <div class="text-muted small">Dentist: {{ $appointment->dentist->name ?? 'TBD' }}</div>
                    </div>
                </div>
                <div class="row text-center track-section">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="track-stat-label">Queue No</div>
                        <div class="h4 track-stat-value" data-update="queueNumber">{{ $queueNumber ? sprintf('A-%02d', $queueNumber) : '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="track-stat-label">Status</div>
                        @php
                            if (!$appointment->checked_in_at) {
                                $label = 'Not Checked In';
                                $badgeClass = 'bg-secondary';
                            } else {
                                $label = match($queueStatus) {
                                    'in_service' => 'In Treatment',
                                    'completed' => 'Completed',
                                    'waiting' => 'Waiting',
                                    default => 'Not Queued',
                                };
                                $badgeClass = match($queueStatus) {
                                    'in_service' => 'bg-warning',
                                    'completed' => 'bg-success',
                                    'waiting' => 'bg-primary',
                                    default => 'bg-secondary',
                                };
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }} track-badge" data-update="status">{{ $label }}</span>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="track-stat-label">Now Serving</div>
                        <div class="h5" data-update="serving">{{ $currentServing ? sprintf('A-%02d', $currentServing) : '—' }}</div>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="track-stat-label">ETA</div>
                        <div class="h5 text-warning" data-update="eta">{{ $etaMinutes !== null ? $etaMinutes . ' min' : 'TBD' }}</div>
                    </div>
                </div>
                <div class="row text-center mt-2">
                    <div class="col-6">
                        <div class="track-stat-label">Room</div>
                        <div class="h5" data-update="room">{{ $room }}</div>
                    </div>
                    <div class="col-6">
                        <div class="track-stat-label">Appt Time</div>
                        <div class="h6">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center text-muted small mb-2">
                <span class="live-dot"></span>
                <span>Live updating every 5 seconds</span>
                <span class="ms-auto" id="lastUpdated" aria-live="polite"></span>
            </div>
        </div>
    </div>
</div>

<script>
    const visitCode = '{{ $appointment->visit_code }}';
    
    function updateTrackingData() {
        fetch(`/api/track/${visitCode}`)
            .then(response => response.json())
            .then(data => {
                // Update Queue Number
                const queueNumberEl = document.querySelector('[data-update="queueNumber"]');
                if (queueNumberEl) {
                    queueNumberEl.textContent = data.queueNumber ? `A-${String(data.queueNumber).padStart(2, '0')}` : '—';
                }

                // Update Status
                const statusEl = document.querySelector('[data-update="status"]');
                if (statusEl) {
                    let label = 'Not Checked In';
                    let badgeClass = 'bg-secondary';

                    if (data.appointment.checked_in_at) {
                        if (data.queueStatus === 'in_service') {
                            label = 'In Treatment';
                            badgeClass = 'bg-warning';
                        } else if (data.queueStatus === 'completed') {
                            label = 'Completed';
                            badgeClass = 'bg-success';
                        } else if (data.queueStatus === 'waiting') {
                            label = 'Waiting';
                            badgeClass = 'bg-primary';
                        }
                    }

                    statusEl.textContent = label;
                    statusEl.className = `badge ${badgeClass} track-badge`;
                }

                // Update Now Serving
                const servingEl = document.querySelector('[data-update="serving"]');
                if (servingEl) {
                    servingEl.textContent = data.currentServing ? `A-${String(data.currentServing).padStart(2, '0')}` : '—';
                }

                // Update ETA
                const etaEl = document.querySelector('[data-update="eta"]');
                if (etaEl) {
                    etaEl.textContent = data.etaMinutes !== null ? `${data.etaMinutes} min` : 'TBD';
                }

                // Update Room
                const roomEl = document.querySelector('[data-update="room"]');
                if (roomEl) {
                    roomEl.textContent = data.room;
                }

                const lastUpdated = document.getElementById('lastUpdated');
                if (lastUpdated) {
                    const dt = new Date();
                    lastUpdated.textContent = `Updated ${dt.toLocaleTimeString()}`;
                }
            })
            .catch(error => console.error('Error updating tracking data:', error));
    }

    // Update every 5 seconds
    setInterval(updateTrackingData, 5000);
    // Run once on load
    updateTrackingData();
</script>
