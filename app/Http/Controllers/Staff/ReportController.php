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
        $completedAppointments = Appointment::where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $cancelledAppointments = Appointment::where('appointments.status', 'cancelled')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        $noShowAppointments = Appointment::where('appointments.status', 'no_show')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();

        $appointmentCompletionRate = $totalAppointments > 0 
            ? round(($completedAppointments / $totalAppointments) * 100, 2) 
            : 0;

        // Revenue Analysis
        $revenueData = Appointment::select('services.name', 'services.price')
            ->with('service')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueData->sum('total_revenue');

        // Dentist Performance
        $dentistPerformance = Appointment::select('dentists.id', 'dentists.name')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->groupBy('dentists.id', 'dentists.name')
            ->selectRaw('dentists.id, dentists.name, COUNT(*) as appointments_completed')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        // Service Popularity
        $servicePopularity = Appointment::select('services.name')
            ->where('appointments.status', 'completed')
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
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.appointment_date', [$dateFrom, $dateTo])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->groupBy('services.id', 'services.name', 'services.price')
            ->selectRaw('services.name, services.price, COUNT(*) as count, (services.price * COUNT(*)) as total_revenue')
            ->orderByRaw('total_revenue DESC')
            ->get();

        $totalRevenue = $revenueByService->sum('total_revenue');
        
        // Total appointments (completed only for revenue)
        $totalAppointments = Appointment::where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])->count();
        
        // Average revenue per appointment
        $averagePerAppointment = $totalAppointments > 0 
            ? round($totalRevenue / $totalAppointments, 2) 
            : 0;

        // Revenue by dentist
        $revenueByDentist = Appointment::select('dentists.id', 'dentists.name')
            ->where('appointments.status', 'completed')
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

    /**
     * Show at-risk patients (patient retention analytics)
     */
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
            $completedAppointments = $appointments->where('status', 'completed')->count();
            $cancelledAppointments = $appointments->where('status', 'cancelled')->count();
            $noShowAppointments = $appointments->where('status', 'no_show')->count();
            
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

    /**
     * Show appointment duration and scheduling optimization analytics
     */
    public function schedulingOptimization(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->subMonths(3)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));

        // Appointment Duration Analysis
        $appointments = Appointment::with(['dentist', 'service'])
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->get();

        $durationStats = [
            'totalAppointments' => 0,
            'averageDuration' => 0,
            'minDuration' => null,
            'maxDuration' => null,
            'byService' => [],
            'byDentist' => []
        ];

        $appointmentsByService = [];
        $appointmentsByDentist = [];

        foreach ($appointments as $apt) {
            if ($apt->start_at && $apt->end_at && $apt->end_at > $apt->start_at) {
                $duration = $apt->start_at->diffInMinutes($apt->end_at);
                
                // Track by service
                if (!isset($appointmentsByService[$apt->service->name])) {
                    $appointmentsByService[$apt->service->name] = [
                        'service_id' => $apt->service_id,
                        'name' => $apt->service->name,
                        'count' => 0,
                        'totalDuration' => 0,
                        'durations' => []
                    ];
                }
                $appointmentsByService[$apt->service->name]['count']++;
                $appointmentsByService[$apt->service->name]['totalDuration'] += $duration;
                $appointmentsByService[$apt->service->name]['durations'][] = $duration;

                // Track by dentist
                if ($apt->dentist) {
                    if (!isset($appointmentsByDentist[$apt->dentist->name])) {
                        $appointmentsByDentist[$apt->dentist->name] = [
                            'dentist_id' => $apt->dentist_id,
                            'name' => $apt->dentist->name,
                            'count' => 0,
                            'totalDuration' => 0,
                            'durations' => []
                        ];
                    }
                    $appointmentsByDentist[$apt->dentist->name]['count']++;
                    $appointmentsByDentist[$apt->dentist->name]['totalDuration'] += $duration;
                    $appointmentsByDentist[$apt->dentist->name]['durations'][] = $duration;
                }

                $durationStats['totalAppointments']++;
                if ($durationStats['minDuration'] === null || $duration < $durationStats['minDuration']) {
                    $durationStats['minDuration'] = $duration;
                }
                if ($durationStats['maxDuration'] === null || $duration > $durationStats['maxDuration']) {
                    $durationStats['maxDuration'] = $duration;
                }
            }
        }

        // Calculate averages for services
        foreach ($appointmentsByService as $service) {
            $durationStats['byService'][] = [
                'name' => $service['name'],
                'count' => $service['count'],
                'averageDuration' => round($service['totalDuration'] / $service['count'], 0),
                'minDuration' => min($service['durations']),
                'maxDuration' => max($service['durations']),
                'variance' => round($this->calculateVariance($service['durations']), 2)
            ];
        }

        // Calculate averages for dentists
        foreach ($appointmentsByDentist as $dentist) {
            $durationStats['byDentist'][] = [
                'name' => $dentist['name'],
                'count' => $dentist['count'],
                'averageDuration' => round($dentist['totalDuration'] / $dentist['count'], 0),
                'minDuration' => min($dentist['durations']),
                'maxDuration' => max($dentist['durations']),
                'variance' => round($this->calculateVariance($dentist['durations']), 2)
            ];
        }

        if ($durationStats['totalAppointments'] > 0) {
            $durationStats['averageDuration'] = round(
                array_sum(array_column($appointmentsByService, 'totalDuration')) / $durationStats['totalAppointments'],
                0
            );
        }

        // Appointment Distribution & Schedule Gaps
        $appointmentsByHour = Appointment::selectRaw('HOUR(appointment_time) as hour, COUNT(*) as count')
            ->where('appointments.status', 'completed')
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
                'percentage' => $appointmentsByHour->sum('count') > 0 
                    ? round(($count / $appointmentsByHour->sum('count')) * 100, 1)
                    : 0
            ];
        }

        // Daily distribution
        $appointmentsByDay = Appointment::selectRaw('DAYNAME(appointment_date) as day, COUNT(*) as count')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->groupByRaw('DAYNAME(appointment_date), DAYOFWEEK(appointment_date)')
            ->orderBy('DAYOFWEEK')
            ->get();

        // Dentist Utilization
        $dentistUtilization = Appointment::selectRaw('dentists.id, dentists.name, COUNT(*) as appointments_count')
            ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->groupBy('dentists.id', 'dentists.name')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        $totalCompletedAppointments = Appointment::where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->count();

        foreach ($dentistUtilization as $dentist) {
            $dentist->utilization_percentage = $totalCompletedAppointments > 0
                ? round(($dentist->appointments_count / $totalCompletedAppointments) * 100, 1)
                : 0;
        }

        // Optimization Recommendations
        $recommendations = [];
        
        // Check for peak hours
        $peakHour = collect($hourlyDistribution)->sortByDesc('count')->first();
        if ($peakHour['count'] > 0) {
            $recommendations[] = [
                'type' => 'Peak Hours',
                'message' => "Peak appointment time is {$peakHour['hour']} with {$peakHour['count']} appointments (" . $peakHour['percentage'] . "%). Consider scheduling more staff during this period.",
                'priority' => 'high'
            ];
        }

        // Check for low utilization hours
        $lowHours = collect($hourlyDistribution)->filter(fn($h) => $h['count'] < 2)->count();
        if ($lowHours > 2) {
            $recommendations[] = [
                'type' => 'Underutilized Time Slots',
                'message' => "There are {$lowHours} hours with very few appointments. Consider promotional offers for off-peak times.",
                'priority' => 'medium'
            ];
        }

        // Check for long treatment times
        if ($durationStats['averageDuration'] > 45) {
            $recommendations[] = [
                'type' => 'Treatment Duration',
                'message' => "Average treatment time is {$durationStats['averageDuration']} minutes. Review if scheduling intervals are adequate.",
                'priority' => 'medium'
            ];
        }

        // Check for dentist utilization imbalance
        if ($dentistUtilization->count() > 1) {
            $max = $dentistUtilization->max('utilization_percentage');
            $min = $dentistUtilization->min('utilization_percentage');
            if ($max - $min > 20) {
                $maxDentist = $dentistUtilization->where('utilization_percentage', $max)->first();
                $minDentist = $dentistUtilization->where('utilization_percentage', $min)->first();
                $recommendations[] = [
                    'type' => 'Dentist Load Balancing',
                    'message' => "{$maxDentist->name} has {$maxDentist->utilization_percentage}% of appointments while {$minDentist->name} has {$minDentist->utilization_percentage}%. Consider better distribution.",
                    'priority' => 'medium'
                ];
            }
        }

        return view('staff.reports.scheduling-optimization', [
            'durationStats' => $durationStats,
            'hourlyDistribution' => $hourlyDistribution,
            'dailyDistribution' => $appointmentsByDay,
            'dentistUtilization' => $dentistUtilization,
            'recommendations' => $recommendations,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalAppointments' => $totalCompletedAppointments
        ]);
    }

    /**
     * Calculate variance for array of numbers
     */
    private function calculateVariance(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }

        $mean = array_sum($numbers) / count($numbers);
        $squareDiffs = array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $numbers);

        return sqrt(array_sum($squareDiffs) / count($squareDiffs));
    }
}
