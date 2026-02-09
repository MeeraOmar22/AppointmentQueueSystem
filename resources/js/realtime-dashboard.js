/**
 * Real-Time Staff Dashboard Module
 * 
 * Listens for AppointmentStateChanged events and updates the dashboard in real-time
 * using Laravel Broadcasting via WebSocket/Pusher
 * 
 * Features:
 * - Auto-refresh appointments and queue on status changes
 * - Toast notifications for all state changes
 * - Real-time statistics updates
 * - Queue auto-management (creation/deletion)
 * - Smooth UI transitions
 */

class RealtimeDashboard {
    constructor(options = {}) {
        this.options = {
            clinicLocation: options.clinicLocation || 'Main',
            refreshInterval: options.refreshInterval || 5000, // Fallback refresh
            notificationDuration: options.notificationDuration || 4000,
            ...options
        };

        this.isListening = false;
        this.queue = [];
        this.stats = {
            total: 0,
            queued: 0,
            in_service: 0,
            completed: 0,
            cancelled: 0
        };

        this.init();
    }

    /**
     * Initialize the real-time dashboard
     */
    init() {
        console.log('[Dashboard] Initializing real-time updates...');

        // Set up Laravel Echo listener for broadcasts
        if (typeof Echo !== 'undefined') {
            this.setupEchoListener();
            console.log('[Dashboard] Echo listener configured');
        } else {
            console.warn('[Dashboard] Laravel Echo not available, using polling fallback');
            this.startPollingFallback();
        }

        // Set up event handlers
        this.setupEventHandlers();

        // Cache DOM elements
        this.cacheDOM();

        // Load initial data
        this.refreshData();

        console.log('[Dashboard] Real-time dashboard initialized');
    }

    /**
     * Setup Echo listener for appointment state changes
     */
    setupEchoListener() {
        if (!window.Echo) {
            console.error('[Dashboard] Echo is not available');
            return;
        }

        // Listen on the clinic location channel
        const channel = `staff.dashboard.${this.options.clinicLocation}`;
        
        console.log(`[Dashboard] Listening on channel: ${channel}`);

        window.Echo.channel(channel)
            .listen('AppointmentStateChanged', (event) => {
                console.log('[Dashboard] Event received:', event);
                this.handleAppointmentStateChange(event);
            })
            .error((error) => {
                console.error('[Dashboard] Echo error:', error);
                // Fallback to polling if Echo fails
                if (!this.isPolling) {
                    console.log('[Dashboard] Switching to polling fallback');
                    this.startPollingFallback();
                }
            });
    }

    /**
     * Fallback to polling if WebSocket is unavailable
     */
    startPollingFallback() {
        if (this.isPolling) return;

        console.log('[Dashboard] Starting polling fallback (every ' + this.options.refreshInterval + 'ms)');
        this.isPolling = true;

        setInterval(() => {
            this.refreshData();
        }, this.options.refreshInterval);
    }

    /**
     * Handle appointment state change event
     */
    async handleAppointmentStateChange(event) {
        const { appointment, previousStatus, newStatus, reason } = event;

        console.log(`[Dashboard] Appointment ${appointment.id}: ${previousStatus} → ${newStatus}`);

        // Show toast notification
        this.showNotification(appointment, previousStatus, newStatus, reason);

        // Refresh dashboard data
        await this.refreshData();

        // Highlight the updated appointment row
        this.highlightAppointment(appointment.id);

        // Update statistics
        this.updateStatsFromEvent(appointment, previousStatus, newStatus);
    }

    /**
     * Refresh all dashboard data from API
     */
    async refreshData() {
        try {
            console.log('[Dashboard] Fetching data from API...');
            
            // Fetch appointments
            const appointmentsResponse = await fetch('/api/staff/appointments/today', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!appointmentsResponse.ok) {
                console.error('[Dashboard] Appointments response status:', appointmentsResponse.status);
                throw new Error('Failed to fetch appointments');
            }
            const appointmentsData = await appointmentsResponse.json();
            console.log('[Dashboard] Appointments data:', appointmentsData);

            // Fetch queue
            const queueResponse = await fetch('/api/staff/queue', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!queueResponse.ok) throw new Error('Failed to fetch queue');
            const queueData = await queueResponse.json();
            console.log('[Dashboard] Queue data:', queueData);

            // Fetch statistics
            const statsResponse = await fetch('/api/staff/summary', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!statsResponse.ok) throw new Error('Failed to fetch statistics');
            const statsData = await statsResponse.json();
            console.log('[Dashboard] Stats data:', statsData);

            // Update UI
            const appointments = appointmentsData.data?.appointments || [];
            console.log('[Dashboard] Updating UI with', appointments.length, 'appointments');
            
            this.updateAppointments(appointments);
            this.updateQueue(queueData.data?.queue || []);
            this.updateStatistics(statsData.data);

            console.log('[Dashboard] Data refreshed successfully');
        } catch (error) {
            console.error('[Dashboard] Error refreshing data:', error);
            this.showErrorNotification('Failed to refresh dashboard data');
        }
    }

