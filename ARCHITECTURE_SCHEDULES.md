# Complete Architecture - Dentist Schedules System

## 🏗️ System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    DENTIST SCHEDULE SYSTEM                      │
└─────────────────────────────────────────────────────────────────┘

Database Models
├── Dentist (name, specialization, status, ...)
├── DentistSchedule (dentist_id, day_of_week, is_available, start_time, end_time)
├── DentistLeave (dentist_id, start_date, end_date, reason) ← NEW
└── Appointment (dentist_id, patient_name, appointment_date, appointment_time, service_id, status)

Controllers
├── DentistScheduleController (index, calendar, events, update)
└── DentistLeaveController (store, destroy) ← NEW

Views
├── staff/dentist-schedules/index.blade.php (list with forms) ← UPDATED
├── staff/dentist-schedules/calendar.blade.php (monthly view)
└── (shared calendar assets)

Routes
├── POST /staff/dentist-leaves (create)
├── DELETE /staff/dentist-leaves/{id} (delete) ← NEW
└── (existing schedule routes)
```

---

## 📊 Database Schema

### DentistLeave Table (Already Exists)
```sql
CREATE TABLE dentist_leaves (
    id BIGINT PRIMARY KEY,
    dentist_id BIGINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(255) NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (dentist_id) REFERENCES dentists(id)
);
```

### Related Tables (Context)
```
dentists
├── id
├── name
├── specialization
├── email
├── phone
├── status (1=active, 0=inactive)
└── ... other fields

dentist_schedules
├── id
├── dentist_id (FK)
├── day_of_week (Monday-Sunday)
├── is_available (boolean)
├── start_time (TIME)
├── end_time (TIME)
└── timestamps

appointments
├── id
├── dentist_id (FK)
├── patient_name
├── appointment_date (DATE)
├── appointment_time (TIME)
├── service_id (FK)
├── status (booked, completed, cancelled)
└── ... other fields
```

---

## 🔄 Data Flow Diagram

### Leave Management Flow
```
User Input
   ↓
[Leave Form]
   ├─ From Date
   ├─ To Date
   ├─ Reason (optional)
   └─ [Add Leave Button]
   ↓
JavaScript AJAX
   ├─ POST to /staff/dentist-leaves
   ├─ Content-Type: application/json
   └─ CSRF token included
   ↓
DentistLeaveController@store
   ├─ Validate data
   ├─ Create record in DB
   └─ Return JSON response
   ↓
Leave Table Updated
   └─ Refresh table HTML
   ↓
Monthly Calendar Syncs
   └─ Red events appear
```

### Appointment History Flow
```
Page Load
   ↓
DentistScheduleController@index
   ├─ Load all dentists
   ├─ Load dentist schedules
   └─ Load dentist leaves (relationships)
   ↓
Blade View Renders
   └─ For each dentist:
      ├─ Render weekly schedule
      ├─ Render leave form
      ├─ Loop through $dentist->leaves()
      │  └─ Display in table
      └─ Loop through $dentist->appointments()
         └─ Filter past 2 weeks
         └─ Display in table
```

---

## 🎨 UI Layout

### Page Structure
```
┌─────────────────────────────────────────────────────────┐
│ Header: "Dentist Schedules"  [View Monthly Calendar]    │
├─────────────────────────────────────────────────────────┤
│ Info Alert: "Adjust templates here..."                  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ┌─ Card: Dr. Helmy ─────────────────────────────────┐  │
│ │                                                   │  │
│ │ 📅 Weekly Schedule                               │  │
│ │ [Table: Day | Availability | Hours | Actions]   │  │
│ │                                                   │  │
│ │ 🚫 Leave Dates                                   │  │
│ │ [Form: From | To | Reason | Add Leave]           │  │
│ │ [Table: Leave history with delete buttons]       │  │
│ │                                                   │  │
│ │ 📊 Recent Appointments (Past 2 Weeks)            │  │
│ │ [Table: Date | Time | Patient | Service | Status]│  │
│ │                                                   │  │
│ └───────────────────────────────────────────────────┘  │
│                                                         │
│ ┌─ Card: Dr. Budi ──────────────────────────────────┐  │
│ │ (same layout)                                    │  │
│ └───────────────────────────────────────────────────┘  │
│                                                         │
│ ┌─ Card: Dr. Siti ──────────────────────────────────┐  │
│ │ (same layout)                                    │  │
│ └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 📝 Code Components

