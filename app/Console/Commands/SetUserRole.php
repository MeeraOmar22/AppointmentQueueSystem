<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:set-role {email} {role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user role by email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->argument('role');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found");
            return 1;
        }

        $validRoles = ['patient', 'staff', 'admin', 'developer'];
        if (!in_array($role, $validRoles)) {
            $this->error("Invalid role. Valid roles are: " . implode(', ', $validRoles));
            return 1;
        }

        $user->update(['role' => $role]);

        $this->info("User {$user->name} ({$email}) role updated to {$role}");
        return 0;
    }
}
