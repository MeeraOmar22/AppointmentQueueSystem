@extends('layouts.queue-display')

@section('title', 'Queue Board')

@section('content')
@php
    $currentServing = $inService->first();
    $nextQueues = $waiting->take(3);
@endphp

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }
    .live-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        display: flex;
        align-items: center;
        gap: 6px;
        background: white;
        padding: 8px 12px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .live-dot {
        width: 10px;
        height: 10px;
        background: #28a745;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    .live-text {
        color: #28a745;
        font-weight: 600;
        font-size: 12px;
    }
    .live-time {
        color: #666;
        font-size: 11px;
        margin-left: 8px;
        border-left: 1px solid #ddd;
        padding-left: 8px;
    }
</style>

<div class="container-fluid h-100 d-flex flex-column justify-content-center align-items-center position-relative">
    <!-- LIVE STATUS BADGE -->
    <div class="live-badge">
        <div class="live-dot"></div>
        <span class="live-text">LIVE</span>
        <span class="live-time" id="lastUpdateTime"></span>
    </div>

    <!-- MAIN CONTENT -->
    <div class="text-center" style="max-width: 1000px;" id="mainContent">
        <!-- NOW SERVING SECTION -->
        <div class="mb-5" id="nowServingSection" style="display: none;">
            <div class="bg-primary rounded-4 shadow-xl p-5" style="min-width: 450px; background: linear-gradient(135deg, #06A3DA 0%, #0088c3 100%); border: 3px solid rgba(255,255,255,0.2);">
                <div class="text-center">
                    <!-- MAIN QUEUE NUMBER -->
                    <p class="text-white" style="font-size: 18px; margin: 0; text-transform: uppercase; letter-spacing: 2px; opacity: 0.9;">Now Serving</p>
                    <p class="display-1 fw-bold text-white" id="currentQueue" style="margin: 12px 0 0 0; line-height: 1.1; font-size: 120px;">
                        A-00
                    </p>
                    
                    <!-- PATIENT INFO -->
                    <div style="margin-top: 12px; color: rgba(255,255,255,0.9);">
                        <p style="font-size: 18px; margin: 4px 0; font-weight: 500;" id="currentPatientName">Unknown</p>
                        <p style="font-size: 14px; margin: 4px 0; opacity: 0.9;" id="currentDentistInfo">In Treatment</p>
                    </div>
                    
                    <!-- ROOM DISPLAY -->
                    <div id="roomSection" style="display: none; margin-top: 16px;">
                        <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 12px 24px; display: inline-block;">
                            <p style="color: rgba(255,255,255,0.8); font-size: 12px; margin: 0; text-transform: uppercase; letter-spacing: 1px;">🚪 Room</p>
                            <p class="fw-bold text-white" id="currentRoom" style="margin: 4px 0 0 0; font-size: 24px;">—</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEXT IN QUEUE SECTION -->
        <div class="w-100" id="nextInQueueSection" style="display: none;">
            <p style="color: #666; font-size: 14px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Next in Line</p>
            <div class="d-flex justify-content-center gap-3" id="nextQueuesContainer" style="flex-wrap: wrap; margin-bottom: 40px;">
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="mt-auto mb-4 text-center">
        <p class="text-muted small" style="opacity: 0.6;">Updates automatically every 2 seconds</p>
    </div>
</div>

