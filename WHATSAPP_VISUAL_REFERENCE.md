# WhatsApp Configuration - Visual Reference

## System Flow Chart

```
┌────────────────────────────────────────────────────────────────┐
│                    APPOINTMENT BOOKING                          │
└────────────────────┬───────────────────────────────────────────┘
                     │
                     ▼
         ┌──────────────────────────┐
         │ User Books Appointment   │
         │ Click Submit             │
         └────────────┬─────────────┘
                      │
        ┌─────────────┴─────────────┐
        │                           │
   [TODAY]                   [FUTURE DATE]
        │                           │
        ▼                           ▼
┌──────────────────────┐  ┌──────────────────────┐
│ Send Confirmation    │  │ Send Confirmation    │
│ WITH:                │  │ WITHOUT:              │
│ • Tracking link      │  │ • No tracking link    │
│ • Check-in link      │  │ • "We'll send on day" │
│ • Time info          │  │ • Time info           │
└──────────────────────┘  └──────────────────────┘
        │                           │
        ▼                           ▼
    SENT ✓                      SENT ✓
                                    │
                        [NEXT DAY 7:45 AM]
                                    │
                                    ▼
                            ┌──────────────────────┐
                            │ Send Today Reminder  │
                            │ WITH:                │
                            │ • Tracking link      │
                            │ • Check-in link      │
                            │ • Time info          │
                            └──────────────────────┘
                                    │
                                    ▼
                                SENT ✓
```

---

## Message Timeline

```
PATIENT BOOKS TODAY'S APPOINTMENT AT 2:30 PM
│
├─ 14:30 User clicks "Confirm Booking"
├─ 14:31 ✓ Confirmation sent (with links)
├─ 14:35 Patient sees WhatsApp notification
├─ 14:40 Patient opens tracking link
├─ 14:45 Patient arrives at clinic
├─ 14:46 Patient uses check-in link from WhatsApp
└─ 15:00 Appointment starts


PATIENT BOOKS APPOINTMENT FOR TOMORROW AT 10 AM
│
├─ Mon 14:00 User clicks "Confirm Booking"
├─ Mon 14:01 ✓ Confirmation sent (no links yet)
├─ Mon 14:05 Patient sees: "We'll send you a tracking link..."
├─ Mon 23:59 Patient waits overnight
│
├─ Tue 07:45 Scheduler: appointments:send-reminders
├─ Tue 07:46 ✓ Today reminder sent (WITH links)
├─ Tue 08:00 Patient sees WhatsApp: "Your appointment is at 10:00"
├─ Tue 09:50 Patient opens tracking link
├─ Tue 09:55 Patient arrives at clinic
├─ Tue 09:56 Patient uses check-in link
└─ Tue 10:00 Appointment starts


PATIENT BOOKS APPOINTMENT FOR NEXT WEEK AT 3 PM
│
├─ Mon 15:00 User clicks "Confirm Booking"
├─ Mon 15:01 ✓ Confirmation sent (no links)
├─ Mon 15:05 Patient sees: "Appointment confirmed..."
│
├─ Tue 10:00 Scheduler: appointments:send-reminders-24h
├─ Tue 10:01 ✓ 24h reminder sent (still no links)
├─ Tue 10:05 Patient sees: "Reminder: appointment tomorrow..."
│
├─ Wed 07:45 Scheduler: appointments:send-reminders
├─ Wed 07:46 ✓ Today reminder sent (NOW with links)
├─ Wed 08:00 Patient sees: "Appointment today at 15:00 [LINKS]"
├─ Wed 14:50 Patient opens tracking link
├─ Wed 14:55 Patient arrives at clinic
├─ Wed 14:56 Patient uses check-in link
└─ Wed 15:00 Appointment starts
```

---

## Message Content Examples

### CONFIRMATION MESSAGE (Future Date)
```
┌─────────────────────────────────────────┐
│  🦷 Dental Clinic Appointment Confirmed │
│                                         │
│  Hi John,                               │
│  Your appointment is confirmed for      │
│  15 Jan 2026, 14:00.                    │
│                                         │
│  Please arrive 5-10 minutes early.      │
│                                         │
│  We'll send you a tracking link on      │
│  the day of your appointment.           │
└─────────────────────────────────────────┘
```

