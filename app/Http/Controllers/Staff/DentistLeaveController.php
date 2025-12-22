<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\DentistLeave;
use Illuminate\Http\Request;

class DentistLeaveController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dentist_id' => 'required|exists:dentists,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            $leave = DentistLeave::create($validated);
            return response()->json(['success' => true, 'data' => $leave]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(DentistLeave $dentistLeave)
    {
        try {
            $dentistLeave->delete();
            return redirect()->back()->with('success', 'Leave deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting leave: ' . $e->getMessage());
        }
    }
}
