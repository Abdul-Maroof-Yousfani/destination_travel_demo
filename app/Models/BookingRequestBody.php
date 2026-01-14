<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Flight;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingRequestBody extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'airline',
        'ticket_limit',
        'payment_limit',
        'xml_body',
        'status',
        'client_id',
        'booking_id',
    ];

    protected $casts = [
        'ticket_limit' => 'datetime',
        'payment_limit' => 'datetime',
        'status' => 'boolean',
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