### DentistLeaveController.php

```php
class DentistLeaveController extends Controller
{
    // Create new leave
    public function store(Request $request)
    {
        // Validate: dentist_id, start_date, end_date, reason (optional)
        // Create DentistLeave record
        // Return JSON response
    }

    // Delete leave
    public function destroy(DentistLeave $dentistLeave)
    {
        // Delete record
        // Redirect with success message
    }
}
```

### View: dentist-schedules/index.blade.php

```php
@foreach($dentists as $dentist)
    <!-- Card wrapper -->
    
    <!-- Section 1: Weekly Schedule -->
    @foreach($days as $day)
        <!-- Display schedule with form -->
    @endforeach
    
    <!-- Section 2: Leave Management -->
    <!-- Form to add leave -->
    <!-- Table with leave history -->
    @forelse($dentist->leaves() as $leave)
        <!-- Show each leave -->
    @endforelse
    
    <!-- Section 3: Appointment History -->
    <!-- Table with recent appointments -->
    @forelse($dentist->appointments()->where('appointment_date', '>=', now()->subWeeks(2)) as $apt)
        <!-- Show each appointment -->
    @endforelse
    
@endforeach
```

---

## 🔌 API Endpoints

### Create Leave
```
POST /staff/dentist-leaves
Content-Type: application/json
X-CSRF-TOKEN: [token]

{
    "dentist_id": 1,
    "start_date": "2025-12-20",
    "end_date": "2025-12-22",
    "reason": "Vacation"
}

Response (200 OK):
{
    "success": true,
    "data": {
        "id": 5,
        "dentist_id": 1,
        "start_date": "2025-12-20",
        "end_date": "2025-12-22",
        "reason": "Vacation",
        "created_at": "2025-12-19T..."
    }
}
```

### Delete Leave
```
DELETE /staff/dentist-leaves/5

Response (302 Redirect):
Redirects to previous page with success message
```

---

## 🔐 Security & Validation

### Input Validation (DentistLeaveController@store)
```php
$validated = $request->validate([
    'dentist_id' => 'required|exists:dentists,id',      // Must exist in DB
    'start_date' => 'required|date',                      // Valid date format
    'end_date' => 'required|date|after_or_equal:start_date',  // End ≥ Start
    'reason' => 'nullable|string|max:255'                 // Optional, max 255 chars
]);
```

### Authentication & Authorization
```php
// Route middleware
Route::post('/staff/dentist-leaves', ...)->middleware(['auth', 'role:staff']);

// Only staff can:
- View schedules
- Add leaves
- Delete leaves
- Manage schedules
```

### CSRF Protection
```html
<!-- In Blade template -->
@csrf  <!-- Automatically included in forms -->

<!-- In AJAX -->
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
```

---

## 🎯 Integration Points

### With Monthly Calendar (`/staff/dentist-schedules/calendar`)
```
DentistScheduleController@events()
    ├─ Fetches appointments (blue)
    ├─ Fetches dentist leaves (red) ← Uses /staff/dentist-leaves data
    ├─ Fetches unavailable days (yellow)
    └─ Fetches available days (green)
```

### With Appointments Page (`/staff/appointments`)
```
[Calendar View Button]
    └─ Links to /staff/dentist-schedules/calendar
    └─ Shows appointments with leaves
```

---

## 🗄️ Database Relationships

```
Dentist (1)
    ├─ hasMany DentistSchedule (7 per dentist - one per day)
    ├─ hasMany DentistLeave (0+ per dentist)
    └─ hasMany Appointment (0+ per dentist)

DentistLeave (Many)
    └─ belongsTo Dentist (1)

Appointment (Many)
    ├─ belongsTo Dentist (1)
    └─ belongsTo Service (1)

DentistSchedule (Many)
    └─ belongsTo Dentist (1)
```

### In Code
```php
// Dentist Model
public function leaves()
{
    return $this->hasMany(DentistLeave::class);
}

public function appointments()
{
    return $this->hasMany(Appointment::class);
}

// DentistLeave Model
public function dentist()
{
    return $this->belongsTo(Dentist::class);
}
```

---

## ⚙️ Backend Processing

### When Page Loads: DentistScheduleController@index()
1. Load all active dentists
2. For each dentist, create default schedules if missing
3. Load schedules relationship
4. Load leaves relationship (new)
5. Pass to view: `$dentists`, `$days`

