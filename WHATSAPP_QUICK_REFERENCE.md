# WhatsApp Cloud API - Quick Reference

## 🚀 Quick Start

### 1. Environment Setup
```env
# .env file
WHATSAPP_TOKEN=EAAT8fFtKwgYBQZAUTCxUo5T5hLSAEAqGcJGuC6LSeHDShEF5nuCgUBzeSnPyLOq70jTVJgHgvIDdfZBRHE1oKyac68bZCEfTOGmhUBMP1aoS3Lt6bdI1WeVpPbtO4oXzMoVzyOc3jsNHBbaeu20PLGCtGzNzGZB5RbTK0RJYKI2pqfka0jfKGVdazdaJgwZDZD
WHATSAPP_PHONE_ID=825233454013145
WHATSAPP_DEFAULT_RECIPIENT=601155577037
```

### 2. Verify Configuration
```bash
php artisan tinker
> config('services.whatsapp.token')
> config('services.whatsapp.phone_id')
```

### 3. Run Scheduler
```bash
# Development (watch mode)
php artisan schedule:work

# Production (add to crontab)
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📨 Message Types & Triggers

| Trigger | Message | Sent At | Links |
|---------|---------|---------|-------|
| **Booking** | Confirmation | Immediately | Today appts only |
| **Scheduled** | Today Reminder | 7:45 AM | ✓ Tracking + Check-in |
| **Scheduled** | 24h Reminder | 10:00 AM | ✗ None |
| **Manual** | Custom | On demand | None |

---

## 🎯 Common Commands

### Test Configuration
```bash
php artisan tinker
$apt = \App\Models\Appointment::first();
app(\App\Services\WhatsAppSender::class)->sendAppointmentConfirmation($apt);
```

### Send Today's Reminders (Manual)
```bash
php artisan appointments:send-reminders
```

### Send 24h Reminders (Manual)
```bash
php artisan appointments:send-reminders-24h
```

### View Scheduled Tasks
```bash
php artisan schedule:list
```

### Check Logs
```bash
tail -f storage/logs/laravel.log | grep -i whatsapp
```

---

## 📱 Phone Number Formats

All these formats are automatically converted to E.164 (+60...):

```
Input: 0123456789    → +60123456789
Input: 60123456789   → +60123456789
Input: +60123456789  → +60123456789
Input: (012) 3456789 → +60123456789
```

---

## 🔄 Integration Points

### 1. Appointment Booking
```php
// app/Http/Controllers/AppointmentController.php
app(WhatsAppSender::class)->sendAppointmentConfirmation($appointment);
```

### 2. Manual Send from Staff Panel
```php
// Future enhancement
app(WhatsAppSender::class)->sendCustomMessage(
    '0123456789',
    'Your appointment has been rescheduled.'
);
```

### 3. In Queue Processing
```php
// Future enhancement
app(WhatsAppSender::class)->sendAppointmentReminderToday($appointment);
```

---

## 🐛 Troubleshooting

### Issue: Messages not sending
```bash
# 1. Check token
php artisan tinker
echo config('services.whatsapp.token');

# 2. Check logs
tail -f storage/logs/laravel.log

# 3. Verify phone format
echo \App\Services\WhatsAppSender::formatMsisdn('0123456789');

# 4. Test API directly
curl -X POST https://graph.facebook.com/v17.0/825233454013145/messages \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"messaging_product":"whatsapp","to":"+60123456789","type":"text","text":{"body":"Test"}}'
```

### Issue: Scheduler not running
```bash
# Check if scheduler is running
php artisan schedule:work

# Or check if cron is configured (Linux/Mac)
crontab -l | grep 'schedule:run'

# Or view schedule list
php artisan schedule:list
```

### Issue: Phone not receiving messages
```
1. Verify phone number format (0123456789 or 60123456789)
2. Ensure WhatsApp is installed on recipient's phone
3. Check token isn't expired (Meta Business Manager)
4. Verify phone is verified in Meta Business Manager
5. Check rate limiting (max ~1000 msg/sec)
```

---

## 📊 Message Templates

### Booking Confirmation (Future Date)
```
🦷 Dental Clinic Appointment Confirmed

Hi {name},
Your appointment is confirmed for {date}, {time}.

Please arrive 5-10 minutes early.

We'll send you a tracking link on the day of your appointment.
```

### Booking Confirmation (Today)
```
🦷 Appointment Today!

