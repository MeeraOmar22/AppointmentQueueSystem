# Testing Guide: Appointment & Queue System

## Overview
This guide covers testing strategies for the appointment booking and queue management system with multiple conditions and scenarios.

---

## 1. Testing Approaches

### A. Unit Tests (Models & Business Logic)
Test individual model methods and business logic in isolation.
- Location: `tests/Unit/`
- Tools: PHPUnit assertions, Model factories
- Focus: Data validation, calculations, state management

### B. Feature Tests (Controller & Routes)
Test complete workflows through HTTP requests.
- Location: `tests/Feature/`
- Tools: HTTP testing, Request/Response assertions
- Focus: User interactions, redirects, data flow

### C. Manual Testing
Test UI/UX and user workflows through browser.
- Focus: Visual consistency, responsive design, user experience

---

## 2. Testing Conditions & Scenarios

### A. APPOINTMENT BOOKING CONDITIONS

#### 1.1 Valid Booking Scenarios
```
✓ User books appointment with all required fields
✓ User selects specific dentist
✓ User selects available time slot
✓ User provides valid phone number
✓ User receives unique visit_token
✓ User receives unique visit_code
✓ Email confirmation is sent
✓ Queue entry is created
✓ Appointment status = 'booked'
```

#### 1.2 Validation Error Scenarios
```
✓ Missing patient name
✓ Missing phone number (invalid format)
✓ Missing email address (invalid format)
✓ Missing service selection
✓ Missing appointment date (past date)
✓ Invalid time format
✓ Exceeding maximum queue for the day
✓ Selected time slot is unavailable
✓ Dentist not available on selected date
```

#### 1.3 Edge Case Scenarios
```
✓ Booking at exactly clinic closing time
✓ Booking on weekend when clinic is closed
✓ Booking when clinic is at full capacity
✓ Booking with special characters in name
✓ Booking with international phone format
✓ Double booking prevention (same patient, same time)
✓ Timezone handling for appointment_date
```

### B. QUEUE MANAGEMENT CONDITIONS

#### 2.1 Queue Number Assignment
```
✓ First appointment of the day = Queue #1
✓ Subsequent appointments get sequential numbers
✓ Queue numbers reset per day
✓ Queue numbers based on appointment_date
✓ Queue numbers account for multiple dentists
✓ Queue numbers account for multiple services
```

#### 2.2 Queue Status Transitions
```
✓ New appointment → Status: 'waiting'
✓ Check-in → Status: 'checked_in'
✓ Treatment start → Status: 'in_service'
✓ Treatment complete → Status: 'completed'
✓ No-show → Status: 'no_show'
✓ Cancellation → Status: 'cancelled'
```

#### 2.3 Queue Position & ETA
```
✓ Correct queue position calculation
✓ ETA based on average service duration
✓ ETA updates when patient checks in
✓ ETA adjusts when patients no-show
✓ Queue moves forward after completion
✓ Position displayed correctly on frontend
```

#### 2.4 Queue Fairness Conditions
```
✓ Earlier appointment → Earlier queue position
✓ Check-in time respected (FIFO principle)
✓ Priority handling if implemented
✓ Fair distribution across dentists
✓ Service time affects queue flow
```

### C. CHECK-IN CONDITIONS

#### 3.1 Valid Check-in Scenarios
```
✓ Check-in with valid visit_token
✓ Check-in with valid visit_code
✓ Check-in with valid phone number
✓ Check-in updates check_in_time
✓ Check-in updates queue_status to 'checked_in'
✓ Check-in redirects to appointment status page
✓ Multiple check-in attempts don't duplicate queue
```

#### 3.2 Invalid Check-in Scenarios
```
✗ Check-in with invalid/expired token
✗ Check-in with wrong phone number
✗ Check-in before appointment date
✗ Check-in after appointment date (next day)
✗ Check-in for cancelled appointment
✗ Check-in for already completed appointment
✗ Check-in with missing required data
```

#### 3.3 Check-in Edge Cases
```
✓ Check-in exactly at appointment time
✓ Early check-in (30 mins before)
✓ Late check-in (after appointment time)
✓ Multiple devices checking in same appointment
✓ Browser back button after check-in
✓ Simultaneous check-in attempts
```

### D. OPERATING HOURS CONDITIONS

