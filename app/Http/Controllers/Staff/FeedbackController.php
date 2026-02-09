<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackRequest;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use App\Enums\AppointmentStatus;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of feedback.
     */
    public function index()
    {
        // Feedback received
        $feedbacks = Feedback::with(['appointment.service', 'appointment.dentist'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Pending feedback responses
        $pendingRequests = FeedbackRequest::pending()
            ->recent()
            ->with('appointment.dentist', 'feedback')
            ->orderBy('request_sent_at', 'asc')
            ->get();

        // Overdue feedback responses (7+ days no response)
        $overdueRequests = FeedbackRequest::overdue()
            ->with('appointment.dentist', 'feedback')
            ->orderBy('request_sent_at', 'asc')
            ->get();

        // Critically overdue (14+ days)
        $criticallyOverdue = FeedbackRequest::criticallyOverdue()
            ->count();

        // Statistics
        $totalRequests = FeedbackRequest::count();
        $respondedFeedbacks = Feedback::whereHas('feedbackRequest')->count();
        
        $stats = [
            'total_feedback' => $respondedFeedbacks,
            'total_requests' => $totalRequests,
            'pending_responses' => $pendingRequests->count(),
            'overdue_responses' => $overdueRequests->count(),
            'critically_overdue' => $criticallyOverdue,
            'response_rate' => $totalRequests > 0 
                ? round(($respondedFeedbacks / $totalRequests) * 100, 1) 
                : 0,
            'average_rating' => round(Feedback::avg('rating'), 1),
            'would_recommend' => Feedback::where('would_recommend', true)->count(),
            'recent_count' => Feedback::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('staff.feedback.index', compact(
            'feedbacks',
            'pendingRequests',
            'overdueRequests',
            'stats'
        ));
    }

    /**
     * Display the specified feedback.
     */
    public function show($id)
    {
        $feedback = Feedback::with(['appointment.service', 'appointment.dentist'])->findOrFail($id);
        
        // Log feedback view
        ActivityLogger::log(
            'viewed',
            'Feedback',
            $feedback->id,
            'Viewed feedback from ' . ($feedback->appointment->patient_name ?? 'Unknown patient'),
            null,
            null
        );
        
        return view('staff.feedback.show', compact('feedback'));
    }

    /**
     * Display all responses received
     */
    public function responses()
    {
        $feedbacks = Feedback::with(['appointment.service', 'appointment.dentist', 'feedbackRequest'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('staff.feedback.responses', compact('feedbacks'));
    }

    /**
     * Display all pending feedback requests
     */
    public function pending()
    {
        $pendingRequests = FeedbackRequest::pending()
            ->with('appointment.dentist', 'feedback')
            ->orderBy('request_sent_at', 'asc')
            ->paginate(20);

        return view('staff.feedback.pending', compact('pendingRequests'));
    }

    /**
     * Display all overdue feedback requests
     */
    public function overdue()
    {
        $overdueRequests = FeedbackRequest::overdue()
            ->with('appointment.dentist', 'feedback')
            ->orderBy('request_sent_at', 'asc')
            ->paginate(20);

        return view('staff.feedback.overdue', compact('overdueRequests'));
    }

    /**
     * Display all feedback with ratings
     */
    public function ratings()
    {
        $feedbacks = Feedback::with(['appointment.service', 'appointment.dentist', 'feedbackRequest'])
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('staff.feedback.ratings', compact('feedbacks'));
    }

    /**
     * Send feedback request to patient.
     * Create FeedbackRequest record and trigger notification.
     */
    public function sendRequest($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        // Check if request already exists
        $existingRequest = FeedbackRequest::where('appointment_id', $appointmentId)->first();
        
        if ($existingRequest && $existingRequest->response_status === 'responded') {
            return back()->with('warning', 'Feedback already received for this appointment');
        }

        // Create or update feedback request
        $feedbackRequest = FeedbackRequest::updateOrCreate(
            ['appointment_id' => $appointmentId],
            [
                'patient_name' => $appointment->patient_name,
                'patient_phone' => $appointment->patient_phone,
                'patient_email' => $appointment->patient_email ?? null,
                'request_sent_at' => now(),
                'response_status' => 'pending',
                'sent_via' => 'manual',
            ]
        );

        // Update appointment status to feedback_scheduled
        $appointment->update(['status' => AppointmentStatus::FEEDBACK_SCHEDULED]);

        // Log activity
        ActivityLogger::log(
            'created',
            'FeedbackRequest',
            $feedbackRequest->id,
            'Sent feedback request to ' . $appointment->patient_name,
            null,
            $feedbackRequest->toArray()
        );

        return back()->with('success', 'Feedback request sent to patient');
    }

    /**
     * Send reminder for pending feedback response.
     */
    public function sendReminder($feedbackRequestId)
    {
        $feedbackRequest = FeedbackRequest::findOrFail($feedbackRequestId);

        if (!$feedbackRequest->isPending()) {
            return back()->with('warning', 'Feedback has already been received');
        }

        // Record reminder
        $feedbackRequest->recordReminder();

        // TODO: Send SMS/Email reminder notification
        // NotificationService::sendFeedbackReminder($feedbackRequest);

        // Log activity
        ActivityLogger::log(
            'reminder_sent',
            'FeedbackRequest',
            $feedbackRequest->id,
            'Sent reminder to ' . $feedbackRequest->patient_name,
            null,
            null
        );

        return back()->with('success', 'Reminder sent to patient (' . $feedbackRequest->reminder_count . ' reminders sent)');
    }

    /**
     * Mark feedback request as not sent / don't follow up.
     */
    public function skipRequest($feedbackRequestId)
    {
        $feedbackRequest = FeedbackRequest::findOrFail($feedbackRequestId);
        $feedbackRequest->update(['response_status' => 'not_sent']);

        ActivityLogger::log(
            'skipped',
            'FeedbackRequest',
            $feedbackRequest->id,
            'Skipped feedback request for ' . $feedbackRequest->patient_name,
            null,
            null
        );

        return back()->with('success', 'Feedback request skipped');
    }
}
