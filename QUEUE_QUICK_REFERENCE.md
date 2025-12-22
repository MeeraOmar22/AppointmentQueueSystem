# Queue System - Quick Reference Guide

## 🚀 Quick Start (For Developers)

### 1. Patient Checks In

```php
// Via API or form submission
$appointment = Appointment::find($id);
$checkInService = new CheckInService();
$queue = $checkInService->checkIn($appointment);

// Patient is now waiting in queue
```

### 2. Auto-Assign Next Patient (Automatic)

```php
// When dentist finishes treatment
$queue = Queue::find($id);
$assignmentService = new QueueAssignmentService();
$assignmentService->completeTreatment($queue);

// Next patient is AUTOMATICALLY assigned!
// No manual staff action needed!
```

### 3. Get Patient's ETA

```php
$assignmentService = new QueueAssignmentService();
$waitTime = $assignmentService->getEstimatedWaitTime($queue);

echo "Wait time: $waitTime minutes";
```

### 4. Create Walk-In

```php
$lateNoShowService = new LateNoShowService();
$appointment = $lateNoShowService->createWalkIn([
    'patient_name' => 'John',
    'patient_phone' => '0123456789',
    'service_id' => 1,
    'clinic_location' => 'seremban',
]);
```

---

## 🎯 API Quick Reference

### Patient Check-In
```bash
POST /api/check-in
{
    "appointment_id": 5
}
```

### Get Next Patient (Staff)
```bash
GET /api/queue/next?clinic_location=seremban
```

### Update Queue Progress
```bash
PATCH /api/queue/3/status
{
    "action": "start_treatment"
}
```

### Get Room Status
```bash
GET /api/rooms/status?clinic_location=seremban
```

### Get Queue Stats (Dashboard)
```bash
GET /api/queue/stats?clinic_location=seremban
```

### Create Walk-In
```bash
POST /api/walk-in
{
    "patient_name": "John",
    "patient_phone": "0123456789",
    "service_id": 1,
    "clinic_location": "seremban"
}
```

---

## 💡 Key Concepts

### Status Values Matter

**Appointment Status**:
- `booked` - Scheduled, not arrived
- `arrived` - Checked in, in queue
- `in_treatment` - Being treated
- `completed` - Done
- `late` - Checked in after threshold
- `no_show` - Never showed up

**Queue Status**:
- `waiting` - In queue, not assigned
- `called` - Assigned to room/dentist
- `in_treatment` - Currently being treated
- `completed` - Finished

**Room Status**:
- `available` - Free and ready
- `occupied` - Patient in treatment

**Dentist Status**:
- `available` - Ready to treat
- `busy` - Currently treating
- `on_break` - On break
- `off` - Not working

### Queue Numbers Are Sacred

- Created at check-in: `Queue::nextNumberForDate($date)`
- Incremented sequentially
- Never reused for same day
- Determines patient order

---

## ⚡ Common Operations

### Mark Patient as In Treatment
```php
$queue = Queue::find($id);
$assignmentService->startTreatment($queue);
// Now: queue->status = in_treatment, room->status = occupied
```

### Complete Treatment (Auto-assigns next)
```php
$queue = Queue::find($id);
$assignmentService->completeTreatment($queue);
// Now: room->status = available, next patient auto-assigned!
```

### Handle Late Patient
```php
$checkInService = new CheckInService();
if ($checkInService->isLate($appointment)) {
    $queue = $checkInService->checkInLate($appointment);
    // Patient marked late, still in queue
}
```

### Handle No-Show (Automatic)
```php
// Runs on schedule (every 5 mins)
$lateNoShowService = new LateNoShowService();
$marked = $lateNoShowService->markNoShowAppointments(30); // 30 min threshold
// Returns count of patients marked
```

### Handle Dentist Emergency
```php
$lateNoShowService = new LateNoShowService();
$result = $lateNoShowService->handleDentistUnavailable(
    $dentistId,
    'reassign' // or 'pause'
);
// Reassigns all their patients to other dentists
```

---

## 🔍 Debugging

### Check Queue Status
```php
$queue = Queue::with('appointment', 'room', 'dentist')->find($id);

echo "Patient: " . $queue->appointment->patient_name;
echo "Queue #: " . $queue->queue_number;
echo "Status: " . $queue->queue_status;
echo "Room: " . $queue->room?->room_number;
echo "Dentist: " . $queue->dentist?->name;
```

### Check Room Status
```php
$rooms = Room::where('clinic_location', 'seremban')
    ->with('currentPatient.appointment')
    ->get();

foreach ($rooms as $room) {
    echo $room->room_number . ": " . $room->status;
    if ($room->currentPatient) {
        echo " - Patient: " . $room->currentPatient->appointment->patient_name;
    }
}
```

### Get Waiting Patients
```php
$waiting = Queue::where('queue_status', 'waiting')
    ->with('appointment', 'dentist', 'room')
    ->get();

foreach ($waiting as $q) {
    echo "Queue #" . $q->queue_number . ": " . $q->appointment->patient_name;
}
```

---

## 🛠️ Admin Tasks

### Reset Room Statuses (Disaster Recovery)
```php
Room::query()->update(['status' => 'available']);
```

### Reset Dentist Statuses
```php
Dentist::query()->update(['status' => 'available']);
```

### Clear Queue for Day
```php
$today = Carbon::today();
Queue::whereHas('appointment', fn($q) => $q->where('appointment_date', $today))
    ->where('queue_status', '!=', 'completed')
    ->delete();
```

### Generate Rooms (if missing)
```php
Room::create(['room_number' => 'Room 1', 'clinic_location' => 'seremban']);
Room::create(['room_number' => 'Room 2', 'clinic_location' => 'seremban']);
```

---

## 📊 Sample Dashboard Query

```php
// Get today's statistics
$stats = [
    'total' => Appointment::whereDate('appointment_date', today())->count(),
    'checked_in' => Appointment::whereDate('appointment_date', today())
        ->whereIn('status', ['arrived', 'in_treatment', 'completed'])
        ->count(),
    'in_treatment' => Queue::where('queue_status', 'in_treatment')
        ->whereHas('appointment', fn($q) => $q->whereDate('appointment_date', today()))
        ->count(),
    'waiting' => Queue::where('queue_status', 'waiting')
        ->whereHas('appointment', fn($q) => $q->whereDate('appointment_date', today()))
        ->count(),
    'available_rooms' => Room::where('status', 'available')->count(),
];
```

---

## 🐛 Troubleshooting

### "No waiting patients"
- Check appointments have check-in time set
- Verify queue entries exist
- Ensure room/dentist available

### "Multiple rooms occupied but showing complete"
- Check room.status is updated correctly
- Verify room_id is set on queue entry
- Queue.markCompleted() should call room.markAvailable()

### "Patient appears twice in queue"
- Check for duplicate queue entries
- Verify appointment.id uniqueness
- Check queue.appointment_id foreign key

### Estimated wait time wrong
- Verify service.estimated_duration is set
- Check queue order by queue_number
- Ensure completed entries have queue_status = completed

---

## 📝 Notes

- **Always use services** - Don't update queue status directly!
- **Queue is truth** - Appointments estimate; queue shows reality
- **Parallel treatment** - Multiple dentists = multiple rooms occupied, no conflict
- **Auto-progression** - Next patient assigned when treatment completes
- **No manual queuing** - After check-in, queue management is automatic

---

**Version**: 1.0
**Last Updated**: December 22, 2025
