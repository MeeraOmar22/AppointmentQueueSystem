<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Dentist;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Show admin reports dashboard
     */
    public function dashboard(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        // Appointment Statistics
        $totalAppointments = Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $completedAppointments = Appointment::where('status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $cancelledAppointments = Appointment::where('status', 'cancelled')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $noShowAppointments = Appointment::where('status', 'no_show')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();

        $appointmentCompletionRate = $totalAppointments > 0 
            ? round(($completedAppointments / $totalAppointments) * 100, 2) 
            : 0;

        // Revenue Analysis
        $revenueData = Appointment::select('services.name', 'services.cost')
            ->with('service')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.cost')
            ->selectRaw('services.name, services.cost, COUNT(*) as count, (services.cost * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueData->sum('total_revenue');

        // Dentist Performance
        $dentistPerformance = Appointment::select('dentists.id', 'dentists.name')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->groupBy('dentists.id', 'dentists.name')
            ->selectRaw('dentists.id, dentists.name, COUNT(*) as appointments_completed')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        // Service Popularity
        $servicePopularity = Appointment::select('services.name')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name')
            ->selectRaw('services.name, COUNT(*) as count')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(10)
            ->get();

        // Feedback Summary
        $averageRating = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->avg('rating') ?? 0;
        $totalFeedback = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        return view('staff.reports.dashboard', compact(
            'dateFrom',
            'dateTo',
            'totalAppointments',
            'completedAppointments',
            'cancelledAppointments',
            'noShowAppointments',
            'appointmentCompletionRate',
            'totalRevenue',
            'revenueData',
            'dentistPerformance',
            'servicePopularity',
            'averageRating',
            'totalFeedback'
        ));
    }

    /**
     * Show detailed appointment analysis
     */
    public function appointmentAnalysis(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $appointments = Appointment::with(['dentist', 'service'])
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->orderBy('appointment_date', 'desc')
            ->paginate(50);

        return view('staff.reports.appointment-analysis', compact('appointments', 'dateFrom', 'dateTo'));
    }

    /**
     * Show revenue report
     */
    public function revenueReport(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $revenueByService = Appointment::select('services.name', 'services.cost')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.cost')
            ->selectRaw('services.name, services.cost, COUNT(*) as count, (services.cost * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueByService->sum('total_revenue');

        return view('staff.reports.revenue-report', compact('revenueByService', 'totalRevenue', 'dateFrom', 'dateTo'));
    }

    /**
     * Export appointments to CSV
     */
    public function exportAppointments(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $appointments = Appointment::with(['dentist', 'service'])
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=appointments_report_" . now()->format('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $callback = function() use ($appointments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Service', 'Dentist', 'Patient Name', 'Status', 'Cost']);
            
            foreach ($appointments as $appointment) {
                fputcsv($file, [
                    $appointment->appointment_date,
                    $appointment->service->name ?? 'N/A',
                    $appointment->dentist->name ?? 'N/A',
                    $appointment->patient_name ?? 'N/A',
                    $appointment->status,
                    $appointment->service->cost ?? 0
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
