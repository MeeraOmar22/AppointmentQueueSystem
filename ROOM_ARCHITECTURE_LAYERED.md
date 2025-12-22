# Treatment Room Architecture - Layered Approach

## Core Principle: Rooms Are NOT Booked, They Are ASSIGNED

Rooms are **shared operational resources** assigned in real-time when treatment starts, not reserved at booking.

---

## 🧱 Three-Layer Architecture

### Layer 1: BOOKING (Planning Layer)
**What happens:** Patient books an appointment
```
appointment {
  date
  estimated_time
  service_id
  preferred_dentist_id  (optional)
  clinic_location
}
```
**NOT here:**
- ❌ No room assignment
- ❌ No room reservation
- ❌ No capacity checking for rooms

**Why:** Rooms are unpredictable (late patients, overruns, equipment issues)

---

### Layer 2: QUEUE (Control Layer)
**What happens:** Patient arrives and checks in
```
queue {
  appointment_id
  queue_number
  arrival_time
  check_in_time
  queue_status (waiting)
}
```
**NOT here:**
- ❌ Room not assigned yet
- ❌ Dentist not assigned yet
- ✅ Just tracks order in line

**Why:** Waiting is about arrival order, not resources

---

### Layer 3: EXECUTION (Resource Layer)
**What happens:** Treatment actually starts

```
treatment_session {
  appointment_id
  queue_id
  dentist_id        ← Assigned NOW
  room_id           ← Assigned NOW
  start_time
  end_time
  status
}
```

**State Changes:**
```
dentist.status = 'busy'
room.status = 'occupied'
appointment.status = 'in_treatment'
queue.queue_status = 'called'
```

**Why:** Both dentist AND room must be free simultaneously

---

## 🎯 Golden Rule

```
Treatment can START only if:
  dentist.status == 'available' AND room.status == 'available'
```

Real-time system finds BOTH, assigns BOTH, starts treatment.

---

## ⚙️ Real-Time Assignment Engine

### When Patient Reaches Front of Queue

```
1. Check: Is any dentist available?
   SELECT * FROM dentists WHERE status = 'available'
   
2. Check: Is any room available?
   SELECT * FROM rooms WHERE status = 'available'
   
3. If both exist:
   - Assign dentist to patient
   - Assign room to patient
   - Create treatment_session
   - Update states (busy, occupied)
   - Mark queue as 'called'
   
4. If dentist available but NO room:
   - Patient waits
   - Queue status stays 'waiting'
   
5. If room available but NO dentist:
   - Patient waits
   - Queue status stays 'waiting'
```

---

## 📊 Database Schema Alignment

### Appointments Table (Booking Layer)
```sql
appointments {
  id
  patient_name
  service_id
  preferred_dentist_id  ← Preference only, not binding
  appointment_date
  estimated_duration
  clinic_location
  status               ← booked, arrived, in_treatment, completed
  created_at
}
```
**Note:** NO room field - room is not known at booking

---

### Queue Table (Control Layer)
```sql
queues {
  id
  appointment_id
  queue_number
  arrival_time
  check_in_time
  queue_status        ← waiting, called, in_treatment, completed
  called_at
  created_at
}
```
**Note:** NO room/dentist yet - assigned during execution

---

### Treatment Sessions Table (Execution Layer) ⭐ NEW
```sql
treatment_sessions {
  id
  appointment_id
  queue_id
  dentist_id          ← Assigned at execution time
  room_id             ← Assigned at execution time
  start_time          ← When treatment actually begins
  end_time
  status              ← in_progress, completed
  created_at
}
```
**This is the critical table linking queues to actual resources**

---

## 🔄 State Flow: Booking → Queue → Execution

### Example: Patient Arrival at Seremban Clinic

```
TIME 9:00 AM
├─ Patient has appointment (booked yesterday)
│  └─ appointment.status = 'booked'
│  └─ NO room assigned yet ✓
│
TIME 9:05 AM
├─ Patient arrives, checks in
│  └─ queue entry created
│  └─ queue.queue_status = 'waiting'
│  └─ Patient is #3 in queue
│  └─ Still NO room assigned ✓
│
TIME 9:15 AM
├─ Two patients ahead finish treatment
│  └─ Patient is now next
│
TIME 9:16 AM
├─ System checks available resources:
│  ├─ Dentist: Dr. Ahmad (available)
│  ├─ Room: Room 2 (available)
│  └─ ✅ Both free, treatment can start
│
TIME 9:16:30 AM
├─ Treatment session created
│  ├─ treatment_session.dentist_id = 1 (Dr. Ahmad)
│  ├─ treatment_session.room_id = 2 (Room 2)
│  ├─ treatment_session.start_time = NOW
│  └─ Status changes:
│      ├─ dentist.status = 'busy'
│      ├─ room.status = 'occupied'
│      ├─ appointment.status = 'in_treatment'
│      └─ queue.queue_status = 'in_treatment'
│
TIME 9:46 AM
├─ Treatment ends
│  └─ treatment_session.end_time = NOW
│  └─ Status changes:
│      ├─ dentist.status = 'available'
│      ├─ room.status = 'available'
│      ├─ appointment.status = 'completed'
│      └─ queue.queue_status = 'completed'
│
TIME 9:46:15 AM
├─ Next patient auto-assigned (if queue exists)
│  └─ Repeat from TIME 9:16 step
```

---

## 🚨 Edge Cases Handled by Layered Architecture

### Case 1: Patient Arrives Late (+20 min)
```
Booking layer: UNAFFECTED
  - appointment still valid
  - no pre-assigned room to conflict
  
Queue layer: Marks late
  - queue.marked_late = true
  - adjusts position (optional: send to back or keep)
  
Execution layer: Assigns when available
  - Still gets any available dentist + room
  - No cascade failures from pre-booked room
```

