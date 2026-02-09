@extends('layouts.staff')

@section('title', 'Staff Calendar')

@section('content')
<!-- Compact Header with Navigation -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">
            <i class="bi bi-calendar3 me-2"></i>Appointment Calendar
        </h4>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="realtime-indicator text-nowrap">
            <span></span> <small>Live Updates</small>
        </span>
        <a href="/staff/appointments" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-list-check me-1"></i>Appointments
        </a>
        <a href="/staff/queue" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-diagram-3 me-1"></i>Queue
        </a>
    </div>
</div>

<!-- Calendar Container -->
<div class="bg-white rounded shadow-sm p-4">
    <!-- Inline Legend and Filter -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <small class="text-muted fw-bold">STATUS:</small>
            <span class="badge" style="background: #198754; color: white; font-size: 0.75rem;">Confirmed</span>
            <span class="badge" style="background: #ffc107; color: #000; font-size: 0.75rem;">Waiting</span>
            <span class="badge" style="background: #0d6efd; color: white; font-size: 0.75rem;">In Treatment</span>
            <span class="badge" style="background: #28a745; color: white; font-size: 0.75rem;">Completed</span>
            <span class="badge" style="background: #dc3545; color: white; font-size: 0.75rem;">Cancelled</span>
        </div>
        <div class="d-flex gap-2">
            <select id="statusFilterInline" class="form-select form-select-sm" style="min-width: 180px;">
                <option value="">📊 All Statuses</option>
                <option value="booked,confirmed,checked_in">Confirmed</option>
                <option value="waiting">Waiting</option>
                <option value="in_treatment">In Treatment</option>
                <option value="completed,feedback_scheduled,feedback_sent">Completed</option>
                <option value="cancelled,no_show">Cancelled</option>
            </select>
            <select id="dentistFilterInline" class="form-select form-select-sm" style="min-width: 200px;">
                <option value="">🩺 All Dentists</option>
                @foreach($dentists as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    
    <!-- Calendar -->
    <div id="calendar"></div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailsContent">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <a href="#" id="editAppointmentBtn" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .realtime-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.85rem;
        color: #dc3545;
        font-weight: 500;
    }

    .realtime-indicator span {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #dc3545;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Calendar Container */
    #calendar {
        background: #fff;
    }

    /* FullCalendar Core Styles */
    .fc {
        font-family: inherit;
        font-size: 0.9rem;
    }

    .fc .fc-toolbar {
        margin-bottom: 1.5rem;
        padding: 0;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .fc .fc-toolbar-title {
        font-weight: 700;
        font-size: 1.3rem;
        color: #2c3e50;
    }

    .fc .fc-button-primary {
        background-color: #0d6efd !important;
        border: none !important;
        padding: 0.35rem 0.65rem !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        border-radius: 4px !important;
    }

    .fc .fc-button-primary:hover {
        background-color: #0b5ed7 !important;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: #0a58ca !important;
    }

    /* Headers */
    .fc .fc-col-header-cell {
        font-weight: 600;
        padding: 10px 4px;
        background: #f8f9fa;
        border-color: #dee2e6;
        font-size: 0.9rem;
        color: #495057;
    }

    .fc .fc-daygrid-day-number {
        padding: 6px 8px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .fc .fc-daygrid-day-frame {
        min-height: 100px;
    }

    .fc .fc-daygrid-day {
        border-color: #e9ecef;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: #fff8e6 !important;
    }

    .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
        background: #ffc107;
        color: white;
        border-radius: 3px;
        padding: 4px 6px;
    }

    /* Time Grid */
    .fc .fc-timegrid-slot {
        height: 3rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .fc .fc-timegrid-axis {
        width: 50px;
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Events - Clean and Simple */
    .fc-event {
        border: none !important;
        border-radius: 4px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        padding: 3px 6px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
    }

    .fc-event:hover {
        box-shadow: 0 2px 6px rgba(0,0,0,0.2) !important;
        transform: translateY(-1px) !important;
    }

    .fc-event-title {
        padding: 0 !important;
        white-space: normal !important;
        word-wrap: break-word !important;
        line-height: 1.2 !important;
        color: white !important;
        font-weight: 700 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2) !important;
    }

    .fc-event-time {
        font-size: 0.75rem !important;
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 600 !important;
        margin-top: 1px !important;
    }

    .fc-daygrid-event {
        margin: 1px 1px !important;
        padding: 1px !important;
    }

    .fc-daygrid-event-frame {
        padding: 0 !important;
    }

    /* Other */
    .fc .fc-daygrid-day-other .fc-daygrid-day-frame {
        background-color: #f8f9fa;
    }

    .fc .fc-daygrid-day-other .fc-daygrid-day-number {
        color: #adb5bd;
    }

    .fc-daygrid-event-dot {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
@vite(['resources/js/app.js'])
<script>
    // Show appointment details in modal
    function showEventDetails(event) {
        const props = event.extendedProps;
        const contentDiv = document.getElementById('eventDetailsContent');
        
        // Format times
        const startTime = event.start ? new Date(event.start).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
        const endTime = event.end ? new Date(event.end).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
        
        // Status colors
        const statusColors = {
            'booked': '#198754',
            'confirmed': '#198754',
            'checked_in': '#198754',
            'waiting': '#ffc107',
            'in_treatment': '#0d6efd',
            'completed': '#28a745',
            'feedback_scheduled': '#28a745',
            'feedback_sent': '#28a745',
            'cancelled': '#dc3545',
            'no_show': '#dc3545'
        };
        
        const statusColor = statusColors[props.status] || '#6c757d';
        const statusLabel = props.status.charAt(0).toUpperCase() + props.status.slice(1).replace(/_/g, ' ');
        
        contentDiv.innerHTML = `
            <div class="mb-3">
                <div style="background: ${statusColor}; color: white; padding: 10px; border-radius: 4px; text-align: center; font-weight: 700; font-size: 0.95rem;">
                    ${statusLabel}
                </div>
            </div>
            
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr>
                        <td class="text-muted fw-bold" style="width: 35%; font-size: 0.85rem;">Patient:</td>
                        <td class="fw-bold">${event.title}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold" style="width: 35%; font-size: 0.85rem;">Time:</td>
                        <td>${startTime} - ${endTime}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold" style="width: 35%; font-size: 0.85rem;">Duration:</td>
                        <td>${props.duration}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold" style="width: 35%; font-size: 0.85rem;">Dentist:</td>
                        <td>${props.dentist}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold" style="width: 35%; font-size: 0.85rem;">Service:</td>
                        <td>${props.service}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold" style="width: 35%; font-size: 0.85rem;">Room:</td>
                        <td>${props.room}</td>
                    </tr>
                </tbody>
            </table>
        `;
        
        document.getElementById('editAppointmentBtn').href = `/staff/appointments/${event.id}/edit`;
        
        const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
        modal.show();
    }
    
    function fetchEvents(info, success, failure) {
        const params = new URLSearchParams({
            start: info.startStr,
            end: info.endStr,
        });
        const dentistFilterEl = document.getElementById('dentistFilterInline');
        const statusFilterEl = document.getElementById('statusFilterInline');
        const dentistId = dentistFilterEl ? dentistFilterEl.value : '';
        const status = statusFilterEl ? statusFilterEl.value : '';
        if (dentistId) params.append('dentist_id', dentistId);
        if (status) params.append('status', status);
            
            console.log('[Calendar] Fetching events:', info.startStr, 'to', info.endStr);
            
            fetch(`/staff/calendar/events?` + params.toString())
                .then(r => {
                    console.log('[Calendar] Response status:', r.status);
                    if (!r.ok) throw new Error(`HTTP ${r.status}`);
                    return r.json();
                })
                .then(data => {
                    console.log('[Calendar] Events received:', data.length, 'appointments');
                    console.log('[Calendar] First event:', JSON.stringify(data[0], null, 2));
                    success(data);
                })
                .catch(err => {
                    console.error('[Calendar] Error fetching events:', err);
                    failure(err);
                });
    }

    function initializeCalendar() {
        try {
            if (typeof FullCalendar === 'undefined') {
                throw new Error('FullCalendar library not loaded');
            }
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [FullCalendar.dayGridPlugin, FullCalendar.timeGridPlugin, FullCalendar.interactionPlugin],
                initialView: 'timeGridWeek',
                timeZone: 'local',
                locale: 'en-GB',
                height: 'auto',
                contentHeight: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                    today: 'Today',
                    timeGridDay: 'Day',
                    timeGridWeek: 'Week',
                    dayGridMonth: 'Month'
                },
                dayHeaderFormat: { weekday: 'short', day: 'numeric', month: 'numeric' },
                eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short', hour12: false },
                firstDay: 1,
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                slotLabelInterval: '00:30:00',
                slotLabelFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short', hour12: false },
                events: fetchEvents,
                eventClick: function(info) {
                    showEventDetails(info.event);
                },
                eventDisplay: 'block',
                eventContent: function(info) {
                    // Custom content rendering with inline styles
                    return {
                        html: `<div style="background-color: ${info.event.backgroundColor}; color: white; padding: 3px 6px; border-radius: 4px; font-weight: 600; font-size: 0.8rem; width: 100%; box-sizing: border-box;">${info.event.title}</div>`
                    };
                },
                eventDidMount: function(info) {
                    console.log('[Calendar] Event mounted:', info.event.title);
                    console.log('[Calendar] Event color:', info.event.backgroundColor);
                    // Force apply the background color to the event element
                    const el = info.el;
                    if (el && info.event.backgroundColor) {
                        el.style.backgroundColor = info.event.backgroundColor;
                        el.style.borderColor = info.event.borderColor || info.event.backgroundColor;
                        // Also apply to inner elements
                        const titleEl = el.querySelector('.fc-event-title');
                        if (titleEl) {
                            titleEl.style.color = 'white';
                        }
                    }
                },
            });
            
            calendar.render();
            console.log('[Calendar] Calendar rendered successfully');

            // Setup filter change listeners for both dentist and status filters
            const dentistFilterInline = document.getElementById('dentistFilterInline');
            const statusFilterInline = document.getElementById('statusFilterInline');
            
            if (dentistFilterInline) {
                dentistFilterInline.addEventListener('change', () => {
                    calendar.refetchEvents();
                });
            }
            
            if (statusFilterInline) {
                statusFilterInline.addEventListener('change', () => {
                    calendar.refetchEvents();
                });
            }
            
        } catch (error) {
            const calDiv = document.getElementById('calendar');
            if (calDiv) {
                calDiv.innerHTML = `<div class="alert alert-danger">Calendar initialization error: ${error.message}</div>`;
            }
            console.error('FullCalendar error:', error);
        }
    }
    
    // Try to initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCalendar);
    } else {
        // DOM already loaded, try now or retry
        if (typeof FullCalendar !== 'undefined') {
            initializeCalendar();
        } else {
            // Retry after a short delay to let CDN load
            setTimeout(function() {
                if (typeof FullCalendar !== 'undefined') {
                    initializeCalendar();
                } else {
                    const calDiv = document.getElementById('calendar');
                    if (calDiv) {
                        calDiv.innerHTML = '<div class="alert alert-danger">Failed to load FullCalendar library. Check internet connection.</div>';
                    }
                }
            }, 1000);
        }
    }
</script>
@endpush
