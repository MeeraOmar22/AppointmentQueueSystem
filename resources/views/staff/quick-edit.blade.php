@extends('layouts.staff')

@section('title', 'Quick Edit Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Quick Edit Dashboard</h3>
        <p class="text-muted mb-0">Quickly update dentists, services, operating hours, and staff visibility</p>
    </div>
</div>

<!-- Quick Actions -->
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="/staff/dentists/create" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Dentist
    </a>
    <a href="/staff/services/create" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Service
    </a>
    <a href="/staff/operating-hours/create" class="btn btn-sm btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Operating Hour
    </a>
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
        <i class="bi bi-plus-circle me-1"></i> Add Staff
    </button>
    <div class="ms-auto text-muted small d-none d-md-block align-self-center">
        Quick actions for fast setup
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Status Control:</strong> Use <strong>Activate/Deactivate</strong> to control visibility on the public website. 
    <ul class="mb-0 mt-2">
        <li><strong>Active:</strong> Visible to public visitors on the website</li>
        <li><strong>Inactive:</strong> Hidden from public but preserved with appointment history</li>
        <li>Items with appointments cannot be deleted — deactivate them instead to hide from website</li>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Update failed:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Total Dentists</p>
                        <h4 class="fw-bold mb-0">{{ $stats['dentists']['total'] }}</h4>
                    </div>
                    <i class="bi bi-person-circle text-primary" style="font-size: 2rem;"></i>
                </div>
                <small class="text-muted">
                    <span class="badge bg-success">{{ $stats['dentists']['active'] }} Active</span>
                    <span class="badge bg-secondary">{{ $stats['dentists']['inactive'] }} Inactive</span>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Total Services</p>
                        <h4 class="fw-bold mb-0">{{ $stats['services']['total'] }}</h4>
                    </div>
                    <i class="bi bi-gear text-info" style="font-size: 2rem;"></i>
                </div>
                <small class="text-muted">
                    <span class="badge bg-success">{{ $stats['services']['active'] }} Active</span>
                    <span class="badge bg-secondary">{{ $stats['services']['inactive'] }} Inactive</span>
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Operating Hours</p>
                        <h4 class="fw-bold mb-0">{{ $operatingHours->count() }}</h4>
                    </div>
                    <i class="bi bi-clock text-warning" style="font-size: 2rem;"></i>
                </div>
                <small class="text-muted d-block">Days configured</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Manage All</p>
                        <h5 class="fw-bold mb-0">4 Modules</h5>
                    </div>
                    <i class="bi bi-lightning text-success" style="font-size: 2rem;"></i>
                </div>
                <small class="text-muted d-block">Click edit to manage</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Content -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <ul class="nav nav-tabs card-header-tabs" role="tablist" style="gap: 0.5rem;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold px-4 py-2 text-dark" id="dentists-tab" data-bs-toggle="tab" data-bs-target="#dentists-content" type="button" role="tab">
                    <i class="bi bi-person-circle me-2"></i>Dentists
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="services-tab" data-bs-toggle="tab" data-bs-target="#services-content" type="button" role="tab">
                    <i class="bi bi-wrench me-2"></i>Services
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="hours-tab" data-bs-toggle="tab" data-bs-target="#hours-content" type="button" role="tab">
                    <i class="bi bi-calendar-event me-2"></i>Operating Hours
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold px-4 py-2 text-secondary" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-content" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Staff
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            <!-- DENTISTS TAB -->
            <div class="tab-pane fade show active" id="dentists-content" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <a href="/staff/dentists/create" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Dentist
                    </a>
                </div>
                @if($dentists->isEmpty())
                    <p class="text-muted text-center py-4">No dentists added yet. <a href="/staff/dentists/create">Create one</a></p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Specialization</th>
                                    <th>Status</th>
                                    <th>Experience</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dentists as $dentist)
                                    <tr>
                                        <td>
                                            <strong>{{ $dentist->name }}</strong>
                                            @if($dentist->appointments_count > 0)
                                                <br><small class="text-muted"><i class="bi bi-calendar-check"></i> {{ $dentist->appointments_count }} appointment(s)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $dentist->specialization ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $dentist->status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $dentist->status ? 'Active (Public)' : 'Inactive (Hidden)' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $dentist->years_of_experience ?? '—' }} years
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDentistModal{{ $dentist->id }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            @if($dentist->status)
                                                <form method="POST" action="/staff/dentists/{{ $dentist->id }}/status" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="0">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Deactivate this dentist?')">
                                                        <i class="bi bi-toggle-off"></i> Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="/staff/dentists/{{ $dentist->id }}/status" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-toggle-on"></i> Activate
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="/staff/dentists/{{ $dentist->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this dentist? They will be moved to Past Records.')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Dentist Modal -->
                                    <div class="modal fade" id="editDentistModal{{ $dentist->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Dentist</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="/staff/dentists/{{ $dentist->id }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" class="form-control" name="name" value="{{ $dentist->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Specialization</label>
                                                            <input type="text" class="form-control" name="specialization" value="{{ $dentist->specialization }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Years of Experience</label>
                                                            <input type="number" class="form-control" name="years_of_experience" value="{{ $dentist->years_of_experience }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Bio</label>
                                                            <textarea class="form-control" name="bio" rows="3">{{ $dentist->bio }}</textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Twitter URL</label>
                                                                <input type="url" class="form-control" name="twitter_url" value="{{ $dentist->twitter_url }}" placeholder="https://twitter.com/username">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Facebook URL</label>
                                                                <input type="url" class="form-control" name="facebook_url" value="{{ $dentist->facebook_url }}" placeholder="https://facebook.com/username">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">LinkedIn URL</label>
                                                                <input type="url" class="form-control" name="linkedin_url" value="{{ $dentist->linkedin_url }}" placeholder="https://linkedin.com/in/username">
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Instagram URL</label>
                                                                <input type="url" class="form-control" name="instagram_url" value="{{ $dentist->instagram_url }}" placeholder="https://instagram.com/username">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- SERVICES TAB -->
            <div class="tab-pane fade" id="services-content" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <a href="/staff/services/create" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Service
                    </a>
                </div>
                @if($services->isEmpty())
                    <p class="text-muted text-center py-4">No services added yet. <a href="/staff/services/create">Create one</a></p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Service Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $service)
                                    <tr>
                                        <td>
                                            <strong>{{ $service->name }}</strong>
                                            @if($service->appointments_count > 0)
                                                <br><small class="text-muted"><i class="bi bi-calendar-check"></i> {{ $service->appointments_count }} appointment(s)</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-success fw-bold">RM {{ number_format($service->price, 2) }}</span>
                                        </td>
                                        <td>
                                            {{ $service->duration_minutes ?? $service->estimated_duration }} min
                                        </td>
                                        <td>
                                            <span class="badge {{ $service->status ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $service->status ? 'Active (Public)' : 'Inactive (Hidden)' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editServiceModal{{ $service->id }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            @if($service->status)
                                                <form method="POST" action="/staff/services/{{ $service->id }}/status" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="0">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('Deactivate this service?')">
                                                        <i class="bi bi-toggle-off"></i> Off
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="/staff/services/{{ $service->id }}/status" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-toggle-on"></i> On
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="/staff/services/{{ $service->id }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this service? It will be moved to Past Records.')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Service Modal -->
                                    <div class="modal fade" id="editServiceModal{{ $service->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Service</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="/staff/services/{{ $service->id }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Service Name</label>
                                                            <input type="text" class="form-control" name="name" value="{{ $service->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Description</label>
                                                            <textarea class="form-control" name="description" rows="3">{{ $service->description }}</textarea>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Price (RM)</label>
                                                                <input type="number" class="form-control" name="price" step="0.01" value="{{ $service->price }}" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Duration (minutes)</label>
                                                                <input type="number" class="form-control" name="duration_minutes" value="{{ $service->duration_minutes ?? $service->estimated_duration }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- OPERATING HOURS TAB -->
            <div class="tab-pane fade" id="hours-content" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <a href="/staff/operating-hours/create" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Operating Hour
                    </a>
                </div>
                @if($operatingHours->isEmpty())
                    <p class="text-muted text-center py-4">No operating hours configured. <a href="/staff/operating-hours/create">Add hours</a></p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="border-right: none;">Day</th>
                                    <th style="border-left: none;">Session</th>
                                    <th>Hours</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    @if(isset($operatingHours[$day]))
                                        @foreach($operatingHours[$day] as $index => $hour)
                                            <tr>
                                                @if($index === 0)
                                                    <td rowspan="{{ $operatingHours[$day]->count() }}" class="fw-bold text-primary" style="border-right: none; border-left: none;">
                                                        {{ $day }}
                                                    </td>
                                                @endif
                                                <td style="border-left: none;">
                                                    @if($hour->session_label)
                                                        <span class="badge bg-light text-dark">{{ $hour->session_label }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($hour->is_closed)
                                                        <span class="badge bg-danger">Closed</span>
                                                    @else
                                                        {{ \Carbon\Carbon::parse($hour->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($hour->end_time)->format('h:i A') }}
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editHoursModal{{ $hour->id }}">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <form method="POST" action="/staff/operating-hours/{{ $hour->id }}/duplicate" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Duplicate this slot to edit the copy">
                                                            <i class="bi bi-files"></i> Duplicate
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <!-- Edit Hours Modal -->
                                            <div class="modal fade" id="editHoursModal{{ $hour->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Operating Hours - {{ $day }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="/staff/operating-hours/{{ $hour->id }}">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Day</label>
                                                                    <select class="form-select" name="day_of_week" required>
                                                                        <option value="Monday" {{ $hour->day_of_week=='Monday' ? 'selected' : '' }}>Monday</option>
                                                                        <option value="Tuesday" {{ $hour->day_of_week=='Tuesday' ? 'selected' : '' }}>Tuesday</option>
                                                                        <option value="Wednesday" {{ $hour->day_of_week=='Wednesday' ? 'selected' : '' }}>Wednesday</option>
                                                                        <option value="Thursday" {{ $hour->day_of_week=='Thursday' ? 'selected' : '' }}>Thursday</option>
                                                                        <option value="Friday" {{ $hour->day_of_week=='Friday' ? 'selected' : '' }}>Friday</option>
                                                                        <option value="Saturday" {{ $hour->day_of_week=='Saturday' ? 'selected' : '' }}>Saturday</option>
                                                                        <option value="Sunday" {{ $hour->day_of_week=='Sunday' ? 'selected' : '' }}>Sunday</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Session Label</label>
                                                                    <input type="text" class="form-control" name="session_label" value="{{ $hour->session_label }}" placeholder="e.g. Morning, Afternoon, Evening">
                                                                </div>
                                                                <div class="form-check mb-3">
                                                                    <input class="form-check-input" type="checkbox" id="isClosed{{ $hour->id }}" name="is_closed" value="1" {{ $hour->is_closed ? 'checked' : '' }} onchange="toggleTimeInputs(this)">
                                                                    <label class="form-check-label" for="isClosed{{ $hour->id }}">
                                                                        Closed on this day
                                                                    </label>
                                                                </div>
                                                                <div id="timeInputs{{ $hour->id }}" {{ $hour->is_closed ? 'style=display:none;' : '' }}>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Opening Time</label>
                                                                        <input type="time" class="form-control" name="start_time" value="{{ $hour->start_time ? \Carbon\Carbon::parse($hour->start_time)->format('H:i') : '' }}" {{ $hour->is_closed ? 'disabled' : 'required' }}>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Closing Time</label>
                                                                        <input type="time" class="form-control" name="end_time" value="{{ $hour->end_time ? \Carbon\Carbon::parse($hour->end_time)->format('H:i') : '' }}" {{ $hour->is_closed ? 'disabled' : 'required' }}>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- STAFF TAB -->
            <div class="tab-pane fade" id="staff-content" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Staff
                    </button>
                </div>
                @if($staff->isEmpty())
                    <p class="text-muted text-center py-4">No staff found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Visibility</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staff as $member)
                                    <tr>
                                        <td><strong>{{ $member->name }}</strong></td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->phone ?? '—' }}</td>
                                        <td>{{ $member->position ?? '—' }}</td>
                                        <td>
                                            <span class="badge {{ ($member->public_visible ?? true) ? 'bg-success' : 'bg-secondary' }}">
                                                {{ ($member->public_visible ?? true) ? 'Visible (Public)' : 'Hidden' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $member->id }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            @if($member->public_visible ?? true)
                                                <form method="POST" action="/staff/users/{{ $member->id }}/visibility" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="public_visible" value="0">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-toggle-off"></i> Hide
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="/staff/users/{{ $member->id }}/visibility" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="public_visible" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-toggle-on"></i> Show
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="/staff/users/{{ $member->id }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member? This will permanently remove their account.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Edit Staff Modal -->
                                    <div class="modal fade" id="editStaffModal{{ $member->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Staff</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="/staff/users/{{ $member->id }}" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Name</label>
                                                            <input type="text" class="form-control" name="name" value="{{ $member->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Position/Title</label>
                                                            <input type="text" class="form-control" name="position" value="{{ $member->position }}" placeholder="e.g. Receptionist, Dental Assistant">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Phone</label>
                                                            <input type="text" class="form-control" name="phone" value="{{ $member->phone }}" placeholder="e.g. 06-677 1940">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Photo</label>
                                                            <input type="file" class="form-control" name="photo" accept="image/*">
                                                            @if($member->photo)
                                                                <small class="text-muted d-block mt-1">Current: <a href="/{{ $member->photo }}" target="_blank">View</a></small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/staff/users" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position/Title</label>
                        <input type="text" class="form-control" name="position" placeholder="e.g. Receptionist, Dental Assistant">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" placeholder="e.g. 06-677 1940">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="visibleCheck" name="public_visible" value="1" checked>
                        <label class="form-check-label" for="visibleCheck">
                            Visible on public pages
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Staff</button>
                </div>
            </form>
        </div>
    </div>
    </div>
<!-- End Add Staff Modal -->

<style>
    /* Tab styling for better user experience */
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

    .nav-tabs .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        right: 0;
        height: 3px;
        background-color: #0d6efd;
    }
</style>

<script>
function toggleTimeInputs(checkbox) {
    const timeInputsDiv = document.getElementById('timeInputs' + checkbox.id.replace('isClosed', ''));
    const startInput = timeInputsDiv?.querySelector('input[name="start_time"]');
    const endInput = timeInputsDiv?.querySelector('input[name="end_time"]');
    if (checkbox.checked) {
        timeInputsDiv.style.display = 'none';
        if (startInput) { startInput.disabled = true; startInput.required = false; }
        if (endInput) { endInput.disabled = true; endInput.required = false; }
    } else {
        timeInputsDiv.style.display = 'block';
        if (startInput) { startInput.disabled = false; startInput.required = true; }
        if (endInput) { endInput.disabled = false; endInput.required = true; }
    }
}
</script>
@endsection
