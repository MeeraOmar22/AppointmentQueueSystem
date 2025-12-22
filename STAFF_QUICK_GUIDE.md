# Dynamic Resource Configuration - Staff Quick Guide

## Getting Started (5 Minutes)

### For Staff Who Want to Add/Configure Rooms

#### 1. Access Room Management
```
Dashboard → Treatment Room Management
URL: http://localhost:8000/staff/rooms
```

#### 2. View Current Rooms
You'll see:
- List of all treatment rooms
- Status (Available / In Use)
- Current patient (if any)
- Statistics cards

#### 3. Add New Room

**When:** New clinic space opens, expansion, or additional treatment area

**Steps:**
1. Click **[Add New Room]** button
2. Fill in:
   - **Room Number:** e.g., "Room 4", "Surgery Suite A"
   - **Capacity:** How many patients (1-10)
   - **Clinic Location:** Choose Seremban or Kuala Pilah
3. Click **[Create Room]**
4. Done ✓ System recognizes new room immediately

**Example:**
```
Room Number: Room 4
Capacity: 3 patients
Clinic: Seremban
→ New room ready to use!
```

#### 4. Edit Room Configuration

**When:** Change room capacity, rename room, or update details

**Steps:**
1. Find room in list
2. Click **[Edit]** button
3. Modify fields
4. Click **[Save Changes]**

#### 5. Delete/Deactivate Room

**When:** Room temporarily closed for maintenance or permanently deactivated

**Steps:**
1. Click **[Delete]** button
2. Confirm action
3. System removes room
4. Queue automatically uses remaining rooms

**Safety:** Cannot delete room if patient is being treated there

---

## For Dentist Availability Management

### Quick Status Update

**When:** Dentist taking break, on lunch, or unavailable

**Steps:**
1. Go to **Dentist Management**
2. Find dentist in list
3. Click their current status (e.g., "Available")
4. Select new status:
   - **Available** - Ready for patients
   - **on_break** - 15-30 min break
   - **off** - Not working today
5. Status changes immediately

**Effect:** Queue system stops assigning patients to unavailable dentists

---

## How It Works Behind The Scenes

### The Queue System is Smart

When a patient arrives and checks in:

```
1. Patient checks in
       ↓
2. System looks for:
   - ANY available room (not room #1, not room #2)
   - ANY available dentist (not Dr. Ahmad only)
       ↓
3. Assigns first matching room + dentist
       ↓
4. Patient starts treatment
       ↓
5. Next patient automatically assigned when room/dentist free
```

### What's "Data-Driven"?

Instead of code saying "use Room 1, Room 2, and that's it", the system asks the database:

```
Database: "Give me all available rooms"
→ Room 1, Room 2, Room 3, Room 4, Room 5

Add Room 6?
→ Room 1, Room 2, Room 3, Room 4, Room 5, Room 6 ✓
```

**Zero code changes needed!**

---

## Real-Time Statistics

### View Current Availability

**Room Stats:** `http://localhost:8000/api/rooms/stats`
```json
{
  "total": 5,
  "available": 3,
  "occupied": 2,
  "rooms": [
    {"room_number": "Room 1", "status": "available"},
    {"room_number": "Room 2", "status": "occupied"},
    ...
  ]
}
```

**Dentist Stats:** `http://localhost:8000/api/dentists/stats`
```json
{
  "total_dentists": 2,
  "available": 1,
  "busy": 1,
  "dentists": [
    {"name": "Dr. Ahmad", "status": "busy", "patients_in_queue": 2},
    {"name": "Dr. Siti", "status": "available", "patients_in_queue": 0}
  ]
}
```

---

## Common Scenarios

### Scenario 1: Clinic Opens New Treatment Room

**Current:** 2 rooms, 1 dentist, patients waiting 45 minutes

**Action:**
1. Room 3 setup complete
2. Staff clicks: Add New Room
3. Room Number: "Room 3"
4. Capacity: "2"
5. Clinic: "Seremban"
6. Submit

**Result:**
- Queue system immediately recognizes Room 3
- Next check-in uses Room 3
- Wait times drop automatically ✓
- **Code changes:** 0
- **Downtime:** 0

### Scenario 2: Second Dentist Joins Clinic

