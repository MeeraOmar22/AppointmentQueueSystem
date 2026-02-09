<?php

namespace App\Http\Controllers\Public;

use App\Models\Service;
use Illuminate\Http\JsonResponse;

class PublicServiceController extends Controller
{
    /**
     * GET /api/public/services
     * Return all available services with safe data only
     */
    public function index(): JsonResponse
    {
        try {
            $services = Service::where('status', 1)
                ->select('id', 'name', 'description', 'price', 'estimated_duration')
                ->orderBy('name')
                ->get()
                ->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name ?? 'Unknown Service',
                        'description' => $service->description ?? '',
                        'price' => 'RM ' . number_format($service->price ?? 0, 2),
                        'duration' => (int)($service->estimated_duration ?? 0),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $services,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ], 200);
        }
    }
}
