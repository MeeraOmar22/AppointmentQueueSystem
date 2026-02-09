/**
 * Calendar Booking System - JavaScript Logic
 * Handles calendar rendering, slot fetching, and booking submission
 */

class CalendarBooking {
    constructor() {
        this.currentDate = new Date();
        this.selectedDate = null;
        this.selectedTime = null;
        this.selectedClinic = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.renderCalendar();
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Clinic selection
        document.getElementById('clinic_id').addEventListener('change', (e) => {
            this.selectedClinic = e.target.value;
            if (this.selectedDate) {
                this.fetchSlots();
            }
        });

        // Calendar navigation
        document.getElementById('prevMonth').addEventListener('click', (e) => {
            e.preventDefault();
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            this.renderCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', (e) => {
            e.preventDefault();
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            this.renderCalendar();
        });

        // Form submission
        document.getElementById('submitBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.submitBooking();
        });
    }

    /**
     * Render mini calendar for the current month
     */
    renderCalendar() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();

        // Update month display
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;

        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        // Clear calendar
        const calendarGrid = document.getElementById('calendar');
        calendarGrid.innerHTML = '';

        // Day of week headers
        const dayHeaders = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        dayHeaders.forEach(day => {
            const header = document.createElement('div');
            header.style.fontWeight = 'bold';
            header.style.textAlign = 'center';
            header.style.paddingBottom = '10px';
            header.style.fontSize = '0.85rem';
            header.style.color = '#666';
            header.textContent = day;
            calendarGrid.appendChild(header);
        });

        // Previous month days
        for (let i = firstDay - 1; i >= 0; i--) {
            const dayNum = daysInPrevMonth - i;
            const dayEl = this.createDayElement(dayNum, 'other-month', null, true);
            calendarGrid.appendChild(dayEl);
        }

        // Current month days
        const today = new Date();
        for (let i = 1; i <= daysInMonth; i++) {
            const date = new Date(year, month, i);
            let className = '';
            let disabled = false;

            // Disable past dates
            if (date < today && date.toDateString() !== today.toDateString()) {
                className = 'disabled';
                disabled = true;
            }

            // Mark selected date
            if (this.selectedDate && date.toDateString() === this.selectedDate.toDateString()) {
                className += ' selected';
            }

            const dayEl = this.createDayElement(i, className, date, disabled);
            calendarGrid.appendChild(dayEl);
        }

        // Next month days
        const totalCells = calendarGrid.children.length - 7; // Subtract header
        const remainingCells = 42 - totalCells; // 6 rows × 7 days
        for (let i = 1; i <= remainingCells; i++) {
            const dayEl = this.createDayElement(i, 'other-month', null, true);
            calendarGrid.appendChild(dayEl);
        }
    }

    /**
     * Create individual day element
     */
    createDayElement(dayNum, className = '', date = null, disabled = false) {
        const dayEl = document.createElement('div');
        dayEl.className = `calendar-day ${className}`;
        dayEl.textContent = dayNum;

        if (!disabled && date && !className.includes('other-month')) {
            dayEl.addEventListener('click', () => {
                this.selectDate(date);
            });
        }

        return dayEl;
    }

    /**
     * Handle date selection
     */
    selectDate(date) {
        this.selectedDate = new Date(date);
        this.selectedTime = null;

        // Clear selected time display
        document.getElementById('selectedTimeInfo').style.display = 'none';
        document.getElementById('appointment_time').value = '';

        // Re-render calendar to show selection
        this.renderCalendar();

        // Update selected date display
        const options = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' };
        const dateString = date.toLocaleDateString('en-US', options);
        document.getElementById('selectedDateDisplay').textContent = dateString;
        document.getElementById('selectedDateInfo').style.display = 'block';

        // Store as YYYY-MM-DD
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        document.getElementById('appointment_date').value = `${year}-${month}-${day}`;

        // Fetch available slots
        if (this.selectedClinic) {
            this.fetchSlots();
        } else {
            alert('Please select a clinic first');
        }
    }

    /**
     * Fetch available slots via AJAX
     */
    async fetchSlots() {
        const date = document.getElementById('appointment_date').value;
        const clinicId = this.selectedClinic;

        if (!date || !clinicId) return;

        // Show loading
        document.getElementById('slotsLoading').style.display = 'block';
        document.getElementById('slotsContainer').style.display = 'none';
        document.getElementById('noDateMessage').style.display = 'none';
        document.getElementById('closedMessage').style.display = 'none';

        try {
            const response = await fetch(`/api/booking/slots?date=${date}&clinic_id=${clinicId}`);
            const result = await response.json();

            document.getElementById('slotsLoading').style.display = 'none';

            if (!result.success) {
                alert('Error: ' + result.message);
                return;
            }

            const data = result.data;

            // Handle closed clinic
            if (data.status === 'closed') {
                document.getElementById('closedReason').textContent = data.message;
                document.getElementById('closedMessage').style.display = 'block';
                return;
            }

            // Display slots
            this.renderSlots(data.slots);

            // Update summary
            document.getElementById('availableCount').textContent = data.summary.availableCount;
            document.getElementById('bookedCount').textContent = data.summary.bookedCount;

            document.getElementById('slotsContainer').style.display = 'block';

        } catch (error) {
            console.error('Error fetching slots:', error);
            alert('Failed to fetch available slots. Please try again.');
            document.getElementById('slotsLoading').style.display = 'none';
        }
    }

    /**
     * Render time slots grouped by session
     */
    renderSlots(slots) {
        const morningSlots = slots.filter(s => s.session === 'Morning');
        const afternoonSlots = slots.filter(s => s.session === 'Afternoon');

        // Render morning session
        if (morningSlots.length > 0) {
            document.getElementById('morningSession').style.display = 'block';
            const morningContainer = document.getElementById('morningSlots');
            morningContainer.innerHTML = '';
            morningSlots.forEach(slot => {
                morningContainer.appendChild(this.createSlotButton(slot));
            });
        } else {
            document.getElementById('morningSession').style.display = 'none';
        }

        // Show break time
        if (morningSlots.length > 0 && afternoonSlots.length > 0) {
            document.getElementById('breakSession').style.display = 'block';
        } else {
            document.getElementById('breakSession').style.display = 'none';
        }

        // Render afternoon session
        if (afternoonSlots.length > 0) {
            document.getElementById('afternoonSession').style.display = 'block';
            const afternoonContainer = document.getElementById('afternoonSlots');
            afternoonContainer.innerHTML = '';
            afternoonSlots.forEach(slot => {
                afternoonContainer.appendChild(this.createSlotButton(slot));
            });
        } else {
            document.getElementById('afternoonSession').style.display = 'none';
        }
    }

    /**
     * Create individual slot button
     */
    createSlotButton(slot) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `slot-btn ${slot.status}`;
        button.textContent = slot.displayTime;
        button.disabled = slot.disabled;

        if (!slot.disabled) {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectTime(slot.time, slot.displayTime);
            });
        }

        if (this.selectedTime === slot.time) {
            button.classList.add('selected');
        }

        return button;
    }

    /**
     * Handle time slot selection
     */
    selectTime(time, displayTime) {
        this.selectedTime = time;
        document.getElementById('appointment_time').value = time;
        document.getElementById('selectedTimeDisplay').textContent = displayTime;
        document.getElementById('selectedTimeInfo').style.display = 'block';

        // Update slot buttons - remove all selected and add to this one
        document.querySelectorAll('.slot-btn.available').forEach(btn => {
            btn.classList.remove('selected');
        });
        event.target.classList.add('selected');

        // Scroll to selected time info
        document.getElementById('selectedTimeInfo').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Submit booking form
     */
    submitBooking() {
        // Validate all required fields
        const errors = [];

        if (!this.selectedClinic) {
            errors.push('Please select a clinic');
        }

        const patientName = document.getElementById('patient_name').value.trim();
        if (!patientName) {
            errors.push('Please enter your name');
        }

        const patientPhone = document.getElementById('patient_phone').value.trim();
        if (!patientPhone) {
            errors.push('Please enter your phone number');
        }

        if (!/^[0-9]{10,11}$/.test(patientPhone)) {
            errors.push('Phone number must be 10-11 digits');
        }

        const serviceId = document.getElementById('service_id').value;
        if (!serviceId) {
            errors.push('Please select a service');
        }

        if (!this.selectedDate) {
            errors.push('Please select a date');
        }

        if (!this.selectedTime) {
            errors.push('Please select a time slot');
        }

        if (errors.length > 0) {
            alert('❌ Please fix the following:\n\n' + errors.map((e, i) => `${i + 1}. ${e}`).join('\n'));
            return;
        }

        // Prepare confirmation message
        const date = document.getElementById('selectedDateDisplay').textContent;
        const time = document.getElementById('selectedTimeDisplay').textContent;
        const serviceOption = document.getElementById('service_id').selectedOptions[0];
        const service = serviceOption.text;

        const confirmMessage = `✅ Confirm your booking:\n\nName: ${patientName}\nPhone: ${patientPhone}\nService: ${service}\nDate & Time: ${date} at ${time}\n\nProceed with booking?`;

        if (!confirm(confirmMessage)) {
            return;
        }

        // Populate hidden form
        document.getElementById('form_clinic_id').value = this.selectedClinic;
        document.getElementById('form_appointment_date').value = document.getElementById('appointment_date').value;
        document.getElementById('form_appointment_time').value = this.selectedTime;
        document.getElementById('form_patient_name').value = patientName;
        document.getElementById('form_patient_phone').value = patientPhone;
        document.getElementById('form_patient_email').value = document.getElementById('patient_email').value;
        document.getElementById('form_service_id').value = serviceId;

        // Disable submit button to prevent double submission
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Processing...';

        // Submit form
        document.getElementById('bookingForm').submit();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    new CalendarBooking();
});
