<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'category',
        'room_number',
        'status'
    ];

    protected $casts = [
        'category' => 'string',
        'status' => 'string'
    ];
}
