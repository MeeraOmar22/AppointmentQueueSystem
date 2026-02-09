<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\OperatingHour;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class OperatingHourController extends Controller
{
    public function index()
    {
        $hours = OperatingHour::orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');
        
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        return view('staff.operating-hours.index', compact('hours', 'daysOfWeek'));
    }

    public function create()
    {
        return view('staff.operating-hours.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'session_label' => 'nullable|string',
            'is_closed' => 'nullable|boolean',
            'start_time' => 'nullable|date_format:H:i|required_unless:is_closed,1',
            'end_time' => 'nullable|date_format:H:i|required_unless:is_closed,1|after:start_time',
        ]);

        $data['is_closed'] = $request->has('is_closed') ? true : false;

        // If the clinic is closed on this entry, drop time fields to avoid nullability issues
        if ($data['is_closed']) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        $hour = OperatingHour::create($data);

        ActivityLogger::log('created', 'OperatingHour', $hour->id, "Created operating hours for {$data['day_of_week']}", null, $hour->toArray());

        return redirect('/staff/operating-hours')->with('success', 'Operating hour added successfully.');
    }

    public function edit($id)
    {
        $hour = OperatingHour::findOrFail($id);
        return view('staff.operating-hours.edit', compact('hour'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'day_of_week' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'session_label' => 'nullable|string',
            'is_closed' => 'nullable|boolean',
            'start_time' => 'nullable|date_format:H:i|required_unless:is_closed,1',
            'end_time' => 'nullable|date_format:H:i|required_unless:is_closed,1|after:start_time',
        ]);

        $data['is_closed'] = $request->has('is_closed') ? true : false;

        // Remove time values when marked closed so DB allows nulls cleanly
        if ($data['is_closed']) {
            $data['start_time'] = null;
            $data['end_time'] = null;
        }

        $hour = OperatingHour::findOrFail($id);
        
        // FIX #13: Warn admin if closing a day that has existing appointments
        if ($data['is_closed'] && !$hour->is_closed) {
            // Check if there are any future appointments on this day
            $dayName = $data['day_of_week'];
            $futureAppointments = \App\Models\Appointment::whereRaw("DAYNAME(appointment_date) = ?", [$dayName])
                ->where('appointment_date', '>=', \Carbon\Carbon::today())
                ->count();
            
            if ($futureAppointments > 0) {
                return redirect()->back()
                    ->withInput()
                    ->with('warning', "Warning: There are {$futureAppointments} existing appointments on {$dayName}s. Closing this day may cause scheduling conflicts. Please review appointments first.");
            }
        }
        
        // FIX #13: Warn if significantly changing operating hours (may affect existing appointments)
        if (!$data['is_closed'] && !$hour->is_closed) {
            if ($hour->start_time !== $data['start_time'] || $hour->end_time !== $data['end_time']) {
                $affectedCount = \App\Models\Appointment::whereRaw("DAYNAME(appointment_date) = ?", [$data['day_of_week']])
                    ->where('appointment_date', '>=', \Carbon\Carbon::today())
                    ->count();
                
                if ($affectedCount > 0) {
                    logger()->warning('Operating hours updated - may affect appointments', [
                        'day' => $data['day_of_week'],
                        'old_time' => $hour->start_time . ' - ' . $hour->end_time,
                        'new_time' => $data['start_time'] . ' - ' . $data['end_time'],
                        'affected_appointments' => $affectedCount,
                    ]);
                }
            }
        }
        
        $oldValues = $hour->toArray();
        $hour->update($data);

        ActivityLogger::log('updated', 'OperatingHour', $hour->id, "Updated operating hours for {$hour->day_of_week}", $oldValues, $hour->fresh()->toArray());

        return redirect('/staff/operating-hours')->with('success', 'Operating hour updated successfully.');
    }

    public function destroy($id)
    {
        $hour = OperatingHour::findOrFail($id);
        $dayOfWeek = $hour->day_of_week;
        
        ActivityLogger::log('deleted', 'OperatingHour', $id, "Deleted operating hours for {$dayOfWeek}", $hour->toArray(), null);
        
        $hour->delete();

        return redirect('/staff/operating-hours')->with('success', 'Operating hour deleted successfully.');
    }

    /**
     * Toggle is_closed status via PATCH (for quick-toggle in system config)
     */
    public function toggleStatus(Request $request, $id)
    {
        $hour = OperatingHour::findOrFail($id);
        
        $isClosed = $request->input('is_closed', false);
        $oldValue = $hour->is_closed;
        
        $hour->is_closed = (bool) $isClosed;
        $hour->save();

        ActivityLogger::log('updated', 'OperatingHour', $hour->id, "Toggled is_closed status for {$hour->day_of_week}", ['is_closed' => $oldValue], ['is_closed' => $isClosed]);

        return response()->json([
            'success' => true,
            'message' => 'Operating hour status updated',
            'data' => [
                'id' => $hour->id,
                'is_closed' => $hour->is_closed,
            ]
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:operating_hours,id',
        ]);

        ActivityLogger::log('deleted', 'OperatingHour', null, "Bulk deleted " . count($ids['ids']) . " operating hours", ['ids' => $ids['ids']], null);

        OperatingHour::whereIn('id', $ids['ids'])->delete();

        return redirect('/staff/operating-hours')->with('success', count($ids['ids']) . ' operating hours deleted successfully.');
    }

    public function stats()
    {
        $hours = OperatingHour::all();

        return response()->json([
            'data' => $hours->map(function (OperatingHour $hour) {
                return [
                    'id' => $hour->id,
                    'day_of_week' => $hour->day_of_week,
                    'session_label' => $hour->session_label ?? '',
                    'opening_time' => $hour->start_time,
                    'closing_time' => $hour->end_time,
                    'status' => $hour->is_closed ? 'closed' : 'open',
                    'is_active' => !$hour->is_closed,
                ];
            }),
        ]);
    }
}
