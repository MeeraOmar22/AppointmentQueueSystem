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
}