### When Leave Form Submitted
1. AJAX sends POST with date range
2. DentistLeaveController validates
3. Creates record in DB
4. Returns JSON response
5. JavaScript updates table on page
6. No page reload needed

### When Delete Clicked
1. POST request with _method=DELETE
2. DentistLeaveController destroys record
3. Redirects back
4. Show success message
5. Page reload shows updated table

### When Page Renders
1. For each dentist, loop through leaves (via relationship)
2. Display in appointment history table
3. Date range: `now()->subWeeks(2)` to today
4. Limit: 10 most recent appointments
5. Show: date, time, patient name, service, status

---

## 🚀 Performance Optimization

### Query Optimization
```php
// Efficient loading
$dentists = Dentist::where('status', 1)
    ->with(['schedules', 'leaves']) // Eager load
    ->get();

// In view loops - no N+1 queries
@foreach($dentist->leaves() as $leave) // Already loaded
```

### Lazy Loading
```php
// Appointments loaded separately in view
// Only shows 10 most recent
->limit(10)
->get()
```

### Caching Opportunities (Future)
```php
// Could cache:
- Dentist list (changes rarely)
- Weekly schedule template (changes rarely)
- Leave calendar (changes frequently - not cached)
- Appointment history (time-bound - could cache hourly)
```

---

## 🐛 Error Handling

### Try-Catch in Controller
```php
public function store(Request $request)
{
    try {
        $leave = DentistLeave::create($validated);
        return response()->json(['success' => true, 'data' => $leave]);
    } catch (\Exception $e) {
        // Log error
        // Return 500 with message
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
```

### Validation Error Handling
```php
// If validation fails, Laravel returns 422 with errors
// AJAX catches and shows alert
.catch(err => {
    alert('Error: ' + (data.message || 'Failed to add leave'));
});
```

---

## 📚 File Structure

```
app/
├── Http/Controllers/Staff/
│   ├── DentistScheduleController.php (index, calendar, events, update)
│   └── DentistLeaveController.php ← NEW
│
└── Models/
    ├── Dentist.php (relationships)
    ├── DentistSchedule.php
    ├── DentistLeave.php ← Already exists
    └── Appointment.php

resources/views/staff/
├── dentist-schedules/
│   ├── index.blade.php ← UPDATED
│   └── calendar.blade.php
└── appointments.blade.php

routes/
└── web.php ← Updated with new routes

database/
└── migrations/
    └── *_create_dentist_leaves_table.php (already exists)

documentation/
├── DENTIST_SCHEDULES_GUIDE.md ← NEW
├── OPERATIONAL_IMPROVEMENTS.md ← NEW
└── QUICK_START_SCHEDULES.md ← NEW
```

---

## 🔄 Maintenance Notes

### To Add New Dentist
1. Create dentist record
2. DentistScheduleController automatically creates 7 schedules
3. Schedules appear on next page load

### To Remove Dentist
1. Soft delete dentist
2. Schedules remain (orphaned)
3. Won't appear in current list (filtered by status=1)

### To Modify Leave
1. Currently: Delete and re-add
2. Future: Could add inline edit modal

### To Backup Leaves
1. Export via: `DentistLeave::all()->toJson()`
2. Or database dump

---

## 📊 Statistics Possible

Future reports could calculate:
```php
// Hours worked
$hours = $dentist->appointments()
    ->where('status', 'completed')
    ->count() * 0.5; // Assuming 30min per appointment

// Days on leave
$leavesDays = $dentist->leaves()
    ->whereBetween('start_date', [$from, $to])
    ->sum(fn($l) => $l->start_date->diffInDays($l->end_date) + 1);

// Busiest day
$appointments
    ->groupBy('appointment_date')
    ->max('count');
```

---

## ✅ Testing Checklist

- [ ] Create leave - appears in table
- [ ] Leave appears on calendar (red)
- [ ] Delete leave - disappears from table
- [ ] Leave removed from calendar
- [ ] Appointment history shows correct data
- [ ] Past 2 weeks filter works
- [ ] Limit 10 appointments works
- [ ] Weekly schedule still works
- [ ] View cache clears properly
- [ ] CSRF validation works
- [ ] Authentication required

---

## 🎓 Learning Resources

- Laravel Model Relationships: https://laravel.com/docs/relations
- Blade Templating: https://laravel.com/docs/blade
- Form Handling: https://laravel.com/docs/requests
- AJAX in Blade: Use X-CSRF-TOKEN header

