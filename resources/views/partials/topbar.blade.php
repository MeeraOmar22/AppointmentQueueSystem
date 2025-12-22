<!-- Topbar Start -->
<div class="topbar container-fluid bg-light ps-5 pe-0">
    <div class="row gx-0">
        <div class="col-md-6 text-center text-lg-start mb-2 mb-lg-0">
            <div class="d-inline-flex align-items-center">
                <small class="py-2">
                    <i class="far fa-clock text-primary me-2"></i>Today's Hours: 
                    @if(isset($operatingHours) && $operatingHours->isNotEmpty())
                        @php
                            $today = now()->format('l'); // Get current day name (e.g., Monday)
                            $todayHours = $operatingHours->where('day_of_week', $today);
                        @endphp
                        @if($todayHours->isNotEmpty())
                            @foreach($todayHours as $index => $hour)
                                @if($hour->is_closed)
                                    <span class="text-danger fw-bold">Closed</span>
                                @else
                                    @if($hour->session_label)
                                        <span class="badge bg-light text-dark me-1">{{ $hour->session_label }}</span>
                                    @endif
                                    {{ date('g:i a', strtotime($hour->start_time)) }} - {{ date('g:i a', strtotime($hour->end_time)) }}
                                    @if(!$loop->last)
                                        <span class="mx-1">|</span>
                                    @endif
                                @endif
                            @endforeach
                        @else
                            <span class="text-muted">Not available</span>
                        @endif
                    @else
                        Mon - Sat: 9:00 am - 9:00 pm
                    @endif
                </small>
            </div>
        </div>
        <div class="col-md-6 text-center text-lg-end">
            <div class="position-relative d-inline-flex align-items-center bg-primary text-white top-shape px-5">
                <div class="me-3 pe-3 border-end py-2">
                    <p class="m-0"><i class="fa fa-envelope-open me-2"></i>klinikgigihelmy@gmail.com</p>
                </div>
                <div class="me-3 pe-3 border-end py-2">
                    <p class="m-0"><i class="fa fa-phone-alt me-2"></i>06-677 1940</p>
                </div>
                <div class="py-2">
                    <a href="https://wa.me/message/PZ6KMZFQVZ22I1" target="_blank" class="text-white text-decoration-none">
                        <p class="m-0"><i class="fab fa-whatsapp me-2"></i>WhatsApp</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->
