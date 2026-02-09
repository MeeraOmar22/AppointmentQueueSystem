@extends('layouts.public')

@section('title', 'Test WhatsApp Feedback Feature')

@section('content')
<div class="container-fluid py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="bg-light rounded-lg p-5">
                    <h2 class="mb-4">🧪 WhatsApp Feedback Feature Test</h2>
                    <p class="text-muted">Complete the following steps to test the feedback feature:</p>

                    <!-- Step 1: Create Test Appointment -->
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Step 1️⃣ Create Test Appointment</h5>
                        </div>
                        <div class="card-body">
                            <p>A test appointment in <code>in_treatment</code> status will be created:</p>
                            <form action="{{ url('/test-feedback-setup') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Your Phone Number (for WhatsApp)</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="e.g., 60123456789" required>
                                    <small class="text-muted">Format: country code + number (e.g., 60 for Malaysia)</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Create Test Appointment
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Step 2: View Test Appointment -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Step 2️⃣ View Test Appointment</h5>
                        </div>
                        <div class="card-body">
                            <p>View appointments that are ready for feedback:</p>
                            @php
                                $testAppointments = \App\Models\Appointment::where('status', 'in_treatment')
                                    ->orWhere('status', 'completed')
                                    ->orWhere('status', 'feedback_sent')
                                    ->latest()
                                    ->limit(5)
                                    ->get();
                            @endphp

                            @if($testAppointments->count() > 0)
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Visit Code</th>
                                            <th>Patient</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($testAppointments as $apt)
                                            <tr>
                                                <td><code>{{ $apt->visit_code }}</code></td>
                                                <td>{{ $apt->patient_name }}</td>
                                                <td>{{ $apt->patient_phone }}</td>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $apt->status->value === 'in_treatment' ? 'warning' : 
                                                        ($apt->status->value === 'completed' ? 'danger' : 'success')
                                                    }}">
                                                        {{ ucfirst(str_replace('_', ' ', $apt->status->value)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($apt->status->value === 'in_treatment')
                                                        <form action="{{ url('/test-feedback-complete') }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" name="appointment_id" value="{{ $apt->id }}">
                                                            <button type="submit" class="btn btn-sm btn-warning" title="Mark as completed and send feedback link">
                                                                <i class="bi bi-check2-circle"></i> Complete
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ url('/feedback?code=' . $apt->visit_code) }}" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="bi bi-link-45deg"></i> Feedback Form
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-muted">No test appointments found. Create one in Step 1.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Step 3: Mark as Completed -->
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Step 3️⃣ Mark Appointment as Completed</h5>
                        </div>
                        <div class="card-body">
                            <p>Clicking "Complete" will:</p>
                            <ul>
                                <li>Change appointment status to <code>completed</code></li>
                                <li>Trigger <code>sendFeedbackLink()</code> method</li>
                                <li>Send WhatsApp message (if credentials configured)</li>
                                <li>Or show message preview below</li>
                            </ul>
                            <p class="text-muted"><small>Use the "Complete" button in the table above to complete an appointment.</small></p>
                        </div>
                    </div>

                    <!-- Step 4: View WhatsApp Message Preview -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Step 4️⃣ WhatsApp Message Preview</h5>
                        </div>
                        <div class="card-body">
                            <p>When completed, patient receives this WhatsApp message:</p>
                            <div class="bg-white border rounded p-3" style="border-left: 4px solid #25D366;">
                                <p><strong>🦷 Thank You for Your Visit!</strong></p>
                                <p>Hi [Patient Name],<br>
                                Thank you for choosing Helmy Dental Clinic for your dental care.</p>
                                <p><strong>⭐ We'd love to hear your feedback!</strong><br>
                                Please share your experience with us:</p>
                                <p style="word-break: break-all;">
                                    <a href="#" class="text-primary">{{ url('/feedback?code=VXXXXXX') }}</a>
                                </p>
                                <p>Your feedback helps us improve our services. Thank you! 😊</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Fill Feedback Form -->
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Step 5️⃣ Submit Feedback</h5>
                        </div>
                        <div class="card-body">
                            <p>Click the feedback link to access the form. The form will:</p>
                            <ul>
                                <li>✅ Pre-load patient name from appointment</li>
                                <li>✅ Pre-load phone number</li>
                                <li>✅ Show appointment date and service</li>
                                <li>✅ Show visit code for verification</li>
                            </ul>
                            <p>Fill out:</p>
                            <ul>
                                <li><strong>Overall Rating</strong> (1-5 stars) - Required</li>
                                <li><strong>Service Quality</strong> (Excellent/Good/Fair/Poor)</li>
                                <li><strong>Staff Friendliness</strong> (Excellent/Good/Fair/Poor)</li>
                                <li><strong>Cleanliness</strong> (Excellent/Good/Fair/Poor)</li>
                                <li><strong>Comments</strong> (Optional) - Share detailed feedback</li>
                                <li><strong>Would Recommend</strong> (Yes/Not Sure) - Required</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Step 6: Verify Feedback -->
                    <div class="card mb-4 border-secondary">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Step 6️⃣ Verify Feedback in Database</h5>
                        </div>
                        <div class="card-body">
                            <p>After submitting feedback, verify it was saved:</p>
                            <pre><code>SELECT * FROM feedback 
WHERE appointment_id = [APPOINTMENT_ID]
ORDER BY created_at DESC;</code></pre>
                            <p class="text-muted"><small>Look for your rating, comments, and timestamp.</small></p>
                        </div>
                    </div>

                    <!-- Success Checklist -->
                    <div class="alert alert-success">
                        <h5>✅ Feature Works When:</h5>
                        <ul class="mb-0">
                            <li>Appointment marked as completed</li>
                            <li>WhatsApp message received (or message preview shown)</li>
                            <li>Feedback link is clickable and loads form</li>
                            <li>Form pre-loads appointment details</li>
                            <li>Feedback submits without errors</li>
                            <li>Thank you page displays</li>
                            <li>Feedback appears in database</li>
                            <li>Appointment status changes to 'feedback_sent'</li>
                        </ul>
                    </div>

                    <!-- Documentation Links -->
                    <div class="alert alert-info">
                        <h5>📚 Learn More:</h5>
                        <ul class="mb-0">
                            <li><a href="{{ asset('WHATSAPP_FEEDBACK_TESTING_GUIDE.md') }}" target="_blank">WhatsApp Feedback Testing Guide</a></li>
                            <li><a href="{{ asset('FEEDBACK_SYSTEM_ARCHITECTURE.md') }}" target="_blank">Feedback System Architecture</a></li>
                            <li><a href="{{ asset('WHATSAPP_SOLUTION.md') }}" target="_blank">WhatsApp Integration Details</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
