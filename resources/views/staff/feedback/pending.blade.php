@extends('layouts.staff')

@section('title', 'Pending Responses')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Pending Responses</h1>
                    <p class="text-muted">Patients waiting to submit feedback</p>
                </div>
                <a href="{{ route('staff.feedback.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    @if($pendingRequests->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Appointment Date</th>
                                <th>Dentist</th>
                                <th>Request Sent</th>
                                <th>Days Pending</th>
                                <th>Status</th>
                                <th>Reminders</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRequests as $request)
                                <tr class="{{ $request->isOverdue() ? 'table-warning' : '' }}">
                                    <td>
                                        <strong>{{ $request->patient_name }}</strong><br>
                                        <small class="text-muted">{{ $request->patient_phone }}</small>
                                    </td>
                                    <td>
                                        {{ $request->appointment->appointment_date->format('d M Y') }}<br>
                                        <small class="text-muted">{{ $request->appointment->appointment_time }}</small>
                                    </td>
                                    <td>{{ $request->appointment->dentist->name ?? 'N/A' }}</td>
                                    <td>
                                        <small class="text-muted">{{ $request->request_sent_at->format('d M Y') }}</small><br>
                                        <small class="text-muted">{{ $request->request_sent_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->isCriticallyOverdue() ? 'danger' : ($request->isOverdue() ? 'warning' : 'info') }}">
                                            {{ $request->daysSinceSent() }} days
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->isCriticallyOverdue() ? 'danger' : ($request->isOverdue() ? 'warning' : 'info') }}">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->reminder_count }} sent</span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" formaction="{{ route('staff.feedback.send-reminder', $request->id) }}" 
                                                    class="btn btn-sm btn-outline-primary" title="Send reminder">
                                                <i class="bi bi-bell"></i> Remind
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" formaction="{{ route('staff.feedback.skip-request', $request->id) }}" 
                                                    class="btn btn-sm btn-outline-secondary" title="Skip this request"
                                                    onclick="return confirm('Mark this as no follow-up required?')">
                                                <i class="bi bi-x"></i> Skip
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    {{ $pendingRequests->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    @else
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle"></i>
            <strong>All patients have responded!</strong> No pending feedback requests at this time.
        </div>
    @endif
</div>
@endsection
