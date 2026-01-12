<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting Area - Queue Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%);
            font-family: 'Open Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        .display-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.5rem;
            opacity: 0.9;
        }

        .current-patient-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .current-label {
            font-size: 1.2rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .current-patient-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .patient-number {
            font-size: 6rem;
            font-weight: 900;
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-right: 40px;
        }

        .patient-info h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .patient-info p {
            font-size: 1.3rem;
            color: #666;
            margin: 5px 0;
        }

        .room-display {
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
            background: linear-gradient(135deg, #091E3E 0%, #0a2647 100%);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            min-width: 200px;
        }

        .next-patients-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .next-label {
            font-size: 1.2rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .queue-list {
            list-style: none;
        }

        .queue-item {
            display: flex;
            align-items: center;
            padding: 20px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-left: 5px solid #091E3E;
            border-radius: 8px;
            font-size: 1.3rem;
        }

        .queue-number {
            font-size: 2rem;
            font-weight: 900;
            color: #091E3E;
            min-width: 80px;
            text-align: center;
        }

        .queue-name {
            flex: 1;
            margin-left: 20px;
            color: #333;
            font-weight: 600;
        }

        .status-badge {
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-called {
            background: #ff6b6b;
            color: white;
        }

        .status-waiting {
            background: #ffd43b;
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: white;
        }

        .empty-state i {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .empty-state p {
            font-size: 2rem;
            font-weight: 600;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .queue-paused {
            background: #fff3cd;
            border: 3px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .patient-number {
                font-size: 4rem;
                margin-right: 20px;
            }

            .patient-info h2 {
                font-size: 1.5rem;
            }

            .current-patient-card {
                flex-direction: column;
                text-align: center;
            }

            .room-display {
                margin-top: 20px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="display-container">
        <div class="header">
            <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i class="fa fa-tooth" style="font-size: 2.5rem; color: white; margin-right: 15px;"></i>
                <div style="text-align: left;">
                    <h1 style="margin: 0; font-size: 2rem; color: white;">Helmy Dental Clinic</h1>
                    <small style="color: rgba(255, 255, 255, 0.8); font-size: 0.85rem;">Waiting Area</small>
                </div>
            </div>
            <p>Please wait for your queue number to be called</p>
        </div>

        <div id="content">
            <!-- Content will be loaded here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fetch queue status every 3 seconds
        function updateDisplay() {
            fetch('/api/queue/status')
                .then(response => response.json())
                .then(data => {
                    updateUI(data);
                })
                .catch(error => console.error('Error fetching queue status:', error));
        }

        function updateUI(data) {
            const content = document.getElementById('content');
            let html = '';

            // Show pause status if paused
            if (data.isPaused) {
                html += `<div class="queue-paused">
                    <i class="fas fa-pause-circle"></i> Queue is Currently Paused
                </div>`;
            }

            // Current patient
            if (data.currentPatient) {
                const roomDisplay = data.currentPatient.room 
                    ? `Room ${data.currentPatient.room}: In Treatment`
                    : 'Waiting Area';
                html += `
                    <div class="current-patient-section pulse">
                        <div class="current-label">🔴 Now Being Treated</div>
                        <div class="current-patient-card">
                            <div class="patient-number">#${String(data.currentPatient.id).padStart(3, '0')}</div>
                            <div class="patient-info">
                                <h2>${data.currentPatient.patient_name}</h2>
                                <p><strong>Service:</strong> ${data.currentPatient.service}</p>
                            </div>
                            <div class="room-display">
                                📍 ${roomDisplay}
                            </div>
                        </div>
                    </div>
                `;
            } else if (data.called_patient) {
                const roomDisplay = data.called_patient.room 
                    ? `Room ${data.called_patient.room}: Proceed`
                    : 'Waiting Area';
                html += `
                    <div class="current-patient-section pulse">
                        <div class="current-label">🟡 Patient Called - Please Proceed</div>
                        <div class="current-patient-card">
                            <div class="patient-number">#${String(data.called_patient.id).padStart(3, '0')}</div>
                            <div class="patient-info">
                                <h2>${data.called_patient.patient_name}</h2>
                                <p><strong>Service:</strong> ${data.called_patient.service}</p>
                            </div>
                            <div class="room-display">
                                📍 ${roomDisplay}
                            </div>
                        </div>
                    </div>
                `;
            }

            // Next patients
            if (data.waitingCount > 0) {
                html += `
                    <div class="next-patients-section">
                        <div class="next-label">⏳ Patients Waiting (${data.waitingCount})</div>
                        <ul class="queue-list">
                            <li class="queue-item">
                                <div class="queue-number">Next...</div>
                                <div class="queue-name">Your turn coming soon</div>
                                <div class="status-badge status-waiting">Waiting</div>
                            </li>
                        </ul>
                    </div>
                `;
            } else if (!data.currentPatient && !data.calledPatient) {
                html += `
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <p>No patients waiting</p>
                        <p style="font-size: 1.2rem; margin-top: 10px;">Queue is clear</p>
                    </div>
                `;
            }

            content.innerHTML = html;
        }

        // Initial load
        updateDisplay();

        // Update every 3 seconds
        setInterval(updateDisplay, 3000);

        // Prevent screen from sleeping (optional)
        document.addEventListener('click', function() {
            document.body.style.opacity = 1;
        });
    </script>
</body>
</html>
