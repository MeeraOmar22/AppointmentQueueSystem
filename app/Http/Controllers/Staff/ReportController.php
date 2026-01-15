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
        $revenueData = Appointment::select('services.name', 'services.price')
            ->with('service')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
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
        
        // Base query with filters
        $query = Appointment::with(['dentist', 'service'])
            ->whereBetween('appointment_date', [$dateFrom, $dateTo]);
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        
        // Apply dentist filter
        if ($request->filled('dentist_id')) {
            $query->where('dentist_id', $request->query('dentist_id'));
        }
        
        $appointments = $query->orderBy('appointment_date', 'desc')->paginate(50);

        // Get counts for statistics
        $countQuery = Appointment::whereBetween('appointment_date', [$dateFrom, $dateTo]);
        $completedCount = (clone $countQuery)->where('status', 'completed')->count();
        $cancelledCount = (clone $countQuery)->where('status', 'cancelled')->count();
        $noShowCount = (clone $countQuery)->where('status', 'no_show')->count();
        
        // Get all dentists for filter dropdown
        $dentists = Dentist::where('status', true)->orderBy('name')->get();

        return view('staff.reports.appointment-analysis', compact(
            'appointments', 
            'dateFrom', 
            'dateTo',
            'dentists',
            'completedCount',
            'cancelledCount',
            'noShowCount'
        ));
    }

    /**
     * Show revenue report
     */
    public function revenueReport(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $revenueByService = Appointment::select('services.name', 'services.price')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueByService->sum('total_revenue');
        
        // Total appointments (completed only for revenue)
        $totalAppointments = Appointment::where('status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        
        // Average revenue per appointment
        $averagePerAppointment = $totalAppointments > 0 
            ? round($totalRevenue / $totalAppointments, 2) 
            : 0;

        // Revenue by dentist
        $revenueByDentist = Appointment::select('dentists.id', 'dentists.name', 'services.price')
            ->where('status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('dentists.id', 'dentists.name')
            ->selectRaw('dentists.id, dentists.name, COUNT(*) as completed_appointments, SUM(services.price) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        return view('staff.reports.revenue-report', compact(
            'revenueByService', 
            'totalRevenue', 
            'dateFrom', 
            'dateTo',
            'totalAppointments',
            'averagePerAppointment',
            'revenueByDentist'
        ));
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
                    $appointment->service->price ?? 0
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
