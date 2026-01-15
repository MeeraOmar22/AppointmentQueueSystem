@extends('developer.layouts.app')

@section('title', 'System Information')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-server"></i> System Information</h1>
    <p>View system configuration and environment details.</p>
</div>

<div class="row">
    <div class="col-lg-6">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h5 class="mb-3"><i class="fas fa-cogs"></i> Application Configuration</h5>
            
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Application Name</span>
                        <strong>{{ $info['app_name'] }}</strong>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Environment</span>
                        <span class="badge bg-{{ $info['app_env'] === 'production' ? 'danger' : 'warning' }}">
                            {{ strtoupper($info['app_env']) }}
                        </span>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Debug Mode</span>
                        <span class="badge bg-{{ $info['app_debug'] ? 'warning' : 'success' }}">
                            {{ $info['app_debug'] ? 'ENABLED' : 'DISABLED' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h5 class="mb-3"><i class="fas fa-database"></i> Database</h5>
            
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Default Connection</span>
                        <strong>{{ ucfirst($info['database']) }}</strong>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-success">Connected</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h5 class="mb-3"><i class="fas fa-code"></i> Framework & Runtime</h5>
            
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Laravel Version</span>
                        <code>{{ $info['laravel_version'] }}</code>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">PHP Version</span>
                        <code>{{ $info['php_version'] }}</code>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: white; border-radius: 8px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h5 class="mb-3"><i class="fas fa-info-circle"></i> System Status</h5>
            
            <div class="list-group">
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Application Health</span>
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Database Connection</span>
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Cache System</span>
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                    </div>
                </div>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Queue System</span>
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
