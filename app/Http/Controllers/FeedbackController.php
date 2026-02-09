<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class FeedbackController extends Controller
{
    /**
     * Show the feedback form.
     */
    public function create(Request $request)
    {
        // Accept both 'token' (new standardized) and 'code' (backward compatibility)
        $visitToken = $request->query('token') ?? $request->query('code');
        
        if (!$visitToken) {
            return redirect('/')->with('error', 'Invalid feedback link. Please contact the clinic.');
        }

        // Try to find by visit_token first, then by visit_code for backward compatibility
        $appointment = Appointment::where('visit_token', $visitToken)
            ->orWhere('visit_code', $visitToken)
            ->first();

        if (!$appointment) {
            return redirect('/')->with('error', 'Appointment not found.');
        }

        // Check if feedback already submitted
        $feedback = Feedback::where('appointment_id', $appointment->id)->first();
        if ($feedback) {
            return view('public.feedback-thanks', compact('feedback'));
        }

        return view('public.feedback', compact('appointment'));
    }

    /**
     * Store the feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'service_quality' => 'nullable|in:excellent,good,fair,poor',
            'staff_friendliness' => 'nullable|in:excellent,good,fair,poor',
            'cleanliness' => 'nullable|in:excellent,good,fair,poor',
            'would_recommend' => 'required|boolean',
        ]);

        $feedback = Feedback::create($validated);

        // Log the feedback submission
        $appointment = Appointment::find($validated['appointment_id']);
        ActivityLogger::log(
            'created',
            'Feedback',
            $feedback->id,
            'Feedback submitted by ' . $validated['patient_name'] . ' - Rating: ' . $validated['rating'] . '/5',
            null,
            ['rating' => $feedback->rating, 'would_recommend' => $feedback->would_recommend, 'appointment_id' => $feedback->appointment_id]
        );

        return redirect()->route('feedback.thanks', ['id' => $feedback->id])
            ->with('success', 'Thank you for your valuable feedback!');
    }

    /**
     * Show thank you page.
     */
    public function thanks($id = null)
    {
        $feedback = null;
        if ($id) {
            $feedback = Feedback::find($id);
        }
        
        return view('public.feedback-thanks', compact('feedback'));
    }
}
