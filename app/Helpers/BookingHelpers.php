<?php

namespace App\Helpers;

use App\Models\Airport;
use Illuminate\Support\Facades\Cache;

class BookingHelpers
{
    public static function codeToCountry($code)
    {
        $code = is_array($code) ? ($code['value'] ?? null) : $code;

        if (!$code || !is_string($code)) {
            return 'Unknown';
        }
        $airports = Cache::rememberForever('airports_list', function () {
            return Airport::orderBy('name')->pluck('name', 'code')->toArray();
        });

        return $airports[$code] ?? $code ?? 'Unknown';
    }
}