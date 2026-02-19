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
                'username' => 'jing',
                'name' => 'Administrator',
                'email' => 'jing@example.com',
                'password' => Hash::make('123456789x'),
                'tel' => '+1234567894',
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