<script>
    let lastQueueData = null;
    let isInitialLoad = true;

    function updateDateTime() {
        const now = new Date();
        const lastUpdateEl = document.getElementById('lastUpdateTime');
        if (lastUpdateEl) {
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            lastUpdateEl.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }

    function fetchQueueData() {
        const timestamp = new Date().toLocaleTimeString();
        console.log(`[Queue Board] Fetching data at ${timestamp}...`);
        
        fetch('/api/queue-board/data', {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma': 'no-cache',
                'Expires': '0',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            cache: 'no-store'
        })
            .then(response => {
                console.log(`[Queue Board] Response status: ${response.status}`);
                if (!response.ok) {
                    console.error(`[Queue Board] HTTP Error: ${response.status} ${response.statusText}`);
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('[Queue Board] ✓ Data received:', {
                    inService: data.inService?.length || 0,
                    waiting: data.waiting?.length || 0
                });
                
                const queueJSON = JSON.stringify(data);
                
                // Always update on first load, then check for changes
                if (isInitialLoad || queueJSON !== lastQueueData) {
                    console.log('[Queue Board] Updating display...');
                    lastQueueData = queueJSON;
                    updateDisplay(data);
                    isInitialLoad = false;
                    updateDateTime();
                    console.log('[Queue Board] ✓ Display updated');
                } else {
                    console.log('[Queue Board] No changes detected, skipping update');
                }
            })
            .catch(error => {
                console.error('[Queue Board] ✗ Fetch Error:', error.message);
                console.error('[Queue Board] Stack:', error.stack);
            });
    }

    function updateDisplay(data) {
        console.log('[Queue Board] updateDisplay() called');
        
        try {
            // Get DOM elements
            const nowServingSection = document.getElementById('nowServingSection');
            const nextInQueueSection = document.getElementById('nextInQueueSection');
            const currentQueue = document.getElementById('currentQueue');
            const currentPatientName = document.getElementById('currentPatientName');
            const currentDentistInfo = document.getElementById('currentDentistInfo');
            const roomSection = document.getElementById('roomSection');
            const currentRoom = document.getElementById('currentRoom');
            const nextQueuesContainer = document.getElementById('nextQueuesContainer');
            
            console.log('[Queue Board] DOM elements found:', {
                nowServingSection: !!nowServingSection,
                nextInQueueSection: !!nextInQueueSection,
                currentQueue: !!currentQueue,
                nextQueuesContainer: !!nextQueuesContainer
            });
            
            // Update current serving
            if (data.inService && data.inService.length > 0) {
                const current = data.inService[0];
                const queueNum = 'A-' + String(current.queue_number).padStart(2, '0');
                
                console.log('[Queue Board] Current inService data:', JSON.stringify(current, null, 2));
                
                // Show the section
                if (nowServingSection) nowServingSection.style.display = 'block';
                
                // Update queue number
                if (currentQueue) {
                    currentQueue.textContent = queueNum;
                    console.log('[Queue Board] ✓ Current queue updated to:', queueNum);
                }
                
                // Update patient name
                if (currentPatientName) {
                    currentPatientName.textContent = current.appointment?.patient_name || 'Unknown';
                }
                
                // Update dentist info
                if (currentDentistInfo) {
                    const dentistName = current.dentist?.name || '';
                    const status = 'In Treatment';
                    currentDentistInfo.textContent = dentistName ? `${dentistName} • ${status}` : status;
                }
                
                // Show room if available
                console.log('[Queue Board] Checking room data:', {
                    hasRoom: !!current.room,
                    roomObject: current.room,
                    hasRoomNumber: !!(current.room && current.room.room_number),
                    roomNumber: current.room?.room_number
                });
                
                if (current.room && current.room.room_number) {
                    if (roomSection) roomSection.style.display = 'block';
                    if (currentRoom) currentRoom.textContent = current.room.room_number;
                    console.log('[Queue Board] ✓ Room displayed:', current.room.room_number);
                } else {
                    if (roomSection) roomSection.style.display = 'none';
                    console.log('[Queue Board] No room data');
                }
            } else {
                // Hide the section if no one in service
                if (nowServingSection) nowServingSection.style.display = 'none';
                if (roomSection) roomSection.style.display = 'none';
                console.log('[Queue Board] No patients in treatment');
            }

            // Update next queues
            if (nextQueuesContainer) {
                if (data.waiting && data.waiting.length > 0) {
                    let html = '';
                    data.waiting.slice(0, 3).forEach((queue) => {
                        const patientName = (queue.appointment?.patient_name || 'Unknown').substring(0, 18);
                        const queueNum = 'A-' + String(queue.queue_number).padStart(2, '0');
                        html += `
                            <div class="bg-white rounded-3 shadow-sm p-4 text-center" style="min-width: 140px; border-left: 4px solid #06A3DA;">
                                <p class="display-6 fw-bold text-primary mb-2" style="margin: 0;">${queueNum}</p>
                                <p class="text-muted small mb-0" style="font-size: 12px;">${patientName}</p>
                            </div>
                        `;
                    });
                    nextQueuesContainer.innerHTML = html;
                    if (nextInQueueSection) nextInQueueSection.style.display = 'block';
                    console.log('[Queue Board] ✓ Next queues updated:', data.waiting.length, 'patients waiting');
                } else {
                    nextQueuesContainer.innerHTML = '';
                    if (nextInQueueSection) nextInQueueSection.style.display = 'none';
                    console.log('[Queue Board] No patients waiting');
                }
            } else {
                console.warn('[Queue Board] Next container not found!');
            }
        } catch (error) {
            console.error('[Queue Board] ✗ Error in updateDisplay:', error.message);
            console.error('[Queue Board] Stack:', error.stack);
        }
    }

    // Initial update
    console.log('[Queue Board] Initializing...');
    console.log('[Queue Board] Current time:', new Date().toLocaleTimeString());
    
    // Call once immediately
    updateDateTime();
    fetchQueueData();
    
    console.log('[Queue Board] Setting up auto-refresh interval (2000ms)...');
    
    // Setup interval - this must happen immediately
    const queueRefreshInterval = setInterval(() => {
        console.log('[Queue Board] Auto-refresh triggered at', new Date().toLocaleTimeString());
        fetchQueueData();
    }, 2000);
    
    console.log('[Queue Board] ✓ Interval ID:', queueRefreshInterval);
    console.log('[Queue Board] ✓ Setup complete - Auto-refresh ACTIVE');

    // Cleanup on unload
    window.addEventListener('beforeunload', () => {
        console.log('[Queue Board] Page unloading, clearing intervals...');
        clearInterval(queueRefreshInterval);
    });
</script>
@endsection