#### 4.1 Operating Hours Validation
```
✓ Cannot book outside operating hours
✓ Cannot book on closed days
✓ Morning session hours respected
✓ Afternoon session hours respected
✓ Break time handled correctly
✓ Weekend closed status respected
✓ Public holidays blocked
```

#### 4.2 Operating Hours Display
```
✓ Today's hours displayed on booking page
✓ Today's hours displayed on check-in page
✓ Today's hours displayed on find-my-booking
✓ "Closed" badge shows on non-operating days
✓ Session labels display correctly
✓ Time formatting is 12-hour (g:i a)
```

### E. DENTIST AVAILABILITY CONDITIONS

#### 5.1 Dentist Schedule
```
✓ Dentist available on working days
✓ Dentist on leave → unavailable
✓ Dentist has maximum appointments per day
✓ Cannot book with unavailable dentist
✓ DentistSchedule respects operating hours
✓ DentistLeave dates block appointments
```

#### 5.2 Dentist Selection
```
✓ User can select from available dentists
✓ Service determines eligible dentists
✓ Dentist availability filtered by date
✓ "Any dentist" option works
✓ Dentist preferences respected
```

### F. DATA PERSISTENCE CONDITIONS

#### 6.1 Database Consistency
```
✓ Appointment record created with correct data
✓ Queue entry created for every appointment
✓ Queue number is unique per day
✓ ActivityLog created for important actions
✓ Timestamp accuracy (created_at, updated_at)
✓ Soft deletes work if implemented
✓ Cascade deletes don't orphan queue entries
```

#### 6.2 Form Persistence
```
✓ Form retains data on validation error
✓ old() helper displays previous input
✓ Session flash messages appear correctly
✓ CSRF token validation works
✓ Rate limiting prevents spam bookings
```

---

## 3. Sample Test Files

### File 1: AppointmentModelTest.php
```php
<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test appointment creation with valid data
     */
    public function test_appointment_can_be_created_with_valid_data()
    {
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '10:00',
            'status' => 'booked',
            'booking_source' => 'web',
        ]);

        $this->assertNotNull($appointment->visit_token);
        $this->assertNotNull($appointment->visit_code);
        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'John Doe',
        ]);
    }

    /**
     * Test visit_token is generated automatically
     */
    public function test_visit_token_is_auto_generated()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Jane Doe',
            'patient_phone' => '60198765432',
            'patient_email' => 'jane@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '11:00',
            'status' => 'booked',
        ]);

        $this->assertTrue(strlen($appointment->visit_token) > 0);
    }

    /**
     * Test visit_code is generated with date
     */
    public function test_visit_code_is_generated_with_date()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Bob Smith',
            'patient_phone' => '60187654321',
            'patient_email' => 'bob@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '14:00',
            'status' => 'booked',
        ]);

        $this->assertNotNull($appointment->visit_code);
        $this->assertMatchesRegularExpression('/\d+/', $appointment->visit_code);
    }

    /**
     * Test appointment status updates
     */
    public function test_appointment_status_can_be_updated()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Alice',
            'patient_phone' => '60123000000',
            'patient_email' => 'alice@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '09:00',
            'status' => 'booked',
        ]);

        $appointment->update(['status' => 'checked_in']);
        $this->assertEquals('checked_in', $appointment->fresh()->status);

        $appointment->update(['status' => 'completed']);
        $this->assertEquals('completed', $appointment->fresh()->status);
    }
}
```

