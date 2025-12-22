<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Appointment;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Show the feedback form.
     */
    public function create(Request $request)
    {
        $visitCode = $request->query('code');
        
        if (!$visitCode) {
            return redirect('/')->with('error', 'Invalid feedback link. Please contact the clinic.');
        }

        $appointment = Appointment::where('visit_code', $visitCode)->first();

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
