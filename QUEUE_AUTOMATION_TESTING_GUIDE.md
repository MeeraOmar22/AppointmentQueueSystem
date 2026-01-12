# Queue Automation System - Testing Guide

## ✅ Pre-Testing Checklist

- [x] Migration run: `php artisan migrate` ✅ SUCCESS
- [x] Routes added to `routes/web.php` ✅
- [x] Controller methods implemented ✅
- [x] Views created/updated ✅
- [x] Database tables created ✅
- [x] Treatment rooms auto-created (Room 1, 2, 3) ✅

---

## 🧪 Test Scenarios

### **Test 1: Basic Patient Flow (No Pause)**

**Objective**: Verify automatic queue progression without pausing

**Setup**:
1. Open two windows:
   - Window A: `http://localhost:8000/staff/treatment-completion` (Dentist)
   - Window B: `http://localhost:8000/public/waiting-area` (TV Display)

2. Create/book 3 appointments for today:
   - Patient A: 09:00 AM
   - Patient B: 09:30 AM
   - Patient C: 10:00 AM

**Test Steps**:

```
STEP 1: Patient A Checks In
├─ Go to public check-in page or API
├─ Patient A checks in
├─ Expected: Window A shows "Checked In" (blue badge)
└─ Expected: Patient A added to "Appointments" table

STEP 2: Verify Auto-Call
├─ Expected: Window A shows "Next Patient" section
├─ Expected: Patient A status changed to "called" (red)
├─ Expected: Window B (TV) shows Patient A with queue number
└─ Check logs: WhatsApp notification sent?

STEP 3: Dentist Treats Patient A
├─ In Window A: Click [✓ Complete] button for Patient A
├─ Modal appears: "Assign Treatment Room"
├─ Select "Room 1" from dropdown
├─ Click "Mark Completed"
└─ Expected: Page refreshes, Patient A status = "completed" (green)

STEP 4: Verify Auto-Call Next Patient
├─ Expected: Patient B status changed to "called" automatically
├─ Expected: Patient B appears in "Next Patient" section
├─ Expected: Window B (TV) shows Patient B
├─ Expected: Activity log shows "patient_called" entry
└─ Check logs: WhatsApp sent to Patient B?

STEP 5: Patient B Checks In
├─ Patient B checks in
├─ Expected: Patient B appears in appointments table
├─ Expected: Status shows "checked_in" (blue)

STEP 6: Repeat for Patient B
├─ Click [✓ Complete] for Patient B
├─ Select Room 2
├─ Verify Patient C auto-called

STEP 7: All Done
├─ All patients completed
├─ Window A shows "Queue is clear"
├─ Window B shows empty state
```

**Pass Criteria**:
- ✅ Each patient auto-called when previous completes
- ✅ All WhatsApp messages sent
- ✅ TV display updates with current patient
- ✅ Activity log records all transitions

---

### **Test 2: Pause/Resume Functionality**

**Objective**: Verify queue can be paused and resumed

**Setup**: Continue from Test 1 or start fresh with 4 patients

**Test Steps**:

```
STEP 1: Pause Queue During Treatment
├─ Patient A is in_treatment
├─ In Window A: Click [⏸ PAUSE QUEUE] button
├─ Confirmation dialog: "Pause Queue? New patients will NOT..."
├─ Click OK
└─ Expected: Button changes to [🟢 RESUME QUEUE]
           Status badge changes to "⏸ PAUSED" (red)

STEP 2: Complete Current Patient While Paused
├─ Dentist finishes Patient A treatment
├─ Click [✓ Complete] button
├─ Select Room 1
├─ Click "Mark Completed"
├─ Expected: Patient A marked as completed ✓
└─ Expected: Patient B NOT auto-called (still "checked_in")

STEP 3: Verify No Auto-Call
├─ Window A: Patient B should still show status "Checked In" (blue)
├─ Window B: TV should still show previous patient or empty
├─ Window A: Activity log shows "treatment_completed" but NO "patient_called"

STEP 4: Resume Queue
├─ In Window A: Click [🟢 RESUME QUEUE] button
├─ Expected: Button changes back to [⏸ PAUSE QUEUE]
           Status badge changes to "🟢 RUNNING" (green)
└─ Expected: Patient B status immediately changes to "called" (red)

STEP 5: Verify Auto-Call Resumed
├─ Window A: Patient B should show "called" status (red)
├─ Window B: TV updates to show Patient B with queue number
├─ Activity log: Shows "queue_resumed" and "patient_called" entries
└─ Check logs: WhatsApp sent to Patient B

STEP 6: Verify Normal Flow Continues
├─ Patient B proceeds as normal
├─ Dentist completes Patient B
├─ Patient C auto-called (queue not paused)
```

