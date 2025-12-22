# Dentist Schedules Management - Improved Operational Features

## What's New

The Dentist Schedules management page (`/staff/dentist-schedules`) has been significantly enhanced to help staff better manage dentist availability, leaves, and view work history.

---

## Features Added

### 1. **📅 Weekly Schedule Management** (Existing)
- Manage each dentist's weekly availability template
- Toggle availability on/off for each day
- Set specific working hours (start time - end time)
- Changes saved immediately

### 2. **🚫 Leave Management** (NEW)
A new section to quickly add and manage dentist leave dates:

#### Adding a Leave
1. **Select Date Range**: 
   - "From" date picker - when the leave starts
   - "To" date picker - when the leave ends
   - Reason field (optional) - why they're on leave (e.g., "Sick", "Vacation", "Training")
   
2. **Add the Leave**: Click "Add Leave" button - the record is created instantly
3. **View Leave History**: Shows all current and past leaves sorted by most recent first
   - Date range (From - To)
   - Reason if provided
   - Number of days
   - Delete button to remove if needed

#### How It Works
- Leaves are stored separately from weekly schedules
- A dentist can be on leave even on their normally "working" days
- Leave dates will show as **red events** on the Monthly Calendar
- Leave override the weekly template (doctor won't show as available even if Tuesday is normally working)

---

### 3. **📊 Recent Appointments History** (NEW)
Shows the last 10 appointments from the past 2 weeks for each dentist:

- **Date** - Appointment date
- **Time** - Appointment time
- **Patient Name** - Who they treated
- **Service** - Type of service
- **Status** - Booked/Completed/Cancelled

#### Benefits
- Staff can quickly see workload trends
- Identify which dentist is overbooked or underutilized
- Track appointment completion rates
- View past appointments without leaving this page

---

## How to Use

### Workflow for Dentist Leave

**Scenario**: Dr. Helmy needs to take 3 days of leave (Dec 20-22) for personal reasons

1. Find Dr. Helmy's card on the page
2. Scroll to "🚫 Leave Dates" section
3. Fill in:
   - From: 2025-12-20
   - To: 2025-12-22
   - Reason: Personal (optional)
4. Click "Add Leave"
5. The leave appears in the table below
6. Check the Monthly Calendar to see the leave as red events

**To Cancel the Leave**:
1. Find the leave record in the table
2. Click "Delete" button
3. Confirm deletion

---

### Workflow for Viewing Work History

**Scenario**: Manager wants to see Dr. Helmy's recent workload

1. Go to `/staff/dentist-schedules`
2. Find Dr. Helmy's card
3. Scroll to "📊 Recent Appointments (Past 2 Weeks)"
4. See up to 10 recent appointments with patient names, services, and status
5. Helps identify:
   - Busiest dentists
   - Appointment completion rates
   - Services most frequently provided

---

## How It Integrates

### With Monthly Calendar (`/staff/dentist-schedules/calendar`)
- Leaves added here appear as **red events** on the calendar
- Unavailable days (from weekly schedule) appear as **yellow**
- Green = available, Blue = appointments
- Filter by specific dentist to focus on one person

### With Appointments Page (`/staff/appointments`)
- Calendar view button takes you to appointments calendar
- Shows all appointments staff has booked
- Same integration with leaves and schedules

---

## Database Structure

### DentistLeave Table
```
- id
- dentist_id (FK to Dentist)
- start_date (DATE)
- end_date (DATE)
- reason (TEXT, nullable)
- created_at / updated_at
```

### Example Leave Records
```
Dr. Helmy: Dec 20-22, 2025 - Personal
Dr. Budi:  Dec 25-26, 2025 - Christmas
```

---

## API Endpoints

For integration or custom features:

### Create a Leave
```
POST /staff/dentist-leaves
Content-Type: application/json

{
  "dentist_id": 1,
  "start_date": "2025-12-20",
  "end_date": "2025-12-22",
  "reason": "Personal"
}

Response:
{
  "success": true,
  "data": { ... leave record ... }
}
```

### Delete a Leave
```
DELETE /staff/dentist-leaves/{leaveId}
```

---

## Technical Details

- **Leave Creation**: AJAX POST request via JavaScript - no page reload
- **Leave Display**: Blade template loops through `$dentist->leaves()` relationship
- **Appointment History**: Queries appointments from past 14 days, limited to 10 recent
- **Color Coding**: Consistent with calendar:
  - Green (#198754) = Available
  - Yellow (#ffc107) = Unavailable (not scheduled to work)
  - Blue (#0d6efd) = Appointments
  - Red (#dc3545) = Leave

---

## Future Enhancements

Possible improvements for next phase:

1. **Leave Types**: Distinguish between Sick Leave, Vacation, Training, etc.
2. **Statistics Dashboard**: 
   - Hours worked this month
   - Appointments per dentist
   - Average appointment duration
   - Busiest days/times
3. **Bulk Leave Entry**: Add leave for multiple dentists at once
4. **Historical Reports**: Export work history for payroll/analytics
5. **Work Hour Tracking**: Track actual hours worked vs. scheduled
6. **Leave Balance**: Track annual leave balance, days used, days remaining
7. **Approval Workflow**: Manager approves/denies leave requests from dentists

---

## Troubleshooting

### Leave not appearing on calendar?
- Refresh the page
- Check that the date range is correct
- Make sure it's today or in the future (past leaves may be filtered)

### Cannot add leave?
- Both From and To dates are required
- End date must be same as or after Start date
- Check browser console for error messages

### Appointment history not showing?
- Only shows appointments from past 14 days
- Limited to 10 most recent
- Check that appointments exist for this dentist
