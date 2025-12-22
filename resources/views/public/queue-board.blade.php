@extends('layouts.public')

@section('title', 'Queue Board')

@section('content')
<div class="container-fluid bg-dark text-white py-3">
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Queue Board</h3>
        <div class="small text-muted">Auto-refreshes every 15s</div>
    </div>
</div>

<div class="container-fluid py-4" style="background: #f4f6fb; min-height: 80vh;">
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="bg-white p-4 rounded shadow-sm">
                <h5 class="fw-bold mb-3">Now Serving</h5>
                @if($inService->count())
                    <div class="list-group list-group-flush">
                        @foreach($inService as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="font-size: 1.4rem;">A-{{ sprintf('%02d', $item->queue_number) }}</div>
                                    <div class="text-muted small">{{ optional($item->appointment)->patient_name }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-primary">Room {{ optional($item->appointment)->room ?? '—' }}</div>
                                    <div class="text-muted small">Dentist: {{ optional($item->appointment->dentist)->name ?? 'TBD' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No patient is being served right now.</p>
                @endif
            </div>
        </div>
        <div class="col-lg-6">
            <div class="bg-white p-4 rounded shadow-sm">
                <h5 class="fw-bold mb-3">Waiting</h5>
                @if($waiting->count())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Queue</th>
                                    <th>Patient</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($waiting as $item)
                                    <tr>
                                        <td class="fw-bold">A-{{ sprintf('%02d', $item->queue_number) }}</td>
                                        <td>{{ optional($item->appointment)->patient_name }}</td>
                                        <td>{{ optional($item->appointment)->room ?? '—' }}</td>
                                        <td><span class="badge bg-warning text-dark">Waiting</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No one waiting.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    // Auto refresh every 15s
    setInterval(() => location.reload(), 15000);
</script>
@endsection