**Pass Criteria**:
- ✅ Queue pauses correctly - no auto-calls
- ✅ Current patient finishes while paused
- ✅ Resume auto-calls next patient
- ✅ Normal flow continues after resume
- ✅ Activity log records pause/resume

---

### **Test 3: Room Assignment**

**Objective**: Verify room assignment functionality

**Setup**: 1 or more appointments for today

**Test Steps**:

```
STEP 1: Complete Treatment with Room Assignment
├─ Go to treatment-completion page
├─ Click [✓ Complete] for any in_treatment patient
├─ Modal opens: "Assign Treatment Room"
├─ Dropdown shows:
│   - No Room Assignment
│   - Room 1 - Treatment Room 1
│   - Room 2 - Treatment Room 2
│   - Room 3 - Treatment Room 3
├─ Select "Room 2"
├─ Click "Mark Completed"
└─ Expected: Patient marked as completed

STEP 2: Verify Room Stored in Database
├─ Check database: SELECT * FROM queues WHERE appointment_id = X;
├─ Expected: treatment_room_id = 2 (Room ID)
├─ Expected: queue_status = "completed"

STEP 3: Check Patient Record in Table
├─ In appointments table, find the completed patient
├─ Room column should show: "Room 2" badge (blue)
├─ Expected: Room display matches selection

STEP 4: Verify Room in WhatsApp Message
├─ When next patient is called, check WhatsApp sent
├─ Expected message: "Your turn! Please proceed to Room 2. Thank you!"
├─ Or (if no room): "Your turn! Please proceed to Waiting Area. Thank you!"

STEP 5: Check TV Display
├─ In Window B (TV), look at current patient
├─ Expected: Shows "📍 Room 2" or assigned room
├─ Expected: Large display for easy reading
```

**Pass Criteria**:
- ✅ Room dropdown works and saves
- ✅ Room displayed in table
- ✅ Room sent in WhatsApp message
- ✅ TV display shows room number
- ✅ Room can be skipped (no assignment)

---

### **Test 4: TV Display Updates**

**Objective**: Verify waiting area TV display auto-updates

**Setup**: Multiple appointments for today

**Test Steps**:

```
STEP 1: Open TV Display
├─ Open new window/browser tab
├─ Navigate to: http://localhost:8000/public/waiting-area
├─ Expected: Full-screen display (no navigation)
           Large fonts, purple gradient background
           "Welcome to Our Clinic" header

STEP 2: Verify Current Patient Display
├─ Dentist desk has patient in treatment
├─ TV should show:
│   ✓ "🔴 NOW BEING CALLED" section (pulsing)
│   ✓ Large queue number (e.g., #001)
│   ✓ Patient name
│   ✓ Service type
│   ✓ Room assignment (e.g., "📍 Room 1")

STEP 3: Auto-Refresh Every 3 Seconds
├─ Perform an action on dentist desk (e.g., click Complete)
├─ Wait 3 seconds
├─ TV should update without page reload
├─ Expected: Display shows new current patient
           Next patient changed
           Waiting count updated

STEP 4: Verify Next Patient Display
├─ TV should show: "⏳ PATIENTS WAITING (3)"
├─ List should show waiting patients:
│   - Status badge: "Waiting" (yellow)
│   - Queue number
│   - Patient name

STEP 5: Pause Queue on Dentist Desk
├─ In dentist window: Click [⏸ PAUSE QUEUE]
├─ Wait 3 seconds for TV to update
├─ Expected: TV shows "⏸ Queue is Currently Paused" alert
           Yellow background, warning icon
           Clear indication to patients

STEP 6: Resume Queue
├─ In dentist window: Click [🟢 RESUME QUEUE]
├─ Wait 3 seconds for TV to update
├─ Expected: Pause alert disappears
           Back to normal display
           Next patient shown

STEP 7: Empty State Display
├─ Mark all patients as completed
├─ Wait 3 seconds for TV update
├─ Expected: "No patients waiting" message
           Checkmark icon
           "Queue is clear" text
```

