<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ancillary extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'passenger_ref',
        'type',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Relation with Ticket
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
