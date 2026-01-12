# MODULE & FUNCTIONAL DECOMPOSITION DIAGRAM
## Dental Clinic Appointment & Queue System Architecture

**System Date:** December 23, 2025  
**Framework:** Laravel 12  
**Architecture Pattern:** Layered (MVC) + Service-Oriented

---

## 1. HIGH-LEVEL SYSTEM ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                            │
│                     (Views & Controllers)                        │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────┐   │
│  │  Public Pages    │  │  Staff Dashboard │  │   API       │   │
│  │  (Blade Views)   │  │  (Blade Views)   │  │ (JSON)      │   │
│  └──────────────────┘  └──────────────────┘  └─────────────┘   │
└─────────────────────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────────────────────┐
│               BUSINESS LOGIC LAYER                               │
│                   (Services)                                     │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────┐   │
│  │ Check-In Service │  │ Queue Assignment │  │  Late/No-   │   │
│  │                  │  │  Service         │  │  Show Service   │
│  └──────────────────┘  └──────────────────┘  └─────────────┘   │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────┐   │
│  │ Activity Logger  │  │ WhatsApp Sender  │  │ (More...)   │   │
│  └──────────────────┘  └──────────────────┘  └─────────────┘   │
└─────────────────────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────────────────────┐
│                    DATA LAYER                                    │
│              (Models & Database)                                 │
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────┐   │
│  │ Appointment      │  │ Dentist & Rooms  │  │ Queue &     │   │
│  │ Feedback         │  │ Services         │  │ Logs        │   │
│  └──────────────────┘  └──────────────────┘  └─────────────┘   │
└─────────────────────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────────────────────┐
│                  DATABASE (SQLite/MySQL)                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. FUNCTIONAL MODULES DECOMPOSITION

### 2.1 PATIENT/PUBLIC MODULE

```
┌─────────────────────────────────────────────────────────────────┐
│                   PATIENT MODULE                                │
│         (Public Appointment & Information Portal)               │
└─────────────────────────────────────────────────────────────────┘

┌──── PRESENTATION LAYER ─────────────────────────────────────────┐
│                                                                   │
│  Controllers:                                                     │
│  ├─ AppointmentController (public booking, tracking)             │
│  │   ├─ create()         → Display booking form                  │
│  │   ├─ store()          → Create appointment                    │
│  │   ├─ visitStatus()    → Track by token                        │
│  │   ├─ trackByCode()    → Track by visit code                   │
│  │   ├─ publicCheckIn()  → Self check-in                         │
│  │   ├─ findMyBooking*() → Find booking form                     │
│  │   └─ queueBoard()     → View queue board                      │
│  │                                                                │
│  ├─ PublicController (clinic info)                              │
│  │   ├─ home()      → Homepage                                   │
│  │   ├─ about()     → About clinic                               │
│  │   ├─ services()  → View services                              │
│  │   ├─ dentists()  → View dentist list                          │
│  │   ├─ contact()   → Contact page                               │
│  │   └─ hours()     → Operating hours                            │
│  │                                                                │
│  ├─ FeedbackController (feedback submission)                     │
│  │   ├─ create()  → Feedback form                                │
│  │   ├─ store()   → Submit feedback                              │
│  │   └─ thanks()  → Confirmation page                            │
│  │                                                                │
│  ├─ ChatbotController (chat support)                            │
│  │   ├─ index()  → Chat interface                                │
│  │   └─ handle() → Process messages                              │
│  │                                                                │
│  Views (Blade Templates):                                        │
│  └─ public/*.blade.php (home, about, services, etc.)            │
│     ├─ book.blade.php              (booking form)                │
│     ├─ queue-board.blade.php       (queue display)               │
│     ├─ track.blade.php             (track appointment)           │
│     ├─ checkin.blade.php           (check-in form)               │
│     ├─ find-my-booking.blade.php   (lookup form)                 │
│     ├─ visit-status.blade.php      (status display)              │
│     ├─ feedback.blade.php          (feedback form)               │
│     └─ dentists.blade.php, hours.blade.php, etc.               │
│                                                                   │
└──── BUSINESS LOGIC LAYER ──────────────────────────────────────┘
│                                                                   │
│  Services:                                                        │
│  ├─ CheckInService                                              │
│  │   ├─ checkIn()              → Process check-in                │
│  │   ├─ validateCheckIn()      → Validate appointment            │
│  │   ├─ isLate()               → Check if late                   │
│  │   └─ checkInLate()          → Late check-in                   │
│  │                                                                │
│  └─ WhatsAppSender                                              │
│      └─ sendAppointmentConfirmation() → Send reminder            │
│                                                                   │
└──── DATA LAYER ────────────────────────────────────────────────┘
│                                                                   │
│  Models:                                                          │
│  ├─ Appointment      (patient appointments)                      │
│  ├─ Service          (clinic services)                           │
│  ├─ Dentist          (dentist information)                       │
│  ├─ Room             (treatment rooms)                           │
│  ├─ OperatingHour    (clinic hours)                              │
│  ├─ Feedback         (patient feedback)                          │
│  └─ Queue            (queue records)                             │
│                                                                   │
└──── DATABASE ──────────────────────────────────────────────────┘
│  Tables:                                                          │
│  ├─ appointments (patient_name, phone, email, status, etc.)     │
│  ├─ queues (queue_status, check_in_time, etc.)                 │
│  ├─ feedback (rating, comments, etc.)                           │
│  ├─ services (name, duration, category)                         │
│  ├─ dentists (name, speciality, status)                         │
│  ├─ rooms (name, status)                                        │
│  └─ operating_hours (day, start_time, end_time)               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

### 2.2 STAFF/OPERATIONS MODULE

```
┌─────────────────────────────────────────────────────────────────┐
│                    STAFF MODULE                                 │
│         (Daily Operations & Management Dashboard)               │
└─────────────────────────────────────────────────────────────────┘

