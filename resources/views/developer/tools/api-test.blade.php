@extends('developer.layouts.app')

@section('title', 'API Test')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-plug"></i> API Test Tool</h1>
    <p>Test API endpoints and view responses.</p>
</div>

<div class="row">
    <div class="col-lg-6">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h5 class="mb-3"><i class="fas fa-cog"></i> Request Configuration</h5>
            
            <form id="apiTestForm">
                <div class="form-group mb-3">
                    <label class="form-label">HTTP Method</label>
                    <select id="method" class="form-select">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Endpoint URL</label>
                    <input type="text" id="endpoint" class="form-control" placeholder="/api/queue/status" value="/api/queue/status">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Request Body (JSON)</label>
                    <textarea id="requestBody" class="form-control" rows="6" placeholder='{"key": "value"}'></textarea>
                    <small class="text-muted">Only for POST/PUT/PATCH requests</small>
                </div>

                <button type="button" id="sendButton" class="btn btn-primary w-100">
                    <i class="fas fa-paper-plane"></i> Send Request
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h5 class="mb-3"><i class="fas fa-server"></i> Response</h5>
            
            <div id="responseContainer" style="display: none;">
                <div class="mb-3">
                    <label class="form-label text-muted small">Status Code</label>
                    <p>
                        <span id="statusCode" class="badge bg-info">Waiting...</span>
                        <span id="statusTime" class="text-muted small ms-2"></span>
                    </p>
                </div>

                <hr>

                <label class="form-label">Response Body</label>
                <pre id="responseBody" style="background: #f3f4f6; padding: 1rem; border-radius: 6px; max-height: 400px; overflow-y: auto;">Loading...</pre>

                <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2" onclick="copyResponse()">
                    <i class="fas fa-copy"></i> Copy Response
                </button>
            </div>

            <div id="noResponse" style="text-align: center; color: #6b7280; padding: 2rem;">
                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                <p>Response will appear here</p>
            </div>
        </div>

        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h5 class="mb-3"><i class="fas fa-link"></i> Quick Links</h5>
            <div class="list-group">
                <button type="button" class="list-group-item list-group-item-action text-start" onclick="setEndpoint('/api/queue/status', 'GET')">
                    <small class="text-muted">GET</small><br>
                    /api/queue/status
                </button>
                <button type="button" class="list-group-item list-group-item-action text-start" onclick="setEndpoint('/api/rooms/status', 'GET')">
                    <small class="text-muted">GET</small><br>
                    /api/rooms/status
                </button>
                <button type="button" class="list-group-item list-group-item-action text-start" onclick="setEndpoint('/api/queue/stats', 'GET')">
                    <small class="text-muted">GET</small><br>
                    /api/queue/stats
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function setEndpoint(endpoint, method) {
    document.getElementById('endpoint').value = endpoint;
    document.getElementById('method').value = method;
    document.getElementById('requestBody').value = '';
}

document.getElementById('sendButton').addEventListener('click', async function() {
    const method = document.getElementById('method').value;
    const endpoint = document.getElementById('endpoint').value;
    const bodyText = document.getElementById('requestBody').value;

    if (!endpoint) {
        alert('Please enter an endpoint URL');
        return;
    }

    const startTime = Date.now();
    document.getElementById('responseContainer').style.display = 'none';
    document.getElementById('noResponse').style.display = 'block';

    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        };

        if (bodyText && ['POST', 'PUT', 'PATCH'].includes(method)) {
            options.body = bodyText;
        }

        const response = await fetch(endpoint, options);
        const endTime = Date.now();
        const duration = endTime - startTime;

        const responseText = await response.text();
        let responseData;
        try {
            responseData = JSON.parse(responseText);
            responseData = JSON.stringify(responseData, null, 2);
        } catch {
            responseData = responseText;
        }

        let statusColor = 'bg-success';
        if (response.status >= 400 && response.status < 500) {
            statusColor = 'bg-warning';
        } else if (response.status >= 500) {
            statusColor = 'bg-danger';
        }

        document.getElementById('statusCode').innerHTML = `<span class="badge ${statusColor}">${response.status}</span>`;
        document.getElementById('statusTime').textContent = `(${duration}ms)`;
        document.getElementById('responseBody').textContent = responseData;
        document.getElementById('responseContainer').style.display = 'block';
        document.getElementById('noResponse').style.display = 'none';

    } catch (error) {
        document.getElementById('statusCode').innerHTML = '<span class="badge bg-danger">Error</span>';
        document.getElementById('responseBody').textContent = error.message;
        document.getElementById('responseContainer').style.display = 'block';
        document.getElementById('noResponse').style.display = 'none';
    }
});

function copyResponse() {
    const responseBody = document.getElementById('responseBody').textContent;
    navigator.clipboard.writeText(responseBody).then(() => {
        alert('Response copied to clipboard!');
    });
}
</script>
@endsection
