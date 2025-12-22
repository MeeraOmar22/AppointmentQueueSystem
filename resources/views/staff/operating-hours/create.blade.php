@extends('layouts.staff')

@section('title', 'Add Operating Hour')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold mb-1">Add Operating Hour</h3>
    <p class="text-muted mb-0">Create a new operating hour entry</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-body">
                <form method="POST" action="/staff/operating-hours">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="day_of_week" class="form-label">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" class="form-select @error('day_of_week') is-invalid @enderror" required>
                            <option value="">Select Day</option>
                            <option value="Monday" {{ old('day_of_week') == 'Monday' ? 'selected' : '' }}>Monday</option>
                            <option value="Tuesday" {{ old('day_of_week') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                            <option value="Wednesday" {{ old('day_of_week') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                            <option value="Thursday" {{ old('day_of_week') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                            <option value="Friday" {{ old('day_of_week') == 'Friday' ? 'selected' : '' }}>Friday</option>
                            <option value="Saturday" {{ old('day_of_week') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                            <option value="Sunday" {{ old('day_of_week') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                        </select>
                        @error('day_of_week')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="session_label" class="form-label">Session Label (Optional)</label>
                        <input type="text" name="session_label" id="session_label" class="form-control @error('session_label') is-invalid @enderror" value="{{ old('session_label') }}" placeholder="e.g., Morning, Afternoon, Evening">
                        <small class="text-muted">Use this to differentiate multiple sessions on the same day</small>
                        @error('session_label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="is_closed_create" name="is_closed" value="1" {{ old('is_closed') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_closed_create">
                            Closed on this day
                        </label>
                    </div>

                    <div id="timeInputsCreate">
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ old('start_time') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ old('end_time') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save
                        </button>
                        <a href="/staff/operating-hours" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('is_closed_create');
    const container = document.getElementById('timeInputsCreate');
    const startInput = document.getElementById('start_time');
    const endInput = document.getElementById('end_time');

    function applyToggle() {
        if (checkbox.checked) {
            container.style.display = 'none';
            startInput.disabled = true;
            endInput.disabled = true;
            startInput.required = false;
            endInput.required = false;
        } else {
            container.style.display = 'block';
            startInput.disabled = false;
            endInput.disabled = false;
            startInput.required = true;
            endInput.required = true;
        }
    }

    checkbox.addEventListener('change', applyToggle);
    applyToggle();
});
</script>
@endsection
