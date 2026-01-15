<?php

namespace App\Services;

use Carbon\Carbon;
use SimpleXMLElement;
use GuzzleHttp\Client;
use App\Services\HelperService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AirblueService
{
    protected $helperService;
    protected $config;
    protected $url;
    protected $clientId;
    protected $clientKey;
    protected $agentId;
    protected $agentPassword;
    protected $target;
    protected $logPath;
    protected $agentType    = "29";
    protected $version      = "1.04";
    protected $regenerateLogs = true;

    public function __construct(HelperService $helperService)
    {
        $this->helperService = $helperService;
        $this->config        = config('services.airblue');

        $logDir = storage_path('logs/airblue');
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);

        $this->logPath        = $logDir . '/' . now()->format('Y_m_d') . '.log';
        $this->logPathBooking = $logDir . '/bookings_' . now()->format('Y_m_d') . '.log';

        $this->url           = $this->config['url'];
        $this->target        = $this->config['service_target'];
        $this->clientId      = $this->config['client_id'];
        $this->clientKey     = $this->config['client_key'];
        $this->agentId       = $this->config['agent_id'];
        $this->agentPassword = $this->config['agent_password'];

        // Handle certificate and key paths (support both relative and absolute paths)
        $certPath = $this->config['cert'] ?? null;
        $keyPath = $this->config['ssl_key'] ?? null;

        // Convert relative paths to absolute paths if needed
        if ($certPath && !str_starts_with($certPath, '/') && !preg_match('/^[A-Z]:\\\\/', $certPath)) {
            // Relative path - check if it's relative to public or base
            if (str_starts_with($certPath, 'public/')) {
                $this->certPath = public_path(str_replace('public/', '', $certPath));
            } else {
                $this->certPath = base_path($certPath);
            }
        } else {
            $this->certPath = $certPath;
        }

        if ($keyPath && !str_starts_with($keyPath, '/') && !preg_match('/^[A-Z]:\\\\/', $keyPath)) {
            // Relative path - check if it's relative to public or base
            if (str_starts_with($keyPath, 'public/')) {
                $this->keyPath = public_path(str_replace('public/', '', $keyPath));
            } else {
                $this->keyPath = base_path($keyPath);
            }
        } else {
            $this->keyPath = $keyPath;
        }

        // Ensure certificate directory exists
        if ($this->certPath) {
            $certDir = dirname($this->certPath);
            if (!is_dir($certDir) && $certDir !== '.' && $certDir !== '') {
                mkdir($certDir, 0755, true);
            }
        }
        if ($this->keyPath) {
            $keyDir = dirname($this->keyPath);
            if (!is_dir($keyDir) && $keyDir !== '.' && $keyDir !== '') {
                mkdir($keyDir, 0755, true);
            }
        }

        if (!file_exists($this->certPath)) {
            throw new \Exception("Airblue cert not found: {$this->certPath}. Please ensure the certificate file exists at the specified path.");
        }
        if (!file_exists($this->keyPath)) {
            throw new \Exception("Airblue key not found: {$this->keyPath}. Please ensure the key file exists at the specified path.");
        }
    }

    /** Reuse your existing sendRequest logic */
    public function sendRequest($endpoint, $xmlBody, $isBooking = false)
    {
        // try {
            // dd($xmlBody);
            if ($this->regenerateLogs) {
                $formatted = $this->helperService->formatXml($xmlBody);
                file_put_contents($this->logPath, "{$endpoint} Request:\n{$formatted}\n\n\n", FILE_APPEND);
                if ($isBooking) {
                    file_put_contents($this->logPathBooking, "{$endpoint} Request:\n{$formatted}\n\n\n", FILE_APPEND);
                }
            }
            
            $response = Http::withOptions([
                    'cert'  => $this->certPath,
                    'ssl_key' => $this->keyPath,
                    'verify' => false,
                    'timeout' => 60,
                ])
                ->withHeaders([
                    'Content-Type' => 'text/xml; charset=utf-8'
                ])
                ->send('POST', $this->url . '', [
                    'body' => $xmlBody
                ]);

            if ($this->regenerateLogs) {
                $formatted = $this->helperService->formatXml((string)$response);
                file_put_contents($this->logPath, "{$endpoint} Response:\n{$formatted}\n\n\n\n\n\n", FILE_APPEND);
                if ($isBooking) {
                    file_put_contents($this->logPathBooking, "{$endpoint} Response:\n{$formatted}\n\n\n\n\n\n", FILE_APPEND);
                }
            }

            if (!$response || !$response->successful()) {
                $status = $response ? $response->status() : null;
                return ['error' => "Airblue request failed ({$endpoint})", 'status' => $status];
            }

            return $this->helperService->XMLtoJSON($response->body());
        // } catch (\Exception $e) {
        //     Log::error('Airblue API Error', ['error' => $e->getMessage()]);
        //     return ['error' => 'API Exception', 'message' => $e->getMessage()];
        // }
    }
    public function searchFlights($data)
    {
        // If multi-segment exists
        if (isset($data['segments']) && is_array($data['segments'])) {
            $segments = [];

            foreach ($data['segments'] as $seg) {
                $segments[] = [
                    'origin' => $seg['arr'],
                    'dest'   => $seg['dest'],
                    'date'   => $seg['dep']
                ];
            }
        } 
        else {
            // Fallback: single or round-trip
            $segments = [
                [
                    'origin' => $data['arr'],
                    'dest'   => $data['dest'],
                    'date'   => $data['dep']
                ]
            ];

            if (!empty($data['return'])) {
                $segments[] = [
                    'origin' => $data['dest'],
                    'dest'   => $data['arr'],
                    'date'   => $data['return']
                ];
            }
        }

        // Build OriginDestinationInformation nodes
        $originDest = '';
        $rph = 1;

        foreach ($segments as $seg) {
            $originDest .= $this->buildOriginDest(
                $seg['origin'],
                $seg['dest'],
                $seg['date'],
                $rph++
            );
        }

        // Pax XML
        $pax = '';
        if (!empty($data['adt']) && $data['adt'] > 0)
            $pax .= "<PassengerTypeQuantity Code=\"ADT\" Quantity=\"{$data['adt']}\"/>";

        if (!empty($data['chd']) && $data['chd'] > 0)
            $pax .= "<PassengerTypeQuantity Code=\"CHD\" Quantity=\"{$data['chd']}\"/>";

        if (!empty($data['inf']) && $data['inf'] > 0)
            $pax .= "<PassengerTypeQuantity Code=\"INF\" Quantity=\"{$data['inf']}\"/>";

        $echoToken = "-" . time() . rand(1000, 9999);

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
            <Header/>
            <Body>
                <AirLowFareSearch xmlns="http://zapways.com/air/ota/3.0">
                    <airLowFareSearchRQ xmlns="http://www.opentravel.org/OTA/2003/05"
                        EchoToken="{$echoToken}" Target="{$this->target}" Version="{$this->version}">
                        <POS>
                            <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                                <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                            </Source>
                        </POS>
                        {$originDest}
                        <TravelerInfoSummary>
                            <AirTravelerAvail>
                                {$pax}
                            </AirTravelerAvail>
                        </TravelerInfoSummary>
                    </airLowFareSearchRQ>
                </AirLowFareSearch>
            </Body>
        </Envelope>
        XML;

        // Debug: view XML
        // dd($xml);

        $raw = $this->sendRequest('AirLowFareSearch', $xml);

        if (isset($raw['error'])) {
            return $raw;
        }
        // dd($this->parseSearchResponse($raw));
        return $this->parseSearchResponse($raw);
    }
    public function bookFlight($data)
    {
        // dd($data);
        // Handle new flights array format (from multiple-flights and updated single/return flights)
        if (isset($data['data']['flights']) && is_array($data['data']['flights'])) {
            $selectedOptions = [];
            $selectedBundles = [];
            
            foreach ($data['data']['flights'] as $flightData) {
                $departure = $flightData['departure'] ?? null;
                $bundle = $flightData['bundle'] ?? null;
                
                if (!$departure || !$bundle) {
                    continue;
                }
                
                // Get flightRaw first
                $flightRaw = $departure['flightRaw'] ?? null;
                
                // Priority: Use segments_raw from flightRaw (raw XML format with @attributes)
                $segmentsRaw = [];
                if ($flightRaw && isset($flightRaw['segments_raw']) && is_array($flightRaw['segments_raw'])) {
                    $segmentsRaw = $flightRaw['segments_raw'];
                } 
                // Fallback: Check if departure has segments_raw (but might be parsed format)
                elseif (isset($departure['segments_raw']) && is_array($departure['segments_raw'])) {
                    // Check if it's raw format (has @attributes) or parsed format
                    $firstSeg = $departure['segments_raw'][0] ?? null;
                    if ($firstSeg && isset($firstSeg['@attributes'])) {
                        // It's raw format, use it
                        $segmentsRaw = $departure['segments_raw'];
                    } elseif ($flightRaw && isset($flightRaw['segments']) && is_array($flightRaw['segments'])) {
                        // Try to get from flightRaw segments
                        foreach ($flightRaw['segments'] as $seg) {
                            if (isset($seg['flightRaw'])) {
                                $segmentsRaw[] = $seg['flightRaw'];
                            }
                        }
                    }
                }
                // Last resort: Try to extract from parsed segments
                elseif (isset($departure['segments']) && is_array($departure['segments'])) {
                    foreach ($departure['segments'] as $seg) {
                        if (isset($seg['flightRaw'])) {
                            $segmentsRaw[] = $seg['flightRaw'];
                        }
                    }
                }
                
                // Build option structure expected by buildAirItineraryXml
                $selectedOptions[] = [
                    'segments_raw' => $segmentsRaw,
                    'flightRaw' => $departure['flightRaw'] ?? null
                ];
                
                $selectedBundles[] = $bundle;
            }
        } else {
            // Legacy format (backward compatibility)
            $selectedOptions = [$data['data']['departure']['flightRaw'] ?? null, $data['data']['return']['flightRaw'] ?? null];
            $selectedBundles = [$data['data']['firstBundle'] ?? null, $data['data']['returnBundle'] ?? null];
        }
        
        $passengers = $data['passengers'] ?? [];
        // Get pax types
        $paxTypes = [];
        foreach ($passengers as $p) {
            $code = $p['type'] ?? 'ADT';
            if (!isset($paxTypes[$code])) $paxTypes[$code] = 0;
            $paxTypes[$code]++;
        }
        // dd($paxTypes);
        $airItinXml = $this->buildAirItineraryXml($selectedOptions, $selectedBundles);
        $priceXml = $this->buildPriceInfoXml($selectedBundles, $paxTypes);
        // dd($priceXml);
        $travXml = $this->buildTravelerInfoXml($passengers, $data['user']);

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
        <Header/>
        <Body>
            <AirBook xmlns="http://zapways.com/air/ota/3.0">
            <airBookRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                <POS>
                <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                    <RequestorID Type="29" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                </Source>
                </POS>
                <AirItinerary>
                <OriginDestinationOptions>
                    {$airItinXml}
                </OriginDestinationOptions>
                </AirItinerary>
                <PriceInfo>
                    <PTC_FareBreakdowns>
                        {$priceXml}
                    </PTC_FareBreakdowns>
                </PriceInfo>
                <TravelerInfo>
                {$travXml}
                </TravelerInfo>
            </airBookRQ>
            </AirBook>
        </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('AirBook', $xml, true);

        // dd($this->parseAirBookResponse($raw));
        return $this->parseAirBookResponse($raw);
    }
    public function getSeat($data)
    {
        // dd($data);
        $legs = $data['legs'] ?? [];
        $bookingTag = $data['bookingTag'] ?? [];

        $seatMapRequestsXml = '';

        foreach ($legs as $legIndex => $leg) {
            $segmentsXml = '';
            foreach ($leg['segments'] as $segIndex => $seg) {
                $flightRaw = $seg['flightRaw'];
                $attrs = $flightRaw['@attributes'] ?? [];

                $rph = "{$legIndex}-{$segIndex}-{$attrs['FareType']}-0-0";

                $segmentsXml .= "<FlightSegmentInfo DepartureDateTime=\"{$attrs['DepartureDateTime']}\" ArrivalDateTime=\"{$attrs['ArrivalDateTime']}\" StopQuantity=\"{$attrs['StopQuantity']}\" RPH=\"{$rph}\" 
                    FlightNumber=\"{$attrs['FlightNumber']}\" FareType=\"{$attrs['FareType']}\" ResBookDesigCode=\"{$attrs['ResBookDesigCode']}\" CabinClass=\"{$attrs['CabinClass']}\" Status=\"{$attrs['Status']}\">
                        <DepartureAirport LocationCode=\"{$flightRaw['DepartureAirport']['@attributes']['LocationCode']}\" />
                        <ArrivalAirport LocationCode=\"{$flightRaw['ArrivalAirport']['@attributes']['LocationCode']}\" />
                        <OperatingAirline Code=\"{$flightRaw['OperatingAirline']['@attributes']['Code']}\" />
                        <Equipment AirEquipType=\"{$flightRaw['Equipment']['@attributes']['AirEquipType']}\" />
                        <MarketingAirline Code=\"{$flightRaw['MarketingAirline']['@attributes']['Code']}\" />
                </FlightSegmentInfo>";
            }

            $seatMapRequestsXml .= "<SeatMapRequest>{$segmentsXml}</SeatMapRequest>";
        }
        // dd($seatMapRequestsXml);
        $bookingXml = '';
        if (!empty($bookingTag)) {
            $instance = $bookingTag['instance'] ?? '';
            $id = $bookingTag['id'] ?? '';
            $bookingXml = "<BookingReferenceID Instance=\"{$instance}\" ID=\"{$id}\" />";
        }

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
            <Header/>
            <Body>
                <AirSeatMap xmlns="http://zapways.com/air/ota/3.0">
                    <airSeatMapRQ EchoToken="-1" Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                                <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                            </Source>
                        </POS>
                        <SeatMapRequests>
                            {$seatMapRequestsXml}
                        </SeatMapRequests>
                        {$bookingXml}
                    </airSeatMapRQ>
                </AirSeatMap>
            </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('GetSeat', $xml, false);

        // dd($this->parseSeatMapResponse($raw));
        return $this->parseSeatMapResponse($raw);
    }
    public function confirmSeats($data)
    {
        $seatXml = '';
        $booking = $data['bookingTag'];
        foreach ($data['seats'] as $flight) {
            $flightRph = $flight['rph']; // "1", "2", etc

            foreach ($flight['seats'] as $seat) {
                $travelerRph = htmlspecialchars($seat['traveler_no'], ENT_XML1);
                $seatNumber  = $seat['seat_number'];
                $rowNumber   = $seat['row_number'];

                $seatXml .= <<<XML
                <SeatRequest SeatNumber="{$seatNumber}" RowNumber="{$rowNumber}" TravelerRefNumberRPHList="{$travelerRph}" FlightRefNumberRPHList="{$flightRph}" />
                XML;
            }
        }
        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
            <Header/>
            <Body>
                <AirBookModify xmlns="http://zapways.com/air/ota/3.0">
                    <airBookModifyRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                                <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                            </Source>
                        </POS>

                        <AirBookModifyRQ ModificationType="5">
                            <TravelerInfo>
                                <SpecialReqDetails>
                                    <SeatRequests>
                                        {$seatXml}
                                    </SeatRequests>
                                </SpecialReqDetails>
                            </TravelerInfo>
                        </AirBookModifyRQ>

                        <AirReservation>
                            <BookingReferenceID Instance="{$booking['instance']}" ID="{$booking['id']}" />
                        </AirReservation>

                    </airBookModifyRQ>
                </AirBookModify>
            </Body>
        </Envelope>
        XML;
        $raw = $this->sendRequest('ConfirmSeats', $xml, true);
        return $this->parseConfirmSeatsResponse($raw);
    }
    public function getAncillaryItems($data)
    {
        // dd($data);
        $legs = $data['legs'] ?? [];
        $bookingTag = $data['bookingTag'] ?? [];

        $ancillaryRequestsXml = '';

        foreach ($legs as $legIndex => $leg) {
            $segmentsXml = '';
            foreach ($leg['segments'] as $segIndex => $seg) {
                $flightRaw = $seg['flightRaw'];
                $attrs = $flightRaw['@attributes'] ?? [];

                $rph = "{$legIndex}-{$segIndex}-{$attrs['FareType']}-0-0";

                $segmentsXml .= "<FlightSegmentInfo DepartureDateTime=\"{$attrs['DepartureDateTime']}\" ArrivalDateTime=\"{$attrs['ArrivalDateTime']}\" StopQuantity=\"{$attrs['StopQuantity']}\" RPH=\"{$rph}\" 
                    FlightNumber=\"{$attrs['FlightNumber']}\" FareType=\"{$attrs['FareType']}\" ResBookDesigCode=\"{$attrs['ResBookDesigCode']}\" CabinClass=\"{$attrs['CabinClass']}\" Status=\"{$attrs['Status']}\">
                        <DepartureAirport LocationCode=\"{$flightRaw['DepartureAirport']['@attributes']['LocationCode']}\" />
                        <ArrivalAirport LocationCode=\"{$flightRaw['ArrivalAirport']['@attributes']['LocationCode']}\" />
                        <OperatingAirline Code=\"{$flightRaw['OperatingAirline']['@attributes']['Code']}\" />
                        <Equipment AirEquipType=\"{$flightRaw['Equipment']['@attributes']['AirEquipType']}\" />
                        <MarketingAirline Code=\"{$flightRaw['MarketingAirline']['@attributes']['Code']}\" />
                </FlightSegmentInfo>";
            }

            $ancillaryRequestsXml .= "<AncillaryItemRequest>{$segmentsXml}</AncillaryItemRequest>";
        }
        // dd($ancillaryRequestsXml);
        $bookingXml = '';
        if (!empty($bookingTag)) {
            $instance = $bookingTag['instance'] ?? '';
            $id = $bookingTag['id'] ?? '';
            $bookingXml = "<BookingReferenceID Instance=\"{$instance}\" ID=\"{$id}\" />";
        }

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
        <Header/>
        <Body>
            <AirAncillaryItems xmlns="http://zapways.com/air/ota/3.0">
            <airAncillaryItemsRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                <POS>
                <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                    <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                </Source>
                </POS>
                <AncillaryItemRequests>
                    {$ancillaryRequestsXml}
                </AncillaryItemRequests>
                {$bookingXml}
            </airAncillaryItemsRQ>
            </AirAncillaryItems>
        </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('AncillaryItems', $xml, true);
        // dd($this->parseAncillaryItemsResponse($raw));

        return $this->parseAncillaryItemsResponse($raw);
    }
    public function confirmAncillaries(array $data)
    {
        $booking = $data['bookingTag'];
        $ssrXml  = '';

        foreach ($data['ancillaries'] as $anc) {

            $travelerRph = htmlspecialchars($anc['traveler_no'], ENT_XML1);
            $flightRph   = $anc['rph']; // IMPORTANT
            $itemCode    = $anc['code'];

            $ssrXml .= <<<XML
            <SpecialServiceRequest 
                ItemCode="{$itemCode}"
                TravelerRefNumberRPHList="{$travelerRph}"
                FlightRefNumberRPHList="{$flightRph}" />
            XML;
        }

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
        <Header/>
        <Body>
            <AirBookModify xmlns="http://zapways.com/air/ota/3.0">
            <airBookModifyRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                <POS>
                <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                    <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                </Source>
                </POS>

                <AirBookModifyRQ ModificationType="5">
                <TravelerInfo>
                    <SpecialReqDetails>
                    <SpecialServiceRequests>
                        {$ssrXml}
                    </SpecialServiceRequests>
                    </SpecialReqDetails>
                </TravelerInfo>
                </AirBookModifyRQ>

                <AirReservation>
                <BookingReferenceID Instance="{$booking['instance']}" ID="{$booking['id']}" />
                </AirReservation>
            </airBookModifyRQ>
            </AirBookModify>
        </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('ConfirmAncillaries', $xml, true);

        // dd($this->parseConfirmSeatsResponse($raw));
        return $this->parseConfirmSeatsResponse($raw);
    }
    public function orderChange(array $data)
    {
        $amount = $data['amount'] ?? 0;
        $code = $data['code'] ?? 'PKR';
        $orderId = $data['orderId'] ?? '';
        $ownerCode = $data['ownerCode'] ?? '';

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
        <Header/>
        <Body>
            <AirDemandTicket xmlns="http://zapways.com/air/ota/3.0">
            <airDemandTicketRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                <POS>
                <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                    <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                </Source>
                </POS>
                <DemandTicketDetail>
                    <BookingReferenceID Instance="{$ownerCode}" ID="{$orderId}"/>
                    <PaymentInfo PaymentType="Cash" CurrencyCode="{$code}" Amount="{$amount}"/>
                </DemandTicketDetail>
            </airDemandTicketRQ>
            </AirDemandTicket>
        </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('AirDemandTicket', $xml, true);
        
        return $this->parseAirDemandTicketResponse($raw);
    }
    public function doTicketPreview(array $data)
    {
        $orderId = $data['orderId'] ?? '';;

        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
        <Header/>
        <Body>
            <Read xmlns="http://zapways.com/air/ota/3.0">
            <readRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                <POS>
                <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                    <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}"/>
                </Source>
                </POS>
                <UniqueID ID="{$orderId}"/>
            </readRQ>
            </Read>
        </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('Read', $xml);
        $result = $raw['Body']['ReadResponse']['ReadResult'] ?? null;
        // dd($result);
        // dd($this->parseReadResponse($result));

        return $this->parseReadResponse($result);
    }
    public function orderCancel(array $data)
    {
        $orderId = $data['orderId'] ?? '';;
        $xml = <<<XML
        <Envelope xmlns="http://schemas.xmlsoap.org/soap/envelope/">
        <Header/>
            <Body>
                <AirBookModify xmlns="http://zapways.com/air/ota/3.0">
                    <airBookModifyRQ Target="{$this->target}" Version="1.04" xmlns="http://www.opentravel.org/OTA/2003/05">
                        <POS>
                            <Source ERSP_UserID="{$this->clientId}/{$this->clientKey}">
                                <RequestorID Type="{$this->agentType}" ID="{$this->agentId}" MessagePassword="{$this->agentPassword}" />
                            </Source>
                        </POS>
                        <AirBookModifyRQ ModificationType="1">
                        </AirBookModifyRQ>
                        <AirReservation>
                            <BookingReferenceID ID="{$orderId}"/>
                        </AirReservation>
                    </airBookModifyRQ>
                </AirBookModify>
            </Body>
        </Envelope>
        XML;

        $raw = $this->sendRequest('AirBookModify', $xml);
        // dd($raw);
        $result = $raw['Body']['AirBookModifyResponse']['AirBookModifyResult'] ?? null;
        // dd($result);
        // dd($this->parseReadResponse($result));

        return $this->parseReadResponse($result);
    }



    // ------------------------------------------------------------------ Format Responses ---------------------------------------------------------------------------------------------------
    protected function buildOriginDest($origin, $dest, $date, $rph)
    {
        $dt = Carbon::parse($date)->format('Y-m-d\T00:00:00');
        return <<<XML
        <OriginDestinationInformation RPH="{$rph}">
            <DepartureDateTime>{$dt}</DepartureDateTime>
            <OriginLocation LocationCode="{$origin}"/>
            <DestinationLocation LocationCode="{$dest}"/>
        </OriginDestinationInformation>
        XML;
    }
    protected function parseSearchResponse($response)
    {
        $legs = [];

        $pricedItineraries = $response['Body']['AirLowFareSearchResponse']['AirLowFareSearchResult']['PricedItineraries']['PricedItinerary'] ?? [];

        if (!isset($pricedItineraries[0])) {
            $pricedItineraries = [$pricedItineraries]; // make sure it's array
        }

        foreach ($pricedItineraries as $itinerary) {
            $odRef = $itinerary['@attributes']['OriginDestinationRefNumber'] ?? null;
            if (!$odRef) continue;

            // Get segments
            $option = $itinerary['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'];
            $segments = $option['FlightSegment'] ?? [];
            if (isset($segments['@attributes'])) {
                $segments = [$segments];
            }

            // Create unique key for this itinerary option (handles connections)
            $segKeys = [];
            foreach ($segments as $seg) {
                $attrs = $seg['@attributes'] ?? [];
                $segKeys[] = ($attrs['FlightNumber'] ?? '') . '-' . ($attrs['DepartureDateTime'] ?? '') . '-' . ($attrs['ArrivalDateTime'] ?? '');
            }
            $itineraryKey = implode('|', $segKeys);

            // Parse segments data
            if (!isset($legs[$odRef][$itineraryKey])) {
                $parsedSegments = $this->parseSegments($segments);

                // Calculate total duration, stops, origin, dest
                $firstDep = $segments[0]['@attributes']['DepartureDateTime'] ?? '';
                $lastArr = $segments[count($segments) - 1]['@attributes']['ArrivalDateTime'] ?? '';
                $duration = $this->calculateDuration($firstDep, $lastArr);

                $legs[$odRef][$itineraryKey] = [
                    'segments' => $parsedSegments,
                    'segments_raw' => $segments,
                    'stops' => count($segments) - 1,
                    'duration' => $duration,
                    'origin' => $segments[0]['DepartureAirport']['@attributes']['LocationCode'] ?? '',
                    'destination' => $segments[count($segments) - 1]['ArrivalAirport']['@attributes']['LocationCode'] ?? '',
                    'bundles' => [],
                    'cheapest_price' => PHP_FLOAT_MAX,
                    'best_bundle' => null,
                ];
            }

            // Extract bundle code (from first segment's RPH, e.g., "0-0-EF-0-0" -> EF)
            $firstSegAttrs = $segments[0]['@attributes'] ?? [];
            preg_match('/-(EF|EX|EV)-/', $firstSegAttrs['RPH'] ?? '', $m);
            $bundleCode = $m[1] ?? 'ST';

            $bundleNameMap = [
                'EV' => 'Economy Value',
                'EF' => 'Economy Standard',
                'EX' => 'Economy Extra',
            ];
            $bundleColorMap = [
                'EV' => '#e74c3c', // red
                'EF' => '#f39c12', // orange
                'EX' => '#27ae60', // green
            ];

            $pricingInfo = $itinerary['AirItineraryPricingInfo'];
            $totalPrice = (float)($pricingInfo['ItinTotalFare']['TotalFare']['@attributes']['Amount'] ?? 0);
            $basePrice  = (float)($pricingInfo['ItinTotalFare']['BaseFare']['@attributes']['Amount'] ?? 0);
            $taxes      = (float)($pricingInfo['ItinTotalFare']['Taxes']['@attributes']['Amount'] ?? 0);
            $fees       = (float)($pricingInfo['ItinTotalFare']['Fees']['@attributes']['Amount'] ?? 0);

            // Extract baggage + penalties
            $baggage = $this->extractBaggageAndRules($pricingInfo);

            $bundle = [
                'bundle_code'       => $bundleCode,
                'bundle_name'       => $bundleNameMap[$bundleCode] ?? 'Standard',
                'color'             => $bundleColorMap[$bundleCode] ?? '#95a5a6',
                'fare_basis'        => $baggage['fare_basis'] ?? ($firstSegAttrs['FareBasisCode'] ?? ''),
                'res_book_desig'    => $firstSegAttrs['ResBookDesigCode'] ?? '',
                'total_price'       => $totalPrice,
                'base_price'        => $basePrice,
                'taxes'             => $taxes,
                'fees'              => $fees,
                'baggage'           => $baggage['baggage'], // e.g. "30 KGS"
                'baggage_raw'       => $baggage['baggage_raw'] ?? [],
                'change_penalty'    => $baggage['change_penalty'] ?? [],
                'refund_penalty'    => $baggage['refund_penalty'] ?? [],
                'rph'               => $option['@attributes']['RPH'] ?? '', // important for booking!
                'pricing_raw'       => $pricingInfo,
            ];

            // Add bundle
            $legs[$odRef][$itineraryKey]['bundles'][] = $bundle;

            // Update cheapest
            if ($totalPrice < $legs[$odRef][$itineraryKey]['cheapest_price']) {
                $legs[$odRef][$itineraryKey]['cheapest_price'] = $totalPrice;
                $legs[$odRef][$itineraryKey]['best_bundle'] = $bundleCode;
            }
        }

        // Sort bundles in each option by price
        foreach ($legs as &$legOptions) {
            foreach ($legOptions as &$option) {
                usort($option['bundles'], fn($a, $b) => $a['total_price'] <=> $b['total_price']);
            }
            $legOptions = array_values($legOptions);
        }

        return [
            'success' => true,
            'legs' => $legs, // Keyed by leg number (1,2,3...)
            'raw'     => $response
        ];
    }
    protected function parseAirBookResponse($response)
    {
        $body = $response['Body']['AirBookResponse']['AirBookResult'] ?? [];
        
        if (!$body) {
            return [
                'error'   => 'Error fetching booking response',
                'details' => 'Invalid AirBook response from Airblue',
            ];
        }

        if (!empty($body['Errors'])) {

            $error = $body['Errors']['Error'] ?? 'Unknown booking error';

            if (is_array($error)) {
                $error = implode(' | ', $error);
            }
            return [
                'error'   => 'Error fetching booking response',
                'details' => $error,
            ];
        }
        
        $reservation = $body['AirReservation'] ?? [];

        $legs = [];
        $bundles = [];
        $passengers = [];
        $tickets = [];
        $bookingRefs = [];

        // Handle flights
        $originDestOptions = $reservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? [];
        if (isset($originDestOptions['@attributes'])) {
            $originDestOptions = [$originDestOptions];
        }

        foreach ($originDestOptions as $option) {
            $segments = $option['FlightSegment'] ?? [];
            if (isset($segments['@attributes'])) {
                $segments = [$segments];
            }

            $parsedSegments = [];
            foreach ($segments as $seg) {
                $attrs = $seg['@attributes'] ?? [];
                $parsedSegments[] = [
                    'flight_number' => $attrs['FlightNumber'] ?? null,
                    'fare_type' => $attrs['FareType'] ?? null,
                    'cabin_class' => $attrs['CabinClass'] ?? null,
                    'status' => $attrs['Status'] ?? null,
                    'departure' => [
                        'airport' => $seg['DepartureAirport']['@attributes']['LocationCode'] ?? null,
                        'terminal' => $seg['DepartureAirport']['@attributes']['Terminal'] ?? null,
                        'datetime' => $attrs['DepartureDateTime'] ?? null,
                    ],
                    'arrival' => [
                        'airport' => $seg['ArrivalAirport']['@attributes']['LocationCode'] ?? null,
                        'terminal' => $seg['ArrivalAirport']['@attributes']['Terminal'] ?? null,
                        'datetime' => $attrs['ArrivalDateTime'] ?? null,
                    ],
                    'airline' => [
                        'operating' => $seg['OperatingAirline']['@attributes']['Code'] ?? null,
                        'marketing' => $seg['MarketingAirline']['@attributes']['Code'] ?? null,
                    ],
                    'equipment' => $seg['Equipment']['@attributes']['AirEquipType'] ?? null,
                    'res_book_designator' => $attrs['ResBookDesigCode'] ?? null,
                    'rph' => $attrs['RPH'] ?? null,
                    'stop_quantity' => (int)($attrs['StopQuantity'] ?? 0),
                    'flightRaw' => $seg,
                ];
            }

            $legs[] = [
                'rph' => $option['@attributes']['RPH'] ?? null,
                'segments' => $parsedSegments,
                'stops' => count($parsedSegments) - 1,
            ];
        }

        // Handle passengers
        $airTravelers = $reservation['TravelerInfo']['AirTraveler'] ?? [];
        if (isset($airTravelers['@attributes'])) {
            $airTravelers = [$airTravelers];
        }

        foreach ($airTravelers as $traveler) {
            $passengers[] = [
                'rph' => $traveler['TravelerRefNumber']['@attributes']['RPH'] ?? null,
                'type' => $traveler['@attributes']['PassengerTypeCode'] ?? 'ADT',
                'birth_date' => $traveler['@attributes']['BirthDate'] ?? null,
                'name' => [
                    'title' => $traveler['PersonName']['NameTitle'] ?? null,
                    'first' => $traveler['PersonName']['GivenName'] ?? null,
                    'last' => $traveler['PersonName']['Surname'] ?? null,
                ],
                'contact' => [
                    'phone' => $traveler['Telephone']['@attributes']['PhoneNumber'] ?? null,
                    'country_code' => $traveler['Telephone']['@attributes']['CountryAccessCode'] ?? null,
                    'email' => $traveler['Email'] ?? null,
                ],
                'segment_rphs' => $traveler['FlightSegmentRPHs']['FlightSegmentRPH'] ?? [],
            ];
        }

        // Handle pricing
        $ptcFareBreakdowns = $reservation['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'] ?? [];
        if (isset($ptcFareBreakdowns['PassengerFare'])) {
            $ptcFareBreakdowns = [$ptcFareBreakdowns];
        }

        foreach ($ptcFareBreakdowns as $fare) {
            $passengerRefs = $fare['TravelerRefNumber'] ?? [];
            if (isset($passengerRefs['@attributes'])) {
                $passengerRefs = [$passengerRefs];
            }

            $fareInfo = $fare['PassengerFare'] ?? [];
            $baseFare = $fareInfo['BaseFare']['@attributes']['Amount'] ?? 0;
            $currency = $fareInfo['BaseFare']['@attributes']['CurrencyCode'] ?? 'PKR';
            $taxes = $fareInfo['Taxes']['@attributes']['Amount'] ?? 0;
            $fees = $fareInfo['Fees']['@attributes']['Amount'] ?? 0;

            $bundles[] = [
                'passenger_rphs' => array_map(fn($p) => $p['@attributes']['RPH'] ?? null, $passengerRefs),
                'base_fare' => (float)$baseFare,
                'taxes' => (float)$taxes,
                'fees' => (float)$fees,
                'currency' => $currency,
            ];
        }

        // Handle tickets
        $ticketing = $reservation['Ticketing'] ?? [];
        if (isset($ticketing['@attributes'])) {
            $ticketing = [$ticketing];
        }
        foreach ($ticketing as $t) {
            $tickets[] = [
                'traveler_rph' => $t['@attributes']['TravelerRefNumber'] ?? null,
                'segment_rph' => $t['@attributes']['FlightSegmentRefNumber'] ?? null,
                'status' => $t['@attributes']['TicketingStatus'] ?? null,
                'vendor' => $t['TicketingVendor']['@attributes']['Code'] ?? null,
                'time_limit' => $t['@attributes']['TicketTimeLimit'] ?? null,
            ];
        }

        // Booking references
        $bookingRefsRaw = $reservation['BookingReferenceID'] ?? [];
        if (isset($bookingRefsRaw['@attributes'])) {
            $bookingRefsRaw = [$bookingRefsRaw];
        }
        foreach ($bookingRefsRaw as $br) {
            $bookingRefs[] = [
                'id' => $br['@attributes']['ID'] ?? null,
                'instance' => $br['@attributes']['Instance'] ?? null,
                'type' => $br['@attributes']['Type'] ?? null,
            ];
        }

        return [
            'success' => true,
            'legs' => $legs,
            'passengers' => $passengers,
            'bundles' => $bundles,
            'tickets' => $tickets,
            'booking_refs' => $bookingRefs,
            'raw' => $response,
        ];
    }
    protected function parseSeatMapResponse($response)
    {
        $result = $response['Body']['AirSeatMapResponse']['AirSeatMapResult'] ?? null;

        if (!$result || !isset($result['SeatMapResponses']['SeatMapResponse'])) {
            return [
                'success' => false,
                'message' => 'Invalid seat map response',
                'raw'     => $response,
            ];
        }

        $seatMapResponses = $result['SeatMapResponses']['SeatMapResponse'] ?? [];

        // Handle single vs multiple flight segments
        if (!isset($seatMapResponses[0])) {
            $seatMapResponses = [$seatMapResponses];
        }

        $flights = [];

        foreach ($seatMapResponses as $responseItem) {
            $segmentInfo = $responseItem['FlightSegmentInfo'] ?? [];
            $attrs = $segmentInfo['@attributes'] ?? [];

            $flightKey = $attrs['RPH'] ?? '1'; // Usually "1", "2" for return

            $flight = [
                'rph'                 => $attrs['RPH'] ?? '',
                'flight_number'       => $attrs['FlightNumber'] ?? '',
                'departure_datetime'  => $attrs['DepartureDateTime'] ?? '',
                'arrival_datetime'    => $attrs['ArrivalDateTime'] ?? '',
                'departure_airport'   => $segmentInfo['DepartureAirport']['@attributes']['LocationCode'] ?? '',
                'arrival_airport'     => $segmentInfo['ArrivalAirport']['@attributes']['LocationCode'] ?? '',
                'aircraft'            => $segmentInfo['Equipment']['@attributes']['AirEquipType'] ?? '',
                'cabin_class'         => $attrs['CabinClass'] ?? 'Y',
                'fare_type'           => $attrs['FareType'] ?? '',
                'res_book_desig'      => $attrs['ResBookDesigCode'] ?? '',
                'rows'                => [], // Will be filled below
            ];

            $cabinClasses = $responseItem['SeatMapDetails']['CabinClass'] ?? [];
            if (!isset($cabinClasses[0])) {
                $cabinClasses = [$cabinClasses];
            }

            foreach ($cabinClasses as $cabin) {
                $rowInfos = $cabin['RowInfo'] ?? [];
                if (!isset($rowInfos[0])) {
                    $rowInfos = [$rowInfos];
                }

                foreach ($rowInfos as $row) {
                    $rowNumber = $row['@attributes']['RowNumber'] ?? '';

                    $seats = $row['SeatInfo'] ?? [];
                    if (!isset($seats[0])) {
                        $seats = [$seats];
                    }

                    $rowSeats = [];

                    foreach ($seats as $seat) {
                        $summary = $seat['Summary']['@attributes'] ?? [];
                        $seatNumber = $summary['SeatNumber'] ?? ' ';

                        // Skip empty aisle gaps if seat number is blank
                        if ($seatNumber === ' ') {
                            $rowSeats[] = [
                                'seat_number' => null,
                                'type'        => 'gap',
                            ];
                            continue;
                        }

                        $availability = $seat['Availability'] ?? 'SeatAvailable';
                        $available = $availability === 'SeatAvailable';
                        $occupied = $availability === 'SeatOccupied';

                        // Extract price
                        $price = 0;
                        $currency = 'PKR';
                        if (isset($seat['Service']['Fee']['@attributes'])) {
                            $fee = $seat['Service']['Fee']['@attributes'];
                            $price = (float)($fee['Amount'] ?? 0);
                            $currency = $fee['CurrencyCode'] ?? 'PKR';
                        }

                        // Extract features
                        $features = [];
                        if (isset($seat['Features'])) {
                            $featList = $seat['Features'];
                            if (!isset($featList[0])) {
                                $featList = [$featList];
                            }
                            foreach ($featList as $f) {
                                if (is_string($f)) {
                                    $features[] = $f;
                                } elseif (isset($f['_text'])) {
                                    $features[] = $f['_text'];
                                } elseif (isset($f['@attributes']['extension'])) {
                                    $features[] = $f['@attributes']['extension'];
                                }
                            }
                        }

                        $rowSeats[] = [
                            'seat_number'  => $seatNumber,
                            'available'    => $available,
                            'occupied'     => $occupied,
                            'blocked'      => !$available && !$occupied, // e.g. NoSeatHere or BlockedSeat_Permanent
                            'price'        => $price,
                            'currency'     => $currency,
                            'features'     => $features,
                            'is_window'    => in_array('Window', $features),
                            'is_aisle'     => in_array('Aisle', $features),
                            'is_exit_row'  => in_array('ExitRow', $features),
                            'is_overwing'  => in_array('Overwing', $features),
                            'bulkhead'     => str_contains(implode(' ', $features), 'Bulkhead'),
                            'limited_recline' => in_array('Limited_comfort', $features),
                            'no_infant'    => str_contains(implode(' ', $features), 'Not_allowed_for_infants') ||
                                            str_contains(implode(' ', $features), 'Seat_not_suitable_for_child'),
                        ];
                    }

                    $flight['rows'][$rowNumber] = [
                        'row_number' => $rowNumber,
                        'seats'      => $rowSeats,
                    ];
                }
            }

            // Sort rows numerically
            uksort($flight['rows'], fn($a, $b) => (int)$a <=> (int)$b);

            $flights[$flightKey] = $flight;
        }

        return [
            'success' => true,
            'flights' => $flights, // keyed by RPH (usually 1, 2, ...)
            'raw'     => $response,
        ];
    }
    protected function parseConfirmSeatsResponse(array $response): array
    {
        $result = $response['Body']['AirBookModifyResponse']['AirBookModifyResult'];
        
        if (!$result) {
            return [
                'error'   => 'Error confirming ancillaries',
                'details' => 'Invalid AirBook response from Airblue',
            ];
        }

        if (!empty($result['Errors'])) {
            $error = $result['Errors']['Error'] ?? 'Unknown booking error';
            if (is_array($error)) {
                $error = implode(' | ', $error);
            }
            return [
                'error'   => 'Error confirming ancillaries',
                'details' => $error,
            ];
        }
        $reservation = $result['AirReservation'];

        // ---------------- BOOKING REFERENCE ----------------
        $bookingNodes = $this->asArray($reservation['BookingReferenceID'] ?? []);
        $primaryBooking = $bookingNodes[0]['@attributes'] ?? [];
        $booking = [
            'pnr'      => $primaryBooking['ID'] ?? null,
            'instance' => $primaryBooking['Instance'] ?? null,
        ];

        // ---------------- FLIGHTS ----------------
        $flights = [];
        $segments = $this->asArray($reservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? []);
        foreach ($segments as $seg) {
            $fs = $seg['FlightSegment'];
            $attr = $fs['@attributes'];
            $flights[] = [
                'rph'                  => $attr['RPH'],
                'flight_number'        => $attr['FlightNumber'],
                'fare_type'            => $attr['FareType'],
                'res_book_desig_code'  => $attr['ResBookDesigCode'] ?? null,
                'cabin'                => $attr['CabinClass'],
                'status'               => $attr['Status'],
                'departure_datetime'   => $attr['DepartureDateTime'],
                'arrival_datetime'     => $attr['ArrivalDateTime'],
                'departure_airport'    => $fs['DepartureAirport']['@attributes']['LocationCode'],
                'departure_terminal'   => $fs['DepartureAirport']['@attributes']['Terminal'] ?? '',
                'arrival_airport'      => $fs['ArrivalAirport']['@attributes']['LocationCode'],
                'arrival_terminal'     => $fs['ArrivalAirport']['@attributes']['Terminal'] ?? '',
                'operating_airline'    => $fs['OperatingAirline']['@attributes']['Code'] ?? null,
                'marketing_airline'    => $fs['MarketingAirline']['@attributes']['Code'] ?? null,
                'equipment'            => $fs['Equipment']['@attributes']['AirEquipType'] ?? null,
            ];
        }

        // ---------------- TRAVELERS ----------------
        $travelers = [];
        $travelerNodes = $this->asArray($reservation['TravelerInfo']['AirTraveler'] ?? []);
        foreach ($travelerNodes as $traveler) {
            $attr = $traveler['@attributes'];
            $name = $traveler['PersonName'];
            $doc = $traveler['Document']['@attributes'] ?? [];

            $travelers[] = [
                'rph'               => $traveler['TravelerRefNumber']['@attributes']['RPH'],
                'type'              => $attr['PassengerTypeCode'],
                'title'             => $name['NameTitle'] ?? null,
                'first_name'        => $name['GivenName'] ?? null,
                'last_name'         => $name['Surname'] ?? null,
                'full_name'         => trim(($name['NameTitle'] ?? '') . ' ' . ($name['GivenName'] ?? '') . ' ' . ($name['Surname'] ?? '')),
                'birth_date'        => $attr['BirthDate'] ?? null,
                'phone'             => $traveler['Telephone']['@attributes']['PhoneNumber'] ?? null,
                'email'             => $traveler['Email'] ?? null,
                'document'          => $doc ? [
                    'id'                => $doc['DocID'] ?? null,
                    'type'              => $doc['DocType'] ?? null,
                    'birth_date'        => $doc['BirthDate'] ?? null,
                    'expire_date'       => $doc['ExpireDate'] ?? null,
                    'issue_country'     => $doc['DocIssueCountry'] ?? null,
                    'nationality'       => $doc['DocHolderNationality'] ?? null,
                ] : null,
                'segments'          => $this->asArray($traveler['FlightSegmentRPHs']['FlightSegmentRPH'] ?? []),
            ];
        }

        // ---------------- SEATS ----------------
        $seats = [];
        // ---------------- ANCILLARIES (SSR) ----------------
        $ancillaries = [];

        $specialReqBlocks = $this->asArray($reservation['TravelerInfo']['SpecialReqDetails'] ?? []);
        foreach ($specialReqBlocks as $block) {
            // Seats
            if (isset($block['SeatRequests']['SeatRequest'])) {
                $seatRequests = $this->asArray($block['SeatRequests']['SeatRequest']);
                foreach ($seatRequests as $seat) {
                    $attr = $seat['@attributes'];
                    $seats[] = [
                        'flight_rph'         => $attr['FlightRefNumberRPHList'],
                        'traveler_rph'       => $attr['TravelerRefNumberRPHList'],
                        'seat_number'        => $attr['SeatNumber'],
                        'row_number'         => $attr['RowNumber'],
                        'status'             => $attr['Status'],
                        'price'              => (int) ($seat['TPA_Extensions']['SeatCost'] ?? 0),
                        'currency'           => 'PKR',
                    ];
                }
            }

            // Ancillaries / SSRs
            if (isset($block['SpecialServiceRequests']['SpecialServiceRequest'])) {
                $ssrs = $this->asArray($block['SpecialServiceRequests']['SpecialServiceRequest']);
                foreach ($ssrs as $ssr) {
                    $attr = $ssr['@attributes'];
                    $ancillaries[] = [
                        'traveler_rph'       => $attr['TravelerRefNumberRPHList'] ?? null,
                        'flight_rph'         => $attr['FlightRefNumberRPHList'] ?? null,
                        'ssr_code'           => $attr['SSRCode'] ?? null,
                        'item_code'          => $attr['ItemCode'] ?? null,
                        'title'              => $attr['ItemTitle'] ?? null,
                        'description'        => $attr['Description'] ?? null,
                        'price'              => isset($attr['ChargeAmount']) ? (int)$attr['ChargeAmount'] : 0,
                        'currency'           => $attr['ChargeCurrency'] ?? 'PKR',
                        'status'             => $attr['Status'] ?? null,
                        'refundable'         => ($attr['CanRefund'] ?? 'false') === 'true',
                        'expires'            => $attr['Expires'] ?? null,
                    ];
                }
            }
        }

        // ---------------- TOTAL FARE ----------------
        $totalFareAttr = $reservation['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes'] ?? [];
        $total = [
            'amount'   => (int) ($totalFareAttr['Amount'] ?? 0),
            'currency' => $totalFareAttr['CurrencyCode'] ?? 'PKR',
        ];

        // ---------------- TICKETING TIME LIMIT ----------------
        $ticketingNodes = $this->asArray($reservation['Ticketing'] ?? []);
        $ticketTimeLimit = $ticketingNodes['@attributes']['TicketTimeLimit'] ?? $ticketingNodes[0]['@attributes']['TicketTimeLimit'] ?? null;

        // ---------------- PER-PASSENGER / PER-SEGMENT FARE DETAILS ----------------
        $priceBreakdown = [];
        $ptcBreakdowns = $this->asArray($reservation['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'] ?? []);

        foreach ($ptcBreakdowns as $ptc) {
            $qtyAttr = $ptc['PassengerTypeQuantity']['@attributes'];
            $travelerRphs = [];
            if (isset($ptc['TravelerRefNumber'])) {
                foreach ($this->asArray($ptc['TravelerRefNumber']) as $ref) {
                    $travelerRphs[] = $ref['@attributes']['RPH'];
                }
            }

            $perSegmentFares = [];
            $fareInfos = $this->asArray($ptc['FareInfo'] ?? []);

            foreach ($fareInfos as $fareInfo) {
                $passengerFare = $fareInfo['PassengerFare'];
                $base = $passengerFare['BaseFare']['@attributes'];
                $taxesTotal = $passengerFare['Taxes']['@attributes']['Amount'] ?? 0;
                $taxes = array_map(fn($t) => $t['@attributes'], $this->asArray($passengerFare['Taxes']['Tax'] ?? []));

                $feesTotal = $passengerFare['Fees']['@attributes']['Amount'] ?? 0;
                $fees = array_map(fn($f) => $f['@attributes'], $this->asArray($passengerFare['Fees']['Fee'] ?? []));

                $baggage = $passengerFare['FareBaggageAllowance']['@attributes'] ?? null;

                $perSegmentFares[] = [
                    'from'           => $fareInfo['DepartureAirport']['@attributes']['LocationCode'],
                    'to'             => $fareInfo['ArrivalAirport']['@attributes']['LocationCode'],
                    'fare_basis'     => $fareInfo['FareInfo']['@attributes']['FareBasisCode'] ?? null,
                    'base_fare'      => (int)$base['Amount'],
                    'taxes_total'    => (int)$taxesTotal,
                    'taxes'          => $taxes,
                    'fees_total'     => (int)$feesTotal,
                    'fees'           => $fees,
                    'baggage'        => $baggage ? [
                        'quantity' => (int)$baggage['UnitOfMeasureQuantity'],
                        'unit'     => $baggage['UnitOfMeasure'],
                    ] : null,
                ];
            }

            $priceBreakdown[] = [
                'passenger_type'  => $qtyAttr['Code'],
                'quantity'        => (int)$qtyAttr['Quantity'],
                'traveler_rphs'   => $travelerRphs,
                'per_segment_fares' => $perSegmentFares,
            ];
        }

        return [
            'success'            => true,
            'booking'            => $booking,
            'flights'            => $flights,
            'travelers'          => $travelers,
            'seats'              => $seats,
            'ancillaries'        => $ancillaries,
            'total'              => $total,
            'ticket_time_limit'  => $ticketTimeLimit,
            'price_breakdown'    => $priceBreakdown,
            'raw'                => $reservation,
        ];
    }
    protected function parseAncillaryItemsResponse(array $response)
    {
        $result = $response['Body']['AirAncillaryItemsResponse']['AirAncillaryItemsResult'];

        $bookingNode = $result['BookingReferenceID']['@attributes'];

        $output = [
            'success' => true,
            'booking' => [
                'id'       => $bookingNode['ID'],
                'instance' => $bookingNode['Instance'],
            ],
            'flights' => [],
        ];
        $ancillaryLoop = isset($result['AncillaryItemResponses']['AncillaryItemResponse'][0]) ? $result['AncillaryItemResponses']['AncillaryItemResponse'] : [$result['AncillaryItemResponses']['AncillaryItemResponse']];
        foreach ($ancillaryLoop as $resp) {

            $fs = $resp['FlightSegmentInfo']['@attributes'];

            $flight = [
                'rph'           => $fs['RPH'],
                'flight_number' => $fs['FlightNumber'],
                'from'          => $resp['FlightSegmentInfo']['DepartureAirport']['@attributes']['LocationCode'],
                'to'            => $resp['FlightSegmentInfo']['ArrivalAirport']['@attributes']['LocationCode'],
                'departure'     => $fs['DepartureDateTime'],
                'arrival'       => $fs['ArrivalDateTime'],
                'ancillaries'   => [],
            ];

            foreach ($resp['AncillaryItemSets']['AncillaryItemSet'] as $set) {

                $group = [
                    'group_code'  => $set['@attributes']['GroupCode'],
                    'title'       => $set['@attributes']['GroupTitle'],
                    'description' => $set['@attributes']['GroupDescription'] ?? null,
                    'multiple'    => $set['@attributes']['MultipleChoice'] === 'true',
                    'items'       => [],
                ];

                foreach ($set['AncillaryItems']['AncillaryItem'] as $item) {
                    $attr = $item['@attributes'];

                    $group['items'][] = [
                        'code'        => $attr['ItemCode'],
                        'title'       => $attr['ItemTitle'],
                        'available'   => $attr['Available'] === 'true',
                        'price'       => isset($attr['ChargeAmount']) ? (int) $attr['ChargeAmount'] : 0,
                        'currency'    => $attr['ChargeCurrency'] ?? null,
                        'refundable'  => $attr['IsRefundable'] === 'true',
                        'description' => $attr['Description'] ?? null,
                    ];
                }

                $flight['ancillaries'][] = $group;
            }

            $output['flights'][] = $flight;
        }

        return $output;
    }
    protected function parseAirDemandTicketResponse(array $response): array
    {
        $result = $response['Body']['AirDemandTicketResponse']['AirDemandTicketResult'] ?? null;

        if (!$result) {
            return [
                'success' => false,
                'error'   => 'Error processing ticket demand',
                'details' => 'Invalid AirDemandTicket response from Airblue',
                'raw'     => $response,
            ];
        }

        // Check for errors
        if (!empty($result['Errors'])) {
            $error = $result['Errors']['Error'] ?? 'Unknown ticket demand error';
            if (is_array($error)) {
                $error = implode(' | ', $error);
            }
            return [
                'success' => false,
                'error'   => 'Error processing ticket demand',
                'details' => $error,
                'raw'     => $response,
            ];
        }

        // Check for Success node (empty node indicates success)
        $success = isset($result['Success']);

        // Extract ticket items
        $tickets = [];
        $ticketItems = $this->asArray($result['TicketItemInfo'] ?? []);

        foreach ($ticketItems as $ticketItem) {
            $attrs = $ticketItem['@attributes'] ?? [];
            $passengerName = $ticketItem['PassengerName'] ?? [];
            $passengerAttrs = $passengerName['@attributes'] ?? [];

            $givenName = $passengerName['GivenName'] ?? null;
            $surname = $passengerName['Surname'] ?? null;
            $title = $passengerName['NameTitle'] ?? null;

            $tickets[] = [
                'ticket_number'       => $attrs['TicketNumber'] ?? null,
                'passenger_type_code' => $passengerAttrs['PassengerTypeCode'] ?? null,
                'passenger'           => [
                    'first_name' => $givenName,
                    'last_name'  => $surname,
                    'title'      => $title,
                    'full_name'  => trim(($title ?? '') . ' ' . ($givenName ?? '') . ' ' . ($surname ?? '')),
                ],
                'raw' => $ticketItem,
            ];
        }

        return [
            'success' => $success,
            'tickets' => $tickets,
            'raw'     => $response,
        ];
    }
    protected function parseReadResponse(array $response)
    {
        $result = $response;

        if (!$result || !isset($result['Success'])) {
            return [
                'success' => false,
                'warnings'   => 'Already Cancelled',
                'error'   => 'Invalid or failed Read response',
                'raw'     => $response
            ];
        }

        $airReservation = $result['AirReservation'] ?? [];

        // Booking references
        $bookingRefs = $airReservation['BookingReferenceID'] ?? [];
        if (!is_array($bookingRefs) || isset($bookingRefs['@attributes'])) {
            $bookingRefs = [$bookingRefs];
        }

        $primaryBooking = null;
        foreach ($bookingRefs as $ref) {
            $attrs = $ref['@attributes'] ?? [];
            if (!isset($attrs['Type'])) {
                $primaryBooking = $attrs;
                break;
            }
        }
        if (!$primaryBooking && !empty($bookingRefs)) {
            $primaryBooking = $bookingRefs[0]['@attributes'] ?? [];
        }

        $output = [
            'success'           => true,
            'booking'           => [
                'id'       => $primaryBooking['ID']       ?? null,
                'instance' => $primaryBooking['Instance'] ?? null,
            ],
            'ticket_time_limit' => null, // will take the earliest one
            'status'            => 'OK', // usually all are OK in Read
            'passengers'        => [],
            'itinerary'         => [],
            'total_fare'        => null,
            'fare_breakdown'    => [], // per passenger type
        ];

        // ────────────────────────────────────────────────
        // 1. Total Fare
        // ────────────────────────────────────────────────
        $totalNode = $airReservation['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes'] ?? null;
        if ($totalNode) {
            $output['total_fare'] = [
                'amount'   => (float) ($totalNode['Amount'] ?? 0),
                'code' => $totalNode['CurrencyCode'] ?? 'PKR',
            ];
        }

        // ────────────────────────────────────────────────
        // 2. Itinerary (multiple legs: outbound + return)
        // ────────────────────────────────────────────────
        $options = $airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption'] ?? [];
        if (!isset($options[0])) $options = $options ? [$options] : [];

        foreach ($options as $optIdx => $option) {
            $optAttr = $option['@attributes'] ?? [];
            $leg = [
                'leg_id'   => $optAttr['RPH'] ?? 'LEG' . ($optIdx + 1),
                'segments' => [],
            ];

            $segments = $option['FlightSegment'] ?? [];
            if (!isset($segments[0])) $segments = $segments ? [$segments] : [];

            foreach ($segments as $seg) {
                $attr = $seg['@attributes'] ?? [];
                $leg['segments'][] = [
                    'rph'              => $attr['RPH']              ?? null,
                    'flight_number'    => $attr['FlightNumber']     ?? null,
                    'departure'        => $attr['DepartureDateTime'] ?? null,
                    'arrival'          => $attr['ArrivalDateTime']   ?? null,
                    'from'             => $seg['DepartureAirport']['@attributes']['LocationCode'] ?? null,
                    'to'               => $seg['ArrivalAirport']['@attributes']['LocationCode']   ?? null,
                    'from_terminal'    => $seg['DepartureAirport']['@attributes']['Terminal']     ?? null,
                    'to_terminal'      => $seg['ArrivalAirport']['@attributes']['Terminal']       ?? null,
                    'operating_airline'=> $seg['OperatingAirline']['@attributes']['Code']         ?? null,
                    'marketing_airline'=> $seg['MarketingAirline']['@attributes']['Code']         ?? null,
                    'aircraft'         => $seg['Equipment']['@attributes']['AirEquipType']        ?? null,
                    'cabin'            => $attr['CabinClass']       ?? null,
                    'booking_class'    => $attr['ResBookDesigCode'] ?? null,
                    'fare_type'        => $attr['FareType']         ?? null,
                    'status'           => $attr['Status']           ?? null,
                    'stops'            => (int) ($attr['StopQuantity'] ?? 0),
                ];
            }

            $output['itinerary'][] = $leg;
        }

        // ────────────────────────────────────────────────
        // 3. Passengers
        // ────────────────────────────────────────────────
        $travelers = $airReservation['TravelerInfo']['AirTraveler'] ?? [];
        if (!isset($travelers[0])) $travelers = $travelers ? [$travelers] : [];

        $rphToPassenger = []; // map RPH → passenger index for later linking

        foreach ($travelers as $idx => $traveler) {
            $attr = $traveler['@attributes'] ?? [];
            $name = $traveler['PersonName'] ?? [];

            $passenger = [
                'type'       => $attr['PassengerTypeCode'] ?? 'ADT',
                'birth_date' => $attr['BirthDate']         ?? null,
                'title'      => $name['NameTitle']         ?? null,
                'first_name' => $name['GivenName']         ?? null,
                'last_name'  => $name['Surname']           ?? null,
                'phone'      => $this->extractPhone($traveler['Telephone'] ?? []),
                'email'      => $traveler['Email']         ?? null,
                'document'   => $this->extractDocument($traveler['Document'] ?? []),
                'rph'        => $traveler['TravelerRefNumber']['@attributes']['RPH'] ?? null,
                'segments'   => $traveler['FlightSegmentRPHs']['FlightSegmentRPH'] ?? [],
                'services'   => $this->extractServicesForTraveler($traveler['SpecialReqDetails'] ?? [], $output['itinerary']),
            ];

            if ($passenger['rph']) {
                $rphToPassenger[$passenger['rph']] = $idx;
            }

            $output['passengers'][] = $passenger;
        }

        // ────────────────────────────────────────────────
        // 4. Fare breakdown per PTC + collect earliest TTL
        // ────────────────────────────────────────────────
        $breakdowns = $airReservation['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'] ?? [];
        if (!isset($breakdowns[0])) $breakdowns = $breakdowns ? [$breakdowns] : [];

        $earliestTTL = null;

        foreach ($breakdowns as $bd) {
            $ptc = $bd['PassengerTypeQuantity']['@attributes'] ?? [];
            $type = $ptc['Code'] ?? 'ADT';
            $qty  = (int) ($ptc['Quantity'] ?? 1);

            $fare = $bd['PassengerFare'] ?? [];
            $base = $fare['BaseFare']['@attributes'] ?? [];
            $taxesNode = $fare['Taxes'] ?? [];
            $feesNode  = $fare['Fees']  ?? [];

            $entry = [
                'type'     => $type,
                'quantity' => $qty,
                'base'     => (float) ($base['Amount'] ?? 0),
                'taxes'    => (float) ($taxesNode['@attributes']['Amount'] ?? 0),
                'fees'     => (float) ($feesNode['@attributes']['Amount'] ?? 0),
                'total_per_pax' => 0, // calculated below
                'currency' => $base['CurrencyCode'] ?? 'PKR',
                'baggage_allowance' => $this->extractBaggage($fare['FareBaggageAllowance'] ?? []),
            ];

            $entry['total_per_pax'] = $entry['base'] + $entry['taxes'] + $entry['fees'];
            $output['fare_breakdown'][] = $entry;

            $ticketingData = $airReservation['Ticketing'] ?? [];

            if ($ticketingData) {
                // Normalize to array of ticketing entries
                $ticketingEntries = $this->asArray($ticketingData);

                foreach ($ticketingEntries as $tick) {
                    $attrs = $tick['@attributes'] ?? [];
                    $ttl   = $attrs['TicketTimeLimit'] ?? null;

                    if ($ttl && (!$earliestTTL || $ttl < $earliestTTL)) {
                        $earliestTTL = $ttl;
                    }
                }
            }
        }

        $output['ticket_time_limit'] = $earliestTTL;

        return $output;
    }

    // ------------------------------------------------------------------ Helper's helper ---------------------------------------------------------------------------------------------------

    protected function parseSegments($segments)
    {
        $parsed = [];
        foreach ($segments as $seg) {
            $attrs = $seg['@attributes'] ?? [];
            $parsed[] = [
                'flight_number'   => $attrs['FlightNumber'] ?? '',
                'airline'         => $seg['MarketingAirline']['@attributes']['Code'] ?? 'PA',
                'departure_time'  => substr($attrs['DepartureDateTime'] ?? '', 11, 5),
                'arrival_time'    => substr($attrs['ArrivalDateTime'] ?? '', 11, 5),
                'departure_date'  => substr($attrs['DepartureDateTime'] ?? '', 0, 10),
                'origin'          => $seg['DepartureAirport']['@attributes']['LocationCode'] ?? '',
                'destination'     => $seg['ArrivalAirport']['@attributes']['LocationCode'] ?? '',
                'equipment'       => $seg['Equipment']['@attributes']['AirEquipType'] ?? '320',
                'duration'        => $this->calculateDuration($attrs['DepartureDateTime'] ?? '', $attrs['ArrivalDateTime'] ?? ''),
                'cabin_class'     => $attrs['CabinClass'] ?? 'Y',
                'status'          => $attrs['Status'] ?? 'ONTIME',
                'stops'           => (int)($attrs['StopQuantity'] ?? 0),
            ];
        }
        return $parsed;
    }
    protected function buildAirItineraryXml($selectedOptions, $selectedBundles)
    {
        $xml = '';
        foreach ($selectedOptions as $legIndex => $option) {
            if (!$option || !isset($selectedBundles[$legIndex])) {
                continue;
            }

            $bundle = $selectedBundles[$legIndex];
            $bundleCode = $bundle['bundle_code'];           // e.g., "EX"
            $resBookDesig = $bundle['res_book_desig'];      // e.g., "X"

            $rphOption = $legIndex . '-0';
            $xml .= "<OriginDestinationOption RPH=\"{$rphOption}\">";

            $segIndex = 0;
            foreach ($option['segments_raw'] as $seg) {
                $attrs = $seg['@attributes'];

                $rphSegment = "{$rphOption}-{$bundleCode}-{$segIndex}-0";

                $xml .= "<FlightSegment DepartureDateTime=\"{$attrs['DepartureDateTime']}\" ArrivalDateTime=\"{$attrs['ArrivalDateTime']}\" StopQuantity=\"{$attrs['StopQuantity']}\" RPH=\"{$rphSegment}\" 
                    FlightNumber=\"{$attrs['FlightNumber']}\" FareType=\"{$bundleCode}\" ResBookDesigCode=\"{$resBookDesig}\" CabinClass=\"{$attrs['CabinClass']}\" Status=\"{$attrs['Status']}\">
                    <DepartureAirport LocationCode=\"{$seg['DepartureAirport']['@attributes']['LocationCode']}\" />
                    <ArrivalAirport LocationCode=\"{$seg['ArrivalAirport']['@attributes']['LocationCode']}\" />
                    <OperatingAirline Code=\"{$seg['OperatingAirline']['@attributes']['Code']}\" />
                    <Equipment AirEquipType=\"{$seg['Equipment']['@attributes']['AirEquipType']}\" />
                    <MarketingAirline Code=\"{$seg['MarketingAirline']['@attributes']['Code']}\" />
                </FlightSegment>";

                $segIndex++;
            }

            $xml .= "</OriginDestinationOption>";
        }

        return $xml;
    }
    protected function buildPriceInfoXml($selectedBundles, $paxTypes)
    {
        $ptcXml = '';

        // Loop through each selected bundle (each segment)
        foreach ($selectedBundles as $bundle) {
            $pricingRaw = $bundle['pricing_raw']['PTC_FareBreakdowns']['PTC_FareBreakdown'] ?? null;
            if (!$pricingRaw) {
                continue;
            }

            // Ensure $pricingRaw is always an array, even if single entry
            if (!is_numeric(key($pricingRaw))) {
                $pricingRaw = [$pricingRaw];
            }

            // Now loop through each PTC_FareBreakdown (one per passenger type)
            foreach ($pricingRaw as $ptc) {
                $ptq = $ptc['PassengerTypeQuantity']['@attributes'] ?? null;
                if (!$ptq) {
                    continue; // Skip if no PassengerTypeQuantity
                }
                $code = $ptq['Code'];      // e.g., ADT, CHD, INF
                $quantity = $ptq['Quantity'];

                // Build PassengerFare XML (reuse the original structure)
                $passengerFare = $ptc['PassengerFare'] ?? [];

                $baseAmt = $passengerFare['BaseFare']['@attributes']['Amount'] ?? '0';

                // Taxes
                $taxesAmount = $passengerFare['Taxes']['@attributes']['Amount'] ?? '0';
                $taxesXml = '<Taxes Amount="' . $taxesAmount . '">';
                $taxList = $passengerFare['Taxes']['Tax'] ?? [];
                if (isset($taxList['@attributes'])) {
                    $taxList = [$taxList];
                }
                foreach ($taxList as $tax) {
                    $attr = $tax['@attributes'];
                    $taxesXml .= '<Tax TaxCode="' . $attr['TaxCode'] . '" CurrencyCode="PKR" Amount="' . $attr['Amount'] . '" />';
                }
                $taxesXml .= '</Taxes>';

                // Fees (handle if missing, e.g., for INF)
                $feesAmount = $passengerFare['Fees']['@attributes']['Amount'] ?? '0';
                $feeList = $passengerFare['Fees']['Fee'] ?? [];
                if (isset($feeList['@attributes'])) {
                    $feeList = [$feeList];
                }
                $feesXml = '';
                if ($feesAmount > 0) {
                    $feesXml = '<Fees Amount="' . $feesAmount . '">';
                    foreach ($feeList as $fee) {
                        $attr = $fee['@attributes'];
                        $feesXml .= '<Fee FeeCode="' . $attr['FeeCode'] . '" CurrencyCode="PKR" Amount="' . $attr['Amount'] . '" />';
                    }
                    $feesXml .= '</Fees>';
                }

                $totalAmt = $passengerFare['TotalFare']['@attributes']['Amount'] ?? '0';

                $passengerFareXml = <<<PF
                <PassengerFare>
                    <BaseFare CurrencyCode="PKR" Amount="{$baseAmt}" />
                    {$taxesXml}
                    {$feesXml}
                    <TotalFare CurrencyCode="PKR" Amount="{$totalAmt}" />
                </PassengerFare>
                PF;

                // Build all FareInfo blocks (pricing + rules/baggage)
                $fareInfos = '';
                $fareInfoList = $ptc['FareInfo'] ?? [];
                // Ensure it's always an array (structure differs for INF)
                if (!is_numeric(key((array)$fareInfoList))) {
                    $fareInfoList = [$fareInfoList];
                }
                foreach ($fareInfoList as $fi) {
                    $fareInfos .= $this->buildSingleFareInfoXml($fi, $bundle);
                }

                // Now build the full PTC_FareBreakdown for this segment and passenger type
                $ptcXml .= <<<PTC
                <PTC_FareBreakdown>
                    <PassengerTypeQuantity Code="{$code}" Quantity="{$quantity}" />
                    {$passengerFareXml}
                    {$fareInfos}
                </PTC_FareBreakdown>
                PTC;
            }
        }

        return $ptcXml;
    }
    protected function buildSingleFareInfoXml($fareInfo, $bundle)
    {
        $xml = '';

        // First FareInfo: pricing details
        if (isset($fareInfo['PassengerFare']['BaseFare'])) {
            $pf = $fareInfo['PassengerFare'];

            $baseAmt = $pf['BaseFare']['@attributes']['Amount'] ?? null;
            $totalAmt = $pf['TotalFare']['@attributes']['Amount'] ?? null;

            $taxesAmount = $pf['Taxes']['@attributes']['Amount'] ?? null;
            $taxesXml = '';
            if ($taxesAmount !== null) {
                $taxesXml = '<Taxes Amount="' . $taxesAmount . '">';
                $taxList = $pf['Taxes']['Tax'] ?? [];
                if (isset($taxList['@attributes'])) $taxList = [$taxList];
                foreach ($taxList as $tax) {
                    $attr = $tax['@attributes'];
                    $taxesXml .= '<Tax TaxCode="' . $attr['TaxCode'] . '" CurrencyCode="PKR" Amount="' . $attr['Amount'] . '" />';
                }
                $taxesXml .= '</Taxes>';
            }

            $feesAmount = $pf['Fees']['@attributes']['Amount'] ?? null;
            $feesXml = '';
            if ($feesAmount !== null) {
                $feesXml = '<Fees Amount="' . $feesAmount . '">';
                $feeList = $pf['Fees']['Fee'] ?? [];
                if (isset($feeList['@attributes'])) $feeList = [$feeList];
                foreach ($feeList as $fee) {
                    $attr = $fee['@attributes'];
                    $feesXml .= '<Fee FeeCode="' . $attr['FeeCode'] . '" CurrencyCode="PKR" Amount="' . $attr['Amount'] . '" />';
                }
                $feesXml .= '</Fees>';
            }

            $depDate = $fareInfo['DepartureDate'] ?? '';
            $depApt = $fareInfo['DepartureAirport']['@attributes']['LocationCode'] ?? '';
            $arrApt = $fareInfo['ArrivalAirport']['@attributes']['LocationCode'] ?? '';
            $fareBasis = $fareInfo['FareInfo']['@attributes']['FareBasisCode'] ?? '';
            $fareType = $fareInfo['FareInfo']['@attributes']['FareType'] ?? '';

            $xml .= <<<FI1
            <FareInfo>
                <DepartureDate>{$depDate}</DepartureDate>
                <DepartureAirport LocationCode="{$depApt}" />
                <ArrivalAirport LocationCode="{$arrApt}" />
                <FareInfo FareBasisCode="{$fareBasis}" FareType="{$fareType}" />
                <PassengerFare>
                    <BaseFare CurrencyCode="PKR" Amount="{$baseAmt}" />
                    {$taxesXml}
                    {$feesXml}
                    <TotalFare CurrencyCode="PKR" Amount="{$totalAmt}" />
                </PassengerFare>
            </FareInfo>
            FI1;
        }

        // Second FareInfo: rules + baggage
        if (isset($fareInfo['RuleInfo']) || isset($fareInfo['PassengerFare']['FareBaggageAllowance'])) {
            $depDate = $fareInfo['DepartureDate'] ?? '';
            $depApt = $fareInfo['DepartureAirport']['@attributes']['LocationCode'] ?? '';
            $arrApt = $fareInfo['ArrivalAirport']['@attributes']['LocationCode'] ?? '';

            $rulesXml = '';
            if (isset($fareInfo['RuleInfo']['ChargesRules'])) {
                $ruleInfo = $fareInfo['RuleInfo']['ChargesRules'];
                $changesXml = '<VoluntaryChanges>';
                foreach ($ruleInfo['VoluntaryChanges']['Penalty'] ?? [] as $penalty) {
                    $attr = $penalty['@attributes'];
                    $changesXml .= '<Penalty HoursBeforeDeparture="' . htmlspecialchars($attr['HoursBeforeDeparture']) . '" CurrencyCode="PKR" Amount="' . $attr['Amount'] . '" />';
                }
                $changesXml .= '</VoluntaryChanges>';

                $refundsXml = '<VoluntaryRefunds>';
                foreach ($ruleInfo['VoluntaryRefunds']['Penalty'] ?? [] as $penalty) {
                    $attr = $penalty['@attributes'];
                    $refundsXml .= '<Penalty HoursBeforeDeparture="' . htmlspecialchars($attr['HoursBeforeDeparture']) . '" CurrencyCode="PKR" Amount="' . $attr['Amount'] . '" />';
                }
                $refundsXml .= '</VoluntaryRefunds>';

                $rulesXml = <<<RULES
                <RuleInfo>
                    <ChargesRules>
                        {$changesXml}
                        {$refundsXml}
                    </ChargesRules>
                </RuleInfo>
                RULES;
            }

            $baggageXml = '';
            if (isset($fareInfo['PassengerFare']['FareBaggageAllowance'])) {
                $bag = $fareInfo['PassengerFare']['FareBaggageAllowance']['@attributes'];
                $baggageXml = '<PassengerFare><FareBaggageAllowance UnitOfMeasureQuantity="' . $bag['UnitOfMeasureQuantity'] . '" UnitOfMeasure="' . $bag['UnitOfMeasure'] . '" /></PassengerFare>';
            }

            $xml .= <<<FI2
            <FareInfo>
                <DepartureDate>{$depDate}</DepartureDate>
                {$rulesXml}
                <DepartureAirport LocationCode="{$depApt}" />
                <ArrivalAirport LocationCode="{$arrApt}" />
                {$baggageXml}
            </FareInfo>
            FI2;
        }

        return $xml;
    }
    protected function buildTravelerInfoXml($passengers, $user)
    {
        $xml = '';
        
        $adultRphs = [];
        $infantIndex = 0;
        $adultDocuments = [];
        foreach ($passengers as $index => $p) {
            $birth = $p['dob'] ?? '1990-01-01';
            $given = $p['name'] ?? 'TEST';
            $surname = $p['surname'] ?? 'TESTER';
            $title = $p['title'] ?? 'Mr';
            $phone = $user['userPhone'] ?? '3001234567'; // USER's
            $countryCode = $user['userPhoneCode'] ?? '92'; // USER's
            $email = $user['userEmail'] ?? 'test@example.com'; // USER's
            $docTypeCode = $user['domestic'] ? 5 : 2; //  2 = Passport; 5 = National ID.
            $expire = $p['passportExpiry'] ?? '2030-01-01';
            $issueCountry = $user['countryCode'] ?? 'PK';
            $rawType = $p['type'] ?? 'ADT';
            if ($rawType === 'Adult') {
                $type = 'ADT';
            } elseif ($rawType === 'Child') {
                $type = 'CHD';
            } elseif ($rawType === 'Infant') {
                $type = 'INF';
            } else {
                $type = 'ADT';
            }
            
            if ($type === 'CHD') {
                $title = ($title === 'Mr') ? 'Mstr' : 'Miss';
            }

            if ($type === 'ADT') {
                $adultRphs[] = $index;
            }


            $identityDocXML = '';
            $titleTag = '';
            $associationXML = '';
    
            if ($type !== 'INF') {
                $passportNumber = htmlspecialchars($p['passportNumber'] ?? '');
    
                $identityDocXML = <<<XML
                    <Document DocID="{$passportNumber}" DocType="{$docTypeCode}" ExpireDate="{$expire}" DocIssueCountry="{$issueCountry}" DocHolderNationality="{$issueCountry}"/>
                XML;
    
                $titleTag = "<NameTitle>{$title}</NameTitle>";

                if ($type === 'ADT') {
                    $adultDocuments[$index] = $identityDocXML;
                }
            } 
            else {
                if (!empty($adultRphs)) {
                    $adultRph = $adultRphs[$infantIndex % count($adultRphs)];
                    $infantIndex++;
    
                    $associationXML = <<<XML
                        <TravelerAssociationRef RPH="{$adultRph}"/>
                    XML;
                    if (isset($adultDocuments[$adultRph])) {
                        $identityDocXML = $adultDocuments[$adultRph];
                    }
                }
            }

            // $loyalty = !empty($p['loyalty']) ? '<CustLoyalty MembershipID="' . $p['loyalty'] . '"/>' : '';
            // {$loyalty}

            $xml .= <<<TRAV
            <AirTraveler BirthDate="{$birth}">
                <PersonName>
                    <GivenName>{$given}</GivenName>
                    <Surname>{$surname}</Surname>
                    {$titleTag}
                </PersonName>
                <Telephone PhoneLocationType="10" CountryAccessCode="{$countryCode}" PhoneNumber="{$phone}"/>
                <Email>{$email}</Email>
                {$identityDocXML}
                {$associationXML}
                <PassengerTypeQuantity Code="{$type}" Quantity="1"/>
                <TravelerRefNumber RPH="{$index}"/>
            </AirTraveler>
            TRAV;
        }
        return $xml;
    }
    protected function extractBaggageAndRules($pricingInfo)
    {
        // dd($pricingInfo);
        $result = [
            'baggage'        => '0 KGS',
            'baggage_raw'    => null,
            'change_penalty' => [],
            'refund_penalty' => [],
            'fare_basis'     => ''
        ];

        $fareInfos = $pricingInfo['PTC_FareBreakdowns']['PTC_FareBreakdown']['FareInfo'] ?? $pricingInfo['PTC_FareBreakdowns']['PTC_FareBreakdown'][0]['FareInfo'] ?? [];

        // Normalize to array
        if (isset($fareInfos['@attributes'])) {
            $fareInfos = [$fareInfos];
        } elseif (!is_array($fareInfos)) {
            $fareInfos = [];
        }

        foreach ($fareInfos as $info) {
            // 1. Fare Basis Code (always in first FareInfo)
            if (empty($result['fare_basis'])) {
                $fb = $info['FareInfo']['@attributes']['FareBasisCode'] ?? '';
                if ($fb) {
                    $result['fare_basis'] = $fb;
                }
            }

            // 2. Baggage — only appears in the SECOND FareInfo block
            if (isset($info['PassengerFare']['FareBaggageAllowance']['@attributes'])) {
                $bag = $info['PassengerFare']['FareBaggageAllowance']['@attributes'];
                $qty = $bag['UnitOfMeasureQuantity'] ?? '0';
                $unit = $bag['UnitOfMeasure'] ?? 'KGS';
                $result['baggage'] = "{$qty} {$unit}";
                $result['baggage_raw'] = $bag;
            }

            // 3. Change & Refund Rules — only in the SECOND FareInfo with RuleInfo
            if (isset($info['RuleInfo']['ChargesRules'])) {
                $rules = $info['RuleInfo']['ChargesRules'];

                // Voluntary Changes
                if (isset($rules['VoluntaryChanges']['Penalty'])) {
                    $penalties = $rules['VoluntaryChanges']['Penalty'];
                    if (!isset($penalties[0])) $penalties = [$penalties];

                    foreach ($penalties as $p) {
                        $hrs = $p['@attributes']['HoursBeforeDeparture'] ?? '';
                        $amt = (float)($p['@attributes']['Amount'] ?? 0);

                        if (str_contains($hrs, '<0')) {
                            $label = 'No-show / After departure';
                        } elseif (str_contains($hrs, '<48')) {
                            $label = 'Less than 48 hours';
                        } else {
                            $label = 'More than 48 hours';
                        }

                        $result['change_penalty'][] = [
                            'label'  => $label,
                            'amount' => $amt
                        ];
                    }
                }

                // Voluntary Refunds
                if (isset($rules['VoluntaryRefunds']['Penalty'])) {
                    $penalties = $rules['VoluntaryRefunds']['Penalty'];
                    if (!isset($penalties[0])) $penalties = [$penalties];

                    foreach ($penalties as $p) {
                        $hrs = $p['@attributes']['HoursBeforeDeparture'] ?? '';
                        $amt = (float)($p['@attributes']['Amount'] ?? 0);

                        if (str_contains($hrs, '<0')) {
                            $label = 'No-show / After departure';
                        } elseif (str_contains($hrs, '<48')) {
                            $label = 'Less than 48 hours';
                        } else {
                            $label = 'More than 48 hours';
                        }

                        $result['refund_penalty'][] = [
                            'label'  => $label,
                            'amount' => $amt
                        ];
                    }
                }
            }
        }
        // dd($result, $pricingInfo);

        return $result;
    }
    protected function calculateDuration($dep, $arr)
    {
        $d = new \DateTime($dep);
        $a = new \DateTime($arr);
        $diff = $d->diff($a);
        return $diff->format('%hh %im');
    }
    protected function asArray($node)
    {
        if (!$node) {
            return [];
        }

        return isset($node[0]) ? $node : [$node];
    }

    // ────────────────────────────────────────────────
    // Helpers (updated / added)
    // ────────────────────────────────────────────────
    private function extractPhone($tel)
    {
        $attr = is_array($tel) && isset($tel['@attributes']) ? $tel['@attributes'] : $tel;
        return !empty($attr['PhoneNumber']) ? $attr : null;
    }

    private function extractDocument($doc)
    {
        $attr = is_array($doc) && isset($doc['@attributes']) ? $doc['@attributes'] : $doc;
        return !empty($attr['DocID']) ? $attr : null;
    }

    private function extractBaggage($bag)
    {
        $attr = is_array($bag) && isset($bag['@attributes']) ? $bag['@attributes'] : $bag;
        if (empty($attr)) return null;
        return [
            'quantity' => (int) ($attr['UnitOfMeasureQuantity'] ?? 0),
            'unit'     => $attr['UnitOfMeasure'] ?? 'KGS',
        ];
    }

    /**
     * Extract seats + SSRs — now per traveler (using TravelerRefNumberRPHList)
     */
    private function extractServicesForTraveler($specialReqDetails, array $itinerary)
    {
        $services = [
            'seats' => [],
            'ssr'   => [],
        ];

        if (empty($specialReqDetails)) return $services;

        $details = is_array($specialReqDetails) && isset($specialReqDetails[0])
            ? $specialReqDetails
            : [$specialReqDetails];

        foreach ($details as $block) {
            // Seats
            if (isset($block['SeatRequests']['SeatRequest'])) {
                $seats = $block['SeatRequests']['SeatRequest'];
                if (!isset($seats[0])) $seats = [$seats];

                foreach ($seats as $s) {
                    $attr = $s['@attributes'] ?? [];
                    $services['seats'][] = [
                        'row'      => $attr['RowNumber'] ?? null,
                        'seat'     => $attr['SeatNumber'] ?? null,
                        'status'   => $attr['Status'] ?? null,
                        'cost'     => (float) ($s['TPA_Extensions']['SeatCost'] ?? 0),
                        'traveler_rph' => $attr['TravelerRefNumberRPHList'] ?? null,
                        'flight_rph'   => $attr['FlightRefNumberRPHList'] ?? null,
                    ];
                }
            }

            // SSRs (extra bag, meal, etc.)
            if (isset($block['SpecialServiceRequests']['SpecialServiceRequest'])) {
                $ssrs = $block['SpecialServiceRequests']['SpecialServiceRequest'];
                if (!isset($ssrs[0])) $ssrs = [$ssrs];

                foreach ($ssrs as $s) {
                    $attr = $s['@attributes'] ?? [];
                    $services['ssr'][] = [
                        'ssr_code'     => $attr['SSRCode'] ?? null,
                        'item_code'    => $attr['ItemCode'] ?? null,
                        'title'        => $attr['ItemTitle'] ?? null,
                        'status'       => $attr['Status'] ?? null,
                        'amount'       => (float) ($attr['ChargeAmount'] ?? 0),
                        'currency'     => $attr['ChargeCurrency'] ?? 'PKR',
                        'expires'      => $attr['Expires'] ?? null,
                        'traveler_rph' => $attr['TravelerRefNumberRPHList'] ?? null,
                        'flight_rph'   => $attr['FlightRefNumberRPHList'] ?? null,
                    ];
                }
            }
        }

        return $services;
    }

    public function getCarrierName()
    {
        return 'airblue';
    }

    // You can now add AirBook, AirDemandTicket, Read, Cancel, etc. the same way
}