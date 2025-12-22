@extends('layouts.staff')

@section('title', 'API Testing')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: #091E3E;">
            <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>API Testing Tool
        </h2>
        <p class="text-muted mb-0">
            Test and debug API endpoints in real-time
        </p>
    </div>
    <div>
        <a href="/staff/developer/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Track Appointment API -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-primary text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="mb-0">
                    <i class="bi bi-geo-alt-fill me-2"></i>Track Appointment API
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Visit Code</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="trackCode" 
                        placeholder="e.g., DNT-20251222-001"
                    >
                    <small class="text-muted">Enter a valid visit code to test the tracking API</small>
                </div>
                <button class="btn btn-primary w-100" onclick="testTrackAPI()">
                    <i class="bi bi-play-fill me-2"></i>Test Track API
                </button>
                <div class="mt-3">
                    <strong>Endpoint:</strong>
                    <code class="d-block bg-light p-2 rounded">GET /api/track/{code}</code>
                </div>
                <div id="trackResult" class="mt-3"></div>
            </div>
        </div>
    </div>

    <!-- Staff Appointments API -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-success text-white" style="border-radius: 16px 16px 0 0;">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check-fill me-2"></i>Staff Appointments API
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <p class="text-muted mb-0">
                        Fetches today's appointments with stats and queue information
                    </p>
                </div>
                <button class="btn btn-success w-100" onclick="testStaffAppointmentsAPI()">
                    <i class="bi bi-play-fill me-2"></i>Test Appointments API
                </button>
                <div class="mt-3">
                    <strong>Endpoint:</strong>
                    <code class="d-block bg-light p-2 rounded">GET /api/staff/appointments</code>
                </div>
                <div id="appointmentsResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <!-- Custom API Tester -->
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-warning text-dark" style="border-radius: 16px 16px 0 0;">
                <h5 class="mb-0">
                    <i class="bi bi-terminal-fill me-2"></i>Custom API Request
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Method</label>
                        <select class="form-select" id="customMethod">
                            <option value="GET" selected>GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                    <div class="col-md-10">
                        <label class="form-label fw-semibold">Endpoint URL</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="customEndpoint" 
                            placeholder="e.g., /api/track/DNT-20251222-001"
                        >
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-semibold">Request Body (JSON) - Optional for POST/PUT</label>
                    <textarea 
                        class="form-control" 
                        id="customBody" 
                        rows="4"
                        placeholder='{"key": "value"}'
                    ></textarea>
                </div>
                <button class="btn btn-warning text-dark w-100 mt-3" onclick="testCustomAPI()">
                    <i class="bi bi-send-fill me-2"></i>Send Request
                </button>
                <div id="customResult" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function testTrackAPI() {
        const code = document.getElementById('trackCode').value;
        if (!code) {
            showResult('trackResult', 'error', 'Please enter a visit code');
            return;
        }

        showResult('trackResult', 'loading', 'Testing API...');

        fetch(`/api/track/${code}`)
            .then(response => response.json())
            .then(data => {
                showResult('trackResult', 'success', JSON.stringify(data, null, 2));
            })
            .catch(error => {
                showResult('trackResult', 'error', error.message);
            });
    }

    function testStaffAppointmentsAPI() {
        showResult('appointmentsResult', 'loading', 'Testing API...');

        fetch('/api/staff/appointments')
            .then(response => response.json())
            .then(data => {
                showResult('appointmentsResult', 'success', JSON.stringify(data, null, 2));
            })
            .catch(error => {
                showResult('appointmentsResult', 'error', error.message);
            });
    }

    function testCustomAPI() {
        const method = document.getElementById('customMethod').value;
        const endpoint = document.getElementById('customEndpoint').value;
        const body = document.getElementById('customBody').value;

        if (!endpoint) {
            showResult('customResult', 'error', 'Please enter an endpoint URL');
            return;
        }

        showResult('customResult', 'loading', 'Sending request...');

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        };

        if ((method === 'POST' || method === 'PUT') && body) {
            try {
                JSON.parse(body); // Validate JSON
                options.body = body;
            } catch (e) {
                showResult('customResult', 'error', 'Invalid JSON in request body');
                return;
            }
        }

        fetch(endpoint, options)
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(JSON.stringify(data, null, 2));
                }
                return data;
            })
            .then(data => {
                showResult('customResult', 'success', JSON.stringify(data, null, 2));
            })
            .catch(error => {
                showResult('customResult', 'error', error.message);
            });
    }

    function showResult(elementId, type, message) {
        const element = document.getElementById(elementId);
        let className = '';
        let icon = '';

        switch(type) {
            case 'success':
                className = 'alert-success';
                icon = '<i class="bi bi-check-circle-fill me-2"></i>';
                break;
            case 'error':
                className = 'alert-danger';
                icon = '<i class="bi bi-x-circle-fill me-2"></i>';
                break;
            case 'loading':
                className = 'alert-info';
                icon = '<i class="bi bi-hourglass-split me-2"></i>';
                break;
        }

        element.innerHTML = `
            <div class="alert ${className}">
                ${icon}
                <strong>${type === 'loading' ? 'Loading...' : type === 'success' ? 'Success!' : 'Error!'}</strong>
                <pre class="mb-0 mt-2" style="white-space: pre-wrap; font-size: 0.85rem;">${message}</pre>
            </div>
        `;
    }
</script>
@endsection
