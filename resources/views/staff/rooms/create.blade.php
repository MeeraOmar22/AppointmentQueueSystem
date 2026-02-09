@extends('layouts.staff')

@section('title', 'Add Treatment Room')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Add Treatment Room</h3>
    <p class="text-muted mb-0">Create a new treatment room</p>
</div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Validation Errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

<div class="row">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form action="{{ route('staff.rooms.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                    <input type="text" 
                        class="form-control @error('room_number') is-invalid @enderror" 
                        id="room_number" 
                        name="room_number" 
                        placeholder="e.g., Room A, Room 1" 
                        value="{{ old('room_number') }}"
                        required>
                    @error('room_number')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Patient Capacity <span class="text-danger">*</span></label>
                    <input type="number" 
                        class="form-control @error('capacity') is-invalid @enderror" 
                        id="capacity" 
                        name="capacity" 
                        min="1" 
                        max="10" 
                        value="{{ old('capacity', 1) }}"
                        required>
                    <small class="text-muted">Number of patients this room can accommodate</small>
                    @error('capacity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', 1) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Enable room for scheduling (Active)</label>
                    <div class="form-text">Inactive rooms are hidden from auto-assignment without deleting the configuration.</div>
                </div>

                <div class="alert alert-info mb-4" role="alert">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Clinic:</strong> {{ $clinicName }}
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Create Room
                    </button>
                    <a href="{{ route('staff.rooms.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
</div>

<style>
    .card {
        border: none;
        border-top: 3px solid #0d6efd;
    }
</style>
@endsection
