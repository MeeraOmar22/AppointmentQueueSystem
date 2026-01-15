@extends('layouts.app')

@section('title', 'My Feedback - Patient Portal')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-4">
                <a href="{{ route('patient.reports.appointments') }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <i class="fas fa-star me-2"></i>My Feedback History
            </h1>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Total Feedback Submitted</h6>
                    <h2 class="text-primary mb-0">{{ $totalFeedback }}</h2>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted mb-2">Average Rating</h6>
                    <div>
                        <h2 class="text-warning mb-1">{{ number_format($averageRating, 1) }} / 5.0</h2>
                        <div class="mb-0">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= floor($averageRating))
                                    <i class="fas fa-star text-warning"></i>
                                @elseif($i - $averageRating < 1)
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                @else
                                    <i class="far fa-star text-warning"></i>
                                @endif
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Records -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Feedback History</h5>
            <a href="{{ route('patient.reports.export-records') }}" class="btn btn-sm btn-light">
                <i class="fas fa-download me-2"></i>Export Records
            </a>
        </div>
        <div class="card-body">
            @if($feedback->count() > 0)
                <div class="list-group">
                    @foreach($feedback as $item)
                        <div class="list-group-item border-bottom">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-1">
                                        {{ $item->appointment->service->name ?? 'Service' }}
                                        @if($item->appointment->dentist)
                                            <span class="text-muted">- Dr. {{ $item->appointment->dentist->name }}</span>
                                        @endif
                                    </h6>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-calendar me-2"></i>
                                        {{ $item->created_at->format('d M Y, H:i') }}
                                    </p>
                                    <p class="mb-0">
                                        {{ $item->comments ?? 'No comment provided' }}
                                    </p>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $item->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <h5 class="text-warning mb-0">{{ $item->rating }} / 5</h5>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($feedback->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $feedback->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    You haven't submitted any feedback yet. We'd love to hear about your experience!
                </div>
            @endif
        </div>
    </div>

    <!-- Rating Distribution -->
    @if($feedback->count() > 0)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Rating Distribution</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @for($rating = 5; $rating >= 1; $rating--)
                        @php
                            $count = $ratingDistribution[$rating] ?? 0;
                            $percentage = $totalFeedback > 0 ? ($count / $totalFeedback) * 100 : 0;
                        @endphp
                        <div class="col-md-6 col-lg-12 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="min-width: 80px;">
                                    @for($i = 1; $i <= $rating; $i++)
                                        <i class="fas fa-star text-warning"></i>
                                    @endfor
                                    <span class="text-muted">{{ $rating }}</span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">
                                            {{ $count }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    @endif

    <!-- Navigation -->
    <div class="row mt-4">
        <div class="col-md-6">
            <a href="{{ route('patient.reports.appointments') }}" class="btn btn-outline-primary w-100">
                <i class="fas fa-calendar-check me-2"></i>View Appointments
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('patient.reports.treatments') }}" class="btn btn-outline-success w-100">
                <i class="fas fa-prescription-bottle me-2"></i>View Treatments
            </a>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        padding: 1.5rem;
    }
    
    .list-group-item:last-child {
        border-bottom: none !important;
    }
</style>
@endsection
