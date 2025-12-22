<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration {
    public function up(): void
    {
        // Build a map of service_id => estimated_duration
        $services = DB::table('services')->select('id', 'estimated_duration')->get()->keyBy('id');

        // Backfill appointments missing start_at and/or end_at
        DB::table('appointments')
            ->orderBy('id')
            ->chunk(500, function ($rows) use ($services) {
                foreach ($rows as $row) {
                    $payload = [];

                    // Determine duration
                    $duration = 15;
                    if ($row->service_id && isset($services[$row->service_id])) {
                        $serviceDuration = (int)($services[$row->service_id]->estimated_duration ?? 0);
                        $duration = max($serviceDuration, 15);
                    }

                    // Compute start_at fallback
                    $computedStart = null;
                    if (empty($row->start_at) && !empty($row->appointment_date) && !empty($row->appointment_time)) {
                        try {
                            $computedStart = Carbon::parse($row->appointment_date.' '.$row->appointment_time);
                            $payload['start_at'] = $computedStart->toDateTimeString();
                        } catch (\Throwable $e) {
                            // Skip invalid date/time rows
                        }
                    }

                    // Compute end_at fallback
                    $startForEnd = null;
                    if (!empty($payload['start_at'])) {
                        $startForEnd = Carbon::parse($payload['start_at']);
                    } elseif (!empty($row->start_at)) {
                        try {
                            $startForEnd = Carbon::parse($row->start_at);
                        } catch (\Throwable $e) {
                            $startForEnd = null;
                        }
                    } elseif (!empty($row->appointment_date) && !empty($row->appointment_time)) {
                        try {
                            $startForEnd = Carbon::parse($row->appointment_date.' '.$row->appointment_time);
                        } catch (\Throwable $e) {
                            $startForEnd = null;
                        }
                    }

                    if (empty($row->end_at) && $startForEnd) {
                        $payload['end_at'] = $startForEnd->copy()->addMinutes($duration)->toDateTimeString();
                    }

                    if (!empty($payload)) {
                        DB::table('appointments')->where('id', $row->id)->update($payload);
                    }
                }
            });
    }

    public function down(): void
    {
        // No-op: Data backfill cannot be reliably reversed.
    }
};
