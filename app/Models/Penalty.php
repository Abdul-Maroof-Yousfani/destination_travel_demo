<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penalty extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'arrival',
        'destination',
        'cancel_fee',
        'change_fee',
        'refund_fee',
        'cabin_type',
        'booking_item_id',
    ];

    protected $casts = [
        'cancel_fee' => 'array',
        'change_fee' => 'array',
        'refund_fee' => 'array',
    ];
    public function bookingItem()
    {
        return $this->belongsTo(BookingItem::class);
    }
}
