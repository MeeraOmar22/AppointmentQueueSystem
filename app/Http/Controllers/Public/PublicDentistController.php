<?php

namespace App\Http\Controllers\Public;

use App\Models\Dentist;
use Illuminate\Http\JsonResponse;

class PublicDentistController extends Controller
{
    /**
     * GET /api/public/dentists
     * Return active dentists only with safe data
     */
    public function index(): JsonResponse
    {
        try {
            $dentists = Dentist::where('status', 1)
                ->select('id', 'name', 'specialization', 'years_of_experience', 'photo')
                ->orderBy('name')
                ->get()
                ->map(function ($dentist) {
                    return [
                        'id' => $dentist->id,
                        'name' => $dentist->name ?? 'Unknown Dentist',
                        'specialization' => $dentist->specialization ?? 'General Dentistry',
                        'experience' => (int)($dentist->years_of_experience ?? 0),
                        'photo' => $dentist->photo ? asset('storage/' . $dentist->photo) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $dentists,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ], 200);
        }
    }

    /**
     * GET /api/public/dentists/available
     * Return dentists available for a specific date/time
     */
    public function available(): JsonResponse
    {
        try {
            $date = request('date');
            $time = request('time');

            if (!$date || !$time) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            // Get all active dentists first
            $dentists = Dentist::where('status', 1)
                ->select('id', 'name', 'specialization')
                ->orderBy('name')
                ->get();

            // TODO: Add availability check logic here
            // For now, return all active dentists

            return response()->json([
                'success' => true,
                'data' => $dentists->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                ]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ], 200);
        }
    }
}
