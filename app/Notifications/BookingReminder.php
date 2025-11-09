<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;

class BookingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $endTime = Carbon::parse($this->booking->booking_date->format('Y-m-d') . ' ' . $this->booking->end_time->format('H:i:s'));
        $minutesLeft = (int) Carbon::now()->diffInMinutes($endTime);
        
        return (new MailMessage)
            ->subject('Booking Reminder - 15 Minutes Left')
            ->greeting('Hello ' . $this->booking->first_name . '!')
            ->line('This is a friendly reminder about your upcoming booking.')
            ->line('**You only have ' . $minutesLeft . ' minutes left** in your current booking session.')
            ->line('**Booking Details:**')
            ->line('• Room: ' . $this->booking->room_name)
            ->line('• Date: ' . $this->booking->booking_date->format('F j, Y'))
            ->line('• Time: ' . $this->booking->formatted_time)
            ->line('• End Time: ' . $endTime->format('g:i A'))
            ->line('Please make sure to wrap up your activities and prepare to vacate the room on time.')
            ->line('If you want to extend your time, kindly ask the front desk.')
            ->line('Thank you for using our booking system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'message' => 'You only have 15 minutes left in your booking',
            'end_time' => $this->booking->end_time,
            'room_name' => $this->booking->room_name,
        ];
    }
}