@extends('layouts.staff')

@section('title', 'Responses Received')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Responses Received</h1>
                    <p class="text-muted">All feedback submissions from patients</p>
                </div>
                <a href="{{ route('staff.feedback.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
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
                                <th>Appointment Date</th>
                                <th>Dentist</th>
                                <th>Rating</th>
                                <th>Comments</th>
                                <th>Would Recommend</th>
                                <th>Submitted</th>
                                <th>Action</th>
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
                                        {{ $feedback->appointment->appointment_date->format('d M Y') }}<br>
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
                                        @if($feedback->comments)
                                            <small>{{ Str::limit($feedback->comments, 50) }}</small>
                                        @else
                                            <small class="text-muted">No comments</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($feedback->would_recommend)
                                            <span class="badge bg-success">Yes</span>
                                        @else
                                            <span class="badge bg-warning">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $feedback->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('staff.feedback.show', $feedback->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
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
            <strong>No responses yet</strong> - Patient feedback submissions will appear here once received.
        </div>
    @endif
</div>
@endsection
