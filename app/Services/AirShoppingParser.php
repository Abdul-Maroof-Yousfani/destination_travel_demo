<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class AirShoppingParser
{
    public function parseAirShoppingResponse(array $response): array
    {
        try {
            $rs = Arr::get($response, 'Response');
            if (!$rs) {
                throw new \Exception('Missing "Response" key in AirShoppingRS');
            }

            $dataLists = Arr::get($rs, 'DataLists', []);
            $offersGroup = Arr::get($rs, 'OffersGroup', []);

            $stops = $this->extractStops($rs);
            $itineraries = $this->extractItineraries($dataLists, $stops);
            $combinations = $this->extractFlightBundleCombinations($offersGroup, $dataLists, $itineraries);

            $availableJourneys = collect($combinations)
                ->flatMap(fn($combo) => array_keys($combo['journeys'] ?? []))
                ->unique()
                ->values()
                ->toArray();

            // Enrich each flight in itineraries
            foreach ($itineraries as &$itinerary) {
                foreach ($itinerary['flights'] as &$flight) {
                    $jid = $flight['journey_id'];
                    $flight['available'] = in_array($jid, $availableJourneys);
                    $flight['availability_status'] = $flight['available'] ? 'available' : 'not_available';
                }
                unset($flight); // good practice
            }
            unset($itinerary);
            $status = 'success';
            $message = null;

            if (empty($combinations)) {
                if (empty($itineraries)) {
                    $status = 'no_flights';
                    $message = 'No flights available for the selected route and dates.';
                } else {
                    $hasInbound = count($itineraries) > 1 && $itineraries[1]['direction'] === 'inbound';
                    
                    if (!$hasInbound) {
                        $status = 'one_way_only';
                        $message = 'Only outbound flights are available. No return options found for the selected dates.';
                    } else {
                        $status = 'no_combinations';
                        $message = 'No fare bundles/combinations available. The selected flights may have no remaining seats in bookable classes.';
                    }
                }
            } elseif (count($combinations) === 0) {
                $status = 'no_combinations';
                $message = 'No bookable fare options found for the requested flights.';
            }

            $coveredJourneys = collect($combinations)->pluck('journeys')->flatten()->keys()->unique()->values();
            $allPossibleJourneys = collect($itineraries)->flatMap(fn($it) => collect($it['flights'])->pluck('journey_id'))->unique();

            if ($coveredJourneys->count() < $allPossibleJourneys->count()) {
                $missing = $allPossibleJourneys->diff($coveredJourneys)->values();
                $message = ($message ? $message . ' ' : '') . 
                        'Some flights have no available bundles (e.g. ' . $missing->implode(', ') . ').';
            }

            $result = [
                'success'       => true,
                'status'        => $status,
                'message'       => $message,
                'metadata'      => $this->extractMetadata($rs, $offersGroup),
                'passengers'    => $this->extractPassengers($dataLists),
                'baggage_rules' => $this->extractBaggageRules($dataLists),
                'bundles'       => $this->extractBundles($dataLists),
                'itineraries'   => $itineraries,
                'combinations'  => $combinations,
            ];

            return $result;

        } catch (\Throwable $e) {
            Log::error('PIA NDC Parsing Error', [
                'message' => $e->getMessage(),
                'response_sample' => json_encode($response, JSON_PRETTY_PRINT)
            ]);

            return [
                'success' => false,
                'error'   => 'Failed to parse PIA flight shopping response',
                'message' => $e->getMessage(),
            ];
        }
    }

    private function extractStops(array $rs): array
    {
        $messages = Arr::get($rs, 'AirShoppingProcessing.MarketingMessage', []);
        $messages = Arr::isAssoc($messages) ? [$messages] : $messages;

        $stops = [];

        foreach ($messages as $msg) {
            $desc = Arr::get($msg, 'Desc.DescText');
            if (str_starts_with($desc, 'STOP_LOCATION')) {
                $parts = explode('---', $desc);
                $loc = trim(str_replace('STOP_LOCATION=', '', $parts[0]));
                $name = trim(str_replace('STOP_LOCATION_NAME=', '', $parts[1] ?? ''));
                $dep_time = trim(str_replace('STOP_DEP_TIME=', '', $parts[2] ?? ''));
                $arr_time = trim(str_replace('STOP_ARR_TIME=', '', $parts[3] ?? ''));
                $segId = Arr::get($msg, 'GeneralAssociation.AssociatedObjectID');

                if ($segId) {
                    $stops[$segId] = [
                        'location' => $loc,
                        'name' => $name ?: null,
                        'arrival_time' => $arr_time,
                        'departure_time' => $dep_time,
                    ];
                }
            }
        }

        return $stops;
    }

    private function extractMetadata(array $rs, array $offersGroup): array
    {
        return [
            'response_ref'     => Arr::get($rs, 'ShoppingResponse.ShoppingResponseRefID'),
            'pricing_type'     => Arr::get($rs, 'AirShoppingProcessing.MarketingMessage.Desc.DescText', 'Unknown'),
            'total_offers'     => (int) Arr::get($offersGroup, 'AllOffersSummary.MatchedOfferQty', 0),
            'lowest_price'     => Arr::get($offersGroup, 'CarrierOffers.CarrierOffersSummary.LowestOfferPrice.TotalAmount', '0.00'),
            'highest_price'    => Arr::get($offersGroup, 'CarrierOffers.CarrierOffersSummary.HighestOfferPrice.TotalAmount', '0.00'),
            'currency'         => 'PKR',
            'payment_deadline' => Arr::get($offersGroup, 'CarrierOffers.Offer.0.OfferItem.0.OfferItemPaymentTimeLimit.PaymentTimeLimitDate.PaymentTimeLimitDateTime'),
        ];
    }

    private function extractPassengers(array $dataLists): array
    {
        $paxList = Arr::get($dataLists, 'PaxList.Pax', []);
        $paxList = Arr::isAssoc($paxList) ? array_values($paxList) : $paxList; // Normalize if assoc
        $passengers = [];

        foreach ($paxList as $pax) {
            $id = $pax['PaxID'] ?? 'unknown';
            $type = $pax['PTC'] ?? 'ADT';

            $passengers[] = [
                'id'    => $id,
                'type'  => $type,
                'label' => match($type) {
                    'ADT' => 'Adult',
                    'CHD' => 'Child',
                    'INF' => 'Infant',
                    default => $type,
                }
            ];
        }

        return $passengers;
    }

    private function extractBaggageRules(array $dataLists): array
    {
        $allowances = Arr::get($dataLists, 'BaggageAllowanceList.BaggageAllowance', []);
        $allowances = Arr::isAssoc($allowances) ? array_values($allowances) : $allowances;
        $rules = [];

        foreach ($allowances as $a) {
            $id = $a['BaggageAllowanceID'] ?? null;
            if (!$id) continue;

            $weight = Arr::get($a, 'PieceAllowance.PieceWeightAllowance.MaximumWeightMeasure', '0');

            $rules[$id] = [
                'id'        => $id,
                'weight_kg' => (int) $weight,
                'type'      => $a['TypeCode'] ?? 'Checked',
            ];
        }

        return $rules;
    }
    // Static bundle details
    private function extractBundles(array $dataLists): array
    {
        $defs = Arr::get($dataLists, 'ServiceDefinitionList.ServiceDefinition', []);
        $defs = Arr::isAssoc($defs) ? array_values($defs) : $defs;
        $bundles = [];

        $mainBundles = ['ECOLIGHT', 'SMART', 'FREEDOM'];

        foreach ($defs as $def) {
            $name = $def['Name'] ?? null;
            if (!in_array($name, $mainBundles)) continue;

            $bundles[strtolower($name)] = [
                'id'           => $def['ServiceDefinitionID'] ?? null,
                'code'         => $def['ServiceCode'] ?? null,
                'name'         => $name,
                'display_name' => ucfirst(strtolower($name)),
                'baggage_ref'  => Arr::get($def, 'ServiceDefinitionAssociation.BaggageAllowanceRef.BaggageAllowanceRefID'),
                'image_url'    => Arr::get($def, 'Desc.1.URL'),
                'included'     => $this->getBundleIncludedServices($def),
            ];
        }

        // Enhanced bundle details with comprehensive keypoints
        if (isset($bundles['ecolight'])) {
            $bundles['ecolight']['baggage_description'] = 'Nil';
            $bundles['ecolight']['display_name'] = 'Light';
            $bundles['ecolight']['included'] = [
                'Free Carry-On Baggage 1PC 7KG',
                'Free Meal/Beverages (Standard)',
                'Seat Selection (charges apply)',
                'Booking Change (charges apply)',
                'Cancellation/Refund (charges apply)',
            ];
            $bundles['ecolight']['checked_baggage'] = 'Nil';
        }
        if (isset($bundles['smart'])) {
            $bundles['smart']['baggage_description'] = '20 KG';
            $bundles['smart']['display_name'] = 'Smart';
            $bundles['smart']['included'] = [
                'Free Carry-On Baggage 1PC 7KG',
                'Free Meal/Beverages (Standard)',
                'Seat Selection (charges apply)',
                'Booking Change (charges apply)',
                'Cancellation/Refund (charges apply)',
            ];
            $bundles['smart']['checked_baggage'] = '20 KG';
        }
        if (isset($bundles['freedom'])) {
            $bundles['freedom']['baggage_description'] = '30 KG';
            $bundles['freedom']['display_name'] = 'Freedom';
            $bundles['freedom']['included'] = [
                'Free Carry-On Baggage 1PC 7KG',
                'Free Meal/Beverages (Standard)',
                'Free Standard Seat Selection',
                '1st change free, 2nd with a fee',
                'Cancellation/Refund (charges apply)',
            ];
            $bundles['freedom']['checked_baggage'] = '30 KG';
        }

        return $bundles;
    }

    private function getBundleIncludedServices(array $def): array
    {
        $refIds = Arr::get($def, 'ServiceDefinitionAssociation.ServiceBundle.ServiceDefinitionRefID', []);
        $refIds = Arr::isAssoc($refIds) ? array_values($refIds) : $refIds;
        $services = [];

        foreach ($refIds as $ref) {
            $services[] = match($ref) {
                'ServiceDef-0-KG' => '0kg checked',
                'ServiceDef-10-KG' => '10kg checked',
                'ServiceDef-20-KG' => '20kg checked',
                'ServiceDef-30-KG' => '30kg checked',
                'ServiceDef-1' => 'Carry-on',
                'ServiceDef-2' => 'Meals: Included',
                'ServiceDef-3' => 'Refunds & Exchanges: Allowed with Higher Fee',
                'ServiceDef-4' => 'Seat Selection: Mandatory with standard charges',
                default => $ref,
            };
        }

        return array_unique($services);
    }

    private function extractItineraries(array $dataLists, array $stops = []): array
    {
        $originDestRaw = Arr::get($dataLists, 'OriginDestList.OriginDest', []);
        $originDests = Arr::isAssoc($originDestRaw) ? [$originDestRaw] : $originDestRaw;

        $journeysRaw = Arr::get($dataLists, 'PaxJourneyList.PaxJourney', []);
        $journeys = Arr::isAssoc($journeysRaw) ? array_values($journeysRaw) : $journeysRaw;

        $segmentsRaw = Arr::get($dataLists, 'PaxSegmentList.PaxSegment', []);
        $segments = Arr::isAssoc($segmentsRaw) ? array_values($segmentsRaw) : $segmentsRaw;

        $itineraries = [];

        foreach ($originDests as $index => $od) {
            $origin = Arr::get($od, 'OriginCode');
            $destination = Arr::get($od, 'DestCode');
            $odId = Arr::get($od, 'OriginDestID');
            $journeyIdsRaw = Arr::get($od, 'PaxJourneyRefID', []);
            $journeyIds = is_string($journeyIdsRaw) ? [$journeyIdsRaw] : $journeyIdsRaw;

            if (!$origin || !$destination || empty($journeyIds)) continue;

            $direction = match ($index) {
                0 => 'outbound',
                1 => 'inbound',
                default => 'leg_' . ($index + 1),
            };

            $date = null;
            $firstJourneyId = $journeyIds[0] ?? null;
            if ($firstJourneyId) {
                $firstJourney = collect($journeys)->firstWhere('PaxJourneyID', $firstJourneyId);
                $firstSegIds = Arr::wrap(Arr::get($firstJourney, 'PaxSegmentRefID'));
                $firstSegId = $firstSegIds[0] ?? null;
                if ($firstSegId) {
                    $firstSegment = collect($segments)->firstWhere('PaxSegmentID', $firstSegId);
                    $depTime = Arr::get($firstSegment, 'Dep.AircraftScheduledDateTime');
                    if ($depTime) {
                        $date = substr($depTime, 0, 10);
                    }
                }
            }

            $flights = [];

            foreach ($journeyIds as $jId) {
                $journey = collect($journeys)->firstWhere('PaxJourneyID', $jId);
                if (!$journey) continue;

                $segIds = Arr::wrap(Arr::get($journey, 'PaxSegmentRefID'));

                $journeySegments = [];
                foreach ($segIds as $segId) {
                    $segment = collect($segments)->firstWhere('PaxSegmentID', $segId);
                    if (!$segment) continue;

                    $marketing = Arr::get($segment, 'MarketingCarrierInfo', []);
                    $dep = Arr::get($segment, 'Dep', []);
                    $arr = Arr::get($segment, 'Arrival', []);

                    $legRaw = Arr::get($segment, 'DatedOperatingLeg', []);
                    $legs = Arr::isAssoc($legRaw) ? [$legRaw] : $legRaw;

                    $technicalStops = [];

                    if (count($legs) > 1) {
                        for ($i = 1; $i < count($legs); $i++) {
                            $leg = $legs[$i];
                            $technicalStops[] = [
                                'location' => Arr::get($leg, 'Arrival.IATA_LocationCode'),
                                'name' => Arr::get($leg, 'Arrival.StationName'),
                                'arrival_time' => null, // no time in leg
                                'departure_time' => null,
                            ];
                        }
                    }

                    $stopInfo = $stops[$segId] ?? null;
                    if ($stopInfo) {
                        $technicalStops[] = $stopInfo;
                    }

                    $journeySegments[] = [
                        'segment_id' => $segId,
                        'flight_number' => Arr::get($marketing, 'MarketingCarrierFlightNumberText', 'N/A'),
                        'carrier' => Arr::get($marketing, 'CarrierDesigCode', 'PK'),
                        'departure' => [
                            'airport' => Arr::get($dep, 'IATA_LocationCode', 'N/A'),
                            'datetime' => Arr::get($dep, 'AircraftScheduledDateTime'),
                        ],
                        'arrival' => [
                            'airport' => Arr::get($arr, 'IATA_LocationCode', 'N/A'),
                            'datetime' => Arr::get($arr, 'AircraftScheduledDateTime'),
                        ],
                        'duration' => Arr::get($segment, 'Duration'),
                        'aircraft' => Arr::get($legs[0] ?? [], 'CarrierAircraftType.CarrierAircraftTypeName', 'N/A'),
                        'technical_stops' => $technicalStops,
                    ];
                }

                $totalDuration = $this->calculateTotalDuration($journeySegments);

                $flights[] = [
                    'journey_id' => $jId,
                    'segments' => $journeySegments,
                    'total_duration' => $totalDuration,
                    'stops' => count($journeySegments) - 1, // number of layovers
                ];
            }

            $itineraries[] = [
                'direction'    => $direction,
                'origin'       => $origin,
                'destination'  => $destination,
                'date'         => $date,
                'od_id'        => $odId,
                'flights'      => $flights,
            ];
        }

        return $itineraries;
    }

    private function calculateTotalDuration(array $segments): string
    {
        if (empty($segments)) {
            return 'PT0M';
        }

        $start = new \DateTime($segments[0]['departure']['datetime']);
        $end = new \DateTime($segments[count($segments) - 1]['arrival']['datetime']);
        $interval = $start->diff($end);

        return 'PT' . $interval->h . 'H' . $interval->i . 'M';
    }

    private function extractFlightBundleCombinations(
        array $offersGroup,
        array $dataLists,
        array $itineraries
    ): array {
        $bundlesMap = [
            'VNBAG'   => 'ECOLIGHT', 'VNBAGCH' => 'ECOLIGHT', 'VNBAGIN' => 'ECOLIGHT',
            'VSM'     => 'SMART',    'VSMCH'   => 'SMART',    'VSMIN'   => 'SMART',
            'VFL'     => 'FREEDOM',  'VFLCH'   => 'FREEDOM',  'VFLIN'   => 'FREEDOM',
        ];

        // Build segment → journey map (very useful for round-trip & multi-city)
        $journeysRaw = Arr::get($dataLists, 'PaxJourneyList.PaxJourney', []);
        $journeys = Arr::isAssoc($journeysRaw) ? array_values($journeysRaw) : $journeysRaw;

        $segmentToJourney = [];
        foreach ($journeys as $j) {
            $segIds = Arr::wrap(Arr::get($j, 'PaxSegmentRefID'));
            foreach ($segIds as $segId) {
                $segmentToJourney[$segId] = Arr::get($j, 'PaxJourneyID');
            }
        }

        $offersRaw = Arr::get($offersGroup, 'CarrierOffers.Offer', []);
        $offers = Arr::isAssoc($offersRaw) ? [$offersRaw] : $offersRaw;

        $combinations = [];

        foreach ($offers as $offerIndex => $offer) {
            $journeyBundles = []; // journeyId => bundleName

            $offerItemsRaw = Arr::get($offer, 'OfferItem', []);
            $offerItems = Arr::isAssoc($offerItemsRaw) ? [$offerItemsRaw] : $offerItemsRaw;

            // We mainly care about the ADT (primary passenger) fare details
            // But we loop ALL offerItems to be safe (sometimes bundles are split)
            foreach ($offerItems as $item) {
                $fareDetailsRaw = Arr::get($item, 'FareDetail', []);
                $fareDetails = Arr::isAssoc($fareDetailsRaw) ? [$fareDetailsRaw] : $fareDetailsRaw;

                foreach ($fareDetails as $fd) {
                    $fareComponentsRaw = Arr::get($fd, 'FareComponent', []);
                    $fareComponents = Arr::isAssoc($fareComponentsRaw) ? [$fareComponentsRaw] : $fareComponentsRaw;

                    foreach ($fareComponents as $fc) {
                        $basis = Arr::get($fc, 'FareBasisCode');
                        $segId = Arr::get($fc, 'PaxSegmentRefID');

                        if (!$basis || !$segId || !isset($segmentToJourney[$segId])) {
                            continue;
                        }

                        $journeyId = $segmentToJourney[$segId];

                        if (isset($bundlesMap[$basis])) {
                            $bundleName = $bundlesMap[$basis];

                            // Keep the most "premium" bundle if conflict (rare but possible)
                            if (!isset($journeyBundles[$journeyId]) ||
                                $this->bundleRank($bundleName) > $this->bundleRank($journeyBundles[$journeyId])) {
                                $journeyBundles[$journeyId] = $bundleName;
                            }
                        }
                    }
                }
            }

            if (empty($journeyBundles)) {
                continue;
            }

            ksort($journeyBundles); // deterministic order
            $key = md5(json_encode($journeyBundles));

            $total = (float) Arr::get($offer, 'TotalPrice.TotalAmount', 0.0);
            $offerId = Arr::get($offer, 'OfferID');

            if (!isset($combinations[$key])) {
                $combinations[$key] = [
                    'journeys'           => $journeyBundles,
                    'total_price_pkr'    => $total,
                    'offer_ids'          => $offerId ? [$offerId] : [],
                    'cheapest_offer_id'  => $offerId,
                    'cheapest_total'     => $total,
                ];
            } else {
                $combinations[$key]['offer_ids'][] = $offerId;

                // Keep track of cheapest offer for this combination
                if ($total < $combinations[$key]['cheapest_total']) {
                    $combinations[$key]['cheapest_total'] = $total;
                    $combinations[$key]['cheapest_offer_id'] = $offerId;
                }
            }
        }

        // Final formatting for frontend
        $result = [];
        foreach (array_values($combinations) as $combo) {
            $jKeys = array_keys($combo['journeys']);

            $formatted = [
                'key'                  => md5(json_encode($combo['journeys'])), // or use your own
                'outbound_journey_id'  => $jKeys[0] ?? null,
                'outbound_bundle'      => $combo['journeys'][$jKeys[0] ?? ''] ?? null,
                'inbound_journey_id'   => $jKeys[1] ?? null,
                'inbound_bundle'       => $combo['journeys'][$jKeys[1] ?? ''] ?? null,
                'total_price_pkr'      => $combo['cheapest_total'],
                'recommended_offer_id' => $combo['cheapest_offer_id'], // ← this is what you want for booking!
                'all_offer_ids'        => $combo['offer_ids'],
                'journeys'             => $combo['journeys'], // keep for multi-city future-proofing
            ];

            $result[] = $formatted;
        }

        // Optional: sort by price
        usort($result, fn($a, $b) => $a['total_price_pkr'] <=> $b['total_price_pkr']);

        return $result;
    }

    /**
     * Helper: higher rank = more premium bundle
     */
    private function bundleRank(string $bundle): int
    {
        return match (strtoupper($bundle)) {
            'FREEDOM'  => 3,
            'SMART'    => 2,
            'ECOLIGHT' => 1,
            default    => 0,
        };
    }
}