┌──── PRESENTATION LAYER ─────────────────────────────────────────┐
│                                                                   │
│  Controllers (staff/*):                                          │
│  │                                                               │
│  ├─ AppointmentController (appointment management)              │
│  │   ├─ index()          → List appointments                     │
│  │   ├─ create()         → Create appointment form               │
│  │   ├─ store()          → Store new appointment                 │
│  │   ├─ edit()           → Edit appointment                      │
│  │   ├─ update()         → Update appointment                    │
│  │   ├─ destroy()        → Delete appointment                    │
│  │   ├─ checkIn()        → Manual check-in                       │
│  │   ├─ storeWalkIn()    → Register walk-in                      │
│  │   └─ updateQueueStatus() → Update queue status               │
│  │                                                                │
│  ├─ CalendarController (calendar view)                          │
│  │   ├─ index()     → Calendar page                              │
│  │   └─ events()    → Get calendar events                        │
│  │                                                                │
│  ├─ DentistController (dentist management)                      │
│  │   ├─ index()        → List dentists                           │
│  │   ├─ create()       → Create dentist form                     │
│  │   ├─ store()        → Store dentist                           │
│  │   ├─ edit()         → Edit dentist                            │
│  │   ├─ update()       → Update dentist                          │
│  │   ├─ destroy()      → Delete dentist                          │
│  │   ├─ updateStatus() → Toggle status                           │
│  │   ├─ stats()        → Dentist statistics                      │
│  │   └─ bulkDestroy()  → Bulk delete                             │
│  │                                                                │
│  ├─ DentistScheduleController (schedule management)             │
│  │   ├─ index()    → List schedules                              │
│  │   ├─ update()   → Update schedule                             │
│  │   ├─ calendar() → Schedule calendar                           │
│  │   └─ events()   → Calendar events                             │
│  │                                                                │
│  ├─ DentistLeaveController (leave management)                   │
│  │   ├─ store()    → Create leave                                │
│  │   └─ destroy()  → Cancel leave                                │
│  │                                                                │
│  ├─ ServiceController (service management)                      │
│  │   ├─ index()        → List services                           │
│  │   ├─ create()       → Create service                          │
│  │   ├─ store()        → Store service                           │
│  │   ├─ edit()         → Edit service                            │
│  │   ├─ update()       → Update service                          │
│  │   ├─ destroy()      → Delete service                          │
│  │   └─ bulkDestroy()  → Bulk delete services                    │
│  │                                                                │
│  ├─ RoomController (room management)                            │
│  │   ├─ index()           → List rooms                           │
│  │   ├─ create()          → Create room                          │
│  │   ├─ store()           → Store room                           │
│  │   ├─ edit()            → Edit room                            │
│  │   ├─ update()          → Update room                          │
│  │   ├─ destroy()         → Delete room                          │
│  │   ├─ bulkToggleStatus() → Bulk toggle status                 │
│  │   └─ stats()           → Room statistics                      │
│  │                                                                │
│  ├─ OperatingHourController (hours management)                  │
│  │   ├─ index()       → List hours                               │
│  │   ├─ create()      → Create hour                              │
│  │   ├─ store()       → Store hour                               │
│  │   ├─ edit()        → Edit hour                                │
│  │   ├─ update()      → Update hour                              │
│  │   ├─ destroy()     → Delete hour                              │
│  │   └─ bulkDestroy() → Bulk delete                              │
│  │                                                                │
│  ├─ QuickEditController (quick actions)                         │
│  │   ├─ index()                → Dashboard                       │
│  │   ├─ updateDentistStatus()  → Toggle dentist                 │
│  │   ├─ updateServiceStatus()  → Toggle service                 │
│  │   ├─ updateOperatingHour()  → Edit hours                     │
│  │   ├─ updateStaffVisibility()→ Toggle visibility              │
│  │   ├─ storeStaff()           → Create staff                   │
│  │   └─ destroyStaff()         → Delete staff                   │
│  │                                                                │
│  ├─ ActivityLogController (audit trail)                         │
│  │   └─ index()  → View activity logs                            │
│  │                                                                │
│  ├─ FeedbackController (feedback management)                    │
│  │   ├─ index() → List feedback                                  │
│  │   └─ show()  → View feedback details                          │
│  │                                                                │
│  ├─ PastController (deleted records)                            │
│  │   ├─ index()              → View deleted                      │
│  │   ├─ restoreDentist()     → Restore dentist                  │
│  │   ├─ forceDeleteDentist() → Permanently delete               │
│  │   ├─ restoreStaff()       → Restore staff                    │
│  │   └─ forceDeleteStaff()   → Permanently delete               │
│  │                                                                │
│  ├─ DeveloperController (dev tools)                             │
│  │   ├─ login()         → Dev login                              │
│  │   ├─ authenticate()  → Authenticate                           │
│  │   ├─ dashboard()     → Dev dashboard                          │
│  │   ├─ apiTest()       → API test interface                     │
│  │   └─ logout()        → Dev logout                             │
│  │                                                                │
│  Views (Blade Templates):                                        │
│  └─ staff/*.blade.php                                            │
│     ├─ appointments.blade.php, appointments-create.blade.php    │
│     ├─ dentists/index.blade.php, create.blade.php, edit.blade.php
│     ├─ operating-hours/index.blade.php, create.blade.php        │
│     ├─ services/index.blade.php, create.blade.php               │
│     ├─ rooms/index.blade.php, create.blade.php                  │
│     ├─ calendar/index.blade.php                                 │
│     ├─ dentist-schedules/index.blade.php, calendar.blade.php   │
│     ├─ activity-logs.blade.php                                  │
│     ├─ feedback/index.blade.php                                 │
│     ├─ quick-edit.blade.php                                     │
│     ├─ past.blade.php                                           │
│     └─ developer/*.blade.php                                    │
│                                                                   │
└──── BUSINESS LOGIC LAYER ──────────────────────────────────────┘
│                                                                   │
│  Services:                                                        │
│  ├─ QueueAssignmentService (queue optimization)                │
│  │   ├─ assignNextPatient()      → Assign next waiting patient   │
│  │   ├─ startTreatment()         → Start treatment               │
│  │   ├─ completeTreatment()      → Complete treatment            │
│  │   ├─ getEstimatedWaitTime()   → Calculate wait time           │
│  │   ├─ allRoomsOccupied()       → Check room availability       │
│  │   ├─ getQueueStats()          → Queue statistics              │
│  │   ├─ findAvailableRoom()      → Find empty room               │
│  │   └─ findAvailableDentist()   → Find free dentist             │
│  │                                                                │
│  ├─ CheckInService (patient check-in)                           │
│  │   ├─ checkIn()              → Process check-in                │
│  │   ├─ validateCheckIn()      → Validate appointment            │
│  │   ├─ isLate()               → Check lateness                  │
│  │   └─ checkInLate()          → Late check-in                   │
│  │                                                                │
│  ├─ LateNoShowService (appointment status)                      │
│  │   ├─ markLateAppointments()    → Auto-mark late               │
│  │   ├─ markNoShowAppointments()  → Auto-mark no-show            │
│  │   ├─ handleDentistUnavailable()→ Handle absence               │
│  │   ├─ createWalkIn()            → Register walk-in             │
│  │   └─ recoverAppointment()      → Find lost appointment        │
│  │                                                                │
│  ├─ ActivityLogger (audit trail)                                │
│  │   └─ log() → Log all actions                                  │
│  │                                                                │
│  └─ WhatsAppSender                                              │
│      └─ sendAppointmentConfirmation()                           │
│                                                                   │
└──── DATA LAYER ────────────────────────────────────────────────┘
│                                                                   │
│  Models:                                                          │
│  ├─ Appointment                                                  │
│  ├─ Queue                                                        │
│  ├─ Dentist & DentistSchedule & DentistLeave                   │
│  ├─ Service                                                      │
│  ├─ Room                                                         │
│  ├─ OperatingHour                                                │
│  ├─ User (Staff)                                                 │
│  ├─ ActivityLog                                                  │
│  └─ Feedback                                                     │
│                                                                   │
└──── DATABASE ──────────────────────────────────────────────────┘
│  All tables (same as Patient Module + additional)               │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

### 2.3 QUEUE & OPERATIONAL MODULE

```
┌─────────────────────────────────────────────────────────────────┐
│              QUEUE & OPERATIONS MODULE                          │
│         (Real-time Queue Management & Optimization)             │
└─────────────────────────────────────────────────────────────────┘

┌──── PRESENTATION LAYER ─────────────────────────────────────────┐
│                                                                   │
│  Controllers (API):                                              │
│  ├─ Api/QueueController (queue operations - JSON API)          │
│  │   ├─ checkIn()            → Check-in patient                 │
│  │   ├─ createWalkIn()       → Register walk-in                 │
│  │   ├─ getNextPatient()     → Get next in queue                │
│  │   ├─ getQueueStatus()     → Get queue details                │
│  │   ├─ updateQueueStatus()  → Update queue state               │
│  │   ├─ getRoomStatus()      → Room status                      │
│  │   ├─ getQueueStats()      → Queue analytics                  │
│  │   ├─ autoMarkLate()       → Mark late patients               │
│  │   └─ autoMarkNoShow()     → Mark no-shows                    │
│  │                                                                │
│  Views:                                                          │
│  ├─ public/queue-board.blade.php (public display)              │
│  └─ staff/appointments.blade.php (staff queue view)             │
│                                                                   │
└──── BUSINESS LOGIC LAYER ──────────────────────────────────────┘
│                                                                   │
│  Services:                                                        │
│  ├─ QueueAssignmentService (CORE)                              │
│  │   ├─ assignNextPatient()                                     │
│  │   │   └─ Automatically assigns next waiting patient          │
│  │   │   └─ Selects: room, dentist, patient                     │
│  │   │   └─ Considers: availability, workload, preferences      │
│  │   │                                                           │
│  │   ├─ startTreatment()                                        │
│  │   │   └─ Marks patient as "in service"                       │
│  │   │   └─ Records start time                                  │
│  │   │                                                           │
│  │   ├─ completeTreatment()                                     │
│  │   │   └─ Marks patient as "completed"                        │
│  │   │   └─ Frees room and dentist                              │
│  │   │   └─ Triggers automatic next assignment                  │
│  │   │                                                           │
│  │   ├─ getEstimatedWaitTime()                                  │
│  │   │   └─ Calculates remaining wait time                      │
│  │   │                                                           │
│  │   ├─ getQueueStats()                                         │
│  │   │   └─ Returns: queue length, avg wait, throughput        │
│  │   │                                                           │
│  │   └─ findAvailable[Room/Dentist]()                          │
│  │       └─ Resource allocation logic                           │
│  │                                                               │
│  ├─ CheckInService (entry point)                               │
│  │   ├─ checkIn()                                               │
│  │   │   ├─ Validates appointment                               │
│  │   │   ├─ Creates Queue record                                │
│  │   │   ├─ Checks if late                                      │
│  │   │   └─ Triggers assignment if on time                      │
│  │   │                                                           │
│  │   ├─ validateCheckIn()                                       │
│  │   │   ├─ Appointment exists                                  │
│  │   │   ├─ Not already checked in                              │
│  │   │   ├─ Not cancelled/no-show                               │
│  │   │   └─ Within operating hours                              │
│  │   │                                                           │
│  │   └─ isLate() + checkInLate()                                │
│  │       └─ Handles late arrivals                               │
│  │                                                               │
│  └─ LateNoShowService (status automation)                       │
│      ├─ markLateAppointments()                                  │
│      │   └─ Auto-mark as late after X mins from appointment    │
│      │                                                           │
│      ├─ markNoShowAppointments()                                │
│      │   └─ Auto-mark no-show after appointment time expires   │
│      │                                                           │
│      └─ handleDentistUnavailable()                              │
│          └─ Reassign queue if dentist unavailable              │
│                                                                   │
└──── DATA LAYER ────────────────────────────────────────────────┘
│                                                                   │
│  Models:                                                          │
│  ├─ Queue (core model for queue operations)                     │
│  │   ├─ Attributes: queue_status, check_in_time,               │
│  │   │              appointment_id, dentist_id, room_id         │
│  │   │              start_treatment_time, end_treatment_time    │
│  │   └─ Relationships: appointment, dentist, room               │
│  │                                                               │
│  ├─ Appointment                                                  │
│  │   └─ Relationships: patient info, appointment details        │
│  │                                                               │
│  ├─ Dentist & DentistSchedule                                  │
│  │   └─ Availability & workload data                            │
│  │                                                               │
│  └─ Room                                                         │
│      └─ Capacity & availability                                 │
│                                                                   │
└──── DATABASE ──────────────────────────────────────────────────┘
│                                                                   │
│  Tables:                                                          │
│  ├─ queues (core table)                                         │
│  │   ├─ id, appointment_id, queue_status                        │
│  │   ├─ dentist_id, room_id                                     │
│  │   ├─ check_in_time, start_treatment_time, end_treatment_time
│  │   └─ created_at, updated_at                                 │
│  │                                                               │
│  ├─ appointments                                                │
│  ├─ dentist_schedules                                           │
│  └─ rooms                                                        │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

### 2.4 ADMIN/SYSTEM MODULE

```
┌─────────────────────────────────────────────────────────────────┐
│               ADMIN/SYSTEM MODULE                               │
│            (System Configuration & Monitoring)                  │
└─────────────────────────────────────────────────────────────────┘

┌──── PRESENTATION LAYER ─────────────────────────────────────────┐
│                                                                   │
│  Controllers:                                                     │
│  ├─ HomeController (dashboard)                                  │
│  │   └─ index() → Admin/user dashboard                          │
│  │                                                                │
│  └─ PastController (data recovery)                              │
│      ├─ index()                → View deleted records            │
│      ├─ restoreDentist/Staff() → Restore deleted items          │
│      └─ forceDelete*()         → Permanently delete             │
│                                                                   │
│  Views:                                                          │
│  └─ home.blade.php, past.blade.php, etc.                       │
│                                                                   │
└──── BUSINESS LOGIC LAYER ──────────────────────────────────────┘
│                                                                   │
│  Services:                                                        │
│  ├─ ActivityLogger (system audit)                              │
│  │   └─ log() → Record all system actions                       │
│  │                                                                │
│  └─ WhatsAppSender (communication)                             │
│      └─ sendAppointmentConfirmation()                          │
│                                                                   │
└──── DATA LAYER ────────────────────────────────────────────────┘
│                                                                   │
│  Models:                                                          │
│  ├─ ActivityLog (audit trail)                                  │
│  │   ├─ user_id, user_name                                     │
│  │   ├─ action (create|update|delete)                          │
│  │   ├─ model_type, model_id                                   │
│  │   ├─ old_values, new_values (JSON)                          │
│  │   ├─ ip_address                                             │
│  │   └─ created_at                                             │
│  │                                                                │
│  └─ User (staff/admin accounts)                                 │
│      ├─ role (staff|admin)                                     │
│      ├─ public_visible flag                                    │
│      └─ Soft deletes                                           │
│                                                                   │
└──── DATABASE ──────────────────────────────────────────────────┘
│                                                                   │
│  Tables:                                                          │
│  ├─ activity_logs                                               │
│  └─ users                                                        │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. CROSS-CUTTING CONCERNS

```
┌─────────────────────────────────────────────────────────────────┐
│            CROSS-CUTTING FEATURES                               │
│      (Features that span across all modules)                    │
└─────────────────────────────────────────────────────────────────┘

┌──── AUTHENTICATION & SECURITY ──────────────────────────────────┐
│  ├─ Controllers/Auth/* (Login, Register, Password Reset)       │
│  ├─ Middleware/RoleMiddleware (role-based access control)      │
│  ├─ Traits: SoftDeletes (for data recovery)                    │
│  └─ Activity Logging (audit trail for all actions)             │
└────────────────────────────────────────────────────────────────┘

┌──── EMAIL & NOTIFICATIONS ──────────────────────────────────────┐
│  ├─ Mail/AppointmentConfirmation (email class)                 │
│  ├─ Services/WhatsAppSender (messaging service)                │
│  └─ Views/emails/* (email templates)                           │
└────────────────────────────────────────────────────────────────┘

┌──── DATA PERSISTENCE & CACHING ─────────────────────────────────┐
│  ├─ Models/* (Eloquent ORM)                                    │
│  ├─ Soft Deletes (data preservation)                           │
│  ├─ Timestamps (automatic created_at, updated_at)              │
│  └─ Database transactions (ACID compliance)                    │
└────────────────────────────────────────────────────────────────┘

┌──── COMMAND LINE INTERFACE ─────────────────────────────────────┐
│  └─ Console/Commands/AssignQueueNumbers.php                    │
│      └─ Scheduled task for automatic queue assignment          │
└────────────────────────────────────────────────────────────────┘

┌──── FRONTEND BUILD & STYLING ───────────────────────────────────┐
│  ├─ js/app.js, bootstrap.js, calendar.js                       │
│  ├─ sass/app.scss, _variables.scss                             │
│  ├─ css/app.css                                                │
│  ├─ layouts/*.blade.php (master templates)                     │
│  └─ partials/* (reusable components)                           │
└────────────────────────────────────────────────────────────────┘
```

---

## 4. DATA FLOW DIAGRAMS

### 4.1 PATIENT APPOINTMENT BOOKING FLOW

```
Patient User
    │
    ├─→ GET /book
    │   └─ AppointmentController::create()
    │      └─ Show booking form with:
    │         ├─ Services (from Service model)
    │         ├─ Dentists (from Dentist model)
    │         └─ Operating Hours (from OperatingHour model)
    │
    ├─→ POST /book
    │   └─ AppointmentController::store()
    │      ├─ Validate input
    │      ├─ Check availability (dentist, room, hours)
    │      ├─ Create Appointment record
    │      ├─ Generate visit_token & visit_code
    │      ├─ Send email confirmation
    │      ├─ Send WhatsApp reminder
    │      └─ Store ActivityLog (booking created)
    │
    └─→ GET /visit/{token}
        └─ AppointmentController::visitStatus()
           ├─ Find appointment by token
           ├─ Check Queue status
           └─ Return status to patient
```

### 4.2 PATIENT CHECK-IN & QUEUE FLOW

```
Patient User / Staff
    │
    ├─→ POST /api/check-in (or /checkin)
    │   └─ QueueController::checkIn() OR AppointmentController::checkinSubmit()
    │      ├─ Call CheckInService::validateCheckIn()
    │      │  ├─ Check appointment exists
    │      │  ├─ Not already checked in
    │      │  └─ Not cancelled
    │      │
    │      ├─ Call CheckInService::isLate()
    │      │  └─ Compare arrival time vs appointment time
    │      │
    │      └─ If on time:
    │         └─ Call CheckInService::checkIn()
    │            ├─ Create Queue record with status='waiting'
    │            ├─ Record check_in_time
    │            ├─ Call QueueAssignmentService::assignNextPatient()
    │            │  ├─ Find next waiting patient
    │            │  ├─ Find available Room
    │            │  ├─ Find available Dentist
    │            │  ├─ Update Queue (assign room, dentist)
    │            │  ├─ Update Appointment status
    │            │  └─ Log ActivityLog
    │            └─ Return Queue record with assignments
    │
    └─ Queue Status Update:
       ├─ 'waiting' → assigned room & dentist
       ├─ 'called'  → dentist calling patient
       ├─ 'in_service' → treatment started
       └─ 'completed' → treatment finished
```

### 4.3 STAFF QUEUE MANAGEMENT FLOW

```
Staff User (Dentist/Receptionist)
    │
    ├─→ GET /staff/appointments
    │   └─ AppointmentController::index()
    │      ├─ List all appointments (filtered by status)
    │      ├─ Show queue positions
    │      └─ Display real-time updates
    │
    ├─→ POST /staff/checkin/{id}
    │   └─ AppointmentController::checkIn()
    │      └─ Manual check-in (same as patient self check-in)
    │
    ├─→ POST /api/queue/{queue}/status
    │   └─ QueueController::updateQueueStatus()
    │      ├─ Validate queue exists
    │      ├─ Update queue_status
    │      │  ├─ waiting → called
    │      │  ├─ called → in_service
    │      │  └─ in_service → completed
    │      ├─ Record timestamps
    │      └─ Log ActivityLog
    │
    ├─→ GET /api/queue/next
    │   └─ QueueController::getNextPatient()
    │      ├─ Call QueueAssignmentService::assignNextPatient()
    │      └─ Return next waiting patient
    │
    └─→ GET /api/queue/stats
        └─ QueueController::getQueueStats()
           └─ QueueAssignmentService::getQueueStats()
              └─ Return: queue length, avg wait, stats
```

### 4.4 LATE & NO-SHOW AUTOMATION FLOW

```
Scheduled Task (Console/Commands/AssignQueueNumbers.php)
    │
    ├─→ POST /api/auto-mark-late
    │   └─ QueueController::autoMarkLate()
    │      ├─ Call LateNoShowService::markLateAppointments(15)
    │      └─ Find appointments with:
    │         └─ check_in_time > appointment_time + 15 mins
    │         └─ status = 'in_queue' or 'waiting'
    │         └─ Update status = 'late'
    │         └─ Log ActivityLog
    │
    └─→ POST /api/auto-mark-no-show
        └─ QueueController::autoMarkNoShow()
           ├─ Call LateNoShowService::markNoShowAppointments(30)
           └─ Find appointments with:
              └─ appointment_time + 30 mins < now
              └─ NOT checked in
              └─ status != 'completed'
              └─ Update status = 'no_show'
              └─ Log ActivityLog
```

---

## 5. MODEL RELATIONSHIPS DIAGRAM

```
┌────────────────────────────────────────────────────────────────┐
│                    DATA MODEL RELATIONSHIPS                     │
└────────────────────────────────────────────────────────────────┘

    User
     ├─ hasMany → ActivityLog
     ├─ hasMany → Appointment (staff-created)
     └─ can be → Staff (public_visible)
     
    Appointment (1)
     ├─ belongsTo ← User (creator)
     ├─ belongsTo ← Service
     ├─ belongsTo ← Dentist
     ├─ belongsTo ← Room (assigned)
     ├─ hasOne ← Queue
     └─ hasMany ← Feedback
     
    Queue (1)
     ├─ belongsTo ← Appointment
     ├─ belongsTo ← Dentist
     ├─ belongsTo ← Room
     └─ Tracks: check_in_time, start_treatment_time, end_treatment_time
     
    Dentist (1)
     ├─ hasMany ← DentistSchedule
     ├─ hasMany ← DentistLeave
     ├─ hasMany ← Appointment
     ├─ hasMany ← Queue
     └─ hasMany ← Feedback
     
    DentistSchedule (M)
     ├─ belongsTo ← Dentist
     └─ Defines: working hours per day
     
    DentistLeave (M)
     ├─ belongsTo ← Dentist
     └─ Marks: absence periods
     
    Service (1)
     ├─ hasMany ← Appointment
     ├─ hasMany ← Feedback
     └─ Defines: estimated_duration
     
    Room (1)
     ├─ hasMany ← Queue
     ├─ hasMany ← Appointment
     └─ Status: active/inactive
     
    OperatingHour (M)
     └─ Defines: clinic hours per day/location
     
    Feedback (M)
     ├─ belongsTo ← Appointment
     ├─ belongsTo ← Dentist
     └─ belongsTo ← Service
     
    ActivityLog (M)
     ├─ belongsTo ← User
     └─ Records: all system actions with audit trail
     
    DentistLeave (M)
     ├─ belongsTo ← Dentist
     └─ Marks: leave periods
```

---

## 6. KEY SERVICES INTERACTION DIAGRAM

```
┌────────────────────────────────────────────────────────────────┐
│              SERVICE LAYER INTERACTIONS                         │
└────────────────────────────────────────────────────────────────┘

    QueueAssignmentService (Core Service)
         │
         ├─→ assignNextPatient()
         │    ├─ Queries: Queue (waiting), Appointment, Room, Dentist
         │    ├─ Transaction: Update Queue with assignments
         │    └─ Calls: ActivityLogger::log()
         │
         ├─→ startTreatment(Queue)
         │    ├─ Updates: Queue status → 'in_service'
         │    ├─ Records: start_treatment_time
         │    └─ Calls: ActivityLogger::log()
         │
         ├─→ completeTreatment(Queue)
         │    ├─ Updates: Queue status → 'completed'
         │    ├─ Records: end_treatment_time
         │    ├─ Calls: ActivityLogger::log()
         │    └─ Triggers: Auto-assignment of next patient
         │
         └─→ getQueueStats()
              └─ Returns: queue length, wait time, stats
    
    CheckInService (Entry Point Service)
         │
         ├─→ validateCheckIn(Appointment)
         │    └─ Validates: appointment exists, status, availability
         │
         ├─→ checkIn(Appointment)
         │    ├─ Creates: Queue record
         │    ├─ Sets: status = 'waiting'
         │    ├─ Records: check_in_time
         │    ├─ Calls: QueueAssignmentService::assignNextPatient()
         │    └─ Calls: ActivityLogger::log()
         │
         └─→ isLate(Appointment) + checkInLate()
              └─ Handles: late arrivals
    
    LateNoShowService (Status Automation)
         │
         ├─→ markLateAppointments()
         │    └─ Auto-marks: late patients (>15 mins after appt time)
         │
         └─→ markNoShowAppointments()
              └─ Auto-marks: no-shows (>30 mins after appt time)
    
    ActivityLogger (Audit Trail)
         └─→ log(action, modelType, modelId, description, oldValues, newValues)
              └─ Creates: ActivityLog record with timestamp, user, IP
    
    WhatsAppSender (Communication)
         └─→ sendAppointmentConfirmation(Appointment)
              └─ Sends: WhatsApp/SMS reminder to patient
```

---

## 7. MODULE DEPENDENCY MATRIX

```
┌──────────────────────────────────────────────────────────────────┐
│          Module Dependencies (What depends on what)              │
└──────────────────────────────────────────────────────────────────┘

PATIENT MODULE
├─ depends on: (nothing - entry point)
└─ used by: Queue Module (via check-in)

STAFF MODULE
├─ depends on: Data Models, Services
└─ used by: Admin Module

QUEUE MODULE
├─ depends on: Patient Module (appointments), Data Models, Services
│             (CheckInService, QueueAssignmentService, LateNoShowService)
└─ used by: Patient Module, Staff Module

ADMIN MODULE
├─ depends on: All other modules
└─ used by: System management

CROSS-CUTTING (used by all modules)
├─ ActivityLogger
├─ WhatsAppSender
├─ RoleMiddleware
├─ Authentication
└─ Database Models
```

---

## 8. DEPLOYMENT ARCHITECTURE LAYERS

```
┌──────────────────────────────────────────────────────────────────┐
│              DEPLOYMENT ARCHITECTURE                             │
└──────────────────────────────────────────────────────────────────┘

┌─ WEB TIER ──────────────────────────────────────────────────────┐
│                                                                   │
│  Browser/Client
│      ↓ (HTTP/HTTPS)
│  Nginx/Apache (Web Server)
│      ↓
│  Laravel Vite (Asset serving - CSS/JS/Images)
│                                                                   │
└────────────────────────────────────────────────────────────────┘

┌─ APPLICATION TIER ──────────────────────────────────────────────┐
│                                                                   │
│  Laravel Application (php artisan serve)
│  ├─ Routing (web.php, api routes)
│  ├─ Controllers & Middleware
│  ├─ Business Logic (Services)
│  ├─ Views (Blade Templates)
│  ├─ Models (Eloquent ORM)
│  └─ Commands (Console)
│                                                                   │
│  Queue Worker (optional)
│  └─ Processes: php artisan queue:listen
│                                                                   │
└────────────────────────────────────────────────────────────────┘

┌─ DATA TIER ─────────────────────────────────────────────────────┐
│                                                                   │
│  Database (SQLite/MySQL)
│  ├─ Tables (appointments, queues, dentists, etc.)
│  ├─ Indexes (performance optimization)
│  └─ Transactions (data consistency)
│                                                                   │
│  Cache Store (Database/Redis)
│  └─ Stores: session, query cache, configuration
│                                                                   │
│  File Storage (Local/S3)
│  └─ Stores: appointments, dentist photos, etc.
│                                                                   │
└────────────────────────────────────────────────────────────────┘

┌─ EXTERNAL SERVICES ────────────────────────────────────────────┐
│                                                                   │
│  Email Service (SMTP/Mailgun/SES)
│  ├─ AppointmentConfirmation mails
│  └─ Password reset emails
│                                                                   │
│  SMS/WhatsApp Service
│  └─ Appointment reminders
│  └─ Notification delivery
│                                                                   │
└────────────────────────────────────────────────────────────────┘
```

---

## 9. FEATURE TO MODULE MAPPING

```
┌────────────────────────────────────────────────────────────────┐
│        Features → Module Mapping                                │
└────────────────────────────────────────────────────────────────┘

PATIENT FEATURES
├─ Book Appointment → PATIENT MODULE + Queue Module
├─ Track Appointment → PATIENT MODULE
├─ Self Check-In → PATIENT MODULE + Queue Module
├─ View Clinic Info → PATIENT MODULE
├─ Feedback Submission → PATIENT MODULE
└─ Chatbot → PATIENT MODULE

STAFF FEATURES
├─ Manage Appointments → STAFF MODULE
├─ View Queue → QUEUE MODULE
├─ Manage Dentists → STAFF MODULE
├─ Manage Services → STAFF MODULE
├─ Manage Rooms → STAFF MODULE
├─ Operating Hours → STAFF MODULE
├─ Quick Edit → STAFF MODULE
├─ Activity Logs → ADMIN MODULE
├─ Feedback Management → STAFF MODULE
├─ Calendar → STAFF MODULE
└─ Developer Tools → ADMIN MODULE

SYSTEM FEATURES
├─ Authentication → Cross-cutting (all modules)
├─ Authorization → Cross-cutting (RoleMiddleware)
├─ Activity Logging → ADMIN MODULE (cross-cutting)
├─ Data Recovery → ADMIN MODULE
├─ Email Notifications → Cross-cutting
├─ SMS/WhatsApp → Cross-cutting
└─ Database Transactions → Data layer (cross-cutting)
```

---

## 10. SUMMARY

### Module Count: 4 Main Modules
1. **PATIENT MODULE** - Public-facing appointment & information portal
2. **STAFF MODULE** - Operational management & daily tasks
3. **QUEUE MODULE** - Real-time queue management & optimization (core differentiator)
4. **ADMIN MODULE** - System configuration & audit

### Key Characteristics
- **Layered Architecture:** Presentation → Business Logic → Data
- **Service-Oriented:** Business logic in services, not controllers
- **Database-Centric:** Eloquent ORM with relationships
- **Event-Driven:** Activity logging on all actions
- **Multi-Tenant Ready:** Clinic location separation (Seremban, Kuala Pilah)
- **Real-Time Capable:** Queue assignment service with optimization

### Total Components
- **13 Main Controllers** (spread across 3 modules)
- **5 Business Services** (core logic)
- **11 Data Models** (Eloquent)
- **50+ API Endpoints** (routes)
- **25+ Views** (Blade templates)
- **2 Layout Templates** (staff, public)

---

**Diagram Version:** 1.0  
**Created:** December 23, 2025  
**Architecture Pattern:** Laravel Layered MVC + Services
