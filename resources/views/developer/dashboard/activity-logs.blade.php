@extends('developer.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-list"></i> Activity Logs</h1>
    <p>Monitor and track all system activities and changes.</p>
</div>

<div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
    <h5 class="mb-3"><i class="fas fa-filter"></i> Filters</h5>
    
    <form action="/developer/activity-logs" method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
            <label class="form-label">Action Type</label>
            <select name="action_type" class="form-select">
                <option value="">All Types</option>
                @foreach ($actionTypes as $type)
                    <option value="{{ $type }}" {{ request('action_type') === $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Model Type</label>
            <select name="model_type" class="form-select">
                <option value="">All Models</option>
                @foreach ($modelTypes as $type)
                    <option value="{{ $type }}" {{ request('model_type') === $type ? 'selected' : '' }}>
                        {{ $type }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">From Date</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>

        <div class="col-md-2">
            <label class="form-label">To Date</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-search"></i> Filter
            </button>
            <a href="/developer/activity-logs" class="btn btn-outline-secondary w-100">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>

    <div class="col-md-12">
        <label class="form-label">Search</label>
        <form action="/developer/activity-logs" method="GET">
            @foreach (request()->query() as $key => $value)
                @if ($key !== 'search')
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search description, user ID, model ID..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
</div>

<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Model</th>
                    <th>Model ID</th>
                    <th>Description</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>
                            <small>{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                        </td>
                        <td>
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
                        </td>
                        <td>
                            <small><strong>{{ $log->model_type }}</strong></small>
                        </td>
                        <td>
                            <small>#{{ $log->model_id }}</small>
                        </td>
                        <td>
                            <small>{{ Str::limit($log->description, 60) }}</small>
                        </td>
                        <td>
                            <small>
                                @if ($log->user)
                                    <i class="fas fa-user"></i> {{ $log->user->name }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </small>
                        </td>
                        <td>
                            <a href="/developer/activity-logs/{{ $log->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No activity logs found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $logs->links() }}
</div>
@endsection
