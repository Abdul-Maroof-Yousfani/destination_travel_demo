<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\HelperService;
use GuzzleHttp\Cookie\CookieJar;

class FlyJinnahService
{
    protected $regenerateLogs;
    protected $logPath;
    protected $logPathBooking;
    protected $agencyName;
    protected $searchUrl;
    protected $authUsername;
    protected $authPassword;
    protected $username;
    protected $password;
    protected $authenticate;
    protected $helperService;
    protected $flight_details;
    protected $agentCode;
    protected $tempToken;
    protected $terminalID;

    public function __construct(HelperService $helperService)
    {
        $this->regenerateLogs = true;
        $logDir = storage_path('logs/flyjinnah');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logPath = $logDir . '/' . now()->format('Y_m_d') . '.log';
        $this->logPathBooking = $logDir . '/bookings_' . now()->format('Y_m_d') . '.log';
        // $this->logPath = storage_path('logs/flyjinnah_logs.txt');
        // $this->logPathBooking = storage_path('logs/flyjinnah_logs_bookings.txt');

        $this->helperService = $helperService;
        $this->agencyName = config('services.agency.name');
        $this->authenticate = config('services.flyjinnah_api.authenticate');
        $this->searchUrl = config('services.flyjinnah_api.search');
        $this->flight_details = config('services.flyjinnah_api.flight_details');

        $this->authUsername = config('services.flyjinnah_api.auth_username');
        $this->authPassword = config('services.flyjinnah_api.auth_password');
        $this->username = config('services.flyjinnah_api.username');
        $this->password = config('services.flyjinnah_api.password');
        
        $this->terminalID = 'TestUser/Test Runner';

        $this->agentCode = config('services.flyjinnah_api.agent_code');
        $this->XMLHeader = '
            <soap:Header>
                <wsse:Security soap:mustUnderstand="1"
                    xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                    <wsse:UsernameToken wsu:Id="UsernameToken-26506823"
                        xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                        <wsse:Username>'.$this->username.'</wsse:Username>
                        <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$this->password.'</wsse:Password>
                    </wsse:UsernameToken>
                </wsse:Security>
            </soap:Header>
        ';
        $this->tempToken = 'eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJBQllERVNUX09ORUFQSSIsImlwIjoiNzIuMjU1LjYyLjIxNiIsImlkIjoiMTZmMDRmZjgtOTJlNS00NmNkLWE5YmUtZjYyMzM4MzYxZjgxIiwiZm4iOiJEZXN0aW5hdGlvbiBUb3VycyBUZXN0IiwibG4iOiJSZXZpc2VkIiwib2MiOiJBQUNLSEkyNzE3Iiwic3QiOiIiLCJwcml2aWxlZ2VzIjpbIkxBQUFBQUkiXSwiaWF0IjoxNzU1NzYwOTQ1LCJleHAiOjE3NTU4NDczNDV9.BdJiUD-H1TOZ9z15-qzAqZ9ym9fLNrfRtMjPMCz6jkU';
    }
    public function sendRequest($endpoint, $xmlBody, $isBooking = false)
    {
        try {
            if ($this->regenerateLogs) {
                $formattedRequest = $this->helperService->formatXml((string) $xmlBody);
                file_put_contents($this->logPath, "{$endpoint} Request:\n{$formattedRequest}\n\n\n", FILE_APPEND);
                if ($isBooking) {
                    file_put_contents($this->logPathBooking, "{$endpoint} Request:\n{$formattedRequest}\n\n\n", FILE_APPEND);
                }
            }
            $response = $this->helperService->postXml($this->flight_details, $this->getSoapHeaders($endpoint), $xmlBody);
            if ($this->regenerateLogs) {
                $formattedResponse = $this->helperService->formatXml((string) $response);
                file_put_contents($this->logPath, "{$endpoint} Response:\n{$formattedResponse}\n\n\n\n\n\n", FILE_APPEND);
                if ($isBooking) {
                    file_put_contents($this->logPathBooking, "{$endpoint} Response:\n{$formattedResponse}\n\n\n\n\n\n", FILE_APPEND);
                }
            }

            if (!$response || !$response->successful()) {
                \Log::error('Flight booking request failed Flyjinnah', [
                    'status' => $response?->status(),
                    'response' => $response?->body()
                ]);
                return ['error' => "Flight booking request failed Flyjinnah ({$endpoint}).", 'details' => $response?->body()];
            }
            // dd($response->body());
            return $response;
        } catch (RequestException $e) {
            $response = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Flyjinnah API Request Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response' => $response,
            ]);
            throw new \Exception('API request failed: ' . $e->getMessage());
        }
    }
    public function authenticate()
    {
        if(!$this->authUsername){
            \Log::error('Env error run config cache cmd');
            return null;
        }
        try {
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "Authenticate Request:\n" . json_encode([
            //     'login' => $this->authUsername,
            //     'password' => $this->authPassword,
            // ], JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);}
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false,
            ])->post($this->authenticate, [
                'login' => $this->authUsername,
                'password' => $this->authPassword,
            ]);
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "Authenticate Response:\n" . json_encode([
            //     'response' => $response->body(),
            //     'status' => $response->status(),
            // ], JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);}
            // dd($response->body());
            if (!$response->successful()) {
                \Log::error('Authentication Failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                // dd('okok');
                return 'Authentication Failed!';
            }

            $data = $response->json();
            $token = $data['tokenPair']['accessToken'] ?? null;

            if (!$token) {
                \Log::error('No token received from API.');
                return 'No token received from API.';
            }

            $decoded = $this->helperService->decodeJWTToken($token);
            $expTime = $decoded['exp'] ?? null;

            if ($expTime) {
                $expiresInSeconds = $expTime - time();
                Cache::put('flyjinnah_token', $token, $expiresInSeconds);
            }

            return $token;

        } catch (\Exception $e) {
            \Log::error('Error in authentication request', ['message' => $e->getMessage()]);
            return null;
        }
    }
    private function getToken()
    {
        $cachedToken = Cache::get('flyjinnah_token');
        // dd($cachedToken);
        // dd($this->authenticate());
        if ($cachedToken) {
            $decoded = $this->helperService->decodeJWTToken($cachedToken);
            $expTime = $decoded['exp'] ?? null;
            // dd($expTime);

            if ($expTime && $expTime - time() > 300) {
                return $cachedToken;
            }
        }

        return $this->authenticate();
    }
    public function searchFlights($data)
    {
        $cabinClass = $data['cabinClass'] ?? 'Y';
        $routeType = $data['routeType'] ?? 'ONEWAY';
        $adt = $data['adt'] ?? 1;
        $chd = $data['chd'] ?? 0;
        $inf = $data['inf'] ?? 0;
        if ($routeType === 'MULTI') {
            return;
        }
        $username = $this->username;
        $agentCode = $this->agentCode;
        $token = $this->getToken();
        
        if (!$token) {
            \Log::error('Authentication failed while searching flights.');
            return ['error' => 'Authentication failed.'];
        }

        // Handle new structure with segments array
        $searchOnds = [];
        $isReturn = false;
        
        if (isset($data['segments']) && is_array($data['segments']) && !empty($data['segments'])) {
            foreach ($data['segments'] as $segment) {
                $origin = $segment['arr'] ?? '';
                $destination = $segment['dest'] ?? '';
                $departureDate = $segment['dep'] ?? '';
                
                if (empty($origin) || empty($destination) || empty($departureDate)) {
                    continue;
                }
                
                $searchOnds[] = [
                    "origin" => ["code" => $origin, "locationType" => "AIRPORT"],
                    "destination" => ["code" => $destination, "locationType" => "AIRPORT"],
                    "searchStartDate" => $departureDate,
                    "searchEndDate" => $departureDate,
                    "preferredDate" => $departureDate,
                    "bookingType" => "NORMAL",
                    "cabinClass" => $cabinClass,
                    "ondRef" => "{$origin}/{$destination}",
                    "interlineQuoteDetails" => null
                ];
            }
            
            // Determine if return based on routeType or number of segments
            $isReturn = ($routeType === 'ROUND') || (count($searchOnds) > 1);
        } else {
            // Fallback to old structure for backward compatibility
            $origin = $data['arr'] ?? '';
            $destination = $data['dest'] ?? '';
            $departureDate = $data['dep'] ?? '';
            $returnDate = $data['return'] ?? null;
            
            $searchOnds[] = [
                "origin" => ["code" => $origin, "locationType" => "AIRPORT"],
                "destination" => ["code" => $destination, "locationType" => "AIRPORT"],
                "searchStartDate" => $departureDate,
                "searchEndDate" => $departureDate,
                "preferredDate" => $departureDate,
                "bookingType" => "NORMAL",
                "cabinClass" => $cabinClass,
                "ondRef" => "{$origin}/{$destination}",
                "interlineQuoteDetails" => null
            ];
            
            if ($returnDate) {
                $searchOnds[] = [
                    "origin" => ["code" => $destination, "locationType" => "AIRPORT"],
                    "destination" => ["code" => $origin, "locationType" => "AIRPORT"],
                    "searchStartDate" => $returnDate,
                    "searchEndDate" => $returnDate,
                    "preferredDate" => $returnDate,
                    "bookingType" => "NORMAL",
                    "cabinClass" => $cabinClass,
                    "ondRef" => "{$destination}/{$origin}",
                    "interlineQuoteDetails" => null
                ];
                $isReturn = true;
            }
        }

        $headers = [
            'X-AERO-SALES-CHANNEL' => "OTA",
            'X-AERO-JOURNEY-TYPE' => $isReturn ? "RETURN" : "ONEWAY",
            'X-AERO-USERID' => "$username",
            'X-AERO-AGENT-CODE' => "$agentCode",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$token}",
        ];
        
        $payload = [
            "searchOnds" => $searchOnds,
            "paxCounts" => [
                ["count" => $adt, "paxType" => "ADT"],
                ["count" => $chd, "paxType" => "CHD"],
                ["count" => $inf, "paxType" => "INF"]
            ],
            "isReturn" => $isReturn,
            "currencyCode" => "PKR",
            "cabinClass" => $cabinClass,
            "metaData" => [
                "agentCode" => "OTA",
                "country" => "PK",
                "station" => "KHI",
                "salesChannel" => "TravelAgent",
                "otherMetaData" => [
                    ["metaDataKey" => "FLIGHT_CUTOVER_TIME", "metaDataValue" => date('Y-m-d\TH:i:s')],
                    ["metaDataKey" => "SKIP_OND_MERGE", "metaDataValue" => "true"]
                ]
            ]
        ];
        // dd($payload);
        try {
            if ($this->regenerateLogs) {file_put_contents($this->logPath, "searchFlights Request:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);}
            $cookieJar = new CookieJar();
            $response = Http::withHeaders($headers)
                ->withOptions([
                    'verify' => false,
                    'cookies' => $cookieJar
                ])
                ->post($this->searchUrl, $payload);
            if ($this->regenerateLogs) {file_put_contents($this->logPath, "searchFlights Response:\n" . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n\n\n\n", FILE_APPEND);}
            // dd($response->json(), $this->searchUrl, $payload, $headers);
            if (!$response->successful()) {
                \Log::error('Flight search failed', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['error' => 'Flight search failed.', 'details' => $response->json()];
            }
            // dd($response->json());
            return $this->getFlights($response->json());
        } catch (\Exception $e) {
            \Log::error('Exception in flight search', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while searching flights.'];
        }
    }
    public function getFlightDetails($data) // getBundles
    {
        $paxCount = $data['paxCount'];
        $firstFlightData = $data['firstFlight'];
        $returnFlightData = $data['returnFlight'] ?? null;
        $connectedFlightData = $data['firstConnectedFlight'] ?? null;
        $returnConnectedFlightData = $data['returnConnectedFlight'] ?? null;

        // dd($paxCount, $firstFlightData, $returnFlightData, $connectedFlightData, $returnConnectedFlightData);
        $adt = $paxCount['adt'] ?? 1;
        $chd = $paxCount['chd'] ?? 0;
        $inf = $paxCount['inf'] ?? 0;
        $directionInd = $returnFlightData ? 'Return' : 'OneWay';

        $flightSegmentsXml = '';

        if ($firstFlightData) {
            $flightSegmentsXml .= $this->addFlightSegments(1, [$firstFlightData, $connectedFlightData]);
        }
        if ($returnFlightData) {
            $flightSegmentsXml .= $this->addFlightSegments(1, [$returnFlightData, $returnConnectedFlightData]);
        }
        

        // $soapUrl = $this->flight_details;

        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            '.$this->XMLHeader.'
            <soap:Body xmlns:ns1="http://www.opentravel.org/OTA/2003/05">
                <ns1:OTA_AirPriceRQ EchoToken="12662148060105253838426" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" Version="20061.00">
                    <ns1:POS>
                        <ns1:Source TerminalID="'.$this->terminalID.'">
                            <ns1:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns1:BookingChannel Type="12"/>
                        </ns1:Source>
                    </ns1:POS>
                    <ns1:AirItinerary DirectionInd="'.$directionInd.'">
                        <ns1:OriginDestinationOptions>
                            ' . $flightSegmentsXml . '
                        </ns1:OriginDestinationOptions>
                    </ns1:AirItinerary>
                    <ns1:TravelerInfoSummary>
                        <ns1:AirTravelerAvail>
                            <ns1:PassengerTypeQuantity Code="ADT" Quantity="'.$adt.'"/>
                            <ns1:PassengerTypeQuantity Code="CHD" Quantity="'.$chd.'"/>
                            <ns1:PassengerTypeQuantity Code="INF" Quantity="'.$inf.'"/>
                        </ns1:AirTravelerAvail>
                        <ns1:SpecialReqDetails>
                            <ns1:SSRRequests/>
                        </ns1:SpecialReqDetails>
                    </ns1:TravelerInfoSummary>
                </ns1:OTA_AirPriceRQ>
            </soap:Body>
        </soap:Envelope>';
        // dd($xmlBody);
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "GetBundlesRQ Request:\n" . $xmlBody . "\n\n", FILE_APPEND);}
        try {
            session([
                'JSESSIONID' => null,
                'TransactionIdentifier' => null,
                'IdsExpireTimeFj' => null,
            ]);
            $response = $this->sendRequest('GetBundles', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('GetBundlesRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (GetBundlesRQ).', 'details' => $response];
            }
            $setCookieHeader = $response->header('Set-Cookie');
            $jsessionId = null;

            if (preg_match('/JSESSIONID=([^;]+)/', $setCookieHeader, $matches)) {
                $jsessionId = $matches[0];
            }
            $arrayResponse = $this->helperService->XMLtoJSON($response->body());
            $errorResponse = $arrayResponse['Body']['OTA_AirPriceRS']['Errors'];
            // return $arrayResponse;
            if ($errorResponse){
                \Log::error('Flight get price request failed', [
                    'status' => $errorResponse['Error']['@attributes']['code'] ?? 500,
                    'response' => $errorResponse['Error']['@attributes'] ?? ''
                ]);
                // dd($errorResponse['Error']['@attributes']);
                return ['error' => 'Flight get price request failed.', 'details' => $errorResponse['Error']['@attributes']];
            }
            // dd($arrayResponse);
            session([
                'JSESSIONID' => $jsessionId,
                'TransactionIdentifier' => $arrayResponse['Body']['OTA_AirPriceRS']['@attributes']['TransactionIdentifier'] ?? null,
                'IdsExpireTimeFj' => now()->addMinutes(10),
            ]);
            return([
                'originDestinationOptions' => $arrayResponse['Body']['OTA_AirPriceRS']['PricedItineraries']['PricedItinerary']['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? 'Not found',
                'bundles' => $arrayResponse['Body']['OTA_AirPriceRS']['PricedItineraries']['PricedItinerary']['AirItinerary']['OriginDestinationOptions']['AABundledServiceExt'] ?? 'Not found',
                'prices' => $arrayResponse['Body']['OTA_AirPriceRS']['PricedItineraries']['PricedItinerary']['AirItineraryPricingInfo'] ?? 'Not found',
                'error' => null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Exception in fetching flight details', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while fetching flight details.'];
        }
    }
    public function getBundlePrice($data)
    {
        $paxCount = $data['data']['paxCount'];
        $segments = $data['data']['segments'];
        $firstFlightBundleId = $data['data']['firstFlightBundleId'] ?? null;
        $returnFlightBundleId = $data['data']['returnFlightBundleId'] ?? null;

        $firstFlightBundleId  = ($firstFlightBundleId === 'basic') ? null : $firstFlightBundleId;
        $returnFlightBundleId = ($returnFlightBundleId === 'basic') ? null : $returnFlightBundleId;
        // dd($firstFlightBundleId, $returnFlightBundleId);
        $returnFlight = $data['data']['returnFlight']['flightSegments'] ?? null;
        $departureFlight = $data['data']['departureFlight']['flightSegments'];
        $cookieJar = new CookieJar();
        $transactionIdentifier = session('TransactionIdentifier');
        if (!$transactionIdentifier) return ['error' => 'Transaction identifier not provided'];
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];
        $flightSegmentsXml = '';
        if ($departureFlight) {
            $flightSegmentsXml .= $this->addFlightSegments(1, $departureFlight);
        }
        if ($returnFlight) {
            $flightSegmentsXml .= $this->addFlightSegments(1, $returnFlight);
        }
        $adt = $paxCount['adt'] ?? 1;
        $chd = $paxCount['chd'] ?? 0;
        $inf = $paxCount['inf'] ?? 0;

        $directionInd = $returnFlight ? 'Return' : 'OneWay';
        
        $bundleIds = '';
        if ($firstFlightBundleId) {
            $bundleIds .= '<ns1:OutBoundBunldedServiceId>'.$firstFlightBundleId.'</ns1:OutBoundBunldedServiceId>';
        }
        if ($returnFlightBundleId) {
            $bundleIds .= '<ns1:InBoundBunldedServiceId>'.$returnFlightBundleId.'</ns1:InBoundBunldedServiceId>';
        }
        // dd($bundleIds);
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            '.$this->XMLHeader.'
            <soap:Body xmlns:ns1="http://www.opentravel.org/OTA/2003/05">
                <ns1:OTA_AirPriceRQ EchoToken="12662148060105253838426" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" TransactionIdentifier="'.$transactionIdentifier.'" Version="20061.00">
                    <ns1:POS>
                        <ns1:Source TerminalID="'.$this->terminalID.'">
                            <ns1:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns1:BookingChannel Type="12"/>
                        </ns1:Source>
                    </ns1:POS>
                    <ns1:AirItinerary DirectionInd="'.$directionInd.'">
                        <ns1:OriginDestinationOptions>
                            ' . $flightSegmentsXml . '
                        </ns1:OriginDestinationOptions>
                    </ns1:AirItinerary>
                    <ns1:TravelerInfoSummary>
                        <ns1:AirTravelerAvail>
                            <ns1:PassengerTypeQuantity Code="ADT" Quantity="'.$adt.'"/>
                            <ns1:PassengerTypeQuantity Code="CHD" Quantity="'.$chd.'"/>
                            <ns1:PassengerTypeQuantity Code="INF" Quantity="'.$inf.'"/>
                        </ns1:AirTravelerAvail>
                    </ns1:TravelerInfoSummary>
                    <ns1:BundledServiceSelectionOptions>
                        ' . $bundleIds . '
                    </ns1:BundledServiceSelectionOptions>
                    <ns1:SpecialReqDetails>
                        <ns1:SSRRequests></ns1:SSRRequests>
                    </ns1:SpecialReqDetails>
                </ns1:OTA_AirPriceRQ>
            </soap:Body>
        </soap:Envelope>';
        try {
            $response = $this->sendRequest('BundlePriceRS', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('GetBundlePriceRS request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (GetBundlePriceRS).', 'details' => $response];
            }
            return $this->helperService->XMLtoJSON($response->body());

        } catch (\Exception $e) {
            \Log::error('Exception in booking flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while booking flight.'];
        }
    }
    public function seatMap($segments)
    {
        // dd($segments['data']);
        $soapUrl = $this->flight_details;
        $cookieJar = new CookieJar();
        $transactionIdentifier = session('TransactionIdentifier');
        if (!$transactionIdentifier) return ['error' => 'Transaction identifier not provided'];
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];

        // dd($segments, $transactionIdentifier, $jsessionId);
        $flightSegmentsXml = '';
        $segments = is_array(reset($segments['data'])) ? $segments['data'] : [$segments['data']];
        foreach ($segments as $segment) {
            $flightSegmentsXml .= $this->addFlightSegmentRequest($segment, 'SeatMap');
        }
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:ns="http://www.opentravel.org/OTA/2003/05" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                '.$this->XMLHeader.'
                <soap:Body>
                    <ns:OTA_AirSeatMapRQ EchoToken="11868765275150-1300257933" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" TransactionIdentifier="'.$transactionIdentifier.'" Version="20061.00">
                        <ns:POS>
                            <ns:Source TerminalID="'.$this->terminalID.'">
                            <ns:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns:BookingChannel Type="12"/>
                            </ns:Source>
                        </ns:POS>
                        <ns:SeatMapRequests>
                        ' . $flightSegmentsXml . '
                        </ns:SeatMapRequests>
                    </ns:OTA_AirSeatMapRQ>
                </soap:Body>
            </soap:Envelope>
        ';
        // dd($xmlBody);
        try {
            $response = $this->sendRequest('SeatMap', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('SeatMapRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (SeatMapRQ).', 'details' => $response];
            }
            return $this->helperService->XMLtoJSON($response->body());
        } catch (\Exception $e) {
            \Log::error('Exception in seat map flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while seat map flight.'];
        }
    }
    public function mealMap($segments)
    {
        // dd($segments['data']);
        $soapUrl = $this->flight_details;
        $cookieJar = new CookieJar();
        $transactionIdentifier = session('TransactionIdentifier');
        if (!$transactionIdentifier) return ['error' => 'Transaction identifier not provided'];
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];

        // dd($segments, $transactionIdentifier, $jsessionId);
        $flightSegmentsXml = '';
        $segments = is_array(reset($segments['data'])) ? $segments['data'] : [$segments['data']];
        foreach ($segments as $segment) {
            $flightSegmentsXml .= $this->addFlightSegmentRequest($segment, 'MealDetails');
        }
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:ns="http://www.opentravel.org/OTA/2003/05" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                '.$this->XMLHeader.'
                <soap:Body>
                    <ns:AA_OTA_AirMealDetailsRQ EchoToken="11868765275150-1300257933" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" TransactionIdentifier="'.$transactionIdentifier.'" Version="20061.00">
                        <ns:POS>
                            <ns:Source TerminalID="'.$this->terminalID.'">
                            <ns:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns:BookingChannel Type="12"/>
                            </ns:Source>
                        </ns:POS>
                        <ns:MealDetailsRequests>
                        ' . $flightSegmentsXml . '
                        </ns:MealDetailsRequests>
                    </ns:AA_OTA_AirMealDetailsRQ>
                </soap:Body>
            </soap:Envelope>
        ';
        // dd($xmlBody);
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "MealMapRQ Request:\n" . $xmlBody . "\n\n", FILE_APPEND);}
        try {
            $response = $this->sendRequest('MealMap', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('MealMapRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (MealMapRQ).', 'details' => $response];
            }
            // $response = Http::withHeaders([
            //     'Content-Type' => 'text/xml; charset=utf-8',
            //     'SOAPAction' => '',
            //     'Cookie' => $jsessionId,
            // ])
            // ->withOptions([
            //     'verify' => false,
            //     'cookies' => $cookieJar,
            // ])
            // ->withBody($xmlBody, 'text/xml')
            // ->post($soapUrl);
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "MealMapRS Response:\n" . (string) $response->body() . "\n\n\n\n", FILE_APPEND);}

            // // \Log::info('SOAP meal XML Request:', ['xml' => $xmlBody]);
            // if (!$response->successful()) {
            //     \Log::error('Flight meal map request failed', [
            //         'status' => $response->status(),
            //         'response' => $response->body()
            //     ]);
            //     return ['error' => 'Flight meal map request failed.', 'details' => $response->body()];
            // }
    
            // \Log::info('SOAP meal XML Request:', ['xml' => $response->body()]);
            // dd($this->helperService->XMLtoJSON($response->body()));
            return $this->helperService->XMLtoJSON($response->body());
        } catch (\Exception $e) {
            \Log::error('Exception in meal map flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while meal map flight.'];
        }
    }
    public function baggageMap($segments)
    {
        // dd($segments['data']);
        $soapUrl = $this->flight_details;
        $cookieJar = new CookieJar();
        $transactionIdentifier = session('TransactionIdentifier');
        if (!$transactionIdentifier) return ['error' => 'Transaction identifier not provided'];
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];

        // dd($segments, $transactionIdentifier, $jsessionId);
        $flightSegmentsXml = '';
        $segments = is_array(reset($segments['data'])) ? $segments['data'] : [$segments['data']];
        foreach ($segments as $segment) {
            $flightSegmentsXml .= $this->addFlightSegmentRequest($segment, 'BaggageDetails');
        }
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:ns="http://www.opentravel.org/OTA/2003/05" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                '.$this->XMLHeader.'
                <soap:Body>
                    <ns:AA_OTA_AirBaggageDetailsRQ EchoToken="11868765275150-1300257933" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" TransactionIdentifier="'.$transactionIdentifier.'" Version="20061.00">
                        <ns:POS>
                            <ns:Source TerminalID="'.$this->terminalID.'">
                            <ns:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns:BookingChannel Type="12"/>
                            </ns:Source>
                        </ns:POS>
                        <ns:BaggageDetailsRequests>
                        ' . $flightSegmentsXml . '
                        </ns:BaggageDetailsRequests>
                    </ns:AA_OTA_AirBaggageDetailsRQ>
                </soap:Body>
            </soap:Envelope>
        ';
        // dd($xmlBody);
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "BaggageMapRQ Request:\n" . $xmlBody . "\n\n", FILE_APPEND);}
        try {
            $response = $this->sendRequest('BaggageMap', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('BaggageMapRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (BaggageMapRQ).', 'details' => $response];
            }
            // $response = Http::withHeaders([
            //     'Content-Type' => 'text/xml; charset=utf-8',
            //     'SOAPAction' => '',
            //     'Cookie' => $jsessionId,
            // ])
            // ->withOptions([
            //     'verify' => false,
            //     'cookies' => $cookieJar,
            // ])
            // ->withBody($xmlBody, 'text/xml')
            // ->post($soapUrl);
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "BaggageMapRS Response:\n" . (string) $response->body() . "\n\n\n\n", FILE_APPEND);}
    
            // // \Log::info('SOAP baggage XML Request:', ['xml' => $xmlBody]);
            // if (!$response->successful()) {
            //     \Log::error('Flight baggage map request failed', [
            //         'status' => $response->status(),
            //         'response' => $response->body()
            //     ]);
            //     return ['error' => 'Flight baggage map request failed.', 'details' => $response->body()];
            // }
    
            // \Log::info('SOAP baggage XML Request:', ['xml' => $response->body()]);
            // dd($this->helperService->XMLtoJSON($response->body()));
            // dd($response->body());
            return $this->helperService->XMLtoJSON($response->body());
        } catch (\Exception $e) {
            \Log::error('Exception in baggage map flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while baggage map flight.'];
        }
    }
    public function finalPrice($data)
    {
        // dd($data);
        $soapUrl = $this->flight_details;
        $cookieJar = new CookieJar();
        $paxCount = $data['data']['paxCount'];
        $segments = $data['data']['segments'];
        $returnFlight = $data['data']['returnFlight']['flightSegments'] ?? null;
        $departureFlight = $data['data']['departureFlight']['flightSegments'];
        $returnFlightData = $data['data']['returnFlight'] ?? null;
        $baggages = $data['baggages'] ?? null;
        $meals = $data['meals'] ?? null;
        $seats = $data['seats'] ?? null;
        $firstFlightBundleId = $data['data']['firstFlightBundleId'] ?? null;
        $returnFlightBundleId = $data['data']['returnFlightBundleId'] ?? null;

        $firstFlightBundleId  = ($firstFlightBundleId === 'basic') ? null : $firstFlightBundleId;
        $returnFlightBundleId = ($returnFlightBundleId === 'basic') ? null : $returnFlightBundleId;

        // dd($paxCount, $segments, $returnFlight, $departureFlight, $returnFlightData);
        $transactionIdentifier = session('TransactionIdentifier');
        if (!$transactionIdentifier) return ['error' => 'Transaction identifier not provided'];
        // dd(session());
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];

        $baggageXml = $mealXml = $seatXml = '';

        if (!empty($baggages)) {
            $baggageTag = '';
            foreach ($baggages as $baggage) {
                $baggageTag .= $this->addBaggage($baggage);
            }
            $baggageXml = "<ns1:BaggageRequests>$baggageTag</ns1:BaggageRequests>";
        }
        if (!empty($meals)) {
            $mealTag = '';
            foreach ($meals as $meal) {
                $mealTag .= $this->addAncisTag($meal, 'meal');
            }
            $mealXml = "<ns1:MealRequests>$mealTag</ns1:MealRequests>";
        }

        if (!empty($seats)) {
            $seatTag = '';
            foreach ($seats as $seat) {
                $seatTag .= $this->addAncisTag($seat, 'seat');
            }
            $seatXml = "<ns1:SeatRequests>$seatTag</ns1:SeatRequests>";
        }
        // dd($baggageXml, $mealXml ,$seatXml);
        $adt = $paxCount['adt'] ?? 1;
        $chd = $paxCount['chd'] ?? 0;
        $inf = $paxCount['inf'] ?? 0;
        $directionInd = $returnFlightData ? 'Return' : 'OneWay';

        $flightSegmentsXml = '';
        if ($departureFlight) {
            $flightSegmentsXml .= $this->addFlightSegments(1, $departureFlight, $segments);
        }
        if ($returnFlight) {
            $flightSegmentsXml .= $this->addFlightSegments(1, $returnFlight, $segments);
        }
        $bundleIds = '';
        if ($firstFlightBundleId) {
            $bundleIds .= '<ns1:OutBoundBunldedServiceId>'.$firstFlightBundleId.'</ns1:OutBoundBunldedServiceId>';
        }
        if ($returnFlightBundleId) {
            $bundleIds .= '<ns1:InBoundBunldedServiceId>'.$returnFlightBundleId.'</ns1:InBoundBunldedServiceId>';
        }

        // $bundleIds = '<ns1:OutBoundBunldedServiceId>'.$firstFlightBundleId.'</ns1:OutBoundBunldedServiceId>';
        // if($returnFlightBundleId) {
        //     $bundleIds.= '
        //         <ns1:InBoundBunldedServiceId>'.$returnFlightBundleId.'</ns1:InBoundBunldedServiceId>';
        // }
        $bundleXml = '';
        $bundleXml = '<ns1:BundledServiceSelectionOptions>
                        ' . $bundleIds . '
                    </ns1:BundledServiceSelectionOptions>';
        // $flightSegmentsXml = '';
        // foreach ($segments as $segment) {
        //     $flightSegmentsXml .= $this->addFlightSegments(1, $segment);
        // }
        // <ns1:SSRRequests>
        // </ns1:SSRRequests>

        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema"
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            '.$this->XMLHeader.'
            <soap:Body xmlns:ns1="http://www.opentravel.org/OTA/2003/05">
                <ns1:OTA_AirPriceRQ EchoToken="12662148060105253838426" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" Version="20061.00" TransactionIdentifier="'.$transactionIdentifier.'">
                    <ns1:POS>
                        <ns1:Source TerminalID="'.$this->terminalID.'">
                            <ns1:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns1:BookingChannel Type="12"/>
                        </ns1:Source>
                    </ns1:POS>
                    <ns1:AirItinerary DirectionInd="'.$directionInd.'">
                        <ns1:OriginDestinationOptions>
                            ' . $flightSegmentsXml . '
                        </ns1:OriginDestinationOptions>
                    </ns1:AirItinerary>
                    <ns1:TravelerInfoSummary>
                        <ns1:AirTravelerAvail>
                            <ns1:PassengerTypeQuantity Code="ADT" Quantity="'.$adt.'"/>
                            <ns1:PassengerTypeQuantity Code="CHD" Quantity="'.$chd.'"/>
                            <ns1:PassengerTypeQuantity Code="INF" Quantity="'.$inf.'"/>
                        </ns1:AirTravelerAvail>
                        <ns1:SpecialReqDetails>
                            ' . $baggageXml . '
                            ' . $mealXml . '
                            ' . $seatXml . '
                        </ns1:SpecialReqDetails>
                    </ns1:TravelerInfoSummary>
                        ' . $bundleXml . '
                </ns1:OTA_AirPriceRQ>
            </soap:Body>
        </soap:Envelope>';
        // dd($xmlBody);
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "FinalPriceRQ Request:\n" . $xmlBody . "\n\n", FILE_APPEND);}
        try {
            $response = $this->sendRequest('FinalPrice', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('FinalPriceRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (FinalPriceRQ).', 'details' => $response];
            }
            // $response = Http::withHeaders([
            //     'Content-Type' => 'text/xml; charset=utf-8',
            //     'SOAPAction' => '',
            //     'Cookie' => $jsessionId,
            // ])
            // ->withOptions([
            //     'timeout' => 120,
            //     'verify' => false,
            //     'cookies' => $cookieJar,
            // ])
            // ->withBody($xmlBody, 'text/xml')
            // ->post($soapUrl);
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "FinalPriceRS Response:\n" . (string) $response->body() . "\n\n\n\n", FILE_APPEND);}
            // // dd($this->helperService->XMLtoJSON($response->body()));
            // // \Log::info('SOAP Final Price XML Request:', ['xml' => $xmlBody]);
            // if (!$response->successful()) {
            //     \Log::error('Flight Final Price request failed', [
            //         'status' => $response->status(),
            //         'response' => $response->body()
            //     ]);
            //     return ['error' => 'Flight Final Price request failed.', 'details' => $response->body()];
            // }
            // \Log::info('SOAP Final Price XML Request:', ['xml' => $response->body()]);
            return $this->helperService->XMLtoJSON($response->body());
        } catch (\Exception $e) {
            \Log::error('Exception in Final Price flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while Final Price flight.'];
        }
    }
    public function bookFlight($data)
    {
        $user = $data['data']['user'];
        $passengers = $data['data']['passengers'];
        $segments = $data['data']['data']['segments'];
        $departureFlight = $data['data']['data']['departureFlight']['flightSegments'];
        $returnFlight = $data['data']['data']['returnFlight']['flightSegments'] ?? null;
        $soapUrl = $this->flight_details;
        $cookieJar = new CookieJar();
        $transactionIdentifier = session('TransactionIdentifier');
        if (!$transactionIdentifier) return ['error' => 'Transaction identifier not provided'];
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];
        // dd($transactionIdentifier, $jsessionId);
        $passengerXml = '';
        $adultIndexes = [];
        $adultCounter = 1;

        // dd($passengers);
        foreach ($passengers as $index => $passenger)
        {
            $rph = ($passenger['type'] === 'Adult') ? 'A' : (($passenger['type'] === 'Child') ? 'C' : 'I');
            $passengerTypeCode = ($passenger['type'] === 'Adult') ? 'ADT' : (($passenger['type'] === 'Child') ? 'CHD' : 'INF');
            $rphNumber = $index + 1;
            if ($passenger['type'] === 'Adult') {
                $adultIndexes[] = $rphNumber;
            }
            $infantAssociation = '';
            if ($passenger['type'] === 'Infant' && !empty($adultIndexes)) {
                $assignedAdult = $adultIndexes[$adultCounter - 1] ?? end($adultIndexes);
                $infantAssociation = "/A{$assignedAdult}";
                $adultCounter++;
            }
            // <ns2:Telephone AreaCityCode=\"{$passenger['areaCode']}\" CountryAccessCode=\"{$passenger['countryCode']}\" PhoneNumber=\"{$passenger['phone']}\"/>
            $passengerXml .= "
                <ns2:AirTraveler BirthDate=\"{$passenger['dob']}T00:00:00\" PassengerTypeCode=\"" . $passengerTypeCode . "\">
                    <ns2:PersonName>
                        <ns2:NameTitle>{$passenger['title']}</ns2:NameTitle>
                        <ns2:GivenName>{$passenger['name']}</ns2:GivenName>
                        <ns2:Surname>{$passenger['surname']}</ns2:Surname>
                    </ns2:PersonName>
                    <ns2:Address>
                        <ns2:CountryName Code=\"{$passenger['nationality']}\"/>
                    </ns2:Address>
                    <ns2:Document DocHolderNationality=\"{$passenger['nationality']}\"/>
                    <ns2:TravelerRefNumber RPH=\"{$rph}{$rphNumber}{$infantAssociation}\"/>
                </ns2:AirTraveler>";
        }
        $flightSegmentsXml = '';
        if ($departureFlight) {
            $flightSegmentsXml .= $this->addFlightSegments(2, $departureFlight, $segments);
        }
        if ($returnFlight) {
            $flightSegmentsXml .= $this->addFlightSegments(2, $returnFlight, $segments);
        }
        $userFullName = $user['userFullName'] ?? '';

        $parts = strpos($userFullName, ' ') !== false 
            ? explode(' ', $userFullName, 2)
            : [$userFullName];
        $loggedInUser = '
        <ns1:ContactInfo>
            <ns1:PersonName>
                <ns1:Title>'. ucwords(strtoupper($user['title'] ?? 'MR')) .'</ns1:Title>
                <ns1:FirstName>'.($parts[0] ?? '').'</ns1:FirstName>
                <ns1:LastName>'.($parts[1] ?? $parts[0] ?? '').'</ns1:LastName>
            </ns1:PersonName>
            <ns1:Telephone>
                <ns1:PhoneNumber>'.($user['userPhone'] ?? '').'</ns1:PhoneNumber>
                <ns1:CountryCode>'.($user['userPhoneCode'] ?? '').'</ns1:CountryCode>
                <ns1:AreaCode>'.($user['cityCode'] ?? '').'</ns1:AreaCode>
            </ns1:Telephone>
            <ns1:Email>'.($user['userEmail'] ?? '').'</ns1:Email>
            <ns1:Address>
                <ns1:CountryName>
                    <ns1:CountryName>'.($user['country'] ?? '').'</ns1:CountryName>
                    <ns1:CountryCode>'.($user['countryCode'] ?? '').'</ns1:CountryCode>
                </ns1:CountryName>
                <ns1:CityName>'.($user['city'] ?? '').'</ns1:CityName>
            </ns1:Address>
        </ns1:ContactInfo>';
        // dd($loggedInUser);
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                '.$this->XMLHeader.'
                <soap:Body xmlns:ns1="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" xmlns:ns2="http://www.opentravel.org/OTA/2003/05">
                    <ns2:OTA_AirBookRQ EchoToken="11868765275150-1300257933" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="' . date('Y-m-d\TH:i:s') . '" TransactionIdentifier="'.$transactionIdentifier.'" Version="20061.00">
                        <ns2:POS>
                            <ns2:Source TerminalID="'.$this->terminalID.'">
                            <ns2:RequestorID Type="4" ID="'.$this->username.'"/>
                            <ns2:BookingChannel Type="12"/>
                            </ns2:Source>
                        </ns2:POS>
                        <ns2:AirItinerary>
                            <ns2:OriginDestinationOptions>
                            ' . $flightSegmentsXml . '
                            </ns2:OriginDestinationOptions>
                        </ns2:AirItinerary>
                        <ns2:TravelerInfo>
                            ' . $passengerXml . '
                        </ns2:TravelerInfo>
                    </ns2:OTA_AirBookRQ>
                    <ns1:AAAirBookRQExt>
                        ' . $loggedInUser . '
                    </ns1:AAAirBookRQExt>
                </soap:Body>
            </soap:Envelope>
        ';
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "BookFlightRQ Request:\n" . $xmlBody . "\n\n", FILE_APPEND);}
        // dd($xmlBody);
        try {
            $response = $this->sendRequest('BookFlight', $xmlBody, true);

            if (!$response || isset($response['error'])) {
                \Log::error('BookFlightRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (BookFlightRQ).', 'details' => $response];
            }
            // $response = Http::withHeaders([
            //     'Content-Type' => 'text/xml; charset=utf-8',
            //     'SOAPAction' => '',
            //     'Cookie' => $jsessionId,
            // ])
            // ->withOptions([
            //     'timeout' => 120,
            //     'verify' => false,
            //     'cookies' => $cookieJar,
            // ])
            // ->withBody($xmlBody, 'text/xml')
            // ->post($soapUrl);
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "BookFlightRS Response:\n" . (string) $response->body() . "\n\n\n\n", FILE_APPEND);}
            // // \Log::info('SOAP XML Booking Request:', ['xml' => $xmlBody]);
            // if (!$response->successful()) {
            //     \Log::error('Flight booking request failed', [
            //         'status' => $response->status(),
            //         'response' => $response->body()
            //     ]);
            //     return ['error' => 'Flight booking request failed.', 'details' => $response->body()];
            // }
            return $this->helperService->XMLtoJSON($response->body());
        } catch (\Exception $e) {
            \Log::error('Exception in booking flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while booking flight.'];
        }
    }
    public function orderChange($data) // modifyReservation
    {
        if(empty($data) || !isset($data['orderId']) || !isset($data['amount']) || !isset($data['transactionId'])) return 'Some data is missing for order change';
        $orderId = $data['orderId'];
        $amount = $data['amount'];
        $transactionId = $data['transactionId'];
        $jsessionId = $data['jsessionId'] ?? null;
        $code = $data['code'] ?? 'PKR';
        $cookieJar = new CookieJar();
        $soapUrl = $this->flight_details;
        session(['JSESSIONID' => $jsessionId]);
        // $jsessionId = session('JSESSIONID');
        // if (!$jsessionId) return ['error' => 'Jsession Id not provided'];
        // dd($orderId, $amount, $code, $cookieJar, $transactionId);
        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                '.$this->XMLHeader.'
                <soap:Body xmlns:ns1="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" xmlns:ns2="http://www.opentravel.org/OTA/2003/05">
                    <ns8:OTA_AirBookModifyRQ EchoToken="11839640750780-171674061" SequenceNmbr="1" TransactionIdentifier="'.$transactionId.'"
                        Version="20061.0" xmlns:ns8="http://www.opentravel.org/OTA/2003/05" xmlns="http://www.isaaviation.com/thinair/webservices/api/airinventory" 
                        xmlns:ns2="http://www.isaaviation.com/thinair/webservices/api/airreservation" xmlns:ns4="http://www.isaaviation.com/thinair/webservices/api/commons"
                        xmlns:ns3="http://www.isaaviation.com/thinair/webservices/api/airschedules" xmlns:ns5="http://www.isaaviation.com/thinair/webservices/api/eticketupdate"
                        xmlns:ns6="http://www.isaaviation.com/thinair/webservices/api/updatepfs" xmlns:ns7="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05">
                        <ns8:POS>
                            <ns8:Source TerminalID="'.$this->terminalID.'">
                                <ns8:RequestorID Type="4" ID="'.$this->username.'"/>
                                <ns8:BookingChannel Type="12"/>
                            </ns8:Source>
                        </ns8:POS>
                        <ns8:AirBookModifyRQ ModificationType="9">
                            <ns8:Fulfillment>
                                <ns8:PaymentDetails>
                                    <ns8:PaymentDetail>
                                        <ns8:DirectBill>
                                            <ns8:CompanyName Code="'.$this->agentCode.'"/>
                                        </ns8:DirectBill>
                                        <ns8:PaymentAmount Amount="'.$amount.'" CurrencyCode="'.$code.'" DecimalPlaces="2"/>
                                    </ns8:PaymentDetail>
                                </ns8:PaymentDetails>
                            </ns8:Fulfillment>
                            <ns8:BookingReferenceID ID="'.$orderId.'" Type="14"/>
                        </ns8:AirBookModifyRQ>
                    </ns8:OTA_AirBookModifyRQ>
                    <ns7:AAAirBookModifyRQExt xmlns:ns7="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" xmlns="http://www.isaaviation.com/thinair/webservices/api/airinventory" xmlns:ns2="http://www.isaaviation.com/thinair/webservices/api/airreservation" xmlns:ns4="http://www.isaaviation.com/thinair/webservices/api/commons" xmlns:ns3="http://www.isaaviation.com/thinair/webservices/api/airschedules" xmlns:ns5="http://www.isaaviation.com/thinair/webservices/api/eticketupdate" xmlns:ns6="http://www.isaaviation.com/thinair/webservices/api/updatepfs" xmlns:ns8="http://www.opentravel.org/OTA/2003/05">
                        <ns7:AALoadDataOptions>
                            <ns7:LoadTravelerInfo>true</ns7:LoadTravelerInfo>
                            <ns7:LoadAirItinery>true</ns7:LoadAirItinery>
                            <ns7:LoadPriceInfoTotals>true</ns7:LoadPriceInfoTotals>
                            <ns7:LoadFullFilment>true</ns7:LoadFullFilment>
                        </ns7:AALoadDataOptions>
                    </ns7:AAAirBookModifyRQExt>
                </soap:Body>
            </soap:Envelope>
        ';
        try {
            $response = $this->sendRequest('modifyReservation', $xmlBody, true);

            if (!$response || isset($response['error'])) {
                \Log::error('modifyReservationRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (modifyReservationRQ).', 'details' => $response];
            }
            $jsonResponse = $this->helperService->XMLtoJSON($response);
            if ($jsonResponse['Body']['OTA_AirBookRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($jsonResponse['error'] ?? null)) {
                return $jsonResponse;
            }
            return $this->formatBookingResponse($jsonResponse);
        } catch (\Exception $e) {
            \Log::error('Exception in booking flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while booking flight.'];
        }
    }
    public function getReservationbyPNR($data)
    {
        if(empty($data) || !isset($data['orderId'])) return 'Some data is missing for order change';
        $orderId = $data['orderId'];
        $cookieJar = new CookieJar();
        $soapUrl = $this->flight_details;

        $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                '.$this->XMLHeader.'
                <soap:Body xmlns:ns1="http://www.isaaviation.com/thinair/webservices/OTA/Extensions/2003/05" xmlns:ns2="http://www.opentravel.org/OTA/2003/05">
                    <ns2:OTA_ReadRQ EchoToken="11839640750780-171674061" PrimaryLangID="en-us" SequenceNmbr="1" TimeStamp="'.date('Y-m-d\TH:i:s').'" Version="20061.00">
                        <ns2:POS>
                            <ns2:Source TerminalID="'.$this->terminalID.'">
                                <ns2:RequestorID ID="'.$this->username.'" Type="4"/>
                                <ns2:BookingChannel Type="12"/>
                            </ns2:Source>
                        </ns2:POS>
                        <ns2:ReadRequests>
                            <ns2:ReadRequest>
                                <ns2:UniqueID ID="'.$orderId.'" Type="14"/>
                            </ns2:ReadRequest>
                        </ns2:ReadRequests>
                    </ns2:OTA_ReadRQ>
                    <ns1:AAReadRQExt>
                        <ns1:AALoadDataOptions>
                            <ns1:LoadTravelerInfo>true</ns1:LoadTravelerInfo>
                            <ns1:LoadAirItinery>true</ns1:LoadAirItinery>
                            <ns1:LoadPriceInfoTotals>true</ns1:LoadPriceInfoTotals>
                            <ns1:LoadFullFilment>true</ns1:LoadFullFilment>
                        </ns1:AALoadDataOptions>
                    </ns1:AAReadRQExt>
                </soap:Body>
            </soap:Envelope>
        ';
        try {
            $response = $this->sendRequest('GetReservationbyPNR', $xmlBody);

            if (!$response || isset($response['error'])) {
                \Log::error('GetReservationbyPNRRQ request failed', ['response' => $response]);
                return ['error' => 'Flight booking request failed Flyjinnah (GetReservationbyPNRRQ).', 'details' => $response];
            }
            $setCookieHeader = $response->header('Set-Cookie');
            $jsessionId = null;
            if (preg_match('/JSESSIONID=([^;]+)/', $setCookieHeader, $matches)) $jsessionId = $matches[0];
            // dd($jsessionId);
            $jsonResponse = $this->helperService->XMLtoJSON($response->body());
            if ($jsonResponse['Body']['OTA_AirBookRS']['Errors']['Error']['@attributes']['ShortText'] ?? ($jsonResponse['error'] ?? null)) {
                return $jsonResponse;
            }
            $data = [
                'amount' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['Amount'] ?? null,
                'code' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['CurrencyCode'] ?? null,
                'transactionId' => $jsonResponse['Body']['OTA_AirBookRS']['@attributes']['TransactionIdentifier'] ?? null,
                'jsessionId' => $jsessionId,
                'message' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['Ticketing']['TicketAdvisory'] ?? 'No message available',
                'response' => $jsonResponse,
            ];
            if (!empty($jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['Ticketing']['@attributes']['TicketTimeLimit'])) {
                $data['timeLimit'] = $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['Ticketing']['@attributes']['TicketTimeLimit'];
            }
            // dd($jsonResponse);
            return $data;
        } catch (\Exception $e) {
            \Log::error('Exception in booking flight', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while booking flight.'];
        }
    }
    // ------------------  Helper Functions  ---------------------
    private function getFlights($fjData)
    {
        if (empty($fjData['ondWiseFlightCombinations'])) {
            return [];
        }
        
        foreach ($fjData['ondWiseFlightCombinations'] as $ondData) {
            foreach ($ondData['dateWiseFlightCombinations'] as $combo) {
                $options = $combo['flightOptions'] ?? [];
        
                // Filter out NOT_AVAILABLE flights
                $validOptions = array_filter($options, function ($opt) {
                    return ($opt['availabilityStatus'] ?? '') !== 'NOT_AVAILABLE';
                });
        
                if (empty($validOptions)) {
                    return [];
                }
            }
        }
        $flightsData = $fjData['ondWiseFlightCombinations'] ?? [];
        $data = [];
        $tax = config('variables.flyjinnah_api.tax') ?? 0;

        foreach ($flightsData as $route => $flightData) {
            if (empty($flightData['dateWiseFlightCombinations'])) {
                continue;
            }
            foreach ($flightData['dateWiseFlightCombinations'] as $date => $details) {
                foreach ($details['flightOptions'] as &$option) {
                    if(empty($option['flightSegments'])) {
                        return $data = [
                            'route' => '',
                            'date' => '',
                            'flights' => []
                        ];
                    }
                    $flightSegments = collect($option['flightSegments']);
                    $option['isConnected'] = $flightSegments->count() > 1;

                    $arrivalDateTime = Carbon::parse($flightSegments->first()['arrivalDateTimeLocal']);
                    $departureDateTime = Carbon::parse($flightSegments->last()['departureDateTimeLocal']);

                    $option['departureTime'] = $arrivalDateTime->format('h:i A');
                    $option['departureDate'] = $arrivalDateTime->format('d M Y');
                    $option['arrivalTime'] = $departureDateTime->format('h:i A');
                    $option['timeDifference'] = $departureDateTime->diff($arrivalDateTime)->format('%hh %im');
                    $option['departureDayIncrease'] = $arrivalDateTime->toDateString() !== $departureDateTime->toDateString();

                    $formatCode = fn($code) => $this->helperService->codeToCountry($code) . "($code)";
                    $option['originCode'] = $formatCode($flightSegments->first()['origin']['airportCode'] ?? null);
                    $option['destinationCode'] = $formatCode($flightSegments->last()['destination']['airportCode'] ?? null);
                    $option['price'] = isset($option['cabinPrices'][0]['price']) ? round($option['cabinPrices'][0]['price'] + ($tax ?? 0)) : null;
                    $option['cabinClass'] = $option['cabinPrices'][0]['cabinClass'] ?? null;
                }
                [$org, $des] = explode('/', $route);
                $data[] = [
                    'route' => $this->helperService->codeToCountry($org) . ' → ' . $this->helperService->codeToCountry($des),
                    'date' => Carbon::parse($date)->format('D, d M, Y'),
                    'flights' => $details['flightOptions']
                ];
            }
        }
        // dd($data);
        return $data;

    }
    private function addFlightSegments($tag, $flights, $segments = null)
    {
        $segmentsXml = '';
        if (isset($flights['flightNumber'])) {
            $flights = [$flights];
        }
        foreach ($flights as $flightData) {
            if (!$flightData) continue;
            $departureCode = $flightData['origin']['airportCode'] ?? $flightData['destination'] ?? '';
            $departureTerminal = $flightData['origin']['terminal'] ?? $flightData['depTerminal'] ?? '';
            $arrivalCode = $flightData['destination']['airportCode'] ?? $flightData['origin'];
            $arrivalTerminal = $flightData['destination']['terminal'] ?? $flightData['arrTerminal'] ?? '';
            $departureDate = $flightData['departure'] ?? $flightData['departureDateTimeLocal'];
            $arrivalDate = $flightData['arrival'] ?? $flightData['arrivalDateTimeLocal'];
            $flightId = $flightData['flightNumber'];
            $airlineCode = substr($flightId, 0, 2);
            $segment = $segments ? $this->getSegmentAttributes($flightId, $segments) : null;
            $rph = $flightData['rph'] ?? ($segment['rph'] ?? '');
            $segmentsXml .= '<ns'.$tag.':FlightSegment ArrivalDateTime="' . $arrivalDate . '" DepartureDateTime="' . $departureDate . '" FlightNumber="' . $flightId . '" RPH="' . $rph . '">
                                <ns'.$tag.':DepartureAirport LocationCode="' . $departureCode . '" Terminal="' . $departureTerminal . '"/>
                                <ns'.$tag.':ArrivalAirport LocationCode="' . $arrivalCode . '" Terminal="' . $arrivalTerminal . '"/>
                                <ns'.$tag.':OperatingAirline Code="' . $airlineCode . '"/>
                            </ns'.$tag.':FlightSegment>';
        }
        return $segmentsXml ? '<ns'.$tag.':OriginDestinationOption>' . $segmentsXml . '</ns'.$tag.':OriginDestinationOption>' : '';
    }
    private function addFlightSegmentRequest($flightData, $type)
    {
        if (!$flightData || !in_array($type, ['SeatMap', 'MealDetails', 'BaggageDetails'])) {
            return '';
        }

        $flightId = $flightData['flightNumber'] ?? '';
        $airlineCode = substr($flightId, 0, 2);

        $requestTag = "ns:{$type}Request";

        return '<' . $requestTag . ' TravelerRefNumberRPHs="">' .
                    '<ns:FlightSegmentInfo ' .
                        'ArrivalDateTime="' . htmlspecialchars($flightData['arrival']) . '" ' .
                        'DepartureDateTime="' . htmlspecialchars($flightData['departure']) . '" ' .
                        'FlightNumber="' . htmlspecialchars($flightData['flightNumber']) . '" ' .
                        'RPH="' . htmlspecialchars($flightData['rph']) . '" returnFlag="false">' .
                        '<ns:DepartureAirport LocationCode="' . htmlspecialchars($flightData['destination']) . '" Terminal="' . htmlspecialchars($flightData['arrTerminal']) . '"/>' .
                        '<ns:ArrivalAirport LocationCode="' . htmlspecialchars($flightData['origin']) . '" Terminal="' . htmlspecialchars($flightData['depTerminal']) . '"/>' .
                        '<ns:OperatingAirline Code="' . htmlspecialchars($airlineCode) . '"/>' .
                    '</ns:FlightSegmentInfo>' .
                '</' . $requestTag . '>';
    }
    private function addAncisTag($data, $type)
    {
        if (!$data || !$type) return '';
        $tagMap = [
            'baggage' => ['tag' => 'BaggageRequest', 'code' => 'BaggageCode', 'key' => 'baggageCode'],
            'meal'    => ['tag' => 'MealRequest',    'code' => 'mealCode',    'key' => 'mealCode'],
            'seat'    => ['tag' => 'SeatRequest',    'code' => 'SeatNumber',  'key' => 'seatId'],
        ];
        if (!isset($tagMap[$type])) return '';
        $tagName = $tagMap[$type]['tag'];
        $codeAttr = $tagMap[$type]['code'];
        $codeKey = $tagMap[$type]['key'];
        $extraAttr = $type === 'meal' ? ' mealQuantity="1"' : '';
        $xml = '';
        foreach ($data as $item) {
            $code = $item[$codeKey] ?? '';
            $traveler = htmlspecialchars($item['passenger'] ?? '');
            if (!$traveler) continue;
            $rph = htmlspecialchars($item['rph'] ?? '');
            $date = htmlspecialchars($item['depDate'] ?? '');
            $flight = htmlspecialchars($item['flightNo'] ?? '');

            $xml .= "
            <ns1:$tagName $codeAttr=\"$code\"$extraAttr TravelerRefNumberRPHList=\"$traveler\" FlightRefNumberRPHList=\"$rph\" DepartureDate=\"$date\" FlightNumber=\"$flight\"/>
            ";
        }
        return $xml;
    }
    private function addBaggage($data)
    {
        if (empty($data)) return '';
        $xml = '';
        $baggages = isset($data[0]) ? $data : [$data];
        foreach ($baggages as $baggageData) {
            $passenger   = $baggageData['passenger'] ?? '';
            $baggageCode = $baggageData['baggageCode'] ?? '';
            $rphs        = $baggageData['rph'] ?? [];
            $flightNos   = $baggageData['flightNo'] ?? [];
            $depDates    = $baggageData['depDate'] ?? [];
            foreach ($rphs as $i => $rph) {
                $flightNumber   = $flightNos[$i] ?? '';
                $departureDate  = $depDates[$i] ?? '';

                $xml .= '<ns1:BaggageRequest '
                    . 'baggageCode="' . htmlspecialchars($baggageCode) . '" '
                    . 'TravelerRefNumberRPHList="' . htmlspecialchars($passenger) . '" '
                    . 'FlightRefNumberRPHList="' . htmlspecialchars($rph) . '" '
                    . 'DepartureDate="' . htmlspecialchars($departureDate) . '" '
                    . 'FlightNumber="' . htmlspecialchars($flightNumber) . '"'
                    . ' />
                    ';
            }
        }
        return $xml;
    }
    private function getSegmentAttributes($flightNo, $segments)
    {
        $segmentArray = isset($segments[0]) ? $segments : [$segments];
        foreach ($segmentArray as $segment) {
            if (($segment['flightNumber'] ?? null) === $flightNo) {
                return $segment;
            }
        }
        return null;
    }
    private function formatBookingResponse($jsonResponse)
    {
        // Initialize passengers array
        $passengers = [];
        $airTravelers = $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['TravelerInfo']['AirTraveler'] ?? [];
        if (!is_array($airTravelers) || !isset($airTravelers[0])) {
            $airTravelers = [$airTravelers]; // Ensure array for single traveler
        }

        // Map passenger details and their reference IDs
        $travelerRefs = [];
        foreach ($airTravelers as $traveler) {
            $rph = $traveler['TravelerRefNumber']['@attributes']['RPH'] ?? null;
            if ($rph) {
                $travelerRefs[$rph] = [
                    'name' => trim(($traveler['PersonName']['NameTitle'] ?? '') . ' ' . 
                                ($traveler['PersonName']['GivenName'] ?? '') . ' ' . 
                                ($traveler['PersonName']['Surname'] ?? '')),
                    'passenger_type' => $traveler['@attributes']['PassengerTypeCode'] ?? 'Unknown',
                    'nationality' => $traveler['Document']['@attributes']['DocHolderNationality'] ?? 'N/A',
                    'phone_number' => $traveler['Telephone']['@attributes']['PhoneNumber'] ?? 'N/A',
                    'ref_no' => $traveler['TravelerRefNumber']['@attributes']['RPH'] ?? '-',
                ];
            }
        }

        // Extract seats
        $seatsByTraveler = [];
        $seatRequests = $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['TravelerInfo']['SpecialReqDetails']['SeatRequests']['SeatRequest'] ?? [];
        if (!is_array($seatRequests) || !isset($seatRequests[0])) {
            $seatRequests = [$seatRequests];
        }
        foreach ($seatRequests as $request) {
            if (isset($request['@attributes'])) {
                $rph = $request['@attributes']['TravelerRefNumberRPHList'] ?? null;
                if ($rph) {
                    $seatsByTraveler[$rph][] = [
                        'seat_number' => $request['@attributes']['SeatNumber'] ?? 'N/A',
                        'flight_number' => $request['@attributes']['FlightNumber'] ?? 'N/A',
                        'departure_date' => $request['@attributes']['DepartureDate'] ?? 'N/A',
                    ];
                }
            }
        }

        // Extract baggage
        $baggageByTraveler = [];
        $baggageRequests = $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['TravelerInfo']['SpecialReqDetails']['BaggageRequests']['BaggageRequest'] ?? [];
        if (!is_array($baggageRequests) || !isset($baggageRequests[0])) {
            $baggageRequests = [$baggageRequests];
        }
        foreach ($baggageRequests as $request) {
            if (isset($request['@attributes'])) {
                $rph = $request['@attributes']['TravelerRefNumberRPHList'] ?? null;
                if ($rph) {
                    $baggageByTraveler[$rph][] = [
                        'baggage_code' => $request['@attributes']['baggageCode'] ?? 'N/A',
                        'flight_number' => $request['@attributes']['FlightNumber'] ?? 'N/A',
                        'departure_date' => $request['@attributes']['DepartureDate'] ?? 'N/A',
                    ];
                }
            }
        }

        // Extract meals
        $mealsByTraveler = [];
        $mealRequests = $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['TravelerInfo']['SpecialReqDetails']['MealRequests']['MealRequest'] ?? [];
        if (!is_array($mealRequests) || !isset($mealRequests[0])) {
            $mealRequests = [$mealRequests];
        }
        foreach ($mealRequests as $request) {
            if (isset($request['@attributes'])) {
                $rph = $request['@attributes']['TravelerRefNumberRPHList'] ?? null;
                if ($rph) {
                    $mealsByTraveler[$rph][] = [
                        'meal_code' => $request['@attributes']['mealCode'] ?? 'N/A',
                        'meal_quantity' => $request['@attributes']['mealQuantity'] ?? 'N/A',
                        'flight_number' => $request['@attributes']['FlightNumber'] ?? 'N/A',
                        'departure_date' => $request['@attributes']['DepartureDate'] ?? 'N/A',
                    ];
                }
            }
        }

        // Extract tickets
        $ticketsByTraveler = [];
        foreach ($airTravelers as $traveler) {
            $rph = $traveler['TravelerRefNumber']['@attributes']['RPH'] ?? null;
            $ticketInfo = $traveler['ETicketInfo']['ETicketInfomation'] ?? [];
            if (!is_array($ticketInfo) || !isset($ticketInfo[0])) {
                $ticketInfo = [$ticketInfo];
            }
            foreach ($ticketInfo as $ticket) {
                if (isset($ticket['@attributes']) && $rph) {
                    $ticketsByTraveler[$rph][] = [
                        'e_ticket_no' => $ticket['@attributes']['eTicketNo'] ?? 'N/A',
                        'coupon_no' => $ticket['@attributes']['couponNo'] ?? 'N/A',
                        'type' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['Ticketing']['@attributes']['TicketType'] ?? 'E-Ticket',
                        'flight_segment' => $ticket['@attributes']['flightSegmentCode'] ?? 'N/A',
                        'status' => $ticket['@attributes']['status'] ?? 'N/A',
                        'used_status' => $ticket['@attributes']['usedStatus'] ?? 'N/A',
                    ];
                }
            }
        }

        // Combine all data into passengers array
        $passengers = [];
        foreach ($travelerRefs as $rph => $traveler) {
            $passengers[] = [
                'name' => $traveler['name'],
                'ref_no' => $traveler['ref_no'],
                'passenger_type' => $traveler['passenger_type'],
                'nationality' => $traveler['nationality'],
                'phone_number' => $traveler['phone_number'],
                'seats' => $seatsByTraveler[$rph] ?? [],
                'baggage' => $baggageByTraveler[$rph] ?? [],
                'meals' => $mealsByTraveler[$rph] ?? [],
                'tickets' => $ticketsByTraveler[$rph] ?? [],
            ];
        }

        return [
            'amount' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['Amount'] ?? null,
            'code' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['CurrencyCode'] ?? null,
            'transactionId' => $jsonResponse['Body']['OTA_AirBookRS']['@attributes']['TransactionIdentifier'] ?? null,
            'message' => $jsonResponse['Body']['OTA_AirBookRS']['AirReservation']['Ticketing']['TicketAdvisory'] ?? 'No message available',
            'passengers' => $passengers,
            'response' => $jsonResponse,
        ];
    }
    public function getCarrierName()
    {
        return 'flyJinnah';
    }
    private function getSoapHeaders($action)
    {
        // Action null tha :)
        $jsessionId = session('JSESSIONID', '');
        // dd($jsessionId);
        return [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => $action,
            'Cookie' => $jsessionId,
        ];
    }
}
