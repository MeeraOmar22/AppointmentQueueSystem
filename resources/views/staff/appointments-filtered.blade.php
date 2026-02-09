@extends('layouts.staff')

@section('title', $title ?? 'Appointments')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $title ?? 'Appointments' }}</h1>
                    <p class="text-muted">{{ $subtitle ?? 'Appointment details' }}</p>
                </div>
                <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    @if($appointments->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Dentist</th>
                                <th>Service</th>
                                <th>Appointment Date & Time</th>
                                <th>Status</th>
                                @if($filter === 'queued' || $filter === 'in_treatment')
                                    <th>Queue #</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                                <tr>
                                    <td>
                                        <strong>{{ $appointment->patient_name }}</strong><br>
                                        <small class="text-muted">{{ $appointment->patient_phone }}</small>
                                    </td>
                                    <td>
                                        {{ $appointment->dentist->name ?? 'Unassigned' }}
                                    </td>
                                    <td>
                                        {{ $appointment->service->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $appointment->appointment_date->format('d M Y') }}<br>
                                        <small class="text-muted">{{ $appointment->appointment_time }}</small>
                                    </td>
                                    <td>
                                        @switch($appointment->status->value)
                                            @case('booked')
                                                <span class="badge bg-info">Booked</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-info">Confirmed</span>
                                                @break
                                            @case('checked_in')
                                                <span class="badge bg-success">Checked In</span>
                                                @break
                                            @case('waiting')
                                                <span class="badge bg-warning">Waiting</span>
                                                @break
                                            @case('in_treatment')
                                                <span class="badge bg-primary">In Treatment</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-success">Completed</span>
                                                @break
                                            @case('feedback_scheduled')
                                                <span class="badge bg-info">Feedback Scheduled</span>
                                                @break
                                            @case('feedback_sent')
                                                <span class="badge bg-info">Feedback Sent</span>
                                                @break
                                            @case('no_show')
                                                <span class="badge bg-secondary">No Show</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $appointment->status->label() }}</span>
                                        @endswitch
                                    </td>
                                    @if($filter === 'queued' || $filter === 'in_treatment')
                                        <td>
                                            @if($appointment->queue)
                                                <span class="badge bg-light text-dark">{{ $appointment->queue->queue_number }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($appointment->status->value === 'booked' || $appointment->status->value === 'confirmed')
                                                <form action="{{ route('staff.appointments.store') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Check In">
                                                        <i class="bi bi-check-circle"></i> Check In
                                                    </button>
                                                </form>
                                            @elseif($appointment->status->value === 'checked_in')
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#callPatientModal{{ $appointment->id }}" title="Call Patient">
                                                    <i class="bi bi-telephone"></i> Call
                                                </button>
                                            @elseif($appointment->status->value === 'in_treatment')
                                                <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-info" title="View Details">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            @elseif($appointment->status->value === 'completed')
                                                <span class="text-success"><i class="bi bi-check-lg"></i> Completed</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        {{-- Previous Page Link --}}
                        @if ($appointments->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $appointments->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($appointments->getUrlRange(1, $appointments->lastPage()) as $page => $url)
                            @if ($page == $appointments->currentPage())
                                <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($appointments->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $appointments->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    @else
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>No appointments found</strong>
            <p class="mb-0">There are no appointments matching this filter for today.</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="text-center">
            <a href="{{ route('staff.appointments.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    @endif
</div>
@endsection
