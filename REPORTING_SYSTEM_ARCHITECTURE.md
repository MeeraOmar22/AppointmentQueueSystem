# Reporting System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                   Clinic Management System                       │
│                   with Reporting Module                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                ┌─────────────┴─────────────┐
                │                           │
        ┌───────▼────────┐        ┌────────▼──────────┐
        │  Staff/Admin   │        │   Patient Users   │
        │   Dashboard    │        │    Dashboard      │
        └───────┬────────┘        └────────┬──────────┘
                │                           │
        ┌───────▼────────────────────────────▼─────────────┐
        │        Authentication Layer (Middleware)        │
        │  ✓ role:staff|developer  ✓ auth  ✓ CSRF       │
        └───────┬────────────────────────────┬────────────┘
                │                            │
        ┌───────▼─────────────┐      ┌──────▼──────────────┐
        │  Staff Reports      │      │  Patient Reports    │
        │  Controllers        │      │  Controllers        │
        └───────┬─────────────┘      └──────┬──────────────┘
                │                            │
    ┌───────────┼───────────┬────────┐      │
    │           │           │        │      │
    │      ┌────▼─┐    ┌───▼──┐   ┌─▼──┐   │
    │      │Report│    │Appt  │   │Rev │   │
    │      │Dash  │    │Analy │   │Rpt │   │
    │      │board │    │sis   │   │    │   │
    │      └────┬─┘    └───┬──┘   └─┬──┘   │
    │           │          │       │        │
    │    ┌──────▼──────────▼───────▼──┐    │
    │    │   Blade Views (Charts,     │    │
    │    │   Tables, Filters)         │    │
    │    └──────────────────────────────┘   │
    │                                        │
    └────────────────────────────────────────┘
                    │
        ┌───────────▼──────────┐
        │  Database Queries    │
        │  (Eloquent ORM)      │
        │  ✓ Appointments      │
        │  ✓ Services          │
        │  ✓ Dentists          │
        │  ✓ Feedback          │
        └──────────────────────┘
```

---

## Request Flow Diagram

### Staff Report Request Flow

```
1. Staff clicks "Reports & Analytics" in sidebar
                        │
                        ▼
2. Routes to /staff/reports/dashboard (role:staff|developer middleware)
                        │
                        ▼
3. ReportController@dashboard method
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
    Query DB        Process        Calculate
    Appts,          Filters        Metrics
    Services
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
    Total          Revenue          Dentist
    Appts          Data             Performance
                        │
                        ▼
    4. Pass data to view (dashboard.blade.php)
                        │
                        ▼
    5. Render HTML with:
        - KPI Cards
        - Tables
        - Charts
        - Filter Form
                        │
                        ▼
    6. Display to browser
```

### Patient Report Request Flow

```
1. Patient clicks "My Reports" > "My Appointments"
                        │
                        ▼
2. Routes to /my-reports/appointments (auth middleware)
                        │
                        ▼
3. PatientReportController@appointmentHistory method
                        │
        ┌───────────────┴───────────────┐
        │                               │
        ▼                               ▼
    Authenticate              Get user email
    User                      from Auth::user()
                        │
                        ▼
    Query DB for appointments
    WHERE patient_email = auth()->user()->email
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
    Count All    Count Completed    Count
    Appts        Appts              Cancelled
                        │
                        ▼
    4. Pass data to view (appointments.blade.php)
                        │
                        ▼
    5. Render HTML with:
        - Summary cards
        - Appointment table
        - Pagination
        - Quick links
                        │
                        ▼
    6. Display to browser
```

---

## Data Model Relationships

```
┌─────────────────┐
│   Appointment   │
├─────────────────┤
│ id              │
│ patient_email   │◄──────┐
│ service_id      │       │
│ dentist_id      │       │
│ appointment_date│       │
│ appointment_time│       │
│ status          │       │
│ notes           │       │
└─────────────────┘       │
        │                 │
        │ has_one         │
        └────────────────►│ Feedback
                          │ (via appointment)

┌──────────────┐          ┌─────────────────┐
│   Service    │◄─────────┤   Appointment   │
├──────────────┤  many    ├─────────────────┤
│ id           │          │ service_id      │
│ name         │          └─────────────────┘
│ price        │                   │
└──────────────┘                   │
                                   │ many
                    ┌──────────────┘
                    │
                    ▼
            ┌──────────────┐
            │   Dentist    │
            ├──────────────┤
            │ id           │
            │ name         │
            │ status       │
            └──────────────┘

┌──────────────┐          ┌──────────────┐
│  Appointment │◄─────────┤   Feedback   │
├──────────────┤  many    ├──────────────┤
│ id           │          │ appointment_ │
└──────────────┘          │ id           │
                          │ rating       │
                          │ comment      │
                          │ created_at   │
                          └──────────────┘
```

---

## URL Routes Map

### Staff Routes (Protected: role:staff|developer)
```
/staff/reports/
├── dashboard          → ReportController@dashboard
├── appointments       → ReportController@appointmentAnalysis
├── revenue           → ReportController@revenueReport
└── export-appointments → ReportController@exportAppointments
```

### Patient Routes (Protected: auth)
```
/my-reports/
├── appointments      → PatientReportController@appointmentHistory
├── treatments        → PatientReportController@treatmentHistory
├── feedback          → PatientReportController@myFeedback
└── export           → PatientReportController@exportRecords
```

---

## File Structure

```
app/Http/Controllers/
├── Staff/
│   └── ReportController.php (171 lines)
│       ├── dashboard()              → KPI overview
│       ├── appointmentAnalysis()    → Detailed breakdown
│       ├── revenueReport()          → Financial analytics
│       └── exportAppointments()     → CSV download
│
└── PatientReportController.php (126 lines)
    ├── appointmentHistory()         → All appointments
    ├── treatmentHistory()           → Completed treatments
    ├── myFeedback()                 → Feedback submitted
    └── exportRecords()              → Personal CSV export

