<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline',
        'base_price',
        'base_price_code',
        'tax',
        'discount',
        'merchant_fee',
        'service_fee',
        'status',
        'is_approve',
        'is_refund',
        'refund_status',
        'payment_method',
        'transaction_id',
        'client_id',
        'booking_id',
    ];

    protected $casts = [
        'is_approve' => 'boolean',
        'is_refund' => 'boolean',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
