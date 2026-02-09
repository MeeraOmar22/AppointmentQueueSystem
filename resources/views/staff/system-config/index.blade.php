@extends('layouts.staff')

@section('title', 'System Configuration')

@section('content')
<style>
    .config-section {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #0d6efd;
    }
    
    .config-section-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .config-section-title i {
        font-size: 1.5rem;
    }
    
    .quick-add-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .quick-add-btn {
        padding: 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    
    .quick-add-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .status-control-table {
        width: 100%;
    }
    
    .status-control-table thead {
        background: #f8f9fa;
    }
    
    .status-control-table th {
        padding: 1rem;
        text-align: left;
        font-weight: 700;
        color: #333;
        border-bottom: 2px solid #dee2e6;
    }
    
    .status-control-table td {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
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
    
    .toggle-btn {
        padding: 0.4rem 1rem;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .toggle-btn.activate {
        color: #198754;
        border-color: #198754;
    }
    
    .toggle-btn.activate:hover {
        background: #198754;
        color: white;
    }
    
    .toggle-btn.deactivate {
        color: #dc3545;
        border-color: #dc3545;
    }
    
    .toggle-btn.deactivate:hover {
        background: #dc3545;
        color: white;
    }
    
    .warning-banner {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        display: flex;
        gap: 1rem;
    }
    
    .warning-icon {
        font-size: 1.5rem;
        color: #856404;
    }
    
    .warning-content {
        color: #856404;
    }
    
    .warning-content strong {
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }
    
    .empty-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    
    @media (max-width: 768px) {
        .quick-add-buttons {
            grid-template-columns: 1fr;
        }
        
        .status-control-table {
            font-size: 0.9rem;
        }
        
        .status-control-table th,
        .status-control-table td {
            padding: 0.75rem;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">System Configuration</h3>
        <p class="text-muted mb-0">Admin panel for clinic configuration and status management</p>
    </div>
</div>

<!-- Warning Banner -->
<div class="warning-banner">
    <div class="warning-icon">⚠️</div>
    <div class="warning-content">
        <strong>Admin Only</strong>
        Changes made here affect all staff and the public website. Exercise caution when deactivating items with appointments.
    </div>
</div>

<!-- Quick Add Section -->
<div class="config-section">
    <div class="config-section-title">
        <i class="bi bi-plus-circle"></i>Quick Add
    </div>
    <div class="quick-add-buttons">
        <a href="/staff/dentists/create" class="quick-add-btn">
            <i class="bi bi-person-plus"></i> Add Dentist
        </a>
        <a href="/staff/services/create" class="quick-add-btn">
            <i class="bi bi-plus-square"></i> Add Service
        </a>
        <a href="/staff/operating-hours/create" class="quick-add-btn">
            <i class="bi bi-clock-history"></i> Add Operating Hour
        </a>
    </div>
</div>

<!-- Dentists Status Control -->
<div class="config-section">
    <div class="config-section-title">
        <i class="bi bi-person-badge"></i>Dentist Status
    </div>
    <div id="dentists-table-container">
        <div class="empty-state">
            <div class="empty-icon">👨‍⚕️</div>
            <p>Loading dentists...</p>
        </div>
    </div>
</div>

<!-- Services Status Control -->
<div class="config-section">
    <div class="config-section-title">
        <i class="bi bi-grid-3x3-gap-fill"></i>Service Status
    </div>
    <div id="services-table-container">
        <div class="empty-state">
            <div class="empty-icon">🛠️</div>
            <p>Loading services...</p>
        </div>
    </div>
</div>

<!-- Operating Hours Status Control -->
<div class="config-section">
    <div class="config-section-title">
        <i class="bi bi-clock-history"></i>Operating Hours
    </div>
    <div id="operating-hours-table-container">
        <div class="empty-state">
            <div class="empty-icon">⏰</div>
            <p>Loading operating hours...</p>
        </div>
    </div>
</div>

<script>
    let allDentists = [];
    let allServices = [];
    let allOperatingHours = [];

    async function loadConfigData() {
        try {
            console.log("Starting loadConfigData...");
            
            const fetchOptions = {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json'
                }
            };
            
            const [dentistsRes, servicesRes, operatingHoursRes] = await Promise.all([
                fetch('/api/dentists/stats', fetchOptions),
                fetch('/api/services', fetchOptions),
                fetch('/api/operating-hours', fetchOptions)
            ]);
            
            console.log("Dentists Status:", dentistsRes.status);
            console.log("Services Status:", servicesRes.status);
            console.log("Operating Hours Status:", operatingHoursRes.status);
            
            if (!dentistsRes.ok || !servicesRes.ok || !operatingHoursRes.ok) {
                throw new Error('API response error');
            }
            
            const dentistsJson = await dentistsRes.json();
            const servicesJson = await servicesRes.json();
            const operatingHoursJson = await operatingHoursRes.json();
            
            allDentists = dentistsJson.data ?? [];
            allServices = servicesJson.data ?? [];
            allOperatingHours = operatingHoursJson.data ?? [];
            
            console.log("Dentists loaded:", allDentists);
            console.log("Services loaded:", allServices);
            console.log("Operating Hours loaded:", allOperatingHours);
            
            renderDentistsTable(allDentists);
            renderServicesTable(allServices);
            renderOperatingHoursTable(allOperatingHours);
            
            console.log("All tables rendered successfully");
        } catch (error) {
            console.error('Error loading config data:', error);
            document.getElementById('dentists-table-container').innerHTML = '<div class="alert alert-danger">Error loading dentists: ' + error.message + '</div>';
            document.getElementById('services-table-container').innerHTML = '<div class="alert alert-danger">Error loading services: ' + error.message + '</div>';
            document.getElementById('operating-hours-table-container').innerHTML = '<div class="alert alert-danger">Error loading operating hours: ' + error.message + '</div>';
        }
    }
    
    function renderDentistsTable(dentists) {
        const container = document.getElementById('dentists-table-container');
        
        if (!dentists || dentists.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">👨‍⚕️</div><p>No dentists configured</p></div>';
            return;
        }
        
        let html = '<table class="status-control-table"><thead><tr><th>Name</th><th>Specialization</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        dentists.forEach(dentist => {
            const id = dentist.id || 'unknown';
            const name = dentist.name || 'Unknown';
            const spec = dentist.specialization || 'General Dentistry';
            const isActive = dentist.status !== false && dentist.status !== 'inactive';
            const statusClass = isActive ? 'status-active' : 'status-inactive';
            const statusText = isActive ? 'Active' : 'Inactive';
            const btnText = isActive ? 'Deactivate' : 'Activate';
            const btnClass = isActive ? 'deactivate' : 'activate';
            const action = isActive ? 'deactivate' : 'activate';
            
            html += `<tr><td><strong>${name}</strong></td><td>${spec}</td><td><span class="status-badge ${statusClass}">${statusText}</span></td><td><a href="/staff/dentists/${id}/edit" class="toggle-btn" style="text-decoration: none; display: inline-block; margin-right: 5px;">Edit</a><button class="toggle-btn ${btnClass}" onclick="toggleDentistStatus(${id}, '${action}', '${name}')" style="margin-left: 5px;">${btnText}</button></td></tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    function renderServicesTable(services) {
        const container = document.getElementById('services-table-container');
        
        if (!services || services.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">🛠️</div><p>No services configured</p></div>';
            return;
        }
        
        let html = '<table class="status-control-table"><thead><tr><th>Service Name</th><th>Price</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        services.forEach(service => {
            const id = service.id || 'unknown';
            const name = service.name || 'Unknown';
            const price = parseFloat(service.price || 0).toFixed(2);
            const duration = service.duration || 0;
            const isActive = service.status ? true : false;
            const statusClass = isActive ? 'status-active' : 'status-inactive';
            const statusText = isActive ? 'Active' : 'Inactive';
            const btnText = isActive ? 'Deactivate' : 'Activate';
            const btnClass = isActive ? 'deactivate' : 'activate';
            const action = isActive ? 'deactivate' : 'activate';
            
            html += `<tr><td><strong>${name}</strong></td><td>RM ${price}</td><td>${duration} mins</td><td><span class="status-badge ${statusClass}">${statusText}</span></td><td><a href="/staff/services/${id}/edit" class="toggle-btn" style="text-decoration: none; display: inline-block; margin-right: 5px;">Edit</a><button class="toggle-btn ${btnClass}" onclick="toggleServiceStatus(${id}, '${action}', '${name}')" style="margin-left: 5px;">${btnText}</button></td></tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    function renderOperatingHoursTable(hours) {
        const container = document.getElementById('operating-hours-table-container');
        
        if (!hours || hours.length === 0) {
            container.innerHTML = '<div class="empty-state"><div class="empty-icon">⏰</div><p>No operating hours configured</p></div>';
            return;
        }
        
        let html = '<table class="status-control-table"><thead><tr><th>Day</th><th>Session</th><th>Opens</th><th>Closes</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        hours.forEach(hour => {
            const id = hour.id || 'unknown';
            const day = hour.day_of_week || 'Unknown';
            const session = hour.session_label || '-';
            const opens = hour.opening_time || 'N/A';
            const closes = hour.closing_time || 'N/A';
            const isActive = hour.is_active ? true : false;
            const statusClass = isActive ? 'status-active' : 'status-inactive';
            const statusText = isActive ? 'Open' : 'Closed';
            const btnText = isActive ? 'Deactivate' : 'Activate';
            const btnClass = isActive ? 'deactivate' : 'activate';
            const action = isActive ? 'deactivate' : 'activate';
            
            html += `<tr><td><strong>${day}</strong></td><td>${session}</td><td>${opens}</td><td>${closes}</td><td><span class="status-badge ${statusClass}">${statusText}</span></td><td><a href="/staff/operating-hours/${id}/edit" class="toggle-btn" style="text-decoration: none; display: inline-block; margin-right: 5px;">Edit</a><button class="toggle-btn ${btnClass}" onclick="toggleOperatingHourStatus(${id}, '${action}', '${day}')" style="margin-left: 5px;">${btnText}</button></td></tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    async function toggleDentistStatus(dentistId, action, dentistName) {
        const message = action === 'deactivate' 
            ? `Deactivate ${dentistName}? Their history will be preserved but they won't be available for new appointments.`
            : `Reactivate ${dentistName}? They will be available for new appointments.`;
        
        if (!confirm(message)) return;
        
        try {
            const endpoint = action === 'deactivate' 
                ? `/staff/dentists/${dentistId}/deactivate`
                : `/staff/dentists/${dentistId}/update`;
            
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: action === 'deactivate' ? false : true })
            });
            
            if (response.ok) {
                loadConfigData();
            } else {
                const errorData = await response.json().catch(() => ({}));
                alert(`Error updating status: ${response.status} ${response.statusText}. ${errorData.message || ''}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating status: ' + error.message);
        }
    }
    
    async function toggleOperatingHourStatus(hourId, action, dayName) {
        const message = action === 'deactivate' 
            ? `Deactivate ${dayName}? The clinic will not be available on this day.`
            : `Reactivate ${dayName}? The clinic will be available on this day.`;
        
        if (!confirm(message)) return;
        
        try {
            const response = await fetch(`/staff/operating-hours/${hourId}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    is_closed: action === 'deactivate'
                })
            });
            
            if (response.ok) {
                loadConfigData();
            } else {
                const errorData = await response.json().catch(() => ({}));
                alert(`Error updating status: ${response.status} ${response.statusText}. ${errorData.message || ''}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating status: ' + error.message);
        }
    }
    
    async function toggleServiceStatus(serviceId, action, serviceName) {
        const message = action === 'deactivate' 
            ? `Deactivate ${serviceName}? It will be hidden from the public website.`
            : `Reactivate ${serviceName}? It will be visible on the public website.`;
        
        if (!confirm(message)) return;
        
        try {
            const response = await fetch(`/staff/services/${serviceId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    status: action === 'deactivate' ? 0 : 1
                })
            });
            
            if (response.ok) {
                loadConfigData();
            } else {
                const errorData = await response.json().catch(() => ({}));
                alert(`Error updating status: ${response.status} ${response.statusText}. ${errorData.message || ''}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating status: ' + error.message);
        }
    }

    
    // Load data on page load
    loadConfigData();
</script>
@endsection
