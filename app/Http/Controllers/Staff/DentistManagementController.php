<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;

/**
 * Dentist Management View Controller
 * 
 * WIRING ONLY - No business logic
 * Returns view for /staff/dentist-management
 */
class DentistManagementController
{
    /**
     * GET /staff/dentist-management
     * Display Dentist Management page (consolidated dentists + schedules)
     * 
     * The view handles:
     * - Displaying dentist information
     * - Managing dentist leave
     * - Setting break status
     * - Reactivating/deactivating dentists
     * 
     * Business logic is handled by:
     * - StaffDentistController (existing)
     * - DentistLeaveController (existing)
     * - Existing appointment state service
     */
    public function index()
    {
        return view('staff.dentist-management.index');
    }
}
