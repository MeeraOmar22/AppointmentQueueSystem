@extends('layouts.staff')

@section('title', 'Patient Feedback')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">Patient Feedback</h1>
            <p class="text-muted">View and analyze patient feedback and ratings</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="{{ route('staff.feedback.responses') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                     onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-chat-heart text-primary" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Responses Received</h6>
                                <h3 class="mb-0">{{ $stats['total_feedback'] }}</h3>
                                <small class="text-success">{{ $stats['response_rate'] }}% response rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('staff.feedback.pending') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                     onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Pending Responses</h6>
                                <h3 class="mb-0 text-warning">{{ $stats['pending_responses'] }}</h3>
                                <small class="text-muted">Awaiting patient feedback</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('staff.feedback.overdue') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                     onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Overdue Responses</h6>
                                <h3 class="mb-0 text-danger">{{ $stats['overdue_responses'] }}</h3>
                                <small class="text-muted">No response in 7+ days</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('staff.feedback.ratings') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100" style="cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" 
                     onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 0.5rem 1rem rgba(0, 0, 0, 0.15)';"
                     onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='';">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="bi bi-star-fill text-warning" style="font-size: 2rem;"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Average Rating</h6>
                                <h3 class="mb-0">{{ $stats['average_rating'] ?? 'N/A' }}/5</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Pending Responses Section -->
    @if($pendingRequests->count() > 0 || $overdueRequests->count() > 0)
    <div class="card border-0 shadow-sm mb-4 border-start border-warning border-5">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-hourglass-split text-warning"></i>
                ⏳ Pending Patient Responses ({{ $pendingRequests->count() }})
                @if($stats['critically_overdue'] > 0)
                    <span class="badge bg-danger ms-2">{{ $stats['critically_overdue'] }} Critical</span>
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if($pendingRequests->count() > 0)
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
                                        <small class="text-muted">{{ $request->request_sent_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $request->isCriticallyOverdue() ? 'danger' : ($request->isOverdue() ? 'warning' : 'info') }}">
                                            {{ $request->daysSinceSent() }} days
                                        </span>
                                    </td>
                                    <td>
                                        @if($request->reminder_count > 0)
                                            <small class="text-muted">{{ $request->reminder_count }} reminder(s) sent</small>
                                        @else
                                            <small class="text-muted">No reminders yet</small>
                                        @endif
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
            @else
                <p class="text-muted text-center py-4">✅ All feedback requests have been responded to!</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Feedback List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-chat-left-text"></i>
                📋 Received Feedback ({{ $feedbacks->count() }})
            </h5>
        </div>
        <div class="card-body">
            <h5 class="card-title mb-4">Recent Feedback</h5>
            
            @if($feedbacks->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Rating</th>
                                <th>Quality</th>
                                <th>Friendliness</th>
                                <th>Cleanliness</th>
                                <th>Recommend</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedbacks as $feedback)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $feedback->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $feedback->patient_name }}</div>
                                        <small class="text-muted">{{ $feedback->patient_phone }}</small>
                                    </td>
                                    <td>{{ $feedback->appointment->service->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $feedback->rating)
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                @else
                                                    <i class="bi bi-star text-muted"></i>
                                                @endif
                                            @endfor
                                            <span class="ms-2 fw-bold">{{ $feedback->rating }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($feedback->service_quality)
                                            <span class="badge bg-{{ $feedback->service_quality == 'excellent' ? 'success' : ($feedback->service_quality == 'good' ? 'primary' : ($feedback->service_quality == 'fair' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($feedback->service_quality) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($feedback->staff_friendliness)
                                            <span class="badge bg-{{ $feedback->staff_friendliness == 'excellent' ? 'success' : ($feedback->staff_friendliness == 'good' ? 'primary' : ($feedback->staff_friendliness == 'fair' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($feedback->staff_friendliness) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($feedback->cleanliness)
                                            <span class="badge bg-{{ $feedback->cleanliness == 'excellent' ? 'success' : ($feedback->cleanliness == 'good' ? 'primary' : ($feedback->cleanliness == 'fair' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($feedback->cleanliness) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($feedback->would_recommend)
                                            <i class="bi bi-check-circle text-success"></i> Yes
                                        @else
                                            <i class="bi bi-x-circle text-warning"></i> No
                                        @endif
                                    </td>
                                    <td>
                                        @if($feedback->comments)
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $feedback->id }}">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        @else
                                            <span class="text-muted">No comments</span>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Feedback Modal -->
                                @if($feedback->comments)
                                <div class="modal fade" id="feedbackModal{{ $feedback->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Feedback from {{ $feedback->patient_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Visit Code:</strong> {{ $feedback->appointment->visit_code }}
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Date:</strong> {{ $feedback->created_at->format('d M Y, H:i') }}
                                                </div>
                                                <div class="mb-3">
                                                    <strong>Rating:</strong>
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $feedback->rating)
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                        @else
                                                            <i class="bi bi-star text-muted"></i>
                                                        @endif
                                                    @endfor
                                                </div>
                                                <div>
                                                    <strong>Comments:</strong>
                                                    <p class="mt-2">{{ $feedback->comments }}</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $feedbacks->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-chat-heart text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">No feedback received yet</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
