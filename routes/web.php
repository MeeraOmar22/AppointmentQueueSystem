<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Staff\AppointmentController as StaffAppointmentController;
use App\Http\Controllers\Staff\OperatingHourController;
use App\Http\Controllers\Staff\DentistController as StaffDentistController;
use App\Http\Controllers\Staff\ServiceController as StaffServiceController;
use App\Http\Controllers\Staff\ActivityLogController;
use App\Http\Controllers\Staff\QuickEditController;
use App\Http\Controllers\Staff\DentistScheduleController;
use App\Http\Controllers\Staff\PastController;
use App\Http\Controllers\Staff\CalendarController;
use App\Http\Controllers\Staff\DentistLeaveController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Staff\FeedbackController as StaffFeedbackController;
use App\Http\Controllers\Staff\RoomController;
use App\Http\Controllers\Developer\AuthController as DeveloperAuthController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Api\QueueController;


Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Authenticated home dashboard route used by login redirect
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');


Route::get('/', [PublicController::class, 'home']);


Route::get('/book', [AppointmentController::class, 'create']);
Route::post('/book', [AppointmentController::class, 'store']);
Route::get('/visit/{token}', [AppointmentController::class, 'visitStatus']);
Route::get('/visit-lookup', [AppointmentController::class, 'visitLookup']);
Route::post('/visit/{token}/check-in', [AppointmentController::class, 'publicCheckIn']);
Route::get('/track/{code}', [AppointmentController::class, 'trackByCode']);
Route::get('/checkin', [AppointmentController::class, 'checkinForm']);
Route::post('/checkin', [AppointmentController::class, 'checkinSubmit']);
Route::get('/queue-board', [AppointmentController::class, 'queueBoard']);
Route::get('/find-my-booking', [AppointmentController::class, 'findMyBookingForm']);
Route::post('/find-my-booking', [AppointmentController::class, 'findMyBookingSubmit']);

Route::get('/', [PublicController::class, 'home']);
Route::get('/about', [PublicController::class, 'about']);
Route::get('/services', [PublicController::class, 'services']);
Route::get('/dentists', [PublicController::class, 'dentists']);
Route::get('/contact', [PublicController::class, 'contact']);
Route::get('/hours', [PublicController::class, 'hours']);

Route::get('/chat', [ChatbotController::class, 'index']);
Route::post('/chat', [ChatbotController::class, 'handle']);

// Feedback Routes
Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
Route::get('/feedback/thanks/{id?}', [FeedbackController::class, 'thanks'])->name('feedback.thanks');

