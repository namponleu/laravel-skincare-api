<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;
use App\Models\User;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users from the database
        $users = User::limit(5)->get();
        
        if ($users->count() < 2) {
            $this->command->info('Need at least 2 users to create messages. Creating test users...');
            
            // Create test users if none exist
            $user1 = User::create([
                'username' => 'testuser1',
                'name' => 'Test User 1',
                'email' => 'test1@example.com',
                'password' => bcrypt('password123'),
                'tel' => '1234567890',
                'user_type' => 'user',
                'is_active' => true
            ]);
            
            $user2 = User::create([
                'username' => 'testuser2',
                'name' => 'Test User 2',
                'email' => 'test2@example.com',
                'password' => bcrypt('password123'),
                'tel' => '0987654321',
                'user_type' => 'user',
                'is_active' => true
            ]);
            
            $users = collect([$user1, $user2]);
        }

        $this->command->info('Creating test messages...');

        // Create sample messages between users
        $messages = [
            [
                'sender_id' => $users[0]->id,
                'receiver_id' => $users[1]->id,
                'body' => 'Hello! How are you today?',
            ],
            [
                'sender_id' => $users[1]->id,
                'receiver_id' => $users[0]->id,
                'body' => 'Hi! I am doing great, thanks for asking!',
            ],
            [
                'sender_id' => $users[0]->id,
                'receiver_id' => $users[1]->id,
                'body' => 'That is wonderful to hear!',
            ],
            [
                'sender_id' => $users[1]->id,
                'receiver_id' => $users[0]->id,
                'body' => 'What are you up to today?',
            ],
            [
                'sender_id' => $users[0]->id,
                'receiver_id' => $users[1]->id,
                'body' => 'Just working on some projects. How about you?',
            ],
        ];

        // Add more messages if we have more users
        if ($users->count() >= 3) {
            $messages = array_merge($messages, [
                [
                    'sender_id' => $users[0]->id,
                    'receiver_id' => $users[2]->id,
                    'body' => 'Hey! Long time no see!',
                ],
                [
                    'sender_id' => $users[2]->id,
                    'receiver_id' => $users[0]->id,
                    'body' => 'Yes! How have you been?',
                ],
                [
                    'sender_id' => $users[1]->id,
                    'receiver_id' => $users[2]->id,
                    'body' => 'Hello there!',
                ],
                [
                    'sender_id' => $users[2]->id,
                    'receiver_id' => $users[1]->id,
                    'body' => 'Hi! Nice to meet you!',
                ],
            ]);
        }

        // Create the messages
        foreach ($messages as $messageData) {
            Message::create($messageData);
        }

        $this->command->info('Created ' . count($messages) . ' test messages!');
        
        // Show summary
        $totalMessages = Message::count();
        $this->command->info("Total messages in database: {$totalMessages}");
        
        // Show conversations
        $conversations = Message::selectRaw('
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as other_user_id,
            COUNT(*) as message_count
        ', [$users[0]->id])
        ->where(function($query) use ($users) {
            $query->where('sender_id', $users[0]->id)
                  ->orWhere('receiver_id', $users[0]->id);
        })
        ->groupBy('other_user_id')
        ->get();

        $this->command->info("User {$users[0]->username} has conversations with:");
        foreach ($conversations as $conv) {
            $otherUser = User::find($conv->other_user_id);
            $this->command->info("- {$otherUser->username} ({$conv->message_count} messages)");
        }
    }
}