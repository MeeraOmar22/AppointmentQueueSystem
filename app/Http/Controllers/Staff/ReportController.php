<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Dentist;
use App\Models\Service;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $completedAppointments = Appointment::where('status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $cancelledAppointments = Appointment::where('status', AppointmentStatus::CANCELLED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $noShowAppointments = Appointment::where('status', AppointmentStatus::NO_SHOW->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();

        $appointmentCompletionRate = $totalAppointments > 0 
            ? round(($completedAppointments / $totalAppointments) * 100, 2) 
            : 0;

        // Revenue Analysis
        $revenueData = Appointment::select('services.name', 'services.price')
            ->with('service')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueData->sum('total_revenue');

        // Dentist Performance
        $dentistPerformance = Appointment::select('dentists.id', 'dentists.name')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->groupBy('dentists.id', 'dentists.name')
            ->selectRaw('dentists.id, dentists.name, COUNT(*) as appointments_completed')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        // Service Popularity
        $servicePopularity = Appointment::select('services.name')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
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
        $completedCount = (clone $countQuery)->where('status', AppointmentStatus::COMPLETED->value)->count();
        $cancelledCount = (clone $countQuery)->where('status', AppointmentStatus::CANCELLED->value)->count();
        $noShowCount = (clone $countQuery)->where('status', AppointmentStatus::NO_SHOW->value)->count();
        
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
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueByService->sum('total_revenue');
        
        // Total appointments (completed only for revenue)
        $totalAppointments = Appointment::where('status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        
        // Average revenue per appointment
        $averagePerAppointment = $totalAppointments > 0 
            ? round($totalRevenue / $totalAppointments, 2) 
            : 0;

        // Revenue by dentist
        $revenueByDentist = Appointment::select('dentists.id', 'dentists.name')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
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
                    $appointment->status->value ?? $appointment->status,
                    $appointment->service->price ?? 0
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export appointments to PDF
     */
    public function exportAppointmentsPdf(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $appointments = Appointment::with(['dentist', 'service'])
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->orderBy('appointment_date', 'desc')
            ->get();

        $totalRevenue = $appointments->sum(function ($apt) {
            return $apt->service->price ?? 0;
        });

        $data = [
            'appointments' => $appointments,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalRevenue' => $totalRevenue,
            'generatedAt' => now()->format('M d, Y H:i A'),
            'totalAppointments' => $appointments->count(),
            'completedAppointments' => $appointments->where('status', 'completed')->count(),
            'cancelledAppointments' => $appointments->where('status', 'cancelled')->count(),
        ];

        $pdf = Pdf::loadView('staff.reports.pdf.appointments-export', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->download('appointments_report_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export revenue report to PDF
     */
    public function exportRevenuePdf(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $revenueByService = Appointment::select('services.name', 'services.price')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueByService->sum('total_revenue');
        $totalAppointments = Appointment::where('appointments.appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        
        $averagePerAppointment = $totalAppointments > 0 
            ? round($totalRevenue / $totalAppointments, 2) 
            : 0;

        $revenueByDentist = Appointment::select('dentists.id', 'dentists.name')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('dentists.id', 'dentists.name')
            ->selectRaw('dentists.id, dentists.name, COUNT(*) as completed_appointments, SUM(services.price) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $data = [
            'revenueByService' => $revenueByService,
            'revenueByDentist' => $revenueByDentist,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalRevenue' => $totalRevenue,
            'totalAppointments' => $totalAppointments,
            'averagePerAppointment' => $averagePerAppointment,
            'generatedAt' => now()->format('M d, Y H:i A'),
        ];

        $pdf = Pdf::loadView('staff.reports.pdf.revenue-export', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->download('revenue_report_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export comprehensive report with all 4 analytics (Appointments, Revenue, Retention, Scheduling)
     */
    public function exportComprehensiveReportPdf(Request $request)
    {
        $timePeriod = $request->query('period', 'monthly'); // daily, weekly, monthly
        
        // Calculate date range based on period
        if ($timePeriod === 'daily') {
            $dateFrom = now()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
            $periodLabel = 'Today';
        } elseif ($timePeriod === 'weekly') {
            $dateFrom = now()->startOfWeek()->format('Y-m-d');
            $dateTo = now()->endOfWeek()->format('Y-m-d');
            $periodLabel = 'This Week';
        } else { // monthly
            $dateFrom = now()->startOfMonth()->format('Y-m-d');
            $dateTo = now()->endOfMonth()->format('Y-m-d');
            $periodLabel = now()->format('F Y');
        }

        // 1. APPOINTMENT ANALYSIS
        $appointments = Appointment::with(['dentist', 'service'])
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->get();
        
        $appointmentStats = [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'cancelled' => $appointments->where('status', AppointmentStatus::CANCELLED->value)->count(),
            'noShow' => $appointments->where('status', AppointmentStatus::NO_SHOW->value)->count(),
        ];

        // 2. REVENUE REPORT
        $revenueByService = Appointment::select('services.name', 'services.price')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueByService->sum('total_revenue');
        $averagePerAppointment = $appointmentStats['completed'] > 0 ? round($totalRevenue / $appointmentStats['completed'], 2) : 0;

        $revenueByDentist = Appointment::select('dentists.id', 'dentists.name')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('dentists.id', 'dentists.name')
            ->selectRaw('dentists.id, dentists.name, COUNT(*) as completed_appointments, SUM(services.price) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        // 3. PATIENT RETENTION (at-risk patients)
        $allPatients = Appointment::distinct('patient_phone')
            ->select('patient_name', 'patient_phone', 'patient_email')
            ->orderBy('patient_name')
            ->get();

        $atRiskPatients = [];
        $loyalPatients = [];

        foreach ($allPatients as $patient) {
            $patientAppointments = Appointment::where('patient_phone', $patient->patient_phone)
                ->orderBy('appointment_date', 'desc')
                ->get();

            if ($patientAppointments->isEmpty()) continue;

            $totalAppointments = $patientAppointments->count();
            $completedAppointments = $patientAppointments->where('status', 'completed')->count();
            $cancelledAppointments = $patientAppointments->where('status', 'cancelled')->count();
            $noShowAppointments = $patientAppointments->where('status', 'no_show')->count();

            $lastAppointmentDate = $patientAppointments->first()->appointment_date;
            $daysSinceLastVisit = now()->diffInDays($lastAppointmentDate);
            $cancelRate = $totalAppointments > 0 ? ($cancelledAppointments + $noShowAppointments) / $totalAppointments : 0;

            $riskScore = 0;
            if ($daysSinceLastVisit > 180) $riskScore += 40;
            elseif ($daysSinceLastVisit > 90) $riskScore += 20;
            if ($cancelRate > 0.3) $riskScore += 25;
            elseif ($cancelRate > 0.15) $riskScore += 15;

            if ($riskScore >= 25) {
                $atRiskPatients[] = [
                    'name' => $patient->patient_name,
                    'lastVisit' => $lastAppointmentDate->format('M d, Y'),
                    'daysSince' => $daysSinceLastVisit,
                    'riskScore' => $riskScore,
                ];
            } else {
                $loyalPatients[] = [
                    'name' => $patient->patient_name,
                    'totalAppointments' => $totalAppointments,
                ];
            }
        }

        usort($atRiskPatients, function ($a, $b) {
            return $b['riskScore'] <=> $a['riskScore'];
        });

        // 4. SCHEDULING ANALYSIS
        $appointmentsByHour = Appointment::selectRaw('HOUR(appointment_time) as hour, COUNT(*) as count')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hourlyDistribution = [];
        for ($i = 8; $i <= 17; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            $count = $appointmentsByHour->where('hour', $i)->first()?->count ?? 0;
            $hourlyDistribution[] = [
                'hour' => $hour . ':00',
                'count' => $count,
            ];
        }

        $dentistUtilization = Appointment::selectRaw('dentists.id, dentists.name, COUNT(*) as appointments_count')
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->groupBy('dentists.id', 'dentists.name')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        $totalCompletedAppointments = Appointment::where('appointments.status', AppointmentStatus::COMPLETED->value)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->count();

        foreach ($dentistUtilization as $dentist) {
            $dentist->utilization_percentage = $totalCompletedAppointments > 0
                ? round(($dentist->appointments_count / $totalCompletedAppointments) * 100, 1)
                : 0;
        }

        $data = [
            'timePeriod' => $timePeriod,
            'periodLabel' => $periodLabel,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedAt' => now()->format('M d, Y H:i A'),
            
            // Appointment Analysis
            'appointmentStats' => $appointmentStats,
            'appointments' => $appointments,
            
            // Revenue Report
            'revenueByService' => $revenueByService,
            'revenueByDentist' => $revenueByDentist,
            'totalRevenue' => $totalRevenue,
            'averagePerAppointment' => $averagePerAppointment,
            
            // Patient Retention
            'atRiskPatients' => $atRiskPatients,
            'loyalPatients' => $loyalPatients,
            'atRiskCount' => count($atRiskPatients),
            'loyalCount' => count($loyalPatients),
            'totalPatients' => count($atRiskPatients) + count($loyalPatients),
            
            // Scheduling Analysis
            'hourlyDistribution' => $hourlyDistribution,
            'dentistUtilization' => $dentistUtilization,
        ];

        $pdf = Pdf::loadView('staff.reports.pdf.comprehensive-report', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10)
            ->setOption('dpi', 300);

        return $pdf->download('comprehensive_report_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Show queue analytics report
     */
    public function queueAnalytics(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $analyticsService = new \App\Services\QueueAnalyticsService();

        $waitTimeAnalysis = $analyticsService->getWaitTimeAnalysis($dateFrom, $dateTo);
        $treatmentAnalysis = $analyticsService->getTreatmentDurationAnalysis($dateFrom, $dateTo);
        $roomUtilization = $analyticsService->getRoomUtilization($dateFrom, $dateTo);
        $queueEfficiency = $analyticsService->getQueueEfficiency($dateFrom, $dateTo);
        $peakHours = $analyticsService->getPeakHoursAnalysis($dateFrom, $dateTo);

        return view('staff.reports.queue-analytics', compact(
            'dateFrom',
            'dateTo',
            'waitTimeAnalysis',
            'treatmentAnalysis',
            'roomUtilization',
            'queueEfficiency',
            'peakHours'
        ));
    }

    /**
     * Export queue analytics report to PDF
     */
    public function exportQueueAnalyticsPdf(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(1)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        $analyticsService = new \App\Services\QueueAnalyticsService();

        $waitTimeAnalysis = $analyticsService->getWaitTimeAnalysis($dateFrom, $dateTo);
        $treatmentAnalysis = $analyticsService->getTreatmentDurationAnalysis($dateFrom, $dateTo);
        $roomUtilization = $analyticsService->getRoomUtilization($dateFrom, $dateTo);
        $queueEfficiency = $analyticsService->getQueueEfficiency($dateFrom, $dateTo);
        $peakHours = $analyticsService->getPeakHoursAnalysis($dateFrom, $dateTo);

        $data = [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'waitTimeAnalysis' => $waitTimeAnalysis,
            'treatmentAnalysis' => $treatmentAnalysis,
            'roomUtilization' => $roomUtilization,
            'queueEfficiency' => $queueEfficiency,
            'peakHours' => $peakHours,
            'generatedAt' => now()->format('M d, Y H:i A'),
        ];

        $pdf = Pdf::loadView('staff.reports.pdf.queue-analytics-export', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);

        return $pdf->download('queue_analytics_report_' . now()->format('Y-m-d') . '.pdf');
    }

    public function patientRetention()
    {
        // Get all unique patients
        $patients = Appointment::distinct('patient_phone')
            ->select('patient_name', 'patient_phone', 'patient_email')
            ->orderBy('patient_name')
            ->get();

        $atRiskPatients = [];
        $loyalPatients = [];

        foreach ($patients as $patient) {
            // Get all appointments for this patient
            $appointments = Appointment::where('patient_phone', $patient->patient_phone)
                ->orderBy('appointment_date', 'desc')
                ->get();

            if ($appointments->isEmpty()) {
                continue;
            }

            $totalAppointments = $appointments->count();
            $completedAppointments = $appointments->where('status', AppointmentStatus::COMPLETED->value)->count();
            $cancelledAppointments = $appointments->where('status', AppointmentStatus::CANCELLED->value)->count();
            $noShowAppointments = $appointments->where('status', AppointmentStatus::NO_SHOW->value)->count();
            
            // Calculate metrics
            $lastAppointmentDate = $appointments->first()->appointment_date;
            $daysSinceLastVisit = now()->diffInDays($lastAppointmentDate);
            $cancelRate = $totalAppointments > 0 ? ($cancelledAppointments + $noShowAppointments) / $totalAppointments : 0;
            $completionRate = $totalAppointments > 0 ? $completedAppointments / $totalAppointments : 0;

            // Analyze appointment frequency trend (last 6 months vs previous 6 months)
            $sixMonthsAgo = now()->subMonths(6);
            $twelvMonthsAgo = now()->subMonths(12);
            
            $recentCount = $appointments->filter(function ($apt) use ($sixMonthsAgo) {
                return Carbon::parse($apt->appointment_date)->gte($sixMonthsAgo);
            })->count();

            $pastCount = $appointments->filter(function ($apt) use ($sixMonthsAgo, $twelvMonthsAgo) {
                $date = Carbon::parse($apt->appointment_date);
                return $date->gte($twelvMonthsAgo) && $date->lt($sixMonthsAgo);
            })->count();

            $frequencyDecline = $pastCount > 0 ? (($pastCount - $recentCount) / $pastCount) : 0;

            // Determine risk level based on multiple factors
            $riskScore = 0;
            $riskFactors = [];

            // Risk Factor 1: Long time since last visit (>90 days = high risk)
            if ($daysSinceLastVisit > 180) {
                $riskScore += 40;
                $riskFactors[] = "No visit for " . $daysSinceLastVisit . " days";
            } elseif ($daysSinceLastVisit > 90) {
                $riskScore += 20;
                $riskFactors[] = "No visit for " . $daysSinceLastVisit . " days";
            }

            // Risk Factor 2: High cancellation/no-show rate (>30% = medium risk)
            if ($cancelRate > 0.3) {
                $riskScore += 25;
                $riskFactors[] = "High cancellation rate: " . round($cancelRate * 100) . "%";
            } elseif ($cancelRate > 0.15) {
                $riskScore += 15;
                $riskFactors[] = "Moderate cancellation rate: " . round($cancelRate * 100) . "%";
            }

            // Risk Factor 3: Declining appointment frequency
            if ($frequencyDecline > 0.5) {
                $riskScore += 25;
                $riskFactors[] = "Appointment frequency declining: " . round($frequencyDecline * 100) . "%";
            } elseif ($frequencyDecline > 0.25) {
                $riskScore += 15;
                $riskFactors[] = "Slight decline in appointments";
            }

            // Risk Factor 4: Low completion rate (<60%)
            if ($completionRate < 0.6) {
                $riskScore += 10;
                $riskFactors[] = "Low completion rate: " . round($completionRate * 100) . "%";
            }

            $patientData = [
                'name' => $patient->patient_name,
                'phone' => $patient->patient_phone,
                'email' => $patient->patient_email,
                'totalAppointments' => $totalAppointments,
                'completedAppointments' => $completedAppointments,
                'cancelledAppointments' => $cancelledAppointments,
                'noShowAppointments' => $noShowAppointments,
                'lastVisit' => $lastAppointmentDate->format('M d, Y'),
                'daysSinceLastVisit' => $daysSinceLastVisit,
                'cancelRate' => round($cancelRate * 100, 1),
                'completionRate' => round($completionRate * 100, 1),
                'frequencyDecline' => round($frequencyDecline * 100, 1),
                'riskScore' => $riskScore,
                'riskFactors' => $riskFactors,
                'riskLevel' => $riskScore >= 50 ? 'High' : ($riskScore >= 25 ? 'Medium' : 'Low')
            ];

            if ($riskScore >= 25) {
                $atRiskPatients[] = $patientData;
            } else {
                $loyalPatients[] = $patientData;
            }
        }

        // Sort by risk score (descending)
        usort($atRiskPatients, function ($a, $b) {
            return $b['riskScore'] <=> $a['riskScore'];
        });

        return view('staff.reports.patient-retention', [
            'atRiskPatients' => $atRiskPatients,
            'loyalPatients' => $loyalPatients,
            'totalPatients' => count($atRiskPatients) + count($loyalPatients),
            'atRiskCount' => count($atRiskPatients),
            'loyalCount' => count($loyalPatients),
            'riskPercentage' => count($atRiskPatients) + count($loyalPatients) > 0 
                ? round((count($atRiskPatients) / (count($atRiskPatients) + count($loyalPatients))) * 100, 1)
                : 0
        ]);
    }


}
