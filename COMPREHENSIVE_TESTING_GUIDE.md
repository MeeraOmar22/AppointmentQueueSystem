# Comprehensive Testing Guide for Dental Clinic System

## Table of Contents
1. [Overview](#overview)
2. [Test Structure](#test-structure)
3. [Running Tests](#running-tests)
4. [Test Categories](#test-categories)
5. [Writing New Tests](#writing-new-tests)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Overview

This document describes the comprehensive testing framework for the Dental Clinic Management System. The system uses **PHPUnit** with **Laravel's testing framework** to ensure code quality and reliability.

### Testing Architecture
```
Tests/
├── Unit/                          # Unit tests for models and services
│   ├── Models/
│   │   ├── AppointmentModelTest.php
│   │   ├── DentistModelTest.php
│   │   ├── ServiceModelTest.php
│   │   ├── UserModelTest.php
│   │   ├── QueueModelTest.php
│   │   └── ActivityLogModelTest.php
│   └── Services/
│       ├── ActivityLoggerServiceTest.php
│       └── CheckInServiceTest.php
├── Feature/
│   ├── AppointmentManagementFeatureTest.php
│   ├── TreatmentCompletionFeatureTest.php
│   ├── CheckInFeatureTest.php
│   ├── BookingFeatureTest.php
│   ├── QueueManagementFeatureTest.php
│   ├── Api/
│   │   └── QueueApiTest.php
│   └── Integration/
│       └── QueueIntegrationTest.php
├── TestCase.php                   # Base test class
└── Pest.php                       # Pest configuration (optional)
```

---

## Test Structure

### Unit Tests
Test individual classes, methods, and functions in isolation.

**Location**: `tests/Unit/`

**Characteristics:**
- Test single responsibility
- Use mocks and stubs
- Fast execution
- No database access (unless explicitly tested)
- Test business logic

### Feature Tests
Test complete workflows and user interactions.

**Location**: `tests/Feature/`

**Characteristics:**
- Test complete features
- Access real database (testing DB)
- Simulate HTTP requests
- Test authentication/authorization
- Test form validation

### Integration Tests
Test multiple components working together.

**Location**: `tests/Feature/Integration/`

**Characteristics:**
- Test service-to-service interactions
- Test database relationships
- Test complex workflows
- Test queue management flow

### API Tests
Test API endpoints and responses.

**Location**: `tests/Feature/Api/`

**Characteristics:**
- Test HTTP status codes
- Test JSON responses
- Test API structure
- Test error handling

---

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test --testsuite=Unit        # Unit tests only
php artisan test --testsuite=Feature     # Feature tests only
```

### Run Specific Test File
```bash
php artisan test tests/Unit/AppointmentModelTest.php
php artisan test tests/Feature/QueueManagementFeatureTest.php
```

### Run Specific Test Method
```bash
php artisan test tests/Unit/AppointmentModelTest.php --filter=test_appointment_can_be_created
```

### Run Tests in Parallel (Faster)
```bash
php artisan test --parallel
php artisan test --parallel --processes=4  # Specify number of processes
```

### Run with Coverage Report
```bash
php artisan test --coverage
php artisan test --coverage --min=80    # Minimum coverage threshold
```

### Run Tests and Stop on Failure
```bash
php artisan test --stop-on-failure
php artisan test --bail
```

### Generate Detailed Report
```bash
php artisan test --verbose
```

---

## Test Categories

### 1. Unit Tests - Models

#### AppointmentModelTest
Tests the Appointment model functionality.

**Tests:**
- ✓ `test_appointment_can_be_created` - Create new appointment
- ✓ `test_appointment_generates_visit_token` - Visit token generation
- ✓ `test_appointment_relationships` - Model relationships

**Run:**
```bash
php artisan test tests/Unit/AppointmentModelTest.php
```

#### DentistModelTest
Tests the Dentist model functionality.

**Tests:**
- ✓ `test_dentist_can_be_created` - Create dentist
- ✓ `test_dentist_can_have_schedule` - Schedule relationships
- ✓ `test_dentist_can_be_deactivated` - Status management
- ✓ `test_active_dentists_scope` - Active dentist filtering

**Run:**
```bash
php artisan test tests/Unit/DentistModelTest.php
```

#### ServiceModelTest
Tests the Service model functionality.

**Tests:**
- ✓ `test_service_can_be_created` - Create service
- ✓ `test_service_status_can_be_toggled` - Status management
- ✓ `test_active_services_scope` - Active service filtering
- ✓ `test_service_duration_validation` - Duration constraints

**Run:**
```bash
php artisan test tests/Unit/ServiceModelTest.php
```

#### QueueModelTest
Tests the Queue model functionality.

**Tests:**
- ✓ `test_queue_can_be_created` - Create queue entry
- ✓ `test_queue_status_validation` - Status validation

**Run:**
```bash
php artisan test tests/Unit/QueueModelTest.php
```

#### UserModelTest
Tests the User model functionality.

**Tests:**
- ✓ `test_user_can_be_created` - Create user
- ✓ `test_user_password_is_hashed` - Password hashing
- ✓ `test_user_roles` - Role assignment
- ✓ `test_user_email_is_unique` - Email uniqueness

**Run:**
```bash
php artisan test tests/Unit/UserModelTest.php
```

#### ActivityLogModelTest
Tests the Activity Log model functionality.

**Tests:**
- ✓ `test_activity_log_can_be_created` - Create log entry
- ✓ `test_activity_log_tracks_changes` - Change tracking
- ✓ `test_activity_log_action_types` - Log actions (created, updated, deleted)
- ✓ `test_activity_log_stores_change_values` - Store old/new values

**Run:**
```bash
php artisan test tests/Unit/ActivityLogModelTest.php
```

### 2. Unit Tests - Services

#### ActivityLoggerServiceTest
Tests the ActivityLogger service.

**Tests:**
- ✓ `test_activity_logger_logs_created_action` - Log creation
- ✓ `test_activity_logger_logs_updated_action` - Log updates
- ✓ `test_activity_logger_logs_deleted_action` - Log deletions
- ✓ `test_activity_logger_stores_description` - Store descriptions
- ✓ `test_activity_logger_stores_json_values` - Store JSON data

**Run:**
```bash
php artisan test tests/Unit/Services/ActivityLoggerServiceTest.php
```

#### CheckInServiceTest
Tests the CheckInService functionality.

**Tests:**
- ✓ `test_check_in_service_creates_queue_entry` - Create queue
- ✓ `test_check_in_assigns_queue_number` - Queue numbering
- ✓ `test_check_in_updates_appointment_status` - Status update
- ✓ `test_check_in_records_check_in_time` - Record check-in time

**Run:**
```bash
php artisan test tests/Unit/Services/CheckInServiceTest.php
```

### 3. Feature Tests - Appointment Management

#### AppointmentManagementFeatureTest
Tests full appointment CRUD functionality.

**Tests:**
- ✓ `test_staff_can_view_appointments` - View appointment list
- ✓ `test_staff_can_create_appointment` - Create appointment
- ✓ `test_staff_can_update_appointment` - Update appointment
- ✓ `test_staff_can_delete_appointment` - Delete appointment
- ✓ `test_appointment_creation_requires_patient_name` - Validation
- ✓ `test_appointment_email_must_be_valid` - Email validation
- ✓ `test_appointment_clinic_location_must_be_valid` - Location validation
- ✓ `test_can_view_single_appointment` - View single appointment

**Run:**
```bash
php artisan test tests/Feature/AppointmentManagementFeatureTest.php
```

### 4. Feature Tests - Check-in

#### CheckInFeatureTest
Tests patient check-in functionality.

**Tests:**
- ✓ Check-in validation
- ✓ Queue entry creation
- ✓ Status updates

**Run:**
```bash
php artisan test tests/Feature/CheckInFeatureTest.php
```

### 5. Feature Tests - Booking

#### BookingFeatureTest
Tests appointment booking workflow.

**Tests:**
- ✓ Create booking
- ✓ Booking validation
- ✓ Confirm booking
- ✓ Walk-in appointments

**Run:**
```bash
php artisan test tests/Feature/BookingFeatureTest.php
```

### 6. Feature Tests - Treatment Completion

#### TreatmentCompletionFeatureTest
Tests treatment completion workflow.

**Tests:**
- ✓ `test_staff_can_view_treatment_completion_page` - View page
- ✓ `test_treatment_completion_page_shows_current_patient` - Display current
- ✓ `test_treatment_completion_page_shows_queue_status` - Show queue status
- ✓ `test_staff_can_mark_appointment_completed` - Mark complete
- ✓ `test_completion_updates_queue_status` - Update status
- ✓ `test_staff_can_pause_queue` - Pause queue
- ✓ `test_staff_can_resume_queue` - Resume queue
- ✓ `test_treatment_completion_page_shows_next_patient` - Display next patient
- ✓ `test_treatment_completion_shows_waiting_count` - Show waiting count

**Run:**
```bash
php artisan test tests/Feature/TreatmentCompletionFeatureTest.php
```

### 7. Feature Tests - Queue Management

#### QueueManagementFeatureTest
Tests complete queue management.

**Tests:**
- ✓ `test_check_in_creates_queue_entry` - Create queue
- ✓ `test_queue_number_is_assigned_on_check_in` - Assign queue number
- ✓ `test_queue_status_can_be_updated` - Update status
- ✓ `test_queue_api_endpoint` - API access
- ✓ `test_queue_entries_for_today` - Daily filtering
- ✓ `test_count_waiting_patients` - Count waiting

**Run:**
```bash
php artisan test tests/Feature/QueueManagementFeatureTest.php
```

### 8. API Tests

#### QueueApiTest
Tests queue API endpoints.

**Tests:**
- ✓ `test_queue_status_api_returns_correct_structure` - API structure
- ✓ `test_queue_status_api_empty_queue` - Empty queue response
- ✓ `test_queue_status_api_with_current_patient` - Current patient endpoint
- ✓ `test_queue_status_api_includes_waiting_count` - Waiting count API
- ✓ `test_queue_status_api_includes_next_patients` - Next patients API

**Run:**
```bash
php artisan test tests/Feature/Api/QueueApiTest.php
```

### 9. Integration Tests

#### QueueIntegrationTest
Tests complete queue workflows.

**Tests:**
- ✓ `test_complete_patient_workflow` - Full workflow (booking→checkin→treatment→complete)
- ✓ `test_multiple_patients_queue_flow` - Multiple patients
- ✓ `test_pause_and_resume_queue_flow` - Pause/resume
- ✓ `test_queue_auto_progression` - Auto-progression
- ✓ `test_appointment_validation_in_booking` - Booking validation
- ✓ `test_dentist_availability_prevents_double_booking` - Availability check

**Run:**
```bash
php artisan test tests/Feature/Integration/QueueIntegrationTest.php
```

---

## Writing New Tests

### Test Template - Unit Test

```php
<?php

namespace Tests\Unit;

use App\Models\YourModel;
use Tests\TestCase;

class YourModelTest extends TestCase
{
    /**
     * Test description
     */
    public function test_something_works()
    {
        // Arrange - Set up test data
        $data = ['key' => 'value'];

        // Act - Perform the action
        $result = YourModel::create($data);

        // Assert - Verify the result
        $this->assertNotNull($result->id);
        $this->assertEquals('value', $result->key);
    }
}
```

### Test Template - Feature Test

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YourFeatureTest extends TestCase
{
    use RefreshDatabase;  // Reset database after each test

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up common test data
        $this->user = User::factory()->create();
    }

    /**
     * Test description
     */
    public function test_user_can_perform_action()
    {
        // Act as authenticated user
        $response = $this->actingAs($this->user)
            ->post('/route', ['data' => 'value']);

        // Assert response
        $response->assertStatus(200);
        $response->assertRedirect('/expected-route');
        
        // Assert database
        $this->assertDatabaseHas('table', ['field' => 'value']);
    }
}
```

### Best Practices for Test Writing

1. **Use Descriptive Names**: `test_user_can_create_appointment_for_today`
2. **Follow AAA Pattern**: Arrange, Act, Assert
3. **One Assert per Test** (when possible)
4. **Use Setup Methods**: Avoid repetition with `setUp()`
5. **Test Edge Cases**: Empty data, invalid input, boundary conditions
6. **Use Factories**: Create test data efficiently
7. **Isolate Tests**: No test should depend on another
8. **Clean Up**: Use `RefreshDatabase` trait

---

## Best Practices

### 1. Database Testing
```php
class YourTest extends TestCase
{
    use RefreshDatabase;  // Migrate and rollback for each test
    
    public function test_something()
    {
        $model = Model::create($data);
        $this->assertDatabaseHas('table', ['id' => $model->id]);
    }
}
```

### 2. Authentication Testing
```php
public function test_authenticated_user_can_access()
{
    $user = User::factory()->create();
    
    $this->actingAs($user)
        ->get('/protected-route')
        ->assertStatus(200);
}
```

### 3. Response Assertions
```php
$response = $this->get('/route');

$response->assertStatus(200);
$response->assertViewIs('view.name');
$response->assertViewHas('variable', $value);
$response->assertRedirect('/new-route');
$response->assertSessionHas('key', 'value');
$response->assertJsonPath('data.id', 1);
```

### 4. Model Assertions
```php
$this->assertDatabaseHas('table', ['field' => 'value']);
$this->assertDatabaseMissing('table', ['field' => 'value']);
$this->assertModelExists($model);
$this->assertModelMissing($model);
```

---

## Troubleshooting

### Common Issues

#### 1. Migration Errors
**Problem**: Tests fail with "Table doesn't exist"

**Solution**:
```bash
# Reset testing database
php artisan migrate:refresh --env=testing

# Or delete and regenerate
rm database/testing.sqlite
php artisan migrate --env=testing
```

#### 2. Tests Not Running
**Problem**: Test files not being discovered

**Solution**:
- Ensure namespace matches: `Tests\Unit\*` or `Tests\Feature\*`
- Check `phpunit.xml` configuration
- Verify class extends `TestCase`

#### 3. Database Not Refreshing
**Problem**: Data persists between tests

**Solution**: Add `RefreshDatabase` trait
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class YourTest extends TestCase
{
    use RefreshDatabase;
}
```

#### 4. Slow Tests
**Problem**: Tests take too long

**Solution**:
```bash
# Run in parallel
php artisan test --parallel

# Run only changed tests
php artisan test --only-failures

# Skip certain tests
php artisan test --exclude=tests/Feature/SlowTest.php
```

#### 5. Permission Errors
**Problem**: "Permission denied" when writing to files

**Solution**:
```bash
# Ensure proper permissions
chmod -R 775 storage bootstrap/cache
```

---

## Test Metrics

### Coverage Goals
- **Unit Tests**: 80%+ coverage
- **Feature Tests**: 70%+ coverage  
- **Overall**: 75%+ coverage

### Performance Goals
- **Unit Tests**: < 0.1s each
- **Feature Tests**: < 0.5s each
- **All Tests**: < 30s total

---

## Continuous Integration

### Running Tests Automatically
Add to `github` workflow or CI/CD pipeline:

```yaml
- name: Run Tests
  run: php artisan test --coverage --min=75
```

---

## Configuration Files

### phpunit.xml
Main test configuration file located at project root.

**Key Settings:**
- Test suites (Unit, Feature)
- Database connection (sqlite for testing)
- Environment variables
- Coverage rules

### .env.testing
Test-specific environment configuration.

**Key Variables:**
- `DB_DATABASE=database/testing.sqlite`
- `MAIL_MAILER=array` (Don't send real emails)
- `QUEUE_CONNECTION=sync` (Run queued jobs synchronously)
- `CACHE_DRIVER=array` (In-memory cache)

---

## Summary

| Category | Count | Coverage |
|----------|-------|----------|
| Unit Tests | 20+ | Models & Services |
| Feature Tests | 30+ | Controllers & Workflows |
| Integration Tests | 6+ | Complete Workflows |
| API Tests | 5+ | API Endpoints |
| **Total** | **61+** | **~75%** |

---

**Last Updated**: January 13, 2026  
**Test Framework**: PHPUnit 10.x with Laravel 12  
**Status**: ✅ Ready for Production

