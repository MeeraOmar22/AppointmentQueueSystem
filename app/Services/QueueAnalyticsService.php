<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Queue Analytics Service
 * 
 * Analyzes actual queue performance using recorded times
 * Provides metrics for reporting and algorithm improvement
 */
class QueueAnalyticsService
{
    /**
     * Get wait time analysis for a date range
     * Compares estimated vs actual wait times
     */
    public function getWaitTimeAnalysis(string $dateFrom, string $dateTo, string $clinicLocation = 'seremban'): array
    {
        $appointments = Appointment::where('clinic_location', $clinicLocation)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('actual_start_time', '!=', null)
            ->where('check_in_time', '!=', null)
            ->get();

        if ($appointments->isEmpty()) {
            return [
                'total_appointments' => 0,
                'average_wait_time' => 0,
                'median_wait_time' => 0,
                'min_wait_time' => 0,
                'max_wait_time' => 0,
                'appointments_by_service' => [],
                'wait_time_distribution' => [],
            ];
        }

        $waitTimes = $appointments->map(function ($appointment) {
            return [
                'appointment_id' => $appointment->id,
                'patient' => $appointment->patient_name,
                'service' => $appointment->service?->name ?? 'N/A',
                'check_in_time' => $appointment->check_in_time,
                'actual_start_time' => $appointment->actual_start_time,
                'wait_minutes' => Carbon::parse($appointment->check_in_time)
                    ->diffInMinutes(Carbon::parse($appointment->actual_start_time)),
            ];
        });

        $groupedByService = $appointments->groupBy('service_id')->map(function ($group) {
            $times = $group->map(function ($apt) {
                return Carbon::parse($apt->check_in_time)
                    ->diffInMinutes(Carbon::parse($apt->actual_start_time));
            });

            return [
                'service_name' => $group->first()->service?->name ?? 'N/A',
                'count' => $group->count(),
                'avg_wait' => round($times->avg(), 2),
                'min_wait' => $times->min(),
                'max_wait' => $times->max(),
            ];
        });

        return [
            'total_appointments' => $appointments->count(),
            'average_wait_time' => round($waitTimes->avg('wait_minutes'), 2),
            'min_wait_time' => $waitTimes->min('wait_minutes'),
            'max_wait_time' => $waitTimes->max('wait_minutes'),
            'median_wait_time' => $this->calculateMedian($waitTimes->pluck('wait_minutes')->toArray()),
            'appointments_by_service' => $groupedByService->values(),
            'wait_time_distribution' => $this->getWaitTimeDistribution($waitTimes),
        ];
    }

    /**
     * Get treatment duration analysis
     * Compares estimated vs actual treatment times
     */
    public function getTreatmentDurationAnalysis(string $dateFrom, string $dateTo, string $clinicLocation = 'seremban'): array
    {
        $appointments = Appointment::where('clinic_location', $clinicLocation)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('actual_start_time', '!=', null)
            ->where('actual_end_time', '!=', null)
            ->with('service')
            ->get();

        if ($appointments->isEmpty()) {
            return [
                'total_completed' => 0,
                'average_duration' => 0,
                'durations_by_service' => [],
            ];
        }

        $durations = $appointments->map(function ($appointment) {
            $estimated = $appointment->service?->estimated_duration ?? 30;
            $actual = Carbon::parse($appointment->actual_start_time)
                ->diffInMinutes(Carbon::parse($appointment->actual_end_time));

            return [
                'appointment_id' => $appointment->id,
                'service' => $appointment->service?->name ?? 'N/A',
                'estimated' => $estimated,
                'actual' => $actual,
                'variance' => $actual - $estimated,
                'variance_percent' => round((($actual - $estimated) / $estimated) * 100, 2),
            ];
        });

        $groupedByService = $appointments->groupBy('service_id')->map(function ($group) {
            $durationData = $group->map(function ($apt) {
                $estimated = $apt->service?->estimated_duration ?? 30;
                return [
                    'estimated' => $estimated,
                    'actual' => Carbon::parse($apt->actual_start_time)
                        ->diffInMinutes(Carbon::parse($apt->actual_end_time)),
                ];
            });

            $actualTimes = $durationData->pluck('actual');
            $estimatedAvg = $durationData->avg('estimated');
            $actualAvg = $actualTimes->avg();

            return [
                'service_name' => $group->first()->service?->name ?? 'N/A',
                'count' => $group->count(),
                'estimated_avg' => round($estimatedAvg, 2),
                'actual_avg' => round($actualAvg, 2),
                'variance_avg' => round($actualAvg - $estimatedAvg, 2),
                'min_actual' => $actualTimes->min(),
                'max_actual' => $actualTimes->max(),
            ];
        });

        return [
            'total_completed' => $appointments->count(),
            'average_estimated' => round($appointments->avg(fn($a) => $a->service?->estimated_duration ?? 30), 2),
            'average_actual' => round($durations->avg('actual'), 2),
            'average_variance' => round($durations->avg('variance'), 2),
            'durations_by_service' => $groupedByService->values(),
            'accuracy_metrics' => $this->getAccuracyMetrics($durations),
        ];
    }

