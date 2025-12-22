@extends('layouts.staff')

@section('title', 'Services Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Services</h3>
        <p class="text-muted mb-0">Manage clinic services</p>
    </div>
    <div>
        <button type="button" class="btn btn-outline-danger me-2" id="bulkDeleteBtn" style="display: none;" onclick="confirmBulkDelete()">
            <i class="bi bi-trash me-2"></i>Delete Selected
        </button>
        <a href="/staff/services/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Service
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
        <form id="bulkDeleteForm" method="POST" action="/staff/services/bulk-delete">
            @csrf
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" class="form-check-input" id="selectAllServices" onclick="toggleSelectAllServices(this)"></th>
                            <th>Service Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Duration (min)</th>
                            <th>Status</th>
                            <th>Appointments</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input item-checkbox" name="ids[]" value="{{ $service->id }}" onchange="updateBulkDeleteButton()" @if(isset($service->appointments_count) && $service->appointments_count > 0) disabled title="Cannot bulk delete: has appointment records" @endif>
                                </td>
                                <td class="fw-semibold">{{ $service->name }}</td>
                                <td>{{ Str::limit($service->description ?? '—', 50) }}</td>
                                <td>RM {{ number_format($service->price, 2) }}</td>
                                <td>{{ $service->estimated_duration }} min</td>
                                <td>
                                    <span class="badge {{ $service->status ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $service->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($service->appointments_count))
                                        <span class="badge bg-light text-dark">{{ $service->appointments_count }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="/staff/services/{{ $service->id }}/edit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitServiceDelete({{ $service->id }})" @if(isset($service->appointments_count) && $service->appointments_count > 0) disabled title="Cannot delete: has appointment records" @endif>
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No services found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<!-- Hidden single delete form -->
<form id="singleDeleteServiceForm" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

<script>
function updateBulkDeleteButton() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    bulkDeleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
}

function toggleSelectAllServices(master) {
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
    
    if (confirm(`Delete ${checkedBoxes.length} selected service(s)?`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}

function submitServiceDelete(id) {
    if (!confirm('Delete this service?')) return;
    const form = document.getElementById('singleDeleteServiceForm');
    form.action = `/staff/services/${id}`;
    form.submit();
}
</script>
@endsection
