<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::select('id', 'name', 'email', 'role')->get();

        if ($users->isEmpty()) {
            $this->info('No users found');
            return 0;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role'],
            $users->map(fn ($u) => [$u->id, $u->name, $u->email, $u->role])->toArray()
        );

        return 0;
    }
}