### TODAY'S REMINDER MESSAGE
```
┌─────────────────────────────────────────┐
│  🦷 Appointment Today!                  │
│                                         │
│  Hi John,                               │
│  Your appointment is at 14:00 today.    │
│                                         │
│  📍 Track Queue:                        │
│  http://localhost:8000/visit/abc...     │
│  (TAP TO VIEW LIVE QUEUE)               │
│                                         │
│  ✅ Quick Check-In:                     │
│  http://localhost:8000/checkin?t=...    │
│  (TAP TO CHECK IN)                      │
│                                         │
│  Tap the links when you're ready.       │
│  See you soon! 😊                       │
└─────────────────────────────────────────┘
```

### 24-HOUR REMINDER MESSAGE
```
┌─────────────────────────────────────────┐
│  🦷 Appointment Reminder                │
│                                         │
│  Hi John,                               │
│  Reminder: Your appointment is          │
│  tomorrow (15 Jan 2026) at 14:00.       │
│                                         │
│  Please arrive 5-10 minutes early.      │
│  See you then! 👋                       │
└─────────────────────────────────────────┘
```

---

## Scheduled Tasks Calendar

```
TIME         TASK                           RUN ON    AFFECTED APPOINTMENTS
─────────────────────────────────────────────────────────────────────────────
07:30 AM     queue:assign-today             Daily     Today's appointments
             (Assign queue numbers)                   (not WhatsApp related)

07:45 AM     appointments:send-reminders    Daily     Today's appointments
             (Send WhatsApp reminders                 - Sends tracking links
              with tracking links)                    - Sends check-in links
                                                      - Personalized with time

10:00 AM     appointments:send-reminders-24h Daily   Tomorrow's appointments
             (Send 24-hour reminders)                - Sends gentle reminder
                                                      - No links (too early)
                                                      - Just confirms details
─────────────────────────────────────────────────────────────────────────────
```

---

## Configuration Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                          .ENV FILE                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ WHATSAPP_TOKEN=EAAT8f...                                 │  │
│  │ WHATSAPP_PHONE_ID=825233454013145                        │  │
│  │ WHATSAPP_DEFAULT_RECIPIENT=601155577037                  │  │
│  └──────────────────────────────────────────────────────────┘  │
└────────────────────┬────────────────────────────────────────────┘
                     │
                     ▼
         ┌──────────────────────────┐
         │  config/services.php     │
         │  ┌────────────────────┐  │
         │  │ whatsapp => [      │  │
         │  │   token => env()   │  │
         │  │   phone_id => env()│  │
         │  │ ]                  │  │
         │  └────────────────────┘  │
         └────────┬─────────────────┘
                  │
        ┌─────────┴────────────┐
        │                      │
        ▼                      ▼
    ┌──────────────┐    ┌──────────────┐
    │  AppointmentController  │    │  WhatsAppSender  │
    │  Sends confirmation     │    │  (4 methods)     │
    └──────────────┘    └──────────────┘
        │                      │
        │                      │
        └─────────┬────────────┘
                  │
                  ▼
        ┌──────────────────────────┐
        │  Console/Kernel.php      │
        │  ┌────────────────────┐  │
        │  │ 07:30 - queue:...  │  │
        │  │ 07:45 - reminders  │  │ ←─ YOUR NEW TASKS
        │  │ 10:00 - reminders-24h │ ←─ YOUR NEW TASKS
        │  └────────────────────┘  │
        └──────────────────────────┘
                  │
                  ▼
        ┌──────────────────────────┐
        │  SendAppointmentReminders│
        │  SendAppointmentReminders24h
        │  (Console Commands)      │
        └──────────────────────────┘
                  │
                  ▼
        ┌──────────────────────────┐
        │  Meta WhatsApp API       │
        │  graph.facebook.com/...  │
        └──────────────────────────┘
                  │
                  ▼
        ┌──────────────────────────┐
        │  Patient's WhatsApp      │
        │  Phone Notification      │
        └──────────────────────────┘
