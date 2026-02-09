@extends('layouts.staff')

@section('title', 'Past Records')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Past Records</h3>
        <p class="text-muted mb-0">Manage deleted dentists, staff members, services, and appointments</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist" style="gap: 0.5rem;">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold px-4 py-2 text-dark" id="dentists-tab" data-bs-toggle="tab" data-bs-target="#dentists-content" type="button" role="tab">
            <i class="bi bi-person-badge me-2"></i>Past Dentists
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-content" type="button" role="tab">
            <i class="bi bi-people me-2"></i>Past Staff
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="services-tab" data-bs-toggle="tab" data-bs-target="#services-content" type="button" role="tab">
            <i class="bi bi-tools me-2"></i>Past Services
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments-content" type="button" role="tab">
            <i class="bi bi-calendar-check me-2"></i>Past Appointments
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- PAST DENTISTS TAB -->
    <div class="tab-pane fade show active" id="dentists-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($pastDentists->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                        <p class="text-muted mt-3">No deleted dentists found</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Experience</th>
                                    <th>Deleted Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastDentists as $dentist)
                                    <tr>
                                        <td>
                                            <strong>{{ $dentist->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $dentist->specialization ?? '—' }}</span>
                                        </td>
                                        <td>
                                            {{ $dentist->years_of_experience ?? '—' }} years
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $dentist->deleted_at->format('d M Y H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/staff/past/dentists/{{ $dentist->id }}/restore" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this dentist?')">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                            <form method="POST" action="/staff/past/dentists/{{ $dentist->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this dentist? This cannot be undone.')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PAST STAFF TAB -->
    <div class="tab-pane fade" id="staff-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($pastStaff->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                        <p class="text-muted mt-3">No deleted staff members found</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Position</th>
                                    <th>Deleted Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastStaff as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-muted text-break">{{ $member->email }}</span>
                                        </td>
                                        <td>
                                            {{ $member->position ?? '—' }}
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $member->deleted_at->format('d M Y H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/staff/past/staff/{{ $member->id }}/restore" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this staff member?')">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                            <form method="POST" action="/staff/past/staff/{{ $member->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this staff member? This cannot be undone.')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PAST SERVICES TAB -->
    <div class="tab-pane fade" id="services-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($pastServices->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                        <p class="text-muted mt-3">No deleted services found</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Deleted Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastServices as $service)
                                    <tr>
                                        <td>
                                            <strong>{{ $service->name }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $service->description ?? '—' }}</span>
                                        </td>
                                        <td>
                                            @if($service->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $service->deleted_at->format('d M Y H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/staff/past/services/{{ $service->id }}/restore" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this service?')">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                            <form method="POST" action="/staff/past/services/{{ $service->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this service? This cannot be undone.')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- PAST APPOINTMENTS TAB -->
    <div class="tab-pane fade" id="appointments-content" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @if($pastAppointments->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                        <p class="text-muted mt-3">No deleted appointments found</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Dentist</th>
                                    <th>Service</th>
                                    <th>Appointment Date</th>
                                    <th>Status</th>
                                    <th>Deleted Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastAppointments as $appointment)
                                    <tr>
                                        <td>
                                            <strong>{{ $appointment->patient_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $appointment->patient_phone }}</small>
                                        </td>
                                        <td>
                                            {{ $appointment->dentist->full_name ?? '—' }}
                                        </td>
                                        <td>
                                            {{ $appointment->service->name ?? '—' }}
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $appointment->status->value === 'completed' ? 'success' : ($appointment->status->value === 'cancelled' ? 'danger' : ($appointment->status->value === 'no_show' ? 'warning' : 'info')) }}">
                                                {{ $appointment->status->label() }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $appointment->deleted_at->format('d M Y H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <form method="POST" action="/staff/past/appointments/{{ $appointment->id }}/restore" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this appointment?')">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                                                </button>
                                            </form>
                                            <form method="POST" action="/staff/past/appointments/{{ $appointment->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this appointment? This cannot be undone.')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-4 p-3 bg-light rounded">
    <i class="bi bi-info-circle me-2"></i>
    <strong>How it works:</strong> When you delete a dentist, staff member, service, or appointment, they are moved to this page instead of being permanently removed. You can restore them at any time, or permanently delete them if needed.
</div>

<style>
    /* Tab styling for consistency */
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #0d6efd !important;
        border-bottom-color: #e9ecef;
    }

    .nav-tabs .nav-link.active {
        color: #212529 !important;
        background-color: transparent;
        border-bottom-color: #0d6efd;
    }
</style>

@endsection
