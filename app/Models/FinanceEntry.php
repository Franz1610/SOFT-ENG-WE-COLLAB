<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceEntry extends Model
{
    protected $fillable = [
        'booking_id',
        'customer_name',
        'gross_total',
        'transaction_date',
        'amount_received',
        'payment_method',
        'gateway_fee',
        'tax_collected',
        'reference_notes',
        'net_revenue',
        'status',
        'created_by',
        'reviewed_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'gross_total' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'tax_collected' => 'decimal:2',
        'net_revenue' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
