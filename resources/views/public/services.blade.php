@extends('layouts.public')

@section('title', 'Our Services - Klinik Pergigian Helmy')

@section('css')
<style>
    .service-card {
        transition: all 0.3s ease;
        border: 2px solid #f0f0f0;
        position: relative;
    }
    
    .service-card:hover {
        transform: translateY(-8px);
        border-color: #0d6efd;
        box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15) !important;
    }
    
    .service-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .service-duration {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        color: #6c757d;
        background: #f8f9fa;
        padding: 6px 12px;
        border-radius: 20px;
        width: fit-content;
    }
    
    .service-price {
        font-size: 1.4rem;
        font-weight: 700;
        color: #0d6efd;
    }
    
    .badge-online {
        background: linear-gradient(135deg, #28a745, #20c997);
        padding: 8px 16px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 25px;
    }
    
    .accordion-button {
        padding: 1rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        border: none;
    }
    
    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, #f8f9ff, #f0f5ff);
        color: #0d6efd;
        box-shadow: none;
    }
    
    .accordion-button:focus {
        box-shadow: none;
        border-color: #0d6efd;
    }
    
    .accordion-item {
        border: 1px solid #e9ecef;
        margin-bottom: 12px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .accordion-body {
        padding: 1.5rem;
        background: #fff;
    }
    
    .service-detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .service-detail-item:last-child {
        border-bottom: none;
    }
    
    .service-detail-label {
        color: #495057;
        font-size: 0.95rem;
    }
    
    .service-detail-price {
        font-weight: 600;
        color: #0d6efd;
        font-size: 1rem;
    }
    
    .section-header {
        margin-bottom: 3rem;
        padding-bottom: 1.5rem;
    }
    
    .section-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 0.5rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
        color: #6c757d;
    }
    
    .service-grid-separator {
        margin: 3rem 0;
        border: 0;
        border-top: 2px solid #f0f0f0;
    }
</style>
@endsection

@section('content')
<!-- Hero Start -->
<div class="container-fluid bg-primary py-5 hero-header mb-3">
    <div class="row py-3">
        <div class="col-12 text-center">
            <h1 class="display-3 text-white animated zoomIn">Services</h1>
            <a href="{{ url('/') }}" class="h4 text-white">Home</a>
            <i class="far fa-circle text-white px-2"></i>
            <a href="{{ url('/services') }}" class="h4 text-white">Services</a>
        </div>
    </div>
</div>
<!-- Hero End -->

<!-- SECTION 1: Online Bookable Services -->
<div class="container-fluid py-5">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">Book Online Now</h2>
            <p class="section-subtitle">Quick and easy appointment booking for our popular services • Get instant confirmation</p>
        </div>

        <!-- Service Cards Grid -->
        <div class="row g-4">
            @foreach($services as $service)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm service-card">
                        <!-- Service Image -->
                        <div class="position-relative overflow-hidden" style="height: 200px;">
                            @if($service->image)
                                <img class="img-fluid w-100 h-100" src="{{ asset($service->image) }}" alt="{{ $service->name }}" style="object-fit: cover;">
                            @else
                                <img class="img-fluid w-100 h-100" src="{{ asset('pergigianhelmy/img/service-' . (($loop->index % 6) + 1) . '.jpg') }}" alt="{{ $service->name }}" style="object-fit: cover;">
                            @endif
                            <!-- Badge -->
                            <span class="badge badge-online position-absolute top-0 end-0 m-3">
                                <i class="bi bi-check-circle me-1"></i> Book Now
                            </span>
                        </div>

                        <!-- Card Content -->
                        <div class="card-body service-info">
                            <div>
                                <h5 class="card-title fw-bold text-dark mb-1">{{ $service->name }}</h5>
                                <p class="card-text text-muted small mb-2">{{ Str::limit($service->description, 75) }}</p>
                            </div>
                            
                            <!-- Duration if available -->
                            @if($service->estimated_duration)
                                <span class="service-duration">
                                    <i class="bi bi-clock"></i>
                                    {{ $service->estimated_duration }} min
                                </span>
                            @endif

                            <!-- Price and Button -->
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <p class="text-muted small mb-1">Starting Price</p>
                                    <p class="service-price mb-0">RM {{ number_format($service->price, 2) }}</p>
                                </div>
                                <a href="{{ url('/book') }}" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="bi bi-calendar-check"></i> Book
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
<!-- SECTION 1 END -->

