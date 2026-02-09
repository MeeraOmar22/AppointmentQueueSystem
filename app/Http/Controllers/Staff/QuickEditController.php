<?php

namespace App\Http\Controllers\Staff;

use App\Models\Dentist;
use App\Models\Service;
use App\Models\OperatingHour;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class QuickEditController
{
    /**
     * Show quick edit dashboard
     */
    public function index()
    {
        // Admin/Developer only
        if (!in_array(auth()->user()->role, ['admin', 'developer'])) {
            abort(403, 'Unauthorized');
        }
        
        $dentists = Dentist::withCount('appointments')->get();
        $services = Service::withCount('appointments')->get();
        $staff = User::where('role', 'staff')->get();
        
        // Group operating hours by day
        $operatingHours = OperatingHour::orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get()
            ->groupBy('day_of_week');
        
        // Summary stats
        $stats = [
            'dentists' => [
                'total' => $dentists->count(),
                'active' => $dentists->where('status', true)->count(),
                'inactive' => $dentists->where('status', false)->count(),
            ],
            'services' => [
                'total' => $services->count(),
                'active' => $services->where('status', true)->count(),
                'inactive' => $services->where('status', false)->count(),
            ],
            'staff' => [
                'total' => $staff->count(),
                'visible' => $staff->where('public_visible', true)->count(),
                'hidden' => $staff->where('public_visible', false)->count(),
            ],
        ];
        
        return view('staff.quick-edit', [
            'dentists' => $dentists,
            'services' => $services,
            'operatingHours' => $operatingHours,
            'staff' => $staff,
            'stats' => $stats,
        ]);
    }

    /**
     * Quick update staff visibility on public pages
     */
    public function updateStaffVisibility(Request $request, User $user)
    {
        $validated = $request->validate([
            'public_visible' => 'required|boolean',
        ]);

        $old = (bool) ($user->public_visible ?? true);
        $user->update(['public_visible' => $validated['public_visible']]);

        ActivityLogger::log(
            'updated',
            'User',
            $user->id,
            "Changed staff '{$user->name}' visibility from " . ($old ? 'Visible' : 'Hidden') . " to " . ($validated['public_visible'] ? 'Visible' : 'Hidden'),
            ['public_visible' => $old],
            ['public_visible' => $validated['public_visible']]
        );

        return back()->with('success', "Staff '{$user->name}' is now " . ($validated['public_visible'] ? 'Visible on public pages' : 'Hidden from public pages'));
    }

    /**
     * Quick update staff basic info (name, phone, photo)
     */
    public function updateStaffInfo(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $oldValues = $user->only(['name', 'position', 'phone', 'photo']);

        if ($request->hasFile('photo')) {
            if ($user->photo && file_exists(public_path($user->photo))) {
                @unlink(public_path($user->photo));
            }
            $photo = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/users'), $filename);
            $validated['photo'] = 'uploads/users/' . $filename;
        }

        $user->update($validated);

        ActivityLogger::log(
            'updated',
            'User',
            $user->id,
            "Updated staff '{$user->name}' basic info",
            $oldValues,
            $user->only(['name', 'position', 'phone', 'photo'])
        );

        return back()->with('success', "Staff '{$user->name}' updated successfully");
    }

    /**
     * Quick update dentist status
     */
    public function updateDentistStatus(Request $request, Dentist $dentist)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $oldStatus = $dentist->status;
        $dentist->update(['status' => $validated['status']]);

        ActivityLogger::log(
            'updated',
            'Dentist',
            $dentist->id,
            "Changed dentist '{$dentist->name}' status from " . ($oldStatus ? 'Active' : 'Inactive') . " to " . ($validated['status'] ? 'Active' : 'Inactive'),
            ['status' => $oldStatus],
            ['status' => $validated['status']]
        );

        return back()->with('success', "Dentist '{$dentist->name}' status updated to " . ($validated['status'] ? 'Active (visible on website)' : 'Inactive (hidden from website)'));
    }

    /**
     * Quick update service status
     */
    public function updateServiceStatus(Request $request, Service $service)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $oldStatus = $service->status;
        $service->update(['status' => $validated['status']]);

        ActivityLogger::log(
            'updated',
            'Service',
            $service->id,
            "Changed service '{$service->name}' status from " . ($oldStatus ? 'Active' : 'Inactive') . " to " . ($validated['status'] ? 'Active' : 'Inactive'),
            ['status' => $oldStatus],
            ['status' => $validated['status']]
        );

        return back()->with('success', "Service '{$service->name}' status updated to " . ($validated['status'] ? 'Active (visible on website)' : 'Inactive (hidden from website)'));
    }

    /**
     * Quick update operating hour
     */
    public function updateOperatingHour(Request $request, OperatingHour $operatingHour)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'session_label' => 'nullable|string|max:255',
            'start_time' => 'nullable|date_format:H:i|required_unless:is_closed,1',
            'end_time' => 'nullable|date_format:H:i|required_unless:is_closed,1|after:start_time',
            'is_closed' => 'nullable|boolean',
        ]);

        $validated['is_closed'] = $request->boolean('is_closed');

        // Strip time values for closed days to keep DB writes valid
        if ($validated['is_closed']) {
            $validated['start_time'] = null;
            $validated['end_time'] = null;
        }

        $oldValues = $operatingHour->only(['day_of_week', 'session_label', 'start_time', 'end_time', 'is_closed']);
        $operatingHour->update($validated);

        ActivityLogger::log(
            'updated',
            'OperatingHour',
            $operatingHour->id,
            "Updated operating hours for {$operatingHour->day_of_week}",
            $oldValues,
            $validated
        );

        return back()->with('success', "Operating hours for {$operatingHour->day_of_week} updated successfully");
    }

    /**
     * Duplicate an operating hour entry for quick editing
     */
    public function duplicateOperatingHour(OperatingHour $operatingHour)
    {
        $data = $operatingHour->only(['day_of_week', 'session_label', 'start_time', 'end_time', 'is_closed']);
        $copy = OperatingHour::create($data);

        ActivityLogger::log(
            'duplicated',
            'OperatingHour',
            $copy->id,
            "Duplicated operating hours for {$operatingHour->day_of_week}" . ($operatingHour->session_label ? " ({$operatingHour->session_label})" : ''),
            $operatingHour->toArray(),
            $copy->toArray()
        );

        return back()->with('success', "Operating hours duplicated. You can now edit the copied entry.");
    }

    /**
     * Create a new staff user (visible on public pages)
     */
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'public_visible' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'public_visible' => $request->boolean('public_visible', true),
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/users'), $filename);
            $data['photo'] = 'uploads/users/' . $filename;
        }

        $user = User::create($data);

        ActivityLogger::log(
            'created',
            'User',
            $user->id,
            "Created staff '{$user->name}'",
            [],
            $user->only(['name', 'email', 'position', 'phone', 'photo', 'public_visible'])
        );

        return back()->with('success', "Staff '{$user->name}' created successfully");
    }

    /**
     * Delete a staff user
     */
    public function destroyStaff(User $user)
    {
        $name = $user->name;
        
        // Delete photo if exists (only if permanently deleting)
        // For soft delete, we keep the photo in case of restoration
        
        ActivityLogger::log(
            'deleted',
            'User',
            $user->id,
            "Deleted staff '{$name}'",
            $user->only(['name', 'email', 'position', 'phone', 'photo', 'public_visible']),
            []
        );

        // Soft delete instead of hard delete
        $user->delete();

        return back()->with('success', "Staff '{$name}' deleted successfully");
    }
}