Hi {name},
Your appointment is at {time} today.

📍 Track Queue:
{tracking_url}

✅ Quick Check-In:
{checkin_url}

Tap the links when you're ready. See you soon! 😊
```

### 24-Hour Reminder
```
🦷 Appointment Reminder

Hi {name},
Reminder: Your appointment is tomorrow ({date}) at {time}.

Please arrive 5-10 minutes early. See you then! 👋
```

### Same-Day Reminder
```
🦷 Appointment Today!

Hi {name},
Your appointment is at {time} today.

📍 Track Queue:
{tracking_url}

✅ Quick Check-In:
{checkin_url}

See you soon! 😊
```

---

## 🔑 Key Features

✅ **Smart Tracking Links**
- Only included on appointment day
- Future appointments don't get tracking URLs

✅ **Automatic Scheduling**
- 7:45 AM: Reminders to today's patients
- 10:00 AM: 24-hour reminders to tomorrow's patients

✅ **Non-Blocking**
- Booking won't fail if WhatsApp is down
- All errors logged to `storage/logs/laravel.log`

✅ **Phone Format Auto-Conversion**
- Handles Malaysian formats automatically
- Converts to E.164 standard (+60...)

✅ **Malaysian Format Support**
- 0123456789 (local)
- 60123456789 (country code without +)
- +60123456789 (E.164 format)

---

## 📈 Monitoring

### Check Sent Messages
```bash
php artisan tinker

# Get all appointments with confirmation sent
\App\Models\Appointment::where('confirmation_sent_at', '!=', null)->count()

# Get appointments without confirmation
\App\Models\Appointment::where('confirmation_sent_at', null)->count()

# View specific appointment
$apt = \App\Models\Appointment::find(1);
echo $apt->confirmation_sent_at;
echo $apt->reminder_today_sent_at;
```

### View Recent Logs
```bash
tail -50 storage/logs/laravel.log | grep -i whatsapp
```

---

## 🔐 Security

| Feature | Status |
|---------|--------|
| Token in .env | ✓ Secured |
| No hardcoded credentials | ✓ Yes |
| Phone validation | ✓ Format check |
| Error logging safe | ✓ No sensitive data |
| Non-blocking calls | ✓ No timeouts |
| Rate limiting | ✓ Built-in scheduler |

---

## 📞 Meta WhatsApp API Reference

- **API Version**: v17.0
- **Endpoint**: `https://graph.facebook.com/v17.0/{phone_id}/messages`
- **Auth**: Bearer token
- **Rate Limit**: ~1000 messages/second (depends on account tier)
- **Token Expiration**: ~60-90 days (check in Business Manager)

---

## 🎓 Example Workflows

### Workflow 1: Patient Books for Today at 2 PM
```
14:30 → User books appointment for 14:00 today (oops, just now)
14:31 → Confirmation sent: "Your appointment is at 14:00 today"
        Includes: Tracking link, Check-in link
14:35 → Patient receives WhatsApp notification
14:40 → Patient clicks tracking link, sees queue
14:45 → Patient arrives at clinic
        Uses check-in link from WhatsApp
```

### Workflow 2: Patient Books for Tomorrow at 10 AM
```
15:00 → User books appointment for 10:00 tomorrow
15:01 → Confirmation sent: "Your appointment is confirmed for tomorrow at 10:00"
        No links: "We'll send you a tracking link on appointment day"
15:05 → Patient receives WhatsApp
Next day 07:45 → Scheduler runs: appointments:send-reminders
        → Sends: "Your appointment is at 10:00 today"
        → Includes: Tracking link, Check-in link
09:50 → Patient opens WhatsApp link
        Sees live queue, arrives at clinic
10:00 → Appointment starts
```

### Workflow 3: Patient Books for Next Week at 3 PM
```
Mon 14:00 → User books appointment for Fri 15:00
Mon 14:01 → Confirmation sent: No links, just confirmation
Mon-Thu → Patient waits for appointment
Fri 07:45 → Scheduler: Send reminders
        → Sends: "Your appointment is at 15:00 today"
        → Includes: Tracking link, Check-in link
Thu 10:00 → Scheduler: Send 24h reminders
        → Sends: "Reminder: appointment tomorrow at 15:00"
        → No links (too early)
Fri 14:50 → Patient opens WhatsApp link
        Sees queue, arrives at clinic
```

---

**Quick Reference Last Updated**: January 13, 2026