**Pass Criteria**:
- ✅ TV updates every 3 seconds
- ✅ Current patient displayed correctly
- ✅ Room number shown
- ✅ Queue number prominent
- ✅ Pause status visible
- ✅ Next patients list accurate
- ✅ Full-screen, no navigation visible
- ✅ Large fonts readable from distance

---

### **Test 5: WhatsApp Notifications**

**Objective**: Verify WhatsApp messages sent correctly

**Prerequisites**: Valid WhatsApp credentials configured in `.env`

**Setup**: Appointment for today with patient phone number

**Test Steps**:

```
STEP 1: Complete Treatment (Auto-Call)
├─ In dentist window: Click [✓ Complete] for a patient
├─ Select room or skip
├─ Click "Mark Completed"
├─ Expected: System calls callNextPatient()

STEP 2: Check WhatsApp Logs
├─ Method 1 (Laravel logs):
│   tail -f storage/logs/laravel.log
│   Look for: "Patient X automatically called"
│
├─ Method 2 (Activity logs in dashboard):
│   Go to: /staff/activity-logs
│   Look for: "patient_called" entries
│
└─ Method 3 (Database check):
    SELECT * FROM activity_logs 
    WHERE action = 'patient_called' 
    ORDER BY created_at DESC LIMIT 5;

STEP 3: Check Message Format
├─ Expected message content:
│   "Your turn! Please proceed to [Room Code]. Thank you!"
│   Examples:
│   - "Your turn! Please proceed to Room 1. Thank you!"
│   - "Your turn! Please proceed to Room 2. Thank you!"
│   - "Your turn! Please proceed to Waiting Area. Thank you!"

STEP 4: Verify Recipient
├─ Message sent to patient's phone number
├─ Format: E.164 (e.g., +60123456789)
├─ Confirm in WhatsApp Cloud API logs

STEP 5: Test with Actual Phone (Optional)
├─ If phone numbers in database are real:
│   Check inbox for actual WhatsApp messages
│   Verify message received within 5 seconds
│   Confirm room information is correct
```

**Pass Criteria**:
- ✅ WhatsApp message sent to correct phone
- ✅ Message format correct
- ✅ Room information accurate
- ✅ Message sent within 5 seconds
- ✅ Activity logged correctly
- ✅ No errors in logs

---

### **Test 6: Activity Logging**

**Objective**: Verify all actions logged to database

**Setup**: Complete several patient treatments

**Test Steps**:

```
STEP 1: Access Activity Logs
├─ Go to: http://localhost:8000/staff/activity-logs
├─ Expected: List of all recent actions

STEP 2: Find Queue-Related Logs
├─ Look for entries with action:
│   - "patient_called" (when patient auto-called)
│   - "treatment_completed" (when dentist marks complete)
│   - "queue_paused" (when dentist pauses queue)
│   - "queue_resumed" (when dentist resumes queue)

STEP 3: Check Log Details
├─ For each log entry, verify:
│   ✓ Action name
│   ✓ Related entity (Appointment or Queue)
│   ✓ Timestamp (current time)
│   ✓ Additional data (dentist name, patient name, etc.)

STEP 4: Database Query (Direct)
├─ Run command:
│   SELECT action, description, additional_data, created_at 
│   FROM activity_logs 
│   WHERE action LIKE '%queue%' OR action LIKE '%treatment%'
│   ORDER BY created_at DESC 
│   LIMIT 20;
│
├─ Expected results:
│   - Rows exist for all actions
│   - Timestamps are accurate
│   - Data includes relevant info (dentist, patient, room, etc.)

STEP 5: Filter by Action Type
├─ In UI, filter logs by action type (if feature exists)
├─ Or use database:
│   SELECT * FROM activity_logs 
│   WHERE action = 'patient_called' 
│   AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
│
└─ Expected: All auto-call actions visible
```

**Pass Criteria**:
- ✅ All actions logged to database
- ✅ Timestamps accurate
- ✅ Correct staff member recorded
- ✅ Patient/room info included
- ✅ Logs visible in UI

---

## 🔍 Database Verification Queries

### Verify New Tables Created

```sql
-- Check queue_settings table
SELECT * FROM queue_settings;
-- Expected: 1 row with is_paused = 0

-- Check treatment_rooms table
SELECT * FROM treatment_rooms WHERE is_active = 1;
-- Expected: Room 1, Room 2, Room 3

-- Check queues table for new columns
DESCRIBE queues;
-- Expected: treatment_room_id, called_at columns exist
-- Expected: queue_status ENUM includes 'called'
```

