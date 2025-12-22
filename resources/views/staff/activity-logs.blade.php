@extends('layouts.staff')

@section('title', 'Activity Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Activity Logs</h3>
        <p class="text-muted mb-0">Track all staff activities and changes</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/staff/activity-logs" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Type</label>
                <select name="model_type" class="form-select">
                    <option value="">All Types</option>
                    @foreach($modelTypes as $type)
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search description..." value="{{ request('search') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-2"></i>Apply Filters
                </button>
                <a href="/staff/activity-logs" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle me-2"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->user_name ?? 'System' }}</div>
                            </td>
                            <td style="vertical-align: middle;">
                                @if($log->action === 'created')
                                    <span class="badge bg-success"><i class="bi bi-plus-circle me-1"></i>Created</span>
                                @elseif($log->action === 'updated')
                                    <span class="badge bg-info"><i class="bi bi-pencil me-1"></i>Updated</span>
                                @elseif($log->action === 'deleted')
                                    <span class="badge bg-danger"><i class="bi bi-trash me-1"></i>Deleted</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($log->action) }}</span>
                                @endif
                            </td>
                            <td style="vertical-align: middle;"><span class="badge bg-light text-dark">{{ $log->model_type }}</span></td>
                            <td style="vertical-align: middle;">{{ $log->description }}</td>
                            <td class="text-muted small" style="vertical-align: middle;">{{ $log->ip_address ?? '—' }}</td>
                            <td class="text-end" style="vertical-align: middle;">
                                @if($log->old_values || $log->new_values)
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#logModal{{ $log->id }}">
                                        <i class="bi bi-eye"></i> View
                                    </button>

                                    <!-- Modal -->
                                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Activity Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h6 class="fw-bold mb-2">Description:</h6>
                                                    <p>{{ $log->description }}</p>
                                                    
                                                    @if($log->old_values)
                                                        <h6 class="fw-bold mb-2 mt-3">Old Values:</h6>
                                                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                    @endif
                                                    
                                                    @if($log->new_values)
                                                        <h6 class="fw-bold mb-2 mt-3">New Values:</h6>
                                                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                                                    @endif
                                                    
                                                    <div class="mt-3">
                                                        <small class="text-muted">
                                                            <strong>User:</strong> {{ $log->user_name }}<br>
                                                            <strong>IP:</strong> {{ $log->ip_address }}<br>
                                                            <strong>Time:</strong> {{ $log->created_at->format('M d, Y h:i A') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No activity logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