Route::middleware(['auth', 'role:staff'])->group(function () {
    // Quick Edit Dashboard
    Route::get('/staff/quick-edit', [QuickEditController::class, 'index']);
    Route::patch('/staff/dentists/{dentist}/status', [QuickEditController::class, 'updateDentistStatus']);
    Route::patch('/staff/services/{service}/status', [QuickEditController::class, 'updateServiceStatus']);
    Route::patch('/staff/operating-hours/{operatingHour}', [QuickEditController::class, 'updateOperatingHour']);
    Route::post('/staff/operating-hours/{operatingHour}/duplicate', [QuickEditController::class, 'duplicateOperatingHour']);
    // Staff visibility and info
    Route::patch('/staff/users/{user}/visibility', [QuickEditController::class, 'updateStaffVisibility']);
    Route::put('/staff/users/{user}', [QuickEditController::class, 'updateStaffInfo']);
    Route::post('/staff/users', [QuickEditController::class, 'storeStaff']);
    Route::delete('/staff/users/{user}', [QuickEditController::class, 'destroyStaff']);

    // Past (Deleted) Records
    Route::get('/staff/past', [PastController::class, 'index']);
    Route::post('/staff/past/dentists/{id}/restore', [PastController::class, 'restoreDentist']);
    Route::delete('/staff/past/dentists/{id}', [PastController::class, 'forceDeleteDentist']);
    Route::post('/staff/past/staff/{id}/restore', [PastController::class, 'restoreStaff']);
    Route::delete('/staff/past/staff/{id}', [PastController::class, 'forceDeleteStaff']);

    Route::get('/staff/appointments', [StaffAppointmentController::class, 'index']);
    Route::get('/staff/appointments/create', [StaffAppointmentController::class, 'create']);
    Route::post('/staff/appointments', [StaffAppointmentController::class, 'store']);
    Route::post('/staff/checkin/{id}', [StaffAppointmentController::class, 'checkIn']);
    Route::post('/staff/appointments/{id}/check-in', [StaffAppointmentController::class, 'checkIn']);
    Route::post('/staff/walk-in', [StaffAppointmentController::class, 'storeWalkIn']);
    Route::put('/staff/queue/{queue}', [StaffAppointmentController::class, 'updateQueueStatus']);
    Route::get('/staff/appointments/{id}/edit', [StaffAppointmentController::class, 'edit']);
    Route::put('/staff/appointments/{id}', [StaffAppointmentController::class, 'update']);
    Route::delete('/staff/appointments/{id}', [StaffAppointmentController::class, 'destroy']);
    Route::get('/staff/treatment-completion', [StaffAppointmentController::class, 'completionPage'])->name('treatment.completion');
    Route::post('/staff/treatment-completion/{appointmentId}', [StaffAppointmentController::class, 'completeTreatment'])->name('treatment.complete');

    
    // Operating Hours Management
    Route::get('/staff/operating-hours', [OperatingHourController::class, 'index']);
    Route::get('/staff/operating-hours/create', [OperatingHourController::class, 'create']);
    Route::post('/staff/operating-hours', [OperatingHourController::class, 'store']);
    Route::get('/staff/operating-hours/{id}/edit', [OperatingHourController::class, 'edit']);
    Route::put('/staff/operating-hours/{id}', [OperatingHourController::class, 'update']);
    Route::delete('/staff/operating-hours/{id}', [OperatingHourController::class, 'destroy']);
    Route::post('/staff/operating-hours/bulk-delete', [OperatingHourController::class, 'bulkDestroy']);
    
    // Dentists Management
    Route::post('/staff/dentists/bulk-delete', [StaffDentistController::class, 'bulkDestroy'])->name('dentists.bulkDestroy');
    Route::get('/staff/dentists', [StaffDentistController::class, 'index'])->name('staff.dentists.index');
    Route::get('/staff/dentists/create', [StaffDentistController::class, 'create']);
    Route::post('/staff/dentists', [StaffDentistController::class, 'store']);
    Route::post('/staff/dentists/{id}/deactivate', [StaffDentistController::class, 'deactivate'])->name('staff.dentists.deactivate');
    Route::get('/staff/dentists/{id}/edit', [StaffDentistController::class, 'edit']);
    Route::put('/staff/dentists/{id}', [StaffDentistController::class, 'update']);
    Route::delete('/staff/dentists/{id}', [StaffDentistController::class, 'destroy']);
    Route::patch('/staff/dentists/{dentist}/status', [StaffDentistController::class, 'updateStatus'])->name('staff.dentists.updateStatus');
    Route::get('/api/dentists/stats', [StaffDentistController::class, 'stats'])->name('staff.dentists.stats');
    
    // Treatment Room Management (Dynamic Resource Configuration)
    Route::get('/staff/rooms', [RoomController::class, 'index'])->name('staff.rooms.index');
    Route::get('/staff/rooms/create', [RoomController::class, 'create'])->name('staff.rooms.create');
    Route::post('/staff/rooms', [RoomController::class, 'store'])->name('staff.rooms.store');
    Route::get('/staff/rooms/{room}/edit', [RoomController::class, 'edit'])->name('staff.rooms.edit');
    Route::put('/staff/rooms/{room}', [RoomController::class, 'update'])->name('staff.rooms.update');
    Route::delete('/staff/rooms/{room}', [RoomController::class, 'destroy'])->name('staff.rooms.destroy');
    Route::post('/staff/rooms/bulk-status', [RoomController::class, 'bulkToggleStatus'])->name('staff.rooms.bulkToggleStatus');
    Route::get('/api/rooms/stats', [RoomController::class, 'stats'])->name('staff.rooms.stats');
    
    // Services Management
    Route::get('/staff/services', [StaffServiceController::class, 'index']);
    Route::get('/staff/services/create', [StaffServiceController::class, 'create']);
    Route::post('/staff/services', [StaffServiceController::class, 'store']);
    Route::get('/staff/services/{id}/edit', [StaffServiceController::class, 'edit']);
    Route::put('/staff/services/{id}', [StaffServiceController::class, 'update']);
    Route::delete('/staff/services/{id}', [StaffServiceController::class, 'destroy']);
    Route::post('/staff/services/bulk-delete', [StaffServiceController::class, 'bulkDestroy']);

    // Dentist schedules
    Route::get('/staff/dentist-schedules', [DentistScheduleController::class, 'index'])->name('staff.dentist-schedules.index');
    Route::patch('/staff/dentist-schedules/{dentistSchedule}', [DentistScheduleController::class, 'update'])->name('staff.dentist-schedules.update');
    Route::get('/staff/dentist-schedules/calendar', [DentistScheduleController::class, 'calendar'])->name('staff.dentist-schedules.calendar');
    Route::get('/staff/dentist-schedules/calendar/events', [DentistScheduleController::class, 'events'])->name('staff.dentist-schedules.events');
    
    // Dentist leave management
    Route::post('/staff/dentist-leaves', [DentistLeaveController::class, 'store'])->name('staff.dentist-leaves.store');
    Route::delete('/staff/dentist-leaves/{dentistLeave}', [DentistLeaveController::class, 'destroy'])->name('staff.dentist-leaves.destroy');
    
    // Feedback Management
    Route::get('/staff/feedback', [StaffFeedbackController::class, 'index'])->name('staff.feedback.index');
    Route::get('/staff/feedback/{id}', [StaffFeedbackController::class, 'show'])->name('staff.feedback.show');

    // Staff Calendar
    Route::get('/staff/calendar', [CalendarController::class, 'index'])->name('staff.calendar.index');
    Route::get('/staff/calendar/events', [CalendarController::class, 'events'])->name('staff.calendar.events');
    
    // Treatment Completion & Queue Management
    Route::get('/staff/treatment-completion', [StaffAppointmentController::class, 'completionPage'])->name('treatment.completion');
    Route::post('/staff/treatment-completion/{appointmentId}', [StaffAppointmentController::class, 'completeTreatment'])->name('treatment.complete');
    Route::post('/staff/pause-queue', [StaffAppointmentController::class, 'pauseQueue'])->name('queue.pause');
    Route::post('/staff/resume-queue', [StaffAppointmentController::class, 'resumeQueue'])->name('queue.resume');
    Route::get('/api/queue/status', [StaffAppointmentController::class, 'getQueueStatus'])->name('api.queue.status');
    
    // Waiting Area Display (TV Screen)
    Route::get('/public/waiting-area', function () {
        return view('public.waiting-area-display');
    })->name('public.waiting-area');
    
    // Real-time API endpoint for staff appointments
    Route::get('/api/staff/appointments', [StaffAppointmentController::class, 'appointmentsApi']);
});

