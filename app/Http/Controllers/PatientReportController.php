<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Feedback;
use Illuminate\Http\Request;

class PatientReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show patient appointment history
     */
    public function appointmentHistory(Request $request)
    {
        $appointments = Appointment::where('patient_email', auth()->user()->email)
            ->with(['dentist', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);

        $totalAppointments = Appointment::where('patient_email', auth()->user()->email)->count();
        $completedAppointments = Appointment::where('patient_email', auth()->user()->email)
            ->where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('patient_email', auth()->user()->email)
            ->where('status', 'cancelled')->count();

        return view('patient.reports.appointments', compact(
            'appointments',
            'totalAppointments',
            'completedAppointments',
            'cancelledAppointments'
        ));
    }

    /**
     * Show patient treatment history
     */
    public function treatmentHistory(Request $request)
    {
        $treatments = Appointment::where('patient_email', auth()->user()->email)
            ->where('status', 'completed')
            ->with(['dentist', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);

        return view('patient.reports.treatments', compact('treatments'));
    }

    /**
     * Show patient feedback history
     */
    public function myFeedback(Request $request)
    {
        $feedbacks = Feedback::where('email', auth()->user()->email)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $averageRating = Feedback::where('email', auth()->user()->email)->avg('rating') ?? 0;
        $totalFeedbackSubmitted = Feedback::where('email', auth()->user()->email)->count();

        return view('patient.reports.feedback', compact(
            'feedbacks',
            'averageRating',
            'totalFeedbackSubmitted'
        ));
    }

    /**
     * Export patient records to PDF or CSV
     */
    public function exportRecords(Request $request)
    {
        $appointments = Appointment::where('patient_email', auth()->user()->email)
            ->with(['dentist', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=my_appointment_records_" . now()->format('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function() use ($appointments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Service', 'Dentist', 'Status', 'Notes']);
            
            foreach ($appointments as $appointment) {
                fputcsv($file, [
                    $appointment->appointment_date,
                    $appointment->service->name ?? 'N/A',
                    $appointment->dentist->name ?? 'N/A',
                    $appointment->status,
                    $appointment->notes ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