### Case 2: Dentist Overruns (+15 min)
```
Booking layer: UNAFFECTED
  - next patient's appointment still valid
  - no pre-assigned room gets freed
  
Queue layer: Patient waits
  - Still first in line
  - Knows dentist is busy
  
Execution layer: Assigns when BOTH free
  - Waits for both dentist + room
  - No parallel room sits empty
```

### Case 3: Room Equipment Failure
```
Booking layer: UNAFFECTED
  - patients' appointments valid
  - never relied on this room
  
Queue layer: UNAFFECTED
  - queue order unchanged
  - doesn't know about rooms yet
  
Execution layer: Skips failed room
  - room.status = 'inactive'
  - Assignment finds next available room
  - Patients continue with remaining rooms
```

### Case 4: Second Dentist Arrives (Peak Demand)
```
Booking layer: UNAFFECTED
  - doesn't care about dentist count
  
Queue layer: UNAFFECTED
  - queue order preserved
  
Execution layer: Better throughput
  - Now 2 dentists available
  - Next assignment has more options
  - Patients treated faster
```

---

## 🎯 Scheduling View (Dentist-Centric)

Staff view shows **dentist schedule** with room as execution detail:

```
Weekly Schedule - Seremban Clinic

Time    | Dr. Ahmad      | Dr. Siti       | Notes
--------|----------------|----------------|--------
9:00    | Azrul (R2)     | -              | Room 2
9:30    | Siti (R1)      | -              | Room 1
10:00   | -              | Ahmad (R2)     | Room 2
10:30   | Kumar (R1)     | -              | Room 1
```

**Key:** 
- Row = Dentist
- Cell = Patient being treated
- (Rx) = Assigned room (execution detail)
- `-` = Free/waiting

---

## ✅ Correct Implementation Checklist

- [ ] Appointments table has NO room_id field
- [ ] Queue table has NO room_id field
- [ ] Treatment_sessions table CREATED with dentist_id, room_id
- [ ] QueueAssignmentService assigns room ONLY during execution
- [ ] Check: if dentist.available AND room.available (both conditions)
- [ ] Set-based queries: `WHERE status = 'available'` (not numeric)
- [ ] Room status updates synchronized with treatment start/end
- [ ] Dentist status updates synchronized with treatment start/end
- [ ] Queue view doesn't show assigned room (not assigned yet)
- [ ] Treatment view shows room and dentist (assigned at execution)

---

## ❌ Anti-Patterns to Avoid

```php
// ❌ WRONG: Assigning room at booking time
appointment.room_id = findRoom();
appointment.save();

// ✅ CORRECT: Assign only during treatment start
treatment_session.room_id = findRoom();
treatment_session.start_time = now();

// ❌ WRONG: Checking room at queue time
if (queue.room_id == null) ...

// ✅ CORRECT: Checking both during execution
if (dentist.available && room.available) ...

// ❌ WRONG: Hard-coded room count
if (rooms_count < 5) ...

// ✅ CORRECT: Set-based availability
available_rooms = Room.where('status', 'available').count()
```

---

## 🔌 Integration Points

### Booking Controller
```php
// Do NOT assign room here
$appointment = Appointment::create([
    'patient_name' => $name,
    'service_id' => $service,
    'appointment_date' => $date,
    // ❌ NO room_id
]);
```

### Queue Check-In Service
```php
// Create queue entry, do NOT assign room yet
$queue = Queue::create([
    'appointment_id' => $appointment->id,
    'queue_number' => $nextNumber,
    'check_in_time' => now(),
    'queue_status' => 'waiting',
    // ❌ NO room_id, NO dentist_id
]);
```

### Treatment Assignment Service (Execution Layer)
```php
// ONLY HERE do you assign room + dentist
$treatmentSession = TreatmentSession::create([
    'appointment_id' => $queue->appointment_id,
    'queue_id' => $queue->id,
    'dentist_id' => $availableDentist->id,  // ← Assigned NOW
    'room_id' => $availableRoom->id,        // ← Assigned NOW
    'start_time' => now(),
]);

// Update status
$dentist->update(['status' => 'busy']);
$room->update(['status' => 'occupied']);
```

---

## 📈 Scalability with Layered Architecture

### Adding Room 3
- Booking layer: ✓ Unaffected
- Queue layer: ✓ Unaffected
- Execution layer: ✓ Automatically available for assignment

### Adding Dr. Siti
- Booking layer: ✓ Unaffected
- Queue layer: ✓ Unaffected
- Execution layer: ✓ Automatically available for assignment

### Removing Room 1 (maintenance)
- Booking layer: ✓ Unaffected
- Queue layer: ✓ Unaffected
- Execution layer: ✓ Assignment uses Room 2-3 instead

---

## Summary: Why This Architecture Matters

| Aspect | Wrong (Room-Booked) | Correct (Room-Assigned) |
|--------|-------------------|------------------------|
| Late patients | Room wasted | Reassigned to next patient |
| Dentist overrun | Cascade delays | Next patient gets available room |
| Room failure | Appointments broken | Patients use remaining rooms |
| Scaling | Need code changes | Add rooms, system adapts |
| Maintainability | Complex logic | Simple: find available + assign |
| Real-world fit | Breaks often | Handles chaos gracefully |

---

**Key Takeaway:**
Rooms belong in the **Execution Layer**, not the Booking Layer.
Queues belong in the **Control Layer**, managing order.
Booking is **Planning**, separated from resource management.

This separation makes the system robust, scalable, and aligned with real-world clinic operations.
