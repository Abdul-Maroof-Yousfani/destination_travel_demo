<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'promocode',
        'price',
        'price_code',
        'discount_in_percent',
        'expire_time',
    ];

    protected $dates = [
        'expire_time',
    ];
}
