<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\AppointmentController as StaffAppointmentController;
use App\Http\Controllers\Staff\OperatingHourController;
use App\Http\Controllers\Staff\DentistController as StaffDentistController;
use App\Http\Controllers\Staff\ServiceController;
use App\Http\Controllers\Staff\ActivityLogController;
use App\Http\Controllers\Staff\QuickEditController;
use App\Http\Controllers\Staff\DentistScheduleController;
use App\Http\Controllers\Staff\DentistLeaveController;
use App\Http\Controllers\Staff\StaffLeaveController;
use App\Http\Controllers\Staff\CalendarController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Staff\FeedbackController as StaffFeedbackController;
use App\Http\Controllers\Staff\RoomController;
use App\Http\Controllers\Developer\DashboardController as DeveloperDashboardController;
use App\Http\Controllers\Staff\ReportController;
use App\Http\Controllers\PatientReportController;
use App\Http\Controllers\Staff\QueueViewController;
use App\Http\Controllers\Staff\DentistManagementController;
use App\Http\Controllers\Staff\SystemConfigController;
use App\Http\Controllers\Staff\PastController;
use App\Http\Controllers\CalendarBookingController;
use App\Http\Controllers\Public\PublicServiceController;
use App\Http\Controllers\Public\PublicDentistController;
use App\Http\Controllers\Public\PublicOperatingHourController;
use App\Http\Controllers\Public\PublicQueueBoardController;
use App\Http\Controllers\Public\PublicBookingController;
use App\Http\Controllers\Public\PublicTrackingController;
use App\Http\Controllers\Api\QueueController;


Route::get('/', [PublicController::class, 'home']);

Auth::routes();

// Authenticated home dashboard route used by login redirect
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

// Booking (single modern system using calendar)
Route::get('/book', [CalendarBookingController::class, 'showForm'])->name('booking.form');
Route::post('/book/submit', [CalendarBookingController::class, 'submitBooking'])->name('booking.submit');
Route::post('/appointment/cancel/{visit_code}', [CalendarBookingController::class, 'cancelAppointment'])->name('appointment.cancel');

// Self-service rescheduling
Route::get('/appointment/reschedule/{code}', [CalendarBookingController::class, 'showRescheduleForm'])->name('appointment.reschedule.form');
Route::post('/appointment/reschedule/{code}', [CalendarBookingController::class, 'submitReschedule'])->name('appointment.reschedule');

// Patient tracking and check-in
Route::get('/track', [AppointmentController::class, 'trackSearch']);
Route::get('/track/{code}', [AppointmentController::class, 'trackByCode']);
Route::get('/appointment/check-in/{code}', [AppointmentController::class, 'showCheckInConfirmation'])->name('appointment.checkin.form');
Route::post('/appointment/check-in/{code}', [AppointmentController::class, 'publicCheckIn'])->name('appointment.checkin');

// Queue board (waiting area display)
Route::get('/queue-board', [AppointmentController::class, 'queueBoard']);
Route::get('/about', [PublicController::class, 'about']);
Route::get('/services', [PublicController::class, 'services']);
Route::get('/dentists', [PublicController::class, 'dentists']);
Route::get('/contact', [PublicController::class, 'contact']);
Route::get('/hours', [PublicController::class, 'hours']);

// Chatbot routes (controller not available - commented out)
// Route::get('/chat', [ChatbotController::class, 'index']);
// Route::post('/chat', [ChatbotController::class, 'handle']);

// Feedback Routes
Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
Route::get('/feedback/thanks/{id?}', [FeedbackController::class, 'thanks'])->name('feedback.thanks');

// Testing Routes (for development/demo only)
Route::get('/test-feedback', function() {
    return view('public.test-feedback');
});
Route::post('/test-feedback-setup', [AppointmentController::class, 'testFeedbackSetup']);
Route::post('/test-feedback-complete', [AppointmentController::class, 'testFeedbackComplete']);

