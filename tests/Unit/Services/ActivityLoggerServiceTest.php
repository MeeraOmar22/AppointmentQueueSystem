<?php

namespace Tests\Unit\Services;

use App\Services\ActivityLogger;
use App\Models\ActivityLog;
use Tests\TestCase;

class ActivityLoggerServiceTest extends TestCase
{
    /**
     * Test activity logger can log creation
     */
    public function test_activity_logger_logs_created_action()
    {
        $newValues = ['name' => 'Test', 'id' => 1];

        ActivityLogger::log('created', 'Test', 1, 'Test created', null, $newValues);

        $log = ActivityLog::where('action', 'created')
            ->where('model_id', 1)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('created', $log->action);
    }

    /**
     * Test activity logger can log update
     */
    public function test_activity_logger_logs_updated_action()
    {
        $oldValues = ['status' => 'pending'];
        $newValues = ['status' => 'completed'];

        ActivityLogger::log('updated', 'Appointment', 1, 'Status updated', $oldValues, $newValues);

        $log = ActivityLog::where('action', 'updated')
            ->where('model_id', 1)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('updated', $log->action);
    }

    /**
     * Test activity logger can log deletion
     */
    public function test_activity_logger_logs_deleted_action()
    {
        $oldValues = ['name' => 'Test', 'id' => 1];

        ActivityLogger::log('deleted', 'Appointment', 1, 'Deleted', $oldValues, null);

        $log = ActivityLog::where('action', 'deleted')
            ->where('model_id', 1)
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('deleted', $log->action);
    }

    /**
     * Test activity logger stores description
     */
    public function test_activity_logger_stores_description()
    {
        $description = 'Appointment created for John Doe';
        
        ActivityLogger::log('created', 'Appointment', 1, $description, null, []);

        $log = ActivityLog::where('model_id', 1)->first();

        $this->assertEquals($description, $log->description);
    }

    /**
     * Test activity logger stores JSON values
     */
    public function test_activity_logger_stores_json_values()
    {
        $newValues = [
            'patient_name' => 'John',
            'patient_phone' => '60123456789',
            'service_id' => 1,
        ];

        ActivityLogger::log('created', 'Appointment', 1, 'Test', null, $newValues);

        $log = ActivityLog::where('model_id', 1)->first();

        $this->assertIsArray($log->new_values);
        $this->assertEquals('John', $log->new_values['patient_name']);
    }
}
