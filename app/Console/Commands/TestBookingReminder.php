<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestBookingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:test-reminder {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test booking that ends in 15 minutes for testing reminder functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $now = Carbon::now();
        $endTime = $now->copy()->addMinutes(15); // Ends in exactly 15 minutes
        $startTime = $now->copy()->subMinutes(45); // Started 45 minutes ago (1 hour total)

        $booking = Booking::create([
            'user_id' => $userId,
            'first_name' => $user->name,
            'last_name' => 'Test',
            'contact' => '1234567890',
            'email' => $user->email,
            'additional_info' => 'Test booking for reminder functionality',
            'pax' => 1,
            'category' => 'individual', // Valid enum value
            'room_id' => 1, // Individual Room
            'booking_date' => $now->toDateString(),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'status' => 'confirmed', // Use confirmed instead of approved
        ]);

        $this->info("Test booking created successfully!");
        $this->info("Booking ID: {$booking->id}");
        $this->info("User: {$user->name}");
        $this->info("Room: {$booking->room_name}");
        $this->info("Date: {$booking->booking_date->format('Y-m-d')}");
        $this->info("Time: {$startTime->format('g:i A')} - {$endTime->format('g:i A')}");
        $this->info("Ends in: 15 minutes");
        $this->info("");
        $this->info("Now run: php artisan bookings:send-reminders");

        return 0;
    }
}