// Developer Routes (role: developer)
Route::middleware(['auth', 'role:developer'])->group(function () {
    Route::get('/developer/dashboard', [DeveloperDashboardController::class, 'index'])->name('developer.dashboard');
    Route::get('/developer/activity-logs', [DeveloperDashboardController::class, 'activityLogs'])->name('developer.activity-logs');
    Route::get('/developer/activity-logs/{id}', [DeveloperDashboardController::class, 'logDetails'])->name('developer.log-details');
    Route::get('/developer/api-test', [DeveloperDashboardController::class, 'apiTest'])->name('developer.api-test');
    Route::get('/developer/system-info', [DeveloperDashboardController::class, 'systemInfo'])->name('developer.system-info');
    Route::get('/developer/database', [DeveloperDashboardController::class, 'databaseTools'])->name('developer.database');
    Route::post('/developer/logout', [DeveloperAuthController::class, 'logout'])->name('developer.logout');
});

// Developer Auth Routes (no auth required)
Route::get('/developer/login', [DeveloperAuthController::class, 'showLoginForm'])->name('developer.login');
Route::post('/developer/login', [DeveloperAuthController::class, 'login'])->name('developer.login.submit');

// Real-time API endpoints for live updates (public)
Route::get('/api/track/{code}', [AppointmentController::class, 'trackByCodeApi']);
// Queue Management API endpoints
Route::post('/api/check-in', [QueueController::class, 'checkIn']);
Route::post('/api/walk-in', [QueueController::class, 'createWalkIn']);
Route::get('/api/queue/next', [QueueController::class, 'getNextPatient']);
Route::get('/api/queue/{queue}/status', [QueueController::class, 'getQueueStatus']);
Route::patch('/api/queue/{queue}/status', [QueueController::class, 'updateQueueStatus']);
Route::get('/api/rooms/status', [QueueController::class, 'getRoomStatus']);
Route::get('/api/queue/stats', [QueueController::class, 'getQueueStats']);
Route::post('/api/auto-mark-late', [QueueController::class, 'autoMarkLate']);
Route::post('/api/auto-mark-no-show', [QueueController::class, 'autoMarkNoShow']);