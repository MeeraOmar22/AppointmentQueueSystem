<!-- 
    Time Slots Display Component
    For: /resources/views/booking/partials/time-slots.blade.php
    
    Usage in Blade:
    @include('booking.partials.time-slots', ['slots' => $slots])
-->

<div class="time-slots-wrapper">
    
    <!-- Status Messages -->
    <div id="slots-message" style="display: none;">
        <div id="clinic-closed-message" class="alert alert-warning d-none">
            ⏸️ <strong>Clinic Closed</strong><br>
            The clinic is closed on this day. Please select another date.
        </div>
        
        <div id="no-slots-message" class="alert alert-info d-none">
            📭 <strong>No Available Slots</strong><br>
            All slots are booked for this date. Please try another day.
        </div>
        
        <div id="lunch-break-message" class="alert alert-secondary d-none">
            🍽️ <strong>Lunch Break</strong><br>
            Lunch break is from 13:00-14:00. Slots during this time are not available.
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="slots-loading" class="text-center py-4" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading time slots...</span>
        </div>
        <p class="mt-2 text-muted">Fetching available slots...</p>
    </div>

    <!-- Slots Grid -->
    <div id="slots-grid-container" style="display: none;">
        <div class="slots-grid-wrapper">
            <div id="slots-grid" class="slots-grid">
                <!-- Populated by JavaScript -->
            </div>
        </div>
        
        <!-- Selected Slot Display -->
        <div id="selected-slot-display" class="alert alert-success mt-3" style="display: none;">
            ✓ <strong>Selected Time:</strong> <span id="selected-time"></span>
        </div>
    </div>

</div>

<!-- Styling for Time Slots -->
<style>
    /* Slots Grid Container */
    .slots-grid-wrapper {
        margin: 20px 0;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
    }

    .slots-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 12px;
    }

    /* Individual Slot Button */
    .slot-btn {
        padding: 12px 8px;
        border: 2px solid #ddd;
        border-radius: 6px;
        background: white;
        color: #333;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    /* Slot States */

    /* Available Slot - Green */
    .slot-btn.available {
        border-color: #4caf50;
        background: #e8f5e9;
        color: #2e7d32;
    }

    .slot-btn.available:hover {
        background: #c8e6c9;
        border-color: #388e3c;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(76, 175, 80, 0.2);
    }

    .slot-btn.available:active {
        transform: translateY(0);
    }

    /* Selected Slot - Blue */
    .slot-btn.selected {
        border-color: #1976d2;
        background: #1976d2;
        color: white;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
    }

    .slot-btn.selected:hover {
        background: #1565c0;
        border-color: #1565c0;
        transform: translateY(-2px);
    }

    /* Booked Slot - Red */
    .slot-btn.booked {
        border-color: #f44336;
        background: #ffebee;
        color: #c62828;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .slot-btn.booked:hover {
        cursor: not-allowed;
    }

    /* Unavailable Slot (Past/Lunch) - Gray */
    .slot-btn.unavailable {
        border-color: #ccc;
        background: #f5f5f5;
        color: #999;
        cursor: not-allowed;
        opacity: 0.5;
    }

    .slot-btn.unavailable:hover {
        cursor: not-allowed;
        background: #f5f5f5;
    }

    /* Disabled Button State */
    .slot-btn:disabled {
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Legend / Explanation */
    .slot-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 6px;
        font-size: 13px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        border: 2px solid;
    }

    .legend-color.available {
        background: #e8f5e9;
        border-color: #4caf50;
    }

    .legend-color.booked {
        background: #ffebee;
        border-color: #f44336;
    }

    .legend-color.unavailable {
        background: #f5f5f5;
        border-color: #ccc;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .slots-grid {
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 8px;
        }

        .slot-btn {
            padding: 10px 6px;
            font-size: 12px;
        }

        .slot-legend {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<!-- JavaScript for Slot Interaction -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Render slots when data is available
    window.renderTimeSlots = function(slots) {
        const grid = document.getElementById('slots-grid');
        const messageBox = document.getElementById('slots-message');
        const gridContainer = document.getElementById('slots-grid-container');
        const closedMsg = document.getElementById('clinic-closed-message');
        const noSlotsMsg = document.getElementById('no-slots-message');

        // Hide all messages
        document.querySelectorAll('[id*="-message"]').forEach(el => 
            el.classList.add('d-none')
        );

        if (!slots || slots.length === 0) {
            // Clinic is closed
            closedMsg.classList.remove('d-none');
            messageBox.style.display = 'block';
            gridContainer.style.display = 'none';
            return;
        }

        // Check if all slots are unavailable
        const availableSlots = slots.filter(s => s.available);
        if (availableSlots.length === 0) {
            noSlotsMsg.classList.remove('d-none');
            messageBox.style.display = 'block';
            gridContainer.style.display = 'none';
            return;
        }

        // Render slots
        grid.innerHTML = '';
        slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `slot-btn slot-btn-${slot.status}`;
            btn.textContent = slot.displayTime;
            btn.dataset.time = slot.time;
            btn.disabled = slot.disabled;

            if (slot.available) {
                btn.classList.add('available');
                btn.onclick = () => selectSlot(btn, slot.time);
            } else if (slot.booked) {
                btn.classList.add('booked');
            } else {
                btn.classList.add('unavailable');
            }

            grid.appendChild(btn);
        });

        messageBox.style.display = 'none';
        gridContainer.style.display = 'block';
    };

    // Select a time slot
    window.selectSlot = function(buttonEl, time) {
        // Deselect previous selection
        document.querySelectorAll('.slot-btn.selected').forEach(btn => 
            btn.classList.remove('selected')
        );

        // Select new slot
        buttonEl.classList.add('selected');
        document.getElementById('appointment_time').value = time;

        // Show selected slot message
        const displayTime = buttonEl.textContent;
        document.getElementById('selected-time').textContent = displayTime;
        document.getElementById('selected-slot-display').style.display = 'block';
    };

    // Show loading indicator
    window.showSlotsLoading = function() {
        document.getElementById('slots-loading').style.display = 'block';
        document.getElementById('slots-grid-container').style.display = 'none';
        document.getElementById('slots-message').style.display = 'none';
    };

    // Hide loading indicator
    window.hideSlotsLoading = function() {
        document.getElementById('slots-loading').style.display = 'none';
    };
});
</script>
