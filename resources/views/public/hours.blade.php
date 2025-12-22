@extends('layouts.public')

@section('title', 'Operating Hours - Klinik Pergigian Helmy')

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-5">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Operating Hours</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/hours') }}" class="h4 text-white">Hours</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- Hours Start -->
<div class="container-fluid py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-12">
                <div class="text-center mx-auto mb-5" style="max-width: 600px;">
                    <h5 class="d-inline-block text-primary text-uppercase border-bottom border-5">Schedule</h5>
                    <h1 class="display-5 mb-4">Our Clinic Operating Hours</h1>
                    <p class="text-muted">We're here to serve you. Check our weekly schedule for both clinic locations.</p>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Seremban Branch -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Seremban Branch</h4>
                        <small>No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, 70450 Seremban</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Day</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysOfWeek as $day)
                                        <tr>
                                            <td class="fw-bold {{ now()->format('l') === $day ? 'text-primary' : '' }}">
                                                {{ $day }}
                                                @if(now()->format('l') === $day)
                                                    <span class="badge bg-primary ms-2">Today</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($hours[$day]) && $hours[$day]->count() > 0)
                                                    @foreach($hours[$day] as $hour)
                                                        <div class="mb-1">
                                                            @if($hour->session_label)
                                                                <span class="badge bg-light text-dark me-2">{{ $hour->session_label }}</span>
                                                            @endif
                                                            @if($hour->is_closed)
                                                                <span class="badge bg-danger">Closed</span>
                                                            @else
                                                                <span class="text-dark">{{ \Carbon\Carbon::parse($hour->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($hour->end_time)->format('h:i A') }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Not available</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-1"><i class="bi bi-telephone-fill text-primary me-2"></i><strong>06-677 1940</strong></p>
                            <p class="mb-0"><i class="bi bi-envelope-fill text-primary me-2"></i>klinikgigihelmy@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kuala Pilah Branch -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Kuala Pilah Branch</h4>
                        <small>No. 902, Jalan Raja Melewar, 72000 Kuala Pilah</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Day</th>
                                        <th>Hours</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysOfWeek as $day)
                                        <tr>
                                            <td class="fw-bold {{ now()->format('l') === $day ? 'text-primary' : '' }}">
                                                {{ $day }}
                                                @if(now()->format('l') === $day)
                                                    <span class="badge bg-primary ms-2">Today</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($hours[$day]) && $hours[$day]->count() > 0)
                                                    @foreach($hours[$day] as $hour)
                                                        <div class="mb-1">
                                                            @if($hour->session_label)
                                                                <span class="badge bg-light text-dark me-2">{{ $hour->session_label }}</span>
                                                            @endif
                                                            @if($hour->is_closed)
                                                                <span class="badge bg-danger">Closed</span>
                                                            @else
                                                                <span class="text-dark">{{ \Carbon\Carbon::parse($hour->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($hour->end_time)->format('h:i A') }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Not available</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-1"><i class="bi bi-telephone-fill text-primary me-2"></i><strong>06-4841237</strong></p>
                            <p class="mb-0"><i class="bi bi-envelope-fill text-primary me-2"></i>klinikgigihelmy@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-info-circle-fill me-2"></i>Important Notes</h5>
                    <ul class="mb-0">
                        <li>Walk-in patients are welcome during operating hours</li>
                        <li>We recommend booking an appointment in advance to avoid waiting</li>
                        <li>Operating hours may vary during public holidays</li>
                        <li>Emergency dental services may be available - please call ahead</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hours End -->
@endsection
