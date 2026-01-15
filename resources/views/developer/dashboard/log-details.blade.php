@extends('developer.layouts.app')

@section('title', 'Activity Log Details')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-magnifying-glass"></i> Log Details</h1>
            <p>View detailed information about this activity log entry.</p>
        </div>
        <a href="/developer/activity-logs" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Log Information</h5>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Action Type</label>
                    <p>
                        @php
                            $colors = [
                                'created' => '#3b82f6',
                                'updated' => '#f59e0b',
                                'deleted' => '#ef4444',
                                'restored' => '#10b981',
                            ];
                            $color = $colors[$log->action_type] ?? '#6b7280';
                        @endphp
                        <span class="badge" style="background-color: {{ $color }};">
                            {{ ucfirst($log->action_type) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Timestamp</label>
                    <p>{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Model Type</label>
                    <p><strong>{{ $log->model_type }}</strong></p>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Model ID</label>
                    <p><code>#{{ $log->model_id }}</code></p>
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label text-muted small">Description</label>
                <p>{{ $log->description }}</p>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label text-muted small">User</label>
                <p>
                    @if ($log->user)
                        <i class="fas fa-user"></i> 
                        <a href="#">{{ $log->user->name }}</a>
                        <small class="text-muted">({{ $log->user->email }})</small>
                    @else
                        <span class="text-muted">System (Automated)</span>
                    @endif
                </p>
            </div>
        </div>

        @if ($log->old_values || $log->new_values)
            <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h5 class="mb-3"><i class="fas fa-exchange-alt"></i> Data Changes</h5>
                
                @if ($log->old_values)
                    <div class="mb-3">
                        <label class="form-label">Before (Old Values)</label>
                        <pre style="background: #f3f4f6; padding: 1rem; border-radius: 6px; max-height: 300px; overflow-y: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif

                @if ($log->new_values)
                    <div class="mb-3">
                        <label class="form-label">After (New Values)</label>
                        <pre style="background: #f3f4f6; padding: 1rem; border-radius: 6px; max-height: 300px; overflow-y: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 80px;">
            <h5 class="mb-3"><i class="fas fa-tags"></i> Meta Information</h5>
            
            <div class="mb-3">
                <label class="form-label text-muted small">Log ID</label>
                <p><code>{{ $log->id }}</code></p>
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label text-muted small">Created At</label>
                <p>
                    {{ $log->created_at->format('M d, Y') }}<br>
                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                </p>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small">Relative Time</label>
                <p>{{ $log->created_at->diffForHumans() }}</p>
            </div>

            <hr>

            @if ($log->user)
                <div class="mb-3">
                    <label class="form-label text-muted small">User</label>
                    <p>
                        <strong>{{ $log->user->name }}</strong><br>
                        <small class="text-muted">{{ $log->user->email }}</small><br>
                        <small class="text-muted">Role: {{ ucfirst($log->user->role) }}</small>
                    </p>
                </div>

                <hr>
            @endif

            <div class="mb-3">
                <label class="form-label text-muted small">Related Resource</label>
                <p>
                    <strong>{{ $log->model_type }}</strong><br>
                    <code>#{{ $log->model_id }}</code>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
