<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingFeedback extends Model
{
    protected $table = 'booking_feedback';

    protected $fillable = [
        'booking_id', 'user_id', 'rating', 'comment', 'reviewed'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}