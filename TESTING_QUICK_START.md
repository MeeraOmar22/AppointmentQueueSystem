# Quick Start: Running Tests for Appointment & Queue System

## 1. Prerequisites

Ensure your `.env.testing` file exists. If not, copy from `.env`:

```bash
cp .env .env.testing
```

Update `.env.testing` with test database:

```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Or for file-based test DB:

```
DB_CONNECTION=sqlite
DB_DATABASE=database/testing.sqlite
```

---

## 2. Run All Tests

```bash
php artisan test
```

Expected output:
```
Tests:     45 passed
Time:      2.34s
```

---

## 3. Run Specific Test Classes

### Appointment Model Tests
```bash
php artisan test tests/Unit/AppointmentModelTest.php
```

Expected: 6 tests pass

### Queue Model Tests
```bash
php artisan test tests/Unit/QueueModelTest.php
```

Expected: 8 tests pass

### Booking Feature Tests
```bash
php artisan test tests/Feature/BookingFeatureTest.php
```

Expected: 15 tests pass

### Check-in Feature Tests
```bash
php artisan test tests/Feature/CheckInFeatureTest.php
```

Expected: 13 tests pass

---

## 4. Run Specific Test Methods

```bash
# Test appointment creation
php artisan test --filter test_appointment_can_be_created_with_valid_data

# Test queue numbering
php artisan test --filter test_queue_numbers_increment_sequentially

# Test booking validation
php artisan test --filter test_booking_requires_patient_name

# Test check-in
php artisan test --filter test_can_checkin_with_valid_token_and_phone
```

---

## 5. Run Tests with Verbose Output

Shows more details about each test:

```bash
php artisan test --verbose
```

---

## 6. Run Tests in Parallel (Faster)

```bash
php artisan test --parallel
```

Typical time: ~1 second vs ~5 seconds sequential

---

## 7. Run Tests with Code Coverage

```bash
php artisan test --coverage
```

Shows percentage of code covered by tests.

Target: > 80% coverage

---

## 8. Run Tests with Pretty Output

```bash
php artisan test --pretty
```

Better formatting in terminal.

---

## 9. Test Multiple Conditions Example

### Example 1: Testing Appointment Validation

```bash
# All validation tests
php artisan test --filter "booking_requires"
```

This will run:
- `test_booking_requires_patient_name`
- `test_booking_requires_valid_phone`
- `test_booking_requires_valid_email`
- `test_booking_requires_service_selection`

### Example 2: Testing Queue Behavior

```bash
php artisan test --filter "queue_numbers"
```

This will run:
- `test_next_queue_number_starts_at_one`
- `test_queue_numbers_increment_sequentially`
- `test_queue_numbers_reset_per_day`

### Example 3: Testing Check-in Scenarios

```bash
php artisan test --filter "checkin"
```

This will run all check-in related tests.

---

## 10. Debug Failed Tests

### See detailed failure info:

```bash
php artisan test --verbose tests/Feature/BookingFeatureTest.php
```

### Use Tinker to inspect test data:

```bash
php artisan tinker --env=testing
>>> Appointment::all();
>>> Queue::all();
```

---

## 11. Watch Mode (Auto-run on File Change)

Install pest/laravel-pest-plugin if needed:

```bash
composer require --dev pest/plugin-laravel
```

Then use watch mode:

```bash
php artisan test --watch
```

---

## 12. Test Specific Scenarios

### Scenario: Booking & Queue Flow
```bash
php artisan test --filter "test_can_book_appointment_with_valid_data|test_booking_creates_queue_entry"
```

### Scenario: Check-in Validation
```bash
php artisan test --filter "checkin.*invalid|checkin.*wrong|checkin.*cancelled"
```

### Scenario: Multiple Conditions
```bash
php artisan test --filter "status"
```

Runs tests containing "status" in name.

---

## 13. Understanding Test Output

### Passing Test
```
✓ test_appointment_can_be_created_with_valid_data
```

### Failing Test
```
✗ test_appointment_can_be_created_with_valid_data
  Failed asserting that null is not null.
  at tests/Unit/AppointmentModelTest.php:45
```

### Skipped Test
```
⊘ test_some_future_feature
```

---

## 14. Common Test Commands

| Command | Purpose |
|---------|---------|
| `php artisan test` | Run all tests |
| `php artisan test --parallel` | Run tests in parallel (fast) |
| `php artisan test --coverage` | Show code coverage % |
| `php artisan test --verbose` | Show detailed output |
| `php artisan test --filter=name` | Run tests matching pattern |
| `php artisan test --watch` | Watch mode (auto-rerun) |
| `php artisan test tests/Unit/` | Run all unit tests |
| `php artisan test tests/Feature/` | Run all feature tests |

---

## 15. Testing Checklist

Before committing code, run:

```bash
# 1. Run all tests
php artisan test

# 2. Check coverage
php artisan test --coverage

# 3. Verify specific areas touched
php artisan test --filter "booking|queue|checkin"

# 4. Run with verbose for details
php artisan test --verbose

# 5. (Optional) Run in parallel for speed
php artisan test --parallel
```

**Expected Result: All tests pass (0 failures)**

---

## 16. Adding New Tests

When you add a new test class:

1. Create file in `tests/Unit/` or `tests/Feature/`
2. Extend `Tests\TestCase`
3. Use `RefreshDatabase` trait
4. Write test methods starting with `test_`

Example:
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_feature_works()
    {
        // Arrange
        $data = ['name' => 'Test'];

        // Act
        $response = $this->post('/some-route', $data);

        // Assert
        $response->assertRedirect();
    }
}
```

Then run:
```bash
php artisan test tests/Feature/NewFeatureTest.php
```

---

## 17. Integration Testing Flow

Test complete user journeys:

```bash
# Test full booking flow: Form → Validation → Queue
php artisan test --filter "test_can_book.*|test_booking_creates_queue"

# Test complete check-in flow: Form → Validation → Status Update
php artisan test --filter "test_can_checkin.*|test_checkin_updates"

# Test queue position calculation
php artisan test --filter "queue_numbers"
```

---

## 18. Performance Testing

Monitor test execution time:

```bash
php artisan test --profiles
```

Or check total time:

```bash
php artisan test

# Look for: Time: 2.34s
```

Target: < 5 seconds for all tests

---

## 19. Continuous Integration Setup

For GitHub Actions, add `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: testing
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install
      - run: php artisan test
```

---

## 20. Quick Reference Summary

```bash
# Most useful commands:
php artisan test                           # Run all
php artisan test --parallel                # Fast
php artisan test --filter=booking          # Specific
php artisan test --coverage                # Coverage %
php artisan test --verbose                 # Details
php artisan test --watch                   # Watch mode
```

**Next Steps:**
1. Run `php artisan test` to verify setup
2. Try `php artisan test --filter=test_appointment`
3. Explore each test file to understand scenarios
4. Add more tests for edge cases
5. Integrate into CI/CD pipeline

---

**Good luck with testing! All tests are ready to run.** ✅
