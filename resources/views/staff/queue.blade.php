@extends('layouts.staff')

@section('title', 'Live Queue Board')

@section('content')
<style>
    /* ============ HEADER ============ */
    .queue-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid #e9ecef;
    }
    
    .queue-header-content h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.25rem;
    }
    
    .queue-header-content p {
        font-size: 0.95rem;
        color: #7f8c8d;
        margin: 0;
    }
    
    .queue-header-actions {
        display: flex;
        gap: 1rem;
    }
    
    /* ============ SUMMARY CARDS ============ */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }
    
    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #3498db;
        transition: all 0.3s ease;
    }
    
    .summary-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .summary-card.card-in-treatment {
        border-left-color: #e74c3c;
    }
    
    .summary-card.card-waiting {
        border-left-color: #f39c12;
    }
    
    .summary-card.card-completed {
        border-left-color: #27ae60;
    }
    
    .summary-card.card-available {
        border-left-color: #9b59b6;
    }
    
    .card-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
    }
    
    .card-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }
    
    .card-icon {
        display: inline-block;
        font-size: 1.2rem;
        margin-right: 0.5rem;
    }
    
    /* ============ MAIN CONTENT GRID ============ */
    .queue-main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2.5rem;
    }
    
    @media (max-width: 1400px) {
        .queue-main-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* ============ IN TREATMENT SECTION ============ */
    .section-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .section-header {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-header-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
        flex: 1;
        color: white;
    }
    
    .section-header-count {
        background: rgba(255, 255, 255, 0.25);
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .section-content {
        padding: 1.5rem;
    }
    
    .in-treatment-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        border-left: 4px solid #e74c3c;
        transition: all 0.3s ease;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1rem;
        align-items: center;
    }
    
    .in-treatment-item:last-child {
        margin-bottom: 0;
    }
    
    .in-treatment-item:hover {
        background: #fff3f0;
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.15);
    }
    
    .in-treatment-room {
        display: inline-block;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        padding: 0.5rem 0.9rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.85rem;
        white-space: nowrap;
        text-align: center;
    }
    
    .in-treatment-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .in-treatment-patient {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
    }
    
    .in-treatment-secondary {
        font-size: 0.85rem;
        color: #000;
        margin: 0;
    }
    
    .in-treatment-details {
        display: contents;
    }
    
    .in-treatment-detail {
        display: contents;
    }
    
    .in-treatment-detail-label {
        display: none;
    }
    
    .in-treatment-detail-value {
        display: none;
    }
    
    .in-treatment-complete-btn {
        width: 40px;
        height: 40px;
        padding: 0;
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(39, 174, 96, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .in-treatment-complete-btn:hover {
        background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
        box-shadow: 0 4px 10px rgba(39, 174, 96, 0.35);
        transform: translateY(-1px);
    }
    
    .in-treatment-complete-btn:active {
        transform: translateY(0);
    }
    
    .empty-state {
        text-align: center;
        padding: 2.5rem 1.5rem;
        color: #95a5a6;
    }
    
    .empty-icon {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }
    
    .empty-text {
        font-size: 1rem;
        font-weight: 500;
        color: #7f8c8d;
    }
    
    /* ============ WAITING QUEUE SECTION ============ */
    .waiting-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1rem;
        padding: 0.85rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border-left: 4px solid #f39c12;
        transition: all 0.3s ease;
        align-items: center;
    }
    
    .waiting-item:last-child {
        margin-bottom: 0;
    }
    
    .waiting-item:hover {
        background: #fffbf0;
        box-shadow: 0 2px 8px rgba(243, 156, 18, 0.15);
    }
    
    .queue-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
        color: white;
        border-radius: 50%;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
    }
    
    .waiting-info {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        min-width: 0;
    }
    
    .waiting-patient-name {
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        font-size: 0.95rem;
    }
    
    .waiting-secondary {
        font-size: 0.8rem;
        color: #000;
        margin: 0;
    }
    
    .waiting-call-btn {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(39, 174, 96, 0.25);
        display: flex;
        align-items: center;
        gap: 0.4rem;
        flex-shrink: 0;
    }
    
    .waiting-call-btn:hover {
        background: linear-gradient(135deg, #229954 0%, #1e8449 100%);
        box-shadow: 0 4px 10px rgba(39, 174, 96, 0.35);
        transform: translateY(-1px);
    }
    
    .waiting-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .waiting-action-btn {
        padding: 0.5rem;
        background: #ecf0f1;
        color: #2c3e50;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .waiting-action-btn:hover {
        background: #bdc3c7;
        transform: scale(1.05);
    }
    
    .reassign-btn:hover {
        background: #3498db !important;
        color: white;
    }
    
    /* ============ SIDEBAR RESOURCES ============ */
    .sidebar-resources {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .resource-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .resource-header {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: white;
        padding: 1rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .resource-list {
        padding: 1.25rem;
    }
    
    .resource-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }
    
    .resource-item:last-child {
        margin-bottom: 0;
    }
    
    .resource-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .resource-status {
        padding: 0.35rem 0.8rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-available {
        background: #d5f4e6;
        color: #27ae60;
    }
    
    .status-busy {
        background: #fdeaa8;
        color: #d68910;
    }
    
    .status-break {
        background: #fadbd8;
        color: #c0392b;
    }
    
    /* ============ COMPLETED TODAY SECTION ============ */
    .completed-section {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        padding: 1.5rem;
        text-align: center;
    }
    
    .completed-header {
        font-size: 0.95rem;
        font-weight: 700;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1rem;
    }
    
    .completed-value {
        font-size: 3rem;
        font-weight: 700;
        color: #27ae60;
        line-height: 1;
    }
    
    .completed-icon {
        display: inline-block;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    /* ============ RESPONSIVENESS ============ */
    @media (max-width: 768px) {
        .queue-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .queue-header-actions {
            width: 100%;
        }
        
        .queue-main-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .sidebar-resources {
            grid-template-columns: 1fr;
        }
        
        .summary-cards {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .in-treatment-details {
            grid-template-columns: 1fr;
        }
    }
    
    /* ============ ANIMATIONS ============ */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
    
    .pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #e74c3c;
        border-radius: 50%;
        animation: pulse 2s infinite;
        margin-right: 0.5rem;
    }
</style>

<!-- Page Header -->
<div class="queue-header">
    <div class="queue-header-content">
        <h1><i class="bi bi-speedometer2 me-3"></i>Live Queue Board</h1>
        <p>Real-time clinic operations • Updated: <span id="todayDate">Loading...</span></p>
    </div>
    <div class="queue-header-actions">
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; width: 100%;">
            <a href="/staff/appointments/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>New Appointment
            </a>
            
            <!-- Queue Status Badge -->
            <div id="queue-status-badge" style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto; padding: 0.5rem 1rem; background: #f8f9fa; border-radius: 8px; font-weight: 600;">
                <span id="queue-status-text">Queue: Active</span>
                <span id="queue-status-dot" style="display: inline-block; width: 10px; height: 10px; background: #27ae60; border-radius: 50%;"></span>
            </div>
            
            <!-- Control Buttons -->
            <button id="pauseBtn" class="btn btn-warning" style="display: none; white-space: nowrap;">
                <i class="bi bi-pause-fill me-2"></i>Pause Queue
            </button>
            <button id="resumeBtn" class="btn btn-success" style="display: none; white-space: nowrap;">
                <i class="bi bi-play-fill me-2"></i>Resume Queue
            </button>
        </div>
    </div>
</div>

<!-- Summary Cards Section -->
<div class="summary-cards">
    <a href="{{ route('staff.queue.in-treatment') }}" class="text-decoration-none">
        <div class="summary-card card-in-treatment" style="cursor: pointer;">
            <div class="card-label"><i class="card-icon bi bi-hourglass"></i>In Treatment</div>
            <div class="card-value" id="stat-in-treatment">0</div>
        </div>
    </a>
    <a href="{{ route('staff.queue.waiting') }}" class="text-decoration-none">
        <div class="summary-card card-waiting" style="cursor: pointer;">
            <div class="card-label"><i class="card-icon bi bi-clock-history"></i>Waiting in Queue</div>
            <div class="card-value" id="stat-waiting">0</div>
        </div>
    </a>
    <a href="{{ route('staff.queue.completed') }}" class="text-decoration-none">
        <div class="summary-card card-completed" style="cursor: pointer;">
            <div class="card-label"><i class="card-icon bi bi-check-circle"></i>Completed Today</div>
            <div class="card-value" id="stat-completed">0</div>
        </div>
    </a>
    <a href="{{ route('staff.queue.available-dentists') }}" class="text-decoration-none">
        <div class="summary-card card-available" style="cursor: pointer;">
            <div class="card-label"><i class="card-icon bi bi-person-check"></i>Dentists Available</div>
            <div class="card-value" id="stat-available">0</div>
        </div>
    </a>
</div>

<!-- Main Content Grid -->
<div class="queue-main-grid">
    <!-- Left: In Treatment + Waiting Queue -->
    <div>
        <!-- In Treatment Section -->
        <div class="section-card" style="margin-bottom: 2rem;">
            <div class="section-header">
                <i class="bi bi-play-circle"></i>
                <h2 class="section-header-title">In Treatment</h2>
                <span class="section-header-count" id="in-treatment-count">0</span>
            </div>
            <div class="section-content" id="in-treatment-content">
                <div class="empty-state">
                    <div class="empty-icon">🚪</div>
                    <div class="empty-text">No patients in treatment</div>
                </div>
            </div>
        </div>
        
        <!-- Waiting Queue Section -->
        <div class="section-card">
            <div class="section-header">
                <i class="bi bi-hourglass-split"></i>
                <h2 class="section-header-title">Waiting Queue</h2>
                <span class="section-header-count" id="waiting-count">0</span>
            </div>
            <div class="section-content" id="waiting-content">
                <div class="empty-state">
                    <div class="empty-icon">✓</div>
                    <div class="empty-text">Queue is empty</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right: Sidebar Resources + Completed -->
    <div>
        <div class="sidebar-resources">
            <!-- Dentists Card -->
            <div class="resource-card">
                <div class="resource-header">
                    <i class="bi bi-person-badge"></i> Dentists
                </div>
                <div class="resource-list" id="dentists-list">
                    <div class="empty-state">
                        <div class="empty-icon">👨‍⚕️</div>
                        <div class="empty-text">No dentists available</div>
                    </div>
                </div>
            </div>
            
            <!-- Rooms Card (Hidden by default) -->
            <div class="resource-card" id="rooms-card" style="display: none;">
                <div class="resource-header">
                    <i class="bi bi-door-open"></i> Treatment Rooms
                </div>
                <div class="resource-list" id="rooms-list">
                    <div class="empty-state">
                        <div class="empty-icon">🚪</div>
                        <div class="empty-text">No rooms configured</div>
                    </div>
                </div>
            </div>
            
            <!-- Completed Today Card -->
            <div class="completed-section">
                <div class="completed-header">
                    <i class="completed-icon bi bi-trophy"></i>Completed Today
                </div>
                <div class="completed-value" id="completed-total">0</div>
            </div>
        </div>
    </div>
</div>

<!-- Reassign Dentist Modal -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="reassignTitle">
                    <i class="bi bi-person-badge"></i> Reassign Dentist
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reassignContent">
                    <p class="text-center text-muted"><small>Loading available dentists...</small></p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmReassignBtn" onclick="confirmReassignment()">
                    <i class="bi bi-check-circle"></i> Reassign
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    console.log('Queue board script loaded');
    
    // Global variables for reassignment modal
    let reassignData = {
        appointmentId: null,
        selectedDentistId: null,
        dentists: []
    };
    
    // Open reassign modal
    function openReassignModal(appointmentId) {
        console.log('🔧 Opening reassign modal for appointment:', appointmentId);
        reassignData.appointmentId = appointmentId;
        reassignData.selectedDentistId = null;
        
        // Get available dentists from current data
        const modal = new bootstrap.Modal(document.getElementById('reassignModal'));
        const allDentists = reassignData.dentists || [];
        
        if (allDentists.length === 0) {
            document.getElementById('reassignContent').innerHTML = `
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No dentists available at this moment
                </div>
            `;
            modal.show();
            return;
        }
        
        // Build dentist selection HTML
        const dentistHTML = allDentists
            .filter(d => d.status === 'available')  // Only show available dentists
            .map(d => `
                <div class="form-check p-2 border rounded mb-2" style="cursor: pointer;">
                    <input 
                        class="form-check-input" 
                        type="radio" 
                        name="dentist" 
                        id="dentist${d.id}" 
                        value="${d.id}"
                        onchange="reassignData.selectedDentistId = ${d.id}">
                    <label class="form-check-label w-100" for="dentist${d.id}" style="cursor: pointer;">
                        <strong>${d.name}</strong>
                        <span class="badge bg-success ms-2">Available</span>
                    </label>
                </div>
            `).join('');
        
        document.getElementById('reassignContent').innerHTML = `
            <div class="mb-3">
                <label class="form-label fw-bold">Select a dentist:</label>
                <div class="dentist-list">
                    ${dentistHTML || '<p class="text-muted">No available dentists</p>'}
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Reason (optional)</label>
                <textarea 
                    class="form-control form-control-sm" 
                    id="reassignReason"
                    placeholder="e.g., Patient preference, specialist needed, emergency"
                    rows="2"></textarea>
            </div>
        `;
        
        modal.show();
    }
    
    // Confirm reassignment
    async function confirmReassignment() {
        if (!reassignData.appointmentId || !reassignData.selectedDentistId) {
            alert('Please select a dentist');
            return;
        }
        
        const reason = document.getElementById('reassignReason')?.value || 'Manual reassignment';
        const confirmBtn = document.getElementById('confirmReassignBtn');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<spinner></spinner> Reassigning...';
        
        try {
            const response = await fetch('/api/queue-board/reassign-dentist', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'include',
                body: JSON.stringify({
                    appointment_id: reassignData.appointmentId,
                    dentist_id: reassignData.selectedDentistId,
                    reason: reason
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log('✅ Reassignment successful:', data);
                alert(`✅ ${data.message}`);
                
                // Close modal and refresh queue board
                bootstrap.Modal.getInstance(document.getElementById('reassignModal')).hide();
                refreshQueueBoard();
            } else {
                console.error('❌ Reassignment failed:', data);
                alert(`❌ Error: ${data.message}`);
            }
        } catch (error) {
            console.error('❌ Error during reassignment:', error);
            alert('Error: ' + error.message);
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalText;
        }
    }
    
    // Real-time update function
    async function refreshQueueBoard() {
        try {
            console.log('🔄 refreshQueueBoard() called at', new Date().toLocaleTimeString());
            const response = await fetch('/api/queue-board/data', {
                credentials: 'include'
            });
            
            console.log('📡 Fetch response status:', response.status, 'OK:', response.ok);
            
            if (!response.ok) {
                console.error('❌ API returned error status:', response.status);
                const errorText = await response.text();
                console.error('❌ Error response:', errorText.substring(0, 300));
                return;
            }
            
            const data = await response.json();
            
            console.log('✅ Queue board API response received:', data);
            
            // Transform API response
            // CRITICAL: Use appointment_id directly from API response for both sections
            const inServiceList = (data.inService || []).map(queue => ({
                id: queue.appointment_id,  // Use appointment ID, not queue ID
                patient_name: queue.appointment?.patient_name || 'Unknown',
                visit_code: queue.appointment?.visit_code,
                service_name: 'In Treatment',
                room_name: queue.room?.room_number,
                dentist_name: queue.dentist?.name
            }));
            
            const waitingList = (data.waiting || []).map(queue => ({
                id: queue.appointment_id,  // CRITICAL: Use appointment_id for call-patient API
                patient_name: queue.appointment?.patient_name || 'Unknown',
                visit_code: queue.appointment?.visit_code,
                service_name: queue.appointment?.service || 'Service',  // API returns service as string
                dentist_name: queue.dentist?.name,
                queue_number: queue.queue_number
            }));
            
            console.log('✅ Transformed waiting list:', waitingList);
            
            // Update all counts
            const inServiceCount = inServiceList.length;
            const waitingCount = waitingList.length;
            const completedCount = data.stats.completedCount || 0;
            const availableDentists = data.stats.available_dentists || 0;
            
            console.log('📊 Updating counts - InService:', inServiceCount, 'Waiting:', waitingCount, 'Completed:', completedCount, 'Available:', availableDentists);
            
            document.getElementById('stat-in-treatment').textContent = inServiceCount;
            document.getElementById('stat-waiting').textContent = waitingCount;
            document.getElementById('stat-completed').textContent = completedCount;
            document.getElementById('stat-available').textContent = availableDentists;
            document.getElementById('in-treatment-count').textContent = inServiceCount;
            document.getElementById('waiting-count').textContent = waitingCount;
            document.getElementById('completed-total').textContent = completedCount;
            
            console.log('✅ DOM updated - stat-completed now shows:', document.getElementById('stat-completed').textContent);
            
            // Update queue status badge and buttons
            updateQueueStatusBadge(data.isPaused);
            
            // Update sections
            updateInTreatment(inServiceList);
            updateWaitingQueue(waitingList);
            updateDentists(data.dentists || []);
            updateRooms(data.rooms || []);
            
            // Store dentists for reassignment modal
            reassignData.dentists = data.dentists || [];
            
            console.log('✅ Queue board updated successfully');
            
        } catch (error) {
            console.error('❌ Error refreshing queue board:', error);
            console.error('❌ Error message:', error.message);
            console.error('❌ Error stack:', error.stack);
        }
    }
    
    function updateInTreatment(appointments) {
        const content = document.getElementById('in-treatment-content');
        
        if (!appointments || appointments.length === 0) {
            content.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">🚪</div>
                    <div class="empty-text">No patients in treatment</div>
                </div>
            `;
            return;
        }
        
        content.innerHTML = appointments.map(apt => `
            <div class="in-treatment-item">
                ${apt.room_name ? `<div class="in-treatment-room">${apt.room_name}</div>` : ''}
                <div class="in-treatment-info">
                    ${apt.visit_code ? `<p class="in-treatment-code" style="font-size: 0.75rem; color: #7f8c8d; margin: 0.25rem 0;"><code>${apt.visit_code}</code></p>` : ''}
                    <p class="in-treatment-patient">${apt.patient_name}</p>
                    <p class="in-treatment-secondary">${apt.dentist_name ? apt.dentist_name + ' • ' : ''}${apt.service_name || 'Service'}</p>
                </div>
                <button class="in-treatment-complete-btn" 
                        onclick="completeAppointment(${apt.id})"
                        onmousedown="event.preventDefault()" 
                        title="Mark treatment as complete">
                    <i class="bi bi-check-lg"></i>
                </button>
            </div>
        `).join('');
    }
    
    function updateWaitingQueue(queue) {
        const content = document.getElementById('waiting-content');
        
        if (!queue || queue.length === 0) {
            content.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">✓</div>
                    <div class="empty-text">Queue is empty - all caught up!</div>
                </div>
            `;
            return;
        }
        
        content.innerHTML = queue.map((patient, index) => `
            <div class="waiting-item">
                <div class="queue-number">${patient.queue_number || (index + 1)}</div>
                <div class="waiting-info">
                    ${patient.visit_code ? `<p class="waiting-code" style="font-size: 0.75rem; color: #7f8c8d; margin: 0.25rem 0;"><code>${patient.visit_code}</code></p>` : ''}
                    <p class="waiting-patient-name">${patient.patient_name}</p>
                    <p class="waiting-secondary">${patient.service_name || 'Pending'} ${patient.dentist_name ? '• ' + patient.dentist_name : ''}</p>
                </div>
                <div class="waiting-actions">
                    <button class="waiting-action-btn reassign-btn" 
                            onclick="openReassignModal(${patient.id})"
                            title="Reassign dentist (emergency)">
                        <i class="bi bi-person-badge"></i>
                    </button>
                    <button class="waiting-call-btn" 
                            onclick="callPatient(${patient.id})"
                            title="Call patient">
                        <i class="bi bi-telephone-fill"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    function updateDentists(dentists) {
        const content = document.getElementById('dentists-list');
        
        if (!dentists || dentists.length === 0) {
            content.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">👨‍⚕️</div>
                    <div class="empty-text">No dentists available</div>
                </div>
            `;
            return;
        }
        
        // Sort dentists: busy first, then by name
        const sorted = [...dentists].sort((a, b) => {
            if (a.status === 'busy' && b.status !== 'busy') return -1;
            if (a.status !== 'busy' && b.status === 'busy') return 1;
            return a.name.localeCompare(b.name);
        });
        
        content.innerHTML = sorted.map(dentist => {
            const statusClass = `status-${dentist.status.toLowerCase()}`;
            const statusLabel = dentist.status === 'busy' ? 'In Treatment' : 'Available';
            
            return `
                <div class="resource-item">
                    <span class="resource-name">${dentist.name}</span>
                    <span class="resource-status ${statusClass}">${statusLabel}</span>
                </div>
            `;
        }).join('');
    }
    
    function updateRooms(rooms) {
        const card = document.getElementById('rooms-card');
        const content = document.getElementById('rooms-list');
        
        if (!rooms || rooms.length === 0) {
            card.style.display = 'none';
            return;
        }
        
        card.style.display = 'block';
        content.innerHTML = rooms.map(room => {
            const isAvailable = room.status === 'available';
            const statusClass = isAvailable ? 'status-available' : 'status-busy';
            const statusLabel = isAvailable ? 'Available' : 'In Use';
            
            return `
                <div class="resource-item">
                    <span class="resource-name">${room.room_number}</span>
                    <span class="resource-status ${statusClass}">${statusLabel}</span>
                </div>
            `;
        }).join('');
    }
    
    // Action functions
    async function callPatient(appointmentId) {
        try {
            console.log('callPatient called for appointment:', appointmentId);
            const response = await fetch(`/api/staff/appointments/${appointmentId}/call-patient`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });
            
            console.log('Call patient response status:', response.status);
            const data = await response.json();
            console.log('Call patient response data:', data);
            
            if (response.ok && data.success) {
                alert(`✓ Called: ${data.patient_name} to Room ${data.room_number}`);
                refreshQueueBoard();
            } else {
                const errorMsg = data.error || data.message || 'Unable to call patient';
                console.error('Call patient failed:', errorMsg);
                alert(`Error: ${errorMsg}`);
            }
        } catch (error) {
            console.error('Error calling patient:', error);
            alert(`Error: ${error.message}`);
        }
    }
    
    async function completeAppointment(appointmentId) {
        if (!confirm('Mark this treatment as complete?')) return;
        
        try {
            // Disable button to prevent double-submit
            event.target.closest('button').disabled = true;
            event.target.closest('button').style.opacity = '0.5';
            
            const response = await fetch(`/staff/appointments/${appointmentId}/complete-treatment`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });
            
            console.log('Complete treatment response status:', response.status);
            console.log('Complete treatment response headers:', {
                contentType: response.headers.get('Content-Type'),
                status: response.statusText
            });
            
            // Check if response is JSON
            const contentType = response.headers.get('Content-Type');
            let data;
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
                console.log('Complete treatment response data:', data);
            } else {
                const text = await response.text();
                console.error('Response is not JSON:', text.substring(0, 200));
                console.error('Response status:', response.status);
                console.error('Response headers:', contentType);
                alert(`Error: Server returned non-JSON response (${response.status}). Check logs.`);
                // Re-enable button on error
                event.target.closest('button').disabled = false;
                event.target.closest('button').style.opacity = '1';
                return;
            }
            
            if (data.success === true || response.ok) {
                alert(`✓ Treatment completed for ${data.patient_name || 'patient'}`);
                refreshQueueBoard();
            } else {
                const errorMsg = data.error || data.message || 'Failed to complete appointment';
                console.error('Completion failed:', errorMsg);
                alert(`Error: ${errorMsg}`);
                // Re-enable button on error
                event.target.closest('button').disabled = false;
                event.target.closest('button').style.opacity = '1';
            }
        } catch (error) {
            console.error('Error completing appointment:', error);
            console.error('Error details:', {
                message: error.message,
                stack: error.stack,
                type: error.type
            });
            alert(`Error: ${error.message || 'Failed to complete appointment'}`);
            // Re-enable button on error
            try {
                event.target.closest('button').disabled = false;
                event.target.closest('button').style.opacity = '1';
            } catch (e) {}
        }
    }
    
    function displayTodayDate() {
        const options = { weekday: 'short', month: 'short', day: 'numeric' };
        const today = new Date().toLocaleDateString('en-US', options);
        document.getElementById('todayDate').textContent = today;
    }
    
    function updateQueueStatusBadge(isPaused) {
        const badge = document.getElementById('queue-status-badge');
        const statusText = document.getElementById('queue-status-text');
        const statusDot = document.getElementById('queue-status-dot');
        const pauseBtn = document.getElementById('pauseBtn');
        const resumeBtn = document.getElementById('resumeBtn');
        
        if (isPaused) {
            statusText.textContent = 'Queue: PAUSED';
            statusDot.style.background = '#e74c3c';
            badge.style.background = '#fadbd8';
            pauseBtn.style.display = 'none';
            resumeBtn.style.display = 'inline-block';
        } else {
            statusText.textContent = 'Queue: Active';
            statusDot.style.background = '#27ae60';
            badge.style.background = '#d5f4e6';
            pauseBtn.style.display = 'inline-block';
            resumeBtn.style.display = 'none';
        }
    }
    
    async function pauseQueue() {
        if (!confirm('Pause the queue? Patients will not be called.')) return;
        
        try {
            const response = await fetch('/staff/pause-queue', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('✓ Queue paused successfully');
                refreshQueueBoard();
            } else {
                alert(`Error: ${data.error || 'Failed to pause queue'}`);
            }
        } catch (error) {
            console.error('Error pausing queue:', error);
            alert(`Error: ${error.message}`);
        }
    }
    
    async function resumeQueue() {
        if (!confirm('Resume the queue? Patients will be called automatically.')) return;
        
        try {
            const response = await fetch('/staff/resume-queue', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('✓ Queue resumed successfully');
                refreshQueueBoard();
            } else {
                alert(`Error: ${data.error || 'Failed to resume queue'}`);
            }
        } catch (error) {
            console.error('Error resuming queue:', error);
            alert(`Error: ${error.message}`);
        }
    }
    
    // Initial load and refresh
    console.log('🚀 Queue board initializing...');
    displayTodayDate();
    
    // Initial load
    refreshQueueBoard().then(() => {
        console.log('✅ Initial refresh complete');
    }).catch(err => {
        console.error('❌ Initial refresh failed:', err);
    });
    
    // Set up auto-refresh every 2 seconds
    const autoRefreshInterval = setInterval(() => {
        console.log('⏰ Auto-refresh triggered');
        refreshQueueBoard().catch(err => {
            console.error('❌ Auto-refresh failed:', err);
        });
    }, 2000);  // Refresh every 2 seconds for real-time updates
    
    console.log('✅ Auto-refresh interval set (every 2 seconds)');
    
    // Attach event listeners for pause/resume buttons
    document.getElementById('pauseBtn')?.addEventListener('click', pauseQueue);
    document.getElementById('resumeBtn')?.addEventListener('click', resumeQueue);
    
    // Listen for real-time updates
    if (typeof Echo !== 'undefined') {
        Echo.channel('clinic-queue').listen('QueueUpdated', () => {
            console.log('📡 Real-time update received from Echo');
            refreshQueueBoard();
        });
    } else {
        console.log('⚠️ Echo not available for real-time updates');
    }
</script>
@endsection
