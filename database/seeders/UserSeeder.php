<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't truncate users to preserve existing admin accounts
        // User::truncate(); // Commented out to preserve existing users

        $users = [
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'), // Default admin password
                'tel' => '+1234567890',
                'is_active' => true,
                'user_type' => 'admin',
            ],
            [
                'username' => 'ponleu',
                'name' => 'Administrator',
                'email' => 'ponleu@example.com',
                'password' => Hash::make('ponleu123'),
                'tel' => '+0121413156',
                'is_active' => true,
                'user_type' => 'admin',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']], 
                $user
            );
        }
    }
}
