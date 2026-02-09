@extends('layouts.staff')

@section('title', $title ?? 'Available Dentists')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $title ?? 'Available Dentists' }}</h1>
                    <p class="text-muted">{{ $subtitle ?? 'Dentists and their workload' }}</p>
                </div>
                <a href="{{ route('staff.queue') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Queue Board
                </a>
            </div>
        </div>
    </div>

    @if($dentists->count() > 0)
        <div class="row">
            @foreach($dentists as $item)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar-circle bg-primary text-white" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        {{ substr($item['dentist']->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-1">{{ $item['dentist']->name }}</h5>
                                    <p class="text-muted small mb-0">Dentist</p>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded text-center">
                                        <div class="fw-bold text-danger">{{ $item['in_treatment_count'] }}</div>
                                        <small class="text-muted">In Treatment</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-light p-3 rounded text-center">
                                        @if($item['next_appointment'])
                                            <div class="fw-bold text-success">{{ $item['next_appointment']->appointment_time }}</div>
                                            <small class="text-muted">Next Appt</small>
                                        @else
                                            <div class="fw-bold text-success">-</div>
                                            <small class="text-muted">No Schedule</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($item['next_appointment'])
                                <div class="mt-3 pt-3 border-top">
                                    <small class="text-muted">Next Patient:</small>
                                    <p class="mb-0 fw-bold">{{ $item['next_appointment']->patient_name }}</p>
                                    <small class="text-muted">{{ $item['next_appointment']->service->name ?? 'N/A' }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>No dentists scheduled</strong>
            <p class="mb-0">There are no dentists with appointments scheduled for today.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="text-center">
            <a href="{{ route('staff.queue') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Queue Board
            </a>
        </div>
    @endif
</div>
@endsection
