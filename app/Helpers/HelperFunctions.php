<?php

namespace App\Helpers;

use DateTime;
use DateInterval;
use Carbon\Carbon;
use SimpleXMLElement;
use App\Models\Airport;
use Illuminate\Support\Facades\Cache;

class HelperFunctions
{
    // Wed, Aug 29 2025 02:32 AM
    public static function formatDateTimeForFlights($timeString, $time = null)
    {
        if (empty($timeString) && empty($time)) {
            return null;
        }
        if (!empty($timeString) && !empty($time)) {
            $dateTime = Carbon::parse($timeString . ' ' . $time);
        } else {
            $dateTime = Carbon::parse($timeString ?? $time);
        }
        return $dateTime->format('D, M d Y h:i A');
    }
    // 29 Aug 2025
    public static function formatDateForFlights($date)
    {
        if (empty($date)) return null;
        return Carbon::parse($date)->format('d M Y');
    }
    // 02:35 AM
    public static function formatTimeForFlights($time)
    {
        if (empty($time)) return null;
        return Carbon::parse($time)->format('h:i A');
    }
    public static function codeToCountry($code)
    {
        $code = is_array($code) ? ($code['value'] ?? null) : $code;

        if (!$code || !is_string($code)) {
            return 'Unknown';
        }
        // Cache for 360 minutes (6 hours)
        $airport = Airport::where('code', $code)->value('name');
        return $airport ?? 'Unknown';
        // $airports = Cache::remember('airports_list', 360, function () {
        //     return Airport::orderBy('name')->pluck('name', 'code')->toArray();
        // });
        // return $airports[$code] ?? $code ?? 'Unknown';
    }
    public static function codeToLocalCheck($code)
    {
        $code = is_array($code) ? ($code['value'] ?? null) : $code;
        if (!$code || !is_string($code)) return false;

        $isLocal = Airport::where('code', $code)->value('is_local');

        return (bool) $isLocal;
    }
    // 2h 40m
    public static function calculateDuration(?string $departure, ?string $arrival): ?string
    {
        if (empty($departure) || empty($arrival)) return null;

        try {
            $d1 = new \DateTime($departure);
            $d2 = new \DateTime($arrival);

            $interval = $d1->diff($d2);

            $hours   = ($interval->days * 24) + $interval->h;
            $minutes = $interval->i;

            return sprintf("%dh %dm", $hours, $minutes);
        } catch (\Exception $e) {
            return null;
        }
    }
    // PT1H45M to 1h 45m dont use yet
    public static function formatDuration(string $duration): ?string
    {
        if (preg_match('/^\d+h\s?\d+m$/', $duration)) {
            return $duration;
        }

        try {
            $interval = new DateInterval($duration);

            $totalMinutes = ($interval->h * 60) + $interval->i;

            if ($interval->s > 0) {
                $totalMinutes++;
            }

            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            return sprintf('%dh %02dm', $hours, $minutes);
        } catch (Exception $e) {
            return $duration;
        }
    }
    public static function getCabinClass($code)
    {
        $map = [
            'Y' => 'Economy',
            'C' => 'Business',
            'J' => 'Business',
            'F' => 'First Class',
            'W' => 'Premium Economy',
        ];

        return $map[strtoupper($code)] ?? $code;
    }
    public static function normalizeToArray($value) {
        if (empty($value)) return [];
        return array_keys($value) === range(0, count($value) - 1)
            ? $value
            : [$value];
    }
}