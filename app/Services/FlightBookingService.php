<?php

namespace App\Services;

use App\Models\Flight;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\Penalty;
use App\Models\Segment;
use App\Models\Ancillary;
use App\Models\Passenger;
use App\Models\BookingItem;
use Illuminate\Support\Carbon;
use App\Models\BookingRequestBody;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlightBookingService
{
    // --------------------------------------------------------------EMIRATES--------------------------------------------------------------
    public function handleBookingEmi(array $response, int $clientId, $cabinClass): array
    {
        $segments = $response['segments'] ?? [];
        $bundle = $response['bundle'] ?? [];
        $responsePassengers = $response['passengers'] ?? [];
        $isOneWay = count($segments) === 1;
        $segmentCount = count($segments);
        $bookingType = $segmentCount === 1 ? 'oneway' : ($segmentCount === 2 ? 'return' : 'multi');
        $tax = config('variables.tax') ?? 400;
        $taxCode = config('variables.tax_code') ?? 'PKR';
        
        $paxCount = [
            'adults' => collect($responsePassengers)->where('type', 'ADT')->count(),
            'children' => collect($responsePassengers)->where('type', 'CNN')->count(),
            'infant' => collect($responsePassengers)->where('type', 'INF')->count(),
        ];
        
        $flightsCreated = [];

        DB::beginTransaction();

        try {
            $order = $bundle['offerID'] ?? [];
            $bookingReferences = $bundle['bookingReferences'] ?? [];
            $timeLimits = $bundle['timeLimits'] ?? [];
            $passengers = [];
            foreach ($responsePassengers as $passenger) {
                $apiName = isset($passenger['givenName']) ? strtolower(preg_replace('/\s+/', '', $passenger['givenName'])) : null;
                $dob = $passenger['birthdate'] ?? null;
                $existingPassenger = Passenger::get()
                    ->filter(function ($p) use ($apiName, $dob) {
                        $dbName = strtolower(preg_replace('/\s+/', '', $p->given_name));
                        return $dbName === $apiName && $p->dob->format('Y-m-d') === $dob;
                    })
                    ->first();
                if ($existingPassenger) {
                    $existingPassenger->update([
                        'passenger_reference' => $passenger['id'],
                        'type' => $passenger['type'],
                    ]);
                    $passengers[] = [
                        'id'                 => $existingPassenger->id,
                        'title'              => $existingPassenger->title ?? '',
                        'given_name'         => $existingPassenger->given_name ?? '',
                        'surname'            => $existingPassenger->surname ?? '',
                        'dob'                => $existingPassenger->dob ? $existingPassenger->dob->toDateString() : null,
                        'nationality'        => $existingPassenger->nationality ?? '',
                        'passport_no'        => $existingPassenger->passport_no ?? '',
                        'passport_exp'       => $existingPassenger->passport_exp ? $existingPassenger->passport_exp->toDateString() : null,
                        'type'               => $existingPassenger->type ?? '',
                        'passenger_reference'=> $existingPassenger->passenger_reference ?? '',
                    ];
                }
            }


            // Create Booking first
            $booking = Booking::create([
                'client_id'         => $clientId,
                'passenger_details' => json_encode($passengers),
                'order_id'          => $order['OrderID'] ?? null,
                'order_owner'       => $order['Owner'] ?? null,
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'flight_booking_id' => $bookingReferences['bookingId'] ?? null,
                'ticket_limit'      => Carbon::parse($timeLimits['ticketingTimeLimit'] ?? null),
                'payment_limit'     => Carbon::parse($timeLimits['paymentTimeLimit'] ?? null),
                'airline_id'        => $bookingReferences['airlineID'] ?? null,
                'airline'           => $bookingReferences['airline'] ?? null,
                'transaction_id'    => $response['transactionId'] ?? '-',
                'price_code'        => $bundle['totalPrice']['code'] ?? null,
                'price'             => $bundle['totalPrice']['amount'] ?? 0,
                'tax'               => $tax,
                'tax_code'          => $taxCode,
                'status'            => Booking::STATUS_INITIAL,
            ]);
            if (!empty($bundle)) {
                foreach ($bundle['offerItem'] as $offerItem) {
                    $bookingItem = BookingItem::create([
                        'passenger_ref' => $offerItem['fareDetail']['passengerRef']['value'] ?? null,
                        'passenger_code' => $offerItem['fareDetail']['passengers'] ?? null,
                        'services' => json_encode($offerItem['services'] ?? []),
                        'taxes' => json_encode($offerItem['fareDetail']['taxes'] ?? []),
                        'price' => $offerItem['totalPrice']['amount'] ?? 0,
                        'price_code' => $offerItem['totalPrice']['code'] ?? null,
                        'booking_id' => $booking->id,
                    ]);
                    $penalties = collect($offerItem['fareDetail']['penalties'] ?? [])->map(function ($penalty) {
                        return [
                            'arrival' => $penalty['arrival'] ?? null,
                            'destination' => $penalty['destination'] ?? null,
                            'cabin_type' => $penalty['cabinType'] ?? null,
                            'cancel_fee' => $penalty['fareRules']['cancelFee'] ?? [],
                            'change_fee' => $penalty['fareRules']['changeFee'] ?? [],
                            'refund_fee' => $penalty['fareRules']['refundFee'] ?? [],
                        ];
                    })->toArray();

                    if (!empty($penalties)) $bookingItem->penalties()->createMany($penalties);
                }
            }

            foreach ($segments as $index => $segment) {
                $flightsInSegment = $segment['flights'] ?? [];

                $isConnected = isset($flightsInSegment['secondFlight']) && !empty($flightsInSegment['secondFlight']);
                // $isOneWay = count($segments) === 1;

                $departureFlight = $flightsInSegment;
                $connectingFlight = $flightsInSegment['secondFlight'] ?? null;

                // Determine overall route
                $departureCode = $departureFlight['Departure']['AirportCode']['value'];
                $arrivalCode = $isConnected
                    ? $connectingFlight['arrival']['AirportCode']['value']
                    : $departureFlight['Arrival']['AirportCode']['value'];

                $departureDate = Carbon::parse($departureFlight['Departure']['Date']['value'] . ' ' . $departureFlight['Departure']['Time']['value']);
                $arrivalDate = $isConnected
                    ? Carbon::parse($connectingFlight['arrival']['Date']['value'] . ' ' . $connectingFlight['arrival']['Time']['value'])
                    : Carbon::parse($departureFlight['Arrival']['Date']['value'] . ' ' . $departureFlight['Arrival']['Time']['value']);

                $segmentArrivalCode = $isConnected
                    ? $connectingFlight['departure']['AirportCode']['value']
                    : $departureFlight['Arrival']['AirportCode']['value'];

                $flight = Flight::create([
                    'airline'        => $departureFlight['flightDetails']['marketingCarrier']['Name']['value'] ?? null,
                    'departure_code' => $departureCode,
                    'arrival_code'   => $arrivalCode,
                    'departure_date' => $departureDate,
                    'arrival_date'   => $arrivalDate ?? null,
                    // 'is_oneway'      => $isOneWay,
                    'is_connected'   => $isConnected,
                    'pax_count'      => $paxCount,
                    'cabin_class'    => $cabinClass,
                    'price'          => $departureFlight['price']['amount'],
                    'price_code'     => $departureFlight['price']['code'],
                    'client_id'      => $clientId,
                    'booking_id'     => $booking->id ?? null,
                ]);

                // Add first segment
                Segment::create([
                    'flight_id'      => $flight->id,
                    'departure_code' => $departureFlight['Departure']['AirportCode']['value'],
                    'arrival_code'   => $segmentArrivalCode,
                    'departure_date' => $departureDate,
                    'flight_duration'=> $departureFlight['flightDetails']['details']['FlightDuration']['Value']['value'] ?? null,
                    'arrival_date'   => Carbon::parse($departureFlight['Arrival']['Date']['value'] . ' ' . $departureFlight['Arrival']['Time']['value']),
                    'flight_number'  => $departureFlight['flightDetails']['marketingCarrier']['FlightNumber']['value'],
                    'direction'      => $index === 0 ? 'outbound' : 'return',
                    // 'price'          => $departureFlight['price']['amount'],
                    // 'price_code'     => $departureFlight['price']['code'],
                ]);

                // Add second segment if connected
                if ($isConnected) {
                    Segment::create([
                        'flight_id'      => $flight->id,
                        'departure_code' => $connectingFlight['departure']['AirportCode']['value'],
                        'arrival_code'   => $connectingFlight['arrival']['AirportCode']['value'],
                        'departure_date' => Carbon::parse($connectingFlight['departure']['Date']['value'] . ' ' . $connectingFlight['departure']['Time']['value']),
                        'flight_duration'=> $connectingFlight['details']['FlightDuration']['Value']['value'] ?? null,
                        'arrival_date'   => $arrivalDate,
                        'flight_number'  => $connectingFlight['marketingCarrier']['FlightNumber']['value'],
                        'direction'      => $index === 0 ? 'outbound' : 'return',
                        // 'price'          => $departureFlight['price']['amount'], // same price
                        // 'price_code'     => $departureFlight['price']['code'],
                    ]);
                }

                $flightsCreated[] = $flight;
            }
            BookingRequestBody::create([
                'booking_id' => $booking->id,
                'airline' => $booking->airline,
                'xml_body' => json_encode($response),
                'client_id' => $clientId,
                'ticket_limit' => $booking->ticket_limit,
                'payment_limit' => $booking->payment_limit,
            ]);

            DB::commit();
            $booking->load('bookingItems.penalties', 'client');
            return [
                'message' => 'Flight booked successfully. Please complete payment before the deadline, otherwise it will be canceled.',
                // 'flights' => $flightsCreated,
                'booking' => $booking
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    public function updateBookingFieldsEmi(array $response, int $bookingId): Booking
    {
        $bundle              = $response['bundle'] ?? [];
        $order               = $bundle['offerID'] ?? [];
        $bookingReferences   = $bundle['bookingReferences'] ?? [];
        $timeLimits          = $bundle['timeLimits'] ?? [];
        $segments            = $response['segments'] ?? [];
        $isOneWay            = count($segments) === 1;
        $segmentCount        = count($segments);
        $bookingType         = $segmentCount === 1 ? 'oneway' : ($segmentCount === 2 ? 'return' : 'multi');
        $booking             = Booking::findOrFail($bookingId);

        $ticketTimeLimit = isset($timeLimits['ticketingTimeLimit']) ? Carbon::parse($timeLimits['ticketingTimeLimit']) : $booking->ticket_limit;
        $paymentTimeLimit = isset($timeLimits['paymentTimeLimit']) ? Carbon::parse($timeLimits['paymentTimeLimit']) : $booking->payment_limit;

        try {
            $booking->update([
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'order_id'          => $order['OrderID'] ?? null,
                'order_owner'       => $order['Owner'] ?? null,
                'flight_booking_id' => $bookingReferences['bookingId'] ?? null,
                'ticket_limit'      => $ticketTimeLimit,
                'payment_limit'     => $paymentTimeLimit,
                'airline_id'        => $bookingReferences['airlineID'] ?? null,
                'airline'           => $bookingReferences['airline'] ?? null,
                'transaction_id'    => $response['transactionId'] ?? $booking->transaction_id,
                'price_code'        => data_get($bundle, 'totalPrice.code', $booking->price_code),
                'price'             => data_get($bundle, 'totalPrice.amount', $booking->price),
                'status'            => $booking->status !== Booking::STATUS_ISSUED ? Booking::STATUS_CHANGED : $booking->status,
            ]);
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => $ticketTimeLimit,
                    'payment_limit' => $paymentTimeLimit,
                    'xml_body' => json_encode($response ?? []),
                ]);
            }

            return $booking->fresh();
        } catch (\Throwable $e) {
            Log::error('Booking table update failed: '.$e->getMessage(), ['booking_id' => $bookingId]);
            throw $e;
        }
    }
    public function issueTicketsEmi(array $data, int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        DB::beginTransaction();
        try {
            $ticketTimeLimit = $data['bundle']['timeLimits']['ticketingTimeLimit'] ?? $booking->ticket_limit;
            $paymentTimeLimit = $data['bundle']['timeLimits']['paymentTimeLimit'] ?? $booking->payment_limit;
            // ----------------------------------------- Update Booking -----------------------------------------
            $booking->update([
                'status' => Booking::STATUS_ISSUED,
                'only_search' => false,
                'ticket_limit' => $ticketTimeLimit,
                'payment_limit' => $paymentTimeLimit,
            ]);
            // ----------------------------------------- Update Booking Request Body -----------------------------------------
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => $ticketTimeLimit,
                    'payment_limit' => $paymentTimeLimit,
                    'xml_body' => json_encode($data ?? []),
                ]);
            }
            // ----------------------------------------- Create Tickets -----------------------------------------
            $tickets = [];
            foreach ($data['ticketInfos'] ?? [] as $ticketInfo) {
                $tickets[] = [
                    'airline' => $ticketInfo['issuingAirlineInfo']['airline'] ?? null,
                    'passenger_reference' => $ticketInfo['passengerReference'] ?? null,
                    'place' => $ticketInfo['issuingAirlineInfo']['place'] ?? null,
                    'ticket_no' => $ticketInfo['ticketDocument']['ticketDocNbr'] ?? null,
                    'type' => $ticketInfo['ticketDocument']['type'] ?? null,
                    'issue_date' => isset($ticketInfo['ticketDocument']['dateOfIssue'], $ticketInfo['ticketDocument']['timeOfIssue'])
                        ? Carbon::parse($ticketInfo['ticketDocument']['dateOfIssue'] . ' ' . $ticketInfo['ticketDocument']['timeOfIssue'])
                        : now(),
                    'price_code' => $ticketInfo['price']['total']['code'] ?? null,
                    'price' => $ticketInfo['price']['total']['value'] ?? null,
                    'price_reference' => $ticketInfo['price']['refs'] ?? null,
                    'ticket_details' => json_encode($ticketInfo),
                    'client_id' => $booking->client_id,
                    'booking_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($tickets)) Ticket::insert($tickets);
            DB::commit();
            return $booking->load('bookingItems.penalties', 'client', 'tickets');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed for Emirates: ' . $e->getMessage());
            throw $e;
        }
    }


    // --------------------------------------------------------------FLYJINNAH--------------------------------------------------------------
    
    public function handleBookingFJ(array $response, int $clientId): array
    {
        $otaAirBookRS = $response['Body']['OTA_AirBookRS'] ?? [];
        $airReservation = $otaAirBookRS['AirReservation'] ?? [];
        $itinerary = $airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? [];
        $airTravelers = $airReservation['TravelerInfo']['AirTraveler'] ?? [];
        $itinerary = isset($itinerary[0]) ? $itinerary : [$itinerary];
        $isOneWay = count($itinerary) === 1;
        $itineraryCount = count($itinerary);
        $bookingType = $itineraryCount === 1 ? 'oneway' : ($itineraryCount === 2 ? 'return' : 'multi');
        $tax = config('variables.tax') ?? 400;
        $taxCode = config('variables.tax_code') ?? 'PKR';

        $paxCount = [
            'adults' => 0,
            'children' => 0,
            'infant' => 0,
        ];
        if (isset($airReservation['TPA_Extensions']['AAAirReservationExt']['ResSummary']['PTCCounts']['PTCCount'])) {
            $ptcCounts = $airReservation['TPA_Extensions']['AAAirReservationExt']['ResSummary']['PTCCounts']['PTCCount'];
            $ptcCounts = isset($ptcCounts[0]) ? $ptcCounts : [$ptcCounts];
            foreach ($ptcCounts as $count) {
                if (!is_array($count)) continue;
                $code = $count['PassengerTypeCode'] ?? '';
                $qty = (int) ($count['PassengerTypeQuantity'] ?? 0);
                if ($code === 'ADT') {
                    $paxCount['adults'] = $qty;
                } elseif (in_array($code, ['CHD', 'CNN'])) {
                    $paxCount['children'] = $qty;
                } elseif ($code === 'INF') {
                    $paxCount['infant'] = $qty;
                }
            }
        }
        $flightsCreated = [];

        DB::beginTransaction();

        try {
            // Handle passengers
            $passengers = [];
            $airTravelers = isset($airTravelers[0]) ? $airTravelers : [$airTravelers];
            foreach ($airTravelers as $passenger) {
                if (!is_array($passenger)) continue;
                $givenName = $passenger['PersonName']['GivenName'] ?? '';
                // $dob = null; // No DOB in FlyJinnah response
                $apiName = strtolower(preg_replace('/\s+/', '', $givenName));
                $existingPassenger = Passenger::get()
                    ->filter(function ($p) use ($apiName) {
                        $dbName = strtolower(preg_replace('/\s+/', '', $p->given_name));
                        return $dbName === $apiName;
                    })
                    ->first();
                if ($existingPassenger) {
                    $existingPassenger->update([
                        'passenger_reference' => $passenger['TravelerRefNumber']['@attributes']['RPH'] ?? null,
                        'type' => $passenger['@attributes']['PassengerTypeCode'] ?? null,
                    ]);
                    $passengers[] = [
                        'id'                 => $existingPassenger->id,
                        'title'              => $existingPassenger->title ?? '',
                        'given_name'         => $existingPassenger->given_name ?? '',
                        'surname'            => $existingPassenger->surname ?? '',
                        'dob'                => $existingPassenger->dob ? $existingPassenger->dob->toDateString() : null,
                        'nationality'        => $existingPassenger->nationality ?? '',
                        'passport_no'        => $existingPassenger->passport_no ?? '',
                        'passport_exp'       => $existingPassenger->passport_exp ? $existingPassenger->passport_exp->toDateString() : null,
                        'type'               => $existingPassenger->type ?? '',
                        'passenger_reference'=> $existingPassenger->passenger_reference ?? '',
                    ];
                }
            }

            // Extract booking details
            $bookingRef = $airReservation['BookingReferenceID']['@attributes'] ?? [];
            $ticketingAttrs = $airReservation['Ticketing']['@attributes'] ?? [];
            $priceInfo = $airReservation['PriceInfo']['ItinTotalFare'] ?? [];
            $totalPriceAttrs = $priceInfo['TotalFare']['@attributes'] ?? [];
            $transactionId = $otaAirBookRS['@attributes']['TransactionIdentifier'] ?? '-';
            $ticketLimit = $ticketingAttrs['TicketTimeLimit'] ?? null;
            $paymentLimit = $ticketingAttrs['TicketTimeLimit'] ?? null; // Assuming same for payment and ticketing
            // $airlineId = 'G9'; // From flight codes
            $airline = 'FlyJinnah';

            // Create Booking
            $booking = Booking::create([
                'client_id'         => $clientId,
                'passenger_details' => json_encode($passengers),
                'order_id'          => $bookingRef['ID'] ?? null,
                'order_owner'       => null,
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'flight_booking_id' => null,
                'ticket_limit'      => $ticketLimit ? Carbon::parse($ticketLimit) : null,
                'payment_limit'     => $paymentLimit ? Carbon::parse($paymentLimit) : null,
                'airline_id'        => null,
                'airline'           => $airline,
                'transaction_id'    => $transactionId,
                'price_code'        => $totalPriceAttrs['CurrencyCode'] ?? null,
                'price'             => $totalPriceAttrs['Amount'] ?? 0,
                'tax'               => $tax,
                'tax_code'          => $taxCode,
                'status'            => Booking::STATUS_INITIAL,
            ]);

            // Create BookingItems from PTC_FareBreakdowns
            $ptcFareBreakdowns = $airReservation['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'] ?? [];
            $ptcFareBreakdowns = isset($ptcFareBreakdowns[0]) ? $ptcFareBreakdowns : [$ptcFareBreakdowns];
            foreach ($ptcFareBreakdowns as $breakdown) {
                if (!is_array($breakdown)) continue;
                $travelerRef = $breakdown['TravelerRefNumber']['@attributes']['RPH'] ?? null;
                $passengerTypeAttrs = $breakdown['PassengerTypeQuantity']['@attributes'] ?? [];
                $passengerFare = $breakdown['PassengerFare'] ?? [];
                $passengerTotalAttrs = $passengerFare['TotalFare']['@attributes'] ?? [];
                $services = $passengerFare['Fees']['Fee'] ?? [];
                $taxes = $passengerFare['Taxes']['Tax'] ?? [];
                if (!is_array($services)) {
                    $services = [$services];
                }
                if (!is_array($taxes)) {
                    $taxes = [$taxes];
                }
                $bookingItem = BookingItem::create([
                    'passenger_ref' => $travelerRef,
                    'passenger_code' => $passengerTypeAttrs['Code'] ?? null,
                    'services' => json_encode(array_filter($services)),
                    'taxes' => json_encode(array_filter($taxes)),
                    'price' => $passengerTotalAttrs['Amount'] ?? 0,
                    'price_code' => $passengerTotalAttrs['CurrencyCode'] ?? null,
                    'booking_id' => $booking->id,
                ]);

                // No penalties
            }

            // Create Flights and Segments
            foreach ($itinerary as $index => $odo) {
                if (!is_array($odo)) continue;
                $flightSegments = $odo['FlightSegment'] ?? [];
                $flightSegments = isset($flightSegments[0]) ? $flightSegments : [$flightSegments];
                if (isset($flightSegments['@attributes'])) {
                    // Single segment, wrap
                    $flightSegments = [$flightSegments];
                }
                $numSegments = count($flightSegments);
                if ($numSegments === 0) continue;
                $isConnected = $numSegments > 1;

                // Determine overall route and times
                $firstSegment = $flightSegments[0] ?? [];
                $lastSegment = $flightSegments[$numSegments - 1] ?? [];
                $departureCode = $firstSegment['DepartureAirport']['@attributes']['LocationCode'] ?? null;
                $cabinClass = $firstSegment['@attributes']['ResCabinClass'] ?? null;
                $arrivalCode = $lastSegment['ArrivalAirport']['@attributes']['LocationCode'] ?? null;
                $departureDate = Carbon::parse($firstSegment['@attributes']['DepartureDateTime'] ?? null);
                $arrivalDate = Carbon::parse($lastSegment['@attributes']['ArrivalDateTime'] ?? null);

                $flight = Flight::create([
                    'airline'        => $airline,
                    'departure_code' => $departureCode,
                    'arrival_code'   => $arrivalCode,
                    'departure_date' => $departureDate,
                    'arrival_date'   => $arrivalDate,
                    'is_connected'   => $isConnected,
                    'pax_count'      => $paxCount,
                    'cabin_class'    => $cabinClass,
                    'price'          => 0,
                    'price_code'     => 'PKR',
                    'client_id'      => $clientId,
                    'booking_id'     => $booking->id,
                ]);

                // Create segments
                foreach ($flightSegments as $seg) {
                    if (!is_array($seg)) continue;
                    $segAttrs = $seg['@attributes'] ?? [];
                    $depAirport = $seg['DepartureAirport']['@attributes'] ?? [];
                    $arrAirport = $seg['ArrivalAirport']['@attributes'] ?? [];
                    $segDepartureDate = Carbon::parse($segAttrs['DepartureDateTime'] ?? null);
                    $segArrivalDate = Carbon::parse($segAttrs['ArrivalDateTime'] ?? null);
                    $flightDuration = $segDepartureDate->diff($segArrivalDate)->format('%Hh %Im');

                    Segment::create([
                        'flight_id'      => $flight->id,
                        'departure_code' => $depAirport['LocationCode'] ?? null,
                        'arrival_code'   => $arrAirport['LocationCode'] ?? null,
                        'departure_date' => $segDepartureDate,
                        'flight_duration'=> $flightDuration,
                        'arrival_date'   => $segArrivalDate,
                        'flight_number'  => $segAttrs['FlightNumber'] ?? null,
                        'direction'      => $index === 0 ? 'outbound' : 'return',
                    ]);
                }

                $flightsCreated[] = $flight;
            }

            BookingRequestBody::create([
                'booking_id' => $booking->id,
                'airline' => $airline,
                'xml_body' => json_encode($response),
                'client_id' => $clientId,
                'ticket_limit' => $booking->ticket_limit,
                'payment_limit' => $booking->payment_limit,
            ]);

            DB::commit();
            $booking->load('bookingItems.penalties', 'client');
            $advisory = $airReservation['Ticketing']['TicketAdvisory'] ?? 'Flight booked successfully. Please complete payment before the deadline, otherwise it will be canceled...';
            return ['message' => $advisory, 'booking' => $booking];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed for FlyJinnah: ' . $e->getMessage());
            throw $e;
        }
    }
    public function updateBookingFieldsFJ(array $data, int $bookingId): Booking
    {
        // $airReservation = $response['AirReservation'] ?? [];
        // $itinerary = $airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? [];
        // $isOneWay = is_array($itinerary) && count($itinerary) === 1;
        // $priceInfo = $airReservation['PriceInfo']['ItinTotalFare'] ?? [];
        // $totalPriceAttrs = $priceInfo['TotalFare']['@attributes'] ?? [];
        // $bookingRef = $airReservation['BookingReferenceID']['@attributes'] ?? [];
        // $ticketingAttrs = $airReservation['Ticketing']['@attributes'] ?? [];
        $booking = Booking::findOrFail($bookingId);
        $timeLimit = isset($data['timeLimit']) ? Carbon::parse($data['timeLimit']) : $booking->ticket_limit;
        try {
            $booking->update([
                // 'is_oneway'         => $isOneWay,
                // 'order_id'          => $bookingRef['ID'] ?? null,
                // 'order_owner'       => null,
                // 'flight_booking_id' => null,
                // 'airline_id'        => null,
                // 'airline'           => 'FlyJinnah',
                'ticket_limit'      => $timeLimit,
                'payment_limit'     => $timeLimit,
                'transaction_id'    => $data['transactionId'] ?? $booking->transaction_id,
                'price_code'        => $data['code'] ?? $booking->price_code,
                'price'             => $data['amount'] ?? $booking->price,
                'status'            => $booking->status !== Booking::STATUS_ISSUED ? Booking::STATUS_CHANGED : $booking->status,
            ]);
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => $timeLimit,
                    'payment_limit' => $timeLimit,
                    'xml_body' => json_encode($data['response'] ?? []),
                ]);
            }
            return $booking->fresh();
        } catch (\Throwable $e) {
            Log::error('Booking table update failed for FlyJinnah: '.$e->getMessage(), ['booking_id' => $bookingId]);
            throw $e;
        }
    }
    public function issueTicketsFJ(array $data, int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        DB::beginTransaction();
        try {
            $booking->update([
                'status' => Booking::STATUS_ISSUED,
                'transaction_id' => $data['transactionId'],
                'only_search' => false,
                'ticket_limit' => null,
                'payment_limit' => null,
            ]);
            // ----------------------------------------- Update Booking Request Body -----------------------------------------
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => null,
                    'payment_limit' => null,
                    'xml_body' => json_encode($data ?? []),
                ]);
            }
            // ----------------------------------------- Create Tickets -----------------------------------------
            $tickets = [];
            $ticketMap = [];
            foreach ($data['passengers'] ?? [] as $ticketInfo) {
                $ticket = [
                    'airline' => $booking->airline ?? 'Flyjinnah',
                    'passenger_reference' => $ticketInfo['ref_no'] ?? null,
                    'ticket_no' => $ticketInfo['tickets'][0]['e_ticket_no'] ?? null,
                    'ticket_numbers' => json_encode($ticketInfo['tickets'] ?? []),
                    'type' => $ticketInfo['tickets'][0]['type'] ?? null,
                    'issue_date' => now(),
                    'price_code' => null,
                    'price' => null,
                    'price_reference' => null,
                    'ticket_details' => json_encode($ticketInfo),
                    'client_id' => $booking->client_id,
                    'booking_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $tickets[] = $ticket;
                $ticketMap[$ticketInfo['ref_no']] = $ticket;
            }
            $newTickets = [];
            if (!empty($tickets)) {
                Ticket::insert($tickets);
                $insertedTickets = Ticket::whereIn('passenger_reference', array_keys($ticketMap))->get()->keyBy('passenger_reference');
                foreach ($ticketMap as $ref => &$ticket) {
                    $ticket['id'] = $insertedTickets[$ref]->id ?? null; // Assign ticket ID
                }
            }
            $ancillaries = [];
            foreach ($data['passengers'] ?? [] as $passenger) {
                $passengerRef = $passenger['ref_no'] ?? null;
                $ticketId = $ticketMap[$passengerRef]['id'] ?? null;

                if (!$ticketId) continue;

                foreach ($passenger['seats'] ?? [] as $seat) {
                    $ancillaries[] = [
                        'ticket_id' => $ticketId,
                        'passenger_reference' => $passengerRef,
                        'type' => 'seat',
                        'details' => json_encode([
                            'seat_number' => $seat['seat_number'] ?? 'N/A',
                            'flight_number' => $seat['flight_number'] ?? null,
                            'departure_date' => $seat['departure_date'] ?? null,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                foreach ($passenger['baggage'] ?? [] as $baggage) {
                    $ancillaries[] = [
                        'ticket_id' => $ticketId,
                        'passenger_reference' => $passengerRef,
                        'type' => 'baggage',
                        'details' => json_encode([
                            'baggage_code' => $baggage['baggage_code'] ?? 'No Bag',
                            'flight_number' => $baggage['flight_number'] ?? null,
                            'departure_date' => $baggage['departure_date'] ?? null,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                foreach ($passenger['meals'] ?? [] as $meal) {
                    $ancillaries[] = [
                        'ticket_id' => $ticketId,
                        'passenger_reference' => $passengerRef,
                        'type' => 'meal',
                        'details' => json_encode([
                            'meal_code' => $meal['meal_code'] ?? null,
                            'meal_quantity' => $meal['meal_quantity'] ?? null,
                            'flight_number' => $meal['flight_number'] ?? null,
                            'departure_date' => $meal['departure_date'] ?? null,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($ancillaries)) Ancillary::insert($ancillaries);
            DB::commit();
            return $booking->load('bookingItems.penalties', 'client', 'tickets.ancillaries');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed for FlyJinnah: ' . $e->getMessage());
            throw $e;
        }
    }

    // --------------------------------------------------------------PIA--------------------------------------------------------------
    public function handleBookingPia(array $response, int $clientId, $cabinClass): array
    {
        $segments = $response['segments'] ?? [];
        $journeys = $response['journeys'] ?? [];
        $order = $response['order'] ?? [];
        $passengers = $response['passengers'] ?? [];
        $isOneWay = count($journeys) === 1;
        $journeyCount = count($journeys);
        $bookingType = $journeyCount === 1 ? 'oneway' : ($journeyCount === 2 ? 'return' : 'multi');
        $tax = config('variables.tax') ?? 400;
        $taxCode = config('variables.tax_code') ?? 'PKR';

        $paxCount = [
            'adults' => collect($passengers)->where('ptc', 'ADT')->count(),
            'children' => collect($passengers)->where('ptc', 'CHD')->count(),
            'infant' => collect($passengers)->where('ptc', 'INF')->count(),
        ];

        $flightsCreated = [];

        DB::beginTransaction();

        try {
            $bookingReferences = [
                'bookingId' => $order['orderID'] ?? null,
                'airlineID' => $order['ownerCode'] ?? null,
                'airline' => $order['ownerCode'] ?? null, // Assuming 'PK' as airline
            ];
            $timeLimits = [
                'paymentTimeLimit' => $response['paymentLimit'] ?? null,
                // No explicit ticketingTimeLimit in PIA response, using paymentLimit for both
                'ticketingTimeLimit' => $response['paymentLimit'] ?? null,
            ];

            $dbPassengers = [];
            foreach ($passengers as $passenger) {
                $apiName = strtolower(preg_replace('/\s+/', '', $passenger['given_name']));
                $dob = $passenger['birthdate'];
                $existingPassenger = Passenger::get()
                    ->filter(function ($p) use ($apiName, $dob) {
                        $dbName = strtolower(preg_replace('/\s+/', '', $p->given_name));
                        return $dbName === $apiName && $p->dob->format('Y-m-d') === $dob;
                    })
                    ->first();
                if ($existingPassenger) {
                    $existingPassenger->update([
                        'passenger_reference' => $passenger['pax_id'],
                        'type' => $passenger['ptc'] === 'CHD' ? 'CNN' : $passenger['ptc'], // Map CHD to CNN for consistency
                    ]);
                    $dbPassengers[] = [
                        'id'                 => $existingPassenger->id,
                        'title'              => $existingPassenger->title ?? '',
                        'given_name'         => $existingPassenger->given_name ?? '',
                        'surname'            => $existingPassenger->surname ?? '',
                        'dob'                => $existingPassenger->dob ? $existingPassenger->dob->toDateString() : null,
                        'nationality'        => $existingPassenger->nationality ?? '',
                        'passport_no'        => $existingPassenger->passport_no ?? '',
                        'passport_exp'       => $existingPassenger->passport_exp ? $existingPassenger->passport_exp->toDateString() : null,
                        'type'               => $existingPassenger->type ?? '',
                        'passenger_reference'=> $existingPassenger->passenger_reference ?? '',
                    ];
                }
            }

            // Determine total price and currency from first passenger's fare_details (assuming consistent)
            $totalPrice = (float) ($response['totalPrice'] ?? 0);
            $priceCode = collect($passengers)->first()['fare_details']['fare_price_type']['price']['currency'] ?? 'PKR';
            $airline = 'PIA';


            // Create Booking first
            $booking = Booking::create([
                'client_id'         => $clientId,
                'passenger_details' => json_encode($dbPassengers),
                'order_id'          => $order['orderID'] ?? null,
                'order_owner'       => $order['ownerCode'] ?? null,
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'flight_booking_id' => $bookingReferences['bookingId'] ?? null,
                'ticket_limit'      => Carbon::parse($timeLimits['ticketingTimeLimit'] ?? null),
                'payment_limit'     => Carbon::parse($timeLimits['paymentTimeLimit'] ?? null),
                'airline_id'        => $bookingReferences['airlineID'] ?? null,
                'airline'           => $airline,
                'transaction_id'    => $response['transaction_id'] ?? '-',
                'price_code'        => $priceCode,
                'price'             => $totalPrice,
                'tax'               => $tax,
                'tax_code'          => $taxCode,
                'status'            => Booking::STATUS_INITIAL,
            ]);

            // Create BookingItems per passenger (similar to offerItems in EMI)
            foreach ($passengers as $passenger) {
                $fareDetails = $passenger['fare_details'] ?? [];
                $farePrice = $fareDetails['fare_price_type']['price'] ?? [];
                $bookingItem = BookingItem::create([
                    'passenger_ref' => $passenger['pax_id'] ?? null,
                    'passenger_code' => $passenger['ptc'] ?? null,
                    'services' => json_encode($passenger['services'] ?? []),
                    'taxes' => json_encode($farePrice['taxes'] ?? []),
                    'price' => $farePrice['total_amount'] ?? 0,
                    'price_code' => $farePrice['currency'] ?? '-',
                    'booking_id' => $booking->id,
                ]);
                $penaltyData = [
                    'booking_item_id' => $bookingItem->id,
                    'arrival' => '', // Not available in PIA response
                    'destination' => '', // Not available in PIA response
                    'cancel_fee' => [
                        [
                            'amount' => $cancelFeeData['cancel_fee'] ?? 0,
                            'currency' => $farePrice['currency'] ?? 'PKR',
                            'penalty_id' => $cancelFeeData['penalty_id'] ?? null,
                            'type_code' => $cancelFeeData['type_code'] ?? 'Cancellation'
                        ]
                    ],
                    'change_fee' => [],
                    'refund_fee' => [],
                    'cabin_type' => collect($fareDetails['fare_components'] ?? [])->first()['cabin_type']['name'] ?? 'ECONOMY',
                ];

                $bookingItem->penalties()->create($penaltyData);
            }

            // Create Flights and Segments based on journeys
            foreach ($journeys as $index => $journey) {
                $journeySegments = collect($segments)->whereIn('segment_id', $journey['segment_refs'])->all();
                $isConnected = count($journeySegments) > 1;

                // First segment as departure
                $departureSegment = array_shift($journeySegments);
                $connectingSegment = $isConnected ? array_shift($journeySegments) : null;

                // Overall route
                $departureCode = $departureSegment['origin'] ?? null;
                $arrivalCode = $isConnected ? $connectingSegment['destination'] ?? null : $departureSegment['destination'] ?? null;

                $departureDate = Carbon::parse($departureSegment['departure_time'] ?? null);
                $arrivalDate = $isConnected ? Carbon::parse($connectingSegment['arrival_time'] ?? null) : Carbon::parse($departureSegment['arrival_time'] ?? null);

                $segmentArrivalCode = $isConnected ? $departureSegment['destination'] ?? null : $departureSegment['destination'] ?? null;

                // Price: No per-segment price in PIA, use total or approximate if needed; here using 0 as placeholder
                $flightPrice = 0; // Adjust if per-journey price available
                $priceCode = 'PKR'; // Default

                $flight = Flight::create([
                    'airline'        => $departureSegment['carrier_name'] ?? $departureSegment['carrier'] ?? null,
                    'departure_code' => $departureCode,
                    'arrival_code'   => $arrivalCode,
                    'departure_date' => $departureDate,
                    'arrival_date'   => $arrivalDate,
                    'is_connected'   => $isConnected,
                    'pax_count'      => $paxCount,
                    'cabin_class'    => $cabinClass,
                    'price'          => $flightPrice,
                    'price_code'     => $priceCode,
                    'client_id'      => $clientId,
                    'booking_id'     => $booking->id ?? null,
                ]);

                // Add first segment
                Segment::create([
                    'flight_id'      => $flight->id,
                    'departure_code' => $departureSegment['origin'],
                    'arrival_code'   => $segmentArrivalCode,
                    'departure_date' => $departureDate,
                    'flight_duration'=> $departureSegment['duration'] ?? null,
                    'arrival_date'   => Carbon::parse($departureSegment['arrival_time'] ?? null),
                    'flight_number'  => $departureSegment['flight_number'],
                    'direction'      => $index === 0 ? 'outbound' : 'return',
                ]);

                // Add connecting segment if exists
                if ($isConnected) {
                    Segment::create([
                        'flight_id'      => $flight->id,
                        'departure_code' => $connectingSegment['origin'],
                        'arrival_code'   => $connectingSegment['destination'],
                        'departure_date' => Carbon::parse($connectingSegment['departure_time'] ?? null),
                        'flight_duration'=> $connectingSegment['duration'] ?? null,
                        'arrival_date'   => $arrivalDate,
                        'flight_number'  => $connectingSegment['flight_number'],
                        'direction'      => $index === 0 ? 'outbound' : 'return',
                    ]);
                }

                $flightsCreated[] = $flight;
            }

            BookingRequestBody::create([
                'booking_id' => $booking->id,
                'airline' => $booking->airline,
                'xml_body' => json_encode($response), // Assuming JSON for PIA, adapt if XML
                'client_id' => $clientId,
                'ticket_limit' => $booking->ticket_limit,
                'payment_limit' => $booking->payment_limit,
            ]);

            DB::commit();
            $booking->load('bookingItems.penalties', 'client');
            return [
                'message' => 'Flight booked successfully. Please complete payment before the deadline, otherwise it will be canceled.',
                'booking' => $booking
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed for PIA: ' . $e->getMessage());
            throw $e;
        }
    }
    public function updateBookingFieldsPia(array $response, int $bookingId): Booking // Fetch API
    {
        $order = $response['order'] ?? [];
        $journeys = $response['journeys'] ?? [];
        $isOneWay = count($journeys) === 1;
        $journeyCount = count($journeys);
        $bookingType = $journeyCount === 1 ? 'oneway' : ($journeyCount === 2 ? 'return' : 'multi');
        $booking = Booking::findOrFail($bookingId);

        $ticketTimeLimit = $response['paymentLimit'] ?? $booking->ticket_limit;
        $paymentTimeLimit = $response['paymentLimit'] ?? $booking->payment_limit;

        try {
            $booking->update([
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'order_id'          => $order['orderID'] ?? null,
                'order_owner'       => $order['ownerCode'] ?? null,
                'flight_booking_id' => $order['orderID'] ?? null,
                'ticket_limit'      => Carbon::parse($ticketTimeLimit),
                'payment_limit'     => Carbon::parse($paymentTimeLimit),
                'airline_id'        => $order['ownerCode'] ?? null,
                'airline'           => 'PIA',
                'transaction_id'    => $response['transaction_id'] ?? $booking->transaction_id,
                'price_code'        => data_get($response, 'passengers.0.fare_details.fare_price_type.price.currency', $booking->price_code),
                'price'             => data_get($response, 'totalPrice', $booking->price),
                'status'            => $booking->status !== Booking::STATUS_ISSUED ? Booking::STATUS_CHANGED : $booking->status,
            ]);

            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => Carbon::parse($ticketTimeLimit),
                    'payment_limit' => Carbon::parse($paymentTimeLimit),
                    'xml_body' => json_encode($response ?? []),
                ]);
            }

            return $booking->fresh();
        } catch (\Throwable $e) {
            Log::error('Booking table update failed for PIA: '.$e->getMessage(), ['booking_id' => $bookingId]);
            throw $e;
        }
    }
    public function issueTicketsPia(array $data, int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        DB::beginTransaction();
        try {
            $ticketTimeLimit = $data['paymentLimit'] ?? $booking->ticket_limit;
            $paymentTimeLimit = $data['paymentLimit'] ?? $booking->payment_limit;

            // Update Booking
            $booking->update([
                'status' => Booking::STATUS_ISSUED,
                'only_search' => false,
                'ticket_limit' => Carbon::parse($ticketTimeLimit),
                'payment_limit' => Carbon::parse($paymentTimeLimit),
            ]);

            // Update Booking Request Body
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => Carbon::parse($ticketTimeLimit),
                    'payment_limit' => Carbon::parse($paymentTimeLimit),
                    'xml_body' => json_encode($data ?? []),
                ]);
            }

            // Create Tickets from tickets array or passengers
            $tickets = [];
            foreach ($data['passengers'] ?? [] as $passenger) {
                $ticket = $passenger['ticket'];
                $tickets[] = [
                    'airline' => $data['order']['ownerCode'] ?? null,
                    'passenger_reference' => $ticket['pax_id'] ?? null,
                    'place' => null, // Not in PIA response
                    'ticket_no' => $ticket['ticketNumber'] ?? null,
                    'type' => $passenger['ptc'] ?? null,
                    'issue_date' => now(), // No issue date in response
                    'price_code' => $passenger['fare_details']['fare_price_type']['price']['currency'] ?? null,
                    'price' => $passenger['fare_details']['fare_price_type']['price']['total_amount'] ?? null,
                    'price_reference' => null, // Not in PIA
                    'ticket_details' => json_encode($ticket),
                    'client_id' => $booking->client_id,
                    'booking_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($tickets)) Ticket::insert($tickets);

            DB::commit();
            return $booking->load('bookingItems.penalties', 'client', 'tickets');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ticket issuance failed for PIA: ' . $e->getMessage());
            throw $e;
        }
    }

    // --------------------------------------------------------------AIRBLUE--------------------------------------------------------------
    public function handleBookingAirblue(array $response, int $clientId): array
    {
        $data = $response['data'] ?? [];
        $user = $response['user'] ?? [];
        $travelers = $data['travelers'] ?? [];
        $paxCount = $response['paxCount'] ?? [];
        $flightsData = $data['flights'] ?? [];
        $isOneWay = count($flightsData) === 1;
        $flightCount = count($flightsData);
        $bookingType = $flightCount === 1 ? 'oneway' : ($flightCount === 2 ? 'return' : 'multi');
        $tax = config('variables.tax') ?? 400;
        $taxCode = config('variables.tax_code') ?? 'PKR';

        $flightsCreated = [];

        DB::beginTransaction();

        try {
            $bookingData = $data['booking'] ?? [];
            $timeLimit = $data['ticket_time_limit'] ?? null;
            $passengers = [];
            foreach ($travelers as $passenger) {
                $apiName = isset($passenger['first_name']) ? strtolower(preg_replace('/\s+/', '', $passenger['first_name'])) : null;
                $dob = $passenger['birth_date'] ?? null;
                $existingPassenger = Passenger::get()
                    ->filter(function ($p) use ($apiName, $dob) {
                        $dbName = strtolower(preg_replace('/\s+/', '', $p->given_name));
                        return $dbName === $apiName && $p->dob->format('Y-m-d') === $dob;
                    })
                    ->first();
                if ($existingPassenger) {
                    $existingPassenger->update([
                        'passenger_reference' => $passenger['rph'],
                        'type' => $passenger['type'],
                    ]);
                    $passengers[] = [
                        'id'                 => $existingPassenger->id,
                        'title'              => $existingPassenger->title ?? '',
                        'given_name'         => $existingPassenger->given_name ?? '',
                        'surname'            => $existingPassenger->surname ?? '',
                        'dob'                => $existingPassenger->dob ? $existingPassenger->dob->toDateString() : null,
                        'nationality'        => $existingPassenger->nationality ?? '',
                        'passport_no'        => $existingPassenger->passport_no ?? '',
                        'passport_exp'       => $existingPassenger->passport_exp ? $existingPassenger->passport_exp->toDateString() : null,
                        'type'               => $existingPassenger->type ?? '',
                        'passenger_reference'=> $existingPassenger->passenger_reference ?? '',
                    ];
                }
            }

            // Create Booking first
            $booking = Booking::create([
                'client_id'         => $clientId,
                'passenger_details' => json_encode($passengers),
                'order_id'          => $bookingData['pnr'] ?? null,
                'order_owner'       => $bookingData['instance'] ?? null,
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'flight_booking_id' => $bookingData['pnr'] ?? null,
                'ticket_limit'      => Carbon::parse($timeLimit),
                'payment_limit'     => Carbon::parse($timeLimit), // Assuming same as ticket limit; adjust if separate
                'airline_id'        => null, // No airline ID in Airblue data
                'airline'           => 'airblue',
                'transaction_id'    => '-', // No transaction ID in data
                'price_code'        => $data['total']['currency'] ?? null,
                'price'             => $data['total']['amount'] ?? 0,
                'tax'               => $tax,
                'tax_code'          => $taxCode,
                'status'            => Booking::STATUS_INITIAL,
            ]);

            // Handle BookingItems (per traveler for granularity)
            $travelers = $data['travelers'] ?? [];
            $priceBreakdowns = $data['price_breakdown'] ?? [];
            $ancillaries = $data['ancillaries'] ?? [];
            $seats = $data['seats'] ?? [];
            $raw = $data['raw'] ?? [];

            // Extract penalties from raw FareInfo (assuming same for all)
            $penalties = [];
            $ptcFareBreakdowns = isset($raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'][0]) ? $raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'] : ([$raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']] ?? []);
            foreach ($ptcFareBreakdowns as $ptc) {
                $fareInfo = isset($ptc['FareInfo'][0]) ? $ptc['FareInfo'] : [$ptc['FareInfo']];
                foreach ($fareInfo as $fareInfo) {
                    $charges = $fareInfo['RuleInfo']['ChargesRules'] ?? [];
                    $penalties[] = [
                        'arrival' => $fareInfo['ArrivalAirport']['@attributes']['LocationCode'] ?? null,
                        'destination' => $fareInfo['DepartureAirport']['@attributes']['LocationCode'] ?? null, // Swapped? Adjust if needed
                        'cabin_type' => null, // No cabin in rules
                        'cancel_fee' => $charges['VoluntaryRefunds']['Penalty'] ?? [],
                        'change_fee' => $charges['VoluntaryChanges']['Penalty'] ?? [],
                        'refund_fee' => [], // No separate refund_fee in data
                    ];
                }
            }
            // Map breakdowns by type
            $breakdownMap = [];
            foreach ($priceBreakdowns as $breakdown) {
                $type = $breakdown['passenger_type'];
                $quantity = (int) $breakdown['quantity'];
                $numSegments = count($flightsData);
                $faresPerPax = count($breakdown['per_segment_fares']) / $quantity; // Should be numSegments
                $fares = $breakdown['per_segment_fares'];
                $rphs = $breakdown['traveler_rphs'] ?? [];

                for ($i = 0; $i < $quantity; $i++) {
                    $paxFares = array_slice($fares, $i * $numSegments, $numSegments);
                    $rph = $rphs[$i] ?? null;
                    $paxAncillaries = array_filter($ancillaries, fn($a) => $a['traveler_rph'] === $rph);
                    $paxSeats = array_filter($seats, fn($s) => $s['traveler_rph'] === $rph);

                    // Calculate per-pax price (sum base + taxes + fees across their segments)
                    $paxPrice = 0;
                    $paxTaxes = [];
                    foreach ($paxFares as $fare) {
                        $paxPrice += (int) $fare['base_fare'] + (int) ($fare['taxes_total'] ?? 0) + (int) ($fare['fees_total'] ?? 0);
                        $paxTaxes = array_merge($paxTaxes, $fare['taxes'] ?? []);
                    }

                    $bookingItem = BookingItem::create([
                        'passenger_ref' => $rph,
                        'passenger_code' => $type,
                        'services' => json_encode(array_values($paxAncillaries)), // Ancillaries as services
                        'taxes' => json_encode(array_unique($paxTaxes, SORT_REGULAR)),
                        'price' => $paxPrice,
                        'price_code' => $data['total']['currency'] ?? 'PKR',
                        'booking_id' => $booking->id,
                        'seats' => json_encode(array_values($paxSeats)), // Extra field for seats (add to migration if needed)
                        'fares' => json_encode($paxFares), // Per-segment fares
                    ]);

                    if (!empty($penalties)) {
                        $bookingItem->penalties()->createMany($penalties);
                    }
                }
                $breakdownMap[$type] = $breakdown;
            }

            // Handle Flights and Segments
            foreach ($flightsData as $index => $flight) {
                $departureCode = $flight['departure_airport'];
                $arrivalCode = $flight['arrival_airport'];
                $departureDate = Carbon::parse($flight['departure_datetime']);
                $arrivalDate = Carbon::parse($flight['arrival_datetime']);

                // Calculate total price for this segment (sum across all relevant per_segment_fares)
                $segmentPrice = 0;
                foreach ($priceBreakdowns as $breakdown) {
                    foreach ($breakdown['per_segment_fares'] as $fare) {
                        if ($fare['from'] === $departureCode && $fare['to'] === $arrivalCode) {
                            $segmentPrice += (int) $fare['base_fare'] + (int) ($fare['taxes_total'] ?? 0) + (int) ($fare['fees_total'] ?? 0);
                        }
                    }
                }

                // Determine direction (ENUM only accepts 'outbound' or 'return')
                // First flight is 'outbound', all subsequent flights are 'return'
                $direction = ($index === 0) ? 'outbound' : 'return';

                $flightModel = Flight::create([
                    'airline'        => 'airblue',
                    'departure_code' => $departureCode,
                    'arrival_code'   => $arrivalCode,
                    'departure_date' => $departureDate,
                    'price'          => $segmentPrice,
                    'price_code'     => $data['total']['currency'] ?? 'PKR',
                    'arrival_date'   => $arrivalDate,
                    'is_connected'   => false, // No connections in Airblue data structure
                    'pax_count'      => $paxCount,
                    'cabin_class'    => $flight['cabin'] ?? 'Y',
                    'client_id'      => $clientId,
                    'booking_id'     => $booking->id,
                ]);

                // Create segment
                Segment::create([
                    'flight_id'      => $flightModel->id,
                    'departure_code' => $departureCode,
                    'arrival_code'   => $arrivalCode,
                    'departure_date' => $departureDate,
                    'flight_duration'=> null, // Calculate or from raw
                    'arrival_date'   => $arrivalDate,
                    'flight_number'  => $flight['flight_number'],
                    'direction'      => $direction,
                    'price'          => $segmentPrice, // Total for this segment
                    'price_code'     => $data['total']['currency'] ?? 'PKR',
                ]);

                $flightsCreated[] = $flightModel;
            }

            BookingRequestBody::create([
                'booking_id' => $booking->id,
                'airline' => $booking->airline,
                'xml_body' => json_encode($response['raw'] ?? $response),
                'client_id' => $clientId,
                'ticket_limit' => $booking->ticket_limit,
                'payment_limit' => $booking->payment_limit,
            ]);

            DB::commit();
            $booking->load('bookingItems.penalties', 'client');
            return [
                'message' => 'Flight booked successfully. Please complete payment before the deadline, otherwise it will be canceled.',
                'booking' => $booking
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed for Airblue: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateBookingFieldsAirblue(array $response, int $bookingId): Booking
    {
        $data = $response['data'] ?? [];
        $bookingData = $data['booking'] ?? [];
        $flightsData = $data['flights'] ?? [];
        $isOneWay = count($flightsData) === 1;
        $flightCount = count($flightsData);
        $bookingType = $flightCount === 1 ? 'oneway' : ($flightCount === 2 ? 'return' : 'multi');
        $booking = Booking::findOrFail($bookingId);

        $ticketTimeLimit = Carbon::parse($data['ticket_time_limit'] ?? $booking->ticket_limit);
        $paymentTimeLimit = Carbon::parse($data['ticket_time_limit'] ?? $booking->payment_limit); // Assuming same

        try {
            $booking->update([
                'is_oneway'         => $isOneWay,
                'type'              => $bookingType,
                'order_id'          => $bookingData['pnr'] ?? $booking->order_id,
                'order_owner'       => $bookingData['instance'] ?? $booking->order_owner,
                'flight_booking_id' => $bookingData['pnr'] ?? $booking->flight_booking_id,
                'ticket_limit'      => $ticketTimeLimit,
                'payment_limit'     => $paymentTimeLimit,
                'airline_id'        => null,
                'airline'           => 'airblue',
                'transaction_id'    => $booking->transaction_id,
                'price_code'        => $data['total']['currency'] ?? $booking->price_code,
                'price'             => $data['total']['amount'] ?? $booking->price,
                'status'            => $booking->status !== Booking::STATUS_ISSUED ? Booking::STATUS_CHANGED : $booking->status,
            ]);
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => $ticketTimeLimit,
                    'payment_limit' => $paymentTimeLimit,
                    'xml_body' => json_encode($response ?? []),
                ]);
            }

            return $booking->fresh();
        } catch (\Throwable $e) {
            Log::error('Booking table update failed for Airblue: '.$e->getMessage(), ['booking_id' => $bookingId]);
            throw $e;
        }
    }

    public function issueTicketsAirblue(array $data, int $bookingId): Booking
    {
        $booking = Booking::findOrFail($bookingId);
        DB::beginTransaction();
        try {
            $ticketTimeLimit = $booking->ticket_limit;
            $paymentTimeLimit = $booking->payment_limit;
            $booking->update([
                'status' => Booking::STATUS_ISSUED,
                'only_search' => false,
                'ticket_limit' => $ticketTimeLimit,
                'payment_limit' => $paymentTimeLimit,
            ]);
            if ($booking->bookingRequest) {
                $booking->bookingRequest()->update([
                    'status' => 'change',
                    'ticket_limit' => $ticketTimeLimit,
                    'payment_limit' => $paymentTimeLimit,
                ]);
            }
            // Create Tickets
            $tickets = [];
            $passengerDetails = json_decode($booking->passenger_details, true) ?? [];
            foreach ($data['tickets'] ?? [] as $ticketInfo) {
                $matchedRef = null;
                $ticketName = strtoupper(trim(($ticketInfo['passenger']['first_name'] ?? '') . ' ' . ($ticketInfo['passenger']['last_name'] ?? '')));
                $ticketType = $ticketInfo['passenger_type_code'] ?? '';
                foreach ($passengerDetails as $pd) {
                    $pdName = strtoupper(trim(($pd['given_name'] ?? '') . ' ' . ($pd['surname'] ?? '')));
                    $pdType = $pd['type'] ?? '';
                    if ($pdName === $ticketName && $pdType === $ticketType) {
                        $matchedRef = $pd['passenger_reference'] ?? null;
                        break;
                    }
                }
                $tickets[] = [
                    'airline' => 'airblue',
                    'passenger_reference' => $matchedRef,
                    'place' => null,
                    'ticket_no' => $ticketInfo['ticket_number'] ?? null,
                    'type' => $ticketInfo['passenger_type_code'] ?? null,
                    'issue_date' => now(),
                    'price_code' => null,
                    'price' => null,
                    'price_reference' => null,
                    'ticket_details' => json_encode($ticketInfo),
                    'client_id' => $booking->client_id,
                    'booking_id' => $booking->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($tickets)) Ticket::insert($tickets);
            DB::commit();
            return $booking->load('bookingItems.penalties', 'client', 'tickets');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Flight/Segment creation failed for Airblue: ' . $e->getMessage());
            throw $e;
        }
    }
}