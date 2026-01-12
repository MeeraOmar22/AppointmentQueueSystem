<?php

namespace Tests\Unit;

use App\Models\Dentist;
use App\Models\DentistSchedule;
use Tests\TestCase;

class DentistModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test dentist
        $this->dentist = Dentist::create([
            'name' => 'Dr. Smith',
            'email' => 'smith@clinic.com',
            'phone' => '60123456789',
            'status' => true,
        ]);
    }

    /**
     * Test dentist creation
     */
    public function test_dentist_can_be_created()
    {
        $dentist = Dentist::create([
            'name' => 'Dr. John',
            'email' => 'john@clinic.com',
            'phone' => '60187654321',
            'status' => true,
        ]);

        $this->assertNotNull($dentist->id);
        $this->assertEquals('Dr. John', $dentist->name);
        $this->assertTrue($dentist->status);
    }

    /**
     * Test dentist can have schedule
     */
    public function test_dentist_can_have_schedule()
    {
        DentistSchedule::create([
            'dentist_id' => $this->dentist->id,
            'day_of_week' => 'Monday',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_available' => true,
        ]);

        $this->assertTrue($this->dentist->schedules()->exists());
        $this->assertEquals('Monday', $this->dentist->schedules()->first()->day_of_week);
    }

    /**
     * Test dentist can be deactivated
     */
    public function test_dentist_can_be_deactivated()
    {
        $this->dentist->update(['status' => false]);
        
        $this->assertFalse((bool) $this->dentist->refresh()->status);
    }

    /**
     * Test dentist retrieves only active dentists
     */
    public function test_active_dentists_scope()
    {
        Dentist::create([
            'name' => 'Dr. Inactive',
            'email' => 'inactive@clinic.com',
            'phone' => '60111111111',
            'status' => false,
        ]);

        $activeDentists = Dentist::where('status', true)->get();
        
        $this->assertEquals(1, $activeDentists->count());
        $this->assertEquals('Dr. Smith', $activeDentists->first()->name);
    }

    /**
     * Test dentist has many appointments relationship
     */
    public function test_dentist_has_many_appointments()
    {
        $this->assertTrue(
            method_exists($this->dentist, 'appointments')
        );
    }

    /**
     * Test dentist schedule relationship
     */
    public function test_dentist_has_many_schedules()
    {
        $this->assertTrue(
            method_exists($this->dentist, 'schedules')
        );
    }
}
