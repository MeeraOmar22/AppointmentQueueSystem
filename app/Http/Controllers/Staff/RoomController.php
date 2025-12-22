<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * Display all rooms for management
     * Filtered by the clinic location this system is deployed to
     */
    public function index()
    {
        $clinicLocation = config('clinic.location', 'seremban');
        
        $rooms = Room::where('clinic_location', $clinicLocation)
            ->orderBy('room_number')
            ->paginate(50);

        // Statistics
        $stats = [
            'total_rooms' => $rooms->total(),
            'available_rooms' => Room::where('clinic_location', $clinicLocation)->where('status', 'available')->count(),
            'occupied_rooms' => Room::where('clinic_location', $clinicLocation)->where('status', 'occupied')->count(),
            'clinic_name' => ucwords(str_replace('_', ' ', $clinicLocation)),
        ];

        return view('staff.rooms.index', [
            'rooms' => $rooms,
            'clinicLocation' => $clinicLocation,
            'stats' => $stats,
        ]);
    }

    /**
     * Show create room form
     */
    public function create()
    {
        $clinicLocation = config('clinic.location', 'seremban');
        $clinicName = ucwords(str_replace('_', ' ', $clinicLocation));
        
        return view('staff.rooms.create', [
            'clinicLocation' => $clinicLocation,
            'clinicName' => $clinicName,
        ]);
    }

    /**
     * Store new room
     */
    public function store(Request $request)
    {
        $clinicLocation = config('clinic.location', 'seremban');
        
        $validated = $request->validate([
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:10',
        ]);

        // Check if room number already exists for this clinic
        $exists = Room::where('room_number', $validated['room_number'])
            ->where('clinic_location', $clinicLocation)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'room_number' => 'Room number already exists for this clinic location.',
            ]);
        }

        $room = Room::create([
            'room_number' => $validated['room_number'],
            'capacity' => $validated['capacity'],
            'status' => 'available',
            'clinic_location' => $clinicLocation,
        ]);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($room)
            ->event('created')
            ->log('Created new treatment room: ' . $room->room_number);

        return redirect('/staff/rooms')
            ->with('success', 'Room created successfully. Room #' . $room->room_number . ' is now available.');
    }

    /**
     * Show edit room form
     */
    public function edit(Room $room)
    {
        $clinicName = ucwords(str_replace('_', ' ', $room->clinic_location));
        
        return view('staff.rooms.edit', [
            'room' => $room,
            'clinicName' => $clinicName,
        ]);
    }

    /**
     * Update room
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:10',
            'status' => 'required|in:available,occupied',
        ]);

        $oldValues = $room->only(['room_number', 'capacity', 'status']);

        $room->update($validated);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($room)
            ->event('updated')
            ->withProperties([
                'old' => $oldValues,
                'new' => $room->only(['room_number', 'capacity', 'status']),
            ])
            ->log('Updated room: ' . $room->room_number);

        return redirect('/staff/rooms')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Delete room
     */
    public function destroy(Room $room)
    {
        // Check if room has active treatment
        $activeQueue = $room->queues()
            ->where('queue_status', '!=', 'completed')
            ->exists();

        if ($activeQueue) {
            return back()->with('error', 'Cannot delete room with active treatment. Please complete treatment first.');
        }

        $roomNumber = $room->room_number;
        $room->delete();

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->event('deleted')
            ->log('Deleted treatment room: ' . $roomNumber);

        return redirect('/staff/rooms')
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Bulk activate/deactivate rooms
     */
    public function bulkToggleStatus(Request $request)
    {
        $validated = $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:rooms,id',
            'status' => 'required|in:available,occupied',
        ]);

        DB::transaction(function () use ($validated) {
            $rooms = Room::whereIn('id', $validated['room_ids'])->get();

            foreach ($rooms as $room) {
                // Don't change status if room is currently occupied and trying to set available
                if ($room->status === 'occupied' && $validated['status'] === 'available') {
                    // Skip - room is in use
                    continue;
                }

                if ($room->status !== $validated['status']) {
                    $room->update(['status' => $validated['status']]);

                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($room)
                        ->event('updated')
                        ->log('Room status changed to: ' . $validated['status']);
                }
            }
        });

        return back()->with('success', 'Room statuses updated successfully.');
    }

    /**
     * Get room statistics API endpoint
     */
    public function stats(Request $request)
    {
        $clinicLocation = config('clinic.location', 'seremban');

        $rooms = Room::where('clinic_location', $clinicLocation)->get();

        return response()->json([
            'success' => true,
            'clinic_location' => $clinicLocation,
            'total' => $rooms->count(),
            'available' => $rooms->where('status', 'available')->count(),
            'occupied' => $rooms->where('status', 'occupied')->count(),
            'rooms' => $rooms->map(function (Room $room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'status' => $room->status,
                    'capacity' => $room->capacity,
                    'current_patient' => $room->currentPatient?->appointment?->patient_name,
                ];
            }),
        ]);
    }
}
