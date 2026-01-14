<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'booking_id',
        'client_id',
        'details',
        'error_type',
        'error_message',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
