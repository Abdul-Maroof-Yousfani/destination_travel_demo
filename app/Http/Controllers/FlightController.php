<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Mail\SendMail;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\Booking;
use App\Models\ErrorLog;
use App\Models\Ancillary;
use App\Models\BookingId;
use App\Services\PiaService;
use Illuminate\Http\Request;
use App\Models\CancelResponse;
use App\Services\HelperService;
use App\Helpers\HelperFunctions;
use App\Services\AirblueService;
use App\Models\Log as BookingLog;
use App\Services\EmiratesService;
use App\Services\FlyJinnahService;
use Illuminate\Support\Facades\App;
use App\Services\UserBookingService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Services\FlightBookingService;
use App\Services\FlightAggregatorService;

class FlightController extends Controller
{
    // Y Economy
    // W Premium Economy
    // C Business
    // J Business
    // P First
    // F First
    public function __construct(
        protected FlyJinnahService $flyJinnahService,
        protected PiaService $piaService,
        protected EmiratesService $emiratesService,
        protected AirblueService $airblueService,
        protected HelperService $helperService,
        protected UserBookingService $bookingService
    ) {}
    public function search(Request $request, FlightAggregatorService $aggregator)
    {
        $segments = [];
        $i = 1;

        while ($request->has("s$i") && $request->has("d$i")) {
            [$arr, $dest] = explode('-', $request->get("s$i"));
            $segments[] = [
                'arr'  => $arr,
                'dest' => $dest,
                'dep'  => $request->get("d$i"),
            ];
            $i++;
        }

        if (!empty($segments)) {
            $request->merge(['segments' => $segments]);
        }

        session([
            'IdsExpireTimeFj'  => null,
            'IdsExpireTimeEmi' => null,
        ]);

        // ===================== VALIDATION =====================
        if ($request->has('segments')) {
            $validated = $request->validate([
                'segments' => 'required|array|min:1',
                'segments.*.arr'  => 'required|string',
                'segments.*.dest' => 'required|string',
                'segments.*.dep'  => 'required|date',

                'cabinClass' => 'nullable|string|in:Y,W,C,J,P,F',
                'routeType'  => 'nullable|string|in:MULTI,ROUND,ONEWAY',
                'adt' => 'required|numeric',
                'chd' => 'nullable|numeric',
                'inf' => 'nullable|numeric',
            ]);
        } else {
            $validated = $request->validate([
                'arr' => 'required|string',
                'dest' => 'required|string',
                'dep' => 'required|date',
                'return' => 'nullable|date',

                'cabinClass' => 'nullable|string|in:Y,W,C,J,P,F',
                'routeType' => 'nullable|string|in:MULTI,ROUND,ONEWAY',
                'adt' => 'required|numeric',
                'chd' => 'nullable|numeric',
                'inf' => 'nullable|numeric',
            ]);

            $validated['segments'] = [[
                'arr' => $validated['arr'],
                'dest' => $validated['dest'],
                'dep' => $validated['dep'],
                'return' => $validated['return'] ?? null,
            ]];
        }

        // ===================== ROUTE TYPE =====================
        $routeType = $validated['routeType']
            ?? (count($validated['segments']) > 1
                ? 'MULTI'
                : (isset($validated['return']) ? 'ROUND' : 'ONEWAY'));

        session(['cabinClass' => $validated['cabinClass'] ?? 'Y']);

        // ===================== SEARCH =====================
        $flights = $aggregator->searchAllFlights($validated);
        // dd($flights);

        if ($request->ajax()) {
            return response()->json($flights);
        }

        $data = $flights;

        // ===================== MULTI FIX =====================
        if ($routeType === 'MULTI') {
            $legs = collect($flights['flights'])->values(); // reset keys

            $data['legs'] = $legs->mapWithKeys(
                fn ($leg, $index) => [$index + 1 => $leg]
            );

            $data['flights'] = $legs;
        } else {
            $data['flights'] = $flights['flights'];
        }

        // ===================== PAX =====================
        $paxCount = [
            'adt' => $validated['adt'] ?? 1,
            'chd' => $validated['chd'] ?? 0,
            'inf' => $validated['inf'] ?? 0,
        ];

        $firstSeg = $validated['segments'][0] ?? [];

        $data['departure'] = [
            'code'    => $firstSeg['arr'] ?? '',
            'airport' => app(HelperFunctions::class)
                            ->codeToCountry($firstSeg['arr'] ?? ''),
        ];

        $data['arrival'] = [
            'code'    => $firstSeg['dest'] ?? '',
            'airport' => app(HelperFunctions::class)
                            ->codeToCountry($firstSeg['dest'] ?? ''),
        ];

        // ===================== LOG =====================
        $searchKey = ($firstSeg['arr'] ?? '') . '_' . ($firstSeg['dest'] ?? '');
        $now = now()->format('d M Y h:i A');

        BookingLog::updateOrCreate(
            [
                'session_id' => session()->getId(),
                'changes' => $searchKey,
            ],
            [
                'notes' => "User searched flight from {$firstSeg['arr']} to {$firstSeg['dest']} on {$now}",
                'updated_at' => now(),
            ]
        );

        return view('home.flights', [
            'paxCount'    => $paxCount,
            'isRoundTrip' => isset($validated['return']),
            'data'        => $data,
            'routeType'   => $routeType,
        ]);
    }

