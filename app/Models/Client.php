<?php

namespace App\Models;

use App\Models\Flight;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\BookingRequestBody;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'name',
        'phone_code',
        'phone',
        'email',
        'password',
        'original_password',
        'profile_path',
        'login_provider',
        'is_active',
        'accept_notification',
        'ip',
        'country_code',
        'country_name',
        'city_code',
        'city',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'accept_notification' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function passengers()
    {
        return $this->hasMany(Passenger::class);
    }

    public function bookingRequestBodies()
    {
        return $this->hasMany(BookingRequestBody::class);
    }

    protected static function booted()
    {
        static::creating(function ($client) {
            $client->ip = request()->ip();
        });
    }

    public function getFullPhoneAttribute()
    {
        $code = ltrim($this->phone_code, '+');
        return '+' . $code . $this->phone;
    }
}