### Verify Status Changes

```sql
-- Check queue status for all today's patients
SELECT 
    a.patient_name,
    a.appointment_time,
    q.queue_number,
    q.queue_status,
    q.treatment_room_id,
    q.called_at
FROM appointments a
LEFT JOIN queues q ON a.id = q.appointment_id
WHERE DATE(a.appointment_date) = CURDATE()
ORDER BY a.appointment_time;
```

### Verify Room Assignment

```sql
-- Check patients with room assignments
SELECT 
    a.patient_name,
    q.queue_number,
    q.queue_status,
    tr.room_code
FROM appointments a
LEFT JOIN queues q ON a.id = q.appointment_id
LEFT JOIN treatment_rooms tr ON q.treatment_room_id = tr.id
WHERE DATE(a.appointment_date) = CURDATE()
  AND q.treatment_room_id IS NOT NULL;
```

---

## 📋 Checklist for Full System Test

- [ ] **Navigation**
  - [ ] `/staff/treatment-completion` loads
  - [ ] `/public/waiting-area` loads
  - [ ] Navbar link "Treatment Completion" works
  
- [ ] **Patient Flow**
  - [ ] Patient checks in → status "checked_in"
  - [ ] System auto-calls → status "called"
  - [ ] Dentist completes → status "completed"
  - [ ] Next patient auto-called
  
- [ ] **Pause/Resume**
  - [ ] Pause button works
  - [ ] Queue stops auto-calling while paused
  - [ ] Resume button works
  - [ ] Auto-calling restarts
  
- [ ] **Room Assignment**
  - [ ] Room dropdown appears in modal
  - [ ] Room selection saved
  - [ ] Room displayed in table
  - [ ] Room sent in WhatsApp
  
- [ ] **TV Display**
  - [ ] Full-screen display
  - [ ] Shows current patient
  - [ ] Shows next patients
  - [ ] Auto-updates every 3 seconds
  - [ ] Shows pause status
  
- [ ] **WhatsApp**
  - [ ] Messages sent when patient called
  - [ ] Room info in message
  - [ ] Activity log records sends
  
- [ ] **Activity Logging**
  - [ ] All actions logged
  - [ ] Logs visible in dashboard
  - [ ] Database queries work

---

## 🚨 Troubleshooting

### Issue: "Queue not found" error

**Solution**:
```
1. Ensure patient has appointment created
2. Appointment must have corresponding queue entry
3. Check: SELECT * FROM queues WHERE appointment_id = X;
```

### Issue: WhatsApp not sending

**Solution**:
```
1. Check .env: WHATSAPP_TOKEN, WHATSAPP_PHONE_ID, WHATSAPP_DEFAULT_RECIPIENT
2. Check logs: tail -f storage/logs/laravel.log
3. Test manually: 
   php artisan tinker
   > App\Services\WhatsAppSender::sendMessage('+60123456789', 'Test')
```

### Issue: TV display not updating

**Solution**:
```
1. Clear browser cache (Ctrl+F5)
2. Check browser console for errors
3. Verify API endpoint works: /api/queue/status (should return JSON)
4. Check network tab for failed requests
```

### Issue: Pause button not working

**Solution**:
```
1. Check CSRF token in page
2. Verify JavaScript console for errors
3. Check if queue_settings table exists:
   SELECT * FROM queue_settings;
4. Manually test:
   UPDATE queue_settings SET is_paused = 1;
```

---

## 📊 Performance Checks

```
METRIC                      EXPECTED
──────────────────────────────────────
TV display refresh:         < 3 seconds
WhatsApp send time:         < 5 seconds
Complete treatment action:  < 2 seconds
Pause/Resume action:        < 1 second
Auto-call detection:        < 2 seconds
Page load:                  < 2 seconds
```

---

## ✅ Sign-Off Checklist

When all tests pass:

- [ ] Dentist can view treatment completion page
- [ ] Patients auto-progress through statuses
- [ ] Pause/resume works correctly
- [ ] Room assignment works
- [ ] TV display updates automatically
- [ ] WhatsApp messages sent
- [ ] Activity logs recorded
- [ ] No errors in logs
- [ ] Database queries verified
- [ ] UI is responsive and clear

---

**Testing Status**: Ready for QA
**Date**: January 13, 2026
**Version**: 1.0

