<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Segment;
use App\Helpers\BookingHelpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flight extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = [
        'logo',
        'duration',
        'departure_name',
        'arrival_name',
        'effective_price',
    ];
    protected $fillable = [
        'airline',
        'departure_code',
        'arrival_code',
        'departure_date',
        'arrival_date',
        'is_connected',
        'pax_count',
        'cabin_class',
        'price',
        'price_code',
        'client_id',
        'booking_id',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'arrival_date' => 'datetime',
        'is_connected' => 'boolean',
        'pax_count' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function segments()
    {
        return $this->hasMany(Segment::class);
    }
    // pax_count
    public function getPaxCountAttribute($value)
    {
        return $value ? json_decode($value, true) : [
            'adults' => 1,
            'children' => 0,
            'infant' => 0,
        ];
    }
    public function setPaxCountAttribute($value)
    {
        $this->attributes['pax_count'] = json_encode($value);
    }
    // logo
    public function getLogoAttribute()
    {
        $airline = strtolower(trim($this->airline));

        $logos = [
            'emirates'   => 'emiratemini.png',
            'flyjinnah'  => 'flyjinnahmini.png',
            'pia'        => 'piamini.png',
            // add more airlines and logos here as needed
        ];

        return $logos[$airline] ?? 'defaultmini.png';
    }
    // airline_code
    public function getAirlineCodeAttribute()
    {
        $airline = strtolower(trim($this->airline));

        $logos = [
            'emirates'   => 'EK',
            'flyjinnah'  => 'FJ',
            'pia'  => 'PIA',
        ];

        return $logos[$airline] ?? 'defaultmini.png';
    }
    // cabin_class
    public function getCabinClassAttribute($value)
    {
        $map = [
            'Y' => 'Economy',
            'C' => 'Business',
            'J' => 'Business',
            'F' => 'First Class',
            'W' => 'Premium Economy',
        ];

        return $map[strtoupper($value)] ?? $value;
    }
    // cabin_name_with_code
    public function getCabinNameWithCodeAttribute(): string
    {
        $code = strtoupper($this->attributes['cabin_class'] ?? '');
        $map = [
            'Y' => 'Economy',
            'W' => 'Premium Economy',
            'C' => 'Business',
            'J' => 'Business',
            'P' => 'First',
            'F' => 'First',
        ];

        $name = $map[$code] ?? 'Unknown';

        return "{$name} ({$code})";
    }
    // duration
    public function getDurationAttribute(): ?string
    {
        if (!$this->departure_date || !$this->arrival_date) {
            return null;
        }

        $diffInMinutes = $this->departure_date->diffInMinutes($this->arrival_date);

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        return sprintf("%dh %dm", $hours, $minutes);
    }

    // departure_name
    public function getDepartureNameAttribute(): string
    {
        return BookingHelpers::codeToCountry($this->departure_code);
    }

    // arrival_name
    public function getArrivalNameAttribute(): string
    {
        return BookingHelpers::codeToCountry($this->arrival_code);
    }
    // price_with_code
    public function getPriceWithCodeAttribute(): string
    {
        $price = (!empty($this->price) && $this->price > 0)
            ? $this->price
            : ($this->booking->price ?? 0);
        $code = $this->price_code ?: ($this->booking->price_code ?? '');
        return trim(strtoupper($code) . ' ' . number_format($price, 2));
    }
}
