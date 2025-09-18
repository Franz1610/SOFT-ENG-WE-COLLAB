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
}
