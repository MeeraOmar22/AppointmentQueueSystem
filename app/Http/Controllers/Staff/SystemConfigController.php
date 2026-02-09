<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;

/**
 * System Configuration View Controller
 * 
 * WIRING ONLY - No business logic
 * Returns view for /staff/system-config (admin/developer only)
 */
class SystemConfigController
{
    /**
     * GET /staff/system-config
     * Display System Configuration page (admin/developer only)
     * 
     * The view handles:
     * - Displaying dentist status controls
     * - Displaying service status controls
     * - Displaying room status controls
     * - Quick add buttons for all entities
     * 
     * Business logic is handled by:
     * - StaffDentistController::updateStatus (existing)
     * - StaffServiceController (existing)
     * - RoomController (existing)
     * - QuickEditController (existing)
     * 
     * Access Control:
     * - Requires auth + role:admin OR role:developer
     * - Enforced by middleware
     */
    public function index()
    {
        // Admin/Developer only
        if (!in_array(auth()->user()->role, ['admin', 'developer'])) {
            abort(403, 'Unauthorized');
        }
        
        return view('staff.system-config.index');
    }
}
