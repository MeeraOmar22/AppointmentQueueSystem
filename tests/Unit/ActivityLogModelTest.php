<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;
use Tests\TestCase;

class ActivityLogModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create required test data
        Service::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Test Service',
                'description' => 'Test',
                'estimated_duration' => 30,
                'duration_minutes' => 30,
                'status' => 1,
            ]
        );
        
        Dentist::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Dr. Test',
                'email' => 'dr@test.com',
                'phone' => '60123456789',
                'status' => true,
            ]
        );
    }

    /**
     * Test activity log creation
     */
    public function test_activity_log_can_be_created()
    {
        $log = ActivityLog::create([
            'action' => 'created',
            'model_type' => 'Appointment',
            'model_id' => 1,
            'description' => 'Appointment created',
            'old_values' => null,
            'new_values' => ['patient_name' => 'John'],
            'user_id' => null,
        ]);

        $this->assertNotNull($log->id);
        $this->assertEquals('created', $log->action);
        $this->assertEquals('Appointment', $log->model_type);
    }

    /**
     * Test activity log tracks changes
     */
    public function test_activity_log_tracks_model_changes()
    {
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        ActivityLog::create([
            'action' => 'created',
            'model_type' => 'Appointment',
            'model_id' => $appointment->id,
            'description' => 'Appointment created for John Doe',
            'old_values' => null,
            'new_values' => $appointment->toArray(),
            'user_id' => null,
        ]);

        $log = ActivityLog::where('model_id', $appointment->id)->first();
        
        $this->assertNotNull($log);
        $this->assertEquals($appointment->id, $log->model_id);
    }

    /**
     * Test activity log actions
     */
    public function test_activity_log_action_types()
    {
        $actions = ['created', 'updated', 'deleted'];
        
        foreach ($actions as $action) {
            ActivityLog::create([
                'action' => $action,
                'model_type' => 'Test',
                'model_id' => 1,
                'description' => "Test {$action}",
                'old_values' => null,
                'new_values' => null,
                'user_id' => null,
            ]);
        }

        $logs = ActivityLog::whereIn('action', $actions)->get();
        
        $this->assertEquals(3, $logs->count());
    }

    /**
     * Test activity log stores old and new values
     */
    public function test_activity_log_stores_change_values()
    {
        $oldValues = ['status' => 'booked'];
        $newValues = ['status' => 'completed'];

        ActivityLog::create([
            'action' => 'updated',
            'model_type' => 'Appointment',
            'model_id' => 1,
            'description' => 'Status updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => null,
        ]);

        $log = ActivityLog::where('action', 'updated')->first();
        
        $this->assertNotNull($log->old_values);
        $this->assertNotNull($log->new_values);
    }

    /**
     * Test activity log timestamp
     */
    public function test_activity_log_records_timestamp()
    {
        $before = now();
        
        $log = ActivityLog::create([
            'action' => 'created',
            'model_type' => 'Test',
            'model_id' => 1,
            'description' => 'Test',
            'old_values' => null,
            'new_values' => null,
            'user_id' => null,
        ]);

        $after = now();

        $this->assertNotNull($log->created_at);
        // SQLite doesn't preserve microseconds, so just check it's close
        $this->assertTrue($log->created_at->diffInSeconds($before) <= 1);
        $this->assertTrue($log->created_at->diffInSeconds($after) <= 1);
    }
}
