<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    // $initialBookings = Booking::initial()->get();
    // $issuedCount = Booking::issued()->count();
    // $bookings = Booking::where('status', Booking::STATUS_PENDING)->get();

    protected $appends = ['canceled_at'];

    public const STATUS_EXPIRED = 'expired';
    public const STATUS_INITIAL = 'initial';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_ERROR = 'error';
    public const STATUS_CHANGED = 'changed';
    public const STATUS_CANCEL = 'cancel';

    use HasFactory, SoftDeletes;
    protected $fillable = [
        'order_id',
        'order_owner',
        'flight_booking_id',
        'ticket_limit',
        'payment_limit',
        'price_code',
        'price',
        'tax',
        'tax_code',
        'only_search',
        'status',
        'is_oneway',
        'type',
        'airline',
        'airline_id',
        'airline_code',
        'passenger_details',
        'transaction_id',
        'client_id',
        'agent_id',
    ];

    protected $casts = [
        'ticket_limit' => 'datetime',
        'payment_limit' => 'datetime',
        'is_oneway' => 'boolean',
        'only_search' => 'boolean',
        'passenger_details' => 'array',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_EXPIRED,
            self::STATUS_INITIAL,
            self::STATUS_ERROR,
            self::STATUS_PENDING,
            self::STATUS_ISSUED,
            self::STATUS_CHANGED,
            self::STATUS_CANCEL,
        ];
    }

    public function agent()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCancel($query)
    {
        return $query->where('status', self::STATUS_CANCEL);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeChanged($query)
    {
        return $query->where('status', self::STATUS_CHANGED);
    }

    public function scopeError($query)
    {
        return $query->where('status', self::STATUS_ERROR);
    }

    public function scopeInitial($query)
    {
        return $query->where('status', self::STATUS_INITIAL);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeIssued($query)
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }
    
    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function bookingRequest()
    {
        return $this->hasOne(BookingRequestBody::class);
    }

    public function errorLogs()
    {
        return $this->hasMany(ErrorLog::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function cancelResponse()
    {
        return $this->hasOne(CancelResponse::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected function canceledAt(): Attribute
    {
        return Attribute::get(function () {
            if ($this->status === self::STATUS_CANCEL) {
                return $this->cancelResponse?->created_at ?? $this->updated_at;
            }
            return null;
        });
    }

    public function getFlightSummary(): string
    {
        // Use type column if available, otherwise fallback to is_oneway
        $tripType = 'RETURN';
        if ($this->type) {
            $tripType = strtoupper($this->type);
        } else {
            $tripType = $this->is_oneway ? 'ONEWAY' : 'RETURN';
        }
        
        $airlineCode = strtoupper($this->airline_id ?? $this->order_id);
        $routes = $this->flights->map(function ($flight) {
            return strtoupper($flight->departure_code . '-' . $flight->arrival_code);
        })->implode("\n");
        if (empty($routes)) {
            $routes = 'N/A';
        }
        return "{$tripType}\n{$airlineCode}\n{$routes}";
    }
    // total_price
    public function getTotalPriceAttribute(): string
    {
        $code = $this->price_code ?? 'Rs.';
        $amount = is_numeric($this->price) ? number_format($this->price, 2) : '0.00';
        return $code . ' ' . $amount;
    }
    // total_tax_price
    public function getTotalTaxPriceAttribute(): string
    {
        $tax = (float) ($this->tax ?? 0);
        $price = is_numeric($this->price) ? (float) $this->price : 0;
        $totalPrice = round($price + $tax, 2);
        $code = $this->price_code ?? 'Rs.';
        return $code . ' ' . number_format($totalPrice, 2);
    }
    // type
    // public function getTypeAttribute(): string
    // {
    //     return $this->is_oneway ? 'ONEWAY' : 'RETURN';
    // }



}