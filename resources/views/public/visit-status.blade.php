@extends('layouts.public')

@section('title', 'Visit Status')

@section('content')
<style>
    .visit-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .status-header {
        background: linear-gradient(135deg, #06A3DA 0%, #0582b8 100%);
        color: white;
        padding: 30px 0;
        margin-bottom: 40px;
        box-shadow: 0 4px 12px rgba(6, 163, 218, 0.2);
    }
    
    .status-header h1 {
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .status-header-subtext {
        font-size: 14px;
        opacity: 0.95;
    }
    
    .patient-card {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .queue-badge {
        background: linear-gradient(135deg, #06A3DA 0%, #0582b8 100%);
        color: white;
        border-radius: 50%;
        width: 90px;
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(6, 163, 218, 0.3);
        margin: 0 auto;
    }
    
    .patient-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #999;
        font-weight: 600;
        margin-bottom: 6px;
    }
    
    .info-value {
        font-size: 16px;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .queue-status-section {
        background: linear-gradient(135deg, #f0f8ff 0%, #e6f2ff 100%);
        border-radius: 12px;
        padding: 24px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        border: 2px solid #06A3DA;
        border-left: 6px solid #06A3DA;
    }
    
    .status-item {
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }
    
    .status-item-label {
        font-size: 12px;
        text-transform: uppercase;
        color: #666;
        font-weight: 600;
        margin-bottom: 12px;
    }
    
    .status-item-value {
        font-size: 24px;
        font-weight: 700;
        color: #06A3DA;
    }
    
    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: capitalize;
    }
    
    .status-waiting {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status-called {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    
    .status-treatment {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-completed {
        background-color: #d4edda;
        color: #155724;
    }
    
    .timeline-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .timeline-item {
        display: flex;
        gap: 16px;
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid #eee;
    }
    
    .timeline-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }
    
    .timeline-icon {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 16px;
    }
    
    .timeline-icon-success {
        background: #28a745;
    }
    
    .timeline-icon-primary {
        background: #06A3DA;
    }
    
    .timeline-icon-warning {
        background: #ffc107;
        color: #333;
    }
    
    .timeline-content h6 {
        margin: 0 0 4px 0;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .timeline-content small {
        color: #999;
    }
    
    .feature-box {
        background: #f0f8ff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        border-left: 4px solid #06A3DA;
    }
    
    .feature-box h6 {
        color: #06A3DA;
        font-weight: 700;
        margin-bottom: 12px;
    }
    
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .feature-list li {
        padding: 6px 0;
        color: #666;
        font-size: 14px;
    }
    
    .check-in-button-wrapper {
        margin-bottom: 24px;
    }
    
    .check-in-btn {
        width: 100%;
        padding: 16px;
        border-radius: 8px;
        border: none;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .check-in-btn-primary {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    
    .check-in-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
    }
    
    .already-checked-in {
        background: #d4edda;
        border: 2px solid #28a745;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        display: flex;
        gap: 16px;
    }
    
    .already-checked-in-icon {
        font-size: 32px;
        color: #28a745;
        flex-shrink: 0;
    }
    
    .support-section {
        background: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
    }
    
    .support-section h6 {
        font-weight: 700;
        margin-bottom: 16px;
        color: #2c3e50;
    }
    
    .support-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
    }
    
    .support-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        background: white;
        border: 2px solid #06A3DA;
        color: #06A3DA;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .support-btn:hover {
        background: #06A3DA;
        color: white;
    }
    
    .alert-custom {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
    }
    
    .alert-custom-icon {
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .alert-custom p {
        margin: 0;
        color: #856404;
        font-size: 14px;
    }
    
    @media (max-width: 768px) {
        .status-header h1 {
            font-size: 24px;
        }
        
        .patient-info-grid {
            grid-template-columns: 1fr;
        }
        
        .queue-status-section {
            grid-template-columns: 1fr;
            gap: 16px;
            padding: 16px;
        }
        
        .status-item {
            padding: 12px 0;
        }
    }
</style>

<div class="visit-container">
    <!-- Status Header -->
    <div class="status-header">
        <div class="container">
            <h1>🏥 Your Visit Status</h1>
            <div class="status-header-subtext">
                <strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }}</strong> at <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</strong>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <!-- Patient Information Card -->
                <div class="patient-card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                        <div style="flex: 1; min-width: 200px;">
                            <div class="info-label">Patient Name</div>
                            <div class="info-value">{{ $appointment->patient_name }}</div>
                            <div style="font-size: 14px; color: #666; margin-top: 4px;">{{ $appointment->patient_phone }}</div>
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <div class="info-label">Service</div>
                            <div class="info-value">{{ $appointment->service?->name ?? 'To be confirmed' }}</div>
                        </div>
                        <div style="flex: 1; min-width: 200px;">
                            <div class="info-label">Dentist</div>
                            <div class="info-value">{{ $appointment->dentist?->name ?? 'Will be assigned' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Queue Status Section -->
                <div class="queue-status-section">
                    <div class="status-item">
                        <div class="status-item-label">Queue Number</div>
                        <div class="queue-badge">{{ $queueNumber ? 'A-' . str_pad($queueNumber, 2, '0', STR_PAD_LEFT) : '—' }}</div>
                    </div>
                    <div class="status-item">
                        <div class="status-item-label">Queue Status</div>
                        <div style="margin-top: 20px;">
                            <span class="status-badge status-{{ $queueStatus }}">
                                {{ str_replace('_', ' ', $queueStatus) }}
                            </span>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="status-item-label">Estimated Wait</div>
                        <div class="status-item-value" style="margin-top: 20px;">{{ $etaMinutes !== null ? $etaMinutes : 'TBD' }} <span style="font-size: 16px;">min</span></div>
                    </div>
                </div>

                <!-- Alert for Early Arrival -->
                <div class="alert-custom">
                    <div class="alert-custom-icon">⏰</div>
                    <div>
                        <p><strong>Arrive Early:</strong> Please arrive 5-10 minutes before your appointment time for a smooth check-in process.</p>
                    </div>
                </div>

                <!-- Visit Timeline -->
                <div class="timeline-section">
                    <h6 style="margin-bottom: 24px; font-weight: 700; color: #2c3e50;">📅 Visit Timeline</h6>
                    
                    <div class="timeline-item">
                        <div class="timeline-icon timeline-icon-success">✓</div>
                        <div class="timeline-content">
                            <h6>Appointment Booked</h6>
                            <small>{{ \Carbon\Carbon::parse($appointment->created_at)->format('d M Y, H:i') }}</small>
                        </div>
                    </div>

                    @if($queueNumber && $queueStatus !== 'not-queued')
                    <div class="timeline-item">
                        <div class="timeline-icon timeline-icon-primary">⏳</div>
                        <div class="timeline-content">
                            <h6>Queued</h6>
                            <small>Queue A-{{ str_pad($queueNumber, 2, '0', STR_PAD_LEFT) }} {{ $etaMinutes !== null ? '• Est. wait: ' . $etaMinutes . ' min' : '' }}</small>
                        </div>
                    </div>
                    @endif

                    @if($appointment->checked_in_at)
                    <div class="timeline-item">
                        <div class="timeline-icon timeline-icon-warning">✓</div>
                        <div class="timeline-content">
                            <h6>Checked In</h6>
                            <small>{{ \Carbon\Carbon::parse($appointment->checked_in_at)->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Check-In Section -->
                @if(\Carbon\Carbon::parse($appointment->appointment_date)->isToday() && !$appointment->checked_in_at)
                <div class="check-in-button-wrapper">
                    <form method="POST" action="{{ url('/visit/' . $appointment->visit_token . '/check-in') }}">
                        @csrf
                        <button type="submit" class="check-in-btn check-in-btn-primary">
                            ✓ I've Arrived at the Clinic
                        </button>
                    </form>
                    <p style="text-align: center; color: #999; font-size: 12px; margin-top: 8px;">
                        Tap to check in and activate your queue number
                    </p>
                </div>
                @elseif(\Carbon\Carbon::parse($appointment->appointment_date)->isToday() && $appointment->checked_in_at)
                <div class="already-checked-in">
                    <div class="already-checked-in-icon">✓</div>
                    <div>
                        <h6 style="margin: 0 0 4px 0; color: #155724;">You're all checked in!</h6>
                        <p style="margin: 0; color: #155724; font-size: 14px;">
                            Checked in at <strong>{{ \Carbon\Carbon::parse($appointment->checked_in_at)->format('H:i') }}</strong>. Your queue number is now active. Thank you!
                        </p>
                    </div>
                </div>
                @endif

                <!-- Feature Box -->
                <div class="feature-box">
                    <h6>🔒 Secure & Private Tracking</h6>
                    <ul class="feature-list">
                        <li>✓ No login required - completely anonymous</li>
                        <li>✓ Your data is stored securely in your browser</li>
                        <li>✓ Real-time queue updates</li>
                        <li>✓ Works on any device with the tracking link</li>
                    </ul>
                </div>

                <!-- Support Section -->
                <div class="support-section">
                    <h6>📞 Need Help?</h6>
                    <p style="color: #666; font-size: 14px; margin-bottom: 16px;">Contact us anytime if you have questions:</p>
                    <div class="support-buttons">
                        <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="support-btn">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=klinikgigihelmy@gmail.com&su=Dental%20Appointment%20Inquiry" target="_blank" class="support-btn">
                            <i class="bi bi-envelope"></i> Email
                        </a>
                    </div>
                </div>

                <!-- Operating Hours Section -->
                @if($operatingHours && $operatingHours->isNotEmpty())
                <div class="patient-card" style="margin-bottom: 40px;">
                    <h6 style="font-weight: 700; margin-bottom: 16px;">🕐 Today's Operating Hours</h6>
                    @php
                        $today = now()->format('l');
                        $todayHours = $operatingHours->where('day_of_week', $today);
                    @endphp
                    @if($todayHours->isNotEmpty())
                        @foreach($todayHours as $hour)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #eee;">
                                <span style="font-weight: 600;">{{ $today }}</span>
                                <div>
                                    @if($hour->is_closed)
                                        <span style="background: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">Closed</span>
                                    @else
                                        <span style="color: #666; font-size: 14px;">
                                            {{ date('g:i a', strtotime($hour->start_time)) }} - {{ date('g:i a', strtotime($hour->end_time)) }}
                                            @if($hour->session_label)
                                                <span style="background: #e7f3ff; color: #06A3DA; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; margin-left: 8px;">{{ $hour->session_label }}</span>
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Store Token & Add Auto-Refresh -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Store token in localStorage for stateless tracking
        const token = window.location.pathname.split('/').pop();
        localStorage.setItem('visit_token', token);
        
        // Handle email button click
        const emailBtn = document.querySelector('a[href*="mailto:"]');
        if (emailBtn) {
            emailBtn.addEventListener('click', function(e) {
                // Allow the default mailto behavior
                // This will trigger the system's default email client
            });
        }
        
        // Optional: Auto-refresh every 30 seconds to get latest queue status
        setInterval(function() {
            location.reload();
        }, 30000);
    });
</script>
@endsection
