@extends('layouts.staff')

@section('title', 'Treatment Rooms')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Treatment Rooms</h3>
        <p class="text-muted mb-0">Manage treatment rooms and capacity</p>
    </div>
    <a href="{{ route('staff.rooms.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Room
    </a>
</div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Rooms</p>
                            <h4 class="text-primary fw-bold mb-0">{{ $stats['total_rooms'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Available</p>
                            <h4 class="text-success fw-bold mb-0">{{ $stats['available_rooms'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="text-muted small mb-1">In Use</p>
                            <h4 class="text-warning fw-bold mb-0">{{ $stats['occupied_rooms'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Clinic</p>
                            <h4 class="text-info fw-bold mb-0">{{ $stats['clinic_name'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="card table-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Room #</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Current Patient</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr>
                            <td>
                                <strong>Room {{ $room->room_number }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $room->capacity }} patient(s)</span>
                            </td>
                            <td>
                                @if ($room->status === 'available')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Available
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-hourglass-end"></i> In Use
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($room->currentPatient)
                                    <span class="text-muted">{{ $room->currentPatient->appointment->patient_name }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('staff.rooms.edit', $room) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('staff.rooms.destroy', $room) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this room?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No treatment rooms configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if ($rooms->hasPages())
        <div class="mt-4">
            {{ $rooms->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<style>
    .card {
        border: none;
        border-top: 3px solid #0d6efd;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
</style>
@endsection
