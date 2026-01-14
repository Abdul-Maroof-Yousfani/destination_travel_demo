<?php

namespace App\Services;

use Carbon\Carbon;
use SimpleXMLElement;
use Illuminate\Support\Str;
use App\Services\HelperService;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class EmiratesService
{
    protected $helperService;
    protected $randomId;

    protected $regenerateLogs;
    protected $logPath;
    protected $logPathBooking;
    protected $agencyName;
    protected $url;
    protected $user;
    protected $u;
    protected $passwordIden;
    protected $agtPassword;
    protected $agencyId;
    protected $agy;
    protected $subscriptionKey;
    protected $pcc;
    protected $role;

    public function __construct(HelperService $helperService)
    {
        $this->regenerateLogs = true;
        $logDir = storage_path('logs/emirates');
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $this->logPath = $logDir . '/' . now()->format('Y_m_d') . '.log';
        $this->logPathBooking = $logDir . '/bookings_' . now()->format('Y_m_d') . '.log';

        $this->helperService = $helperService;
        $this->randomId = Str::random(30);

        $this->agencyName = config('services.emirates_api.agency_name');
        $this->url = config('services.emirates_api.url');
        $this->user = config('services.emirates_api.user');
        $this->u = config('services.emirates_api.u');
        $this->passwordIden = config('services.emirates_api.passwordIden');
        $this->agtPassword = config('services.emirates_api.agtPassword');
        $this->agencyId = config('services.emirates_api.agency_id');
        $this->agy = config('services.emirates_api.agy');
        $this->subscriptionKey = config('services.emirates_api.subscription_key');
        $this->pcc = config('services.emirates_api.pcc');
        $this->role = config('services.emirates_api.role');
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
            $response = $this->helperService->postXml($this->url, $this->getSoapHeaders($endpoint), $xmlBody);
            if ($this->regenerateLogs) {
                $formattedResponse = $this->helperService->formatXml((string) $response);
                file_put_contents($this->logPath, "{$endpoint} Response:\n{$formattedResponse}\n\n\n\n\n\n", FILE_APPEND);
                if ($isBooking) {
                    file_put_contents($this->logPathBooking, "{$endpoint} Response:\n{$formattedResponse}\n\n\n\n\n\n", FILE_APPEND);
                }
            }

            if (!$response || !$response->successful()) {
                \Log::error('Flight booking request failed Emirate', [
                    'status' => $response?->status(),
                    'response' => $response?->body()
                ]);
                return ['error' => "Flight booking request failed Emirate ({$endpoint}).", 'details' => $response?->body()];
            }
            // dd($response->body());
            $parsed = $this->helperService->XMLtoJSONEmirate($response);

            return $parsed['SOAP-ENV:Body']['XXTransactionResponse']['RSP'] ?? $parsed;
        } catch (RequestException $e) {
            $response = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body';
            Log::error('Emirate API Request Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'response' => $response,
            ]);
            throw new \Exception('API request failed: ' . $e->getMessage());
        }
    }
    public function searchFlights($data) // AirShoppingRQ
    {
        $cabinClass = $data['cabinClass'] ?? 'Y';
        $routeType = $data['routeType'] ?? 'ONEWAY';
        if ($routeType === 'MULTI') {
            return ['error' => 'Multi-segment flights are not supported for Emirates.'];
        }
        
        session([
            'responseId' => null,
            'IdsExpireTimeEmi' => null,
        ]);
        
        $paxXml = $this->getPaxTag([
            'adt' => $data['adt'] ?? 0, 
            'chd' => $data['chd'] ?? 0, 
            'inf' => $data['inf'] ?? 0
        ]);
        
        // Handle new structure with segments array
        if (isset($data['segments']) && is_array($data['segments']) && !empty($data['segments'])) {
            $originDestinations = '';
            $cabinPreferences = '';
            $odKey = 1;
            
            foreach ($data['segments'] as $segment) {
                $origin = $segment['arr'] ?? '';
                $destination = $segment['dest'] ?? '';
                $departureDate = $segment['dep'] ?? '';
                
                if (empty($origin) || empty($destination) || empty($departureDate)) {
                    continue;
                }
                
                $originDestinations .= '
                <OriginDestination OriginDestinationKey="OD' . $odKey . '">
                    <Departure>
                        <AirportCode>' . $origin . '</AirportCode>
                        <Date>' . $departureDate . '</Date>
                    </Departure>
                    <Arrival>
                        <AirportCode>' . $destination . '</AirportCode>
                    </Arrival>
                </OriginDestination>';
                
                $cabinPreferences .= '
                <CabinType>
                    <Code>' . $cabinClass . '</Code>
                    <OriginDestinationReferences>OD' . $odKey . '</OriginDestinationReferences>
                </CabinType>';
                
                $odKey++;
            }
        } else {
            // Fallback to old structure for backward compatibility
            $origin = $data['arr'] ?? '';
            $destination = $data['dest'] ?? '';
            $departureDate = $data['dep'] ?? '';
            $returnDate = $data['return'] ?? null;
            
            $originDestinations = '
            <OriginDestination OriginDestinationKey="OD1">
                <Departure>
                    <AirportCode>' . $origin . '</AirportCode>
                    <Date>' . $departureDate . '</Date>
                </Departure>
                <Arrival>
                    <AirportCode>' . $destination . '</AirportCode>
                </Arrival>
            </OriginDestination>';
            
            $cabinPreferences = '
            <CabinType>
                <Code>' . $cabinClass . '</Code>
                <OriginDestinationReferences>OD1</OriginDestinationReferences>
            </CabinType>';
            
            if ($returnDate) {
                $originDestinations .= '
                <OriginDestination OriginDestinationKey="OD2">
                    <Departure>
                        <AirportCode>' . $destination . '</AirportCode>
                        <Date>' . $returnDate . '</Date>
                    </Departure>
                    <Arrival>
                        <AirportCode>' . $origin . '</AirportCode>
                    </Arrival>
                </OriginDestination>';
                $cabinPreferences .= '
                <CabinType>
                    <Code>' . $cabinClass . '</Code>
                    <OriginDestinationReferences>OD2</OriginDestinationReferences>
                </CabinType>';
            }
        }
        $body = '<AirShoppingRQ Version="17.2" TransactionIdentifier="'.$this->randomId.'">
                        <Document id="document"/>
                        <Party>
                            <Sender>
                                <TravelAgencySender>
                                    <PseudoCity>'.$this->pcc.'</PseudoCity>
                                    <AgencyID>'.$this->agencyId.'</AgencyID>
                                </TravelAgencySender>
                            </Sender>
                        </Party>
                        <CoreQuery>
                            <OriginDestinations>
                                ' . $originDestinations . '
                            </OriginDestinations>
                        </CoreQuery>
                        <Preference>
                            <CabinPreferences>
                                ' . $cabinPreferences . '
                            </CabinPreferences>
                        </Preference>
                        <DataLists>
                            ' . $paxXml . '
                        </DataLists>
                    </AirShoppingRQ>';
        try {
            $data = $this->sendRequest('AirShoppingRQ', $this->getSoapEnvelope($body));

            if (!$data || isset($data['error']) || isset($data['AirShoppingRS']['Errors'])) {
                \Log::error('AirShoppingRQ request failed', ['response' => $data]);
                return ['error' => 'Flight booking request failed Emirate (AirShoppingRQ).', 'details' => $data];
            }

            $airShoppingRS = $data['AirShoppingRS'];
            $flightData = [
                'offers' => $airShoppingRS['OffersGroup']['AirlineOffers']['Offer'] ?? '',
                'passengers' => $airShoppingRS['DataLists']['PassengerList']['Passenger'] ?? '',
                'baggageList' => $airShoppingRS['DataLists']['BaggageAllowanceList']['BaggageAllowance'] ?? '',
                'fares' => $airShoppingRS['DataLists']['FareList']['FareGroup'] ?? '',
                'flightSegments' => $airShoppingRS['DataLists']['FlightSegmentList']['FlightSegment'] ?? '',
                'flights' => $airShoppingRS['DataLists']['FlightList']['Flight'] ?? '',
                'destinationList' => $airShoppingRS['DataLists']['OriginDestinationList']['OriginDestination'] ?? '',
                'priceClass' => $airShoppingRS['DataLists']['PriceClassList']['PriceClass'] ?? '',
                'serviceList' => $airShoppingRS['DataLists']['ServiceDefinitionList']['ServiceDefinition'] ?? '',
                'responseId' => $airShoppingRS['ShoppingResponseID']['ResponseID']['value'] ?? '',
                'currencyFormat' => collect($airShoppingRS['Metadata']['Other']['OtherMetadata'] ?? [])
                    ->firstWhere(fn($item) => array_key_exists('CurrencyMetadatas', $item))['CurrencyMetadatas'] ?? [],
                'cabinClass' => $cabinClass,
                'request' => 1,
            ];
            // dd($flightData);
            session([
                'responseId' => $airShoppingRS['ShoppingResponseID']['ResponseID']['value'] ?? '',
            ]);
            // dd($this->getFlights($flightData));
            return $this->getFlights($flightData);
        } catch (\Exception $e) {
            \Log::error('Exception in fetching flight details', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while searchFlights details.'];
        }
    }
    public function getBundlePrice($data) // OfferPriceRQ...........
    {
        // dd($data);
        $data = $data['data'];
        if(empty($data)) return '';
        $offer = $this->formatOfferTag($data['depOfferIds'], $data['firstFlightBundleId'], $data['responseId']);
        if($data['rtnOfferIds']) {
            $offer .= $this->formatOfferTag($data['rtnOfferIds'], $data['returnFlightBundleId'], $data['responseId']);
        }
        $paxXml = $this->getPaxTag($data['paxCount']);
        $farePrefrences = '
                    <Preference>
                        <FarePreferences>
                            <Types>
                                <Type>70J</Type>
                                <Type>749</Type>
                            </Types>
                            <Exclusion>
                                <NoMinStayInd>false</NoMinStayInd>
                                <NoMaxStayInd>false</NoMaxStayInd>
                                <NoAdvPurchaseInd>false</NoAdvPurchaseInd>
                                <NoPenaltyInd>false</NoPenaltyInd>
                            </Exclusion>
                        </FarePreferences>
                        <PricingMethodPreference>
                            <BestPricingOption>Y</BestPricingOption>
                        </PricingMethodPreference>
                        <ServicePricingOnlyPreference>
                            <ServicePricingOnlyInd>false</ServicePricingOnlyInd>
                        </ServicePricingOnlyPreference>
                    </Preference>';
        $body = '<OfferPriceRQ Version="17.2" TransactionIdentifier="'.$this->randomId.'">
                    <Document id="document"/>
                    <Party>
                        <Sender>
                            <TravelAgencySender>
                                <PseudoCity>'.$this->pcc.'</PseudoCity>
                                <AgencyID>'.$this->agencyId.'</AgencyID>
                            </TravelAgencySender>
                        </Sender>
                    </Party>
                    <Query>
                        ' . $offer . '
                    </Query>
                    <DataLists>
                        ' . $paxXml . '
                    </DataLists>
                </OfferPriceRQ>';
        // dd($this->getSoapEnvelope($body));
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OfferPriceRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        try {
            $data = $this->sendRequest('OfferPriceRQ', $this->getSoapEnvelope($body));

            if (!$data || isset($data['error']) || isset($data['OfferPriceRS']['Errors'])) {
                \Log::error('OfferPriceRQ request failed', ['response' => $data]);
                return ['error' => 'Flight booking request failed Emirate (OfferPriceRQ).', 'details' => $data];
            }
            $offerPriceRS = $data['OfferPriceRS'];
            // $response = $this->helperService->postXml($this->url, $this->getSoapHeaders('OfferPriceRQ'), $this->getSoapEnvelope($body));
            // // dd($response->body());
            // if (!$response->successful()) {
            //     \Log::error('Flight bundle request failed Emirates', [
            //         'status' => $response->status(),
            //         'response' => $response->body()
            //     ]);
            //     return ['error' => 'Flight bundle request failed Emirates.', 'details' => $response->body()];
            // }
            // $responseXml = $response->body();
            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OfferPriceRS Response:\n" . (string) $responseXml . "\n\n\n\n\n\n", FILE_APPEND);}
            // $data = $this->helperService->XMLtoJSONEmirate($responseXml);
            // dd($responseXml);

            // if(isset($offerPriceRS['Errors'])){
            //     return ['error' => 'Flight bundle failed (OfferPriceRS).', 'details' => $offerPriceRS['Errors']['Error']];
            // }
            $flightData = [
                'offers' => $offerPriceRS['PricedOffer'] ?? '',
                'passengers' => $offerPriceRS['DataLists']['PassengerList']['Passenger'] ?? '',
                'baggageList' => $offerPriceRS['DataLists']['BaggageAllowanceList']['BaggageAllowance'] ?? '',
                'fares' => $offerPriceRS['DataLists']['FareList']['FareGroup'] ?? '',
                'flightSegments' => $offerPriceRS['DataLists']['FlightSegmentList']['FlightSegment'] ?? '',
                'flights' => $offerPriceRS['DataLists']['FlightList']['Flight'] ?? '',
                'destinationList' => $offerPriceRS['DataLists']['OriginDestinationList']['OriginDestination'] ?? '',
                'priceClass' => $offerPriceRS['DataLists']['PriceClassList']['PriceClass'] ?? '',
                'serviceList' => $offerPriceRS['DataLists']['ServiceDefinitionList']['ServiceDefinition'] ?? '',
                'responseId' => $offerPriceRS['ShoppingResponseID']['ResponseID']['value'] ?? '',
                'currencyFormat' => collect($offerPriceRS['Metadata']['Other']['OtherMetadata'] ?? [])
                    ->firstWhere(fn($item) => array_key_exists('CurrencyMetadatas', $item))['CurrencyMetadatas'] ?? [],
                'request' => 2,
            ];
            // dd($flightData);
            session([
                'responseId' => $offerPriceRS['ShoppingResponseID']['ResponseID']['value'] ?? '',
            ]);
            // dd($this->getFlights($flightData));
            return $this->getFlights($flightData);
        } catch (\Exception $e) {
            \Log::error('Exception in getBundlePrice details', ['message' => $e->getMessage()]);
            return ['error' => 'Exception occurred while getBundlePrice details.'];
        }
    }
    public function bookFlight($data) // OrderCreateRQ (for confirm booking without payment)...................
    {
        if(empty($data)) return '';
        $user = $data['user'];
        // dd($data);
        $offer = $this->formatOfferTag($data['offerIds'], $data['bundleId'], $data['responseId']);
        $paxXml = $this->getPaxContactTag($data['paxCount'], $data['passengers']);
        // dd($paxXml);
        $loggedInTag = '
            <ContactList>
                <ContactInformation ContactID="CID1">
                    <ContactProvided>
                        <EmailAddress>
                            <Label>Personal</Label>
                            <EmailAddressValue>'.$user['userEmail'].'</EmailAddressValue>
                        </EmailAddress>
                    </ContactProvided>
                    <ContactProvided>
                        <Phone>
                            <Label>Home</Label>
                            <CountryDialingCode>'.$user['userPhoneCode'].'</CountryDialingCode>
                            <PhoneNumber>'.$user['userPhone'].'</PhoneNumber>
                        </Phone>
                    </ContactProvided>
                </ContactInformation>
            </ContactList>';
        $body = '<OrderCreateRQ Version="17.2" TransactionIdentifier="'.$this->randomId.'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns1="http://ndc.farelogix.com/aug">
                    <Document id="document"/>
                    <Party>
                        <Sender>
                            <TravelAgencySender>
                                <PseudoCity>'.$this->pcc.'</PseudoCity>
                                <AgencyID>'.$this->agencyId.'</AgencyID>
                            </TravelAgencySender>
                        </Sender>
                    </Party>
                    <Query>
                        <Order>
                            ' . $offer . '
                        </Order>
                        <DataLists>
                            ' . $paxXml . '
                            ' . $loggedInTag . '
                        </DataLists>
                    </Query>
                </OrderCreateRQ>';
        // dd($this->getSoapEnvelope($body));
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OrderCreateRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        // if ($this->regenerateLogs) {file_put_contents($this->logPathBooking, "OrderCreateRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        try {
            $data = $this->sendRequest('OrderCreateRQ', $this->getSoapEnvelope($body), true);

            if (!$data || isset($data['error']) || isset($data['OrderViewRS']['Errors'])) {
                \Log::error('OrderCreateRQ request failed', ['response' => $data]);
                return ['error' => 'Flight booking request failed Emirate (OrderCreateRQ).', 'details' => $data];
            }
            $orderViewRS = $data['OrderViewRS'];
            // $response = $this->helperService->postXml($this->url, $this->getSoapHeaders('OrderCreateRQ'), $this->getSoapEnvelope($body));
            // if (!$response || !$response->successful()) {
            //     \Log::error('Flight booking request failed Emirates', [
            //         'status' => $response?->status(),
            //         'response' => $response?->body()
            //     ]);
            //     return ['error' => 'Flight booking request failed Emirates.', 'details' => $response?->body()];
            // }

            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OrderCreateRS Response:\n" . (string) $response->body() . "\n\n\n\n\n\n", FILE_APPEND);}
            // if ($this->regenerateLogs) {file_put_contents($this->logPathBooking, "OrderCreateRS Response:\n" . (string) $response->body() . "\n\n\n\n\n\n", FILE_APPEND);}

            // $data = $this->helperService->XMLtoJSONEmirate($response->body());
            // // dd($data);

            // $orderViewRS = $data['SOAP-ENV:Body']['XXTransactionResponse']['RSP']['OrderViewRS'] ?? null;
            // if (!$orderViewRS) {
            //     return ['error' => 'Invalid response structure.', 'details' => $data];
            // }

            // if (isset($orderViewRS['Errors'])) {
            //     return ['error' => 'Flight booking failed.', 'details' => $orderViewRS['Errors']['Error']];
            // }

            $flightData = [
                'offers' => $orderViewRS['Response']['Order'] ?? '',
                'passengers' => $orderViewRS['Response']['DataLists']['PassengerList']['Passenger'] ?? '',
                'baggageList' => $orderViewRS['Response']['DataLists']['BaggageAllowanceList']['BaggageAllowance'] ?? '',
                'fares' => $orderViewRS['Response']['DataLists']['FareList']['FareGroup'] ?? '',
                'flightSegments' => $orderViewRS['Response']['DataLists']['FlightSegmentList']['FlightSegment'] ?? '',
                'flights' => $orderViewRS['Response']['DataLists']['FlightList']['Flight'] ?? '',
                'destinationList' => $orderViewRS['Response']['DataLists']['OriginDestinationList']['OriginDestination'] ?? '',
                'priceClass' => $orderViewRS['Response']['DataLists']['PriceClassList']['PriceClass'] ?? '',
                'serviceList' => $orderViewRS['Response']['DataLists']['ServiceDefinitionList']['ServiceDefinition'] ?? '',
                'transactionId' => $orderViewRS['@attributes']['TransactionIdentifier'] ?? '',
                'currencyFormat' => collect($orderViewRS['Response']['Metadata']['Other']['OtherMetadata'] ?? [])
                    ->firstWhere(fn($item) => array_key_exists('CurrencyMetadatas', $item))['CurrencyMetadatas'] ?? [],
                'request' => 3,
            ];
            session(['responseId' => null]);

            // dd($this->getFlights($flightData));
            return $this->getFlights($flightData);

        } catch (\Exception $e) {
            \Log::error('Exception in bookFlight details', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Exception occurred while bookFlight details.'];
        }

    }
    public function orderRetrieve($data) // OrderRetrieveRQ (for fetch order details like latest price & fares)...........
    {
        if(empty($data) || !isset($data['orderId'])) return 'Some data is missing for order retrieve';
        $orderId = $data['orderId'];
        $orderOwner = $data['owner'] ?? 'EK';
        // dd($data);
        $body = '<OrderRetrieveRQ Version="17.2" TransactionIdentifier="'.$this->randomId.'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns1="http://ndc.farelogix.com/aug">
                    <Document id="document"/>
                    <Party>
                        <Sender>
                            <TravelAgencySender>
                                <PseudoCity>'.$this->pcc.'</PseudoCity>
                                <AgencyID>'.$this->agencyId.'</AgencyID>
                            </TravelAgencySender>
                        </Sender>
                    </Party>
                    <Query>
                        <Filters>
                            <OrderID Owner="'.$orderOwner.'">'.$orderId.'</OrderID>
                        </Filters>
                    </Query>
                </OrderRetrieveRQ>';
        // dd($this->getSoapEnvelope($body));
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OrderRetrieveRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        try {
            $data = $this->sendRequest('OrderRetrieveRQ', $this->getSoapEnvelope($body));

            if (!$data || isset($data['error']) || isset($data['OrderViewRS']['Errors'])) {
                \Log::error('OrderRetrieveRQ request failed', ['response' => $data]);
                return ['error' => 'Flight booking request failed Emirate (OrderRetrieveRQ).', 'details' => $data];
            }
            $orderViewRS = $data['OrderViewRS'];
            // $response = $this->helperService->postXml(
            //     $this->url,
            //     $this->getSoapHeaders('OrderRetrieveRQ'),
            //     $this->getSoapEnvelope($body)
            // );

            // if (!$response || !$response->successful()) {
            //     \Log::error('Flight booking request failed Emirates', [
            //         'status' => $response?->status(),
            //         'response' => $response?->body()
            //     ]);
            //     return ['error' => 'Flight booking request failed Emirates (orderRetrieve).', 'details' => $response?->body()];
            // }

            // if ($this->regenerateLogs) file_put_contents($this->logPath, "OrderRetrieveRS Response:\n" . (string) $response->body() . "\n\n\n\n\n\n", FILE_APPEND);
            // $data = $this->helperService->XMLtoJSONEmirate($response->body());

            // $orderViewRS = $data['SOAP-ENV:Body']['XXTransactionResponse']['RSP']['OrderViewRS'] ?? null;
            // if (!$orderViewRS) return ['error' => 'Invalid response structure.', 'details' => $data];

            // if (isset($orderViewRS['Errors'])) {
            //     return ['error' => 'Flight booking failed. (orderRetrieve)', 'details' => $orderViewRS['Errors']['Error']];
            // }

            $flightData = [
                'offers' => $orderViewRS['Response']['Order'] ?? '',
                'passengers' => $orderViewRS['Response']['DataLists']['PassengerList']['Passenger'] ?? '',
                'baggageList' => $orderViewRS['Response']['DataLists']['BaggageAllowanceList']['BaggageAllowance'] ?? '',
                'fares' => $orderViewRS['Response']['DataLists']['FareList']['FareGroup'] ?? '',
                'flightSegments' => $orderViewRS['Response']['DataLists']['FlightSegmentList']['FlightSegment'] ?? '',
                'flights' => $orderViewRS['Response']['DataLists']['FlightList']['Flight'] ?? '',
                'destinationList' => $orderViewRS['Response']['DataLists']['OriginDestinationList']['OriginDestination'] ?? '',
                'priceClass' => $orderViewRS['Response']['DataLists']['PriceClassList']['PriceClass'] ?? '',
                'serviceList' => $orderViewRS['Response']['DataLists']['ServiceDefinitionList']['ServiceDefinition'] ?? '',
                'transactionId' => $orderViewRS['@attributes']['TransactionIdentifier'] ?? '',
                'currencyFormat' => collect($orderViewRS['Response']['Metadata']['Other']['OtherMetadata'] ?? [])
                    ->firstWhere(fn($item) => array_key_exists('CurrencyMetadatas', $item))['CurrencyMetadatas'] ?? [],
                'request' => 3,
            ];

            // dd($this->getFlights($flightData));
            return $this->getFlights($flightData);

        } catch (\Exception $e) {
            \Log::error('Exception in fetching flight details (orderRetrieve).', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Exception occurred while orderRetrieve details.'];
        }

    }
    public function orderChange($data) // OrderChangeRQ (for approve payment)................
    {
        if(empty($data) || !isset($data['orderId']) || !isset($data['amount'])) return 'Some data is missing for order change';
        $orderId = $data['orderId'];
        $amount = $data['amount'];
        $code = $data['code'] ?? 'PKR';
        // dd($data);
        $body = '<OrderChangeRQ Version="17.2" TransactionIdentifier="'.$this->randomId.'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns1="http://ndc.farelogix.com/aug">
                    <Document id="document"/>
                    <Party>
                        <Sender>
                            <TravelAgencySender>
                                <PseudoCity>'.$this->pcc.'</PseudoCity>
                                <AgencyID>'.$this->agencyId.'</AgencyID>
                            </TravelAgencySender>
                        </Sender>
                    </Party>
                    <Query>
                        <OrderID>'.$orderId.'</OrderID>
                        <Payments>
                            <Payment>
                                <Type>CA</Type>
                                <Method>
					                <Cash CashInd="true"/>
                                </Method>
                                <Amount Code="'.$code.'">'.$amount.'</Amount>
                            </Payment>
                        </Payments>
                    </Query>
                </OrderChangeRQ>';
        // dd($this->getSoapEnvelope($body));
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OrderChangeRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        // if ($this->regenerateLogs) {file_put_contents($this->logPathBooking, "OrderChangeRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        try {
            $data = $this->sendRequest('OrderChangeRQ', $this->getSoapEnvelope($body), true);
            // dd($data);

            if (!$data || isset($data['error']) || isset($data['OrderViewRS']['Errors'])) {
                \Log::error('OrderChangeRQ request failed', ['response' => $data]);
                return ['error' => 'Flight booking request failed Emirate (OrderChangeRQ).', 'details' => $data];
            }
            $orderViewRS = $data['OrderViewRS'];
            if (isset($orderViewRS['Warnings'])) {
                return ['error' => 'Flight booking request failed Emirate (OrderChangeRQ).', 'details' => $orderViewRS['Warnings']['Warning']['value']];

            }
            // $response = $this->helperService->postXml(
            //     $this->url,
            //     $this->getSoapHeaders('OrderChangeRQ'),
            //     $this->getSoapEnvelope($body)
            // );

            // if (!$response || !$response->successful()) {
            //     \Log::error('Flight booking request failed Emirates', [
            //         'status' => $response?->status(),
            //         'response' => $response?->body()
            //     ]);
            //     return ['error' => 'Flight booking request failed Emirates (orderChange).', 'details' => $response?->body()];
            // }

            // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OrderChangeRS Response:\n" . (string) $response->body() . "\n\n\n\n\n\n", FILE_APPEND);}
            // if ($this->regenerateLogs) {file_put_contents($this->logPathBooking, "OrderChangeRS Response:\n" . (string) $response->body() . "\n\n\n\n\n\n", FILE_APPEND);}

            // $data = $this->helperService->XMLtoJSONEmirate($response->body());

            // $orderViewRS = $data['SOAP-ENV:Body']['XXTransactionResponse']['RSP']['OrderViewRS'] ?? null;
            // if (!$orderViewRS) return ['error' => 'Invalid response structure.', 'details' => $data];

            // if (isset($orderViewRS['Errors'])) return ['error' => 'Flight booking failed.', 'details' => $orderViewRS['Errors']['Error']];

            $flightData = [
                'offers' => $orderViewRS['Response']['Order'] ?? '',
                'ticketInfos' => $orderViewRS['Response']['TicketDocInfos']['TicketDocInfo'] ?? '',
                'passengers' => $orderViewRS['Response']['DataLists']['PassengerList']['Passenger'] ?? '',
                'baggageList' => $orderViewRS['Response']['DataLists']['BaggageAllowanceList']['BaggageAllowance'] ?? '',
                'fares' => $orderViewRS['Response']['DataLists']['FareList']['FareGroup'] ?? '',
                'flightSegments' => $orderViewRS['Response']['DataLists']['FlightSegmentList']['FlightSegment'] ?? '',
                'flights' => $orderViewRS['Response']['DataLists']['FlightList']['Flight'] ?? '',
                'destinationList' => $orderViewRS['Response']['DataLists']['OriginDestinationList']['OriginDestination'] ?? '',
                'priceClass' => $orderViewRS['Response']['DataLists']['PriceClassList']['PriceClass'] ?? '',
                'serviceList' => $orderViewRS['Response']['DataLists']['ServiceDefinitionList']['ServiceDefinition'] ?? '',
                'transactionId' => $orderViewRS['@attributes']['TransactionIdentifier'] ?? '',
                'currencyFormat' => collect($orderViewRS['Response']['Metadata']['Other']['OtherMetadata'] ?? [])
                    ->firstWhere(fn($item) => array_key_exists('CurrencyMetadatas', $item))['CurrencyMetadatas'] ?? [],
                'request' => 3,
            ];

            return $this->getFlights($flightData);
        } catch (\Exception $e) {
            \Log::error('Exception in fetching flight details(orderChange)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Exception occurred while orderChange details.'];
        }

    }
    public function orderCancel($data) // OrderCancelRQ (for cancel order)
    {
        if(empty($data) || !isset($data['orderId'])) return 'Some data is missing for order retrieve';
        $orderId = $data['orderId'];
        $orderOwner = $data['owner'] ?? 'EK';
        // dd($data);
        $body = '<OrderCancelRQ Version="17.2" TransactionIdentifier="'.$this->randomId.'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns1="http://ndc.farelogix.com/aug">
                    <Document id="document"/>
                    <Party>
                        <Sender>
                            <TravelAgencySender>
                                <PseudoCity>'.$this->pcc.'</PseudoCity>
                                <AgencyID>'.$this->agencyId.'</AgencyID>
                            </TravelAgencySender>
                        </Sender>
                    </Party>
                    <Query>
                        <Order Owner="'.$orderOwner.'" OrderID="'.$orderId.'"/>
                    </Query>
                </OrderCancelRQ>';
        // dd($this->getSoapEnvelope($body));
        // if ($this->regenerateLogs) {file_put_contents($this->logPath, "OrderCancelRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        // if ($this->regenerateLogs) {file_put_contents($this->logPathBooking, "OrderCancelRQ Request:\n" . (string) $this->getSoapEnvelope($body) . "\n", FILE_APPEND);}
        try {
            $data = $this->sendRequest('orderCancel', $this->getSoapEnvelope($body), true);

            if (!$data || isset($data['error']) || isset($data['OrderViewRS']['Errors'])) {
                \Log::error('orderCancel request failed', ['response' => $data]);
                return ['error' => 'Flight booking request failed Emirate (orderCancel).', 'details' => $data];
            }
            $orderCancelRS = $data['OrderCancelRS'];
            // $response = $this->helperService->postXml(
            //     $this->url,
            //     $this->getSoapHeaders('OrderCancelRQ'),
            //     $this->getSoapEnvelope($body)
            // );

            // if (!$response || !$response->successful()) {
            //     \Log::error('Flight booking request failed Emirates (orderCancel)', [
            //         'status' => $response?->status(),
            //         'response' => $response?->body()
            //     ]);
            //     return ['error' => 'Flight booking request failed Emirates (orderCancel).', 'details' => $response?->body()];
            // }

            // if ($this->regenerateLogs) file_put_contents($this->logPath, "OrderCancelRS Response:\n" . (string) $response->body() . "\n", FILE_APPEND);
            // if ($this->regenerateLogs) file_put_contents($this->logPathBooking, "OrderCancelRS Response:\n" . (string) $response->body() . "\n", FILE_APPEND);
            // $data = $this->helperService->XMLtoJSONEmirate($response->body());

            // $orderCancelRS = $data['SOAP-ENV:Body']['XXTransactionResponse']['RSP']['OrderCancelRS'] ?? null;
            // if (!$orderCancelRS) return ['error' => 'Invalid response structure.', 'details' => $data];

            // if (isset($orderCancelRS['Errors'])) {
            //     return ['error' => 'Flight booking failed.', 'details' => $orderCancelRS['Errors']['Error']];
            // }
            // dd($orderCancelRS);

            $flightData = [
                'orderReference' => $orderCancelRS['Response']['OrderReference']['value'],
                'ticketInfos' => $this->ticketInfos($orderCancelRS['Response']['TicketDocInfos']['TicketDocInfo'] ?? [])
            ];
            if (isset($orderCancelRS['Warnings'])) {
                $flightData['warnings'] = [
                    'shortText' => $orderCancelRS['Warnings']['Warning']['@attributes']['ShortText'],
                    'code' => $orderCancelRS['Warnings']['Warning']['@attributes']['Code'],
                    'details' => $orderCancelRS['Warnings']['Warning']['value']
                ];
            }

            // dd($flightData);
            return $flightData;

        } catch (\Exception $e) {
            \Log::error('Exception in fetching flight details (orderCancel)', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'Exception occurred while orderCancel details.'];
        }

    }

    // ------------------  Helper Functions  ---------------------

    private function getSoapEnvelope($body)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://farelogix.com/ns1" xmlns:t="http://farelogix.com/flx/t">
                <SOAP-ENV:Header>
                    <t:TransactionControl>
                        <tc>
                            <iden u="'.$this->u.'" p="'.$this->passwordIden.'" pseudocity="'.$this->pcc.'" agt="'.$this->user.'" agtpwd="'.$this->agtPassword.'" agtrole="'.$this->role.'" agy="'.$this->agy.'"/>
                            <agent user="'.$this->user.'"/>
                            <trace>'.$this->pcc.'_ek</trace>
                            <script engine="FLXDM" name="'.$this->agencyName.'"/>
                        </tc>
                    </t:TransactionControl>
                </SOAP-ENV:Header>
                <SOAP-ENV:Body>
                    <ns1:XXTransaction>
                        <REQ>
                            '.$body.'
                        </REQ>
                    </ns1:XXTransaction>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>';
    }
    private function getSoapHeaders($action)
    {
        return [
            'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            'Content-Type' => 'text/xml;charset=UTF-8',
            'SOAPAction' => $action,
            'Agency' => $this->agencyName,
            'IATA' => $this->agencyId,
            'PCC' => $this->pcc,
        ];
    }
    private function getPaxTag($data)
    {
        if(empty($data) || empty($data['adt'])) return '';
        $paxId = 1;
        $adtIds = [];
        $paxXml = "<PassengerList>";
        for ($i = 0; $i < $data['adt']; $i++, $paxId++) {
            $paxXml .= '
                <Passenger PassengerID="T'.$paxId.'">
                    <PTC>ADT</PTC>
                </Passenger>';
            $adtIds[] = 'T'.$paxId;
        }
        for ($i = 0; $i < $data['chd']; $i++, $paxId++) {
            $paxXml .= '
                <Passenger PassengerID="T'.$paxId.'">
                    <PTC>CNN</PTC>
                </Passenger>';
        }
        for ($i = 0; $i < $data['inf']; $i++) {
            if (isset($adtIds[$i])) {
                $infId = $adtIds[$i] . '.1';
                $paxXml .= '
                    <Passenger PassengerID="'.$infId.'">
                        <PTC>INF</PTC>
                    </Passenger>';
            } else {
                break;
            }
        }
        $paxXml .= "</PassengerList>";
        return $paxXml;
    }
    private function getFlights($data)
    {
        // dd($data);
        if (empty($data) || empty($data['destinationList']) || empty($data['flights']) || empty($data['flightSegments']) || empty($data['offers']) || empty($data['baggageList']) || empty($data['priceClass'])) return 'Something missing';

        $destinations = isset($data['destinationList'][0]) ? $data['destinationList'] : [$data['destinationList']];
        $flights = collect(isset($data['flights'][0]) ? $data['flights'] : [$data['flights']]);
        $segments = collect(isset($data['flightSegments'][0]) ? $data['flightSegments'] : [$data['flightSegments']]);
        $offers = collect(isset($data['offers'][0]) ? $data['offers'] : [$data['offers']]);
        $baggages = collect(isset($data['baggageList'][0]) ? $data['baggageList'] : [$data['baggageList']]);
        $priceClass = collect(isset($data['priceClass'][0]) ? $data['priceClass'] : [$data['priceClass']]);
        $passengers = collect(isset($data['passengers'][0]) ? $data['passengers'] : [$data['passengers']]);
        $currencyFormat = $data['currencyFormat'] ?? null;
        $serviceList = collect($data['serviceList']) ?? null;
        $responseId = $data['responseId'] ?? null;
        $transactionId = $data['transactionId'] ?? null;
        $cabinClass = $data['cabinClass'] ?? 'y';
        $request = $data['request'];
        $ticketInfos = !empty($data['ticketInfos']) ? (collect(isset($data['ticketInfos'][0]) ? $data['ticketInfos'] : [$data['ticketInfos']])) : null;
        // dd($destinations, $flights, $segments, $offers, $baggages, $priceClass, $responseId);
        $timeZone = config('variables.setting.timezone') ?? 'Asia/Karachi';
        $data = [];
        $tax = config('variables.flyjinnah_api.tax') ?? 0;
        if ($ticketInfos) {
            $data['ticketInfos'] = $this->ticketInfos($ticketInfos);
        }
        $matchedOffers = '';
        foreach ($destinations as $item) {
            $flightIds = explode(' ', $item['FlightReferences']['value'] ?? ''); // fetch bundle with this Aliiiiiiiiiii);
            $matchedFlights = $flights->filter(fn($flight) => in_array($flight['@attributes']['FlightKey'] ?? null, $flightIds))->values();
            $flightSegmentReferences = $matchedFlights->map(fn($flight) => explode(' ', $flight['SegmentReferences']['value'] ?? ''))->values();
            // dd($flightSegmentReferences);
            $segmentDetails=[];
            foreach ($flightSegmentReferences as $segmentIds) {
                $flightSegments = collect($segmentIds)
                    ->map(fn($id) => $segments->firstWhere('@attributes.SegmentKey', $id))
                    ->filter()
                    ->values();

                $relatedFlightKeys = $matchedFlights
                    ->filter(fn($flight) => !array_diff($segmentIds, explode(' ', $flight['SegmentReferences']['value'] ?? '')))
                    ->pluck('@attributes.FlightKey')
                    ->all();
                // dd($relatedFlightKeys);

                $matchedOffers = $offers
                    ->filter(function ($offer) use ($relatedFlightKeys) {
                        return isset($offer['FlightsOverview']['FlightRef']['value'])
                            ? in_array($offer['FlightsOverview']['FlightRef']['value'], $relatedFlightKeys)
                            : true;
                    })
                    ->map(function ($offer) use ($baggages, $priceClass, $serviceList, $passengers, $currencyFormat) {
                        $offer['BaggageAllowance'] = collect($offer['BaggageAllowance'] ?? [])
                            ->map(fn($allowance) => $this->updateBaggageAllowance($allowance, $baggages))
                            ->all();
                        // dd($offer);
                        // $item = isset($offer['OfferItem'][0]) ? $offer['OfferItem'][0] : $offer['OfferItem'];
                        $priceClassRef = $offer['FlightsOverview']['FlightRef']['@attributes']['PriceClassRef'] ?? null;
                        $offer['priceClass'] = $priceClassRef ? $priceClass->where('@attributes.PriceClassID', $priceClassRef)->values()->first() : '';
                        $refs = $offer['BookingReferences']['BookingReference'] ?? [];

                        $bookingReferences = [
                            'bookingId' => ($refs[0]['OtherID']['value'] ?? '') . ' ' . ($refs[0]['ID']['value'] ?? ''),
                            'airlineID' => ($refs[1]['AirlineID']['value'] ?? '') . ' ' . ($refs[1]['ID']['value'] ?? ''),
                            'airline' => $refs[1]['AirlineID']['@attributes']['Name'] ?? '',
                        ];
                        $formattedItems = $this->formatOfferItems((isset($offer['OrderItems']) ? $offer['OrderItems'] : $offer['OfferItem']), $serviceList, $passengers, $currencyFormat);
                        return [
                            'offerID' => $offer['@attributes'] ?? null,
                            'bookingReferences' => !empty($bookingReferences) ? $bookingReferences : null,
                            'parameters' => $offer['Parameters'] ?? null,
                            // 'timeLimits' => $offer['TimeLimits'] ?? null,
                            'timeLimits' => isset($offer['TimeLimits']) 
                                ? $offer['TimeLimits'] : (!empty($formattedItems) && isset($formattedItems[0]['timeLimits']) ? $formattedItems[0]['timeLimits'] : null),
                            'totalPrice' =>  [
                                'code' => $offer['TotalPrice']['DetailCurrencyPrice']['Total']['@attributes']['Code'] ?? ($offer['TotalOrderPrice']['DetailCurrencyPrice']['Total']['@attributes']['Code'] ?? ''),
                                'amount' => $offer['TotalPrice']['DetailCurrencyPrice']['Total']['value'] ?? ($offer['TotalOrderPrice']['DetailCurrencyPrice']['Total']['value'] ?? ''),
                            ],
                            'offerItem' => $formattedItems,
                            'baggageAllowance' => $offer['BaggageAllowance'],
                            'priceClass' => $offer['priceClass'],
                        ];
                    })->values();
                $expTime = isset($matchedOffers->first()['timeLimits']['OfferExpiration']['@attributes']['DateTime']) ? Carbon::parse($matchedOffers->first()['timeLimits']['OfferExpiration']['@attributes']['DateTime'])->setTimezone($timeZone) : now()->addMinutes(20);
                session([
                    'IdsExpireTimeEmi' => $expTime,
                ]);
                // dd($matchedOffers);
                $lowestPrice = [
                    'code' => $matchedOffers->min(fn($offer) => data_get($offer, 'totalPrice.code', 'PKR')),
                    'amount' => $matchedOffers->min(fn($offer) => (float) data_get($offer, 'totalPrice.amount', 0)) + $tax
                ];
                if ($flightSegments->isNotEmpty()) {
                    $secondFlight = [];
                    if (count($flightSegments) > 1) {
                        $last = $flightSegments->last();
                        $secondFlight = [
                            'departure' => $last['Departure'] ?? [],
                            'arrival' => $last['Arrival'] ?? [],
                            'isConnected' => isset($last['@attributes']['ConnectInd']) ? filter_var($last['@attributes']['ConnectInd'], FILTER_VALIDATE_BOOLEAN) : null,
                            'details' => $last['FlightDetail'] ?? [],
                            'equipment' => $last['Equipment'] ?? [],
                            'marketingCarrier' => $last['MarketingCarrier'] ?? [],
                        ];
                    }
                    $first = $flightSegments->first();
                    $totalMinutes = 0;
                    try {
                        $d1 = new \DateInterval($first['FlightDetail']['FlightDuration']['Value']['value'] ?? 'PT0M');
                        $totalMinutes += ($d1->h * 60) + $d1->i;
                    } catch (\Exception $e) {
                        \Log::error('Exception in calculate duration', ['message' => $e->getMessage()]);
                    }

                    if (!empty($secondFlight) && isset($secondFlight['details']['FlightDuration']['Value']['value'])) {
                        try {
                            $d2 = new \DateInterval($secondFlight['details']['FlightDuration']['Value']['value']);
                            $totalMinutes += ($d2->h * 60) + $d2->i;
                        } catch (\Exception $e) {
                            \Log::error('Exception in calculate duration', ['message' => $e->getMessage()]);
                        }
                    }

                    $hours = floor($totalMinutes / 60);
                    $minutes = $totalMinutes % 60;
                    $duration = ($hours > 0 ? $hours . 'h ' : '') . ($minutes > 0 ? $minutes . 'm' : '');
                    $segmentDetails[] = [
                        'Departure' => $first['Departure'] ?? [],
                        // 'Arrival' => $flightSegments->last()['Arrival'] ?? [],
                        'Arrival' => $first['Arrival'] ?? [],
                        'segmentKey' => $first['@attributes']['SegmentKey'] ?? [],
                        'flightDetails' => [
                            'isConnected' => isset($first['@attributes']['ConnectInd']) ? filter_var($first['@attributes']['ConnectInd'], FILTER_VALIDATE_BOOLEAN) : null,
                            'details' => $first['FlightDetail'] ?? [],
                            'equipment' => $first['Equipment'] ?? [],
                            'marketingCarrier' => $first['MarketingCarrier'] ?? [],
                        ],
                        'secondFlight' => $secondFlight,
                        'duration' => $duration ?? '',
                        'price' => $lowestPrice,
                        'bundles' => $request === 1 ? $matchedOffers->all() : [],
                    ];
                }
            }
            if($request === 2 || $request === 3) {
                $data['segments'][] = [
                    'departureCode' => $this->helperService->codeToCountry($item['DepartureCode']),
                    'arrivalCode' => $this->helperService->codeToCountry($item['ArrivalCode']),
                    'flights' => collect($segmentDetails)->first(),
                    'responseId' => $responseId,
                ];
            } else {
                $data[] = [
                    'departureCode' => $this->helperService->codeToCountry($item['DepartureCode']),
                    'arrivalCode' => $this->helperService->codeToCountry($item['ArrivalCode']),
                    'flights' => $segmentDetails,
                    'responseId' => $responseId,
                ];
            }
        }
        if($request === 2 || $request === 3) {
            $data['bundle'] = $matchedOffers->first();
            $expTime = isset($data['bundle']['timeLimits']['OfferExpiration']['@attributes']['DateTime']) ? Carbon::parse($data['bundle']['timeLimits']['OfferExpiration']['@attributes']['DateTime'])->setTimezone($timeZone) : now()->addMinutes(20);
            session([
                'IdsExpireTimeEmi' => $expTime,
            ]);
        }
        if($request === 3) {
            $data['passengers'] = [];
            foreach ($passengers as $pax) {
                $data['passengers'][] = [
                    'id' => $pax['@attributes']['PassengerID'] ?? '',
                    'type' => $pax['PTC']['value'] ?? '',
                    'birthdate' => $pax['Birthdate']['value'] ?? '',
                    'gender' => $pax['Individual']['Gender']['value'] ?? '',
                    'givenName' => $pax['Individual']['GivenName']['value'] ?? '',
                    'surname' => $pax['Individual']['Surname']['value'] ?? '',
                    'title' => $pax['Individual']['NameTitle']['value'] ?? '',
                    'contactRef' => $pax['ContactInfoRef']['value'] ?? null,
                    'infantRef' => $pax['InfantRef']['value'] ?? null,
                ];
            }
        }
        if($request === 1) {
            $data['cabinClass'] = $cabinClass;
        }
        $data['transactionId'] = $transactionId;
        // dd($data);
        return $data;
    }
    private function formatOfferItems($data, $serviceItems = null, $passengers, $currencyFormat)
    {
        $data = isset($data['OrderItem']) ? $data['OrderItem'] : $data;
        // dd($data);
        $fareRef = [
            1 => 'No Show',
            2 => 'Prior to Departure',
            3 => 'After Departure'
        ];
        if (empty($data)) return [];
        $data = isset($data[0]) ? $data : [$data];
        $offers = [];
        foreach ($data as $item) {
            // $item = isset($item['OrderItem']) ? $item['OrderItem'] : $item;
            // dd($item);
            $offerItemID = $item['@attributes']['OfferItemID'] ?? ($item['@attributes']['OrderItemID'] ?? '');
            $priceTag = $item['TotalPriceDetail'] ?? ($item['PriceDetail'] ?? '');
            // dd($item, $offerItemID);
            $currency = $priceTag['TotalAmount']['DetailCurrencyPrice']['Total']['@attributes']['Code'] ?? '';
            $price = $priceTag['TotalAmount']['DetailCurrencyPrice']['Total']['value'] ?? '';
            $fareComponent = isset($item['FareDetail']['FareComponent'][0]) ? $item['FareDetail']['FareComponent'] : [$item['FareDetail']['FareComponent']];
            $refs = explode(' ', $item['FareDetail']['PassengerRefs']['value'] ?? '');
            // dd($refs);
            // dd($currency, $price, $fareComponent, $refs);
            $fareDetail = [
                'passengerRef' => $item['FareDetail']['PassengerRefs'] ?? '',
                'passengers' => collect($refs)
                    ->map(fn($ref) => collect($passengers)->firstWhere('@attributes.PassengerID', $ref)['PTC']['value'] ?? null)
                    ->filter()->countBy()
                    ->map(fn($count, $type) => "$type x $count")
                    ->implode(', ') ?: 'N/A',
                'taxes' => [
                    'baseAmount' => [
                        'code' => $item['FareDetail']['Price']['BaseAmount']['@attributes']['Code'] ?? '',
                        'amount' => $item['FareDetail']['Price']['BaseAmount']['value'] ?? '',
                    ],
                    'tax' => collect($item['FareDetail']['Price']['Taxes']['Breakdown']['Tax'])->map(function ($tax) {
                        return [
                            'price' => [
                                'code' => $tax['Amount']['@attributes']['Code'] ?? '',
                                'amount' => $tax['Amount']['value'] ?? '',
                            ],
                            'taxCode' => $tax['TaxCode']['value'] ?? '',
                            'description' => $tax['Description']['value'] ?? '',
                        ];
                    })->values()->all(),
                    'total' => [
                        'code' => $item['FareDetail']['Price']['Taxes']['Total']['@attributes']['Code'] ?? '',
                        'amount' => $item['FareDetail']['Price']['Taxes']['Total']['value'] ?? '',
                    ]
                ],
                'penalties' => collect($fareComponent)->map(function ($fare) use ($fareRef, $currencyFormat) {
                    return [
                        'arrival' => $fare['SegmentRefs']['@attributes']['ON_Point'] ?? '',
                        'destination' => $fare['SegmentRefs']['@attributes']['OFF_Point'] ?? '',
                        // $fare['FareRules']['Penalty']['@attributes'] ?? '',
                        'cabinType' => $fare['FareBasis']['CabinType']['CabinTypeName']['value'] ?? '',
                        'fareRules' => [
                            'cancelFee' => collect($fare['FareRules']['Penalty']['Details']['Detail'] ?? [])
                                ->filter(fn($item) => ($item['Type']['value'] ?? '') === 'Cancel' && isset($item['Application']['value']))
                                ->mapWithKeys(function ($item) use ($fareRef, $currencyFormat) {
                                    $label = $fareRef[$item['Application']['value']] ?? 'Unknown';
                                    $amountData = $item['Amounts']['Amount'] ?? [];

                                    return [
                                        $label => [
                                            'amountApplication' => $amountData['AmountApplication']['value'] ?? null,
                                            'price' => [
                                                'amount' => $this->formatMinorUnitsAmount(
                                                    $currencyFormat,
                                                    $amountData['CurrencyAmountValue']['@attributes']['Code'] ?? null,
                                                    $amountData['CurrencyAmountValue']['value'] ?? null
                                                ),
                                                'code' => $amountData['CurrencyAmountValue']['@attributes']['Code'] ?? null,
                                            ]
                                        ]
                                    ];
                                })
                                ->whenEmpty(fn() => collect(['Status' => 'No cancellation allowed']))
                                ->toArray(),

                            'changeFee' => collect($fare['FareRules']['Penalty']['Details']['Detail'] ?? [])
                                ->filter(fn($item) => ($item['Type']['value'] ?? '') === 'Change' && isset($item['Application']['value']))
                                ->mapWithKeys(function ($item) use ($fareRef, $currencyFormat) {
                                    $label = $fareRef[$item['Application']['value']] ?? 'Unknown';
                                    $amountData = $item['Amounts']['Amount'] ?? [];

                                    return [
                                        $label => [
                                            'amountApplication' => $amountData['AmountApplication']['value'] ?? null,
                                            'price' => [
                                                'amount' => $this->formatMinorUnitsAmount(
                                                    $currencyFormat,
                                                    $amountData['CurrencyAmountValue']['@attributes']['Code'] ?? null,
                                                    $amountData['CurrencyAmountValue']['value'] ?? null
                                                ),
                                                'code' => $amountData['CurrencyAmountValue']['@attributes']['Code'] ?? null,
                                            ]
                                        ]
                                    ];
                                })
                                ->whenEmpty(fn() => collect(['Status' => 'No change allowed']))
                                ->toArray(),

                            'refundFee' => collect($fare['FareRules']['Penalty']['Details']['Detail'] ?? [])
                                ->filter(fn($item) => ($item['Type']['value'] ?? '') === 'Refund' && isset($item['Application']['value']))
                                ->mapWithKeys(function ($item) use ($fareRef) {
                                    $label = $fareRef[$item['Application']['value']] ?? 'Unknown';
                                    $amountData = $item['Amounts']['Amount'] ?? [];

                                    return [
                                        $label => [
                                            'amountApplication' => $amountData['AmountApplication']['value'] ?? null,
                                            'price' => [
                                            'amount' => $this->formatMinorUnitsAmount($currencyFormat, ($amountData['CurrencyAmountValue']['@attributes']['Code'] ?? null), ($amountData['CurrencyAmountValue']['value'] ?? null)),
                                                'code' => $amountData['CurrencyAmountValue']['@attributes']['Code'] ?? null,
                                            ]
                                        ]
                                    ];
                                })
                            ->whenEmpty(fn() => collect(['Status' => 'Non-refundable']))
                            ->toArray(),
                        ],
                    ];
                })->values()->all()
            ];
            $services = isset($item['Service'][0]) ? $item['Service'] : [$item['Service']];
            $formattedServices = null;
            // if (!empty($serviceItems) && $serviceItems->filter()->isNotEmpty()) {
                $formattedServices = collect($services)->map(function ($service) use ($serviceItems) {
                    $serviceID = $service['@attributes']['ServiceID'] ?? '';
                    $passengerRefs = $service['PassengerRefs']['value'] ?? $service['PassengerRef']['value'] ?? '';
                    if (!isset($service['ServiceDefinitionRef'])) {
                        return [
                            'id' => $serviceID,
                            'passengerRefs' => $passengerRefs,
                            'details' => null,
                        ];
                    }
                    $definitionID = $service['ServiceDefinitionRef']['value'] ?? null;
                    $details = [];
                    if ($definitionID) {
                        $matched = $serviceItems->firstWhere('@attributes.ServiceDefinitionID', $definitionID);
                        if ($matched) {
                            $descriptionsRaw = $matched['Descriptions']['Description'] ?? [];
                            $descriptions = isset($descriptionsRaw[0]) ? $descriptionsRaw : [$descriptionsRaw];
                            $descriptionsCollection = collect($descriptions);
                            $typeEntry = $descriptionsCollection->firstWhere('Application.value', 'Type');
                            $name = $typeEntry['Text']['value'] ?? '';
                            $application = $typeEntry['Application']['value'] ?? '';
                            $detailsList = $descriptionsCollection
                                ->filter(fn($desc) => ($desc['Application']['value'] ?? '') === 'Details')
                                ->pluck('Text.value')->filter()->values()->first();
                            if ($application && $name) {
                                $details[$application] = $name;
                            }
                
                            if ($detailsList) {
                                $details['details'] = $detailsList;
                            }
                            
                        }
                    }
                    if (empty($details)) {
                        return null;
                    }
                
                    return [
                        'id' => $serviceID,
                        'passengerRefs' => $passengerRefs,
                        'details' => $details,
                    ];
                })->filter()->values()->all();
            // } else {
            //     $formattedServices = [
            //         'id' => $service['@attributes']['ServiceID'] ?? '',
            //         'passengerRefs' => $passengerRefs,
            //         'details' => null,
            //     ];
            //     dd('ggs');
            // }
            // dd($fareDetail);
            $offer = [
                'id' => $offerItemID,
                'totalPrice' => ['code' => $currency, 'amount' => $price],
                'services' => $formattedServices ?? ($services ?? ''),
                'fareDetail' => $fareDetail ?? '',
            ];
            if (isset($item['TimeLimits'])) {
                $offer['timeLimits'] = [
                    'paymentTimeLimit' => $item['TimeLimits']['PaymentTimeLimit']['@attributes']['Timestamp'] ?? null,
                    'ticketingTimeLimit' => $item['TimeLimits']['TicketingTimeLimits']['@attributes']['Timestamp'] ?? null
                ];
            }
            $offers[] = $offer;
        }
        // dd($offers);
        return $offers;
    }
    private function updateBaggageAllowance($allowance, $baggages)
    {
        $refId = $allowance['BaggageAllowanceRef']['value'] ?? null;
        if ($refId) {
            $baggageDetail = $baggages->firstWhere('@attributes.BaggageAllowanceID', $refId);
            if ($baggageDetail) {
                $allowance['baggage_detail'] = $baggageDetail;
            }
        }
        return $allowance;
    }
    private function formatOfferTag($data, $offerId, $responseId)
    {
        // dd($data, $offerId, $responseId);
        $resId = $responseId ?? session('responseId') ?? '';
        $offer = '<Offer OfferID="' . $offerId['OfferID'] . '" Owner="' . $offerId['Owner'] . '" ResponseID="' . $resId . '">';
        foreach ($data as $item) {
            $offer .= '<OfferItem OfferItemID="' . $item['id'] . '">
                            <PassengerRefs>' . $item['PassengerRef'] . '</PassengerRefs>
                    </OfferItem>';
        }
        $offer .= '</Offer>';
        return $offer;
    }
    private function getPaxContactTag(array $paxCount, array $passengers): string
    {
        $xml = "<PassengerList>";
        $adtIds = [];
        $paxId = 1;
        $infantIndex = 0;

        foreach ($passengers as $pax) {
            $type = strtolower($pax['type']);
            $gender = ($pax['title'] === 'Mr') ? 'Male' : 'Female';
            $title = strtoupper($pax['title']);
            $name = strtoupper(preg_replace("/[^a-zA-Z]/", '', $pax['name']));
            $surname = strtoupper(preg_replace("/[^a-zA-Z]/", '', $pax['surname']));
            $dob = $pax['dob'];
            $country = $pax['nationality'] ?? 'PK';
            $ref = 'CID1';
    
            if ($type === 'adult') {
                $id = "T$paxId";
                $adtIds[] = $id;
    
                $xml .= "<Passenger PassengerID=\"$id\">
                            <PTC>ADT</PTC>
                            <ResidenceCountryCode>$country</ResidenceCountryCode>
                            <Individual>
                                <Birthdate>$dob</Birthdate>
                                <Gender>$gender</Gender>
                                <NameTitle>$title</NameTitle>
                                <GivenName>$name</GivenName>
                                <Surname>$surname</Surname>
                            </Individual>
                            <ContactInfoRef>$ref</ContactInfoRef>";
    
                if ($infantIndex < ($paxCount['inf'] ?? 0)) {
                    $xml .= "<InfantRef>{$id}.1</InfantRef>";
                    $infantIndex++;
                }
    
                $xml .= "</Passenger>";
                $paxId++;
            }
    
            elseif ($type === 'child') {
                $id = "T$paxId";
                $xml .= "<Passenger PassengerID=\"$id\">
                            <PTC>CNN</PTC>
                            <ResidenceCountryCode>$country</ResidenceCountryCode>
                            <Individual>
                                <Birthdate>$dob</Birthdate>
                                <Gender>$gender</Gender>
                                <NameTitle>$title</NameTitle>
                                <GivenName>$name</GivenName>
                                <Surname>$surname</Surname>
                            </Individual>
                            <ContactInfoRef>$ref</ContactInfoRef>
                        </Passenger>";
                $paxId++;
            }
        }
    
        $infantIndex = 0;
        foreach ($passengers as $pax) {
            $type = strtolower($pax['type']);
            if ($type !== 'infant') continue;
    
            if (!isset($adtIds[$infantIndex])) {
                continue;
            }
    
            $gender = ($pax['title'] === 'Mr') ? 'Male' : 'Female';
            $title = strtoupper($pax['title']);
            $name = strtoupper(preg_replace("/[^a-zA-Z]/", '', $pax['name']));
            $surname = strtoupper(preg_replace("/[^a-zA-Z]/", '', $pax['surname']));
            $dob = $pax['dob'];
            $country = $pax['nationality'] ?? 'PK';
            $ref = 'CID1';
    
            $id = $adtIds[$infantIndex] . '.1';
            $xml .= "<Passenger PassengerID=\"$id\">
                        <PTC>INF</PTC>
                        <ResidenceCountryCode>$country</ResidenceCountryCode>
                        <Individual>
                            <Birthdate>$dob</Birthdate>
                            <Gender>$gender</Gender>
                            <NameTitle>$title</NameTitle>
                            <GivenName>$name</GivenName>
                            <Surname>$surname</Surname>
                        </Individual>
                        <ContactInfoRef>$ref</ContactInfoRef>
                    </Passenger>";
            $infantIndex++;
        }
    
        return $xml . "</PassengerList>";
    }
    // private function formatBookFlight($data)
    // {
    //     // dd($data);
    //     if (empty($data) || empty($data['destinationList']) || empty($data['flights']) || empty($data['flightSegments']) || empty($data['offers']) || empty($data['baggageList']) || empty($data['priceClass'])) return 'Something missing';

    //     $destinations = isset($data['destinationList'][0]) ? $data['destinationList'] : [$data['destinationList']];
    //     $flights = collect(isset($data['flights'][0]) ? $data['flights'] : [$data['flights']]);
    //     $segments = collect(isset($data['flightSegments'][0]) ? $data['flightSegments'] : [$data['flightSegments']]);
    //     $offers = collect(isset($data['offers'][0]) ? $data['offers'] : [$data['offers']]);
    //     $baggages = collect(isset($data['baggageList'][0]) ? $data['baggageList'] : [$data['baggageList']]);
    //     $priceClass = collect(isset($data['priceClass'][0]) ? $data['priceClass'] : [$data['priceClass']]);
    //     $passengers = collect(isset($data['passengers'][0]) ? $data['passengers'] : [$data['passengers']]);
    //     // dd($passengers);
    //     $serviceList = collect($data['serviceList']) ?? null;
    //     $responseId = $data['responseId'] ?? null;
    //     $request = $data['request'];
    //     // dd($destinations, $flights, $segments, $offers, $baggages, $priceClass, $responseId);
    //     $timeZone = config('variables.setting.timezone') ?? 'Asia/Karachi';
    //     $data = [];
    //     $tax = config('variables.flyjinnah_api.tax') ?? 0;
    //     $matchedOffers = '';
    //     foreach ($destinations as $item) {
    //         $flightIds = explode(' ', $item['FlightReferences']['value'] ?? ''); // fetch bundle with this Aliiiiiiiiiii);
    //         $matchedFlights = $flights->filter(fn($flight) => in_array($flight['@attributes']['FlightKey'] ?? null, $flightIds))->values();
    //         $flightSegmentReferences = $matchedFlights->map(fn($flight) => explode(' ', $flight['SegmentReferences']['value'] ?? ''))->values();
    //         // dd($flightSegmentReferences);
    //         $segmentDetails=[];
    //         foreach ($flightSegmentReferences as $segmentIds) {
    //             $flightSegments = collect($segmentIds)
    //                 ->map(fn($id) => $segments->firstWhere('@attributes.SegmentKey', $id))
    //                 ->filter()
    //                 ->values();

    //             $relatedFlightKeys = $matchedFlights
    //                 ->filter(fn($flight) => !array_diff($segmentIds, explode(' ', $flight['SegmentReferences']['value'] ?? '')))
    //                 ->pluck('@attributes.FlightKey')
    //                 ->all();
    //             // dd($relatedFlightKeys);

    //             $matchedOffers = $offers
    //                 ->filter(function ($offer) use ($relatedFlightKeys) {
    //                     return isset($offer['FlightsOverview']['FlightRef']['value'])
    //                         ? in_array($offer['FlightsOverview']['FlightRef']['value'], $relatedFlightKeys)
    //                         : true;
    //                 })
    //                 ->map(function ($offer) use ($baggages, $priceClass, $serviceList, $passengers) {
    //                     $offer['BaggageAllowance'] = collect($offer['BaggageAllowance'] ?? [])
    //                         ->map(fn($allowance) => $this->updateBaggageAllowance($allowance, $baggages))
    //                         ->all();
    //                     // dd($offer);
    //                     // $item = isset($offer['OfferItem'][0]) ? $offer['OfferItem'][0] : $offer['OfferItem'];
    //                     $priceClassRef = $offer['FlightsOverview']['FlightRef']['@attributes']['PriceClassRef'] ?? null;
    //                     $offer['priceClass'] = $priceClassRef ? $priceClass->where('@attributes.PriceClassID', $priceClassRef)->values()->first() : '';
    //                     return [
    //                         'offerID' => $offer['@attributes'] ?? null,
    //                         'parameters' => $offer['Parameters'] ?? null,
    //                         'timeLimits' => $offer['TimeLimits'] ?? null,
    //                         'totalPrice' =>  [
    //                             'code' => $offer['TotalPrice']['DetailCurrencyPrice']['Total']['@attributes']['Code'] ?? '',
    //                             'amount' => $offer['TotalPrice']['DetailCurrencyPrice']['Total']['value'] ?? '',
    //                         ],
    //                         'offerItem' => $this->formatOfferItems2($offer['OrderItems'], $serviceList, $passengers),
    //                         'baggageAllowance' => $offer['BaggageAllowance'],
    //                         'priceClass' => $offer['priceClass'],
    //                     ];
    //                 })->values();
    //             $expTime = isset($matchedOffers->first()['timeLimits']['OfferExpiration']['@attributes']['DateTime']) ? Carbon::parse($matchedOffers->first()['timeLimits']['OfferExpiration']['@attributes']['DateTime'])->setTimezone($timeZone) : now()->addMinutes(20);
    //             session([
    //                 'IdsExpireTimeEmi' => $expTime,
    //             ]);
    //             // dd($matchedOffers);
    //             $lowestPrice = [
    //                 'code' => $matchedOffers->min(fn($offer) => data_get($offer, 'totalPrice.code', 'PKR')),
    //                 'amount' => $matchedOffers->min(fn($offer) => (float) data_get($offer, 'totalPrice.amount', 0)) + $tax
    //             ];
    //             if ($flightSegments->isNotEmpty()) {
    //                 $secondFlight = [];
    //                 if (count($flightSegments) > 1) {
    //                     $last = $flightSegments->last();
    //                     $secondFlight = [
    //                         'departure' => $last['Departure'] ?? [],
    //                         'arrival' => $last['Arrival'] ?? [],
    //                         'isConnected' => isset($last['@attributes']['ConnectInd']) ? filter_var($last['@attributes']['ConnectInd'], FILTER_VALIDATE_BOOLEAN) : null,
    //                         'details' => $last['FlightDetail'] ?? [],
    //                         'equipment' => $last['Equipment'] ?? [],
    //                         'marketingCarrier' => $last['MarketingCarrier'] ?? [],
    //                     ];
    //                 }
    //                 $first = $flightSegments->first();
    //                 $totalMinutes = 0;
    //                 try {
    //                     $d1 = new \DateInterval($first['FlightDetail']['FlightDuration']['Value']['value'] ?? 'PT0M');
    //                     $totalMinutes += ($d1->h * 60) + $d1->i;
    //                 } catch (\Exception $e) {
    //                     \Log::error('Exception in calculate duration', ['message' => $e->getMessage()]);
    //                 }

    //                 if (!empty($secondFlight) && isset($secondFlight['details']['FlightDuration']['Value']['value'])) {
    //                     try {
    //                         $d2 = new \DateInterval($secondFlight['details']['FlightDuration']['Value']['value']);
    //                         $totalMinutes += ($d2->h * 60) + $d2->i;
    //                     } catch (\Exception $e) {
    //                         \Log::error('Exception in calculate duration', ['message' => $e->getMessage()]);
    //                     }
    //                 }

    //                 $hours = floor($totalMinutes / 60);
    //                 $minutes = $totalMinutes % 60;
    //                 $duration = ($hours > 0 ? $hours . 'h ' : '') . ($minutes > 0 ? $minutes . 'm' : '');
    //                 $segmentDetails[] = [
    //                     'Departure' => $first['Departure'] ?? [],
    //                     'Arrival' => $flightSegments->last()['Arrival'] ?? [],
    //                     'segmentKey' => $first['@attributes']['SegmentKey'] ?? [],
    //                     'flightDetails' => [
    //                         'isConnected' => filter_var($first['@attributes']['ConnectInd'], FILTER_VALIDATE_BOOLEAN),
    //                         'details' => $first['FlightDetail'] ?? [],
    //                         'equipment' => $first['Equipment'] ?? [],
    //                         'marketingCarrier' => $first['MarketingCarrier'] ?? [],
    //                     ],
    //                     'secondFlight' => $secondFlight,
    //                     'duration' => $duration ?? '',
    //                     'price' => $lowestPrice,
    //                     'bundles' => $request === 1 ? $matchedOffers->all() : [],
    //                 ];
    //             }
    //         }
    //         if($request === 2 || $request === 3) {
    //             $data['segments'][] = [
    //                 'departureCode' => $this->helperService->codeToCountry($item['DepartureCode']),
    //                 'arrivalCode' => $this->helperService->codeToCountry($item['ArrivalCode']),
    //                 'flights' => collect($segmentDetails)->first(),
    //                 'responseId' => $responseId,
    //             ];
    //         } else {
    //             $data[] = [
    //                 'departureCode' => $this->helperService->codeToCountry($item['DepartureCode']),
    //                 'arrivalCode' => $this->helperService->codeToCountry($item['ArrivalCode']),
    //                 'flights' => $segmentDetails,
    //                 'responseId' => $responseId,
    //             ];
    //         }
    //     }
    //     if($request === 2 || $request === 3) {
    //         $data['bundle'] = $matchedOffers->first();
    //         $expTime = isset($data['bundle']['timeLimits']['OfferExpiration']['@attributes']['DateTime']) ? Carbon::parse($data['bundle']['timeLimits']['OfferExpiration']['@attributes']['DateTime'])->setTimezone($timeZone) : now()->addMinutes(20);
    //         session([
    //             'IdsExpireTimeEmi' => $expTime,
    //         ]);
    //     }
    //     if($request === 3) {
    //         $data['passengers'] = [];
    //         foreach ($passengers as $pax) {
    //             $data['passengers'][] = [
    //                 'id' => $pax['@attributes']['PassengerID'] ?? '',
    //                 'type' => $pax['PTC']['value'] ?? '',
    //                 'birthdate' => $pax['Birthdate']['value'] ?? '',
    //                 'gender' => $pax['Individual']['Gender']['value'] ?? '',
    //                 'givenName' => $pax['Individual']['GivenName']['value'] ?? '',
    //                 'surname' => $pax['Individual']['Surname']['value'] ?? '',
    //                 'title' => $pax['Individual']['NameTitle']['value'] ?? '',
    //                 'contactRef' => $pax['ContactInfoRef']['value'] ?? null,
    //                 'infantRef' => $pax['InfantRef']['value'] ?? null,
    //             ];
    //         }
    //     }
    //     // dd($data);
    //     return $data;
    // }
    private function formatMinorUnitsAmount($currencyFormat, $code, $amount)
    {
        // dd($currencyFormat, $code, $amount);
        if (!is_numeric($amount)) {
            preg_match('/\d+/', $amount, $matches);
            if (empty($matches)) {
                return $amount;
            }
            $amount = (int)$matches[0];
        }

        if (empty($currencyFormat['CurrencyMetadata']) || !$code) {
            return $amount;
        }

        $metadata = collect($currencyFormat['CurrencyMetadata'])
            ->firstWhere(fn($item) => ($item['@attributes']['MetadataKey'] ?? '') === $code);

        $decimals = $metadata ? intval($metadata['Decimals']['value'] ?? 2) : 2;

        $formattedAmount = $amount / pow(10, $decimals);
        return number_format($formattedAmount, $decimals, '.', '');
    }
    private function ticketInfos($ticketInfos)
    {
        // dd($ticketInfos);
        if (empty($ticketInfos)) return [];
        $ticketInfos = isset($ticketInfos[0]) ? $ticketInfos : [$ticketInfos];
        $tickets = [];
        foreach ($ticketInfos as $ticketInfo) {
            $couponInfo = $ticketInfo['TicketDocument']['CouponInfo'] ?? [];
            $couponInfo = isset($couponInfo[0]) ? $couponInfo : [$couponInfo];
            $tickets[] = [
                'passengerReference' => $ticketInfo['PassengerReference']['value'] ?? '',
                'agentId' => [
                    'type' => $ticketInfo['AgentIDs']['AgentID']['Type']['value'] ?? '',
                    'id' => $ticketInfo['AgentIDs']['AgentID']['ID']['value'] ?? '',
                ],
                'issuingAirlineInfo' => [
                    'airline' => $ticketInfo['IssuingAirlineInfo']['AirlineName']['value'] ?? '',
                    'place' => $ticketInfo['IssuingAirlineInfo']['Place']['value'] ?? '',
                ],
                'price' => [
                    'passengerReferences' => $ticketInfo['Price']['PassengerReferences']['refs'] ?? '',
                    'refs' => $ticketInfo['Price']['@attributes']['refs'] ?? '',
                    'total' => [
                        'code' => $ticketInfo['Price']['Total']['@attributes']['Code'] ?? '',
                        'value' => $ticketInfo['Price']['Total']['value'] ?? '',
                    ],
                    'details' => [
                        'application' => $ticketInfo['Price']['Details']['Detail']['Application']['value'] ?? '',
                        'amount' => [
                            'code' => $ticketInfo['Price']['Details']['Detail']['Amount']['@attributes']['Code'] ?? '',
                            'value' => $ticketInfo['Price']['Details']['Detail']['Amount']['value'] ?? '',
                        ],
                        'taxes' => [
                            'total' => [
                                'code' => $ticketInfo['Price']['Taxes']['Total']['@attributes']['Code'] ?? '',
                                'value' => $ticketInfo['Price']['Taxes']['Total']['value'] ?? '',
                            ],
                            'breakdown' => collect($ticketInfo['Price']['Taxes']['Breakdown']['Tax'] ?? [])
                                ->map(fn($tax) => [
                                    'amount' => [
                                        'code' => $tax['Amount']['@attributes']['Code'] ?? '',
                                        'value' => $tax['Amount']['value'] ?? 0,
                                    ],
                                    'taxCode' => $tax['TaxCode']['value'] ?? '',
                                    'description' => $tax['Description']['value'] ?? '',
                                ])->all(),
                        ],
                        'fees' => [
                            'code' => $ticketInfo['Price']['Fees']['Total']['@attributes']['Code'] ?? '',
                            'value' => $ticketInfo['Price']['Fees']['Total']['value'] ?? 0,
                        ],
                    ],
                ],
                'ticketDocument' => [
                    'ticketDocNbr' => $ticketInfo['TicketDocument']['TicketDocNbr']['value'] ?? '',
                    'type' => $ticketInfo['TicketDocument']['Type']['value'] ?? '',
                    'numberOfBooklets' => $ticketInfo['TicketDocument']['NumberofBooklets']['value'] ?? '',
                    'dateOfIssue' => $ticketInfo['TicketDocument']['DateOfIssue']['value'] ?? '',
                    'timeOfIssue' => $ticketInfo['TicketDocument']['TimeOfIssue']['value'] ?? '',
                    'ticketingLocation' => $ticketInfo['TicketDocument']['TicketingLocation']['value'] ?? '',
                    'reportingType' => $ticketInfo['TicketDocument']['ReportingType']['value'] ?? '',
                    'couponInfo' => collect($couponInfo)->map(fn($coupon) => [
                        'couponNumber' => $coupon['CouponNumber']['value'] ?? '',
                        'couponReference' => $coupon['CouponReference']['value'] ?? '',
                        'fareBasisCode' => $coupon['FareBasisCode']['Code']['value'] ?? '',
                        'status' => $coupon['Status']['value'] ?? '',
                        'validatingAirline' => $coupon['ValidatingAirline']['value'] ?? '',
                        'couponMedia' => $coupon['CouponMedia']['value'] ?? '',
                        'couponValid' => [
                            'effective' => $coupon['CouponValid']['EffectiveDatePeriod']['Effective']['value'] ?? '',
                            'expiration' => $coupon['CouponValid']['EffectiveDatePeriod']['Expiration']['value'] ?? '',
                        ],
                        'currentAirlineInfo' => [
                            'departureDateTime' => isset($coupon['CurrentAirlineInfo']['DepartureDateTime']['@attributes'])
                                ? Carbon::parse($coupon['CurrentAirlineInfo']['DepartureDateTime']['@attributes']['ShortDate'] . ' ' . $coupon['CurrentAirlineInfo']['DepartureDateTime']['@attributes']['Time'] ?? null)
                                : null,
                            'arrivalDateTime' => isset($coupon['CurrentAirlineInfo']['ArrivalDateTime']['@attributes'])
                                ? Carbon::parse($coupon['CurrentAirlineInfo']['ArrivalDateTime']['@attributes']['ShortDate'] . ' ' . $coupon['CurrentAirlineInfo']['ArrivalDateTime']['@attributes']['Time'] ?? null)
                                : null,
                            'status' => $coupon['CurrentAirlineInfo']['Status']['value'] ?? '',
                            'departure' => [
                                'code' => $coupon['CurrentAirlineInfo']['Departure']['AirportCode']['value'] ?? '',
                                'date' => $coupon['CurrentAirlineInfo']['Departure']['Date']['value'] ?? '',
                                'time' => $coupon['CurrentAirlineInfo']['Departure']['Time']['value'] ?? '',
                                'airport' => $coupon['CurrentAirlineInfo']['Departure']['AirportName']['value'] ?? '',
                                'terminal' => $coupon['CurrentAirlineInfo']['Departure']['Terminal']['Name']['value'] ?? '',
                            ],
                            'arrival' => [
                                'code' => $coupon['CurrentAirlineInfo']['Arrival']['AirportCode']['value'] ?? '',
                                'date' => $coupon['CurrentAirlineInfo']['Arrival']['Date']['value'] ?? '',
                                'time' => $coupon['CurrentAirlineInfo']['Arrival']['Time']['value'] ?? '',
                                'airport' => $coupon['CurrentAirlineInfo']['Arrival']['AirportName']['value'] ?? '',
                                'terminal' => $coupon['CurrentAirlineInfo']['Arrival']['Terminal']['Name']['value'] ?? '',
                            ],
                            'marketingCarrier' => [
                                'name' => $coupon['CurrentAirlineInfo']['MarketingCarrier']['Name']['value'] ?? '',
                                'airlineID' => $coupon['CurrentAirlineInfo']['MarketingCarrier']['AirlineID']['value'] ?? '',
                                'flightNumber' => $coupon['CurrentAirlineInfo']['MarketingCarrier']['FlightNumber']['value'] ?? '',
                                'resBookDesigCode' => $coupon['CurrentAirlineInfo']['MarketingCarrier']['ResBookDesigCode']['value'] ?? '',
                            ],
                            'equipment' => [
                                'name' => $coupon['CurrentAirlineInfo']['Equipment']['Name']['value'] ?? '',
                                'AircraftCode' => $coupon['CurrentAirlineInfo']['Equipment']['AircraftCode']['value'] ?? '',
                            ],
                        ],
                        'AddlBaggageInfo' => [
                            'number' => $coupon['AddlBaggageInfo']['AllowableBag']['@attributes']['Number'] ?? '',
                            'type' => $coupon['AddlBaggageInfo']['AllowableBag']['@attributes']['Type'] ?? '',
                        ],
                    ])->all(),
                ]
            ];
        }
        return $tickets;
    }
    public function getCarrierName()
    {
        return 'emirates';
    }
}

// AirShoppingRQ > OfferPriceRQ > OrderCreateRQ > OrderRetrieveRQ > OrderChangeRQ > OrderCancelRQ