    /**
     * Get room utilization metrics
     */
    public function getRoomUtilization(string $dateFrom, string $dateTo, string $clinicLocation = 'seremban'): array
    {
        $appointments = Appointment::where('clinic_location', $clinicLocation)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('actual_start_time', '!=', null)
            ->where('actual_end_time', '!=', null)
            ->with('queue')
            ->get();

        $rooms = Room::where('clinic_location', $clinicLocation)->where('is_active', true)->get();
        $dateRange = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;

        if ($appointments->isEmpty()) {
            return [
                'total_rooms' => $rooms->count(),
                'overall_utilization_percent' => 0,
                'utilization_by_room' => [],
                'date_range' => $dateRange . ' days',
            ];
        }

        // Calculate utilization per room
        $utilizationByRoom = $rooms->map(function ($room) use ($appointments, $dateRange) {
            $roomAppointments = $appointments->filter(fn($apt) => $apt->queue?->room_id === $room->id);

            if ($roomAppointments->isEmpty()) {
                return [
                    'room_number' => $room->room_number,
                    'total_minutes' => 0,
                    'utilization_percent' => 0,
                    'appointments_count' => 0,
                ];
            }

            $totalMinutes = $roomAppointments->sum(function ($apt) {
                return Carbon::parse($apt->actual_start_time)
                    ->diffInMinutes(Carbon::parse($apt->actual_end_time));
            });

            // Working hours: 8:00 AM - 5:00 PM = 9 hours = 540 minutes
            $availableMinutes = $dateRange * 540;
            $utilizationPercent = round(($totalMinutes / $availableMinutes) * 100, 2);

            return [
                'room_number' => $room->room_number,
                'total_minutes' => $totalMinutes,
                'utilization_percent' => $utilizationPercent,
                'appointments_count' => $roomAppointments->count(),
            ];
        });

        $totalMinutes = $utilizationByRoom->sum('total_minutes');
        $totalAvailable = (Room::where('clinic_location', $clinicLocation)->where('is_active', true)->count()) * $dateRange * 540;
        $overallUtilization = $totalAvailable > 0 ? round(($totalMinutes / $totalAvailable) * 100, 2) : 0;

        return [
            'total_rooms' => $rooms->count(),
            'overall_utilization_percent' => $overallUtilization,
            'utilization_by_room' => $utilizationByRoom->sortByDesc('utilization_percent')->values(),
            'date_range' => $dateRange . ' days',
        ];
    }

    /**
     * Get queue efficiency metrics
     */
    public function getQueueEfficiency(string $dateFrom, string $dateTo, string $clinicLocation = 'seremban'): array
    {
        $appointments = Appointment::where('clinic_location', $clinicLocation)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->where('check_in_time', '!=', null)
            ->get();

        if ($appointments->isEmpty()) {
            return [
                'total_completed' => 0,
                'on_time_count' => 0,
                'on_time_percent' => 0,
                'early_completions' => 0,
                'early_percent' => 0,
                'late_completions' => 0,
                'late_percent' => 0,
            ];
        }

        $onTimeCount = 0;
        $earlyCount = 0;
        $lateCount = 0;

        foreach ($appointments as $appointment) {
            $scheduledEnd = Carbon::parse($appointment->end_at);
            $actualEnd = Carbon::parse($appointment->actual_end_time);

            if ($actualEnd->equalTo($scheduledEnd)) {
                $onTimeCount++;
            } elseif ($actualEnd->isBefore($scheduledEnd)) {
                $earlyCount++;
            } else {
                $lateCount++;
            }
        }

        return [
            'total_completed' => $appointments->count(),
            'on_time_count' => $onTimeCount,
            'on_time_percent' => round(($onTimeCount / $appointments->count()) * 100, 2),
            'early_completions' => $earlyCount,
            'early_percent' => round(($earlyCount / $appointments->count()) * 100, 2),
            'late_completions' => $lateCount,
            'late_percent' => round(($lateCount / $appointments->count()) * 100, 2),
        ];
    }

