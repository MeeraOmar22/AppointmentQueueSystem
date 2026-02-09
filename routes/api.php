<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Staff\DentistController;
use App\Http\Controllers\Staff\ServiceController;
use App\Http\Controllers\Staff\RoomController;
use App\Http\Controllers\Staff\AppointmentController;
use App\Http\Controllers\CalendarBookingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes for calendar booking (no auth required)
Route::get('/booking/slots', [CalendarBookingController::class, 'getAvailableSlots']);

// Staff appointment endpoints - session authenticated (used from staff dashboard with session auth)
// These accept session-based auth from the web routes
Route::post('/staff/appointments/{id}/complete', [AppointmentController::class, 'completeTreatment']);
Route::post('/staff/appointments/{id}/call-patient', [AppointmentController::class, 'callPatient']);

// Debug route
Route::get('/debug/test', function () {
    return response()->json(['status' => 'ok', 'time' => now()]);
});

// TEMPORARY: Public test endpoint for call-patient
Route::get('/staff/appointments/{id}/call-patient-test', function ($id) {
    return response()->json(['status' => 'test', 'id' => $id, 'message' => 'Test endpoint working']);
});

// Debug route - authenticated user
Route::middleware(['auth:web'])->get('/debug/user', function (Request $request) {
    return response()->json([
        'authenticated' => $request->user() !== null,
        'user' => $request->user() ? ['id' => $request->user()->id, 'email' => $request->user()->email] : null,
    ]);
});

// Authenticated API routes
Route::middleware(['auth:web'])->group(function () {
    
    // Current authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Staff API endpoints
    Route::prefix('staff')->group(function () {
        // Appointments endpoints
        Route::get('/appointments/today', [AppointmentController::class, 'getAppointmentsData']);
        Route::get('/summary', [AppointmentController::class, 'getSummaryStatistics']);
        Route::get('/queue', [AppointmentController::class, 'getActiveQueue']);
        Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    });
});
