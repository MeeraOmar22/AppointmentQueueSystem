<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /**
     * Test user creation
     */
    public function test_user_can_be_created()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
    }

    /**
     * Test user password is hashed
     */
    public function test_user_password_is_hashed()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * Test user role assignment
     */
    public function test_user_roles()
    {
        $staffUser = User::create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $this->assertEquals('staff', $staffUser->role);
        $this->assertEquals('admin', $adminUser->role);
    }

    /**
     * Test user email is unique
     */
    public function test_user_email_is_unique()
    {
        User::create([
            'name' => 'User 1',
            'email' => 'duplicate@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $this->expectException(\Exception::class);
        
        User::create([
            'name' => 'User 2',
            'email' => 'duplicate@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }

    /**
     * Test user can be deleted
     */
    public function test_user_can_be_deleted()
    {
        $user = User::create([
            'name' => 'Temp User',
            'email' => 'temp@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $userId = $user->id;
        $user->delete();

        $this->assertNull(User::find($userId));
    }

    /**
     * Test user fillable attributes
     */
    public function test_user_fillable_attributes()
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
    }
}
