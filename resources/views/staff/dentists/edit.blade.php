@extends('layouts.staff')

@section('title', 'Edit Dentist')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Edit Dentist</h3>
    <p class="text-muted mb-0">Update dentist profile</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="/staff/dentists/{{ $dentist->id }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $dentist->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="photo" class="form-label">Photo</label>
                        @if($dentist->photo)
                            <div class="mb-2">
                                <img src="{{ asset($dentist->photo) }}" alt="{{ $dentist->name }}" style="max-width: 200px; height: auto; border-radius: 8px;">
                                <p class="text-muted small mt-1">Current photo</p>
                            </div>
                        @endif
                        <input type="file" name="photo" id="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Upload new photo to replace current (JPG, PNG, max 2MB)</small>
                        @error('photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="specialization" class="form-label">Specialization</label>
                        <input type="text" name="specialization" id="specialization" class="form-control @error('specialization') is-invalid @enderror" value="{{ old('specialization', $dentist->specialization) }}">
                        @error('specialization')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="years_of_experience" class="form-label">Years of Experience</label>
                        <input type="number" name="years_of_experience" id="years_of_experience" class="form-control @error('years_of_experience') is-invalid @enderror" value="{{ old('years_of_experience', $dentist->years_of_experience) }}" min="0">
                        @error('years_of_experience')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea name="bio" id="bio" class="form-control @error('bio') is-invalid @enderror" rows="4">{{ old('bio', $dentist->bio) }}</textarea>
                        <small class="text-muted">Brief description about the dentist</small>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $dentist->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $dentist->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="twitter_url" class="form-label">Twitter URL</label>
                            <input type="url" name="twitter_url" id="twitter_url" class="form-control @error('twitter_url') is-invalid @enderror" value="{{ old('twitter_url', $dentist->twitter_url) }}" placeholder="https://twitter.com/username">
                            @error('twitter_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="facebook_url" class="form-label">Facebook URL</label>
                            <input type="url" name="facebook_url" id="facebook_url" class="form-control @error('facebook_url') is-invalid @enderror" value="{{ old('facebook_url', $dentist->facebook_url) }}" placeholder="https://facebook.com/username">
                            @error('facebook_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                            <input type="url" name="linkedin_url" id="linkedin_url" class="form-control @error('linkedin_url') is-invalid @enderror" value="{{ old('linkedin_url', $dentist->linkedin_url) }}" placeholder="https://linkedin.com/in/username">
                            @error('linkedin_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="instagram_url" class="form-label">Instagram URL</label>
                            <input type="url" name="instagram_url" id="instagram_url" class="form-control @error('instagram_url') is-invalid @enderror" value="{{ old('instagram_url', $dentist->instagram_url) }}" placeholder="https://instagram.com/username">
                            @error('instagram_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $dentist->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $dentist->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Update
                        </button>
                        <a href="/staff/dentists" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
