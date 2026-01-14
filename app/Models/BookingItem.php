<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingItem extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'passenger_ref',
        'passenger_code',
        'services',
        'taxes',
        'price',
        'price_code',
        'booking_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }
    public function getTotalPriceAttribute(): string
    {
        $code = $this->price_code ?? 'Rs.';
        $amount = is_numeric($this->price) ? number_format($this->price, 2) : '0.00';
        return $code . ' ' . $amount;
    }
}