### File 2: QueueModelTest.php
```php
<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test next queue number for a date
     */
    public function test_next_queue_number_starts_at_one()
    {
        $nextNum = Queue::nextNumberForDate(now()->toDateString());
        $this->assertEquals(1, $nextNum);
    }

    /**
     * Test queue numbers increment sequentially
     */
    public function test_queue_numbers_increment_sequentially()
    {
        $date = now()->toDateString();

        // Create first appointment and queue
        $apt1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '60111111111',
            'patient_email' => 'p1@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $apt1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        // Create second appointment
        $apt2 = Appointment::create([
            'patient_name' => 'Patient 2',
            'patient_phone' => '60122222222',
            'patient_email' => 'p2@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => $date,
            'appointment_time' => '11:00',
            'status' => 'booked',
        ]);

        $nextNum = Queue::nextNumberForDate($date);
        $this->assertEquals(2, $nextNum);

        Queue::create([
            'appointment_id' => $apt2->id,
            'queue_number' => 2,
            'queue_status' => 'waiting',
        ]);

        $nextNum = Queue::nextNumberForDate($date);
        $this->assertEquals(3, $nextNum);
    }

    /**
     * Test queue numbers reset per day
     */
    public function test_queue_numbers_reset_per_day()
    {
        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        // Create queue for today
        $apt1 = Appointment::create([
            'patient_name' => 'Today Patient',
            'patient_phone' => '60133333333',
            'patient_email' => 'today@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => $today,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $apt1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        // Tomorrow should start at 1 again
        $nextNum = Queue::nextNumberForDate($tomorrow);
        $this->assertEquals(1, $nextNum);
    }

    /**
     * Test queue relationship with appointment
     */
    public function test_queue_has_appointment_relationship()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Related Patient',
            'patient_phone' => '60144444444',
            'patient_email' => 'related@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '15:00',
            'status' => 'booked',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $this->assertNotNull($queue->appointment);
        $this->assertEquals($appointment->id, $queue->appointment->id);
    }
}
```

### File 3: BookingFeatureTest.php
```php
<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create required test data
        Service::create(['name' => 'Cleaning', 'description' => 'Dental cleaning']);
        Dentist::create(['name' => 'Dr. Smith', 'email' => 'smith@clinic.com']);
    }

    /**
     * Test booking form page loads
     */
    public function test_booking_form_page_loads()
    {
        $response = $this->get('/book');
        $response->assertStatus(200);
        $response->assertSee('Book Your Appointment');
    }

    /**
     * Test booking with valid data
     */
    public function test_can_book_appointment_with_valid_data()
    {
        $response = $this->post('/book', [
            'patient_name' => 'Test Patient',
            'patient_phone' => '60155555555',
            'patient_email' => 'test@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'Test Patient',
        ]);
    }

    /**
     * Test booking validation errors
     */
    public function test_booking_requires_patient_name()
    {
        $response = $this->post('/book', [
            'patient_phone' => '60155555555',
            'patient_email' => 'test@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('patient_name');
    }

    /**
     * Test booking validation errors for phone
     */
    public function test_booking_requires_valid_phone()
    {
        $response = $this->post('/book', [
            'patient_name' => 'Test Patient',
            'patient_phone' => 'invalid',
            'patient_email' => 'test@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('patient_phone');
    }

    /**
     * Test booking with past date is rejected
     */
    public function test_cannot_book_with_past_date()
    {
        $response = $this->post('/book', [
            'patient_name' => 'Test Patient',
            'patient_phone' => '60155555555',
            'patient_email' => 'test@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->subDay()->toDateString(),
            'appointment_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('appointment_date');
    }

    /**
     * Test booking success page
     */
    public function test_booking_success_page_shows_confirmation()
    {
        $this->post('/book', [
            'patient_name' => 'Test Patient',
            'patient_phone' => '60155555555',
            'patient_email' => 'test@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
        ]);

        $appointment = Appointment::where('patient_name', 'Test Patient')->first();
        $response = $this->get('/booking-success/' . $appointment->visit_token);
        
        $response->assertStatus(200);
        $response->assertSee('Booking Confirmed');
    }
}
```

### File 4: CheckInFeatureTest.php
```php
<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test check-in form loads
     */
    public function test_checkin_form_loads()
    {
        $response = $this->get('/checkin');
        $response->assertStatus(200);
        $response->assertSee('Check In');
    }

    /**
     * Test check-in with valid token
     */
    public function test_can_checkin_with_valid_token()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Check In Patient',
            'patient_phone' => '60166666666',
            'patient_email' => 'checkin@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60166666666',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'checked_in',
        ]);
    }

    /**
     * Test check-in with invalid token fails
     */
    public function test_cannot_checkin_with_invalid_token()
    {
        $response = $this->post('/checkin', [
            'token' => 'invalid-token-xyz',
            'phone' => '60166666666',
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test check-in with wrong phone fails
     */
    public function test_cannot_checkin_with_wrong_phone()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Check In Patient',
            'patient_phone' => '60166666666',
            'patient_email' => 'checkin@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60177777777', // Wrong phone
        ]);

        $response->assertSessionHasErrors();
    }

    /**
     * Test check-in for completed appointment fails
     */
    public function test_cannot_checkin_completed_appointment()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Completed Patient',
            'patient_phone' => '60188888888',
            'patient_email' => 'completed@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->subDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'completed',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60188888888',
        ]);

        $response->assertSessionHasErrors();
    }
}
```