// Staff routes (operations, management, reports)
Route::middleware(['auth', 'role:staff|admin|developer'])->group(function () {
    // REFACTORED: New primary operational pages
    Route::get('/staff/queue', [QueueViewController::class, 'index'])->name('staff.queue');
    Route::get('/staff/queue/filter/in-treatment', [QueueViewController::class, 'inTreatment'])->name('staff.queue.in-treatment');
    Route::get('/staff/queue/filter/waiting', [QueueViewController::class, 'waiting'])->name('staff.queue.waiting');
    Route::get('/staff/queue/filter/completed', [QueueViewController::class, 'completed'])->name('staff.queue.completed');
    Route::get('/staff/queue/filter/available-dentists', [QueueViewController::class, 'availableDentists'])->name('staff.queue.available-dentists');
    Route::get('/staff/dentist-management', [DentistManagementController::class, 'index'])->name('staff.dentist.management');

    Route::get('/staff/appointments', [StaffAppointmentController::class, 'index'])->name('staff.appointments.index');
    Route::get('/staff/appointments/test/whatsapp', [StaffAppointmentController::class, 'whatsappTest'])->name('staff.appointments.whatsapp-test');
    Route::post('/staff/appointments/send-whatsapp', [StaffAppointmentController::class, 'sendWhatsappMessage'])->name('staff.appointments.send-whatsapp');
    Route::get('/staff/appointments/filter/total', [StaffAppointmentController::class, 'totalAppointments'])->name('staff.appointments.total');
    Route::get('/staff/appointments/filter/queued', [StaffAppointmentController::class, 'queuedAppointments'])->name('staff.appointments.queued');
    Route::get('/staff/appointments/filter/in-treatment', [StaffAppointmentController::class, 'inTreatmentAppointments'])->name('staff.appointments.in-treatment');
    Route::get('/staff/appointments/filter/completed', [StaffAppointmentController::class, 'completedAppointments'])->name('staff.appointments.completed');
    Route::get('/staff/appointments/create', [StaffAppointmentController::class, 'create']);
    Route::post('/staff/appointments', [StaffAppointmentController::class, 'store']);
    Route::post('/staff/checkin/{id}', [StaffAppointmentController::class, 'checkIn']);
    Route::post('/staff/appointments/{id}/check-in', [StaffAppointmentController::class, 'checkIn']);
    Route::post('/staff/walk-in', [StaffAppointmentController::class, 'storeWalkIn']);
    Route::put('/staff/queue/{queue}', [StaffAppointmentController::class, 'updateQueueStatus']);
    Route::get('/staff/appointments/{id}/edit', [StaffAppointmentController::class, 'edit']);
    Route::put('/staff/appointments/{id}', [StaffAppointmentController::class, 'update']);
    Route::post('/staff/appointments/{id}/update-status', [StaffAppointmentController::class, 'updateStatus']);
    Route::post('/staff/appointments/{id}/cancel', [StaffAppointmentController::class, 'cancelAppointment']);
    Route::post('/staff/appointments/{id}/complete-treatment', [StaffAppointmentController::class, 'completeTreatment']);
    Route::delete('/staff/appointments/{id}', [StaffAppointmentController::class, 'destroy']);
    Route::get('/api/staff/appointments/today', [StaffAppointmentController::class, 'getAppointmentsData']);
    Route::get('/api/staff/available-dentists', [StaffAppointmentController::class, 'getAvailableDentists'])->name('api.available-dentists');
    
    // Feedback Management
    Route::get('/staff/feedback', [StaffFeedbackController::class, 'index'])->name('staff.feedback.index');
    Route::get('/staff/feedback/{id}', [StaffFeedbackController::class, 'show'])->name('staff.feedback.show');
    Route::get('/staff/feedback/filter/responses', [StaffFeedbackController::class, 'responses'])->name('staff.feedback.responses');
    Route::get('/staff/feedback/filter/pending', [StaffFeedbackController::class, 'pending'])->name('staff.feedback.pending');
    Route::get('/staff/feedback/filter/overdue', [StaffFeedbackController::class, 'overdue'])->name('staff.feedback.overdue');
    Route::get('/staff/feedback/filter/ratings', [StaffFeedbackController::class, 'ratings'])->name('staff.feedback.ratings');
    Route::post('/staff/feedback/send-request/{appointmentId}', [StaffFeedbackController::class, 'sendRequest'])->name('staff.feedback.send-request');
    Route::post('/staff/feedback/send-reminder/{feedbackRequestId}', [StaffFeedbackController::class, 'sendReminder'])->name('staff.feedback.send-reminder');
    Route::post('/staff/feedback/skip-request/{feedbackRequestId}', [StaffFeedbackController::class, 'skipRequest'])->name('staff.feedback.skip-request');
    
    // Staff Reports & Analytics
    Route::get('/staff/reports/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('/staff/reports/appointments', [ReportController::class, 'appointmentAnalysis'])->name('reports.appointments');
    Route::get('/staff/reports/revenue', [ReportController::class, 'revenueReport'])->name('reports.revenue');
    Route::get('/staff/reports/queue-analytics', [ReportController::class, 'queueAnalytics'])->name('reports.queue-analytics');
    Route::get('/staff/reports/export-appointments', [ReportController::class, 'exportAppointments'])->name('reports.export');
    Route::get('/staff/reports/export-appointments-pdf', [ReportController::class, 'exportAppointmentsPdf'])->name('reports.export-pdf');
    Route::get('/staff/reports/export-revenue-pdf', [ReportController::class, 'exportRevenuePdf'])->name('reports.export-revenue-pdf');
    Route::get('/staff/reports/export-queue-analytics-pdf', [ReportController::class, 'exportQueueAnalyticsPdf'])->name('reports.export-queue-analytics-pdf');
    Route::get('/staff/reports/export-comprehensive-pdf', [ReportController::class, 'exportComprehensiveReportPdf'])->name('reports.export-comprehensive-pdf');
    Route::get('/staff/reports/patient-retention', [ReportController::class, 'patientRetention'])->name('reports.patient-retention');
    
    // Treatment Completion & Queue Management
    Route::post('/staff/pause-queue', [StaffAppointmentController::class, 'pauseQueue'])->name('queue.pause');
    Route::post('/staff/resume-queue', [StaffAppointmentController::class, 'resumeQueue'])->name('queue.resume');
    Route::get('/api/queue/status', [StaffAppointmentController::class, 'getQueueStatus'])->name('api.queue.status');
    
    // Real-time API endpoint for staff appointments
    Route::get('/api/staff/appointments', [StaffAppointmentController::class, 'appointmentsApi']);
    
    // Past (Deleted) Records
    Route::get('/staff/past', [PastController::class, 'index']);
    Route::post('/staff/past/dentists/{id}/restore', [PastController::class, 'restoreDentist']);
    Route::delete('/staff/past/dentists/{id}', [PastController::class, 'forceDeleteDentist']);
    Route::post('/staff/past/staff/{id}/restore', [PastController::class, 'restoreStaff']);
    Route::delete('/staff/past/staff/{id}', [PastController::class, 'forceDeleteStaff']);
    Route::post('/staff/past/services/{id}/restore', [PastController::class, 'restoreService']);
    Route::delete('/staff/past/services/{id}', [PastController::class, 'forceDeleteService']);
    Route::post('/staff/past/appointments/{id}/restore', [PastController::class, 'restoreAppointment']);
    Route::delete('/staff/past/appointments/{id}', [PastController::class, 'forceDeleteAppointment']);
    
    // Dentist schedules
    Route::get('/staff/dentist-schedules', [DentistScheduleController::class, 'index'])->name('staff.dentist-schedules.index');
    Route::patch('/staff/dentist-schedules/{dentistSchedule}', [DentistScheduleController::class, 'update'])->name('staff.dentist-schedules.update');
    Route::get('/staff/dentist-schedules/calendar', [DentistScheduleController::class, 'calendar'])->name('staff.dentist-schedules.calendar');
    Route::get('/staff/dentist-schedules/calendar/events', [DentistScheduleController::class, 'events'])->name('staff.dentist-schedules.events');
    
    // Dentist leave management
    Route::post('/staff/dentist-leaves', [DentistLeaveController::class, 'store'])->name('staff.dentist-leaves.store');
    Route::delete('/staff/dentist-leaves/{dentistLeave}', [DentistLeaveController::class, 'destroy'])->name('staff.dentist-leaves.destroy');

    // Staff leave management
    Route::get('/staff/leave', [StaffLeaveController::class, 'index'])->name('staff.leave.index');
    Route::get('/staff/leave/create', [StaffLeaveController::class, 'create'])->name('staff.leave.create');
    Route::post('/staff/leave', [StaffLeaveController::class, 'store'])->name('staff.leave.store');
    Route::get('/staff/leave/{staffLeave}/edit', [StaffLeaveController::class, 'edit'])->name('staff.leave.edit');
    Route::patch('/staff/leave/{staffLeave}', [StaffLeaveController::class, 'update'])->name('staff.leave.update');
    Route::delete('/staff/leave/{staffLeave}', [StaffLeaveController::class, 'destroy'])->name('staff.leave.destroy');
    Route::get('/api/staff/leave/calendar', [StaffLeaveController::class, 'getCalendar'])->name('api.staff.leave.calendar');
    Route::get('/api/staff/on-leave', [StaffLeaveController::class, 'getStaffOnLeave'])->name('api.staff.on-leave');
    
    // Staff Calendar
    Route::get('/staff/calendar', [CalendarController::class, 'index'])->name('staff.calendar.index');
    Route::get('/staff/calendar/events', [CalendarController::class, 'events'])->name('staff.calendar.events');
});

// Admin/Developer configuration routes
Route::middleware(['auth', 'role:staff|admin|developer'])->group(function () {
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
    
    // Operating Hours Management
    Route::get('/staff/operating-hours', [OperatingHourController::class, 'index']);
    Route::get('/staff/operating-hours/create', [OperatingHourController::class, 'create']);
    Route::post('/staff/operating-hours', [OperatingHourController::class, 'store']);
    Route::get('/staff/operating-hours/{id}/edit', [OperatingHourController::class, 'edit']);
    Route::put('/staff/operating-hours/{id}', [OperatingHourController::class, 'update']);
    Route::patch('/staff/operating-hours/{id}', [OperatingHourController::class, 'toggleStatus']);
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
    
    // Treatment Room Management (Dynamic Resource Configuration)
    Route::get('/staff/rooms', [RoomController::class, 'index'])->name('staff.rooms.index');
    Route::get('/staff/rooms/create', [RoomController::class, 'create'])->name('staff.rooms.create');
    Route::post('/staff/rooms', [RoomController::class, 'store'])->name('staff.rooms.store');
    Route::get('/staff/rooms/{room}/edit', [RoomController::class, 'edit'])->name('staff.rooms.edit');
    Route::put('/staff/rooms/{room}', [RoomController::class, 'update'])->name('staff.rooms.update');
    Route::delete('/staff/rooms/{room}', [RoomController::class, 'destroy'])->name('staff.rooms.destroy');
    Route::post('/staff/rooms/bulk-status', [RoomController::class, 'bulkToggleStatus'])->name('staff.rooms.bulkToggleStatus');
    
    // Services Management
    Route::get('/staff/services', [ServiceController::class, 'index']);
    Route::get('/staff/services/create', [ServiceController::class, 'create']);
    Route::post('/staff/services', [ServiceController::class, 'store']);
    Route::get('/staff/services/{id}/edit', [ServiceController::class, 'edit']);
    Route::put('/staff/services/{id}', [ServiceController::class, 'update']);
    Route::delete('/staff/services/{id}', [ServiceController::class, 'destroy']);
    Route::post('/staff/services/bulk-delete', [ServiceController::class, 'bulkDestroy']);
    
    // System Configuration
    Route::get('/staff/system-config', [SystemConfigController::class, 'index'])->name('staff.system.config');
});

// Developer Routes (role: admin|developer) - Protected with auth + role middleware
Route::middleware(['auth', 'role:admin|developer'])->group(function () {
    Route::get('/developer/dashboard', [DeveloperDashboardController::class, 'index'])->name('developer.dashboard');
    Route::get('/developer/activity-logs', [DeveloperDashboardController::class, 'activityLogs'])->name('developer.activity-logs');
    Route::get('/developer/activity-logs/{id}', [DeveloperDashboardController::class, 'logDetails'])->name('developer.log-details');
    Route::get('/developer/api-test', [DeveloperDashboardController::class, 'apiTest'])->name('developer.api-test');
    Route::get('/developer/system-info', [DeveloperDashboardController::class, 'systemInfo'])->name('developer.system-info');
    Route::get('/developer/database', [DeveloperDashboardController::class, 'databaseTools'])->name('developer.database');
});

// Patient Report Routes (authenticated patients only)
Route::middleware(['auth'])->group(function () {
    Route::get('/my-reports/appointments', [PatientReportController::class, 'appointmentHistory'])->name('patient.reports.appointments');
    Route::get('/my-reports/treatments', [PatientReportController::class, 'treatmentHistory'])->name('patient.reports.treatments');
    Route::get('/my-reports/feedback', [PatientReportController::class, 'myFeedback'])->name('patient.reports.feedback');
    Route::get('/my-reports/export', [PatientReportController::class, 'exportRecords'])->name('patient.reports.export-records');
    Route::get('/my-reports/export-pdf', [PatientReportController::class, 'exportRecordsPdf'])->name('patient.reports.export-pdf');
});

// Real-time API endpoints for live updates (public)
Route::prefix('api/public')->group(function () {
    // Services
    Route::get('/services', [PublicServiceController::class, 'index']);
    
    // Dentists
    Route::get('/dentists', [PublicDentistController::class, 'index']);
    Route::get('/dentists/available', [PublicDentistController::class, 'available']);
    
    // Operating Hours
    Route::get('/hours', [PublicOperatingHourController::class, 'index']);
    Route::get('/hours/today', [PublicOperatingHourController::class, 'today']);
    
    // Queue Board
    Route::get('/queue-board/data', [PublicQueueBoardController::class, 'data']);
    
    // Booking
    Route::get('/booking/form-data', [PublicBookingController::class, 'formData']);
    Route::post('/booking/submit', [PublicBookingController::class, 'submit']);
    Route::post('/booking/check-in', [PublicBookingController::class, 'checkIn']);
    
    // Tracking
    Route::get('/track/{visit_code}', [PublicTrackingController::class, 'track']);
});

// Legacy API endpoints (for backward compatibility - will be deprecated)
Route::get('/api/track/{code}', [AppointmentController::class, 'trackByCodeApi'])->name('api.track.status');

// Queue Board API - Requires staff authentication
Route::middleware(['auth', 'role:staff|admin|developer'])->group(function () {
    Route::get('/api/queue-board/data', [AppointmentController::class, 'getQueueBoardData']);
    Route::post('/api/queue-board/reassign-dentist', [AppointmentController::class, 'reassignDentist']);
});

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

// System Configuration API endpoints (authenticated, session-based)
Route::middleware(['auth:web'])->group(function () {
    Route::get('/api/dentists/stats', [StaffDentistController::class, 'stats']);
    Route::get('/api/services', [ServiceController::class, 'stats']);
    Route::get('/api/operating-hours', [OperatingHourController::class, 'stats']);
    Route::get('/api/rooms/stats', [RoomController::class, 'stats']);
});