    public function getBundles(Request $request)
    {
        // return response()->json(
        //     $this->flyJinnahService->getFlightDetails($request->only([
        //         'paxCount', 'firstFlight', 'connectedFlight', 'isReturn'
        //     ])), 200
        // );
        return response()->json(
            $this->flyJinnahService->getFlightDetails($request->only([
                'paxCount', 'firstFlight', 'returnFlight', 
                'firstConnectedFlight', 'returnConnectedFlight'
            ])), 200
        );
    }
    public function bookingDetails(Request $request)
    {
        $airline = $request->airline ?? '';
        dd($airline);
        if (empty($airline)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Airline is missing in request'
            ], 400);
        }
        $data = [];
        $passengerTypes = [
            'adt' => 'Adult',
            'chd' => 'Child',
            'inf' => 'Infant'
        ];
        $now = now()->format('d M Y h:i A');
        BookingLog::create([
            'session_id' => session()->getId(),
            'notes' => "User fetch booking details on {$now}",
        ]);
        // dd($request->all());
        if ($airline === 'emirate') {
            $data = [
                'airline' => $airline,
                'logo' => 'emirates.png',
                'departure' => $request->departureFlight ?? null,
                'return' => $request->returnFlight ?? null,
                'paxCount' => $request->paxCount ?? null,
                'firstFlightBundleId' => $request->firstBundleId ?? null,
                'returnFlightBundleId' => $request->secondBundleId ?? null,
                'responseId' => $request->responseId ?? null,
                'passengerTypes' => $passengerTypes,
                'depOfferIds' => $request->depOfferIds ?? null,
                'rtnOfferIds' => $request->rtnOfferIds ?? null,
            ];
            $flightDetails = $this->emiratesService->getBundlePrice([
                'data' => $data ?? null
            ]);
            if (!empty($flightDetails['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => $flightDetails['error'],
                    'details' => $flightDetails['details'],
                ], 400);
            }
            $data['flightDetails'] = $flightDetails;
            // dd($data['flightDetails']);
            $totalFarePrice = $flightDetails['bundle']['totalPrice'] ?? null;
            session([
                'data' => $data,
                'totalFare' => $totalFarePrice ?? null
            ]);
            return response()->json([
                'status' => 'success',
                'redirect' => route('flightBooking')
            ], 200);
        } else if ($airline === 'flyjinnah') {
            // dd('flyjinnah');
            $isDirectBooking = filter_var($request->isDirectBooking, FILTER_VALIDATE_BOOLEAN);
            // dd($isDirectBooking);
            $flightTotalFare = $request->flightTotalFare ?? null;
            $data = [
                'airline' => $airline,
                'logo' => 'Fly_Jinnah_logo.png',
                'departure' => $request->departureFlight ?? null,
                'return' => $request->returnFlight ?? null,
                'paxCount' => $request->paxCount ?? null,
                'segments' => $request->segments ?? null,
                'firstFlightBundleId' => $request->firstBundleId ?? null,
                'returnFlightBundleId' => $request->secondBundleId ?? null,
                'returnFlight' => $request->rtnSelectedFlight ?? null,
                'departureFlight' => $request->depSelectedFlight ?? null,
                'isDirectBooking' => $isDirectBooking,
                'passengerTypes' => $passengerTypes,
            ];
            if ($isDirectBooking) {
                session([
                    'data' => $data,
                    'totalFare' => $flightTotalFare ?? null
                ]);
                return response()->json([
                    'status' => 'success',
                    'redirect' => route('flightBooking')
                ], 200);
            };

            // merge bundle with transaction id and now add ancis (required) :)
            $farePrice = $this->flyJinnahService->getBundlePrice([
                'data' => $data ?? null
            ]);
            // dd($farePrice);
            $errorMessage = $farePrice['Body']['OTA_AirPriceRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($farePrice['error'] ?? null);
            if ($errorMessage) {
                $filteredMessage = strtok($errorMessage, '[');
                return response()->json([
                    'status' => 'error',
                    'message' => $filteredMessage
                ], 400);
            }
            $totalFarePrice = $farePrice['Body']['OTA_AirPriceRS']['PricedItineraries']['PricedItinerary']['AirItineraryPricingInfo']['ItinTotalFare'] ?? '';
            // dd($totalFarePrice);
            session([
                'data' => $data,
                'totalFare' => $totalFarePrice ?? null
            ]);
            return response()->json(['status' => 'success', 'redirect' => route('flightBooking')]);
        } else if ($airline === 'pia') {
            // dd($request->all());
            $data = [
                'offer_id' => $request->offer_id,
                'airline' => $request->airline ?? null,
                'total_price_pkr' => $request->total_price_pkr ?? null,
                'logo' => 'pia.png',
                'departure' => $request->departureFlight ?? null,
                'return' => $request->returnFlight ?? null,
                'paxCount' => $request->paxCount ?? null,
                'outbound_bundle' => $request->outbound_bundle,
                'inbound_bundle' => $request->inbound_bundle,
                'passengerTypes' => $passengerTypes,
            ];
            $totalFarePrice = ($data['total_price_pkr'] ?? 0);
            session(['data' => $data, 'totalFare' => $totalFarePrice ?? null]);
            return response()->json(['status' => 'success', 'redirect' => route('flightBooking')]);
        } else if ($airline === 'airblue') {
            // dd($request->all());
            $data = [
                'airline' => $airline,
                'logo' => 'airblue.png',
                'flights' => $request->flights ?? null,
                'departure' => $request->departureFlight ?? null,
                'return' => $request->returnFlight ?? null,
                'paxCount' => $request->paxCount ?? null,
                'firstBundle' => $request->firstBundleId ?? null,
                'returnBundle' => $request->secondBundleId ?? null,
                'passengerTypes' => $passengerTypes,
            ];
            $totalFarePrice = ($data['firstBundle']['total_price'] ?? 0) + ($data['returnBundle']['total_price'] ?? 0);
            session([
                'data' => $data,
                'totalFare' => $totalFarePrice ?? null
            ]);
            return response()->json(['status' => 'success', 'redirect' => route('flightBooking')]);
        } 
        return response()->json(['status' => 'error', 'message' => 'Missing flight name!'], 400);
    }
    public function booking()
    {
        $data = session('data', []);
        // dd($data, session('totalFare', []));
        $isLocal = false;
        if (!empty($data) && $data['airline'] === 'airblue') {
            $departure = filter_var($data['flights'][0]['departure']['departure']['local'], FILTER_VALIDATE_BOOLEAN);
            $arrival   = filter_var($data['flights'][0]['departure']['arrival']['local'], FILTER_VALIDATE_BOOLEAN);
            $isLocal = $departure && $arrival;
        }
        if (!empty($data) && $data['airline'] !== 'airblue') {
            $departure = filter_var($data['departure']['departure']['local'], FILTER_VALIDATE_BOOLEAN);
            $arrival   = filter_var($data['departure']['arrival']['local'], FILTER_VALIDATE_BOOLEAN);
            $isLocal = $departure && $arrival;
        }
        $data['isLocal'] = $isLocal;
        // dd($data);
        return view('home.booking', [
            'data' => $data,
            'totalFare' => session('totalFare', []),
            'tax' => config('variables.flyjinnah_api.tax') ?? 0
        ]);
    }
    public function getSeat(Request $request)
    {
        if (empty($request->data)) {
            return response()->json(['status' => 'error', 'message' => 'Missing seat data!'], 400);
        }

        if($request->airline === 'airblue') {
            $getSeat = $this->airblueService->getSeat($request->data ?? null);
            // dd($getSeat);
            if (!empty($getSeat['error'])) {
                return response()->json(['status'  => 'error', 'message' => $getSeat['error'], 'details' => $getSeat['message'] ?? null], 400);
            }
            return response()->json($getSeat);
        }

        $seatMap = $this->flyJinnahService->seatMap([
            'data' => $request->data ?? null,
        ]);
        // dd($seatMap);
        $errorMessage = $seatMap['Body']['OTA_AirSeatMapRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($seatMap['error'] ?? null);
        if ($errorMessage) {
            $filteredMessage = strtok($errorMessage, '[');
            return response()->json([
                'status' => 'error',
                'message' => $filteredMessage
            ], 400);
        }
        $seatXml = isset($seatMap['Body']['OTA_AirSeatMapRS']['SeatMapResponses']['SeatMapResponse'][0]) ? $seatMap['Body']['OTA_AirSeatMapRS']['SeatMapResponses']['SeatMapResponse'] : [$seatMap['Body']['OTA_AirSeatMapRS']['SeatMapResponses']['SeatMapResponse']];
        // $seatXml = $seatMap['Body']['OTA_AirSeatMapRS']['SeatMapResponses']['SeatMapResponse'];
        // dd($seatXml);
        foreach ($seatXml as &$item) {
            $item['FlightSegmentInfo']['ArrivalAirport']['City'] = $this->helperService->codeToCountry($item['FlightSegmentInfo']['ArrivalAirport']['@attributes']['LocationCode']) ?? null;
            $item['FlightSegmentInfo']['DepartureAirport']['City'] = $this->helperService->codeToCountry($item['FlightSegmentInfo']['DepartureAirport']['@attributes']['LocationCode']) ?? null;
        }
        return response()->json([
            'status' => 'success',
            'data' => $seatXml
        ], 200);
    }
    public function confirmSeats(Request $request)
    {
        if (empty($request->data)) {
            return response()->json(['status' => 'error', 'message' => 'Missing seat data!'], 400);
        }

        if($request->airline === 'airblue') {
            $getSeat = $this->airblueService->confirmSeats($request->data ?? null);
            // dd($getSeat);
            if (!empty($getSeat['error'])) {
                return response()->json(['status'  => 'error', 'message' => $getSeat['error'], 'details' => $getSeat['details'] ?? null], 400);
            }
            return response()->json($getSeat);
        }
        return response()->json(['status' => 'false', 'message' => 'Airline not supported'], 400);
    }
    public function getMeal(Request $request)
    {
        // return response()->json([
        //     'status' => 'success',
        //     'data' => null
        // ], 200);
        $mealMap = $this->flyJinnahService->mealMap([
            'data' => $request->data ?? null,
        ]);
        // dd($mealMap);
        $errorMessage = $mealMap['Body']['AA_OTA_AirMealDetailsRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($mealMap['error'] ?? null);
        if ($errorMessage) {
            $filteredMessage = strtok($errorMessage, '[');
            return response()->json([
                'status' => 'error',
                'message' => $filteredMessage
            ], 400);
        }
        $mealXml = isset($mealMap['Body']['AA_OTA_AirMealDetailsRS']['MealDetailsResponses']['MealDetailsResponse'][0]) ? $mealMap['Body']['AA_OTA_AirMealDetailsRS']['MealDetailsResponses']['MealDetailsResponse'] : [$mealMap['Body']['AA_OTA_AirMealDetailsRS']['MealDetailsResponses']['MealDetailsResponse']];
        // dd($mealXml);
        foreach ($mealXml as &$item) {
            $item['FlightSegmentInfo']['ArrivalAirport']['City'] = $this->helperService->codeToCountry($item['FlightSegmentInfo']['ArrivalAirport']['@attributes']['LocationCode']) ?? null;
            $item['FlightSegmentInfo']['DepartureAirport']['City'] = $this->helperService->codeToCountry($item['FlightSegmentInfo']['DepartureAirport']['@attributes']['LocationCode']) ?? null;
        }
        return response()->json([
            'status' => 'success',
            'data' => $mealXml
        ], 200);
    }
    public function getBaggage(Request $request)
    {
        if (empty($request->data)) {
            return response()->json(['status' => 'error', 'message' => 'Missing seat data!'], 400);
        }

        if($request->airline === 'airblue') {
            $getAncillaryItems = $this->airblueService->getAncillaryItems($request->data ?? null);
            // dd($getAncillaryItems);
            if (!empty($getAncillaryItems['error'])) {
                return response()->json(['status'  => 'error', 'message' => $getAncillaryItems['error'], 'details' => $getAncillaryItems['details'] ?? null], 400);
            }
            return response()->json($getAncillaryItems);
        }
        $baggageMap = $this->flyJinnahService->baggageMap([
            'data' => $request->data ?? null,
        ]);
        // dd($baggageMap);
        $errorMessage = $baggageMap['Body']['AA_OTA_AirBaggageDetailsRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($baggageMap['error'] ?? null);
        if ($errorMessage) {
            $filteredMessage = strtok($errorMessage, '[');
            return response()->json([
                'status' => 'error',
                'message' => $filteredMessage
            ], 400);
        }
        $baggageXml = isset($baggageMap['Body']['AA_OTA_AirBaggageDetailsRS']['BaggageDetailsResponses']['OnDBaggageDetailsResponse'][0]) ? $baggageMap['Body']['AA_OTA_AirBaggageDetailsRS']['BaggageDetailsResponses']['OnDBaggageDetailsResponse'] : [$baggageMap['Body']['AA_OTA_AirBaggageDetailsRS']['BaggageDetailsResponses']['OnDBaggageDetailsResponse']];
        // dd($baggageXml);
        foreach ($baggageXml as &$item) { 
            $loop = isset($item['OnDFlightSegmentInfo'][0]) ? $item['OnDFlightSegmentInfo'] : [&$item['OnDFlightSegmentInfo']]; // Ensure it's always an array

            foreach ($loop as &$value) { // Reference to modify the array
                if (isset($value['ArrivalAirport']['@attributes']['LocationCode'])) continue;
                if (isset($value['DepartureAirport']['@attributes']['LocationCode'])) continue;
                $value['ArrivalAirport']['City'] = $this->helperService->codeToCountry($value['ArrivalAirport']['@attributes']['LocationCode']) ?? null;
                $value['DepartureAirport']['City'] = $this->helperService->codeToCountry($value['DepartureAirport']['@attributes']['LocationCode']) ?? null;
            }
        }
        unset($item);
        unset($value);   
        // dd($baggageXml);
        return response()->json([
            'status' => 'success',
            'data' => $baggageXml
        ], 200);
    }
    public function confirmAncillaries(Request $request)
    {
        if (empty($request->data)) {
            return response()->json(['status' => 'error', 'message' => 'Missing seat data!'], 400);
        }

        if($request->airline === 'airblue') {
            $getAncillaries = $this->airblueService->confirmAncillaries($request->data ?? null);
            // dd($getAncillaries);
            if (!empty($getAncillaries['error'])) {
                return response()->json(['status'  => 'error', 'message' => $getAncillaries['error'], 'details' => $getAncillaries['details'] ?? null], 400);
            }
            return response()->json($getAncillaries);
        }
        return response()->json(['status' => 'false', 'message' => 'Airline not supported'], 400);
    }
    public function payment(Request $request)
    {
        $now = now()->format('d M Y h:i A');
        BookingLog::create([
            'session_id' => session()->getId(),
            'notes' => "User add payment on {$now}",
        ]);
        return response()->json(['status' => 'success', 'message' => 'payment is in progress']);
        // return response()->json(['status' => 'error', 'message' => 'payment error'], 400);
    }
    public function bookFlight(Request $request)
    {
        // dd($request->all());
        $airline = $request->airline ?? '';
        $flights = [];
        $now = now()->format('d M Y h:i A');
        // dd($request->all(), session('data'), session('totalFare'));
        if ($airline === 'emirate') {
            $cabinClass = session('cabinClass', 'Y');
            $data = $request->only(['user', 'paymentOnHold', 'offerIds', 'bundleId', 'responseId', 'paxCount', 'passengers']);
            $bookFlight = $this->emiratesService->bookFlight($data ?? null);
            if (!empty($bookFlight['error'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $bookFlight['error'],
                    'details' => $bookFlight['details'] ?? null,
                ], 400);
            }
            $client = $this->bookingService->createUser($request->user);
            $passengers = $this->bookingService->createPassengers($request->passengers, $client->id);
            $flights = app(FlightBookingService::class)->handleBookingEmi($bookFlight, $client->id, $cabinClass);
        } elseif ($airline === 'flyjinnah') {
            $data = [
                'data' => session('data') ?? null,
                'meals' => $request->meals ?? null,
                'baggages' => $request->baggages ?? null,
                'seats' => $request->seats ?? null,
                'user' => $request->user ?? null,
                'passengers' => $request->passengers ?? null,
            ];
            // dd($request->all(), $data);
            $isDirectBooking = filter_var($request->isDirectBooking, FILTER_VALIDATE_BOOLEAN);
            // $finalPrice = [];
            if (!$isDirectBooking) {
                $finalPrice = $this->flyJinnahService->finalPrice($data);
                $errorMessage = $finalPrice['Body']['OTA_AirPriceRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($finalPrice['error'] ?? null);

                if ($errorMessage) return response()->json(['status' => 'error', 'message' => strtok($errorMessage, '['), 'details' => $errorMessage,], 400);
            }
            // dd($data);
            $bookingResponse = $this->flyJinnahService->bookFlight(['data' => $data]);
            // dd($bookingResponse);
            $errorMessage = $bookingResponse['Body']['OTA_AirBookRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($bookingResponse['error'] ?? null);
            if ($errorMessage) return response()->json(['status' => 'error','message' => strtok($errorMessage, '[')], 400);

            $client = $this->bookingService->createUser($request->user);
            $passengers = $this->bookingService->createPassengers($request->passengers, $client->id);
            $flights = app(FlightBookingService::class)->handleBookingFJ($bookingResponse, $client->id);
        } elseif ($airline === 'pia') {
            $cabinClass = session('cabinClass', 'Y');
            $data = $request->only(['user', 'passengers', 'data', 'paxCount']);
            $bookFlight = $this->piaService->bookFlight($data ?? null);
            // dd($bookFlight);
            if (!empty($bookFlight['error'])) {
                return response()->json(['status'  => 'error', 'message' => $bookFlight['error'], 'details' => $bookFlight['message'] ?? null], 400);
            }
            $client = $this->bookingService->createUser($request->user);
            $passengers = $this->bookingService->createPassengers($request->passengers, $client->id);
            $flights = app(FlightBookingService::class)->handleBookingPia($bookFlight, $client->id, $cabinClass);
            // dd($request->all(), $flights);
        } elseif ($airline === 'airblue') {
            $data = $request->only(['user', 'passengers', 'data', 'paxCount']);
            // dd($data);
            if ($request->status === 'fetch') {
                $cabinClass = session('cabinClass', 'Y');
                $bookFlight = $this->airblueService->bookFlight($data ?? null);
                if (!empty($bookFlight['error'])) {
                    return response()->json(['status'  => 'error', 'message' => $bookFlight['error'], 'details' => $bookFlight['details'] ?? null], 400);
                }
                return response()->json($bookFlight);
            } elseif($request->status === 'create') {
                $client = $this->bookingService->createUser($request->user);
                $passengers = $this->bookingService->createPassengers($request->passengers, $client->id);
                $flights = app(FlightBookingService::class)->handleBookingAirblue($data, $client->id);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Missing flight name!'], 400);
        }
        BookingLog::create([
            'session_id' => session()->getId(),
            'notes' => "Temporary booking is generated on {$now}",
        ]);

        BookingLog::where('session_id', session()->getId())->update([
            'session_id' => null,
            'booking_id' => $flights['booking']['id'] ?? null,
        ]);
        return response()->json($flights, empty($flights) ? 404 : 200);
    }
    public function fetchDetails(Request $request) // OrderRetrieveRQ
    {
        $validatedData = $request->validate([
            'bookingId' => 'required|exists:bookings,id',
            'clientId' => 'required|exists:clients,id',
        ]);
        $booking = Booking::with('bookingRequest')->find($validatedData['bookingId']);
        if (!$booking) return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
        if ($booking->client_id !== (int)$validatedData['clientId']) return response()->json(['status' => 'error', 'message' => 'Client does not match this booking.'], 403);
        // dd($booking);
        $airline = strtolower($booking->airline);
        if ($airline === 'emirates') {
            $orderRetrieve = $this->emiratesService->orderRetrieve([
                'amount'  => $booking->price,
                'code'    => $booking->price_code,
                'orderId' => $booking->order_id,
            ]);

            if (!empty($orderRetrieve['error'])) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $orderRetrieve['error'],
                    'details' => $orderRetrieve['details'] ?? null,
                ], 400);
            }
            // Use the robust extractor
            $latest = $this->extractTotalPriceAndCode($orderRetrieve, (float) $booking->price, $booking->price_code);
            $skipComparison = $latest['price_code'] === null || $latest['price'] <= 0;

            if ($skipComparison) {
                return response()->json([
                    'status'   => 'error',
                    'message'  => 'Fetched latest order details. Could not find a valid total price in the response.',
                    'note'     => 'Price comparison skipped due to missing/invalid amounts in OrderRetrieve response.',
                    'latest'   => $latest,
                    'data'     => $orderRetrieve,
                ], 400);
            }

            // Compare
            $comparison = $this->generatePriceComparisonEmi(
                (float) $booking->price,
                $booking->price_code,
                (float) $latest['price'],
                $latest['price_code']
            );
            $updatedBooking = app(FlightBookingService::class)->updateBookingFieldsEmi($orderRetrieve, $booking->id);
            return response()->json([
                'status'      => 'success',
                'message'     => 'Fetched latest order details.',
                'price_source'=> $latest['source'],
                'comparison'  => $comparison,
                'booking_old' => [
                    'price'      => (float) $booking->price,
                    'price_code' => $booking->price_code,
                ],
                'booking_new' => [
                    'price'      => (float) $updatedBooking->price,
                    'price_code' => $updatedBooking->price_code,
                ],
            ]);
        } else if($airline === 'flyjinnah') {
            $orderRetrieve = $this->flyJinnahService->getReservationbyPNR(['orderId' => $booking->order_id]);
            // dd($orderRetrieve);

            $errorMessage = $orderRetrieve['Body']['OTA_AirBookRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($orderRetrieve['error'] ?? null);
            if ($errorMessage) {
                $filteredMessage = strtok($errorMessage, '[');
                return response()->json(['status' => 'error', 'message' => $filteredMessage, 'details' => $errorMessage], 400);
            }
            $skipComparison = $orderRetrieve['code'] === null || $orderRetrieve['amount'] <= 0;

            if ($skipComparison) {
                // You can decide to not update booking at all in this case
                return response()->json([
                    'status'   => 'error',
                    'message'  => $orderRetrieve['message'] ?? 'Fetched latest order details. Could not find a valid total price in the response.',
                    'note'     => 'Price comparison skipped due to missing/invalid amounts in GetReservationbyPNR response.',
                    'data'     => $orderRetrieve,
                ], 400);
            }
            $comparison = $this->generatePriceComparisonFJ(
                (float) $booking->price,
                $booking->price_code,
                (float) $orderRetrieve['amount'],
                $orderRetrieve['code']
            );
            $updatedBooking = app(FlightBookingService::class)->updateBookingFieldsFJ($orderRetrieve, $booking->id);
            // dd($updatedBooking);
            return response()->json([
                'status'      => 'success',
                'message'     => 'Fetched latest order details.',
                'comparison'  => $comparison,
                'booking_old' => [
                    'price'      => (float) $booking->price,
                    'price_code' => $booking->price_code,
                ],
                'booking_new' => [
                    'price'      => (float) $updatedBooking->price,
                    'price_code' => $updatedBooking->price_code,
                ],
                'data' => [
                    'transactionId' => $orderRetrieve['transactionId'],
                    'jsessionId'    => $orderRetrieve['jsessionId'],
                ],
            ]);
        } else if($airline === 'pia') {
            $orderRetrieve = $this->piaService->doTicketPreview(['orderId' => $booking->order_id, 'owner' => $booking->order_owner, 'price_code' => $booking->price_code]);
            // dd($orderRetrieve);
            if (!empty($orderRetrieve['error'])) {
                return response()->json(['status'  => 'error', 'message' => $orderRetrieve['message'], 'details' => $orderRetrieve['error'] ?? null], 400);
            }
            $skipComparison = $orderRetrieve['totalPrice'] <= 0;

            if ($skipComparison) {
                return response()->json([
                    'status'   => 'error',
                    'message'  => 'Fetched latest order details. Could not find a valid total price in the response.',
                    'note'     => 'Price comparison skipped due to missing/invalid amounts in GetReservationbyPNR response.',
                    'data'     => $orderRetrieve,
                ], 400);
            }
            $comparison = $this->generatePriceComparisonFJ(
                (float) $booking->price,
                $booking->price_code,
                (float) $orderRetrieve['totalPrice'],
                'PKR'
            );
            $updatedBooking = app(FlightBookingService::class)->updateBookingFieldsPia($orderRetrieve, $booking->id);
            return response()->json([
                'status'      => 'success',
                'message'     => 'Fetched latest order details.',
                'comparison'  => $comparison,
                'booking_old' => [
                    'price'      => (float) $booking->price,
                    'price_code' => $booking->price_code,
                ],
                'booking_new' => [
                    'price'      => (float) $updatedBooking->price,
                    'price_code' => $updatedBooking->price_code,
                ]
            ]);
        }
        return response()->json(['status' => 'error', 'message' => 'Airline Missing!.'], 401);
    }
    public function confirmBooking(Request $request)
    {
        $validatedData = $request->validate([
            'bookingId' => 'required|exists:bookings,id',
            'clientId' => 'required|exists:clients,id',
            'jsessionId' => 'nullable|string',
            'transactionId' => 'nullable|string',
        ]);
        $booking = Booking::with('bookingRequest', 'payment')->find($validatedData['bookingId']);
        if (!$booking) return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
        if ($booking->client_id !== (int)$validatedData['clientId']) return response()->json(['status' => 'error', 'message' => 'Client does not match this booking.'], 403);
        // dd($booking);
        // ✅ Check if payment exists
        // if (!$booking->payment) return response()->json(['status' => 'error', 'message' => 'No payment found for this booking.'], 400);

        $airline = strtolower($booking->airline);
        $now = now()->format('d M Y h:i A');

        if ($airline === 'emirates') {
            $orderChange = $this->emiratesService->orderChange([
                'amount' => $booking->price,
                'code' => $booking->price_code,
                'orderId' => $booking->order_id,
            ]);
            if (!empty($orderChange['error'])) {
                $details = $orderChange['details'] ?? [];
                if (is_string($details)) {
                    $details = json_decode($details, true) ?? [];
                }
                // $messages = collect($details)->pluck('value')->filter()->values()->all();
                $messages = collect($details)
                    ->map(function ($item) {
                        $value = is_array($item) && isset($item['value']) ? $item['value'] : $item;
                        return is_string($value) ? trim($value) : null;
                    })
                    ->filter()->values()->all();
                $alreadyTicketed = collect($messages)->contains(function ($message) {
                    return str_contains(strtolower($message), 'already ticketed');
                });
                if (!$alreadyTicketed) {
                    BookingLog::create([
                        'booking_id' => $booking->id,
                        'notes' => "Error found on approve tickets on {$now}",
                    ]);
                    $booking->update(['status' => Booking::STATUS_ERROR]);
                    ErrorLog::create([
                        'client_id' => $booking->client_id,
                        'booking_id' => $booking->id,
                        'error_type' => 'ticketing',
                        'error_message' => json_encode($messages),
                        'details' => json_encode($details),
                    ]);
                }
                $messages = empty($messages) ? ($orderChange['details'] ?? []) : $messages;
                return response()->json([
                    'status' => 'error',
                    'message' => $alreadyTicketed
                        ? 'This flight was already ticketed.'
                        : 'Flight booking failed.',
                    'code' => $alreadyTicketed ? 409 : 400,
                    'details' => $messages,
                ], $alreadyTicketed ? 409 : 400);
            }
            $updatedBooking = app(FlightBookingService::class)->issueTicketsEmi($orderChange, $booking->id);
            BookingLog::create(['booking_id' => $booking->id, 'notes' => "Ticket issued on {$now}"]);
            return response()->json(['status' => 'success', 'message' => 'Success! Your flight is booked. Safe travels!.', 'booking' => $updatedBooking]);
        } else if ($airline === 'flyjinnah') {
            $orderChange = $this->flyJinnahService->orderChange([
                'amount' => $booking->price,
                'code' => $booking->price_code,
                'orderId' => $booking->order_id,
                'transactionId' => $validatedData['transactionId'],
                'jsessionId' => $validatedData['jsessionId'] ?? null
            ]);
            // dd($orderChange);
            $alreadyTicketedMsg = str_contains(strtolower($orderChange['message'] ?? ''), 'already paid');
            if ($alreadyTicketedMsg) return response()->json(['status' => 'error', 'message' => 'This flight was already ticketed.'], 409);
            $errorMessage = $orderChange['Body']['OTA_AirBookRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($orderChange['error'] ?? null);
            if ($errorMessage) {
                $details = $orderChange['Body'] ?? [];
                $alreadyTicketed = collect($errorMessage)->contains(function ($errorMessage) {
                    return str_contains(strtolower($errorMessage), 'already paid');
                });
                if (!$alreadyTicketed) {
                    BookingLog::create([
                        'booking_id' => $booking->id,
                        'notes' => "Error found on approve tickets on {$now}",
                    ]);
                    $booking->update(['status' => Booking::STATUS_ERROR]);
                    ErrorLog::create([
                        'client_id' => $booking->client_id,
                        'booking_id' => $booking->id,
                        'error_type' => 'ticketing',
                        'error_message' => json_encode($errorMessage),
                        'details' => json_encode($details),
                    ]);
                };
                return response()->json([
                    'status' => 'error',
                    'message' => $alreadyTicketed ? 'This flight was already ticketed.' : 'Flight booking failed.',
                    'code' => $alreadyTicketed ? 409 : 400,
                    'details' => $errorMessage,
                ], $alreadyTicketed ? 409 : 400);
            }
            $updatedBooking = app(FlightBookingService::class)->issueTicketsFJ($orderChange, $booking->id);
            // dd($updatedBooking);
            BookingLog::create(['booking_id' => $booking->id, 'notes' => "Ticket issued on {$now}"]);
            return response()->json(['status' => 'success', 'message' => 'Success! Your flight is booked. Safe travels!.', 'booking' => $updatedBooking]);
        } else if ($airline === 'pia') {
            $orderChange = $this->piaService->orderChange([
                'amount' => $booking->price,
                'code' => $booking->price_code,
                'orderId' => $booking->order_id,
                'ownerCode' => $booking->order_owner,
            ]);
            // dd($orderChange);
            $alreadyTicketedMsg = str_contains(strtolower($orderChange['message'] ?? ''), 'already');
            if ($alreadyTicketedMsg) return response()->json(['status' => 'error', 'message' => 'This flight was already ticketed.'], 409);
            $errorMessage = $orderChange['error'] ?? null;
            if ($errorMessage) {
                $details = $orderChange['message'] ?? [];
                $alreadyTicketed = collect($errorMessage)->contains(function ($errorMessage) {
                    return str_contains(strtolower($errorMessage), 'already');
                });
                if (!$alreadyTicketed) {
                    BookingLog::create([
                        'booking_id' => $booking->id,
                        'notes' => "Error found on approve tickets on {$now}",
                    ]);
                    $booking->update(['status' => Booking::STATUS_ERROR]);
                    ErrorLog::create([
                        'client_id' => $booking->client_id,
                        'booking_id' => $booking->id,
                        'error_type' => 'ticketing',
                        'error_message' => json_encode($errorMessage),
                        'details' => json_encode($details),
                    ]);
                };
                return response()->json([
                    'status' => 'error',
                    'message' => $alreadyTicketed ? 'This flight was already ticketed.' : 'Flight booking failed.',
                    'code' => $alreadyTicketed ? 409 : 400,
                    'details' => $errorMessage,
                ], $alreadyTicketed ? 409 : 400);
            }
            $updatedBooking = app(FlightBookingService::class)->issueTicketsPia($orderChange, $booking->id);
            // dd($updatedBooking);
            BookingLog::create(['booking_id' => $booking->id, 'notes' => "Ticket issued on {$now}"]);
            return response()->json(['status' => 'success', 'message' => 'Success! Your flight is booked. Safe travels!.', 'booking' => $updatedBooking]);
        } else if ($airline === 'airblue') {
            $orderChange = $this->airblueService->orderChange([
                'amount' => $booking->price,
                'code' => $booking->price_code,
                'orderId' => $booking->order_id,
                'ownerCode' => $booking->order_owner,
            ]);
            // dd($orderChange);
            $alreadyTicketedMsg = str_contains(strtolower($orderChange['message'] ?? ''), 'already');
            if ($alreadyTicketedMsg) return response()->json(['status' => 'error', 'message' => 'This flight was already ticketed.'], 409);
            $errorMessage = $orderChange['error'] ?? null;
            if ($errorMessage) {
                $details = $orderChange['message'] ?? [];
                $alreadyTicketed = collect($errorMessage)->contains(function ($errorMessage) {
                    return str_contains(strtolower($errorMessage), 'already');
                });
                if (!$alreadyTicketed) {
                    BookingLog::create([
                        'booking_id' => $booking->id,
                        'notes' => "Error found on approve tickets on {$now}",
                    ]);
                    $booking->update(['status' => Booking::STATUS_ERROR]);
                    ErrorLog::create([
                        'client_id' => $booking->client_id,
                        'booking_id' => $booking->id,
                        'error_type' => 'ticketing',
                        'error_message' => json_encode($errorMessage),
                        'details' => json_encode($details),
                    ]);
                };
                return response()->json([
                    'status' => 'error',
                    'message' => $alreadyTicketed ? 'This flight was already ticketed.' : 'Flight booking failed.',
                    'code' => $alreadyTicketed ? 409 : 400,
                    'details' => $errorMessage,
                ], $alreadyTicketed ? 409 : 400);
            }
            $updatedBooking = app(FlightBookingService::class)->issueTicketsAirblue($orderChange, $booking->id);
            // dd($updatedBooking);
            BookingLog::create(['booking_id' => $booking->id, 'notes' => "Ticket issued on {$now}"]);
            return response()->json(['status' => 'success', 'message' => 'Success! Your flight is booked. Safe travels!.', 'booking' => $updatedBooking]);
        }
        return response()->json(['status' => 'error', 'message' => 'Airline not supported'], 400);
    }
    public function orderCancel(Request $request) // OrderCancelRQ
    {
        $validatedData = $request->validate([
            'bookingId' => 'required|exists:bookings,id',
            'clientId' => 'required|exists:clients,id',
        ]);
        $booking = Booking::with('tickets')->find($validatedData['bookingId']);
        if (!$booking) return response()->json(['status' => 'error', 'message' => 'Booking not found'], 404);
        if ($booking->client_id !== (int)$validatedData['clientId']) return response()->json(['status' => 'error', 'message' => 'Client does not match this booking.'], 403);
        // if ($booking->status === Booking::STATUS_INITIAL) return response()->json(['status' => 'error', 'message' => 'The booking is in its initial stage and cannot be canceled.'], 200);
        $now = now()->format('d M Y h:i A');
        $airline = strtolower($booking->airline);
        if ($airline === 'emirates') {
            $data = [
                'owner' => $booking->order_owner ?? null,
                'orderId' => $booking->order_id ?? null,
            ];
            $orderCancel = $this->emiratesService->orderCancel($data ?? []);
            if (!empty($orderCancel['error'])) {
                BookingLog::create([
                    'booking_id' => $booking->id,
                    'notes' => "Error Cancel Order on {$now}",
                ]);
                $booking->update(['status' => Booking::STATUS_ERROR]);
                ErrorLog::create([
                    'client_id' => $booking->client_id,
                    'booking_id' => $booking->id,
                    'error_type' => 'cancellation',
                    'error_message' => 'Error Cancel Order',
                    'details' => json_encode($orderCancel),
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $orderCancel['error'] ?? 'Error Cancel Order',
                    'details' => $orderCancel['details'] ?? '',
                ], 400);
            }
            if (!empty($orderCancel['warnings'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order already cancelled',
                    'details' => $orderCancel['warnings']['details'] ?? 'Cannot perform cancel - Order already cancelled',
                ], 400);
            }
            $booking->update(['status' => Booking::STATUS_CANCEL]);
            $booking->tickets()->update(['status' => 'cancel']);
            CancelResponse::create([
                'xml_body' => json_encode($orderCancel),
                'booking_id' => $booking->id
            ]);
            BookingLog::create([
                'booking_id' => $booking->id,
                'notes' => "Cancel Order on {$now}",
            ]);
            return response()->json(['status' => 'success', 'message' => 'Success! Flight cancelled successfully!.', 'data' => $orderCancel]);
        } elseif ($airline === 'flyjinnah') {
            $booking->update(['status' => Booking::STATUS_CANCEL]);
            $booking->tickets()->update(['status' => 'cancel']);
            BookingLog::create([
                'booking_id' => $booking->id,
                'notes' => "Cancel Order on {$now}",
            ]);
            return response()->json(['status' => 'success', 'message' => 'Success! Flight cancelled successfully!.']);
        } elseif ($airline === 'pia') {
            $data = [
                'orderId' => $booking->order_id ?? null,
                'owner' => $booking->order_owner ?? null,
                'code' => $booking->price_code ?? null,
            ];
            $orderCancel = $this->piaService->orderCancel($data ?? []);
            if (!empty($orderCancel['error'])) {
                BookingLog::create([
                    'booking_id' => $booking->id,
                    'notes' => "Error Cancel Order on {$now}",
                ]);
                $booking->update(['status' => Booking::STATUS_ERROR]);
                ErrorLog::create([
                    'client_id' => $booking->client_id,
                    'booking_id' => $booking->id,
                    'error_type' => 'cancellation',
                    'error_message' => 'Error Cancel Order',
                    'details' => json_encode($orderCancel),
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => $orderCancel['error'] ?? 'Error Cancel Order',
                    'details' => $orderCancel['details'] ?? '',
                ], 400);
            }
            if (!empty($orderCancel['warnings'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order already cancelled',
                    'details' => $orderCancel['warnings']['details'] ?? 'Cannot perform cancel - Order already cancelled',
                ], 400);
            }
            $booking->update(['status' => Booking::STATUS_CANCEL]);
            $booking->tickets()->update(['status' => 'cancel']);
            CancelResponse::create([
                'xml_body' => json_encode($orderCancel),
                'booking_id' => $booking->id
            ]);
            BookingLog::create([
                'booking_id' => $booking->id,
                'notes' => "Cancel Order on {$now}",
            ]);
            return response()->json(['status' => 'success', 'message' => 'Success! Flight cancelled successfully!.', 'data' => $orderCancel]);
        }
        return response()->json(['status' => 'warning', 'message' => 'Missing Airline'], 400);
    }
    public function verifyClient(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|numeric',
        ]);
        $user = auth()->guard('client')->user();
        if ($user && $user->email === $request->email) {
            return response()->json([
                'message' => 'Logged in user',
                'status'  => 'success'
            ]);
        }
        $client = Client::where('email', $request->email)->orWhere('phone', $request->phone)->first();
        if ($client) {
            return response()->json(['message' => 'Email or phone already exists, please login', 'status'  => 'warning'], 400);
        } else {
            return response()->json(['message' => 'Email and phone are new', 'status' => 'success']);
        }
    }

    // Helper Emirates
    protected function extractTotalPriceAndCode(array $response, float $fallbackPrice, ?string $fallbackCode): array
    {
        // Try all likely places for total price in different payloads
        $candidates = [
            ['path' => 'bundle.totalPrice',                               'amount' => 'amount', 'code' => 'code'],
            ['path' => 'totalPrice',                                      'amount' => 'amount', 'code' => 'code'],
            ['path' => 'OrderViewRS.Response.TotalOrderPrice',            'amount' => 'Amount.value', 'code' => 'CurrencyCode.value'],
            ['path' => 'OrderViewRS.Response.Order.TotalAmount',          'amount' => 'Amount.value', 'code' => 'CurrencyCode.value'],
            ['path' => 'data.order.totalPrice',                           'amount' => 'amount', 'code' => 'code'],
            ['path' => 'order.totalPrice',                                'amount' => 'amount', 'code' => 'code'],
            ['path' => 'payment.totalPrice',                              'amount' => 'amount', 'code' => 'code'],
            // Add more if you know the exact Emirates structure
        ];

        foreach ($candidates as $c) {
            $amount = data_get($response, "{$c['path']}.{$c['amount']}");
            $code   = data_get($response, "{$c['path']}.{$c['code']}");

            if (!is_null($amount)) {
                // normalize
                $amount = (float) $amount;
                return [
                    'price'      => $amount,
                    'price_code' => $code,
                    'source'     => $c['path'], // for logging/debug
                ];
            }
        }

        // If nothing matched, return fallback (the old booking price)
        return [
            'price'      => $fallbackPrice,
            'price_code' => $fallbackCode,
            'source'     => 'fallback',
        ];
    }
    protected function generatePriceComparisonEmi(float $oldPrice, ?string $oldCode, float $newPrice, ?string $newCode): array
    {
        $delta      = $newPrice - $oldPrice;
        $direction  = $delta > 0 ? 'increased' : ($delta < 0 ? 'decreased' : 'same');
        $absDelta   = abs($delta);
        $pctChange  = $oldPrice > 0 ? round(($absDelta / $oldPrice) * 100, 2) : null;
        // {
        // "old_price": 262926,
        // "old_price_code": "PKR",
        // "new_price": 270000,
        // "new_price_code": "PKR",
        // "difference": 7074,
        // "difference_label": "increased",
        // "percent_change": 2.69,
        // "same_currency": true
        // }
        return [
            'old_price'        => $oldPrice,
            'old_price_code'   => $oldCode,
            'new_price'        => $newPrice,
            'new_price_code'   => $newCode,
            'difference'       => $absDelta,
            'difference_label' => $direction,
            'percent_change'   => $pctChange,   // null when old price = 0
            'same_currency'    => $oldCode === $newCode,
        ];
    }
    protected function generatePriceComparisonFJ($oldPrice, $oldPriceCode, $newPrice, $newPriceCode)
    {
        $difference = $newPrice - $oldPrice;
        $differenceLabel = $difference == 0 ? 'same' : ($difference > 0 ? 'increased' : 'decreased');
        $percentChange = $oldPrice != 0 ? round(($difference / $oldPrice) * 100, 2) : 0;
        $sameCurrency = $oldPriceCode === $newPriceCode;

        return [
            'old_price' => $oldPrice,
            'old_price_code' => $oldPriceCode,
            'new_price' => $newPrice,
            'new_price_code' => $newPriceCode,
            'difference' => $difference,
            'difference_label' => $differenceLabel,
            'percent_change' => $percentChange,
            'same_currency' => $sameCurrency,
        ];
    }

    // private function storeDetails ($data) {
    //     try {
    //         $userDetails = $this->bookingService->saveData($data);
    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }
}