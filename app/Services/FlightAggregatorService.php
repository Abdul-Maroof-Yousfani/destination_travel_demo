<?php

namespace App\Services;

use App\Models\Airport;
use App\Services\PiaService;
use App\Helpers\HelperFunctions;
use App\Services\AirblueService;
use App\Services\EmiratesService;
use App\Services\FlyJinnahService;
use Illuminate\Support\Collection;
// 1) if in bundles have array so show directly bundles of this flight (speacially in emirates)
// 2) if in bundles have key only so match bundle from outside flights bundles tag and show  (speacially in pia)
// 3) if in bundles if null so fetch bundles from api (speacially in flyjinnah)
class FlightAggregatorService
{
    protected $services;

    public function __construct(
        HelperFunctions $helper,
        PiaService $pia,
        EmiratesService $emirate,
        FlyJinnahService $flyjinnah,
        AirblueService $airblue
    ) {
        $this->services = [$emirate, $airblue];
        // $this->services = [$emirate, $flyjinnah, $airblue];
        // $this->services = [$pia, $emirate, $flyjinnah];
        // $this->services = [$flyjinnah];
        // $this->services = [$emirate];
        // $this->services = [$airblue];
        // $this->services = [$pia];
        $this->helper = $helper;
    }
    public function searchAllFlights($params)
    {
        $arrCode  = $params['arr'] ?? null;
        $destCode = $params['dest'] ?? null;

        $skipCarriers = [];
        $isMulti = ($params['routeType'] ?? '') === 'MULTI' || count($params['segments'] ?? []) > 2;

        if ($arrCode && $destCode) {
            $airports = Airport::whereIn('code', [$arrCode, $destCode])->pluck('is_local', 'code');
            $arrIsLocal  = $airports[$arrCode] ?? null;
            $destIsLocal = $airports[$destCode] ?? null;
            if ($arrIsLocal === true && $destIsLocal === true) {
                $skipCarriers = config('flight.skip_local', []);
            }
        }

        $outboundFlights = collect();
        $inboundFlights = collect();
        $allBundles = collect();
        $allExtras = collect();
        $allErrors = collect();
        if ($isMulti) {
            $allLegs = collect(); // Will be collect of leg collections (each leg has its options)
            if(!empty($this->services)) {
                foreach ($this->services as $service) {
                    $carrier = strtolower($service->getCarrierName());
                    if (in_array($carrier, $skipCarriers, true)) continue;

                    $rawFlights = $service->searchFlights($params);
                    $normalized = $this->normalizeFlights($rawFlights, $service->getCarrierName());
                    
                    // For multi, append all legs from this service (assuming services return all legs)
                    foreach ($normalized['flights'] as $leg) {
                        $allLegs->push($leg);
                    }
                }
            }
            // dd($rawFlights, $normalized, $allLegs);

            $totalCount = $allLegs->flatten(1)->filter(fn($f) => (float)preg_replace('/[^\d.]/', '', $f['price'] ?? 0) > 0)->count();

            return [
                'flights' => $allLegs->map(fn($leg) => $leg->sortBy(fn($f) => (float)preg_replace('/[^\d.]/', '', $f['price'] ?? 0))->values()),
                'total_count' => $totalCount,
                'bundles' => $allBundles,
                'extras' => $allExtras,
                'errors' => $allErrors,
            ];
        } else {
            if(!empty($this->services)) {
            foreach ($this->services as $service) {
                    $carrier = strtolower($service->getCarrierName());
                    if (in_array($carrier, $skipCarriers, true)) {
                        continue;
                    }

                    $rawFlights = $service->searchFlights($params);
                    // dd($rawFlights);
                    $normalized = $this->normalizeFlights($rawFlights, $service->getCarrierName());

                    // Collect bundles
                    if ($carrier === 'pia') {
                        $allBundles = $allBundles->merge($normalized['bundles'] ?? []);
                    }
                    // elseif ($carrier === 'flyjinnah') {
                    //     // Fetch bundles if needed (implement in FlyJinnahService)
                    //     $bundles = [];
                    //     $allBundles = $allBundles->merge($bundles);
                    // }
                    // Emirates bundles are per-flight, so no global merge needed here

                    // Collect errors
                    if (!empty($normalized['errors'])) {
                        $allErrors = $allErrors->merge($normalized['errors']);
                    }
                    if (!empty($normalized['extras'])) {
                        $allExtras = $allExtras->merge($normalized['extras']);
                    }

                    // Merge flights (index 0 = outbound, 1 = inbound)
                    $outbound = $normalized['flights'][0] ?? collect();
                    $inbound = $normalized['flights'][1] ?? collect();
                    $outboundFlights = $outboundFlights->merge($outbound);
                    $inboundFlights = $inboundFlights->merge($inbound);
                } 
            }

            // Sort by price
            // $outboundFlights = $outboundFlights->sortBy('price')->values();
            // $inboundFlights = $inboundFlights->sortBy('price')->values();
            $outboundFlights = $outboundFlights->filter(function ($flight) {
                $price = (float) preg_replace('/[^\d.]/', '', $flight['price'] ?? 0);
                return $price > 0;
            })->values();

            $outboundFlights = $outboundFlights->sortBy(function ($flight) {
                return (float) preg_replace('/[^\d.]/', '', $flight['price'] ?? 0);
            })->values();

            $inboundFlights = $inboundFlights->sortBy(function ($flight) {
                return (float) preg_replace('/[^\d.]/', '', $flight['price'] ?? 0);
            })->values();

            $flights = collect([$outboundFlights, $inboundFlights]);
            $departureCount = $outboundFlights->count();
            $returnCount = $inboundFlights->count();
            return [
                'flights' => $flights,
                'total_count' => $departureCount + $returnCount,
                'departure_count' => (int) $departureCount,
                'return_count' => (int) $returnCount,
                'bundles' => $allBundles,
                'extras' => $allExtras,
                'errors' => $allErrors
            ];
        }
    }
    private function normalizeFlights($rawData, $carrier)
    {
        $flightsCollection = collect();
        $bundles = collect();
        $extras = collect();
        $errors = collect();

        if ($carrier === 'pia') {
            $bundles = collect($rawData['bundles'] ?? []);
            $itineraries = $rawData['itineraries'] ?? [];
            $combinations = collect($rawData['combinations'] ?? []);
            $extras = collect([
                'pia' => [
                    'combinations' => $combinations,
                ]
            ]);

            $outbound = collect();
            $inbound = collect();

            foreach ($itineraries as $itinerary) {
                $direction = $itinerary['direction'] ?? 'outbound';
                $flightsRaw = $itinerary['flights'] ?? [];

                foreach ($flightsRaw as $flight) {
                    $journeyId = $flight['journey_id'] ?? null;
                    if (!$journeyId) continue;

                    // Find cheapest price for this journey across all bundles
                    $relevantCombos = $combinations->filter(function ($c) use ($journeyId) {
                        return isset($c['journeys'][$journeyId]);
                    });

                    if ($relevantCombos->isEmpty()) continue; 

                    // Get the absolute minimum price from all combinations for this journey
                    $price = $relevantCombos->min('total_price_pkr') ?? 0;
                    $segments = $flight['segments'] ?? [];

                    if (empty($segments)) continue;

                    $firstSegment = $segments[0];
                    $lastSegment = end($segments);

                    $depDatetime = $firstSegment['departure']['datetime'] ?? '';
                    $arrDatetime = $lastSegment['arrival']['datetime'] ?? '';

                    $formattedFlight = [
                        'carrier' => $carrier,
                        'cabinClass' => $this->helper::getCabinClass('Y'),
                        'departure' => [
                            'code' => $firstSegment['departure']['airport'] ?? '',
                            'airport' => $this->helper::codeToCountry($firstSegment['departure']['airport'] ?? ''),
                            'local' => $this->helper::codeToLocalCheck($firstSegment['departure']['airport'] ?? ''),
                            'datetime' => $this->helper::formatDateTimeForFlights($depDatetime),
                            'date' => $this->helper::formatDateForFlights($depDatetime),
                            'time' => $this->helper::formatTimeForFlights($depDatetime),
                        ],
                        'arrival' => [
                            'code' => $lastSegment['arrival']['airport'] ?? '',
                            'airport' => $this->helper::codeToCountry($lastSegment['arrival']['airport'] ?? ''),
                            'local' => $this->helper::codeToLocalCheck($lastSegment['arrival']['airport'] ?? ''),
                            'datetime' => $this->helper::formatDateTimeForFlights($arrDatetime),
                            'date' => $this->helper::formatDateForFlights($arrDatetime),
                            'time' => $this->helper::formatTimeForFlights($arrDatetime),
                        ],
                        'duration' => $this->helper::calculateDuration($depDatetime, $arrDatetime),
                        'isConnected' => count($segments) > 1,
                        'price' => number_format($price, 2, '.', ''),
                        'code' => 'PKR',
                        'segments' => $this->normalizeSegment($segments, $carrier),
                        'bundles' => null, 
                        'status' => $flight['availability_status'] ?? 'AVAILABLE',
                        'flightRaw' => $flight,
                    ];

                    if ($direction === 'outbound') {
                        $outbound->push($formattedFlight);
                    } else {
                        $inbound->push($formattedFlight);
                    }
                }
            }
            $flightsCollection->push($outbound);
            $flightsCollection->push($inbound);
            
        } elseif ($carrier === 'flyjinnah') {
            $outboundFlights = $rawData ?? [];
            foreach ($outboundFlights as $flights) {
                $data = collect();
                if (!empty($flights['flights'])) {
                    \Log::info('FlyJinnah Flight Count', ['count' => count($flights['flights'])]);
                    foreach ($flights['flights'] as $flight) {
                        $segments = $flight['flightSegments'] ?? [];
                        if (empty($segments)) continue;

                        $departure = $segments[0]['departureDateTimeLocal'] ?? '';
                        $arrival = $segments[1]['arrivalDateTimeLocal'] ?? $segments[0]['arrivalDateTimeLocal'] ?? '';
                        $data->push([
                            'carrier' => $carrier,
                            'cabinClass' => $this->helper::getCabinClass($flight['cabinPrices'][0]['cabinClass'] ?? null),
                            'departure' => [
                                'code' => $segments[0]['origin']['airportCode'] ?? '',
                                'airport' => $this->helper::codeToCountry($segments[0]['origin']['airportCode'] ?? ''),
                                'local' => $this->helper::codeToLocalCheck($segments[0]['origin']['airportCode'] ?? ''),
                                'datetime' => $this->helper::formatDateTimeForFlights($departure),
                                'date' => $this->helper::formatDateForFlights($departure),
                                'time' => $this->helper::formatTimeForFlights($departure),
                            ],
                            'arrival' => [
                                'code' => $segments[1]['destination']['airportCode'] ?? $segments[0]['destination']['airportCode'] ?? '',
                                'airport' => $this->helper::codeToCountry($segments[1]['destination']['airportCode'] ?? $segments[0]['destination']['airportCode'] ?? ''),
                                'local' => $this->helper::codeToLocalCheck($segments[1]['destination']['airportCode'] ?? $segments[0]['destination']['airportCode'] ?? ''),
                                'datetime' => $this->helper::formatDateTimeForFlights($arrival),
                                'date' => $this->helper::formatDateForFlights($arrival),
                                'time' => $this->helper::formatTimeForFlights($arrival),
                            ],
                            'duration' => $this->helper::calculateDuration($departure, $arrival),
                            'isConnected' => count($segments) > 1,
                            'price' => number_format($flight['price'] ?? 0, 2),
                            'code' => 'PKR',
                            'segments' => $this->normalizeSegment($segments, $carrier),
                            'bundles' => null, // Fetch via API in FlyJinnahService if needed
                            'status' => $flight['availabilityStatus'] ?? 'AVAILABLE',
                            'flightRaw' => $flight,
                        ]);
                    }
                }
                $flightsCollection->push($data);
            }
        } elseif ($carrier === 'emirates') {
            if (isset($rawData['error']) && !empty($rawData['error'])) {
                $errors->push([
                    'details' => $rawData['details']['value'] ?? $rawData,
                ]);
            }
            $flightsData = array_filter($rawData, function ($key) {
                return is_int($key);
            }, ARRAY_FILTER_USE_KEY);
            $extras = collect([
                'emirates' => [
                    'responseId' => $flightsData[0]['responseId'] ?? $flightsData['responseId'] ?? '',
                ]
            ]);
            foreach ($flightsData as $flights) {
                // dd($flights);
                $data = collect();
                if (!empty($flights['flights'])) {
                    \Log::info('Emirates Flight Count', ['count' => count($flights['flights'])]);
                    foreach ($flights['flights'] as $flight) {
                        $departure = ($flight['Departure']['Date']['value'] ?? '') . ' ' . ($flight['Departure']['Time']['value'] ?? '');
                        $arrival = $flight['secondFlight']['arrival'] ?? $flight['Arrival'] ?? '';
                        $arrivalDateTime = ($arrival['Date']['value'] ?? '') . ' ' . ($arrival['Time']['value'] ?? '');
                        $data->push([
                            'carrier' => $carrier,
                            'cabinClass' => $this->helper::getCabinClass($rawData['cabinClass'] ?? null),
                            'departure' => [
                                'code' => $flight['Departure']['AirportCode']['value'] ?? '',
                                'airport' => $this->helper::codeToCountry($flight['Departure']['AirportCode']['value'] ?? ''),
                                'local' => $this->helper::codeToLocalCheck($flight['Departure']['AirportCode']['value'] ?? ''),
                                'datetime' => $this->helper::formatDateTimeForFlights($departure),
                                'date' => $this->helper::formatDateForFlights($departure),
                                'time' => $this->helper::formatTimeForFlights($departure),
                            ],
                            'arrival' => [
                                'code' => $arrival['AirportCode']['value'] ?? '',
                                'airport' => $this->helper::codeToCountry($arrival['AirportCode']['value'] ?? ''),
                                'local' => $this->helper::codeToLocalCheck($arrival['AirportCode']['value'] ?? ''),
                                'datetime' => $this->helper::formatDateTimeForFlights($arrivalDateTime),
                                'date' => $this->helper::formatDateForFlights($arrivalDateTime),
                                'time' => $this->helper::formatTimeForFlights($arrivalDateTime),
                            ],
                            'duration' => $this->helper::calculateDuration($departure, $arrivalDateTime),
                            'isConnected' => $flight['flightDetails']['isConnected'] ?? false,
                            'price' => number_format($flight['price']['amount'] ?? 0, 2),
                            'code' => $flight['price']['code'] ?? 'AED',
                            'segments' => $this->normalizeSegment($flight, $carrier),
                            'bundles' => $flight['bundles'] ?? [],
                            'status' => 'AVAILABLE',
                            'flightRaw' => null,
                        ]);
                    }
                }
                $flightsCollection->push($data);
            }
        } elseif ($carrier === 'airblue') {
            if (isset($rawData['error'])) {
                $errors->push([
                    'details' => $rawData['message'] ?? $rawData,
                ]);
            } elseif (isset($rawData['success']) && $rawData['success']) {
                $legs = $rawData['legs'] ?? [];
                foreach (array_values($legs) as $legOptions) {
                    $data = collect();
                    foreach ($legOptions as $option) {
                        $normalized = $this->normalizeAirblueOption($option, $carrier);
                        $data->push($normalized);
                    }
                    $flightsCollection->push($data);
                }
            }
        }

        return [
            'flights' => $flightsCollection,
            'bundles' => $bundles,
            'extras' => $extras,
            'errors' => $errors
        ];
    }
    private function normalizeAirblueOption($option, $carrier)
    {
        $segments = [];
        foreach ($option['segments'] as $seg) {
            $segments[] = [
                'departure' => [
                    'code' => $seg['origin'],
                    'airport' => $this->helper->codeToCountry($seg['origin']),
                    'local' => $this->helper::codeToLocalCheck($seg['origin'] ?? ''),
                    'datetime' => $seg['departure_date'] . 'T' . $seg['departure_time'] . ':00',
                    'date' => $this->helper::formatDateForFlights($seg['departure_date']),
                    'time' => $this->helper::formatTimeForFlights($seg['departure_time']),
                ],
                'arrival' => [
                    'code' => $seg['destination'],
                    'airport' => $this->helper->codeToCountry($seg['destination']),
                    'local' => $this->helper::codeToLocalCheck($seg['destination'] ?? ''),
                    'datetime' => $seg['departure_date'] . 'T' . $seg['arrival_time'] . ':00', // Assume same day, adjust if needed
                    'date' => $this->helper::formatDateForFlights($seg['departure_date']),
                    'time' => $this->helper::formatTimeForFlights($seg['arrival_time']),
                ],
                'flight_number' => $seg['flight_number'],
                'duration' => $seg['duration'],
                'carrier' => $seg['airline'],
                'baggage' => $option['bundles'][0]['baggage_raw'] ?? [],
            ];
        }

        $first = $segments[0];
        $last = end($segments);

        return [
            'carrier' => $carrier,
            'cabinClass' => 'Y',
            'departure' => $first['departure'],
            'arrival' => $last['arrival'],
            'duration' => $option['duration'],
            'isConnected' => $option['stops'] > 0,
            'price' => $option['cheapest_price'],
            'code' => 'PKR',
            'segments' => $segments,
            'bundles' => $option['bundles'],
            'status' => 'AVAILABLE',
            'flightRaw' => $option,
        ];
    }
    private function normalizeSegment($segments, $airline)
    {
        if ($airline === 'pia') {
            $data = [];
            foreach ($segments as $segment) {
                $depDateTime = $segment['departure']['datetime'] ?? '';
                $arrDateTime = $segment['arrival']['datetime'] ?? '';

                $data[] = [
                    'segment_key' => $segment['segment_id'] ?? null,
                    'departure' => [
                        'code' => $segment['departure']['airport'] ?? '',
                        'airport' => $this->helper::codeToCountry($segment['departure']['airport'] ?? ''),
                        'local' => $this->helper::codeToLocalCheck($segment['departure']['airport'] ?? ''),
                        'datetime' => $depDateTime,
                        'zuluTime' => null,
                    ],
                    'arrival' => [
                        'code' => $segment['arrival']['airport'] ?? '',
                        'airport' => $this->helper::codeToCountry($segment['arrival']['airport'] ?? ''),
                        'local' => $this->helper::codeToLocalCheck($segment['arrival']['airport'] ?? ''),
                        'datetime' => $arrDateTime,
                        'zuluTime' => null,
                    ],
                    'flight_number' => $segment['flight_number'] ?? '',
                    'duration' => $this->helper::calculateDuration($depDateTime, $arrDateTime),
                    'aircraft' => $segment['aircraft'] ?? '',
                    'carrier' => $segment['carrier'] ?? 'PK',
                    'baggage' => [],
                    'technical_stops' => $segment['technical_stops'] ?? [],
                ];
            }
            return $data;
        } elseif ($airline === 'flyjinnah') {
            $data = [];
            foreach ($segments as $segment) {
                $departure = $segment['departureDateTimeLocal'] ?? null;
                $arrival = $segment['arrivalDateTimeLocal'] ?? null;
                $data[] = [
                    'segment_key' => $segment['flightSegmentRef'] ?? null,
                    'departure' => [
                        'code' => $segment['origin']['airportCode'] ?? '',
                        'airport' => $this->helper::codeToCountry($segment['origin']['airportCode'] ?? ''),
                        'local' => $this->helper::codeToLocalCheck($segment['origin']['airportCode'] ?? ''),
                        'datetime' => $departure,
                        'zuluTime' => $segment['departureDateTimeZulu'] ?? '',
                    ],
                    'arrival' => [
                        'code' => $segment['destination']['airportCode'] ?? '',
                        'airport' => $this->helper::codeToCountry($segment['destination']['airportCode'] ?? ''),
                        'local' => $this->helper::codeToLocalCheck($segment['destination']['airportCode'] ?? ''),
                        'datetime' => $arrival,
                        'zuluTime' => $segment['arrivalDateTimeZulu'] ?? '',
                    ],
                    'flight_number' => $segment['flightNumber'] ?? '',
                    'duration' => $this->helper::calculateDuration($departure, $arrival),
                    'aircraft' => $segment['aircraftModel'] ?? '',
                    'carrier' => 'FJ',
                    'baggage' => [],
                ];
            }
            return $data;
        } elseif ($airline === 'emirates') {
            // dd($segments);
            $segmentsArr = [];
            // $firstArrival = !empty($segments['secondFlight'])
            //     ? $segments['secondFlight']['departure']
            //     : $segments['Arrival'];
            $firstArrival = $segments['Arrival'];

            $segmentsArr[] = [
                'segment_key' => $segments['segmentKey'] ?? null,
                'departure' => [
                    'code' => $segments['Departure']['AirportCode']['value'] ?? '',
                    'airport' => $segments['Departure']['AirportName']['value'] ?? '',
                    'local' => $this->helper::codeToLocalCheck($segments['Departure']['AirportCode']['value'] ?? ''),
                    'datetime' => ($segments['Departure']['Date']['value'] ?? '') . 'T' . ($segments['Departure']['Time']['value'] ?? ''),
                    'zuluTime' => null,
                ],
                'arrival' => [
                    'code' => $firstArrival['AirportCode']['value'] ?? '',
                    'airport' => $firstArrival['AirportName']['value'] ?? '',
                    'local' => $this->helper::codeToLocalCheck($firstArrival['AirportCode']['value'] ?? ''),
                    'datetime' => ($firstArrival['Date']['value'] ?? '') . 'T' . ($firstArrival['Time']['value'] ?? ''),
                    'zuluTime' => null,
                ],
                'flight_number' => $segments['flightDetails']['marketingCarrier']['FlightNumber']['value'] ?? '',
                'duration' => $segments['flightDetails']['details']['FlightDuration']['Value']['value'] ?? $segments['duration'] ?? null,
                'aircraft' => $segments['flightDetails']['equipment']['Name']['value'] ?? '',
                'carrier' => $segments['flightDetails']['marketingCarrier']['AirlineID']['value'] ?? 'EK',
                'baggage' => $segments['bundles'][0]['baggageAllowance'] ?? [],
                'isConnected' => $segments['flightDetails']['isConnected'] ?? false,
            ];

            if (!empty($segments['secondFlight'])) {
                $sf = $segments['secondFlight'];
                $segmentsArr[] = [
                    'segment_key' => null,
                    'departure' => [
                        'code' => $sf['departure']['AirportCode']['value'] ?? '',
                        'local' => $this->helper::codeToLocalCheck($sf['departure']['AirportCode']['value'] ?? ''),
                        'airport' => $sf['departure']['AirportName']['value'] ?? '',
                        'datetime' => ($sf['departure']['Date']['value'] ?? '') . 'T' . ($sf['departure']['Time']['value'] ?? ''),
                        'zuluTime' => null,
                    ],
                    'arrival' => [
                        'code' => $sf['arrival']['AirportCode']['value'] ?? '',
                        'local' => $this->helper::codeToLocalCheck($sf['arrival']['AirportCode']['value'] ?? ''),
                        'airport' => $sf['arrival']['AirportName']['value'] ?? '',
                        'datetime' => ($sf['arrival']['Date']['value'] ?? '') . 'T' . ($sf['arrival']['Time']['value'] ?? ''),
                        'zuluTime' => null,
                    ],
                    'flight_number' => $sf['marketingCarrier']['FlightNumber']['value'] ?? '',
                    'duration' => $sf['details']['FlightDuration']['Value']['value'] ?? null,
                    'aircraft' => $sf['equipment']['Name']['value'] ?? '',
                    'carrier' => $sf['marketingCarrier']['AirlineID']['value'] ?? 'EK',
                    'baggage' => $segments['bundles'][0]['baggageAllowance'] ?? [],
                    'isConnected' => $sf['isConnected'] ?? false,
                ];
            }

            return $segmentsArr;
        }

        return $segments;
    }
}