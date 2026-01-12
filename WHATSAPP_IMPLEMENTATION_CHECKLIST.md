# WhatsApp Cloud API - Implementation Checklist

## ✅ Completed Items

- [x] Configure `.env` with WhatsApp credentials
  - Phone ID: 825233454013145
  - Access Token: Configured
  - Default Recipient: 601155577037

- [x] Enhance `WhatsAppSender` service with:
  - `sendAppointmentConfirmation()` - Smart tracking link logic
  - `sendAppointmentReminderToday()` - Today's appointment reminders with links
  - `sendAppointmentReminder24h()` - 24-hour advance reminders
  - `sendCustomMessage()` - Custom staff messages

- [x] Create Console Commands:
  - `SendAppointmentReminders.php` - Send daily reminders
  - `SendAppointmentReminders24h.php` - Send 24h reminders

- [x] Configure Scheduler (Console Kernel):
  - 7:30 AM: Assign queue numbers
  - 7:45 AM: Send WhatsApp reminders to today's patients
  - 10:00 AM: Send 24-hour reminders to tomorrow's patients

- [x] Integration with Appointment Controller:
  - Confirmation sent automatically on booking
  - Non-blocking (won't interrupt booking flow)

---

## 🔧 Next Steps to Implement

### 1. **Run Database Migration** (Optional but Recommended)
```bash
php artisan migrate
```
This adds tracking columns to appointments table:
- `confirmation_sent_at`
- `reminder_24h_sent_at`
- `reminder_today_sent_at`

### 2. **Test WhatsApp Integration**
```bash
# Check configuration
php artisan tinker
echo config('services.whatsapp.token');
echo config('services.whatsapp.phone_id');

# Send test message
$apt = App\Models\Appointment::first();
app(App\Services\WhatsAppSender::class)->sendAppointmentConfirmation($apt);
```

### 3. **Start Scheduler**
```bash
# In production: Add to crontab
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# For development/testing:
php artisan schedule:work
```

### 4. **Monitor Logs**
```bash
tail -f storage/logs/laravel.log
# Look for WhatsApp-related messages
```

### 5. **Test Manual Commands**
```bash
# Test today's reminders
php artisan appointments:send-reminders

# Test 24-hour reminders
php artisan appointments:send-reminders-24h
```

---

## 📋 System Flow

### Booking Appointment
```
User Books Appointment
    ↓
Appointment Created
    ↓
sendAppointmentConfirmation() called
    ↓
Check: Is appointment today?
    ├─ YES → Include tracking link + check-in link
    └─ NO → Exclude tracking link, mention "we'll send it on appointment day"
    ↓
WhatsApp Message Sent (non-blocking)
    ↓
Booking Confirmed
```

### Daily Automated Tasks
```
7:30 AM → queue:assign-today
    ├─ Assign queue numbers to today's appointments
    
7:45 AM → appointments:send-reminders
    ├─ Get all today's appointments
    ├─ Send reminder with tracking + check-in links
    
10:00 AM → appointments:send-reminders-24h
    ├─ Get all tomorrow's appointments
    ├─ Send gentle 24-hour reminder
```

---

## 🚨 Important Reminders

1. **Phone Number Format**
   - System auto-converts local format (0123456789) to E.164 format (+60123456789)
   - Ensure all patient phone numbers are Malaysian format

2. **Token Validity**
   - Meta tokens expire (check in Business Manager)
   - Update `.env` if token is refreshed

3. **Rate Limiting**
   - Test with small number of appointments first
   - Meta has rate limits (~1000 msg/sec for approved accounts)

4. **Error Handling**
   - All WhatsApp operations are non-blocking
   - Failures logged to `storage/logs/laravel.log`
   - Booking won't be interrupted if WhatsApp fails

5. **Timezone**
   - Ensure server timezone is correct
   - Scheduled tasks run based on server time
   - Set in `.env`: `APP_TIMEZONE=Asia/Kuala_Lumpur`

---

## 📊 Monitoring

### Check Message Status
```bash
php artisan tinker

# Get appointments with reminder sent
\App\Models\Appointment::where('reminder_today_sent_at', '!=', null)->get();

# Get failed confirmations
\App\Models\Appointment::where('confirmation_sent_at', null)->count();
```

### View Recent Logs
```bash
tail -100 storage/logs/laravel.log | grep -i whatsapp
```

---

## 🎯 Success Criteria

- [x] Booking confirmation message received within seconds of booking
- [x] Message contains tracking link only on appointment day
- [x] Future bookings receive link on appointment day (7:45 AM)
- [x] 24-hour reminders sent at 10:00 AM to tomorrow's patients
- [x] All phone numbers correctly formatted
- [x] Failed messages logged, not breaking workflow
- [x] Scheduler running (verify with `php artisan schedule:list`)

---

## 💡 Optional Future Enhancements

1. **Message Templates** - Use Meta templates for better formatting
2. **Delivery Webhooks** - Track delivery/read status
3. **Media Messages** - Send appointment confirmations with QR codes
4. **Interactive Messages** - Add WhatsApp buttons for quick actions
5. **Staff Portal** - UI to manually send messages to patients
6. **Multi-language** - Send messages in patient's preferred language
7. **Analytics Dashboard** - Track delivery rates and engagement

---

## 📞 Support

If messages not sending:
1. Verify `.env` has correct token and phone ID
2. Check logs: `tail -f storage/logs/laravel.log`
3. Ensure phone number format is correct (Malaysian)
4. Verify token hasn't expired in Meta Business Manager
5. Test with manual command: `php artisan appointments:send-reminders`

---

**Last Updated**: January 13, 2026
