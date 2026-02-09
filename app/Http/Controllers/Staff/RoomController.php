<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\RoomManagementService;
use App\Services\RoomService;
use App\Services\ExceptionAlertService;

class RoomController extends Controller
{
    public function __construct(
        private RoomManagementService $roomService,
        private RoomService $roomSvc,
    ) {
        // Authorization: Only staff, admin, or developer can manage treatment rooms
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['staff', 'admin', 'developer'])) {
                abort(403, 'Unauthorized to manage treatment rooms');
            }
            return $next($request);
        });
    }

    /**
     * Display all rooms for management
     * Shows rooms with their current status and patient assignments
     */
    public function index()
    {
        $clinicLocation = config('clinic.location', 'seremban');
        
        $rooms = Room::where('clinic_location', $clinicLocation)
            ->with([
                'queues' => fn($q) => $q->where('queue_status', 'in_treatment')
                    ->with(['appointment.service', 'appointment.dentist'])
            ])
            ->orderBy('room_number')
            ->paginate(50);

        $stats = $this->roomService->getRoomStatistics($clinicLocation);

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
            'room_number' => 'required|string|max:50|unique:rooms,room_number,NULL,id,clinic_location,' . $clinicLocation,
            'capacity' => 'required|integer|min:1|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        try {
            $room = $this->roomSvc->createRoom(
                array_merge($validated, ['is_active' => $request->boolean('is_active', true)]),
                $clinicLocation
            );

            return redirect('/staff/rooms')
                ->with('success', 'Room created successfully. Room #' . $room->room_number . ' is now available.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['room_number' => $e->getMessage()]);
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create room: ' . $e->getMessage()]);
        }
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
     * 
     * FIX #10: Prevent deactivating rooms that have active treatments
     * Prevents breaking workflow by disabling room mid-treatment
     */
    public function update(Request $request, Room $room)
    {
        // For AJAX requests with only is_active, allow partial update
        if ($request->expectsJson() && $request->has('is_active') && !$request->has('room_number')) {
            $validated = $request->validate([
                'is_active' => 'required|boolean',
            ]);
            
            // FIX #10: If trying to deactivate, check for active treatments
            if (!$validated['is_active'] && $room->is_active) {
                $activePatients = \App\Models\Queue::where('room_id', $room->id)
                    ->where('queue_status', 'in_treatment')
                    ->count();
                
                if ($activePatients > 0) {
                    return response()->json([
                        'error' => "Cannot deactivate {$room->room_number}: Currently treating {$activePatients} patient(s). Room must complete treatment first.",
                        'active_patients' => $activePatients,
                    ], 422);
                }
            }
            
            $oldValues = $room->only(['is_active']);
            $wasActive = $room->is_active;
            $room->update($validated);
            $roomAfter = $room->fresh();
            
            ActivityLogger::log(
                'updated',
                'Room',
                $room->id,
                'Updated room status: ' . $room->room_number,
                $oldValues,
                $roomAfter->only(['is_active'])
            );
            
            if ($wasActive && !$roomAfter->is_active) {
                ExceptionAlertService::roomDisabled($roomAfter, $wasActive);
            }
            
            return response()->json(['message' => 'Room updated successfully.']);
        }
        
        // Full update validation
        $validated = $request->validate([
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1|max:10',
            'status' => 'required|in:available,occupied',
            'is_active' => 'required|boolean',
        ]);

        $oldValues = $room->only(['room_number', 'capacity', 'status', 'is_active']);

        $wasActive = $room->is_active;
        $room->update($validated);
        $roomAfter = $room->fresh();

        // Log activity
        ActivityLogger::log(
            'updated',
            'Room',
            $room->id,
            'Updated room: ' . $room->room_number,
            $oldValues,
            $roomAfter->toArray()
        );

        if ($wasActive && !$roomAfter->is_active) {
            ExceptionAlertService::roomDisabled($roomAfter, $wasActive);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Room updated successfully.']);
        }

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
        ActivityLogger::log(
            'deleted',
            'Room',
            null,
            'Deleted treatment room: ' . $roomNumber,
            ['room_number' => $roomNumber],
            null
        );

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

                    ActivityLogger::log(
                        'updated',
                        'Room',
                        $room->id,
                        'Room status changed to: ' . $validated['status'],
                        ['status' => $room->getOriginal('status')],
                        ['status' => $validated['status']]
                    );
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
            'data' => $rooms->map(function (Room $room) {
                return [
                    'id' => $room->id,
                    'name' => $room->room_number,
                    'type' => 'Treatment Room',
                    'status' => $room->is_active ? true : false,
                    'capacity' => $room->capacity,
                ];
            }),
        ]);
    }
}
