<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CancelResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'xml_body',
        'booking_id',
    ];

    // protected $casts = [
    //     'ticket_limit' => 'datetime',
    //     'payment_limit' => 'datetime',
    //     'status' => 'boolean',
    // ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