```

---

## Data Tracking

```
APPOINTMENTS TABLE (With Optional Tracking)
┌────────────────────────────────────────────────────────┐
│ id                                                     │
│ patient_name        → John Doe                         │
│ patient_phone       → 0123456789 → +60123456789       │
│ appointment_date    → 2026-01-15                       │
│ appointment_time    → 14:00:00                         │
│ status              → booked                           │
│ visit_token         → uuid                             │
│ ──────────────────────────────────────────────────────  │
│ [NEW] confirmation_sent_at    → 2026-01-13 14:31:45  │
│ [NEW] reminder_24h_sent_at    → 2026-01-14 10:00:15  │
│ [NEW] reminder_today_sent_at  → 2026-01-15 07:45:30  │
│ ──────────────────────────────────────────────────────  │
│ created_at          → 2026-01-13 14:30:00             │
│ updated_at          → 2026-01-15 07:45:30             │
└────────────────────────────────────────────────────────┘
```

---

## Error Handling Flow

```
WhatsApp Message Attempt
         │
         ▼
    ┌─────────┐
    │ Send to │
    │ Meta API│
    └────┬────┘
         │
    ┌────┴─────────┐
    │              │
   YES            NO
    │              │
    ▼              ▼
┌────────┐    ┌──────────────┐
│SUCCESS │    │  EXCEPTION   │
│ ✓      │    │  Caught      │
└────────┘    └────────┬─────┘
    │                  │
    │                  ▼
    │          ┌──────────────┐
    │          │ Log Error    │
    │          │ to storage/  │
    │          │ logs/...log  │
    │          └──────────────┘
    │                  │
    └────────┬─────────┘
             │
             ▼
    ┌──────────────────┐
    │ Booking continues│
    │ (non-blocking)   │
    └──────────────────┘
```

---

## Method Usage Map

```
sendAppointmentConfirmation()
├─ Called from: AppointmentController@store
├─ When: Immediately after booking
├─ Input: Appointment model
├─ Output: WhatsApp message sent (or logged if error)
└─ Smart Logic:
   ├─ If today: Include tracking + check-in links
   └─ If future: Exclude links, mention "day of appointment"

sendAppointmentReminderToday()
├─ Called from: SendAppointmentReminders console command
├─ When: Daily at 7:45 AM
├─ Input: Appointment model
├─ Output: WhatsApp message with both links
└─ Recipients: All today's appointments (not cancelled)

sendAppointmentReminder24h()
├─ Called from: SendAppointmentReminders24h console command
├─ When: Daily at 10:00 AM
├─ Input: Appointment model
├─ Output: WhatsApp message (no links, gentle reminder)
└─ Recipients: All tomorrow's appointments (not cancelled)

sendCustomMessage()
├─ Called from: Staff panel (future enhancement)
├─ When: On demand (manually)
├─ Input: Phone number, message text
├─ Output: WhatsApp message sent
└─ Use cases: Cancellations, rescheduling, urgent notices

formatMsisdn()
├─ Called from: All methods internally
├─ When: Before sending any message
├─ Input: Any phone number format
├─ Output: E.164 format (+60...)
└─ Handles: 0123456789, 60123456789, +60123456789, etc.

sendMessage() (Private)
├─ Called from: All public methods
├─ When: Internally for API call
├─ Input: Phone, message body, credentials
├─ Output: HTTP response from Meta API
└─ Purpose: Core API communication
```

---

## Quick Command Reference

```
Test Configuration:
$ php artisan tinker
> config('services.whatsapp.token')
> config('services.whatsapp.phone_id')

Start Scheduler (Development):
$ php artisan schedule:work

View Scheduled Tasks:
$ php artisan schedule:list

Send Today's Reminders (Manual):
$ php artisan appointments:send-reminders

Send 24h Reminders (Manual):
$ php artisan appointments:send-reminders-24h

View Logs:
$ tail -f storage/logs/laravel.log | grep whatsapp

Database Migration (Optional):
$ php artisan migrate

Check Message Tracking:
$ php artisan tinker
> \App\Models\Appointment::where('confirmation_sent_at', '!=', null)->count()
```

---

## Success Checklist

```
✓ Credentials in .env
✓ Services config updated
✓ WhatsAppSender service enhanced
✓ Console commands created
✓ Scheduler configured
✓ Integration points complete
✓ Non-blocking error handling
✓ Phone number formatting
✓ Message templates ready
✓ Documentation complete
✓ Scheduler running
✓ Test booking made
✓ Message received
✓ Links verified
✓ Logs checked
```

---

**Last Updated**: January 13, 2026
**Status**: ✅ Ready for Deployment
