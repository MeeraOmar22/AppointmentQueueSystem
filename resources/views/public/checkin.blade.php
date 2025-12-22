@extends('layouts.public')

@section('title', 'Self Check-In')

@section('content')
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-4 text-white">Self Check-In</h1>
            <span class="h6 text-white">Enter your Visit Code to check in</span>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="bg-light p-4 rounded shadow-sm mb-4">
                <h5 class="fw-bold mb-3">Check-In</h5>
                <p class="text-muted small">Use the Visit Code shown after booking. Works for online and walk-in bookings.</p>
                <form method="POST" action="{{ url('/checkin') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Visit Code</label>
                        <input type="text" name="visit_code" value="{{ old('visit_code', request('code')) }}" class="form-control form-control-lg" placeholder="DNT-20251220-004" required>
                        @error('visit_code')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-success w-100" id="checkinBtn">I've Arrived</button>
                </form>
            </div>

            <div class="alert alert-info border-0" role="alert">
                <i class="bi bi-lightbulb me-2"></i>
                No login needed. Queue number and room will be assigned automatically after you check in.
            </div>

            <!-- Operating Hours Section -->
            <div class="bg-light p-4 rounded shadow-sm">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Today's Operating Hours</h6>
                @if($operatingHours && $operatingHours->isNotEmpty())
                    @php
                        $today = now()->format('l');
                        $todayHours = $operatingHours->where('day_of_week', $today);
                    @endphp
                    @if($todayHours->isNotEmpty())
                        @foreach($todayHours as $hour)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-muted">{{ $today }}</span>
                                @if($hour->is_closed)
                                    <span class="badge bg-danger">Closed</span>
                                @else
                                    <span class="text-muted">
                                        {{ date('g:i a', strtotime($hour->start_time)) }} - {{ date('g:i a', strtotime($hour->end_time)) }}
                                        @if($hour->session_label)
                                            <span class="badge bg-light text-dark ms-2">{{ $hour->session_label }}</span>
                                        @endif
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">Hours not configured</p>
                    @endif
                @else
                    <p class="text-muted small mb-0">Hours not available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('checkinBtn');
        btn.disabled = true;
        btn.textContent = 'Checking In...';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-secondary');
    });
</script>
@endsection
