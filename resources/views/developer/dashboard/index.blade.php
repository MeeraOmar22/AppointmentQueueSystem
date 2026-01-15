@extends('developer.layouts.app')

@section('title', 'Developer Dashboard')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
    <p>Welcome to the developer tools panel. Monitor system activity and access developer utilities here.</p>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-2">Total Activity Logs</p>
                    <h3 class="mb-0">{{ number_format($totalLogs) }}</h3>
                </div>
                <i class="fas fa-list fa-2x" style="color: #3b82f6; opacity: 0.2;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-2">Today's Activity</p>
                    <h3 class="mb-0">{{ number_format($logsToday) }}</h3>
                </div>
                <i class="fas fa-calendar-alt fa-2x" style="color: #10b981; opacity: 0.2;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-2">Log Types</p>
                    <h3 class="mb-0">{{ count($logTypes) }}</h3>
                </div>
                <i class="fas fa-tags fa-2x" style="color: #f59e0b; opacity: 0.2;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted mb-2">Database Records</p>
                    <h3 class="mb-0">N/A</h3>
                </div>
                <i class="fas fa-database fa-2x" style="color: #ef4444; opacity: 0.2;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-history"></i> Recent Activity</h5>
                <a href="/developer/activity-logs" class="btn btn-sm btn-primary">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>Description</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentLogs->take(10) as $log)
                            <tr>
                                <td>
                                    <small>{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #3b82f6;">
                                        {{ ucfirst($log->action_type) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $log->model_type }}</small>
                                </td>
                                <td>
                                    <small>{{ Str::limit($log->description, 50) }}</small>
                                </td>
                                <td>
                                    <small>{{ $log->user?->name ?? 'System' }}</small>
                                </td>
                                <td>
                                    <a href="/developer/activity-logs/{{ $log->id }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No activity logs found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h5 class="mb-3"><i class="fas fa-tools"></i> Quick Tools</h5>
            <div class="list-group">
                <a href="/developer/api-test" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">API Test</h6>
                            <p class="mb-0 text-muted small">Test API endpoints</p>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </a>
                <a href="/developer/system-info" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">System Info</h6>
                            <p class="mb-0 text-muted small">View system configuration</p>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </a>
                <a href="/developer/database" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">Database Tools</h6>
                            <p class="mb-0 text-muted small">Manage database operations</p>
                        </div>
                        <i class="fas fa-arrow-right text-muted"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> System Status</h5>
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span>Application Status</span>
                        <span class="badge bg-success">Running</span>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span>Database Connection</span>
                        <span class="badge bg-success">Connected</span>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span>Laravel Version</span>
                        <span class="badge bg-info">{{ app()->version() }}</span>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span>PHP Version</span>
                        <span class="badge bg-info">{{ phpversion() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
