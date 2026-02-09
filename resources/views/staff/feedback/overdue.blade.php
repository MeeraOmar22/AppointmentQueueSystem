@extends('layouts.staff')

@section('title', 'Overdue Responses')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Overdue Responses</h1>
                    <p class="text-muted">Patients who haven't responded in 7+ days</p>
                </div>
                <a href="{{ route('staff.feedback.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    @if($overdueRequests->count() > 0)
        <div class="card border-0 shadow-sm border-start border-danger border-5">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle text-danger"></i>
                    Requires Follow-up
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Appointment Date</th>
                                <th>Dentist</th>
                                <th>Request Sent</th>
                                <th>Days Overdue</th>
                                <th>Severity</th>
                                <th>Reminders Sent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueRequests as $request)
                                <tr class="table-{{ $request->isCriticallyOverdue() ? 'danger' : 'warning' }}">
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
                                        <span class="badge bg-{{ $request->isCriticallyOverdue() ? 'danger' : 'warning' }}">
                                            {{ $request->daysSinceSent() }} days
                                        </span>
                                    </td>
                                    <td>
                                        @if($request->isCriticallyOverdue())
                                            <span class="badge bg-danger">CRITICAL (14+ days)</span>
                                        @else
                                            <span class="badge bg-warning">Overdue (7-14 days)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->reminder_count }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" formaction="{{ route('staff.feedback.send-reminder', $request->id) }}" 
                                                    class="btn btn-sm btn-outline-primary" title="Send another reminder">
                                                <i class="bi bi-bell"></i> Send Reminder
                                            </button>
                                        </form>
                                        <form method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" formaction="{{ route('staff.feedback.skip-request', $request->id) }}" 
                                                    class="btn btn-sm btn-outline-secondary" title="No further follow-up"
                                                    onclick="return confirm('Mark as no follow-up required?')">
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
                    {{ $overdueRequests->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    @else
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle"></i>
            <strong>No overdue responses!</strong> All feedback requests are either pending (less than 7 days) or have been responded to.
        </div>
    @endif
</div>
@endsection
