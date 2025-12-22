@extends('layouts.staff')

@section('title', 'Operating Hours Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Operating Hours</h3>
        <p class="text-muted mb-0">Manage clinic operating hours</p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-danger me-2" id="bulkDeleteBtn" style="display: none;" onclick="confirmBulkDelete()">
            <i class="bi bi-trash me-2"></i>Delete Selected
        </button>
        <a href="/staff/operating-hours/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add New
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Action failed:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card table-card">
    <div class="card-body">
        <form id="bulkDeleteForm" method="POST" action="/staff/operating-hours/bulk-delete">
            @csrf
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAllOperatingHours" onclick="toggleSelectAllOperatingHours(this)"></th>
                            <th style="border-right: none;">Day</th>
                            <th style="border-left: none;">Session</th>
                            <th>Hours</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $hasHours = false; @endphp
                        @foreach($daysOfWeek as $day)
                            @if(isset($hours[$day]) && $hours[$day]->count() > 0)
                                @php $hasHours = true; @endphp
                                @foreach($hours[$day] as $index => $hour)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input item-checkbox" name="ids[]" value="{{ $hour->id }}" onchange="updateBulkDeleteButton()">
                                        </td>
                                        @if($index === 0)
                                            <td rowspan="{{ $hours[$day]->count() }}" class="fw-bold text-primary" style="vertical-align: middle; border-right: none; border-left: none;">
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
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitOperatingHourDelete({{ $hour->id }})">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
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
                                                                <input type="time" class="form-control" name="start_time" value="{{ \Carbon\Carbon::parse($hour->start_time)->format('H:i') }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Closing Time</label>
                                                                <input type="time" class="form-control" name="end_time" value="{{ \Carbon\Carbon::parse($hour->end_time)->format('H:i') }}" required>
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
                        @if(!$hasHours)
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No operating hours configured.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Hidden single delete form -->
<form id="singleDeleteOperatingHoursForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

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
function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    bulkDeleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
}

function toggleSelectAllOperatingHours(master) {
    const boxes = document.querySelectorAll('.item-checkbox');
    boxes.forEach(cb => {
        if (!cb.disabled) {
            cb.checked = master.checked;
        }
    });
    updateBulkDeleteButton();
}

function confirmBulkDelete() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select items to delete.');
        return;
    }
    
    if (confirm(`Delete ${checkedBoxes.length} selected item(s)?`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}

function submitOperatingHourDelete(id) {
    if (!confirm('Delete this operating hour?')) return;
    const form = document.getElementById('singleDeleteOperatingHoursForm');
    form.action = `/staff/operating-hours/${id}`;
    form.submit();
}
</script>
@endsection
