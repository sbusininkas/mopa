<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin {email} {--role=admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role (admin, teacher, or student) to a user by email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->option('role');

        if (!in_array($role, ['admin', 'teacher', 'student'])) {
            $this->error('Invalid role. Must be one of: admin, teacher, student');
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->update(['role' => $role]);

        $roleNames = [
            'admin' => 'Administratorius',
            'teacher' => 'Mokytojas',
            'student' => 'Mokinys',
        ];

        $this->info("User {$user->name} ({$email}) role updated to {$roleNames[$role]}");
        return 0;
    }
}
