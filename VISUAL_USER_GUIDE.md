# Visual User Guide - Dentist Schedules

## 🎬 How to Add a Leave (Step-by-Step with Screenshots)

### Step 1: Navigate to Dentist Schedules
```
Sidebar Menu
    └─ Dentist Schedules
       (or visit: http://127.0.0.1:8000/staff/dentist-schedules)
```

### Step 2: Find the Dentist
```
┌──────────────────────────────────────────┐
│ Page shows list of dentist cards         │
│ Find "Dr. Helmy" or scroll to find       │
│ the dentist you need                     │
└──────────────────────────────────────────┘
```

### Step 3: Locate Leave Section
```
Within each dentist card, scroll down to find:

╔════════════════════════════════════════════╗
║ 🚫 Leave Dates                             ║
╚════════════════════════════════════════════╝
```

### Step 4: Fill the Form
```
You'll see this form:

┌─────────────────────────────────────────────┐
│ From Date      [Dec 20 ▼]                  │
│ To Date        [Dec 22 ▼]                  │
│ Reason         [Vacation         ]         │
│                [Add Leave Button]           │
└─────────────────────────────────────────────┘

Example values:
├─ From Date: 2025-12-20
├─ To Date: 2025-12-22
└─ Reason: Vacation (OPTIONAL)
```

### Step 5: Click "Add Leave"
```
Button location: Right side of form
Action: Single click

The system will:
✓ Validate the dates
✓ Save to database
✓ Update the leave table below
✓ No page reload needed!
```

### Step 6: Verify Leave Was Added
```
Below the form, you'll see the leave history table:

┌────────────────────────────────────────────┐
│ Leave History:                             │
├────────────────────────────────────────────┤
│ From         To           Reason   Days   │
├────────────────────────────────────────────┤
│ Dec 20       Dec 22       Vacation  3     │
│ [Delete]                                  │
└────────────────────────────────────────────┘
```

### Step 7: Check Monthly Calendar
```
To verify on the calendar:

1. Click "View Monthly Calendar" button (top right)
2. Look for RED events on Dec 20-22
3. If filtered by Dr. Helmy, should show 3 red days
```

---

## 📊 How to View Appointment History

### Locate the History Section
```
Within same dentist card, scroll down further to find:

╔════════════════════════════════════════════╗
║ 📊 Recent Appointments (Past 2 Weeks)      ║
╚════════════════════════════════════════════╝
```

### What You'll See
```
┌─────────────────────────────────────────────────────┐
│ Date    Time      Patient    Service    Status      │
├─────────────────────────────────────────────────────┤
│ Dec 18  2:00 PM   Ahmad      Cleaning   Completed  │
│ Dec 17  10:30 AM  Siti       Root Canal Completed  │
│ Dec 16  3:15 PM   Budi       Filling    Completed  │
│ Dec 15  1:00 PM   Maria      Cleaning   Booked     │
├─────────────────────────────────────────────────────┤
│ (scrollable - shows up to 10 most recent)           │
└─────────────────────────────────────────────────────┘
```

### How to Interpret
```
Column Meanings:
├─ Date: When the appointment occurred
├─ Time: Appointment start time
├─ Patient: Name of patient treated
├─ Service: Type of service (Cleaning, Filling, etc.)
└─ Status: 
   ├─ Completed (green) = Already done
   └─ Booked (blue) = Scheduled, not yet done
```

### Use Cases
```
1. Check if doctor is busy:
   Count appointments in the list
   More = busier

2. Check appointment quality:
   Look at Completed vs Booked ratio
   Higher completed = good work rate

3. See what services are popular:
   Scan Service column
   More "Cleanings" = most booked service

4. Check patient names:
   Scan Patient column
   Frequent names = loyal patients
```

---

## 🗑️ How to Delete a Leave

### Find the Leave
```
Locate in the "🚫 Leave Dates" section:

┌─────────────────────────────────────────┐
│ Leave History:                          │
│ Dec 20-22   Vacation   3 days  [Delete] │
│ Dec 25-26   Christmas  2 days  [Delete] │
└─────────────────────────────────────────┘
```

### Click Delete
```
Step 1: Find the leave you want to remove
Step 2: Click the red "Delete" button
Step 3: Confirm deletion (browser will ask)
Step 4: Leave is removed immediately
Step 5: Calendar updates automatically
```

