@extends('layouts.staff')

@section('title', 'Staff Calendar')

@section('content')
<div id="calendar"></div>
@endsection

@push('styles')
<style>
    .main-content { padding-top: 0.5rem !important; }
    #calendar { 
        background: #fff; 
        border-radius: 12px; 
        box-shadow: 0 2px 12px rgba(9,30,62,0.08); 
        padding: 24px;
        min-height: calc(100vh - 160px);
    }
    .fc .fc-toolbar { 
        margin-bottom: 1.25rem !important; 
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: nowrap;
    }
    .fc .fc-toolbar-chunk { 
        display: flex; 
        align-items: center; 
        gap: 0.5rem;
        flex-shrink: 0;
    }
    .fc .fc-toolbar-title { 
        font-weight: 700; 
        font-size: 1.75rem; 
        color: #2c3e50;
        margin: 0;
        line-height: 1;
    }
    .fc .fc-button { 
        padding: 0.375rem 0.5rem; 
        font-weight: 500;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
    }
    .fc .fc-button-group { 
        display: flex; 
        gap: 0;
    }
    .fc-event { font-weight: 500; font-size: 0.9rem; }
    .fc-daygrid-event { padding: 3px 5px; margin: 1px 0; }
</style>
@endpush

@push('scripts')
@vite(['resources/js/app.js'])
<script>
    function fetchEvents(info, success, failure) {
        const params = new URLSearchParams({
            start: info.startStr,
            end: info.endStr,
        });
        const filterEl = document.getElementById('dentistFilterInline');
        const dentistId = filterEl ? filterEl.value : '';
        if (dentistId) params.append('dentist_id', dentistId);
        fetch(`/staff/calendar/events?` + params.toString())
            .then(r => r.json())
            .then(data => success(data))
            .catch(err => failure(err));
    }

    function initializeCalendar() {
        try {
            if (typeof FullCalendar === 'undefined') {
                throw new Error('FullCalendar library not loaded');
            }
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [FullCalendar.timeGridPlugin, FullCalendar.dayGridPlugin, FullCalendar.interactionPlugin],
                initialView: 'timeGridDay',
                timeZone: 'local',
                locale: 'en-GB',
                headerToolbar: {
                    left: 'prev,next',
                    center: '',
                    right: 'dentistFilter legend timeGridDay,timeGridWeek,dayGridMonth'
                },
                buttonText: {
                    today: 'Today',
                    timeGridDay: 'Day',
                    timeGridWeek: 'Week',
                    dayGridMonth: 'Month'
                },
                dayHeaderFormat: { weekday: 'short', day: 'numeric', month: 'numeric' },
                customButtons: {
                    dentistFilter: { text: '', hint: 'Filter by dentist' },
                    legend: { text: '', hint: 'Color legend' }
                },
                firstDay: 1,
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
                contentHeight: 'auto',
                expandRows: true,
                events: fetchEvents,
                eventClick: function(info) {
                    if (info.event.url) { window.location.href = info.event.url; }
                },
            });
            
            calendar.render();

            // Inject filter dropdown
            const filterBtn = document.querySelector('.fc-dentistFilter-button');
            if (filterBtn) {
                filterBtn.innerHTML = `<select id="dentistFilterInline" class="form-select form-select-sm" style="min-width: 200px; height: 38px; font-size: 0.9rem;">
                    <option value="">🩺 All Dentists</option>
                    @foreach($dentists as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>`;
                filterBtn.style.cssText = 'padding: 0; border: none; background: transparent; height: 38px; display: inline-flex; align-items: center;';
            }

            // Inject legend
            const legendBtn = document.querySelector('.fc-legend-button');
            if (legendBtn) {
                legendBtn.innerHTML = `<div class="d-flex gap-2 align-items-center" style="font-size: 0.85rem; height: 38px;">
                    <span class="badge px-2 py-1" style="background: #198754; color: white; white-space: nowrap;">✓ Confirmed</span>
                    <span class="badge px-2 py-1" style="background: #ffc107; color: #000; white-space: nowrap;">⏱ Waiting</span>
                    <span class="badge px-2 py-1" style="background: #0d6efd; color: white; white-space: nowrap;">⚕ Progress</span>
                    <span class="badge px-2 py-1" style="background: #dc3545; color: white; white-space: nowrap;">⚠ Conflict</span>
                    <span class="badge px-2 py-1" style="background: #6c757d; color: white; white-space: nowrap;">✔ Done</span>
                </div>`;
                legendBtn.style.cssText = 'padding: 0; border: none; background: transparent; height: 38px; display: inline-flex; align-items: center;';
            }

            // Setup filter change listener
            setTimeout(() => {
                const dentistFilterInline = document.getElementById('dentistFilterInline');
                if (dentistFilterInline) {
                    dentistFilterInline.addEventListener('change', () => {
                        calendar.refetchEvents();
                    });
                }
            }, 100);
            
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
