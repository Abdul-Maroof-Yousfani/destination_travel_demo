<?php

namespace App\Services;

use DOMDocument;
use SimpleXMLElement;
use App\Models\Airport;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class HelperService
{
    function postXml($url, $headers, $body)
    {
        $cookieJar = new CookieJar();
        return Http::withHeaders($headers)
        ->timeout(120)
        ->withOptions(['verify' => false, 'cookies' => $cookieJar])
        ->withBody($body, 'text/xml')->post($url);
    }
    function decodeJWTToken($token)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return 'Invalid JWT format';
        }
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
        return $payload ?: 'Invalid payload';
    }
    function XMLtoJSON($xml)
    {
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $xml = preg_replace('/(<\/?)[a-zA-Z0-9]+:/', '$1', $xml);
        $cleanXml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        return json_decode(json_encode($cleanXml), true);
    }
    function XMLtoJSONEmirate($xml)
    {
        libxml_use_internal_errors(true);
        $xml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xml);
        $xml = preg_replace('/(<\/?)[a-zA-Z0-9]+:/', '$1', $xml);
        $simpleXml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($simpleXml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return ["msg" => "Failed to parse XML", "errors" => $errors, "xml" => $xml];
        }
        return $this->simpleXml($simpleXml);
    }
    private function simpleXml(SimpleXMLElement $xml)
    {
        $result = [];
        $attributes = [];
        foreach ($xml->attributes() as $attr => $value) {
            $attributes[$attr] = (string) $value;
        }
        if (!empty($attributes)) {
            $result['@attributes'] = $attributes;
        }
        $hasChildren = false;
        foreach ($xml->children() as $child) {
            $hasChildren = true;
            $name = $child->getName();
            $value = $this->simpleXml($child);

            if (isset($result[$name])) {
                if (!is_array($result[$name]) || !isset($result[$name][0])) {
                    $result[$name] = [$result[$name]];
                }
                $result[$name][] = $value;
            } else {
                $result[$name] = $value;
            }
        }
        $text = trim((string)$xml);
        if (!$hasChildren && $text !== '') {
            $result['value'] = $text;
        } elseif (!$hasChildren && empty($result)) {
            $result = null;
        }
    
        return $result;
    }


    function codeToCountry($code)
    {
        $code = is_array($code) ? ($code['value'] ?? null) : $code;

        if (!$code || !is_string($code)) {
            return 'Unknown';
        }
        // Cache for 360 minutes (6 hours)
        $airports = Cache::remember('airports_list', 360, function () {
            return Airport::orderBy('name')->pluck('name', 'code')->toArray();
        });
        return $airports[$code] ?? $code ?? 'Unknown';
    }

    function codeToLocalCheck($code)
    {
        $code = is_array($code) ? ($code['value'] ?? null) : $code;

        if (!$code || !is_string($code)) {
            return 'Unknown';
        }
        // Cache for 360 minutes (6 hours)
        $airports = Cache::remember('airports_local_check', 360, function () {
            return Airport::pluck('is_local', 'code')->toArray();
        });

        return isset($airports[$code]) ? (bool)$airports[$code] : false;
    }

    function formatXml(string $xml): string
    {
        // Check if XML is empty or only whitespace
        if (trim($xml) === '') {
            return $xml;
        }
        
        try {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            return $dom->saveXML();
        } catch (\Throwable $e) {
            // If invalid XML or any error, return original string
            return $xml;
        }
    }
}
