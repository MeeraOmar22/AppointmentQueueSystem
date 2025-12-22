# 🚀 Quick Test Execution Guide

## Run All Tests
```bash
cd "c:\Users\User\Desktop\FYP 2\laravel12_bootstrap"
php artisan test
```

## Expected Output
```
Tests: 43 passed (42 fixed + 1 existing)
Duration: ~5 seconds
```

---

## Run Specific Test Classes

### Unit Tests - Appointments
```bash
php artisan test tests/Unit/AppointmentModelTest.php
```
**Expected:** 6 tests passing

### Unit Tests - Queues
```bash
php artisan test tests/Unit/QueueModelTest.php
```
**Expected:** 8 tests passing

### Feature Tests - Booking
```bash
php artisan test tests/Feature/BookingFeatureTest.php
```
**Expected:** 15 tests passing

### Feature Tests - Check-in
```bash
php artisan test tests/Feature/CheckInFeatureTest.php
```
**Expected:** 13 tests passing

---

## Run with Verbose Output
```bash
php artisan test --verbose
```

## Run Specific Test Method
```bash
php artisan test --filter=test_appointment_can_be_created
php artisan test --filter=test_can_book_appointment_with_valid_data
```

## Run Tests with Coverage
```bash
php artisan test --coverage
```

---

## What Was Fixed

✅ **AppointmentModelTest.php** (6 tests)
- Transaction-based cleanup
- Added dentist_preference field

✅ **QueueModelTest.php** (8 tests)
- Transaction-based cleanup
- Added dentist_preference field

✅ **BookingFeatureTest.php** (15 tests)
- Transaction-based cleanup
- createTestData() helper method
- CSRF bypass for all tests

✅ **CheckInFeatureTest.php** (13 tests)
- Transaction-based cleanup
- Service/Dentist setup in setUp()
- Added dentist_preference field

---

## Troubleshooting

### If tests still fail
1. Ensure database migrations are up to date
   ```bash
   php artisan migrate
   ```

2. Clear cache
   ```bash
   php artisan cache:clear
   ```

3. Run tests with verbose output to see detailed errors
   ```bash
   php artisan test --verbose
   ```

### If CSRF errors occur
- Already handled via `$this->withoutMiddleware(VerifyCsrfToken::class);`
- Check that feature tests include proper middleware bypass

### If foreign key errors persist
- Verify Service and Dentist records are created before Appointments
- Check transaction scope - setUp() should create data INSIDE transaction

---

## Key Changes Summary

| File | Change |
|------|--------|
| Appointment.php | Added 'dentist_preference' to fillable |
| AppointmentModelTest.php | DB transactions + dentist_preference |
| QueueModelTest.php | DB transactions + dentist_preference |
| BookingFeatureTest.php | DB transactions + CSRF bypass + createTestData() |
| CheckInFeatureTest.php | DB transactions + CSRF bypass + dentist_preference |

---

## Test Status: ✅ READY TO RUN