resources/views/
├── staff/reports/
│   ├── dashboard.blade.php          → KPI cards & charts
│   ├── appointment-analysis.blade.php → Detailed table
│   └── revenue-report.blade.php     → Service breakdown
│
├── patient/reports/
│   ├── appointments.blade.php       → Appointment list
│   ├── treatments.blade.php         → Treatment history
│   └── feedback.blade.php           → Feedback with ratings
│
└── layouts/
    ├── staff.blade.php              (modified: added sidebar menu)
    └── app.blade.php                (modified: added navbar dropdown)

routes/
└── web.php                           (modified: added 8 routes)
```

---

## Database Query Examples

### Staff Dashboard Queries

```php
// Total appointments in date range
Appointment::whereBetween('appointment_date', [$from, $to])->count()

// Appointments by status
Appointment::where('status', 'completed')
    ->whereBetween('appointment_date', [$from, $to])
    ->count()

// Revenue by service
Appointment::select('services.name', 'services.cost')
    ->join('services', 'appointments.service_id', '=', 'services.id')
    ->where('status', 'completed')
    ->groupBy('services.id')
    ->selectRaw('COUNT(*) as count, SUM(services.cost) as total_revenue')

// Dentist performance
Appointment::select('dentists.id', 'dentists.name')
    ->join('dentists', 'appointments.dentist_id', '=', 'dentists.id')
    ->where('status', 'completed')
    ->groupBy('dentists.id')
    ->selectRaw('COUNT(*) as appointments_completed')
```

### Patient Report Queries

```php
// Patient's appointments (filtered by email)
Appointment::where('patient_email', auth()->user()->email)
    ->orderBy('appointment_date', 'desc')
    ->paginate(15)

// Patient's completed treatments
Appointment::where('patient_email', auth()->user()->email)
    ->where('status', 'completed')
    ->with('service', 'dentist')
    ->paginate(20)

// Patient's feedback
Feedback::whereHas('appointment', function($q) {
    $q->where('patient_email', auth()->user()->email);
})
->with('appointment.service', 'appointment.dentist')
->paginate(20)
```

---

## Data Flow Summary

```
┌──────────────┐
│   Browser    │  GET /staff/reports/dashboard
└──────────────┘
       │
       │ HTTP Request
       │
       ▼
┌──────────────────────────┐
│   Route (web.php)        │
│   /staff/reports/*       │
└──────────────────────────┘
       │
       │ Match route
       │
       ▼
┌──────────────────────────┐
│ Middleware Stack         │
│ ✓ auth                   │
│ ✓ role:staff|developer   │
└──────────────────────────┘
       │
       │ Pass middleware
       │
       ▼
┌──────────────────────────┐
│ Controller Method        │
│ ReportController         │
│ @dashboard               │
└──────────────────────────┘
       │
       │ Query database
       │
       ▼
┌──────────────────────────┐
│ Database (MySQL/Postgres)│
│ Query: appointments,     │
│ services, dentists,      │
│ feedback tables          │
└──────────────────────────┘
       │
       │ Return data
       │
       ▼
┌──────────────────────────┐
│ Process/Aggregate Data   │
│ Calculate metrics        │
│ Format for display       │
└──────────────────────────┘
       │
       │ Pass to view
       │
       ▼
┌──────────────────────────┐
│ Blade View               │
│ dashboard.blade.php      │
│ Render HTML + Charts     │
└──────────────────────────┘
       │
       │ Return HTML
       │
       ▼
┌──────────────────────────┐
│   Browser                │
│   Display Report         │
└──────────────────────────┘
```

---

## Security & Access Control

```
┌─────────────────────────────────────────────────────────┐
│                   Request Comes In                      │
└─────────────────────────────────┬───────────────────────┘
                                  │
                         ┌────────▼────────┐
                         │  Is User Auth'd? │
                         └────────┬────────┘
                                  │
                    Yes           │           No
                  ┌───────────────┘
                  │
                  ▼
        ┌─────────────────┐
        │ Check User Role │
        └────────┬────────┘
                 │
        ┌────────┼────────┐
        │        │        │
    staff    developer  patient
        │        │        │
        └────┬───┘        │
             │            │
             ▼            ▼
        ┌─────────┐   ┌──────────┐
        │ Approve │   │ Approve  │
        │ Report  │   │ Patient  │
        │ Access  │   │ Reports  │
        └────┬────┘   └──┬───────┘
             │          │
             │    Filter by
             │    user email
             │
             ▼
        ┌─────────────────┐
        │  Load Permitted │
        │  Data Only      │
        └────────┬────────┘
                 │
                 ▼
        ┌─────────────────┐
        │ Render Report   │
        └─────────────────┘
```

---

## Performance Optimization

```
Query Optimization:
├── Use database indexes
│   └── appointment_date, patient_email, status
├── Eager loading
│   ├── ->with('service', 'dentist')
│   └── Prevents N+1 queries
├── Pagination
│   └── 15-20 items per page
└── Aggregations
    └── COUNT, SUM in database

Caching:
├── Route caching (via optimize:clear)
├── Config caching
├── View caching
└── No query caching (real-time data)

Frontend Optimization:
├── Bootstrap 5 CDN (cached)
├── FontAwesome CDN (cached)
├── Minimal JavaScript
└── CSS inline for critical styles
```

---

This architecture provides a robust, secure, and scalable reporting system for both administrative users and patients.
