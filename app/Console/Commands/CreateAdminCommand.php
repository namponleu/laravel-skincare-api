<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--username=admin} {--password=admin123} {--email=admin@example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->option('username');
        $password = $this->option('password');
        $email = $this->option('email');

        // Check if admin already exists
        $adminExists = User::where('username', $username)->exists();
        
        if ($adminExists) {
            $this->info("Admin user '{$username}' already exists!");
            return;
        }

        // Create admin user
        User::create([
            'username' => $username,
            'password' => Hash::make($password),
            'name' => 'Administrator',
            'email' => $email,
            'tel' => '1234567890',
            'is_active' => true,
            'user_type' => 'admin',
        ]);

        $this->info("Admin user created successfully!");
        $this->line("Username: {$username}");
        $this->line("Password: {$password}");
        $this->line("Email: {$email}");
        $this->warn("Please change the password after first login!");
    }
}
