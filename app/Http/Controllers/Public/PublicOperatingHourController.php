<?php

namespace App\Http\Controllers\Public;

use App\Models\OperatingHour;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PublicOperatingHourController extends Controller
{
    /**
     * GET /api/public/hours
     * Return operating hours for the week
     */
    public function index(): JsonResponse
    {
        try {
            $hours = OperatingHour::all();

            if ($hours->isEmpty()) {
                // Default hours if not configured
                return response()->json([
                    'success' => true,
                    'data' => $this->defaultHours(),
                ]);
            }

            $formatted = $hours->map(function ($hour) {
                return [
                    'day' => ucfirst($hour->day_of_week),
                    'open' => $hour->start_time ? substr($hour->start_time, 0, 5) : '09:00',
                    'close' => $hour->end_time ? substr($hour->end_time, 0, 5) : '21:00',
                    'closed' => (bool)$hour->is_closed,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => $this->defaultHours(),
            ], 200);
        }
    }

    /**
     * GET /api/public/hours/today
     * Return today's operating hours only
     */
    public function today(): JsonResponse
    {
        try {
            $today = now()->format('l'); // Monday, Tuesday, etc.

            $hour = OperatingHour::where('day_of_week', $today)->first();

            if (!$hour) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'day' => $today,
                        'open' => '09:00',
                        'close' => '21:00',
                        'closed' => false,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'day' => ucfirst($hour->day_of_week),
                    'open' => $hour->start_time ? substr($hour->start_time, 0, 5) : '09:00',
                    'close' => $hour->end_time ? substr($hour->end_time, 0, 5) : '21:00',
                    'closed' => (bool)$hour->is_closed,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'day' => now()->format('l'),
                    'open' => '09:00',
                    'close' => '21:00',
                    'closed' => false,
                ],
            ], 200);
        }
    }

    /**
     * Default hours fallback
     */
    private function defaultHours(): array
    {
        return [
            ['day' => 'Monday', 'open' => '09:00', 'close' => '21:00', 'closed' => false],
            ['day' => 'Tuesday', 'open' => '09:00', 'close' => '21:00', 'closed' => false],
            ['day' => 'Wednesday', 'open' => '09:00', 'close' => '21:00', 'closed' => false],
            ['day' => 'Thursday', 'open' => '09:00', 'close' => '21:00', 'closed' => false],
            ['day' => 'Friday', 'open' => '09:00', 'close' => '21:00', 'closed' => false],
            ['day' => 'Saturday', 'open' => '09:00', 'close' => '21:00', 'closed' => false],
            ['day' => 'Sunday', 'open' => '00:00', 'close' => '00:00', 'closed' => true],
        ];
    }
}
