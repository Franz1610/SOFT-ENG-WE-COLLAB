<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'contact',
        'email',
        'additional_info',
        'pax',
        'category',
        'room_id',
        'booking_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financeEntry()
    {
        return $this->hasOne(FinanceEntry::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getRoomNameAttribute(): string
    {
        $roomNames = [
            1 => 'Individual Room',
            2 => 'Master Room',
            3 => 'Common Room',
        ];

        return $roomNames[$this->room_id] ?? 'Unknown Room';
    }

    public function getFormattedTimeAttribute(): string
    {
        $startTime = \Carbon\Carbon::parse($this->start_time)->format('g:iA');
        $endTime = \Carbon\Carbon::parse($this->end_time)->format('g:iA');
        
        return $startTime . '-' . $endTime;
    }

    public function getCompanyNameAttribute(): string
    {
        // Generate a company name from user's name if not provided
        return $this->first_name . ' ' . $this->last_name . ' Booking';
    }

    public function getTotalPriceAttribute(): float
    {
        // Calculate total price based on room rate and duration
        // This is a simple calculation - you can modify based on your pricing logic
        $roomRates = [
            1 => 50.00, // Individual Room rate per hour
            2 => 100.00, // Master Room rate per hour  
            3 => 75.00, // Common Room rate per hour
        ];

        $startTime = \Carbon\Carbon::parse($this->start_time);
        $endTime = \Carbon\Carbon::parse($this->end_time);
        $hours = $endTime->diffInHours($startTime);
        
        $hourlyRate = $roomRates[$this->room_id] ?? 50.00;
        
        return $hours * $hourlyRate;
    }
}
