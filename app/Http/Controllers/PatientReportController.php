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

        $totalTreatments = $treatments->total();
        
        // Get unique services
        $uniqueServices = Appointment::where('patient_email', auth()->user()->email)
            ->where('status', 'completed')
            ->distinct('service_id')
            ->count('service_id');
        
        // Calculate service summary
        $servicesSummary = Appointment::select('services.name')
            ->where('patient_email', auth()->user()->email)
            ->where('status', 'completed')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name')
            ->selectRaw('services.name, COUNT(*) as count, SUM(services.price) as total_cost')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count,
                    'total_cost' => $item->total_cost ?? 0
                ];
            })
            ->toArray();

        return view('patient.reports.treatments', compact(
            'treatments',
            'totalTreatments',
            'uniqueServices',
            'servicesSummary'
        ));
    }

    /**
     * Show patient feedback history
     */
    public function myFeedback(Request $request)
    {
        $feedback = Feedback::where('patient_phone', auth()->user()->phone)
            ->orWhere('patient_name', auth()->user()->name)
            ->with(['appointment.service', 'appointment.dentist'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalFeedback = Feedback::where('patient_phone', auth()->user()->phone)
            ->orWhere('patient_name', auth()->user()->name)
            ->count();
        
        $averageRating = Feedback::where('patient_phone', auth()->user()->phone)
            ->orWhere('patient_name', auth()->user()->name)
            ->avg('rating') ?? 0;

        // Build rating distribution
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = Feedback::where('patient_phone', auth()->user()->phone)
                ->orWhere('patient_name', auth()->user()->name)
                ->where('rating', $i)
                ->count();
        }

        return view('patient.reports.feedback', compact(
            'feedback',
            'averageRating',
            'totalFeedback',
            'ratingDistribution'
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
