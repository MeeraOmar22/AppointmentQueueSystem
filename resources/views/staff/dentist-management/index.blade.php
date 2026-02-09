@extends('layouts.staff')

@section('title', 'Dentist Management')

@section('content')
<style>
    .dentist-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
    }
    
    .dentist-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .dentist-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #333;
    }
    
    .dentist-specialization {
        font-size: 0.9rem;
        color: #666;
        margin-top: 0.25rem;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }
    
    .status-available {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-busy {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-break {
        background: #e7d4f5;
        color: #6c3fa0;
    }
    
    .dentist-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-size: 0.95rem;
        color: #333;
        font-weight: 600;
    }
    
    .schedule-section {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .schedule-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0.75rem;
    }
    
    .schedule-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
        font-size: 0.9rem;
        color: #666;
    }
    
    .leave-item {
        background: #fff3cd;
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid #ffc107;
        margin-bottom: 0.5rem;
    }
    
    .leave-date {
        font-weight: 600;
        color: #856404;
    }
    
    .leave-reason {
        font-size: 0.85rem;
        color: #999;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .action-btn {
        padding: 0.4rem 0.8rem;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-edit {
        background: #0d6efd;
        color: white;
    }
    
    .btn-edit:hover {
        background: #0b5ed7;
    }
    
    .btn-schedule {
        background: #6f42c1;
        color: white;
    }
    
    .btn-schedule:hover {
        background: #5a32a3;
    }
    
    .btn-break {
        background: #fd7e14;
        color: white;
    }
    
    .btn-break:hover {
        background: #e07b39;
    }
    
    .btn-deactivate {
        background: #dc3545;
        color: white;
    }
    
    .btn-deactivate:hover {
        background: #c82333;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
        background: white;
        border-radius: 12px;
    }
    
    .empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    .tabs-container {
        margin-bottom: 2rem;
    }
    
    .tab-button {
        padding: 0.75rem 1.5rem;
        background: #f8f9fa;
        border: 2px solid transparent;
        border-bottom: 2px solid #dee2e6;
        cursor: pointer;
        font-weight: 600;
        color: #666;
        transition: all 0.2s ease;
        border-radius: 8px 8px 0 0;
    }
    
    .tab-button.active {
        background: white;
        color: #0d6efd;
        border-bottom-color: #0d6efd;
    }
    
    .modal-content {
        border-radius: 12px;
    }
    
    .modal-header {
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Dentist Overview</h3>
        <p class="text-muted mb-0">View dentist profiles, availability, and schedules</p>
    </div>
    @if(auth()->check() && auth()->user()->role === 'staff')
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveModal">
            <i class="bi bi-plus-circle me-2"></i>Add Leave
        </button>
        <a href="/staff/dentists/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Dentist
        </a>
    </div>
    @else
    <div class="alert alert-info mb-0">
        <i class="bi bi-info-circle me-2"></i>To manage dentists, use <strong>System Config</strong>
    </div>
    @endif
</div>

<!-- Tab Navigation -->
<div class="tabs-container">
    <div class="nav nav-pills mb-3" role="tablist">
        <button class="tab-button active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-pane" role="tab">
            <i class="bi bi-check-circle me-2"></i>Active Dentists
        </button>
        <button class="tab-button" id="inactive-tab" data-bs-toggle="tab" data-bs-target="#inactive-pane" role="tab">
            <i class="bi bi-x-circle me-2"></i>Inactive Dentists
        </button>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content">
    <!-- Active Dentists -->
    <div class="tab-pane fade show active" id="active-pane" role="tabpanel">
        <div id="active-dentists-list">
            <div class="empty-state">
                <div class="empty-icon">👨‍⚕️</div>
                <p>Loading dentists...</p>
            </div>
        </div>
    </div>
    
    <!-- Inactive Dentists -->
    <div class="tab-pane fade" id="inactive-pane" role="tabpanel">
        <div id="inactive-dentists-list">
            <div class="empty-state">
                <div class="empty-icon">✓</div>
                <p>Loading inactive dentists...</p>
            </div>
        </div>
    </div>
</div>

<!-- Add Leave Modal (Staff Only) -->
@if(auth()->check() && auth()->user()->role === 'staff')
<div class="modal fade" id="addLeaveModal" tabindex="-1" aria-labelledby="addLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeaveModalLabel">Add Leave Period</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addLeaveForm" onsubmit="submitLeave(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="leave-dentist" class="form-label fw-bold">Dentist</label>
                        <select id="leave-dentist" class="form-select" required>
                            <option value="" selected disabled>Select dentist</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label for="leave-from" class="form-label fw-bold">From Date</label>
                            <input type="date" id="leave-from" class="form-control" required>
                        </div>
                        <div class="col">
                            <label for="leave-to" class="form-label fw-bold">To Date</label>
                            <input type="date" id="leave-to" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="leave-reason" class="form-label fw-bold">Reason (optional)</label>
                        <input type="text" id="leave-reason" class="form-control" placeholder="e.g., Annual Leave, Sick Leave, Training">
                    </div>
                    <div class="alert alert-info alert-sm">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Dentist will be unavailable for new appointments during this period.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Leave</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
    let allDentists = [];
    const userRole = '{{ auth()->user()->role }}';
    
    async function loadDentists() {
        try {
            const response = await fetch('/api/dentists/stats');
            const data = await response.json();
            allDentists = data.data || [];
            
            renderDentists();
        } catch (error) {
            console.error('Error loading dentists:', error);
        }
    }
    
    function renderDentists() {
        const activeDentists = allDentists.filter(d => d.status !== false);
        const inactiveDentists = allDentists.filter(d => d.status === false);
        
        renderActiveDentists(activeDentists);
        renderInactiveDentists(inactiveDentists);
        populateDentistSelect(activeDentists);
    }
    
    function getActionButtonsHTML(dentistId, dentistName) {
        if (userRole === 'staff') {
            return `
                <a href="/staff/dentists/${dentistId}/edit" class="action-btn btn-edit">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                <button type="button" class="action-btn btn-break" onclick="markBreak(${dentistId})">
                    <i class="bi bi-pause-circle me-1"></i>Mark Break
                </button>
                <button type="button" class="action-btn btn-schedule" data-bs-toggle="modal" data-bs-target="#addLeaveModal" onclick="setDentistId(${dentistId})">
                    <i class="bi bi-calendar-plus me-1"></i>Add Leave
                </button>
                <button type="button" class="action-btn btn-deactivate" onclick="deactivateDentist(${dentistId}, '${dentistName}')">
                    <i class="bi bi-trash me-1"></i>Deactivate
                </button>
            `;
        } else {
            return `<span class="text-muted small">Manage in System Config</span>`;
        }
    }

    
    function renderActiveDentists(dentists) {
        const container = document.getElementById('active-dentists-list');
        
        if (dentists.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">👨‍⚕️</div>
                    <p>No active dentists yet</p>
                    <p style="font-size: 0.9rem; margin-bottom: 1rem;">Add your first dentist to get started</p>
                    <a href="/staff/dentists/create" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Add Dentist
                    </a>
                </div>
            `;
            return;
        }
        
        container.innerHTML = dentists.map(dentist => `
            <div class="dentist-card" id="dentist-${dentist.id}">
                <div class="dentist-header">
                    <div>
                        <div class="dentist-name">${dentist.name}</div>
                        <div class="dentist-specialization">${dentist.specialization || 'General Dentistry'}</div>
                    </div>
                    <div>
                        <span class="status-badge status-active">Active</span>
                        <span class="status-badge" style="margin-left: 0.5rem;" id="status-${dentist.id}">
                            <span class="status-${(dentist.status ? 'available' : 'inactive').toLowerCase()}">
                                ${dentist.status ? 'Available' : 'Inactive'}
                            </span>
                        </span>
                    </div>
                </div>
                
                <div class="dentist-info">
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">${dentist.email || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value">${dentist.phone || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">License</span>
                        <span class="info-value">${dentist.license_number || 'N/A'}</span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    ${getActionButtonsHTML(dentist.id, dentist.name)}
                </div>
            </div>
        `).join('');
    }
    
    function renderInactiveDentists(dentists) {
        const container = document.getElementById('inactive-dentists-list');
        
        if (dentists.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">✓</div>
                    <p>No inactive dentists</p>
                    <p style="font-size: 0.9rem;">All dentists are currently active</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = dentists.map(dentist => `
            <div class="dentist-card">
                <div class="dentist-header">
                    <div>
                        <div class="dentist-name">${dentist.name}</div>
                        <div class="dentist-specialization">${dentist.specialization || 'General Dentistry'}</div>
                    </div>
                    <span class="status-badge status-inactive">Inactive</span>
                </div>
                
                <div class="dentist-info">
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">${dentist.email || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value">${dentist.phone || 'N/A'}</span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="/staff/dentists/${dentist.id}/edit" class="action-btn btn-edit">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <button type="button" class="action-btn" style="background: #198754; color: white;" onclick="reactivateDentist(${dentist.id}, '${dentist.name}')">
                        <i class="bi bi-check-circle me-1"></i>Reactivate
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    function populateDentistSelect(dentists) {
        const select = document.getElementById('leave-dentist');
        const options = dentists.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
        select.innerHTML = '<option value="" selected disabled>Select dentist</option>' + options;
    }
    
    function getStatusLabel(status) {
        const labels = {
            'available': 'Available',
            'busy': 'In Treatment',
            'break': 'On Break'
        };
        return labels[status] || status;
    }
    
    function setDentistId(dentistId) {
        document.getElementById('leave-dentist').value = dentistId;
    }
    
    async function submitLeave(event) {
        event.preventDefault();
        
        const dentistId = document.getElementById('leave-dentist').value;
        const fromDate = document.getElementById('leave-from').value;
        const toDate = document.getElementById('leave-to').value;
        const reason = document.getElementById('leave-reason').value;
        
        if (new Date(toDate) < new Date(fromDate)) {
            alert('End date must be after start date');
            return;
        }
        
        try {
            const response = await fetch(`/staff/dentist-leaves`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ dentist_id: dentistId, from_date: fromDate, to_date: toDate, reason: reason })
            });
            
            if (response.ok) {
                alert('Leave added successfully');
                document.getElementById('addLeaveForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('addLeaveModal')).hide();
                loadDentists();
            } else {
                alert('Error adding leave');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error adding leave');
        }
    }
    
    async function deactivateDentist(dentistId, dentistName) {
        if (!confirm(`Deactivate ${dentistName}? They will no longer be available for new appointments, but their history will be preserved.`)) return;
        
        try {
            const response = await fetch(`/staff/dentists/${dentistId}/deactivate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                loadDentists();
            } else {
                alert('Error deactivating dentist');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deactivating dentist');
        }
    }
    
    async function reactivateDentist(dentistId, dentistName) {
        if (!confirm(`Reactivate ${dentistName}? They will be available for new appointments.`)) return;
        
        try {
            const response = await fetch(`/staff/dentists/${dentistId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: 'active' })
            });
            
            if (response.ok) {
                loadDentists();
            } else {
                alert('Error reactivating dentist');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error reactivating dentist');
        }
    }
    
    async function markBreak(dentistId) {
        const duration = prompt('Break duration in minutes (e.g., 30, 60):', '30');
        if (!duration) return;
        
        try {
            const response = await fetch(`/staff/dentists/${dentistId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: 'break', duration_minutes: parseInt(duration) })
            });
            
            if (response.ok) {
                alert('Break marked. Dentist is now unavailable.');
                loadDentists();
            } else {
                alert('Error setting break');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error setting break');
        }
    }
    
    // Initialize on page load
    loadDentists();
</script>
@endsection
