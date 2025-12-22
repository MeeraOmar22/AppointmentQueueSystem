@extends('layouts.staff')

@section('title', 'Dentists Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dentists</h3>
        <p class="text-muted mb-0">Manage dentist profiles</p>
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
        <form id="bulkDeleteForm" method="POST" action="{{ route('dentists.bulkDestroy') }}" novalidate>
            @csrf
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-outline-danger me-2" id="bulkDeleteBtn" style="display: none;" onclick="handleBulkDelete()">
                    <i class="bi bi-trash me-2"></i>Delete Selected
                </button>
                <a href="/staff/dentists/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Add Dentist
                </a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll" onclick="toggleSelectAll(this)">
                            </th>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Appointments</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dentists as $dentist)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input item-checkbox" name="ids[]" value="{{ $dentist->id }}" onchange="updateBulkDeleteButton()"
                                        @if(isset($dentist->appointments_count) && $dentist->appointments_count > 0) disabled title="Cannot bulk delete: has appointment records" @endif>
                                </td>
                                <td class="fw-semibold">{{ $dentist->name }}</td>
                                <td>{{ $dentist->specialization ?? '—' }}</td>
                                <td>{{ $dentist->email ?? '—' }}</td>
                                <td>{{ $dentist->phone ?? '—' }}</td>
                                <td>
                                    <span class="badge {{ $dentist->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $dentist->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($dentist->appointments_count))
                                        <span class="badge bg-light text-dark">{{ $dentist->appointments_count }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="/staff/dentists/{{ $dentist->id }}/edit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="submitSingleDelete({{ $dentist->id }})"
                                            @if(isset($dentist->appointments_count) && $dentist->appointments_count > 0) disabled title="Cannot delete: has appointment records" @endif>
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                    @if(isset($dentist->appointments_count) && $dentist->appointments_count > 0 && $dentist->status)
                                    <button type="button" class="btn btn-sm btn-outline-warning ms-1" onclick="submitDeactivate({{ $dentist->id }})" title="Deactivate this dentist">
                                        <i class="bi bi-slash-circle"></i> Deactivate
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No dentists found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        <!-- Hidden single delete form (kept outside of bulk form to avoid nesting) -->
        <form id="singleDeleteForm" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>

        <!-- Hidden status action form (deactivate) -->
        <form id="statusForm" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>

<script>
function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    bulkDeleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
}

function toggleSelectAll(master) {
    const items = document.querySelectorAll('.item-checkbox');
    items.forEach(cb => {
        cb.checked = master.checked;
    });
    updateBulkDeleteButton();
}

function handleBulkDelete() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Please select items to delete.');
        return;
    }
    if (!confirm(`Delete ${checkedBoxes.length} selected dentist(s)?`)) {
        return;
    }
    const form = document.getElementById('bulkDeleteForm');
    form.submit();
}

function submitSingleDelete(id) {
    if (!confirm('Delete this dentist?')) {
        return false;
    }
    const form = document.getElementById('singleDeleteForm');
    form.action = `/staff/dentists/${id}`;
    form.submit();
}

function submitDeactivate(id) {
    if (!confirm('Deactivate this dentist? They will no longer be selectable in bookings.')) {
        return false;
    }
    const form = document.getElementById('statusForm');
    form.action = `/staff/dentists/${id}/deactivate`;
    form.submit();
}
</script>
@endsection
