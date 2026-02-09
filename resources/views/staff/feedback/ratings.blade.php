@extends('layouts.staff')

@section('title', 'Patient Ratings')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Patient Ratings</h1>
                    <p class="text-muted">Feedback scores and ratings from patients</p>
                </div>
                <a href="{{ route('staff.feedback.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Rating Summary Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Average Rating</h6>
                    <div class="rating mb-2">
                        @php
                            $avgRating = $feedbacks->avg('rating') ?? 0;
                            for($i = 1; $i <= 5; $i++) {
                                if($i <= round($avgRating)) {
                                    echo '<i class="bi bi-star-fill text-warning" style="font-size: 1.5rem;"></i>';
                                } else {
                                    echo '<i class="bi bi-star text-muted" style="font-size: 1.5rem;"></i>';
                                }
                            }
                        @endphp
                    </div>
                    <h3 class="mb-0">{{ number_format($avgRating, 1) }}/5</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">5 Star Ratings</h6>
                    <h3 class="mb-0 text-success">{{ $feedbacks->where('rating', 5)->count() }}</h3>
                    <small class="text-muted">Excellent</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Recommend Rate</h6>
                    <h3 class="mb-0 text-success">{{ $feedbacks->where('would_recommend', true)->count() }}</h3>
                    <small class="text-muted">Would recommend</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Feedback</h6>
                    <h3 class="mb-0">{{ $feedbacks->count() }}</h3>
                    <small class="text-muted">Responses</small>
                </div>
            </div>
        </div>
    </div>

    @if($feedbacks->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Appointment</th>
                                <th>Dentist</th>
                                <th>Rating</th>
                                <th>Service Quality</th>
                                <th>Staff Friendliness</th>
                                <th>Cleanliness</th>
                                <th>Recommend</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feedbacks as $feedback)
                                <tr>
                                    <td>
                                        <strong>{{ $feedback->patient_name }}</strong><br>
                                        <small class="text-muted">{{ $feedback->patient_phone }}</small>
                                    </td>
                                    <td>
                                        <small>{{ $feedback->appointment->appointment_date->format('d M Y') }}</small><br>
                                        <small class="text-muted">{{ $feedback->appointment->appointment_time }}</small>
                                    </td>
                                    <td>{{ $feedback->appointment->dentist->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="rating">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $feedback->rating)
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                @else
                                                    <i class="bi bi-star text-muted"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ in_array($feedback->service_quality, ['excellent', 'good']) ? 'success' : 'warning' }}">
                                            {{ $feedback->service_quality ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ in_array($feedback->staff_friendliness, ['excellent', 'good']) ? 'success' : 'warning' }}">
                                            {{ $feedback->staff_friendliness ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ in_array($feedback->cleanliness, ['excellent', 'good']) ? 'success' : 'warning' }}">
                                            {{ $feedback->cleanliness ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($feedback->would_recommend)
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Yes
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="bi bi-x-circle"></i> No
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $feedback->created_at->format('d M Y') }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    {{ $feedbacks->links('pagination::bootstrap-4') }}
                </nav>
            </div>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>No ratings available yet</strong> - Patient feedback will appear here once submitted.
        </div>
    @endif
</div>
@endsection