---

## 4. Running Tests

### Run all tests:
```bash
php artisan test
```

### Run specific test file:
```bash
php artisan test tests/Unit/AppointmentModelTest.php
```

### Run specific test method:
```bash
php artisan test --filter test_appointment_can_be_created_with_valid_data
```

### Run with coverage report:
```bash
php artisan test --coverage
```

### Run in parallel (faster):
```bash
php artisan test --parallel
```

---

## 5. Testing Checklist

### Before Deployment
- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Code coverage > 80% (target)
- [ ] No SQL errors in logs
- [ ] No JavaScript console errors
- [ ] Database migrations run cleanly
- [ ] Seeder data loads correctly

### Manual Testing Checklist
- [ ] Book appointment with valid data
- [ ] Book appointment with invalid data (validation)
- [ ] Check-in with valid token
- [ ] Check-in with invalid token
- [ ] View appointment status
- [ ] View queue position
- [ ] Find my booking by phone
- [ ] Operating hours display on all pages
- [ ] Email notifications sent
- [ ] Responsive design works
- [ ] Mobile check-in works

### Post-Deployment Smoke Tests
- [ ] Can access /book page
- [ ] Can book appointment
- [ ] Email confirmation received
- [ ] Can check-in with token
- [ ] Can view appointment status
- [ ] Operating hours display correctly

---

## 6. Performance Testing

### Load Testing Conditions
```
- 100 simultaneous bookings
- 50 simultaneous check-ins
- Large queue calculations
- Database query optimization
- Memory usage under load
```

### Metrics to Monitor
- Page load time < 2 seconds
- API response time < 500ms
- Queue position calculation < 100ms
- Database queries < 5 per request

---

## 7. Debugging Tips

### Common Issues

#### Issue: "Property does not exist" in Queue tests
- **Solution**: Ensure Appointment is created before Queue reference

#### Issue: Validation fails unexpectedly
- **Solution**: Check form request rules, run `php artisan tinker` to inspect data

#### Issue: Tests pass locally, fail in CI
- **Solution**: Check .env.testing file, ensure RefreshDatabase is used, check timezone settings

### Useful Commands
```bash
# Inspect database state
php artisan tinker
>>> Appointment::all();
>>> Queue::all();

# Run migrations in test environment
php artisan migrate --env=testing

# Check test database
php artisan tinker --env=testing

# View failed test details
php artisan test --verbose
```

---

## 8. Test Data Factory

### Create appointment factory file at: tests/Feature/AppointmentFactory.php

```php
<?php

namespace Tests\Factories;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;

class AppointmentFactory
{
    public static function createBookedAppointment($overrides = [])
    {
        $defaults = [
            'patient_name' => 'Test Patient',
            'patient_phone' => '60123456789',
            'patient_email' => 'test@example.com',
            'clinic_location' => 'Penang',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ];

        return Appointment::create(array_merge($defaults, $overrides));
    }

    public static function createCheckedInAppointment($overrides = [])
    {
        $appointment = self::createBookedAppointment($overrides);
        $appointment->update(['status' => 'checked_in']);
        return $appointment;
    }
}
```

**Usage in tests:**
```php
$appointment = AppointmentFactory::createBookedAppointment([
    'patient_name' => 'John Doe',
]);
```

---

## 9. Test Coverage Goals

| Component | Target Coverage | Type |
|-----------|-----------------|------|
| Models | 90%+ | Unit |
| Controllers | 85%+ | Feature |
| Validation | 100% | Feature |
| Queue Logic | 95%+ | Unit |
| Check-in Flow | 90%+ | Feature |

---

## Next Steps

1. Create test files from examples above
2. Set up database for testing (.env.testing)
3. Run initial tests to verify setup
4. Add more test cases for edge conditions
5. Integrate with CI/CD pipeline
6. Monitor coverage and improve
7. Add performance benchmarks

---

**This testing guide provides comprehensive coverage for appointment booking and queue management. Adapt the test scenarios to your specific business requirements and add more as needed.**
