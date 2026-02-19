<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Message;

class CleanupMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:cleanup {--dry-run : Show what would be deleted without actually deleting} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove messages where users send to admin, keeping only admin-to-user messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🧹 Starting message cleanup...');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No data will be deleted');
        }
        
        // Get admin user ID
        $admin = User::where('user_type', 'admin')->first();
        
        if (!$admin) {
            $this->error('❌ No admin user found!');
            return 1;
        }
        
        $this->info("👤 Admin user found: {$admin->name} (ID: {$admin->id})");
        
        // Count messages to be deleted (user -> admin)
        $messagesToDelete = Message::where('sender_id', '!=', $admin->id)
            ->where('receiver_id', $admin->id)
            ->count();
            
        // Count messages to keep (admin -> user)
        $messagesToKeep = Message::where('sender_id', $admin->id)
            ->where('receiver_id', '!=', $admin->id)
            ->count();
            
        $this->info("📊 Current message statistics:");
        $this->line("   • Messages to DELETE (user → admin): {$messagesToDelete}");
        $this->line("   • Messages to KEEP (admin → user): {$messagesToKeep}");
        $this->line("   • Total messages: " . ($messagesToDelete + $messagesToKeep));
        
        if ($messagesToDelete === 0) {
            $this->info("✅ No messages to delete! Database is already clean.");
            return 0;
        }
        
        if ($isDryRun) {
            $this->info("🔍 DRY RUN: Would delete {$messagesToDelete} messages");
            
            // Show sample messages that would be deleted
            $sampleMessages = Message::where('sender_id', '!=', $admin->id)
                ->where('receiver_id', $admin->id)
                ->with(['sender', 'receiver'])
                ->limit(5)
                ->get();
                
            if ($sampleMessages->count() > 0) {
                $this->info("📝 Sample messages that would be deleted:");
                foreach ($sampleMessages as $message) {
                    $this->line("   • ID {$message->id}: '{$message->body}' from {$message->sender->name} to {$message->receiver->name}");
                }
            }
            
            return 0;
        }
        
        // Confirm deletion (skip if --force flag is used)
        $force = $this->option('force');
        if (!$force && !$this->confirm("⚠️  Are you sure you want to delete {$messagesToDelete} messages?")) {
            $this->info("❌ Operation cancelled.");
            return 0;
        }
        
        // Perform deletion
        $this->info("🗑️  Deleting messages...");
        
        $deletedCount = Message::where('sender_id', '!=', $admin->id)
            ->where('receiver_id', $admin->id)
            ->delete();
            
        $this->info("✅ Successfully deleted {$deletedCount} messages!");
        
        // Show final statistics
        $remainingMessages = Message::count();
        $this->info("📊 Final statistics:");
        $this->line("   • Remaining messages: {$remainingMessages}");
        $this->line("   • All remaining messages are admin → user");
        
        return 0;
    }
}
