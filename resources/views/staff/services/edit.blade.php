@extends('layouts.staff')

@section('title', 'Edit Service')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Edit Service</h3>
    <p class="text-muted mb-0">Update service details</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="/staff/services/{{ $service->id }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Service Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Service Image</label>
                        @if($service->image)
                            <div class="mb-2">
                                <img src="{{ asset($service->image) }}" alt="{{ $service->name }}" style="max-width: 200px; height: auto; border-radius: 8px;">
                                <p class="text-muted small mt-1">Current image</p>
                            </div>
                        @endif
                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Upload new image to replace current (JPG, PNG, max 2MB)</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price (RM) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" required>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                        <input type="number" name="duration_minutes" id="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', $service->duration_minutes ?? $service->estimated_duration) }}" required>
                        @error('duration_minutes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $service->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $service->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update
                        </button>
                        <a href="/staff/services" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
