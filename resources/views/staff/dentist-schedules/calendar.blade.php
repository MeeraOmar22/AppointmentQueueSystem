@extends('layouts.staff')

@section('title', 'Dentist Schedule Calendar')

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
        margin-bottom: 0.5rem !important; 
        padding: 0.5rem 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: nowrap;
        overflow: visible;
    }
    .fc .fc-toolbar-chunk { 
        display: flex; 
        align-items: center; 
        gap: 0.25rem;
        flex-shrink: 1;
        min-width: 0;
    }
    .fc .fc-toolbar-title { 
        font-weight: 700; 
        font-size: 1.75rem; 
        color: #2c3e50;
        margin: 0;
        line-height: 1;
    }
    .fc .fc-button { 
        padding: 0.25rem 0.4rem; 
        font-weight: 500;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
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
        
        const url = `/staff/dentist-schedules/calendar/events?` + params.toString();
        console.log('Fetching events from:', url);
        
        fetch(url)
            .then(r => {
                console.log('Response status:', r.status);
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                console.log('Events received:', data);
                success(data);
            })
            .catch(err => {
                console.error('Error fetching events:', err);
                failure(err);
            });
    }

    function initializeCalendar() {
        try {
            console.log('Starting calendar initialization...');
            if (typeof FullCalendar === 'undefined') {
                throw new Error('FullCalendar library not loaded');
            }
            console.log('FullCalendar loaded:', typeof FullCalendar);
            const calendarEl = document.getElementById('calendar');
            if (!calendarEl) {
                throw new Error('Calendar element not found');
            }
            console.log('Calendar element found');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: [FullCalendar.dayGridPlugin, FullCalendar.interactionPlugin],
                initialView: 'dayGridMonth',
                timeZone: 'local',
                locale: 'en-GB',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'dentistFilter legend',
                    right: 'manageBtn title'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month'
                },
                customButtons: {
                    dentistFilter: { text: '', hint: 'Filter by dentist' },
                    legend: { text: '', hint: 'Legend' },
                    manageBtn: { 
                        text: 'Manage',
                        click: function() {
                            window.location.href = '/staff/dentist-schedules';
                        }
                    }
                },
                firstDay: 1,
                contentHeight: 'auto',
                expandRows: true,
                events: fetchEvents,
            });
            
            console.log('Calendar instance created, rendering...');
            calendar.render();
            console.log('Calendar rendered');

            // Inject filter dropdown
            const filterBtn = document.querySelector('.fc-dentistFilter-button');
            if (filterBtn) {
                filterBtn.innerHTML = `<select id="dentistFilterInline" class="form-select form-select-sm" style="min-width: 140px; height: 36px; font-size: 0.85rem; padding: 0.25rem 0.5rem;">
                    <option value="">🧷 All</option>
                    @foreach(\App\Models\Dentist::where('status', 1)->orderBy('name')->get() as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>`;
                filterBtn.style.cssText = 'padding: 0; border: none; background: transparent; height: 36px; display: inline-flex; align-items: center; margin: 0;';
                console.log('Filter dropdown injected');
            }

            // Inject legend
            const legendBtn = document.querySelector('.fc-legend-button');
            if (legendBtn) {
                legendBtn.innerHTML = `<div class="d-flex gap-2 align-items-center" style="font-size: 0.85rem; height: 36px;">
                    <span class="badge" style="background: #198754; color: white; padding: 0.35rem 0.65rem;">Avail</span>
                    <span class="badge" style="background: #ffc107; color: black; padding: 0.35rem 0.65rem;">Unavail</span>
                    <span class="badge" style="background: #0d6efd; color: white; padding: 0.35rem 0.65rem;">Appt</span>
                    <span class="badge" style="background: #dc3545; color: white; padding: 0.35rem 0.65rem;">Leave</span>
                </div>`;
                legendBtn.style.cssText = 'padding: 0; border: none; background: transparent; height: 36px; display: inline-flex; align-items: center; white-space: nowrap; margin: 0;';
                console.log('Legend injected with all statuses');
            }

            setTimeout(() => {
                const filterEl = document.getElementById('dentistFilterInline');
                if (filterEl) {
                    filterEl.addEventListener('change', () => {
                        console.log('Filter changed, refetching');
                        calendar.refetchEvents();
                    });
                    console.log('Filter listener attached');
                }
            }, 100);

        } catch (error) {
            console.error('Calendar initialization error:', error.message);
            alert('Error: ' + error.message);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCalendar);
    } else {
        initializeCalendar();
    }
</script>
@endpush