    /**
     * Update appointments in the table
     */
    updateAppointments(appointments) {
        const container = document.getElementById('appointmentsTableBody');
        if (!container) return;

        // Update or insert appointment rows
        appointments.forEach(appointment => {
            const row = document.getElementById(`appointment-${appointment.id}`);
            if (row) {
                // Update existing row with fade effect
                this.updateAppointmentRow(row, appointment);
            } else {
                // Insert new row
                this.insertAppointmentRow(appointment);
            }
        });

        // Remove appointments that no longer exist
        document.querySelectorAll('[id^="appointment-"]').forEach(row => {
            const appointmentId = parseInt(row.id.replace('appointment-', ''));
            if (!appointments.find(a => a.id === appointmentId)) {
                row.remove();
            }
        });
    }

    /**
     * Update a single appointment row
     */
    updateAppointmentRow(row, appointment) {
        // Update status badge
        const statusBadge = row.querySelector('[data-status]');
        if (statusBadge) {
            const oldStatus = statusBadge.textContent;
            statusBadge.textContent = this.formatStatus(appointment.status);
            statusBadge.className = `badge ${this.getStatusBadgeClass(appointment.status)}`;
        }

        // Update queue number if in queue
        const queueCell = row.querySelector('[data-queue-number]');
        if (queueCell && appointment.queue) {
            queueCell.textContent = `#${appointment.queue.queue_number}`;
            queueCell.classList.remove('d-none');
        } else if (queueCell) {
            queueCell.classList.add('d-none');
        }

        // Add fade effect
        row.style.opacity = '0.7';
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transition = 'opacity 0.3s ease-in-out';
        }, 50);
    }

    /**
     * Insert a new appointment row
     */
    insertAppointmentRow(appointment) {
        const tbody = document.getElementById('appointmentsTableBody');
        if (!tbody) return;

        const row = document.createElement('tr');
        row.id = `appointment-${appointment.id}`;
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <p class="fw-semibold mb-0">${appointment.patient_name}</p>
                        <small class="text-muted">${appointment.patient_phone}</small>
                    </div>
                </div>
            </td>
            <td>${appointment.appointment_time}</td>
            <td>${appointment.service?.name || 'N/A'}</td>
            <td>${appointment.dentist?.name || 'Unassigned'}</td>
            <td>${appointment.queue ? '#' + appointment.queue.queue_number : '-'}</td>
            <td>
                <span class="badge ${this.getStatusBadgeClass(appointment.status)}" data-status>
                    ${this.formatStatus(appointment.status)}
                </span>
            </td>
            <td>
                <div class="btn-group" role="group">
                    ${this.getActionButtons(appointment)}
                </div>
            </td>
        `;

        // Add animation
        row.style.animation = 'slideIn 0.3s ease-in-out';
        tbody.appendChild(row);
    }

    /**
     * Update queue display
     */
    updateQueue(queueEntries) {
        const container = document.getElementById('queueDisplay');
        if (!container) return;

        // Sort by queue number
        queueEntries.sort((a, b) => a.queue_number - b.queue_number);

        // Update queue list
        const queueHtml = queueEntries.map((entry, index) => `
            <div class="queue-item ${index === 0 ? 'current' : ''}" data-queue-id="${entry.id}">
                <div class="queue-number">#${entry.queue_number}</div>
                <div class="queue-details">
                    <div class="queue-patient">${entry.appointment.patient_name}</div>
                    <div class="queue-status">
                        <span class="badge ${this.getStatusBadgeClass(entry.appointment.status)}">
                            ${this.formatStatus(entry.appointment.status)}
                        </span>
                    </div>
                </div>
                <div class="queue-time">${this.getWaitingTime(entry)}</div>
            </div>
        `).join('');

        container.innerHTML = queueHtml || '<p class="text-muted">Queue is empty</p>';
    }

    /**
     * Update statistics
     */
    updateStatistics(stats) {
        document.querySelectorAll('[data-stat]').forEach(element => {
            const stat = element.dataset.stat;
            const newValue = stats[stat] || 0;
            const oldValue = parseInt(element.textContent) || 0;

            if (newValue !== oldValue) {
                // Animate number change
                this.animateNumber(element, oldValue, newValue);
                element.textContent = newValue;
            }
        });

        this.stats = stats;
    }

    /**
     * Update statistics from a single event (fast path)
     */
    updateStatsFromEvent(appointment, previousStatus, newStatus) {
        const stats = { ...this.stats };

        // Update counts based on status change
        if (previousStatus !== newStatus) {
            const statusKey = this.getStatKeyForStatus(previousStatus);
            const newStatusKey = this.getStatKeyForStatus(newStatus);

            if (statusKey && stats[statusKey]) stats[statusKey]--;
            if (newStatusKey && stats[newStatusKey]) stats[newStatusKey]++;
            if (newStatusKey === 'total') stats.total = Math.max(0, stats.total - 1);

            // Update UI elements
            document.querySelectorAll('[data-stat]').forEach(element => {
                const stat = element.dataset.stat;
                if (stats[stat]) {
                    element.textContent = stats[stat];
                }
            });
        }
    }

    /**
     * Show toast notification
     */
    showNotification(appointment, previousStatus, newStatus, reason) {
        const message = this.getNotificationMessage(appointment, newStatus);
        const type = this.getNotificationType(newStatus);

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast notification-${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <i class="bi bi-${this.getNotificationIcon(type)} me-2"></i>
                <strong class="me-auto">Appointment Update</strong>
                <small>${new Date().toLocaleTimeString()}</small>
            </div>
            <div class="toast-body">
                <p class="mb-1"><strong>${appointment.patient_name}</strong></p>
                <p class="mb-0">${message}</p>
                ${reason ? `<small class="text-muted">Reason: ${reason}</small>` : ''}
            </div>
        `;

        // Add to container
        const container = document.getElementById('notificationContainer') || this.createNotificationContainer();
        container.appendChild(toast);

        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);

        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, this.options.notificationDuration);
    }

    /**
     * Show error notification
     */
    showErrorNotification(message) {
        const toast = document.createElement('div');
        toast.className = 'toast notification-error';
        toast.innerHTML = `
            <div class="toast-header">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong class="me-auto">Error</strong>
                <small>${new Date().toLocaleTimeString()}</small>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        const container = document.getElementById('notificationContainer') || this.createNotificationContainer();
        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, this.options.notificationDuration);
    }

    /**
     * Create notification container if not exists
     */
    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notificationContainer';
        container.className = 'notification-container';
        document.body.appendChild(container);
        return container;
    }

    /**
     * Highlight updated appointment
     */
    highlightAppointment(appointmentId) {
        const row = document.getElementById(`appointment-${appointmentId}`);
        if (!row) return;

        row.classList.add('highlight');
        setTimeout(() => row.classList.remove('highlight'), 1500);
    }

    /**
     * Cache frequently accessed DOM elements
     */
    cacheDOM() {
        this.elements = {
            statsContainer: document.getElementById('statsContainer'),
            appointmentsTable: document.getElementById('appointmentsTable'),
            queueDisplay: document.getElementById('queueDisplay'),
            notificationContainer: document.getElementById('notificationContainer')
        };
    }

    /**
     * Setup event handlers
     */
    setupEventHandlers() {
        // Handle filter changes
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filterAppointmentsByStatus(e.target.value);
            });
        }

        // Handle tab changes
        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                this.onTabChange(e.target);
            });
        });

        // Handle action buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-action]')) {
                this.handleAction(e.target.closest('[data-action]'));
            }
        });
    }

    /**
     * Get action buttons HTML for appointment
     */
    getActionButtons(appointment) {
        const buttons = [];

        if (appointment.status === 'BOOKED' || appointment.status === 'CONFIRMED') {
            buttons.push(`
                <button class="btn btn-sm btn-success" data-action="checkin" data-id="${appointment.id}">
                    <i class="bi bi-check-circle"></i> Check In
                </button>
            `);
        }

        if (appointment.status === 'CHECKED_IN' || appointment.status === 'WAITING') {
            buttons.push(`
                <button class="btn btn-sm btn-warning" data-action="call-next" data-id="${appointment.id}">
                    <i class="bi bi-telephone"></i> Call
                </button>
            `);
        }

        if (appointment.status === 'IN_TREATMENT') {
            buttons.push(`
                <button class="btn btn-sm btn-primary" data-action="complete" data-id="${appointment.id}">
                    <i class="bi bi-check"></i> Complete
                </button>
            `);
        }

        if (appointment.status !== 'CANCELLED' && appointment.status !== 'COMPLETED') {
            buttons.push(`
                <button class="btn btn-sm btn-danger" data-action="cancel" data-id="${appointment.id}">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            `);
        }

        return buttons.join('');
    }

    /**
     * Handle appointment action (check in, call, complete, cancel)
     */
    async handleAction(button) {
        const action = button.dataset.action;
        const appointmentId = button.dataset.id;

        try {
            let endpoint = '';
            let method = 'POST';
            let body = null;

            switch (action) {
                case 'checkin':
                    endpoint = `/staff/checkin/${appointmentId}`;
                    break;
                case 'call-next':
                    endpoint = `/staff/appointments/${appointmentId}/call-next`;
                    break;
                case 'complete':
                    endpoint = `/staff/appointments/${appointmentId}/complete-treatment`;
                    break;
                case 'cancel':
                    endpoint = `/staff/appointments/${appointmentId}/cancel`;
                    break;
                default:
                    return;
            }

            const response = await fetch(endpoint, {
                method,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: body ? JSON.stringify(body) : undefined
            });

            if (!response.ok) {
                throw new Error(`Action failed: ${response.statusText}`);
            }

            // Success - notification will be shown by event listener
        } catch (error) {
            console.error(`[Dashboard] Action ${action} failed:`, error);
            this.showErrorNotification(`Failed to ${action} appointment`);
        }
    }

    /**
     * Filter appointments by status
     */
    filterAppointmentsByStatus(status) {
        const rows = document.querySelectorAll('tr[id^="appointment-"]');
        rows.forEach(row => {
            const statusBadge = row.querySelector('[data-status]');
            const rowStatus = statusBadge?.textContent.trim().toUpperCase();
            
            if (status === '' || rowStatus === status.toUpperCase()) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    /**
     * Handle tab change
     */
    onTabChange(tab) {
        const tabContent = document.querySelector(tab.dataset.bsTarget);
        console.log('[Dashboard] Tab changed to:', tab.id);
        // Tab content should auto-update via refresh
    }

    /**
     * Get notification message for status change
     */
    getNotificationMessage(appointment, newStatus) {
        const messages = {
            'BOOKED': `Appointment booked at ${appointment.appointment_time}`,
            'CONFIRMED': 'Appointment confirmed',
            'CHECKED_IN': `${appointment.patient_name} checked in`,
            'WAITING': 'Waiting for treatment',
            'IN_TREATMENT': 'Treatment in progress',
            'COMPLETED': 'Treatment completed',
            'CANCELLED': 'Appointment cancelled',
            'NO_SHOW': 'Patient no-show'
        };
        return messages[newStatus] || `Status changed to ${newStatus}`;
    }

    /**
     * Get notification type for status
     */
    getNotificationType(status) {
        const types = {
            'BOOKED': 'info',
            'CONFIRMED': 'info',
            'CHECKED_IN': 'success',
            'WAITING': 'info',
            'IN_TREATMENT': 'warning',
            'COMPLETED': 'success',
            'CANCELLED': 'danger',
            'NO_SHOW': 'danger'
        };
        return types[status] || 'info';
    }

    /**
     * Get notification icon
     */
    getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'info': 'info-circle',
            'warning': 'exclamation-circle',
            'danger': 'x-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * Get status badge class
     */
    getStatusBadgeClass(status) {
        const classes = {
            'BOOKED': 'bg-info',
            'CONFIRMED': 'bg-info',
            'CHECKED_IN': 'bg-success',
            'WAITING': 'bg-success',
            'IN_TREATMENT': 'bg-warning text-dark',
            'COMPLETED': 'bg-success',
            'CANCELLED': 'bg-danger',
            'NO_SHOW': 'bg-danger'
        };
        return classes[status] || 'bg-secondary';
    }

    /**
     * Format status for display
     */
    formatStatus(status) {
        return status
            .split('_')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    }

    /**
     * Get stat key for status
     */
    getStatKeyForStatus(status) {
        const mapping = {
            'BOOKED': 'total',
            'CONFIRMED': 'total',
            'CHECKED_IN': 'queued',
            'WAITING': 'queued',
            'IN_TREATMENT': 'in_service',
            'COMPLETED': 'completed',
            'CANCELLED': 'cancelled',
            'NO_SHOW': 'cancelled'
        };
        return mapping[status];
    }

    /**
     * Get waiting time for queue entry
     */
    getWaitingTime(entry) {
        if (!entry.check_in_time) return '-';
        const checkInTime = new Date(entry.check_in_time);
        const now = new Date();
        const minutes = Math.floor((now - checkInTime) / 60000);
        return `${minutes} min`;
    }

    /**
     * Animate number change
     */
    animateNumber(element, from, to) {
        const duration = 300;
        const start = Date.now();

        const animate = () => {
            const elapsed = Date.now() - start;
            const progress = Math.min(elapsed / duration, 1);
            const current = Math.floor(from + (to - from) * progress);
            element.textContent = current;

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    /**
     * Get CSRF token from page
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const clinicLocation = document.body.dataset.clinicLocation || 'Main';
    window.dashboard = new RealtimeDashboard({
        clinicLocation: clinicLocation,
        refreshInterval: 5000,
        notificationDuration: 4000
    });
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealtimeDashboard;
}