**Current:** 1 dentist (Dr. Ahmad), queue of 5 patients

**Action:**
1. Register Dr. Siti in Dentist Management
2. Set status: "available"
3. Done

**Result:**
- Both dentists can treat patients simultaneously
- Next patient assigned to Dr. Siti (if Dr. Ahmad is busy)
- Throughput doubles ✓
- **Code changes:** 0
- **Downtime:** 0

### Scenario 3: Morning Peak Demand

**Time:** 9 AM, 8 patients waiting

**Current Status:**
- Room 1: Dr. Ahmad treating (5 min remaining)
- Room 2: Empty
- Dentist capacity: Dr. Ahmad only

**Action:** Dr. Siti arrives

1. Staff updates Dr. Siti status: "available"
2. System immediately assigns next patient to Dr. Siti in Room 2
3. Two patients treated in parallel ✓
4. Wait time reduced from 30 min → 15 min

### Scenario 4: Maintenance Period

**Room 1 needs repair (2 hours)**

**Action:**
1. Go to Room 1 → [Edit]
2. Temporarily set status: "occupied" (or delete it)
3. Click Save

**Result:**
- New patients assigned to Room 2, 3, 4
- Room 1 skipped while in maintenance
- When repair done: Re-add Room 1
- Patients resume normal flow ✓

---

## Troubleshooting for Staff

### Q: New room doesn't appear in queue assignment

**A:** Check:
1. Room status is "available" (not occupied)
2. Room clinic location matches patient location
3. Refresh browser

### Q: Dentist status change not working

**A:** 
1. Check permissions (you're logged in as staff?)
2. Dentist may have active patient → status auto-manages
3. Refresh page

### Q: Can't delete room

**A:** Room likely has patient being treated
- Wait for patient to finish
- Or mark room status "occupied" temporarily

### Q: Wait times still high after adding room

**A:** Verify:
1. New room shows as "available"
2. New dentist assigned (or existing dentist taking a break?)
3. Peak hours - even with more capacity, wait exists
4. Check API stats: `/api/rooms/stats`

---

## Admin Access Links

| Task | URL |
|------|-----|
| View all rooms | `/staff/rooms` |
| Add new room | `/staff/rooms/create` |
| Edit room | `/staff/rooms/{id}/edit` |
| Manage dentists | `/staff/dentists` |
| View statistics | `/api/rooms/stats` |
| Dentist stats | `/api/dentists/stats` |

---

## Key Principles

### 1. Data Not Code
- Rooms are data (database rows)
- Dentists are data (database rows)
- Not hard-coded in application logic

### 2. Immediate Effect
- Add room → used in next patient assignment
- Change dentist status → queue updates instantly
- No restart required ✓

### 3. Scale Infinitely
- 2 rooms → works
- 5 rooms → works
- 10 rooms → works
- 100 rooms → still works
- Code never changes ✓

### 4. Audit Trail
Every change is logged:
- Who created/edited room
- When the change happened
- What was changed
- Viewable in Activity Logs

---

## Pro Tips

✅ **Do:**
- Add rooms during business hours
- Check statistics regularly for capacity planning
- Mark dentists "on_break" for lunch breaks
- Document when rooms added (for expansion tracking)

❌ **Don't:**
- Delete room while patient being treated (system prevents this)
- Try to set room capacity >10 (physical limit)
- Create duplicate room numbers in same location

---

## Support / Questions

For technical issues:
- Contact: IT/Developer team
- Location: `/staff/developer` (for dev testing)

For operational questions:
- Contact: Clinic Manager
- Check: Activity Logs for change history

---

## Summary Checklist

**To expand clinic capacity:**

- [ ] Decide how many new rooms needed
- [ ] Set up physical room infrastructure
- [ ] Log in to staff dashboard
- [ ] Navigate to Room Management
- [ ] Add new room(s) via UI
- [ ] Verify room appears in statistics
- [ ] Test by checking in patients
- [ ] Monitor wait times for improvement

**That's it!** No developer needed. Clinic scales automatically. ✓

---

**Remember:** This system is designed so that clinics grow faster than bureaucracy. Add resources, get better patient flow. Simple.

---

*Version 1.0 | Last Updated: January 2025 | For Clinic Staff*