    /**
     * Get peak hours analysis
     */
    public function getPeakHoursAnalysis(string $dateFrom, string $dateTo, string $clinicLocation = 'seremban'): array
    {
        $appointments = Appointment::where('clinic_location', $clinicLocation)
            ->whereBetween('appointment_date', [$dateFrom, $dateTo])
            ->where('actual_start_time', '!=', null)
            ->get();

        if ($appointments->isEmpty()) {
            return [
                'peak_hour' => 'N/A',
                'peak_hour_appointments' => 0,
                'busiest_day' => 'N/A',
                'busiest_day_appointments' => 0,
                'hourly_distribution' => [],
                'daily_distribution' => [],
                'total_appointments' => 0,
            ];
        }

        // Calculate hourly distribution
        $hourlyDistribution = [];
        $dailyDistribution = [];

        foreach ($appointments as $appointment) {
            $hour = Carbon::parse($appointment->actual_start_time)->hour;
            $day = Carbon::parse($appointment->appointment_date)->format('l');
            
            $hourlyDistribution[$hour] = ($hourlyDistribution[$hour] ?? 0) + 1;
            $dailyDistribution[$day] = ($dailyDistribution[$day] ?? 0) + 1;
        }

        ksort($hourlyDistribution);

        // Get peak hour (hour with most appointments)
        $peakHour = collect($hourlyDistribution)->keys()->first();
        $peakHourAppointments = $hourlyDistribution[$peakHour] ?? 0;

        // Get busiest day
        $busiestDay = collect($dailyDistribution)->sortDesc()->keys()->first();
        $busiestDayAppointments = $dailyDistribution[$busiestDay] ?? 0;

        return [
            'peak_hour' => str_pad($peakHour, 2, '0', STR_PAD_LEFT) . ':00',
            'peak_hour_appointments' => $peakHourAppointments,
            'busiest_day' => $busiestDay ?? 'N/A',
            'busiest_day_appointments' => $busiestDayAppointments,
            'hourly_distribution' => $hourlyDistribution,
            'daily_distribution' => $dailyDistribution,
            'total_appointments' => $appointments->count(),
        ];
    }

    /**
     * Helper: Calculate median of array
     */
    private function calculateMedian(array $values): float
    {
        if (empty($values)) return 0;

        sort($values);
        $count = count($values);
        $mid = floor(($count - 1) / 2);

        return $count % 2 !== 0
            ? $values[$mid]
            : ($values[$mid] + $values[$mid + 1]) / 2;
    }

    /**
     * Helper: Get wait time distribution
     */
    private function getWaitTimeDistribution(Collection $waitTimes): array
    {
        $ranges = [
            '0-5' => 0,
            '6-10' => 0,
            '11-20' => 0,
            '21-30' => 0,
            '31-45' => 0,
            '45+' => 0,
        ];

        foreach ($waitTimes as $item) {
            $minutes = $item['wait_minutes'];
            if ($minutes <= 5) $ranges['0-5']++;
            elseif ($minutes <= 10) $ranges['6-10']++;
            elseif ($minutes <= 20) $ranges['11-20']++;
            elseif ($minutes <= 30) $ranges['21-30']++;
            elseif ($minutes <= 45) $ranges['31-45']++;
            else $ranges['45+']++;
        }

        return $ranges;
    }

    /**
     * Helper: Get accuracy metrics
     */
    private function getAccuracyMetrics(Collection $durations): array
    {
        $withinRange = $durations->filter(fn($d) => abs($d['variance']) <= 5)->count();
        $overEstimated = $durations->filter(fn($d) => $d['variance'] < -5)->count();
        $underEstimated = $durations->filter(fn($d) => $d['variance'] > 5)->count();
        $total = $durations->count();

        return [
            'accurate_estimates' => $withinRange,
            'accurate_percent' => round(($withinRange / $total) * 100, 2),
            'overestimated' => $overEstimated,
            'overestimated_percent' => round(($overEstimated / $total) * 100, 2),
            'underestimated' => $underEstimated,
            'underestimated_percent' => round(($underEstimated / $total) * 100, 2),
        ];
    }
}
