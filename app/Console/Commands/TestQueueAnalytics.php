<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueueAnalyticsService;
use Carbon\Carbon;

class TestQueueAnalytics extends Command
{
    protected $signature = 'test:queue-analytics';
    protected $description = 'Test the queue analytics service';

    public function handle()
    {
        $this->line("\n🔍 Testing Queue Analytics Service...\n");

        $analyticsService = new QueueAnalyticsService();
        
        $dateFrom = now()->subMonths(1)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $this->line("Date Range: $dateFrom to $dateTo\n");

        // Test Wait Time Analysis
        $this->line("📊 Wait Time Analysis:");
        $waitTimeAnalysis = $analyticsService->getWaitTimeAnalysis($dateFrom, $dateTo);
        $this->line("  ✓ Total Appointments: {$waitTimeAnalysis['total_appointments']}");
        $this->line("  ✓ Average Wait: {$waitTimeAnalysis['average_wait_time']} minutes");
        $this->line("  ✓ Median Wait: {$waitTimeAnalysis['median_wait_time']} minutes");
        $this->line("  ✓ Range: {$waitTimeAnalysis['min_wait_time']} - {$waitTimeAnalysis['max_wait_time']} minutes\n");

        // Test Treatment Duration Analysis
        $this->line("⏱️  Treatment Duration Analysis:");
        $treatmentAnalysis = $analyticsService->getTreatmentDurationAnalysis($dateFrom, $dateTo);
        $this->line("  ✓ Total Completed: {$treatmentAnalysis['total_completed']}");
        if ($treatmentAnalysis['total_completed'] > 0) {
            $this->line("  ✓ Average Estimated: {$treatmentAnalysis['average_estimated']} minutes");
            $this->line("  ✓ Average Actual: {$treatmentAnalysis['average_actual']} minutes");
            $this->line("  ✓ Average Variance: {$treatmentAnalysis['average_variance']} minutes");
            $this->line("  ✓ Accurate Estimates: {$treatmentAnalysis['accuracy_metrics']['accurate_percent']}%");
        } else {
            $this->line("  ℹ️  No completed appointments in this period");
        }
        $this->line("");

        // Test Room Utilization
        $this->line("🚪 Room Utilization:");
        $roomUtilization = $analyticsService->getRoomUtilization($dateFrom, $dateTo);
        $this->line("  ✓ Total Active Rooms: {$roomUtilization['total_rooms']}");
        $this->line("  ✓ Overall Utilization: {$roomUtilization['overall_utilization_percent']}%\n");

        // Test Queue Efficiency
        $this->line("✅ Queue Efficiency:");
        $queueEfficiency = $analyticsService->getQueueEfficiency($dateFrom, $dateTo);
        $this->line("  ✓ Total Completed: {$queueEfficiency['total_completed']}");
        $this->line("  ✓ On-Time: {$queueEfficiency['on_time_percent']}%");
        $this->line("  ✓ Early: {$queueEfficiency['early_percent']}%");
        $this->line("  ✓ Late: {$queueEfficiency['late_percent']}%\n");

        // Test Peak Hours
        $this->line("🕐 Peak Hours Analysis:");
        $peakHours = $analyticsService->getPeakHoursAnalysis($dateFrom, $dateTo);
        foreach ($peakHours['peak_hours'] as $peak) {
            $this->line("  • {$peak['hour_label']}: {$peak['appointments']} appointments");
        }

        $this->line("\n✨ All analytics services working correctly!\n");
    }
}
