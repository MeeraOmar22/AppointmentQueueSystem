# WhatsApp Cloud API Integration Guide

## Configuration Summary

Your WhatsApp Cloud API is now configured with the following credentials:
- **Phone ID**: 825233454013145
- **Access Token**: EAAT8fFtKwgYBQZAUTCxUo5T5hLSAEAqGcJGuC6LSeHDShEF5nuCgUBzeSnPyLOq70jTVJgHgvIDdfZBRHE1oKyac68bZCEfTOGmhUBMP1aoS3Lt6bdI1WeVpPbtO4oXzMoVzyOc3jsNHBbaeu20PLGCtGzNzGZB5RbTK0RJYKI2pqfka0jfKGVdazdaJgwZDZD
- **Default Recipient**: 601155577037

---

## What's Configured

### 1. **Enhanced WhatsApp Service** (`app/Services/WhatsAppSender.php`)

#### Methods Available:

**`sendAppointmentConfirmation(Appointment $appointment)`**
- Sent immediately after booking
- **Smart Logic**: 
  - If booking is for TODAY: Includes tracking link + check-in link
  - If booking is for FUTURE DATE: No tracking link (users get it on appointment day)
- Example message (future date):
  ```
  🦷 Dental Clinic Appointment Confirmed
  
  Hi John,
  Your appointment is confirmed for 15 Jan 2026, 14:00.
  
  Please arrive 5-10 minutes early.
  
  We'll send you a tracking link on the day of your appointment.
  ```

**`sendAppointmentReminderToday(Appointment $appointment)`**
- Sent at **7:45 AM** to all patients with TODAY's appointments
- Includes both tracking link AND quick check-in link
- Example:
  ```
  🦷 Appointment Today!
  
  Hi John,
  Your appointment is at 14:00 today.
  
  📍 Track Queue:
  http://localhost:8000/visit/uuid-token
  
  ✅ Quick Check-In:
  http://localhost:8000/checkin?token=uuid-token
  
  Tap the links when you're ready. See you soon! 😊
  ```

**`sendAppointmentReminder24h(Appointment $appointment)`**
- Sent at **10:00 AM** to patients with TOMORROW's appointments
- Gentle reminder without links
- Example:
  ```
  🦷 Appointment Reminder
  
  Hi John,
  Reminder: Your appointment is tomorrow (15 Jan 2026) at 14:00.
  
  Please arrive 5-10 minutes early. See you then! 👋
  ```

**`sendCustomMessage(string $phoneNumber, string $message)`**
- For staff to send custom messages to patients
- Useful for cancellations, rescheduling, special notices
- Returns boolean success status

---

### 2. **Automated Scheduled Tasks** (`app/Console/Kernel.php`)

Every day, the system automatically runs:

| Time  | Command | Purpose |
|-------|---------|---------|
| 7:30 AM | `queue:assign-today` | Assign queue numbers to today's appointments |
| 7:45 AM | `appointments:send-reminders` | Send WhatsApp reminders with tracking links |
| 10:00 AM | `appointments:send-reminders-24h` | Send 24-hour advance reminders |

---

### 3. **New Console Commands**

#### Run Today's Reminders (Manual)
```bash
php artisan appointments:send-reminders
```
Output:
```
Reminder sent to John Doe (0123456789)
Reminder sent to Jane Smith (0198765432)
Successfully sent 2 reminder(s) out of 2.
```

#### Run 24-Hour Reminders (Manual)
```bash
php artisan appointments:send-reminders-24h
```

#### View Scheduled Tasks
```bash
php artisan schedule:list
```

---

## Implementation Status

### ✅ Completed
- [x] `.env` configured with WhatsApp credentials
- [x] Enhanced `WhatsAppSender` service with multiple methods
- [x] Smart tracking link logic (today vs future dates)
- [x] Console commands for reminders
- [x] Scheduled tasks in kernel
- [x] Booking confirmation integration

### 📝 Usage Examples

#### Send Confirmation (Automatic on Booking)
```php
app(WhatsAppSender::class)->sendAppointmentConfirmation($appointment);
```

#### Send Today's Reminders (Automatic 7:45 AM)
```php
// Runs automatically via scheduler
// Or manually: php artisan appointments:send-reminders
```

