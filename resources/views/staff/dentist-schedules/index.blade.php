@extends('layouts.staff')

@section('title', 'Dentist Schedules')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dentist Schedules</h3>
        <p class="text-muted mb-0">View and manage dentist schedules and availability</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="/staff/dentist-schedules/calendar" class="btn btn-primary">
            <i class="bi bi-calendar3 me-2"></i>Monthly Calendar
        </a>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
            <i class="bi bi-plus-circle me-2"></i>Add Leave
        </button>
    </div>
</div>

<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeaveModalLabel">Add Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLeaveForm">
                    <div class="mb-3">
                        <label for="leave-dentist" class="form-label">Dentist</label>
                        <select id="leave-dentist" class="form-select" required>
                            <option value="" selected disabled>Select dentist</option>
                            @foreach($dentists as $dentistOption)
                                <option value="{{ $dentistOption->id }}">{{ $dentistOption->name }} {{ $dentistOption->status ? '' : '(Inactive)' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label for="leave-from" class="form-label">From</label>
                            <input type="date" id="leave-from" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="leave-to" class="form-label">To</label>
                            <input type="date" id="leave-to" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="leave-reason" class="form-label">Reason (optional)</label>
                        <input type="text" id="leave-reason" class="form-control" placeholder="E.g. training, personal">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitLeaveBtn">Save leave</button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-light border d-flex align-items-center" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    <div>
        Use the search and tabs to reduce clutter. Overview shows the essentials; switch to Schedule, Leaves, or History for details.
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php($todayName = \Carbon\Carbon::now()->isoFormat('dddd'))
@foreach($dentists as $dentist)
    @php($todaySchedule = $dentist->schedules->firstWhere('day_of_week', $todayName))
    @php($onLeaveToday = $dentist->leaves->first(function($l){ return \Carbon\Carbon::today()->betweenIncluded($l->start_date, $l->end_date); }))
    @php($recentCount = $dentist->appointments()->where('appointment_date', '>=', \Carbon\Carbon::now()->subWeeks(2))->count())
    @php($nextLeave = $dentist->leaves()->where('start_date', '>=', \Carbon\Carbon::today())->orderBy('start_date')->first())

    <div class="card mb-3 dentist-card shadow-sm" data-name="{{ strtolower($dentist->name) }}" data-active="{{ $dentist->status ? '1' : '0' }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1">{{ $dentist->name }}</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge {{ $dentist->status ? 'bg-success' : 'bg-secondary' }}">{{ $dentist->status ? 'Active' : 'Inactive' }}</span>
                        @if($onLeaveToday)
                            <span class="badge bg-danger">On leave today</span>
                        @elseif($todaySchedule && $todaySchedule->is_available && $todaySchedule->start_time && $todaySchedule->end_time)
                            <span class="badge bg-primary">Today: {{ \Carbon\Carbon::parse($todaySchedule->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($todaySchedule->end_time)->format('H:i') }}</span>
                        @elseif($todaySchedule && !$todaySchedule->is_available)
                            <span class="badge bg-warning text-dark">Today: Unavailable</span>
                        @endif
                        @if($nextLeave)
                            <span class="badge bg-light text-dark">Next leave: {{ $nextLeave->start_date->format('M d') }}</span>
                        @endif
                        <span class="badge bg-info text-dark">Recent appts (2 wks): {{ $recentCount }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2" role="group">
                    <button class="btn btn-outline-secondary btn-sm" data-expand-card>Expand</button>
                    <button class="btn btn-outline-secondary btn-sm" data-collapse-card>Collapse</button>
                </div>
            </div>

            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-overview-{{ $dentist->id }}" type="button" role="tab">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-schedule-{{ $dentist->id }}" type="button" role="tab">Schedule</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-leaves-{{ $dentist->id }}" type="button" role="tab">Leaves</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-history-{{ $dentist->id }}" type="button" role="tab">History</button>
                </li>
            </ul>

            <div class="tab-content p-3">
                <div class="tab-pane fade show active" id="tab-overview-{{ $dentist->id }}" role="tabpanel">
                    <div class="row gy-2">
                        <div class="col-md-6">
                            <div class="card card-body border-0 shadow-sm">
                                <div class="d-flex justify-content-between"><span>Status</span><strong>{{ $dentist->status ? 'Active' : 'Inactive' }}</strong></div>
                                <div class="d-flex justify-content-between"><span>On leave today</span><strong>{{ $onLeaveToday ? 'Yes' : 'No' }}</strong></div>
                                <div class="d-flex justify-content-between"><span>Today</span>
                                    <strong>
                                        @if($onLeaveToday)
                                            On leave
                                        @elseif($todaySchedule && $todaySchedule->is_available && $todaySchedule->start_time && $todaySchedule->end_time)
                                            {{ \Carbon\Carbon::parse($todaySchedule->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($todaySchedule->end_time)->format('H:i') }}
                                        @elseif($todaySchedule && !$todaySchedule->is_available)
                                            Unavailable
                                        @else
                                            —
                                        @endif
                                    </strong>
                                </div>
                                <div class="d-flex justify-content-between"><span>Next leave</span><strong>{{ $nextLeave ? $nextLeave->start_date->format('M d') : '—' }}</strong></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-body border-0 shadow-sm">
                                <div class="d-flex justify-content-between"><span>Recent appointments (2 wks)</span><strong>{{ $recentCount }}</strong></div>
                                <div class="d-flex justify-content-between"><span>Leaves recorded</span><strong>{{ $dentist->leaves->count() }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-schedule-{{ $dentist->id }}" role="tabpanel">
                    <div class="table-responsive" style="max-height: 260px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Day</th>
                                    <th style="width: 120px;">Available</th>
                                    <th style="width: 160px;">Start</th>
                                    <th style="width: 160px;">End</th>
                                    <th style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($days as $day)
                                    @php($schedule = $dentist->schedules->firstWhere('day_of_week', $day))
                                    <tr>
                                        <td><strong>{{ $day }}</strong></td>
                                        <td>
                                            <form method="POST" action="/staff/dentist-schedules/{{ $schedule->id ?? 0 }}" class="row gx-2 gy-1 align-items-center availability-toggle-form">
                                                @csrf
                                                @method('PATCH')
                                                <div class="col-auto">
                                                    <div class="form-check">
                                                        <input class="form-check-input availability-toggle" type="checkbox" name="is_available" value="1" {{ $schedule && $schedule->is_available ? 'checked' : '' }}>
                                                    </div>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <input type="time" name="start_time" form="form-{{ $schedule->id ?? $day }}" class="form-control form-control-sm" value="{{ $schedule && $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '' }}" {{ $schedule && $schedule->is_available ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <input type="time" name="end_time" form="form-{{ $schedule->id ?? $day }}" class="form-control form-control-sm" value="{{ $schedule && $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '' }}" {{ $schedule && $schedule->is_available ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <form id="form-{{ $schedule->id ?? $day }}" method="POST" action="/staff/dentist-schedules/{{ $schedule->id ?? 0 }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_available" value="{{ $schedule && $schedule->is_available ? 1 : 0 }}">
                                                <button type="submit" class="btn btn-sm btn-primary" data-schedule-save="{{ $dentist->id }}">Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-leaves-{{ $dentist->id }}" role="tabpanel">
                    <div class="alert alert-light py-2 px-3">Use the Add Leave button at the top to record leave.</div>
                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="font-size: 0.85rem;">From</th>
                                    <th style="font-size: 0.85rem;">To</th>
                                    <th style="font-size: 0.85rem;">Reason</th>
                                    <th style="font-size: 0.85rem;">Days</th>
                                    <th style="font-size: 0.85rem;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dentist->leaves()->orderBy('start_date', 'desc')->get() as $leave)
                                    <tr style="font-size: 0.85rem;">
                                        <td>{{ $leave->start_date->format('M d') }}</td>
                                        <td>{{ $leave->end_date->format('M d') }}</td>
                                        <td>{{ $leave->reason ?? '' }}</td>
                                        <td><span class="badge bg-info">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} days</span></td>
                                        <td>
                                            <form method="POST" action="/staff/dentist-leaves/{{ $leave->id }}" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this leave?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-2" style="font-size: 0.85rem;">No leaves recorded</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-history-{{ $dentist->id }}" role="tabpanel">
                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="font-size: 0.85rem;">Date</th>
                                    <th style="font-size: 0.85rem;">Time</th>
                                    <th style="font-size: 0.85rem;">Patient</th>
                                    <th style="font-size: 0.85rem;">Service</th>
                                    <th style="font-size: 0.85rem;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dentist->appointments()->where('appointment_date', '>=', \Carbon\Carbon::now()->subWeeks(2))->orderBy('appointment_date', 'desc')->limit(10)->get() as $apt)
                                    <tr style="font-size: 0.85rem;">
                                        <td>{{ $apt->appointment_date ? $apt->appointment_date->format('M d') : '' }}</td>
                                        <td>{{ $apt->appointment_time ?? '' }}</td>
                                        <td>{{ $apt->patient_name }}</td>
                                        <td><small>{{ $apt->service?->name ?? '' }}</small></td>
                                        <td><span class="badge bg-{{ $apt->status == 'completed' ? 'success' : ($apt->status == 'booked' ? 'primary' : 'secondary') }}">{{ ucfirst($apt->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-2" style="font-size: 0.85rem;">No appointments in past 2 weeks</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  function addLeave() {
      const dentistId = document.getElementById('leave-dentist').value;
      const fromDate = document.getElementById('leave-from').value;
      const toDate = document.getElementById('leave-to').value;
      const reason = document.getElementById('leave-reason').value;

      if (!dentistId || !fromDate || !toDate) {
          alert('Please select a dentist and both dates');
          return;
      }

      fetch('/staff/dentist-leaves', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
              dentist_id: dentistId,
              start_date: fromDate,
              end_date: toDate,
              reason: reason
          })
      })
      .then(r => r.json())
      .then(data => {
          if (data.success) {
              if (window.bootstrap) {
                  const modalEl = document.getElementById('addLeaveModal');
                  const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                  modal.hide();
              }
              document.getElementById('addLeaveForm').reset();
              alert('Leave added successfully');
              location.reload();
          } else {
              alert('Error: ' + (data.message || 'Failed to add leave'));
          }
      })
      .catch(err => {
          console.error('Error:', err);
          alert('Error adding leave');
      });
  }

  document.getElementById('submitLeaveBtn').addEventListener('click', addLeave);

    const searchInput = document.getElementById('dentistSearch');
    const showInactiveToggle = document.getElementById('showInactiveToggle');

  function applyFilters() {
      const q = (searchInput?.value || '').trim().toLowerCase();
      const showInactive = !!(showInactiveToggle && showInactiveToggle.checked);
      document.querySelectorAll('.dentist-card').forEach(card => {
          const name = (card.getAttribute('data-name') || '').toLowerCase();
          const active = card.getAttribute('data-active') === '1';
          const searchMatch = name.includes(q);
          const activeMatch = active || showInactive;
          const visible = searchMatch && activeMatch;
          card.style.display = visible ? '' : 'none';
      });
  }

  if (searchInput) searchInput.addEventListener('input', applyFilters);
  if (showInactiveToggle) showInactiveToggle.addEventListener('change', applyFilters);

  applyFilters();

  function expandCard(card) {
      const tabContent = card.querySelector('.tab-content');
      if (tabContent) tabContent.classList.remove('d-none');
      const panes = card.querySelectorAll('.tab-pane');
      panes.forEach((pane, idx) => {
          const isOverview = idx === 0;
          pane.classList.toggle('show', isOverview);
          pane.classList.toggle('active', isOverview);
      });
      card.querySelectorAll('.nav-link').forEach((tab, idx) => {
          tab.classList.toggle('active', idx === 0);
      });
  }

  function collapseCard(card) {
      const tabContent = card.querySelector('.tab-content');
      if (tabContent) tabContent.classList.add('d-none');
      const panes = card.querySelectorAll('.tab-pane');
      panes.forEach((pane, idx) => {
          const isOverview = idx === 0;
          pane.classList.toggle('show', isOverview);
          pane.classList.toggle('active', isOverview);
      });
      card.querySelectorAll('.nav-link').forEach((tab, idx) => {
          tab.classList.toggle('active', idx === 0);
      });
  }

  function showTabById(targetId) {
      if (!targetId) return;
      const tabTrigger = document.querySelector(`[data-bs-target="#${targetId}"]`);
      if (!tabTrigger) return;
      const card = tabTrigger.closest('.card');
      if (card) {
          const tabContent = card.querySelector('.tab-content');
          if (tabContent) tabContent.classList.remove('d-none');
      }
      if (window.bootstrap && bootstrap.Tab) {
          new bootstrap.Tab(tabTrigger).show();
      } else {
          tabTrigger.classList.add('active');
          const pane = document.getElementById(targetId);
          if (pane) pane.classList.add('show', 'active');
      }
  }

  const expandAllBtn = document.getElementById('expandAllBtn');
  const collapseAllBtn = document.getElementById('collapseAllBtn');

  if (expandAllBtn) {
      expandAllBtn.addEventListener('click', function() {
          document.querySelectorAll('.dentist-card').forEach(card => expandCard(card));
      });
  }
  if (collapseAllBtn) {
      collapseAllBtn.addEventListener('click', function() {
          document.querySelectorAll('.dentist-card').forEach(card => collapseCard(card));
      });
  }

  document.querySelectorAll('[data-expand-card]').forEach(btn => {
      btn.addEventListener('click', function() {
          const card = this.closest('.card');
          if (card) expandCard(card);
      });
  });
  document.querySelectorAll('[data-collapse-card]').forEach(btn => {
      btn.addEventListener('click', function() {
          const card = this.closest('.card');
          if (card) collapseCard(card);
      });
  });

  // Write current tab to hash so a reload returns to the same tab
  document.querySelectorAll('.nav-link[data-bs-target]').forEach(tab => {
      tab.addEventListener('shown.bs.tab', function() {
          const target = this.getAttribute('data-bs-target');
          if (target) {
              history.replaceState(null, '', target);
          }
      });
  });

  // When saving schedule, jump back to that dentist's schedule tab after reload
  document.querySelectorAll('[data-schedule-save]').forEach(btn => {
      btn.addEventListener('click', function() {
          const id = this.getAttribute('data-schedule-save');
          if (id) {
              history.replaceState(null, '', `#tab-schedule-${id}`);
          }
      });
  });

  // When toggling availability checkbox, store the target tab in sessionStorage before submitting
  document.querySelectorAll('input[name="is_available"][type="checkbox"]').forEach(checkbox => {
      checkbox.addEventListener('change', function() {
          const form = this.closest('.availability-toggle-form');
          if (form) {
              const card = form.closest('.card');
              if (card) {
                  const activeTab = card.querySelector('.nav-link.active');
                  if (activeTab) {
                      const target = activeTab.getAttribute('data-bs-target');
                      if (target) {
                          // Store the target tab in sessionStorage without the # prefix
                          const tabId = target.startsWith('#') ? target.substring(1) : target;
                          sessionStorage.setItem('activeTab', tabId);
                      }
                  }
              }
              // Submit the form after storing the tab
              form.submit();
          }
      });
  });

  // Restore tab from sessionStorage on load (takes priority) or from hash
  window.addEventListener('load', function() {
      const storedTab = sessionStorage.getItem('activeTab');
      if (storedTab) {
          sessionStorage.removeItem('activeTab'); // Clear it after using
          showTabById(storedTab);
      } else if (window.location.hash) {
          const targetId = window.location.hash.substring(1);
          showTabById(targetId);
      }
  });
});
</script>
@endpush
@endsection
