<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class VerifyAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:verify-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all users as email verified for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting email verification for all users...');
        
        // Get all unverified users
        $unverifiedUsers = User::whereNull('email_verified_at')->get();
        
        if ($unverifiedUsers->count() === 0) {
            $this->info('All users are already verified!');
            return;
        }
        
        $this->info("Found {$unverifiedUsers->count()} unverified users.");
        
        // Verify all users
        $verifiedCount = 0;
        foreach ($unverifiedUsers as $user) {
            $user->markEmailAsVerified();
            $verifiedCount++;
            $this->line("✓ Verified: {$user->email}");
        }
        
        $this->info("Successfully verified {$verifiedCount} users!");
        
        // Show all users status
        $this->line('');
        $this->info('Current user status:');
        $allUsers = User::all();
        
        foreach ($allUsers as $user) {
            $status = $user->hasVerifiedEmail() ? '✓ Verified' : '✗ Not Verified';
            $role = $user->role ?? 'user';
            $this->line("• {$user->email} ({$role}) - {$status}");
        }
    }
}
