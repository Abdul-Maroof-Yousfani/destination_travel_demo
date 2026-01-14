<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'type',
        'notes',
        'changes',
        'image',
        'booking_id',
        'user_id',
    ];

    /**
     * Relationships
     */

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Log may belong to a user (nullable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
