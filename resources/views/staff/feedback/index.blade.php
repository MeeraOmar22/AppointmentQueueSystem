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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-chat-heart text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Feedback</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
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
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-hand-thumbs-up text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Would Recommend</h6>
                            <h3 class="mb-0">{{ $stats['would_recommend'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-check text-info" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">This Month</h6>
                            <h3 class="mb-0">{{ $stats['recent_count'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="card border-0 shadow-sm">
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
