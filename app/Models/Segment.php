<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Segment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'departure_code',
        'arrival_code',
        'departure_date',
        'arrival_date',
        'flight_number',
        'flight_duration',
        'direction',
        'price',
        'price_code',
        'flight_id',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }
    public function getFormattedDurationAttribute(): string
    {
        try {
            if (!$this->flight_duration) {
                return 'N/A';
            }
            // If it's already a formatted string (e.g., "02h 30m"), just return it
            if (!str_starts_with($this->flight_duration, 'PT')) {
                return $this->flight_duration;
            }
            $interval = new \DateInterval($this->flight_duration);
            $parts = [];
            if ($interval->h > 0) {
                $parts[] = "{$interval->h}h";
            }
            if ($interval->i > 0) {
                $parts[] = "{$interval->i}m";
            }
            return implode(' ', $parts) ?: '0m';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

}