#### Send Custom Message from Staff Panel
```php
app(WhatsAppSender::class)->sendCustomMessage(
    '0123456789',
    'Your appointment has been rescheduled to tomorrow at 2 PM.'
);
```

---

## Error Handling

All WhatsApp operations are **non-blocking**:
- If API is down, booking continues
- Errors are logged to `storage/logs/laravel.log`
- No user-facing interruptions
- Messages can be retried via commands

---

## Testing WhatsApp Integration

### 1. Test Configuration
```bash
php artisan tinker

# Check if credentials are loaded
echo config('services.whatsapp.token');
echo config('services.whatsapp.phone_id');
```

### 2. Manual Send Test
```bash
php artisan tinker

$appointment = \App\Models\Appointment::first();
app(\App\Services\WhatsAppSender::class)->sendAppointmentConfirmation($appointment);
```

### 3. View Scheduled Tasks
```bash
php artisan schedule:list
```

### 4. Run Scheduler Manually
```bash
php artisan schedule:work
# Then in another terminal, run the commands
php artisan appointments:send-reminders
```

---

## Integration Points

### 1. **Appointment Booking** (`AppointmentController@store`)
- Automatically sends confirmation on successful booking
- No tracking link if booking is for future date
- Includes tracking link if booking is for today

### 2. **Activity Logging**
- All WhatsApp messages logged to system activity log
- Staff can view message history in activity logs panel

### 3. **Staff Panel Integration** (Future)
Can add button to manually send messages:
```php
// In Staff\AppointmentController
Route::post('/appointments/{id}/send-reminder', 'sendReminder');

public function sendReminder(Appointment $appointment) {
    app(WhatsAppSender::class)->sendAppointmentReminderToday($appointment);
    return back()->with('success', 'Reminder sent');
}
```

---

## Phone Number Format

The system automatically handles Malaysian phone formats:

| Input Format | Converted To |
|-------------|------------|
| 60123456789 | +60123456789 |
| 0123456789 | +60123456789 |
| +60123456789 | +60123456789 |
| (012) 3456789 | +60123456789 |

---

## Important Notes

1. **Token Expiration**: Meta tokens expire periodically. You'll need to refresh them in `.env`
2. **Phone Verification**: The number must be WhatsApp-verified in Meta Business Manager
3. **Message Templates**: Current implementation uses simple text. Can be upgraded to template messages later
4. **Rate Limiting**: Meta has rate limits (~1000 messages/second for approved accounts)
5. **Timezone**: Ensure server timezone is set correctly for scheduled tasks

---

## Next Steps (Optional Enhancements)

1. **Template Messages**: Use Meta's message templates for better formatting
2. **Media Attachments**: Send appointment confirmations with QR codes
3. **Interactive Messages**: Add buttons for quick actions in WhatsApp
4. **Webhook Handling**: Receive delivery/read receipts from patients
5. **Analytics**: Track message delivery and engagement rates
6. **Staff Portal**: Add UI to manually send/resend messages

---

## Troubleshooting

### Messages Not Sending?

1. **Check .env**
   ```bash
   php artisan tinker
   echo config('services.whatsapp.token');  // Should not be empty
   ```

2. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Verify Phone Format**
   - Ensure phone numbers are in correct format
   - Test with `formatMsisdn()` method

4. **Test API Directly**
   ```bash
   curl -X POST https://graph.facebook.com/v17.0/825233454013145/messages \
     -H "Authorization: Bearer EAAT8f..." \
     -d '{...message payload...}'
   ```

5. **Check Token Expiration**
   - Token may need refresh in Meta Business Manager
   - Refresh interval: typically 60-90 days

---

## Database Considerations

No new tables required. The system uses existing `appointments` table. Optional: Add tracking columns if needed later:

```sql
ALTER TABLE appointments ADD COLUMN (
    confirmation_sent_at TIMESTAMP NULL,
    reminder_sent_at TIMESTAMP NULL,
    reminder_24h_sent_at TIMESTAMP NULL
);
```

This would allow you to track when messages were sent and avoid duplicates.

---

**Configuration Last Updated**: January 13, 2026