### Result
```
After deletion:
✓ Removed from leave history table
✓ Removed from monthly calendar
✓ Dentist shows as available again
✓ No page reload needed
```

---

## 🔄 Weekly Schedule Management (Review)

### Location
```
Top of each dentist card:

╔════════════════════════════════════════════╗
║ 📅 Weekly Schedule                         ║
╚════════════════════════════════════════════╝
```

### Layout
```
┌──────────────────────────────────────────────────────┐
│ Day        | Availability | Hours         | Actions  │
├──────────────────────────────────────────────────────┤
│ Monday     | Available    | 9:00 - 5:00  | On/Off   │
│ Tuesday    | Available    | 9:00 - 5:00  | On/Off   │
│ Wednesday  | Off          | —            | On/Off   │
│ Thursday   | Available    | 9:00 - 5:00  | On/Off   │
│ Friday     | Available    | 9:00 - 5:00  | On/Off   │
│ Saturday   | Off          | —            | On/Off   │
│ Sunday     | Off          | —            | On/Off   │
└──────────────────────────────────────────────────────┘
```

### How to Change
```
1. Toggle the checkbox to turn day On/Off
2. If On: Enter start and end times
3. Click Save
4. Changes take effect immediately
5. Reflects on calendar as green (available) or yellow (off)
```

### Important
```
⚠️  Note: Leave (from Leave Dates section) overrides this!

Example:
├─ Monday is normally Available (9-5)
├─ But you add leave for this Monday
└─ Then this Monday shows as Red (leave) on calendar
```

---

## 🎯 Complete Workflow Example

### Scenario: Managing Dr. Budi's Schedule for Next Week

```
STEP 1: ENTER LEAVE
    ↓
1. Go to /staff/dentist-schedules
2. Find Dr. Budi
3. Add leave: Dec 23-24 (Christmas)
4. Verify in leave table
    ↓
STEP 2: CHECK WORKLOAD
    ↓
3. Scroll to Recent Appointments
4. See: 
   - Dec 18: 5 patients
   - Dec 19: 3 patients
   - Dec 20: 4 patients
5. Total: 12 patients before leave
    ↓
STEP 3: VERIFY ON CALENDAR
    ↓
6. Click "View Monthly Calendar"
7. Filter by Dr. Budi
8. See:
   - Green days (available)
   - Red days (leave: Dec 23-24)
   - Blue dots (appointments already booked)
   - Yellow days (day off)
    ↓
RESULT: ✅ Schedule managed!
```

---

## ❓ Common Tasks

### Task 1: "I made a mistake, need to delete a leave"
```
1. Find leave in table
2. Click Delete button
3. Confirm
4. Done - automatically removes from calendar too
```

### Task 2: "Is Dr. Ahmad available next week?"
```
1. Find Dr. Ahmad
2. Check Weekly Schedule section
3. Look at which days show "Available"
4. Check Leave Dates - any holidays?
5. Verify on monthly calendar
```

### Task 3: "How busy is Dr. Siti?"
```
1. Find Dr. Siti
2. Scroll to Recent Appointments
3. Count appointments (max 10 shown)
4. If 10 items, she's probably busy!
5. All "Completed" = good productivity
```

### Task 4: "Add leave for entire week"
```
From: Monday (e.g., Dec 20)
To: Friday (e.g., Dec 24)
Reason: Annual leave
Click Add Leave - creates entire week in one go!
```

### Task 5: "Cancel a leave I just added"
```
1. Scroll to leave table
2. Find the leave (usually at top)
3. Click Delete
4. Confirm
5. Leave is removed
```

---

## 🎨 Visual Color Guide

### What the Colors Mean

#### On Monthly Calendar
```
🟢 GREEN
└─ Dentist is available to see patients

🟡 YELLOW
└─ Dentist is OFF (not working that day)

🔵 BLUE
└─ Appointment is scheduled

🔴 RED
└─ Dentist is on LEAVE

🟤 GRAY/MUTED
└─ Already passed (historical)
```

#### Status Badges
```
[Active] - Green
└─ Dentist can work

[Inactive] - Gray
└─ Dentist is deactivated

[Completed] - Green badge on appointment
└─ Appointment already done

[Booked] - Blue badge on appointment
└─ Appointment scheduled, not yet done
```

