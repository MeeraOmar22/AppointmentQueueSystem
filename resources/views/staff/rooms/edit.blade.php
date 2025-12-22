@extends('layouts.staff')

@section('title', 'Edit Treatment Room')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Edit Treatment Room</h3>
    <p class="text-muted mb-0">Update room configuration</p>
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
                <form action="{{ route('staff.rooms.update', $room) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                    <input type="text" 
                        class="form-control @error('room_number') is-invalid @enderror" 
                        id="room_number" 
                        name="room_number" 
                        value="{{ old('room_number', $room->room_number) }}"
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
                        value="{{ old('capacity', $room->capacity) }}"
                        required>
                    <small class="text-muted">Number of patients this room can accommodate</small>
                    @error('capacity')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Clinic Location</label>
                    <div class="form-control bg-light" style="border: 1px solid #dee2e6;">
                        {{ $clinicName }}
                    </div>
                    <small class="text-muted">Clinic location is fixed for this deployment</small>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Room Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" 
                        id="status" 
                        name="status" 
                        required>
                        <option value="available" @selected(old('status', $room->status) === 'available')>
                            Available
                        </option>
                        <option value="occupied" @selected(old('status', $room->status) === 'occupied')>
                            Occupied / In Use
                        </option>
                    </select>
                    <small class="text-muted">Status is automatically managed during patient treatment</small>
                    @error('status')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
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
@endsection
