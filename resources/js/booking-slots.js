/**
 * Booking Slot Manager
 * 
 * Handles:
 * - Fetching available time slots from server
 * - Rendering slot grid UI
 * - Slot selection logic
 * - Lunch break display
 * - Booked slot handling
 * 
 * Usage:
 *   const bookingSlots = new BookingSlotManager();
 *   bookingSlots.fetchSlots('2025-02-15');
 */

class BookingSlotManager {
    
    constructor() {
        // Configuration
        this.slotApiUrl = '/api/booking/slots';
        this.lunchStart = '13:00';
        this.lunchEnd = '14:00';
        
        // State
        this.selectedDate = null;
        this.selectedTime = null;
        this.slots = [];
        
        // DOM selectors
        this.elements = {
            slotsLoading: document.getElementById('slots-loading'),
            slotsGrid: document.getElementById('slots-grid'),
            gridContainer: document.getElementById('slots-grid-container'),
            messageBox: document.getElementById('slots-message'),
            selectedDisplay: document.getElementById('selected-slot-display'),
            selectedTime: document.getElementById('selected-time'),
            appointmentDate: document.getElementById('appointment_date'),
            appointmentTime: document.getElementById('appointment_time'),
        };
    }

    /**
     * Fetch available slots for a specific date
     * @param {string} date - Format: Y-m-d
     */
    async fetchSlots(date) {
        if (!date) return;

        this.selectedDate = date;
        this.selectedTime = null; // Reset selection
        this.showLoading();

        try {
            // Get the selected service_id from the form
            const serviceRadio = document.querySelector('input[name="service_id"]:checked');
            const serviceId = serviceRadio ? serviceRadio.value : null;
            
            if (!serviceId) {
                this.showError('Please select a service first');
                return;
            }

            const response = await fetch(
                `${this.slotApiUrl}?date=${date}&service_id=${serviceId}`
            );
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (!data.success) {
                this.showError(data.message || 'Failed to fetch slots');
                return;
            }

            this.slots = data.slots || [];
            this.renderSlots();

        } catch (error) {
            console.error('Error fetching slots:', error);
            this.showError('Unable to fetch available slots. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Render time slots in the grid
     */
    renderSlots() {
        const grid = this.elements.slotsGrid;
        
        if (!grid) return;

        grid.innerHTML = '';

        // No slots available
        if (!this.slots || this.slots.length === 0) {
            // Check if selected date is today
            const isToday = this.isToday(this.selectedDate);
            const messageId = isToday ? 'outside-hours' : 'clinic-closed';
            this.showMessage(messageId);
            this.elements.gridContainer.style.display = 'none';
            return;
        }

        // Check if any available slots exist
        const availableSlots = this.slots.filter(s => s.available);
        if (availableSlots.length === 0) {
            // Check if selected date is today
            const isToday = this.isToday(this.selectedDate);
            const messageId = isToday ? 'outside-hours' : 'no-slots';
            this.showMessage(messageId);
            this.elements.gridContainer.style.display = 'none';
            return;
        }

        // Render each slot button
        this.slots.forEach(slot => {
            const button = this.createSlotButton(slot);
            grid.appendChild(button);
        });

        // Hide messages and show grid
        this.hideMessages();
        this.elements.gridContainer.style.display = 'block';
    }

    /**
     * Check if a date string is today
     * @param {string} dateStr - Date in Y-m-d format
     * @returns {boolean}
     */
    isToday(dateStr) {
        if (!dateStr) return false;
        const today = new Date();
        const todayStr = today.getFullYear() + '-' + 
                        String(today.getMonth() + 1).padStart(2, '0') + '-' +
                        String(today.getDate()).padStart(2, '0');
        return dateStr === todayStr;
    }

    /**
     * Create a single slot button element
     * @param {object} slot - Slot data
     * @returns {HTMLElement} Button element
     */
    createSlotButton(slot) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'slot-btn';
        button.textContent = slot.displayTime;
        button.dataset.time = slot.time;

        // Apply status classes
        if (slot.available) {
            button.classList.add('available');
            button.onclick = (e) => {
                e.preventDefault();
                this.selectSlot(button, slot.time);
            };
        } else if (slot.booked) {
            button.classList.add('booked');
            button.disabled = true;
            button.title = 'This slot is already booked';
        } else {
            // unavailable (past time or lunch break)
            button.classList.add('unavailable');
            button.disabled = true;
            button.title = slot.isPast ? 
                'This time has already passed' : 
                'Not available during this time';
        }

        return button;
    }

    /**
     * Handle slot selection
     * @param {HTMLElement} buttonEl - The clicked button
     * @param {string} time - Time in H:i format
     */
    selectSlot(buttonEl, time) {
        // Deselect previous selection
        document.querySelectorAll('.slot-btn.selected').forEach(btn => {
            btn.classList.remove('selected');
        });

        // Select new slot
        buttonEl.classList.add('selected');
        this.selectedTime = time;

        // Update hidden input
        this.elements.appointmentTime.value = time;

        // Show selected slot message
        this.showSelectedSlotMessage(buttonEl.textContent);
    }

    /**
     * Display the selected slot confirmation
     * @param {string} displayTime - Human-readable time
     */
    showSelectedSlotMessage(displayTime) {
        if (this.elements.selectedDisplay) {
            this.elements.selectedTime.textContent = displayTime;
            this.elements.selectedDisplay.style.display = 'block';

            // Scroll to message
            this.elements.selectedDisplay.scrollIntoView({ 
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    /**
     * Show loading indicator
     */
    showLoading() {
        if (this.elements.slotsLoading) {
            this.elements.slotsLoading.style.display = 'block';
        }
        if (this.elements.gridContainer) {
            this.elements.gridContainer.style.display = 'none';
        }
        this.hideMessages();
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        if (this.elements.slotsLoading) {
            this.elements.slotsLoading.style.display = 'none';
        }
    }

    /**
     * Show a status message
     * @param {string} messageId - ID of message to show
     */
    showMessage(messageId) {
        // Hide all messages first
        this.hideMessages();

        // Show specific message
        const messageEl = document.getElementById(`${messageId}-message`);
        if (messageEl) {
            messageEl.classList.remove('d-none');
        }

        if (this.elements.messageBox) {
            this.elements.messageBox.style.display = 'block';
        }
    }

    /**
     * Hide all status messages
     */
    hideMessages() {
        if (this.elements.messageBox) {
            this.elements.messageBox.style.display = 'none';
        }
        document.querySelectorAll('[id*="-message"]').forEach(el => {
            el.classList.add('d-none');
        });
    }

    /**
     * Show error message
     * @param {string} message - Error message
     */
    showError(message) {
        this.hideMessages();
        if (this.elements.messageBox) {
            this.elements.messageBox.innerHTML = 
                `<div class="alert alert-danger">⚠️ ${message}</div>`;
            this.elements.messageBox.style.display = 'block';
        }
    }

    /**
     * Get currently selected time
     * @returns {string|null} Selected time in H:i format
     */
    getSelectedTime() {
        return this.selectedTime;
    }

    /**
     * Reset slot selection
     */
    resetSelection() {
        this.selectedTime = null;
        this.elements.appointmentTime.value = '';
        document.querySelectorAll('.slot-btn.selected').forEach(btn => {
            btn.classList.remove('selected');
        });
        if (this.elements.selectedDisplay) {
            this.elements.selectedDisplay.style.display = 'none';
        }
    }
}

// Note: BookingSlotManager is instantiated in the calendar.blade.php view
// window.bookingSlots = new BookingSlotManager();