<!-- Divider -->
<div class="bg-light py-4">
    <div class="container">
        <hr class="my-0">
    </div>
</div>

<!-- SECTION 2: Complete Service & Price List -->
<div class="container-fluid bg-light py-5">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <h2 class="section-title">Complete Service Catalog</h2>
            <p class="section-subtitle">Full reference guide with detailed pricing for all available treatments</p>
        </div>

        <!-- Accordion Price List -->
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="priceListAccordion">
                    
                    <!-- Category 1: Preventive & Restorative -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true">
                                <i class="bi bi-tooth me-2 text-primary"></i> Preventive & Restorative Dentistry
                            </button>
                        </h2>
                        <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <!-- Amalgam Filling -->
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Amalgam Filling (Black Filling)</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="small text-muted mb-2"><strong>Permanent Teeth:</strong></p>
                                            <div class="service-detail-item">
                                                <span class="service-detail-label">1 surface</span>
                                                <span class="service-detail-price">RM70 – RM80</span>
                                            </div>
                                            <div class="service-detail-item">
                                                <span class="service-detail-label">2 surfaces</span>
                                                <span class="service-detail-price">RM85 – RM140</span>
                                            </div>
                                            <div class="service-detail-item">
                                                <span class="service-detail-label">3 surfaces</span>
                                                <span class="service-detail-price">RM145 – RM180</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="small text-muted mb-2"><strong>Primary (Baby) Teeth:</strong></p>
                                            <div class="service-detail-item">
                                                <span class="service-detail-label">1 surface</span>
                                                <span class="service-detail-price">RM60 – RM75</span>
                                            </div>
                                            <div class="service-detail-item">
                                                <span class="service-detail-label">2 surfaces</span>
                                                <span class="service-detail-price">RM70 – RM85</span>
                                            </div>
                                            <div class="service-detail-item">
                                                <span class="service-detail-label">3 surfaces</span>
                                                <span class="service-detail-price">RM85 – RM100</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="small text-muted mt-3 ps-0"><em>💡 Prices depend on the size of the filling</em></p>
                                </div>

                                <hr class="my-4">

                                <!-- Composite Filling -->
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Composite Filling (Tooth-Colored)</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">1 surface</span>
                                        <span class="service-detail-price">RM80 – RM100</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">2 surfaces</span>
                                        <span class="service-detail-price">RM100 – RM200</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">3 surfaces</span>
                                        <span class="service-detail-price">RM200 – RM300</span>
                                    </div>
                                    <p class="small text-muted mt-3"><em>💡 Prices depend on the size of the filling</em></p>
                                </div>

                                <hr class="my-4">

                                <!-- Composite Anterior Veneer -->
                                <div>
                                    <h6 class="fw-bold text-dark mb-3">Composite Anterior Veneer</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Per tooth</span>
                                        <span class="service-detail-price">RM200 – RM450</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 2: Endodontics -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                                <i class="bi bi-lightning-fill me-2 text-primary"></i> Endodontics (Root Canal)
                            </button>
                        </h2>
                        <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Pulp Capping</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Per tooth</span>
                                        <span class="service-detail-price">RM100 – RM120</span>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Pulpotomy</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Per tooth</span>
                                        <span class="service-detail-price">RM70 – RM80</span>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div>
                                    <h6 class="fw-bold text-dark mb-3">Root Canal Treatment</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Anterior tooth</span>
                                        <span class="service-detail-price">RM700 – RM800</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Premolar tooth</span>
                                        <span class="service-detail-price">RM800 – RM900</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Molar tooth</span>
                                        <span class="service-detail-price">RM900 – RM1000</span>
                                    </div>
                                    <p class="small text-muted mt-3"><em>💡 Clinic examination required. Prices vary by complexity</em></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 3: Prosthetics -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                                <i class="bi bi-ear-fill me-2 text-primary"></i> Prosthetics (Dentures)
                            </button>
                        </h2>
                        <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Partial Denture</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Acrylic – first tooth</span>
                                        <span class="service-detail-price">RM185</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Each additional tooth</span>
                                        <span class="service-detail-price">RM35</span>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div>
                                    <h6 class="fw-bold text-dark mb-3">Full Denture</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Full acrylic (per arch)</span>
                                        <span class="service-detail-price">RM800</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 4: Oral Surgery -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                                <i class="bi bi-activity me-2 text-primary"></i> Oral Surgery
                            </button>
                        </h2>
                        <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Tooth Extraction</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Anterior (front tooth)</span>
                                        <span class="service-detail-price">RM70 – RM120</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Posterior (back tooth)</span>
                                        <span class="service-detail-price">RM80 – RM200</span>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div>
                                    <h6 class="fw-bold text-dark mb-3">Surgical Extraction</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Impacted/retained root</span>
                                        <span class="service-detail-price">RM250 – RM600</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Wisdom tooth surgery</span>
                                        <span class="service-detail-price">RM800 – RM1000</span>
                                    </div>
                                    <p class="small text-muted mt-3"><em>💡 Price depends on extraction difficulty</em></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 5: Emergency Treatment -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                                <i class="bi bi-exclamation-triangle me-2 text-primary"></i> Emergency Treatment
                            </button>
                        </h2>
                        <div id="collapse5" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Dressing</span>
                                    <span class="service-detail-price">RM70 – RM100</span>
                                </div>
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Medication</span>
                                    <span class="service-detail-price">RM55 – RM90</span>
                                </div>
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Infected socket</span>
                                    <span class="service-detail-price">RM50 – RM100</span>
                                </div>
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Incision & drainage</span>
                                    <span class="service-detail-price">RM100 – RM150</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 6: Periodontal Treatment -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6">
                                <i class="bi bi-shield-check me-2 text-primary"></i> Periodontal Treatment
                            </button>
                        </h2>
                        <div id="collapse6" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="mb-4">
                                    <h6 class="fw-bold text-dark mb-3">Scaling & Polishing</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Complete cleaning</span>
                                        <span class="service-detail-price">RM80 – RM300</span>
                                    </div>
                                    <p class="small text-muted mt-3"><em>💡 Price depends on tartar amount and staining</em></p>
                                </div>
                                <hr class="my-4">
                                <div>
                                    <h6 class="fw-bold text-dark mb-3">Gingival Curettage</h6>
                                    <div class="service-detail-item">
                                        <span class="service-detail-label">Per quadrant</span>
                                        <span class="service-detail-price">RM100</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 7: Crown & Veneer -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7">
                                <i class="bi bi-gem me-2 text-primary"></i> Crown & Veneer
                            </button>
                        </h2>
                        <div id="collapse7" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Metal Crown</span>
                                    <span class="service-detail-price">RM600</span>
                                </div>
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Porcelain Metal Crown</span>
                                    <span class="service-detail-price">RM700</span>
                                </div>
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Zirconia Crown</span>
                                    <span class="service-detail-price">RM1000</span>
                                </div>
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Veneer</span>
                                    <span class="service-detail-price">RM1200</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category 8: Dental Consultation -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8">
                                <i class="bi bi-chat-left-dots me-2 text-primary"></i> Dental Consultation
                            </button>
                        </h2>
                        <div id="collapse8" class="accordion-collapse collapse" data-bs-parent="#priceListAccordion">
                            <div class="accordion-body">
                                <div class="service-detail-item">
                                    <span class="service-detail-label">Consultation Fee</span>
                                    <span class="service-detail-price">RM20 – RM60</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Disclaimer -->
                <div class="alert alert-light border border-warning mt-5" role="alert">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill me-3 text-warning" style="font-size: 1.5rem; flex-shrink: 0;"></i>
                        <div>
                            <h6 class="alert-heading mb-2">📋 Important Information</h6>
                            <p class="mb-0 small">Final treatment type and pricing will be confirmed after a dental examination. Prices shown are reference prices. Please contact us at <strong>(04) 555-1234</strong> for more information or to schedule a consultation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- SECTION 2 END -->

@endsection
