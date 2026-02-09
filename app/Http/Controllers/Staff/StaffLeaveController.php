<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\StaffLeave;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffLeaveController extends Controller
{
    /**
     * Display staff leave management page
     */
    public function index(Request $request)
    {
        $leaves = StaffLeave::with('user')
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        $upcomingLeaves = StaffLeave::where('start_date', '>=', Carbon::today())
            ->with('user')
            ->orderBy('start_date', 'asc')
            ->get();

        $staff = User::whereIn('role', ['staff', 'admin'])
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return view('staff.leave.index', [
            'leaves' => $leaves,
            'upcomingLeaves' => $upcomingLeaves,
            'staff' => $staff,
        ]);
    }

    /**
     * Show create leave form
     */
    public function create()
    {
        $staff = User::whereIn('role', ['staff', 'admin'])
            ->orderBy('name')
            ->get();

        return view('staff.leave.create', [
            'staff' => $staff,
        ]);
    }

    /**
     * Store new leave record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $leave = StaffLeave::create($validated);

        ActivityLogger::log(
            'staff_leave_created',
            'StaffLeave',
            $leave->id,
            "Staff leave created for {$leave->user->name} from {$leave->start_date->format('Y-m-d')} to {$leave->end_date->format('Y-m-d')}",
            [],
            ['user_id' => $leave->user_id, 'reason' => $leave->reason]
        );

        return redirect()->route('staff.leave.index')
            ->with('success', "Leave created for {$leave->user->name}");
    }

    /**
     * Show edit leave form
     */
    public function edit(StaffLeave $leave)
    {
        $staff = User::whereIn('role', ['staff', 'admin'])
            ->orderBy('name')
            ->get();

        return view('staff.leave.edit', [
            'leave' => $leave,
            'staff' => $staff,
        ]);
    }

    /**
     * Update leave record
     */
    public function update(Request $request, StaffLeave $leave)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $oldData = $leave->toArray();
        $leave->update($validated);

        ActivityLogger::log(
            'staff_leave_updated',
            'StaffLeave',
            $leave->id,
            "Staff leave updated for {$leave->user->name}",
            $oldData,
            $leave->toArray()
        );

        return redirect()->route('staff.leave.index')
            ->with('success', "Leave updated for {$leave->user->name}");
    }

    /**
     * Delete leave record
     */
    public function destroy(StaffLeave $leave)
    {
        $staffName = $leave->user->name;
        $leave->delete();

        ActivityLogger::log(
            'staff_leave_deleted',
            'StaffLeave',
            $leave->id,
            "Staff leave deleted for {$staffName}",
            ['user_id' => $leave->user_id],
            []
        );

        return redirect()->route('staff.leave.index')
            ->with('success', "Leave deleted for {$staffName}");
    }

    /**
     * Get staff members on leave for a specific date (AJAX)
     */
    public function getStaffOnLeave(Request $request)
    {
        $date = $request->validate(['date' => 'required|date_format:Y-m-d'])['date'];

        $staffOnLeave = StaffLeave::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->with('user')
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->user_id,
                    'name' => $leave->user->name,
                    'role' => $leave->user->role,
                    'reason' => $leave->reason,
                    'return_date' => $leave->end_date->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'date' => $date,
            'staff_on_leave' => $staffOnLeave,
        ]);
    }

    /**
     * Get dentist availability calendar for a month
     */
    public function getCalendar(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $leaves = StaffLeave::where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->with('user')
            ->get();

        $calendar = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $staffOnLeave = $leaves->filter(function ($leave) use ($dateStr) {
                return $leave->start_date->format('Y-m-d') <= $dateStr && $leave->end_date->format('Y-m-d') >= $dateStr;
            })->map(fn($l) => $l->user->name)->toArray();

            if (!empty($staffOnLeave)) {
                $calendar[$dateStr] = $staffOnLeave;
            }
        }

        return response()->json([
            'success' => true,
            'month' => $month,
            'calendar' => $calendar,
        ]);
    }
}
