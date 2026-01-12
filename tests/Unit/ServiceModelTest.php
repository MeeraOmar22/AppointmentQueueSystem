<?php

namespace Tests\Unit;

use App\Models\Service;
use Tests\TestCase;

class ServiceModelTest extends TestCase
{
    /**
     * Test service creation
     */
    public function test_service_can_be_created()
    {
        $service = Service::create([
            'name' => 'Root Canal',
            'description' => 'Root canal treatment',
            'estimated_duration' => 60,
            'duration_minutes' => 60,
            'status' => 1,
        ]);

        $this->assertNotNull($service->id);
        $this->assertEquals('Root Canal', $service->name);
        $this->assertEquals(60, $service->estimated_duration);
    }

    /**
     * Test service status can be toggled
     */
    public function test_service_status_can_be_toggled()
    {
        $service = Service::create([
            'name' => 'Cleaning',
            'description' => 'Teeth cleaning',
            'estimated_duration' => 30,
            'duration_minutes' => 30,
            'status' => 1,
        ]);

        $service->update(['status' => 0]);
        
        $this->assertEquals(0, $service->refresh()->status);
    }

    /**
     * Test active services scope
     */
    public function test_active_services_scope()
    {
        Service::create([
            'name' => 'Active Service',
            'description' => 'Active service',
            'estimated_duration' => 30,
            'duration_minutes' => 30,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Inactive Service',
            'description' => 'Inactive service',
            'estimated_duration' => 30,
            'duration_minutes' => 30,
            'status' => 0,
        ]);

        $activeServices = Service::where('status', 1)->get();
        
        $this->assertGreaterThanOrEqual(1, $activeServices->count());
    }

    /**
     * Test service duration validation
     */
    public function test_service_minimum_duration()
    {
        $service = Service::create([
            'name' => 'Quick Check',
            'description' => 'Quick checkup',
            'estimated_duration' => 5,
            'duration_minutes' => 5,
            'status' => 1,
        ]);

        $this->assertEquals(5, $service->estimated_duration);
    }

    /**
     * Test service relationships exist
     */
    public function test_service_has_appointments()
    {
        $this->assertTrue(
            method_exists(Service::class, 'appointments')
        );
    }
}
