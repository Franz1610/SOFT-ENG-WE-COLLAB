<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to users who have 15 minutes left in their booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for bookings ending in 15 minutes...');

        // Get current time
        $now = Carbon::now();
        
        // Calculate the target time (15 minutes from now)
        $targetTime = $now->copy()->addMinutes(15);
        
        // Find bookings that are:
        // 1. Active today
        // 2. End time is exactly 15 minutes from now (with 1-minute tolerance)
        // 3. Status is confirmed
        // 4. Reminder has not been sent yet
        $bookings = Booking::whereDate('booking_date', $now->toDateString())
            ->where('status', 'confirmed') // Only send reminders for confirmed bookings
            ->whereNull('reminder_sent_at') // Only bookings that haven't received reminders yet
            ->get()
            ->filter(function ($booking) use ($now, $targetTime) {
                // Create full datetime for booking end time
                $bookingEndTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->end_time->format('H:i:s'));
                
                // Check if booking end time is between 14-16 minutes from now (gives 2-minute window)
                $diffInMinutes = $now->diffInMinutes($bookingEndTime, false);
                
                return $diffInMinutes >= 14 && $diffInMinutes <= 16 && $diffInMinutes > 0;
            });

        if ($bookings->isEmpty()) {
            $this->info('No bookings found ending in 15 minutes.');
            return 0;
        }

        $sentCount = 0;

        foreach ($bookings as $booking) {
            try {
                // Create a temporary notifiable object using the booking's email and name
                $notifiable = new class($booking->email, $booking->first_name) {
                    public $email;
                    public $name;
                    
                    public function __construct($email, $name) {
                        $this->email = $email;
                        $this->name = $name;
                    }
                    
                    public function routeNotificationForMail() {
                        return $this->email;
                    }
                };
                
                // Send notification to the booking email
                \Illuminate\Support\Facades\Notification::route('mail', $booking->email)
                    ->notify(new BookingReminder($booking));
                
                // Mark reminder as sent
                $booking->update(['reminder_sent_at' => $now]);
                
                $sentCount++;
                
                $this->info("Reminder sent to {$booking->first_name} {$booking->last_name} at {$booking->email} for booking #{$booking->id}");
                
                // Log the action
                Log::info("Booking reminder sent", [
                    'booking_id' => $booking->id,
                    'recipient_email' => $booking->email,
                    'recipient_name' => $booking->first_name . ' ' . $booking->last_name,
                    'room' => $booking->room_name,
                    'end_time' => $booking->end_time,
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for booking #{$booking->id}: " . $e->getMessage());
                Log::error("Failed to send booking reminder", [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Successfully sent {$sentCount} booking reminders.");
        
        return 0;
    }
}
