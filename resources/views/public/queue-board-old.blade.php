@extends('layouts.queue-display')

@section('title', 'Queue Board')

@section('content')
@php
    $currentServing = $inService->first();
    $nextQueues = $waiting->take(3);
@endphp

<div class="container-fluid h-100 d-flex flex-column justify-content-center align-items-center">
    <!-- LIVE INDICATOR -->
    <div style="position: absolute; top: 20px; right: 20px; display: flex; align-items: center; gap: 8px;">
        <div style="width: 12px; height: 12px; background: #28a745; border-radius: 50%; animation: pulse 2s infinite;"></div>
        <span class="text-success fw-semibold small">LIVE</span>
        <span id="lastUpdateTime" class="text-muted small" style="margin-left: 10px; font-size: 11px;"></span>
    </div>

    <!-- LOADING SPINNER (hidden by default) -->
    <div id="loadingSpinner" style="position: absolute; top: 20px; left: 20px; display: none; align-items: center; gap: 8px;">
        <div style="width: 16px; height: 16px; border: 2px solid #f3f3f3; border-top: 2px solid #06A3DA; border-radius: 50%; animation: spin 1s linear infinite;"></div>
        <span class="text-muted small">Updating...</span>
    </div>

    <!-- NOW SERVING - Main Focus -->
    <div class="mb-5">
        <div class="bg-primary rounded-3 shadow-lg p-5" style="min-width: 500px; background: linear-gradient(135deg, #06A3DA 0%, #0088c3 100%);">
            <div class="text-center">
                <p class="text-white-50 mb-2 small text-uppercase fw-semibold">Now Serving</p>
                <p class="display-1 fw-bold text-white" id="currentQueue" style="margin: 0; line-height: 1;">
                    @if($currentServing)
                        A-{{ sprintf('%02d', $currentServing->queue_number) }}
                    @else
                        —
                    @endif
                </p>
                <div class="mt-4 pt-4 border-top border-white border-opacity-25" id="roomSection" style="display: none;">
                    <p class="text-white-50 small mb-2 text-uppercase fw-semibold">Treatment Room</p>
                    <p class="display-5 fw-bold text-white" style="margin: 0;" id="currentRoom">
                        @if($currentServing && $currentServing->room)
                            🚪 {{ $currentServing->room->room_number }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- NEXT IN QUEUE -->
    @if($nextQueues->count() > 0)
        <div class="w-100 text-center">
            <p class="text-muted small mb-3 text-uppercase fw-semibold">Next Up</p>
            <div class="d-flex justify-content-center gap-3" style="max-width: 900px; margin: 0 auto; flex-wrap: wrap;">
                @forelse($nextQueues as $queue)
                    <div class="bg-white rounded-3 shadow-sm p-3 text-center" style="min-width: 130px; border-top: 4px solid #06A3DA;">
                        <p class="display-5 fw-bold text-primary mb-1" style="margin: 0;">
                            A-{{ sprintf('%02d', $queue->queue_number) }}
                        </p>
                        <p class="text-muted small mb-0">{{ Str::limit($queue->appointment->patient_name ?? 'Unknown', 15) }}</p>
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="mt-auto mb-4 text-center">
        <p class="text-muted small">⏳ Please wait for your number to be called</p>
    </div>
</div>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

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
            lastUpdateEl.textContent = `Updated: ${hours}:${minutes}:${seconds}`;
        }
    }

    function showLoadingSpinner(show) {
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.style.display = show ? 'flex' : 'none';
        }
    }

    function fetchQueueData() {
        showLoadingSpinner(true);
        console.log('[Queue Board] Fetching data...', new Date().toLocaleTimeString());
        
        fetch('/api/queue-board/data', {
            method: 'GET',
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma': 'no-cache',
                'Expires': '0',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store'
        })
            .then(response => {
                if (!response.ok) {
                    console.error('[Queue Board] HTTP Error:', response.status);
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('[Queue Board] Data received:', data);
                const queueJSON = JSON.stringify(data);
                
                // Always update on first load, then check for changes
                if (isInitialLoad || queueJSON !== lastQueueData) {
                    lastQueueData = queueJSON;
                    updateDisplay(data);
                    isInitialLoad = false;
                    console.log('[Queue Board] Display updated');
                }
                
                // Update timestamp
                updateDateTime();
                showLoadingSpinner(false);
            })
            .catch(error => {
                console.error('[Queue Board] Fetch error:', error);
                showLoadingSpinner(false);
            });
    }

    function updateDisplay(data) {
        try {
            // Update current serving
            const currentQueue = document.getElementById('currentQueue');
            const roomSection = document.getElementById('roomSection');
            const currentRoom = document.getElementById('currentRoom');
            
            if (data.inService && data.inService.length > 0) {
                const current = data.inService[0];
                const queueNum = 'A-' + String(current.queue_number).padStart(2, '0');
                currentQueue.textContent = queueNum;
                console.log('[Queue Board] Current queue updated to:', queueNum);
                
                // Show room if available
                if (current.room) {
                    if (roomSection) roomSection.style.display = 'block';
                    if (currentRoom) currentRoom.textContent = '🚪 ' + current.room.room_number;
                } else {
                    if (roomSection) roomSection.style.display = 'none';
                }
            } else {
                currentQueue.textContent = '—';
                if (roomSection) roomSection.style.display = 'none';
            }

            // Update next queues
            const nextContainer = document.querySelector('.d-flex.justify-content-center.gap-3');
            if (nextContainer) {
                if (data.waiting && data.waiting.length > 0) {
                    let html = '';
                    data.waiting.slice(0, 3).forEach((queue) => {
                        const patientName = (queue.appointment?.patient_name || 'Unknown').substring(0, 15);
                        const queueNum = 'A-' + String(queue.queue_number).padStart(2, '0');
                        html += `
                            <div class="bg-white rounded-3 shadow-sm p-3 text-center" style="min-width: 130px; border-top: 4px solid #06A3DA;">
                                <p class="display-5 fw-bold text-primary mb-1" style="margin: 0;">${queueNum}</p>
                                <p class="text-muted small mb-0">${patientName}</p>
                            </div>
                        `;
                    });
                    nextContainer.innerHTML = html;
                    console.log('[Queue Board] Next queues updated:', data.waiting.length, 'waiting');
                } else {
                    nextContainer.innerHTML = '';
                }
            }
        } catch (error) {
            console.error('[Queue Board] Update display error:', error);
        }
    }

    // Initial update
    console.log('[Queue Board] Initializing...');
    updateDateTime();
    fetchQueueData();

    // Refresh every 2 seconds for live updates (every 2000ms)
    let queueRefreshInterval = setInterval(fetchQueueData, 2000);

    window.addEventListener('beforeunload', function() {
        clearInterval(queueRefreshInterval);
        console.log('[Queue Board] Page unloading, cleared intervals');
    });

    console.log('[Queue Board] Setup complete - Refreshing every 2 seconds');
</script>
@endsection
