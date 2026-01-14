<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'airline',
        'passenger_reference',
        'place', // Ticket issue place
        'ticket_no',
        'ticket_numbers',
        'type',
        'issue_date',
        'price_code',
        'price',
        'status',
        'price_reference',
        'ticket_details',
        'client_id',
        'booking_id',
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'ticket_numbers' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function ancillaries()
    {
        return $this->hasMany(Ancillary::class);
    }

    protected $with = ['ancillaries'];
}
