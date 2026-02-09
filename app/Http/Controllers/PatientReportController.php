<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show patient appointment history
     * 
     * CRIT-002 FIX: Use user_id instead of email for secure data isolation
     * Previously: where('patient_email', auth()->user()->email) could leak to other patients
     * Now: where('user_id', auth()->id()) - only returns authenticated user's appointments
     * MEDIUM-005 FIX: Added null checks for authenticated user
     */
    public function appointmentHistory(Request $request)
    {
        // MEDIUM-005 FIX: Ensure user is authenticated
        if (!auth()->check() || !auth()->id()) {
            abort(403, 'Unauthorized access to appointment history');
        }

        $appointments = Appointment::where('user_id', auth()->id())
            ->with(['dentist', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);

        $totalAppointments = Appointment::where('user_id', auth()->id())->count();
        $completedAppointments = Appointment::where('user_id', auth()->id())
            ->where('status', 'completed')->count();
        $cancelledAppointments = Appointment::where('user_id', auth()->id())
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
     * 
     * CRIT-002 FIX: Use user_id instead of email for secure data isolation
     * MEDIUM-005 FIX: Added null checks for authenticated user
     */
    public function treatmentHistory(Request $request)
    {
        // MEDIUM-005 FIX: Ensure user is authenticated
        if (!auth()->check() || !auth()->id()) {
            abort(403, 'Unauthorized access to treatment history');
        }

        $treatments = Appointment::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->with(['dentist', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);

        $totalTreatments = $treatments->total();
        
        // Get unique services
        $uniqueServices = Appointment::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->distinct('service_id')
            ->count('service_id');
        
        // Calculate service summary
        $servicesSummary = Appointment::select('services.name')
            ->where('user_id', auth()->id())
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
     * 
     * CRIT-001 FIX: Use user_id instead of phone/name for secure data isolation
     * Previously: where('patient_phone', ...) orWhere('patient_name', ...) could leak other patients
     * Now: where('user_id', auth()->id()) - only returns authenticated user's feedback
     * MEDIUM-005 FIX: Added null checks for authenticated user
     */
    public function myFeedback(Request $request)
    {
        // MEDIUM-005 FIX: Ensure user is authenticated
        if (!auth()->check() || !auth()->id()) {
            abort(403, 'Unauthorized access to feedback');
        }

        $feedback = Feedback::where('user_id', auth()->id())
            ->with(['appointment.service', 'appointment.dentist'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalFeedback = Feedback::where('user_id', auth()->id())->count();
        
        $averageRating = Feedback::where('user_id', auth()->id())
            ->avg('rating') ?? 0;

        // Build rating distribution
        $ratingDistribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingDistribution[$i] = Feedback::where('user_id', auth()->id())
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
     * 
     * CRIT-002 FIX: Use user_id instead of email for secure data isolation
     * MEDIUM-001 FIX: Use chunking for large datasets to prevent memory issues
     * MEDIUM-005 FIX: Added null checks and error handling
     * MEDIUM-007 FIX: Proper error handling prevents silent failures
     */
    public function exportRecords(Request $request)
    {
        // MEDIUM-005 FIX: Ensure user is authenticated for export
        if (!auth()->check() || !auth()->id()) {
            abort(403, 'Unauthorized access to export');
        }

        try {
            $headers = array(
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=my_appointment_records_" . now()->format('Y-m-d') . ".csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );

            $callback = function() {
                $file = fopen('php://output', 'w');
                if (!$file) {
                    throw new \Exception('Failed to open output stream for CSV export');
                }

                fputcsv($file, ['Date', 'Service', 'Dentist', 'Status', 'Notes']);
                
                /**
                 * MEDIUM-001 FIX: Process in chunks to prevent memory overflow
                 * Instead of loading all appointments into memory at once,
                 * fetch and output in chunks of 100 records
                 */
                $chunkSize = 100;
                
                Appointment::where('user_id', auth()->id())
                    ->with(['dentist', 'service'])
                    ->orderBy('appointment_date', 'desc')
                    ->chunk($chunkSize, function ($appointments) use ($file) {
                        foreach ($appointments as $appointment) {
                            fputcsv($file, [
                                $appointment->appointment_date,
                                $appointment->service->name ?? 'N/A',
                                $appointment->dentist->name ?? 'N/A',
                                $appointment->status->value ?? $appointment->status,  // Handle enum
                                $appointment->notes ?? ''
                            ]);
                        }
                    });
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            // MEDIUM-007 FIX: Don't silently fail - log and inform user
            \Log::error('CSV export failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            
            return redirect()->back()
                ->withError('Failed to export records: ' . $e->getMessage())
                ->withStatus('error');
        }
    }

    /**
     * Export patient records to PDF
     * 
     * Generates a professional PDF with appointment history
     */
    public function exportRecordsPdf(Request $request)
    {
        // Ensure user is authenticated for export
        if (!auth()->check() || !auth()->id()) {
            abort(403, 'Unauthorized access to export');
        }

        try {
            $appointments = Appointment::where('user_id', auth()->id())
                ->with(['dentist', 'service'])
                ->orderBy('appointment_date', 'desc')
                ->get();

            $treatments = Appointment::where('user_id', auth()->id())
                ->where('status', 'completed')
                ->with(['dentist', 'service'])
                ->orderBy('appointment_date', 'desc')
                ->get();

            $totalCost = $treatments->sum(function ($apt) {
                return $apt->service->price ?? 0;
            });

            $data = [
                'user' => auth()->user(),
                'appointments' => $appointments,
                'treatments' => $treatments,
                'totalAppointments' => $appointments->count(),
                'completedAppointments' => $appointments->where('status', 'completed')->count(),
                'cancelledAppointments' => $appointments->where('status', 'cancelled')->count(),
                'totalCost' => $totalCost,
                'generatedAt' => now()->format('M d, Y H:i A'),
            ];

            $pdf = Pdf::loadView('patient.reports.pdf.records-export', $data)
                ->setPaper('a4')
                ->setOption('margin-top', 10)
                ->setOption('margin-bottom', 10)
                ->setOption('margin-left', 10)
                ->setOption('margin-right', 10);

            return $pdf->download('my_appointment_records_' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF export failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            
            return redirect()->back()
                ->withError('Failed to export records: ' . $e->getMessage())
                ->withStatus('error');
        }
    }}