<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

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
        $feedbacks = Feedback::with(['appointment.service', 'appointment.dentist'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => Feedback::count(),
            'average_rating' => round(Feedback::avg('rating'), 1),
            'would_recommend' => Feedback::where('would_recommend', true)->count(),
            'recent_count' => Feedback::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('staff.feedback.index', compact('feedbacks', 'stats'));
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
}
