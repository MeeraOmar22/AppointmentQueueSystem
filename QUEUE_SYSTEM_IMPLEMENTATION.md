# Queue Management System - Implementation Roadmap

## Phase 1: Database Schema Enhancements ✅
- [ ] Create `Room` model and migration
- [ ] Update `Appointment` migration with new status values
- [ ] Update `Queue` migration with room_id, dentist_id fields
- [ ] Update `Dentist` migration with status field

## Phase 2: Model Updates ✅
- [ ] Update Appointment model relationships
- [ ] Update Queue model relationships
- [ ] Update Dentist model relationships
- [ ] Create Room model with relationships

## Phase 3: Service Layer (Business Logic) 🔥
- [ ] Create `CheckInService` - handles patient arrival
- [ ] Create `QueueAssignmentService` - auto-assigns rooms/dentists
- [ ] Create `LateNoShowService` - handles edge cases
- [ ] Create `RoomAvailabilityService` - tracks room/dentist status

## Phase 4: Controller Updates
- [ ] Update `CheckInController` to use CheckInService
- [ ] Update `AppointmentController` for new queue logic
- [ ] Create `RoomController` for staff operations
- [ ] Create `QueueTransitionController` for status changes

## Phase 5: API Endpoints
- [ ] POST `/api/check-in` - patient self check-in
- [ ] GET `/api/queue/next` - get next available room/dentist
- [ ] PATCH `/api/queue/{id}/status` - update queue status
- [ ] GET `/api/rooms/status` - real-time room availability

## Phase 6: Views & Frontend
- [ ] Update patient tracking page with live queue number
- [ ] Update staff dashboard with room status
- [ ] Add walk-in creation interface
- [ ] Add late/no-show markers

## Phase 7: Testing & Validation
- [ ] Unit tests for services
- [ ] Feature tests for workflows
- [ ] Edge case testing

---

## Current System State
- ✅ Appointment model exists (needs status update)
- ✅ Queue model exists (needs room_id, dentist_id)
- ✅ Dentist model exists (has status field)
- ❌ Room model missing (MUST CREATE)
- ❌ Service layer missing
- ❌ Queue auto-assignment missing

---

## Key Implementation Details

### Status Values
**Appointment**:
- booked, arrived, in_queue, in_treatment, completed, no_show, cancelled, late

**Queue**:
- waiting, called, in_treatment, completed

**Dentist**:
- available, busy, on_break, off

**Room**:
- available, occupied

---

## Next Step
Start with Phase 1: Create Room model and migrations