---

## 📱 Mobile View

### On Phone/Tablet
```
All sections stack vertically:

[Header]
    ↓
[Weekly Schedule] ← scrollable table
    ↓
[Leave Dates] ← date picker inputs
    ↓
[Leave History] ← scrollable table
    ↓
[Recent Appointments] ← scrollable table
```

### Tips for Mobile
- Swipe left/right to scroll tables
- Use date picker calendar widget
- Form inputs are touch-friendly
- All buttons large enough to tap

---

## ✅ Validation Rules

### When Adding Leave
```
Error: "Please select both from and to dates"
└─ Must fill BOTH From and To dates

Error: "Dates invalid"
└─ Check date format (should be YYYY-MM-DD)
└─ Often shown by browser calendar picker

Error: "End date before start date"
└─ Make sure To date >= From date
└─ Example: Can't say "Dec 25 to Dec 20"
```

### When Saving
```
If error: Check browser console (F12)
├─ Look for red error messages
├─ Check if dates are in valid format
└─ Verify dentist exists in system

If still failing:
├─ Refresh page
├─ Try again
└─ Contact admin if persistent
```

---

## 🔔 Notifications & Feedback

### Visual Feedback
```
When you add a leave:
1. Form temporarily disables
2. Brief loading state
3. Leave appears in table
4. No page reload
5. Can immediately add another

When you delete:
1. Browser asks for confirmation
2. You click OK
3. Leave removed from table
4. No page reload
```

### Success Confirmation
```
No notification shown for:
├─ Adding leave ← but see it in table
└─ Deleting leave ← but see it disappear

Optional: Check calendar later
├─ Leave should appear as red event
└─ Only if you add it for future date
```

---

## 🚨 Troubleshooting Guide

### Problem: Leave won't save
```
Solution 1:
├─ Check both dates are selected
├─ Try using calendar picker instead of typing
└─ Click Add Leave button again

Solution 2:
├─ Open browser console (F12)
├─ Look for error messages
├─ Take note of what it says
└─ Contact admin with error message

Solution 3:
├─ Refresh page
├─ Try adding again
└─ Different dates?
```

### Problem: Leave appears but not on calendar
```
Solution 1:
├─ Refresh the calendar page
├─ Sometimes takes a moment to sync
└─ Try filtering by dentist

Solution 2:
├─ Check leave is in the future
├─ Past leaves may not show on calendar
└─ Verify on correct month

Solution 3:
├─ Check dentist filter on calendar
├─ Make sure "All Dentists" selected
└─ Or filter by correct dentist
```

### Problem: No appointments showing
```
Solution 1:
├─ Need to create appointments first
├─ Go to /staff/appointments
└─ Book some appointments

Solution 2:
├─ Check date range
├─ Only shows past 2 weeks
├─ Appointment older = won't show
└─ Create test appointment if needed

Solution 3:
├─ Verify for correct dentist
├─ Scroll to find right dentist card
└─ Check if dentist has any appointments
```

---

## 📞 Getting Help

### For Feature Questions
1. Read the Quick Start Guide
2. Check this visual guide
3. See if there's an FAQ for your question

### For Technical Issues
1. Check browser console (F12)
2. Note any error messages
3. Take a screenshot
4. Contact system administrator

### For Feature Requests
1. Document what you need
2. Explain the workflow
3. Suggest how it should work
4. Submit to development team

---

## ✨ Pro Tips

**Tip 1**: Add leave in bulk
```
From: Dec 20
To: Dec 25
└─ Creates 6 days of leave in one form!
```

**Tip 2**: Use reason field
```
Instead of just blank, add:
├─ "Vacation"
├─ "Sick Leave"
├─ "Training"
├─ "Conference"
└─ Makes history useful for HR
```

**Tip 3**: Check workload regularly
```
Each week:
├─ Look at Recent Appointments
├─ See who's busiest
├─ Balance scheduling accordingly
└─ Prevent burnout
```

**Tip 4**: Plan ahead
```
Add leaves early:
├─ Longer notice = better planning
├─ Staff can adjust schedules
├─ Patients get better availability
└─ Everyone's happy!
```

**Tip 5**: Use calendar view
```
Don't just use this page:
├─ View calendar monthly
├─ See overall team coverage
├─ Spot gaps in availability
├─ Better resource planning
```

