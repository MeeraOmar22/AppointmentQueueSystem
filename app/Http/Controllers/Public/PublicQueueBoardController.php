<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\JsonResponse;

class PublicQueueBoardController extends Controller
{
    /**
     * GET /api/public/queue-board/data
     * Return current queue status for waiting area display
     * 
     * CRITICAL: Eagerly loads appointment relationships to prevent null values
     */
    public function data(): JsonResponse
    {
        try {
            // FIX: Get current patient with all relationships eagerly loaded (NO select() restrictions)
            // Ensures dentist and room relationships hydrate correctly for display
            $current = Queue::with(['appointment', 'room', 'dentist'])
                ->where('queue_status', 'in_treatment')
                ->first();

            $nowServing = null;
            if ($current) {
                $nowServing = [
                    'queue' => 'A-' . str_pad($current->queue_number, 2, '0', STR_PAD_LEFT),
                    'patient' => $current->appointment?->patient_name ?? 'N/A',
                    'room' => $current->room?->room_number ? 'Room ' . $current->room->room_number : null,
                    'dentist' => $current->dentist?->name ?? null,
                ];
            }

            // FIX: Get next waiting patients with complete relationship loading (NO select() restrictions)
            $nextUp = Queue::with(['appointment', 'dentist'])
                ->where('queue_status', 'waiting')
                ->orderBy('queue_number')
                ->take(3)
                ->get()
                ->map(function ($queue) {
                    return [
                        'queue' => 'A-' . str_pad($queue->queue_number, 2, '0', STR_PAD_LEFT),
                        'patient' => $queue->appointment?->patient_name ?? 'N/A',
                    ];
                });

            return response()->json([
                'success' => true,
                'now_serving' => $nowServing,
                'next_up' => $nextUp,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'now_serving' => null,
                'next_up' => [],
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ], 200);
        }
    }
}
