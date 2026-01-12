<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment Completion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%);
            font-family: 'Open Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container-main {
            max-width: 900px;
            width: 100%;
        }

        .header-section {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header-section .queue-status {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            margin-top: 15px;
        }

        .queue-status.running {
            background: #10b981;
            border-color: #10b981;
        }

        .queue-status.paused {
            background: #ef4444;
            border-color: #ef4444;
        }

        .control-buttons {
            text-align: center;
            margin-bottom: 40px;
        }

        .control-buttons .btn {
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 10px;
            min-width: 200px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%);
            border: 2px solid white;
            color: white;
        }

        .control-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #0a2647 0%, #091E3E 100%);
            color: white;
        }

        .main-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .patient-card {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .patient-card.current {
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%);
            color: white;
        }

        .patient-card.next {
            background: #f0f9ff;
            border: 3px solid #091E3E;
            color: #333;
        }

        .patient-card.empty {
            background: #f3f4f6;
            color: #666;
            padding: 60px 30px;
        }

        .patient-number {
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 15px;
            line-height: 1;
        }

        .patient-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .patient-info {
            font-size: 1.1rem;
            margin: 10px 0;
            opacity: 0.9;
        }

        .patient-service {
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid rgba(255, 255, 255, 0.3);
        }

        .patient-card.next .patient-service {
            border-top-color: #bfdbfe;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-complete {
            padding: 20px 50px;
            font-size: 1.3rem;
            font-weight: 700;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 250px;
        }

        .btn-complete:hover {
            background: #059669;
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-complete:active {
            transform: scale(0.98);
        }

        .btn-complete:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        .info-text {
            text-align: center;
            color: #666;
            margin-top: 30px;
            font-size: 1rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            margin-top: 30px;
            text-align: center;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .alert {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            font-size: 1.1rem;
        }

        .room-badge {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 10px;
            font-size: 1rem;
        }

        .current .room-badge {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .waiting-count {
            background: #fef3c7;
            color: #92400e;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
            font-size: 1rem;
        }

        .call-whatsapp-btn {
            padding: 10px 20px;
            font-size: 0.95rem;
            background: #25d366;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .call-whatsapp-btn:hover {
            background: #20ba5a;
            text-decoration: none;
            color: white;
        }

        .keyboard-hint {
            text-align: center;
            color: #999;
            font-size: 0.9rem;
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 1.8rem;
            }

            .main-content {
                padding: 20px;
            }

            .patient-number {
                font-size: 2.5rem;
            }

            .patient-name {
                font-size: 1.5rem;
            }

            .btn-complete {
                padding: 15px 30px;
                font-size: 1.1rem;
                min-width: 200px;
            }

            .control-buttons .btn {
                min-width: 150px;
                padding: 12px 25px;
                font-size: 1rem;
            }
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .pulsing {
            animation: pulsing 2s ease-in-out infinite;
        }

        @keyframes pulsing {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
</head>
<body>
    <div class="container-main">
        <!-- Logo Header -->
        <div class="header-section">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="fa fa-tooth" style="font-size: 2.5rem; color: white; margin-right: 15px;"></i>
                <div style="text-align: left;">
                    <h1 style="margin: 0; font-size: 2rem; color: white;">Helmy Dental Clinic</h1>
                    <small style="color: rgba(255, 255, 255, 0.8); font-size: 0.85rem;">Treatment Completion</small>
                </div>
            </div>
            <div class="queue-status @if($isPaused) paused @else running @endif">
                @if($isPaused)
                    <i class="fas fa-pause-circle"></i> PAUSED
                @else
                    <i class="fas fa-circle"></i> RUNNING
                @endif
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="control-buttons">
            @if($isPaused)
                <button type="button" class="btn btn-success btn-lg" id="resumeBtn">
                    <i class="fas fa-play"></i> Resume Queue
                </button>
            @else
                <button type="button" class="btn btn-warning btn-lg" id="pauseBtn">
                    <i class="fas fa-pause"></i> Pause Queue
                </button>
            @endif
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Main Content -->
        <div class="main-content">
            <!-- Current Patient -->
            <div class="section-title">Currently Being Treated</div>
            
            @if($currentPatient)
                <div class="patient-card current pulsing">
                    <div class="patient-number">#{{ str_pad($currentPatient->queue->queue_number ?? '?', 3, '0', STR_PAD_LEFT) }}</div>
                    <div class="patient-name">{{ $currentPatient->patient_name }}</div>
                    <div class="patient-info">
                        <strong>Service:</strong> {{ $currentPatient->service->service_name ?? 'N/A' }}
                    </div>
                    @if($currentPatient->queue->treatment_room_id)
                        <div class="room-badge">
                            📍 {{ DB::table('treatment_rooms')->find($currentPatient->queue->treatment_room_id)->room_code }}
                        </div>
                    @endif
                    <div class="action-buttons">
                        <button type="button" class="btn-complete" data-id="{{ $currentPatient->id }}">
                            ✓ Mark Completed
                        </button>
                    </div>
                </div>
            @else
                <div class="patient-card empty">
                    <div style="font-size: 3rem; margin-bottom: 20px;">😊</div>
                    <div style="font-size: 1.5rem; font-weight: 600;">No Patient in Treatment</div>
                    <div style="font-size: 1rem; margin-top: 10px;">Waiting for next patient...</div>
                </div>
            @endif

            <!-- Next Patient -->
            <div class="section-title">Next Patient</div>
            
            @if($nextPatient)
                <div class="patient-card next">
                    <div class="patient-number">#{{ str_pad($nextPatient->queue->queue_number ?? '?', 3, '0', STR_PAD_LEFT) }}</div>
                    <div class="patient-name">{{ $nextPatient->patient_name }}</div>
                    <div class="patient-info">
                        @if($nextPatient->status === 'called')
                            <strong style="color: #dc2626;">🔴 CALLED - Please Proceed</strong>
                        @else
                            <strong style="color: #3b82f6;">Waiting...</strong>
                        @endif
                    </div>
                    <div class="patient-info">
                        <strong>Service:</strong> {{ $nextPatient->service->service_name ?? 'N/A' }}
                    </div>
                    <a href="https://wa.me/{{ str_replace(['+', '-', ' '], '', $nextPatient->phone) }}" target="_blank" class="call-whatsapp-btn">
                        <i class="fab fa-whatsapp"></i> Contact Patient
                    </a>
                </div>
            @else
                <div class="patient-card empty">
                    <div style="font-size: 3rem; margin-bottom: 20px;">✨</div>
                    <div style="font-size: 1.5rem; font-weight: 600;">Queue is Clear!</div>
                    <div style="font-size: 1rem; margin-top: 10px;">All patients completed</div>
                </div>
            @endif

            @if($nextPatient && $waitingCount > 0)
                <div class="info-text">
                    <span class="waiting-count">{{ $waitingCount }} more waiting...</span>
                </div>
            @endif

            <div class="keyboard-hint">
                💡 Treat patients and click "Mark Completed" - Next patient auto-called
            </div>
        </div>
    </div>

    <!-- Room Assignment Modal -->
    <div class="modal fade" id="roomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Treatment Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3"><strong>Select treatment room for next patient:</strong></p>
                    <select id="roomSelect" class="form-select form-select-lg">
                        <option value="">📍 No Room Assignment</option>
                        @foreach($treatmentRooms as $room)
                            <option value="{{ $room->id }}">📍 {{ $room->room_code }} - {{ $room->room_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success btn-lg" id="confirmCompleteBtn">
                        <i class="fas fa-check-circle"></i> Mark Completed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedAppointmentId = null;

        // Complete treatment button
        document.querySelectorAll('.btn-complete').forEach(btn => {
            btn.addEventListener('click', function() {
                selectedAppointmentId = this.dataset.id;
                document.getElementById('roomSelect').value = '';
                const modal = new bootstrap.Modal(document.getElementById('roomModal'));
                modal.show();
            });
        });

        // Confirm complete
        document.getElementById('confirmCompleteBtn').addEventListener('click', function() {
            if (!selectedAppointmentId) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Processing...';

            const roomId = document.getElementById('roomSelect').value;
            const formData = new FormData();
            formData.append('treatment_room_id', roomId);

            fetch(`/staff/treatment-completion/${selectedAppointmentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle"></i> Mark Completed';
                    alert('Error: ' + (data.error || data.message));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Mark Completed';
                alert('An error occurred');
            });
        });

        // Pause Queue
        document.getElementById('pauseBtn')?.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Pausing...';

            fetch('/staff/pause-queue', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-pause"></i> Pause Queue';
            });
        });

        // Resume Queue
        document.getElementById('resumeBtn')?.addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Resuming...';

            fetch('/staff/resume-queue', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-play"></i> Resume Queue';
            });
        });

        // Auto-refresh every 10 seconds
        setInterval(function() {
            fetch('/api/queue/status')
                .then(response => response.json())
                .then(data => {
                    // Optionally update UI without full reload
                    console.log('Queue status:', data);
                });
        }, 10000);
    </script>
</body>
</html>
