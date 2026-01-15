<div class="modal-body">
    @php
        $bookingRequest = $booking->bookingRequest ?? null;
        $xmlBody = $bookingRequest && isset($bookingRequest->xml_body) ? json_decode($bookingRequest->xml_body, true) : null;
        $airline = strtolower($booking->airline);
    @endphp
    @if ($bookingRequest)
        @if ($airline === 'emirates')
            <div class="accordion" id="bookingAccordion">
                <!-- General Booking Information -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="generalInfoHeading">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#generalInfo" aria-expanded="true" aria-controls="generalInfo">
                            General Booking Information Emirates
                        </button>
                    </h2>
                    <div id="generalInfo" class="accordion-collapse collapse show" aria-labelledby="generalInfoHeading" data-bs-parent="#bookingAccordion">
                        <div class="accordion-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><strong>ID:</strong> {{ $bookingRequest->id ?? 'N/A' }}</li>
                                <li class="list-group-item"><strong>Airline:</strong>
                                    {{ $bookingRequest->airline ?? 'N/A' }}</li>
                                <li class="list-group-item"><strong>Ticket Limit:</strong>
                                    {{ isset($bookingRequest->ticket_limit) ? \Carbon\Carbon::parse($bookingRequest->ticket_limit)->format('d M Y, H:i') : 'N/A' }}
                                </li>
                                <li class="list-group-item"><strong>Payment Limit:</strong>
                                    {{ isset($bookingRequest->payment_limit) ? \Carbon\Carbon::parse($bookingRequest->payment_limit)->format('d M Y, H:i') : 'N/A' }}
                                </li>
                                <li class="list-group-item"><strong>Status:</strong>
                                    {{ isset($bookingRequest->status) ? ucfirst($bookingRequest->status) : 'N/A' }}</li>
                                <li class="list-group-item"><strong>Client ID:</strong>
                                    {{ $bookingRequest->client_id ?? 'N/A' }}</li>
                                <li class="list-group-item"><strong>Booking ID:</strong>
                                    {{ $bookingRequest->booking_id ?? 'N/A' }}</li>
                                <li class="list-group-item"><strong>Created At:</strong>
                                    {{ isset($bookingRequest->created_at) ? \Carbon\Carbon::parse($bookingRequest->created_at)->format('d M Y, H:i') : 'N/A' }}
                                </li>
                                <li class="list-group-item"><strong>Updated At:</strong>
                                    {{ isset($bookingRequest->updated_at) ? \Carbon\Carbon::parse($bookingRequest->updated_at)->format('d M Y, H:i') : 'N/A' }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Passenger Information -->
                @if ($xmlBody && isset($xmlBody['passengers']) && !empty($xmlBody['passengers']))
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="passengerInfoHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#passengerInfo" aria-expanded="false" aria-controls="passengerInfo">
                                Passenger Information
                            </button>
                        </h2>
                        <div id="passengerInfo" class="accordion-collapse collapse"
                            aria-labelledby="passengerInfoHeading" data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                @foreach ($xmlBody['passengers'] as $passenger)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            Passenger {{ $passenger['id'] ?? 'Unknown' }}
                                            ({{ $passenger['type'] ?? 'N/A' }})
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>Name:</strong>
                                                    {{ isset($passenger['title']) ? $passenger['title'] : '' }}
                                                    {{ $passenger['givenName'] ?? '' }}
                                                    {{ $passenger['surname'] ?? '' }}</li>
                                                <li class="list-group-item"><strong>Birthdate:</strong>
                                                    {{ isset($passenger['birthdate']) ? \Carbon\Carbon::parse($passenger['birthdate'])->format('d M Y') : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Gender:</strong>
                                                    {{ $passenger['gender'] ?? 'N/A' }}</li>
                                                <li class="list-group-item"><strong>Contact Ref:</strong>
                                                    {{ $passenger['contactRef'] ?? 'N/A' }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">No passenger information available.</div>
                @endif

                <!-- Flight Segments -->
                @if ($xmlBody && isset($xmlBody['segments']) && !empty($xmlBody['segments']))
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="segmentsHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#segments" aria-expanded="false" aria-controls="segments">
                                Flight Segments
                            </button>
                        </h2>
                        <div id="segments" class="accordion-collapse collapse" aria-labelledby="segmentsHeading"
                            data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                @foreach ($xmlBody['segments'] as $index => $segment)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            Segment: {{ $segment['departureCode'] ?? 'N/A' }} to
                                            {{ $segment['arrivalCode'] ?? 'N/A' }}
                                        </div>
                                        <div class="card-body">
                                            <h6>Flight Details</h6>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>Departure:</strong>
                                                    {{ $segment['flights']['Departure']['AirportName']['value'] ?? 'N/A' }}
                                                    ({{ $segment['flights']['Departure']['AirportCode']['value'] ?? 'N/A' }})
                                                    on
                                                    {{ isset($segment['flights']['Departure']['Date']['value']) ? \Carbon\Carbon::parse($segment['flights']['Departure']['Date']['value'])->format('d M Y') : 'N/A' }}
                                                    at {{ $segment['flights']['Departure']['Time']['value'] ?? 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Arrival:</strong>
                                                    {{ $segment['flights']['Arrival']['AirportName']['value'] ?? 'N/A' }}
                                                    ({{ $segment['flights']['Arrival']['AirportCode']['value'] ?? 'N/A' }})
                                                    on
                                                    {{ isset($segment['flights']['Arrival']['Date']['value']) ? \Carbon\Carbon::parse($segment['flights']['Arrival']['Date']['value'])->format('d M Y') : 'N/A' }}
                                                    at {{ $segment['flights']['Arrival']['Time']['value'] ?? 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Duration:</strong>
                                                    {{ $segment['duration'] ?? 'N/A' }}</li>
                                                <li class="list-group-item"><strong>Price:</strong>
                                                    {{ isset($segment['price']) ? $segment['price']['code'] . ' ' . number_format($segment['price']['amount'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Aircraft:</strong>
                                                    {{ $segment['flights']['equipment']['Name']['value'] ?? 'N/A' }}
                                                    ({{ $segment['flights']['equipment']['AircraftCode']['value'] ?? 'N/A' }})
                                                </li>
                                                <li class="list-group-item"><strong>Carrier:</strong>
                                                    {{ $segment['flights']['marketingCarrier']['Name']['value'] ?? 'N/A' }}
                                                    (Flight
                                                    {{ $segment['flights']['marketingCarrier']['FlightNumber']['value'] ?? 'N/A' }})
                                                </li>
                                            </ul>
                                            @if (isset($segment['flights']['secondFlight']))
                                                <h6 class="mt-3">Connecting Flight</h6>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"><strong>Departure:</strong>
                                                        {{ $segment['flights']['secondFlight']['departure']['AirportName']['value'] ?? 'N/A' }}
                                                        ({{ $segment['flights']['secondFlight']['departure']['AirportCode']['value'] ?? 'N/A' }})
                                                        on
                                                        {{ isset($segment['flights']['secondFlight']['departure']['Date']['value']) ? \Carbon\Carbon::parse($segment['flights']['secondFlight']['departure']['Date']['value'])->format('d M Y') : 'N/A' }}
                                                        at
                                                        {{ $segment['flights']['secondFlight']['departure']['Time']['value'] ?? 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Arrival:</strong>
                                                        {{ $segment['flights']['secondFlight']['arrival']['AirportName']['value'] ?? 'N/A' }}
                                                        ({{ $segment['flights']['secondFlight']['arrival']['AirportCode']['value'] ?? 'N/A' }})
                                                        on
                                                        {{ isset($segment['flights']['secondFlight']['arrival']['Date']['value']) ? \Carbon\Carbon::parse($segment['flights']['secondFlight']['arrival']['Date']['value'])->format('d M Y') : 'N/A' }}
                                                        at
                                                        {{ $segment['flights']['secondFlight']['arrival']['Time']['value'] ?? 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Aircraft:</strong>
                                                        {{ $segment['flights']['secondFlight']['equipment']['Name']['value'] ?? 'N/A' }}
                                                        ({{ $segment['flights']['secondFlight']['equipment']['AircraftCode']['value'] ?? 'N/A' }})
                                                    </li>
                                                    <li class="list-group-item"><strong>Carrier:</strong>
                                                        {{ $segment['flights']['secondFlight']['marketingCarrier']['Name']['value'] ?? 'N/A' }}
                                                        (Flight
                                                        {{ $segment['flights']['secondFlight']['marketingCarrier']['FlightNumber']['value'] ?? 'N/A' }})
                                                    </li>
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">No flight segments available.</div>
                @endif

                <!-- Pricing Information -->
                @if ($xmlBody && isset($xmlBody['ticketInfos']) && !empty($xmlBody['ticketInfos']))
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="pricingInfoHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#pricingInfo" aria-expanded="false" aria-controls="pricingInfo">
                                Pricing Information
                            </button>
                        </h2>
                        <div id="pricingInfo" class="accordion-collapse collapse" aria-labelledby="pricingInfoHeading"
                            data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                @foreach ($xmlBody['ticketInfos'] as $ticket)
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            Ticket for Passenger {{ $ticket['passengerReference'] ?? 'N/A' }}
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>Ticket Number:</strong>
                                                    {{ $ticket['ticketDocument']['ticketDocNbr'] ?? 'N/A' }}</li>
                                                <li class="list-group-item"><strong>Base Fare:</strong>
                                                    {{ isset($ticket['price']['details']['amount']) ? $ticket['price']['details']['amount']['code'] . ' ' . number_format($ticket['price']['details']['amount']['value'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Total Price:</strong>
                                                    {{ isset($ticket['price']['total']) ? $ticket['price']['total']['code'] . ' ' . number_format($ticket['price']['total']['value'], 2) : 'N/A' }}
                                                </li>
                                            </ul>
                                            @if (isset($ticket['price']['details']['taxes']['breakdown']) &&
                                                    !empty($ticket['price']['details']['taxes']['breakdown']))
                                                <h6 class="mt-3">Tax Breakdown</h6>
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($ticket['price']['details']['taxes']['breakdown'] as $tax)
                                                        <li class="list-group-item">
                                                            {{ $tax['description'] ?? 'Unknown Tax' }}:
                                                            {{ isset($tax['amount']) ? $tax['amount']['code'] . ' ' . number_format($tax['amount']['value'], 2) : 'N/A' }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p>No tax breakdown available.</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">No pricing information available.</div>
                @endif

                <!-- Penalties -->
                @if ($xmlBody && isset($xmlBody['bundle']['offerItem']) && !empty($xmlBody['bundle']['offerItem']))
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="penaltiesHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#penalties" aria-expanded="false" aria-controls="penalties">
                                Penalties
                            </button>
                        </h2>
                        <div id="penalties" class="accordion-collapse collapse" aria-labelledby="penaltiesHeading"
                            data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                @foreach ($xmlBody['bundle']['offerItem'] as $offer)
                                    @if (isset($offer['fareDetail']['penalties']) && !empty($offer['fareDetail']['penalties']))
                                        @foreach ($offer['fareDetail']['penalties'] as $penalty)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    {{ $penalty['arrival'] ?? 'N/A' }} to
                                                    {{ $penalty['destination'] ?? 'N/A' }}
                                                    ({{ $penalty['cabinType'] ?? 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <h6>Cancellation Fees</h6>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Prior to
                                                                Departure:</strong>
                                                            {{ isset($penalty['fareRules']['cancelFee']['Prior to Departure']['price']) ? $penalty['fareRules']['cancelFee']['Prior to Departure']['price']['code'] . ' ' . number_format($penalty['fareRules']['cancelFee']['Prior to Departure']['price']['amount'], 2) : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>No Show:</strong>
                                                            {{ isset($penalty['fareRules']['cancelFee']['No Show']['price']) ? $penalty['fareRules']['cancelFee']['No Show']['price']['code'] . ' ' . number_format($penalty['fareRules']['cancelFee']['No Show']['price']['amount'], 2) : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                    <h6>Change Fees</h6>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Prior to
                                                                Departure:</strong>
                                                            {{ isset($penalty['fareRules']['changeFee']['Prior to Departure']['price']) ? $penalty['fareRules']['changeFee']['Prior to Departure']['price']['code'] . ' ' . number_format($penalty['fareRules']['changeFee']['Prior to Departure']['price']['amount'], 2) : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>After Departure:</strong>
                                                            {{ isset($penalty['fareRules']['changeFee']['After Departure']['price']) ? $penalty['fareRules']['changeFee']['After Departure']['price']['code'] . ' ' . number_format($penalty['fareRules']['changeFee']['After Departure']['price']['amount'], 2) : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>No Show:</strong>
                                                            {{ isset($penalty['fareRules']['changeFee']['No Show']['price']) ? $penalty['fareRules']['changeFee']['No Show']['price']['code'] . ' ' . number_format($penalty['fareRules']['changeFee']['No Show']['price']['amount'], 2) : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                    <h6>Refund Status</h6>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Status:</strong>
                                                            {{ $penalty['fareRules']['refundFee']['Status'] ?? 'N/A' }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>No penalties available for this offer.</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">No penalties information available.</div>
                @endif
            </div>
        @elseif ($airline === 'flyjinnah')
            @php
                $airReservation = !empty($xmlBody['response']['Body']['OTA_AirBookRS']['AirReservation'])
                    ? $xmlBody['response']['Body']['OTA_AirBookRS']['AirReservation']
                    : (!empty($xmlBody['Body']['OTA_AirBookRS']['AirReservation'])
                        ? $xmlBody['Body']['OTA_AirBookRS']['AirReservation']
                        : null);
            @endphp
            @if ($bookingRequest && (is_array($xmlBody) || is_array($airReservation)))
                <div class="accordion" id="bookingAccordion">
                    <!-- General Booking Information -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="generalInfoHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#generalInfo" aria-expanded="true" aria-controls="generalInfo">
                                General Booking Information
                            </button>
                        </h2>
                        <div id="generalInfo" class="accordion-collapse collapse show"
                            aria-labelledby="generalInfoHeading" data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>ID:</strong>
                                        {{ $bookingRequest->id ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Airline:</strong>
                                        {{ $bookingRequest->airline ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Ticket Limit:</strong>
                                        {{ !empty($bookingRequest->ticket_limit) ? \Carbon\Carbon::parse($bookingRequest->ticket_limit)->format('d M Y, H:i') : (!empty($airReservation['Ticketing']['@attributes']['TicketTimeLimit']) ? \Carbon\Carbon::parse($airReservation['Ticketing']['@attributes']['TicketTimeLimit'])->format('d M Y, H:i') : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Payment Limit:</strong>
                                        {{ !empty($bookingRequest->payment_limit) ? \Carbon\Carbon::parse($bookingRequest->payment_limit)->format('d M Y, H:i') : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Status:</strong>
                                        {{ !empty($bookingRequest->status) ? ucfirst($bookingRequest->status) : (!empty($airReservation['Ticketing']['TicketAdvisory']) ? $airReservation['Ticketing']['TicketAdvisory'] : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Client ID:</strong>
                                        {{ $bookingRequest->client_id ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Booking ID:</strong>
                                        {{ $bookingRequest->booking_id ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Transaction ID:</strong>
                                        {{ !empty($xmlBody['transactionId']) ? $xmlBody['transactionId'] : (!empty($xmlBody['Body']['OTA_AirBookRS']['@attributes']['TransactionIdentifier']) ? $xmlBody['Body']['OTA_AirBookRS']['@attributes']['TransactionIdentifier'] : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Total Amount:</strong>
                                        {{ !empty($xmlBody['code']) && !empty($xmlBody['amount']) ? $xmlBody['code'] . ' ' . number_format($xmlBody['amount'], 2) : (!empty($airReservation['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['CurrencyCode']) && !empty($airReservation['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['Amount']) ? $airReservation['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['CurrencyCode'] . ' ' . number_format($airReservation['PriceInfo']['ItinTotalFare']['TotalFare']['@attributes']['Amount'], 2) : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Message:</strong>
                                        {{ !empty($xmlBody['message']) ? $xmlBody['message'] : (!empty($airReservation['Ticketing']['TicketAdvisory']) ? $airReservation['Ticketing']['TicketAdvisory'] : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Booking Reference:</strong>
                                        {{ !empty($airReservation['BookingReferenceID']['@attributes']['ID']) ? $airReservation['BookingReferenceID']['@attributes']['ID'] : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Created At:</strong>
                                        {{ !empty($bookingRequest->created_at) ? \Carbon\Carbon::parse($bookingRequest->created_at)->format('d M Y, H:i') : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Updated At:</strong>
                                        {{ !empty($bookingRequest->updated_at) ? \Carbon\Carbon::parse($bookingRequest->updated_at)->format('d M Y, H:i') : 'N/A' }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Information -->
                    @if (!empty($xmlBody['passengers']) && is_array($xmlBody['passengers']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="passengerInfoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#passengerInfo" aria-expanded="false"
                                    aria-controls="passengerInfo">
                                    Passenger Information
                                </button>
                            </h2>
                            <div id="passengerInfo" class="accordion-collapse collapse"
                                aria-labelledby="passengerInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @foreach ($xmlBody['passengers'] as $index => $passenger)
                                        @if (is_array($passenger))
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Passenger
                                                    {{ !empty($passenger['ref_no']) ? $passenger['ref_no'] : 'Unknown' }}
                                                    ({{ !empty($passenger['passenger_type']) ? $passenger['passenger_type'] : 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Name:</strong>
                                                            {{ !empty($passenger['name']) ? $passenger['name'] : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Nationality:</strong>
                                                            {{ !empty($passenger['nationality']) ? $passenger['nationality'] : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Phone Number:</strong>
                                                            {{ !empty($passenger['phone_number']) ? $passenger['phone_number'] : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Reference Number:</strong>
                                                            {{ !empty($passenger['ref_no']) ? $passenger['ref_no'] : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                    <!-- Seats -->
                                                    @if (!empty($passenger['seats']) && is_array($passenger['seats']))
                                                        <h6 class="mt-3">Seats</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($passenger['seats'] as $seat)
                                                                @if (is_array($seat))
                                                                    <li class="list-group-item">
                                                                        <strong>Seat:</strong>
                                                                        {{ !empty($seat['seat_number']) ? $seat['seat_number'] : 'N/A' }}
                                                                        (Flight
                                                                        {{ !empty($seat['flight_number']) ? $seat['flight_number'] : 'N/A' }},
                                                                        {{ !empty($seat['departure_date']) ? \Carbon\Carbon::parse($seat['departure_date'])->format('d M Y, H:i') : 'N/A' }})
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>No seat information available.</p>
                                                    @endif
                                                    <!-- Baggage -->
                                                    @if (!empty($passenger['baggage']) && is_array($passenger['baggage']))
                                                        <h6 class="mt-3">Baggage</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($passenger['baggage'] as $baggage)
                                                                @if (is_array($baggage))
                                                                    <li class="list-group-item">
                                                                        <strong>Baggage:</strong>
                                                                        {{ !empty($baggage['baggage_code']) ? $baggage['baggage_code'] : 'N/A' }}
                                                                        (Flight
                                                                        {{ !empty($baggage['flight_number']) ? $baggage['flight_number'] : 'N/A' }},
                                                                        {{ !empty($baggage['departure_date']) ? \Carbon\Carbon::parse($baggage['departure_date'])->format('d M Y, H:i') : 'N/A' }})
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>No baggage information available.</p>
                                                    @endif
                                                    <!-- Meals -->
                                                    @if (!empty($passenger['meals']) && is_array($passenger['meals']))
                                                        <h6 class="mt-3">Meals</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($passenger['meals'] as $meal)
                                                                @if (is_array($meal))
                                                                    <li class="list-group-item">
                                                                        <strong>Meal:</strong>
                                                                        {{ !empty($meal['meal_code']) ? $meal['meal_code'] : 'N/A' }}
                                                                        (Quantity:
                                                                        {{ !empty($meal['meal_quantity']) ? $meal['meal_quantity'] : 'N/A' }},
                                                                        Flight
                                                                        {{ !empty($meal['flight_number']) ? $meal['flight_number'] : 'N/A' }},
                                                                        {{ !empty($meal['departure_date']) ? \Carbon\Carbon::parse($meal['departure_date'])->format('d M Y, H:i') : 'N/A' }})
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>No meal information available.</p>
                                                    @endif
                                                    <!-- Tickets -->
                                                    @if (!empty($passenger['tickets']) && is_array($passenger['tickets']))
                                                        <h6 class="mt-3">Tickets</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($passenger['tickets'] as $ticket)
                                                                @if (is_array($ticket))
                                                                    <li class="list-group-item">
                                                                        <strong>Ticket:</strong>
                                                                        {{ !empty($ticket['e_ticket_no']) ? $ticket['e_ticket_no'] : 'N/A' }}
                                                                        (Coupon:
                                                                        {{ !empty($ticket['coupon_no']) ? $ticket['coupon_no'] : 'N/A' }},
                                                                        Segment:
                                                                        {{ !empty($ticket['flight_segment']) ? $ticket['flight_segment'] : 'N/A' }},
                                                                        Status:
                                                                        {{ !empty($ticket['status']) ? $ticket['status'] : 'N/A' }})
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>No ticket information available.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @elseif (!empty($airReservation['TravelerInfo']['AirTraveler']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="passengerInfoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#passengerInfo" aria-expanded="false"
                                    aria-controls="passengerInfo">
                                    Passenger Information
                                </button>
                            </h2>
                            <div id="passengerInfo" class="accordion-collapse collapse"
                                aria-labelledby="passengerInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @php
                                        // Normalize AirTraveler to always be an array
                                        $travelers =
                                            is_array($airReservation['TravelerInfo']['AirTraveler']) &&
                                            isset($airReservation['TravelerInfo']['AirTraveler'][0])
                                                ? $airReservation['TravelerInfo']['AirTraveler']
                                                : [$airReservation['TravelerInfo']['AirTraveler']];
                                    @endphp
                                    @foreach ($travelers as $index => $traveler)
                                        @if (is_array($traveler))
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Passenger
                                                    {{ !empty($traveler['TravelerRefNumber']['@attributes']['RPH']) ? $traveler['TravelerRefNumber']['@attributes']['RPH'] : 'Unknown' }}
                                                    ({{ !empty($traveler['@attributes']['PassengerTypeCode']) ? $traveler['@attributes']['PassengerTypeCode'] : 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Name:</strong>
                                                            {{ !empty($traveler['PersonName']['GivenName']) && !empty($traveler['PersonName']['Surname']) ? $traveler['PersonName']['GivenName'] . ' ' . $traveler['PersonName']['Surname'] : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Nationality:</strong>
                                                            {{ !empty($traveler['Document']['@attributes']['DocHolderNationality']) ? $traveler['Document']['@attributes']['DocHolderNationality'] : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Phone Number:</strong>
                                                            {{ !empty($traveler['Telephone']['@attributes']['PhoneNumber']) ? $traveler['Telephone']['@attributes']['PhoneNumber'] : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Reference Number:</strong>
                                                            {{ !empty($traveler['TravelerRefNumber']['@attributes']['RPH']) ? $traveler['TravelerRefNumber']['@attributes']['RPH'] : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                    <!-- Tickets -->
                                                    @if (
                                                        !empty($traveler['ETicketInfo']) &&
                                                            is_array($traveler['ETicketInfo']) &&
                                                            !empty($traveler['ETicketInfo']['ETicketInformation']) &&
                                                            is_array($traveler['ETicketInfo']['ETicketInformation']))
                                                        <h6 class="mt-3">Tickets</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($traveler['ETicketInfo']['ETicketInformation'] as $ticket)
                                                                @if (is_array($ticket))
                                                                    <li class="list-group-item">
                                                                        <strong>Ticket:</strong>
                                                                        {{ !empty($ticket['@attributes']['eTicketNo']) ? $ticket['@attributes']['eTicketNo'] : 'N/A' }}
                                                                        (Coupon:
                                                                        {{ !empty($ticket['@attributes']['couponNo']) ? $ticket['@attributes']['couponNo'] : 'N/A' }},
                                                                        Segment:
                                                                        {{ !empty($ticket['@attributes']['flightSegmentCode']) ? $ticket['@attributes']['flightSegmentCode'] : 'N/A' }},
                                                                        Status:
                                                                        {{ !empty($ticket['@attributes']['status']) ? $ticket['@attributes']['status'] : 'N/A' }})
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>No ticket information available.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No passenger information available.</div>
                    @endif

                    <!-- Flight Segments -->
                    @if (
                        !empty($airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption']) &&
                            is_array($airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="segmentsHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#segments" aria-expanded="false" aria-controls="segments">
                                    Flight Segments
                                </button>
                            </h2>
                            <div id="segments" class="accordion-collapse collapse" aria-labelledby="segmentsHeading"
                                data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @php
                                        $options =
                                            $airReservation['AirItinerary']['OriginDestinationOptions'][
                                                'OriginDestinationOption'
                                            ];
                                        $options = is_array($options) && isset($options[0]) ? $options : [$options];
                                    @endphp
                                    @foreach ($options as $index => $option)
                                        @if (!empty($option['FlightSegment']) && is_array($option['FlightSegment']))
                                            @foreach ($option['FlightSegment'] as $segment)
                                                @if (is_array($segment))
                                                    <div class="card mb-3">
                                                        <div class="card-header">
                                                            Segment:
                                                            {{ !empty($segment['DepartureAirport']['@attributes']['LocationCode']) ? $segment['DepartureAirport']['@attributes']['LocationCode'] : 'N/A' }}
                                                            to
                                                            {{ !empty($segment['ArrivalAirport']['@attributes']['LocationCode']) ? $segment['ArrivalAirport']['@attributes']['LocationCode'] : 'N/A' }}
                                                        </div>
                                                        <div class="card-body">
                                                            <h6>Flight Details</h6>
                                                            <ul class="list-group list-group-flush">
                                                                <li class="list-group-item"><strong>Departure:</strong>
                                                                    {{ !empty($segment['Comment']) ? str_replace('airport_short_names:', '', $segment['Comment']) : 'N/A' }}
                                                                    ({{ !empty($segment['DepartureAirport']['@attributes']['LocationCode']) ? $segment['DepartureAirport']['@attributes']['LocationCode'] : 'N/A' }})
                                                                    on
                                                                    {{ !empty($segment['@attributes']['DepartureDateTime']) ? \Carbon\Carbon::parse($segment['@attributes']['DepartureDateTime'])->format('d M Y') : 'N/A' }}
                                                                    at
                                                                    {{ !empty($segment['@attributes']['DepartureDateTime']) ? \Carbon\Carbon::parse($segment['@attributes']['DepartureDateTime'])->format('H:i') : 'N/A' }}
                                                                </li>
                                                                <li class="list-group-item"><strong>Arrival:</strong>
                                                                    {{ !empty($segment['Comment']) ? str_replace('airport_short_names:', '', $segment['Comment']) : 'N/A' }}
                                                                    ({{ !empty($segment['ArrivalAirport']['@attributes']['LocationCode']) ? $segment['ArrivalAirport']['@attributes']['LocationCode'] : 'N/A' }})
                                                                    on
                                                                    {{ !empty($segment['@attributes']['ArrivalDateTime']) ? \Carbon\Carbon::parse($segment['@attributes']['ArrivalDateTime'])->format('d M Y') : 'N/A' }}
                                                                    at
                                                                    {{ !empty($segment['@attributes']['ArrivalDateTime']) ? \Carbon\Carbon::parse($segment['@attributes']['ArrivalDateTime'])->format('H:i') : 'N/A' }}
                                                                </li>
                                                                <li class="list-group-item"><strong>Terminal:</strong>
                                                                    Departure -
                                                                    {{ !empty($segment['DepartureAirport']['@attributes']['Terminal']) ? $segment['DepartureAirport']['@attributes']['Terminal'] : 'N/A' }},
                                                                    Arrival -
                                                                    {{ !empty($segment['ArrivalAirport']['@attributes']['Terminal']) ? $segment['ArrivalAirport']['@attributes']['Terminal'] : 'N/A' }}
                                                                </li>
                                                                <li class="list-group-item"><strong>Duration:</strong>
                                                                    {{ !empty($segment['@attributes']['DepartureDateTime']) && !empty($segment['@attributes']['ArrivalDateTime']) ? \Carbon\Carbon::parse($segment['@attributes']['DepartureDateTime'])->diffInMinutes(\Carbon\Carbon::parse($segment['@attributes']['ArrivalDateTime'])) . ' minutes' : 'N/A' }}
                                                                </li>
                                                                <li class="list-group-item"><strong>Flight
                                                                        Number:</strong>
                                                                    {{ !empty($segment['@attributes']['FlightNumber']) ? $segment['@attributes']['FlightNumber'] : 'N/A' }}
                                                                </li>
                                                                <li class="list-group-item"><strong>Cabin
                                                                        Class:</strong>
                                                                    {{ !empty($segment['@attributes']['ResCabinClass']) ? $segment['@attributes']['ResCabinClass'] : 'N/A' }}
                                                                </li>
                                                                <li class="list-group-item"><strong>Status:</strong>
                                                                    {{ !empty($segment['@attributes']['Status']) ? $segment['@attributes']['Status'] : 'N/A' }}
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No flight segments available.</div>
                    @endif

                    <!-- Pricing Information -->
                    @if (!empty($airReservation['PriceInfo']['ItinTotalFare']) && is_array($airReservation['PriceInfo']['ItinTotalFare']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="pricingInfoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#pricingInfo" aria-expanded="false" aria-controls="pricingInfo">
                                    Pricing Information
                                </button>
                            </h2>
                            <div id="pricingInfo" class="accordion-collapse collapse"
                                aria-labelledby="pricingInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @php
                                        $priceInfo = $airReservation['PriceInfo']['ItinTotalFare'];
                                    @endphp
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            Pricing Details
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>Base Fare:</strong>
                                                    {{ !empty($priceInfo['BaseFare']['@attributes']['CurrencyCode']) && !empty($priceInfo['BaseFare']['@attributes']['Amount']) ? $priceInfo['BaseFare']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['BaseFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Equivalent Base Fare:</strong>
                                                    {{ !empty($priceInfo['EquiBaseFare']['@attributes']['CurrencyCode']) && !empty($priceInfo['EquiBaseFare']['@attributes']['Amount']) ? $priceInfo['EquiBaseFare']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['EquiBaseFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Taxes:</strong>
                                                    {{ !empty($priceInfo['Taxes']['Tax']['@attributes']['CurrencyCode']) && !empty($priceInfo['Taxes']['Tax']['@attributes']['Amount']) ? $priceInfo['Taxes']['Tax']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['Taxes']['Tax']['@attributes']['Amount'], 2) . ' (' . $priceInfo['Taxes']['Tax']['@attributes']['TaxCode'] . ')' : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Fees:</strong>
                                                    {{ !empty($priceInfo['Fees']['Fee']['@attributes']['CurrencyCode']) && !empty($priceInfo['Fees']['Fee']['@attributes']['Amount']) ? $priceInfo['Fees']['Fee']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['Fees']['Fee']['@attributes']['Amount'], 2) . ' (' . $priceInfo['Fees']['Fee']['@attributes']['FeeCode'] . ')' : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Total Fare:</strong>
                                                    {{ !empty($priceInfo['TotalFare']['@attributes']['CurrencyCode']) && !empty($priceInfo['TotalFare']['@attributes']['Amount']) ? $priceInfo['TotalFare']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['TotalFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Equivalent Total Fare:</strong>
                                                    {{ !empty($priceInfo['TotalEquivFare']['@attributes']['CurrencyCode']) && !empty($priceInfo['TotalEquivFare']['@attributes']['Amount']) ? $priceInfo['TotalEquivFare']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['TotalEquivFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Total Fare with CC Fee:</strong>
                                                    {{ !empty($priceInfo['TotalFareWithCCFee']['@attributes']['CurrencyCode']) && !empty($priceInfo['TotalFareWithCCFee']['@attributes']['Amount']) ? $priceInfo['TotalFareWithCCFee']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['TotalFareWithCCFee']['@attributes']['Amount'], 2) : 'N/A' }}
                                                </li>
                                                <li class="list-group-item"><strong>Equivalent Total Fare with CC
                                                        Fee:</strong>
                                                    {{ !empty($priceInfo['TotalEquivFareWithCCFee']['@attributes']['CurrencyCode']) && !empty($priceInfo['TotalEquivFareWithCCFee']['@attributes']['Amount']) ? $priceInfo['TotalEquivFareWithCCFee']['@attributes']['CurrencyCode'] . ' ' . number_format($priceInfo['TotalEquivFareWithCCFee']['@attributes']['Amount'], 2) : 'N/A' }}
                                                </li>
                                            </ul>
                                            <!-- Detailed Tax Breakdown -->
                                            @if (
                                                !empty($airReservation['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']) &&
                                                    is_array($airReservation['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']))
                                                @php
                                                    $ptcFare =
                                                        is_array(
                                                            $airReservation['PriceInfo']['PTC_FareBreakdowns'][
                                                                'PTC_FareBreakdown'
                                                            ],
                                                        ) &&
                                                        isset(
                                                            $airReservation['PriceInfo']['PTC_FareBreakdowns'][
                                                                'PTC_FareBreakdown'
                                                            ][0],
                                                        )
                                                            ? $airReservation['PriceInfo']['PTC_FareBreakdowns'][
                                                                'PTC_FareBreakdown'
                                                            ]
                                                            : [
                                                                $airReservation['PriceInfo']['PTC_FareBreakdowns'][
                                                                    'PTC_FareBreakdown'
                                                                ],
                                                            ];
                                                @endphp
                                                <h6 class="mt-3">Fare Breakdown</h6>
                                                @foreach ($ptcFare as $fare)
                                                    @if (is_array($fare) && !empty($fare['PassengerTypeQuantity']['@attributes']))
                                                        <div class="card mb-3">
                                                            <div class="card-header">
                                                                Passenger Type:
                                                                {{ !empty($fare['PassengerTypeQuantity']['@attributes']['Code']) ? $fare['PassengerTypeQuantity']['@attributes']['Code'] : 'N/A' }}
                                                                (Quantity:
                                                                {{ !empty($fare['PassengerTypeQuantity']['@attributes']['Quantity']) ? $fare['PassengerTypeQuantity']['@attributes']['Quantity'] : 'N/A' }})
                                                            </div>
                                                            <div class="card-body">
                                                                <ul class="list-group list-group-flush">
                                                                    <li class="list-group-item"><strong>Fare Basis
                                                                            Code:</strong>
                                                                        {{ !empty($fare['FareBasisCodes']['FareBasisCode']) ? $fare['FareBasisCodes']['FareBasisCode'] : 'N/A' }}
                                                                    </li>
                                                                    <li class="list-group-item"><strong>Base
                                                                            Fare:</strong>
                                                                        {{ !empty($fare['PassengerFare']['BaseFare']['@attributes']['CurrencyCode']) && !empty($fare['PassengerFare']['BaseFare']['@attributes']['Amount']) ? $fare['PassengerFare']['BaseFare']['@attributes']['CurrencyCode'] . ' ' . number_format($fare['PassengerFare']['BaseFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                                    </li>
                                                                    <li class="list-group-item"><strong>Equivalent Base
                                                                            Fare:</strong>
                                                                        {{ !empty($fare['PassengerFare']['EquiBaseFare']['@attributes']['CurrencyCode']) && !empty($fare['PassengerFare']['EquiBaseFare']['@attributes']['Amount']) ? $fare['PassengerFare']['EquiBaseFare']['@attributes']['CurrencyCode'] . ' ' . number_format($fare['PassengerFare']['EquiBaseFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                                    </li>
                                                                    <li class="list-group-item"><strong>Total
                                                                            Fare:</strong>
                                                                        {{ !empty($fare['PassengerFare']['TotalFare']['@attributes']['CurrencyCode']) && !empty($fare['PassengerFare']['TotalFare']['@attributes']['Amount']) ? $fare['PassengerFare']['TotalFare']['@attributes']['CurrencyCode'] . ' ' . number_format($fare['PassengerFare']['TotalFare']['@attributes']['Amount'], 2) : 'N/A' }}
                                                                    </li>
                                                                </ul>
                                                                @if (!empty($fare['PassengerFare']['Taxes']['Tax']) && is_array($fare['PassengerFare']['Taxes']['Tax']))
                                                                    <h6 class="mt-3">Taxes</h6>
                                                                    <ul class="list-group list-group-flush">
                                                                        @foreach ($fare['PassengerFare']['Taxes']['Tax'] as $tax)
                                                                            @if (is_array($tax))
                                                                                <li class="list-group-item">
                                                                                    <strong>{{ !empty($tax['@attributes']['TaxName']) ? $tax['@attributes']['TaxName'] : 'Tax' }}:</strong>
                                                                                    {{ !empty($tax['@attributes']['CurrencyCode']) && !empty($tax['@attributes']['Amount']) ? $tax['@attributes']['CurrencyCode'] . ' ' . number_format($tax['@attributes']['Amount'], 2) : 'N/A' }}
                                                                                    ({{ !empty($tax['@attributes']['TaxCode']) ? $tax['@attributes']['TaxCode'] : 'N/A' }})
                                                                                </li>
                                                                            @endif
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                                @if (!empty($fare['PassengerFare']['Fees']['Fee']) && is_array($fare['PassengerFare']['Fees']['Fee']))
                                                                    <h6 class="mt-3">Fees</h6>
                                                                    <ul class="list-group list-group-flush">
                                                                        @foreach ($fare['PassengerFare']['Fees']['Fee'] as $fee)
                                                                            <li class="list-group-item">
                                                                                <strong>Fee:</strong>
                                                                                {{ $fee }}
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                            <!-- Payment Details -->
                                            @if (
                                                !empty($airReservation['Fulfillment']['PaymentDetails']['PaymentDetail']) &&
                                                    is_array($airReservation['Fulfillment']['PaymentDetails']['PaymentDetail']))
                                                <h6 class="mt-3">Payment Details</h6>
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($airReservation['Fulfillment']['PaymentDetails']['PaymentDetail'] as $payment)
                                                        @if (is_array($payment))
                                                            <li class="list-group-item">
                                                                <strong>Payment:</strong>
                                                                {{ !empty($payment['PaymentAmount']['@attributes']['CurrencyCode']) && !empty($payment['PaymentAmount']['@attributes']['Amount']) ? $payment['PaymentAmount']['@attributes']['CurrencyCode'] . ' ' . number_format($payment['PaymentAmount']['@attributes']['Amount'], 2) : 'N/A' }}
                                                                ({{ !empty($payment['PaymentAmountInPayCur']['@attributes']['CurrencyCode']) && !empty($payment['PaymentAmountInPayCur']['@attributes']['Amount']) ? $payment['PaymentAmountInPayCur']['@attributes']['CurrencyCode'] . ' ' . number_format($payment['PaymentAmountInPayCur']['@attributes']['Amount'], 2) : 'N/A' }})
                                                                @if (!empty($payment['DirectBill']['CompanyName']))
                                                                    <br><strong>Company:</strong>
                                                                    {{ $payment['DirectBill']['CompanyName'] }}
                                                                @endif
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @else
                                                <p>No payment details available.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No pricing information available.</div>
                    @endif

                    <!-- Penalties -->
                    @if (
                        !empty($airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption']) &&
                            is_array($airReservation['AirItinerary']['OriginDestinationOptions']['OriginDestinationOption']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="penaltiesHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#penalties" aria-expanded="false" aria-controls="penalties">
                                    Penalties
                                </button>
                            </h2>
                            <div id="penalties" class="accordion-collapse collapse"
                                aria-labelledby="penaltiesHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @php
                                        $hasPenalties = false;
                                        $options =
                                            $airReservation['AirItinerary']['OriginDestinationOptions'][
                                                'OriginDestinationOption'
                                            ];
                                        $options = is_array($options) && isset($options[0]) ? $options : [$options];
                                    @endphp
                                    @foreach ($options as $index => $option)
                                        @if (!empty($option['FlightSegment']) && is_array($option['FlightSegment']))
                                            @foreach ($option['FlightSegment'] as $segment)
                                                @if (is_array($segment) &&
                                                        !empty($segment['AvailableFlexiOperations']['FlexiOperations']) &&
                                                        is_array($segment['AvailableFlexiOperations']['FlexiOperations']))
                                                    @php $hasPenalties = true; @endphp
                                                    <div class="card mb-3">
                                                        <div class="card-header">
                                                            Segment:
                                                            {{ !empty($segment['DepartureAirport']['@attributes']['LocationCode']) ? $segment['DepartureAirport']['@attributes']['LocationCode'] : 'N/A' }}
                                                            to
                                                            {{ !empty($segment['ArrivalAirport']['@attributes']['LocationCode']) ? $segment['ArrivalAirport']['@attributes']['LocationCode'] : 'N/A' }}
                                                        </div>
                                                        <div class="card-body">
                                                            <h6>Flexi Operations</h6>
                                                            <ul class="list-group list-group-flush">
                                                                @foreach ($segment['AvailableFlexiOperations']['FlexiOperations'] as $operation)
                                                                    @if (is_array($operation) && !empty($operation['@attributes']))
                                                                        <li class="list-group-item">
                                                                            <strong>{{ !empty($operation['@attributes']['AllowedOperationName']) ? $operation['@attributes']['AllowedOperationName'] : 'N/A' }}:</strong>
                                                                            Allowed
                                                                            {{ !empty($operation['@attributes']['NumberOfAllowedOperations']) ? $operation['@attributes']['NumberOfAllowedOperations'] : 'N/A' }}
                                                                            time(s),
                                                                            Cutoff:
                                                                            {{ !empty($operation['@attributes']['FlexiOperationCutoverTimeInMinutes']) ? $operation['@attributes']['FlexiOperationCutoverTimeInMinutes'] . ' minutes' : 'N/A' }}
                                                                        </li>
                                                                    @endif
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                    @if (!$hasPenalties)
                                        <p>No penalty information available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No penalties information available.</div>
                    @endif
                </div>
            @endif
        @elseif ($airline === 'pia')
            @if ($bookingRequest && is_array($xmlBody))
                <div class="accordion" id="bookingAccordion">
                    <!-- General Booking Information -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="generalInfoHeading">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#generalInfo" aria-expanded="true" aria-controls="generalInfo">
                                General Booking Information PIA
                            </button>
                        </h2>
                        <div id="generalInfo" class="accordion-collapse collapse show"
                            aria-labelledby="generalInfoHeading" data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>ID:</strong>
                                        {{ $bookingRequest->id ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Airline:</strong>
                                        {{ $bookingRequest->airline ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Ticket Limit:</strong>
                                        {{ !empty($bookingRequest->ticket_limit) ? \Carbon\Carbon::parse($bookingRequest->ticket_limit)->format('d M Y, H:i') : (!empty($xmlBody['paymentLimit']) ? \Carbon\Carbon::parse($xmlBody['paymentLimit'])->format('d M Y, H:i') : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Payment Limit:</strong>
                                        {{ !empty($bookingRequest->payment_limit) ? \Carbon\Carbon::parse($bookingRequest->payment_limit)->format('d M Y, H:i') : (!empty($xmlBody['paymentLimit']) ? \Carbon\Carbon::parse($xmlBody['paymentLimit'])->format('d M Y, H:i') : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Status:</strong>
                                        {{ !empty($bookingRequest->status) ? ucfirst($bookingRequest->status) : (!empty($xmlBody['order']['statusCode']) ? $xmlBody['order']['statusCode'] : 'N/A') }}
                                    </li>
                                    <li class="list-group-item"><strong>Client ID:</strong>
                                        {{ $bookingRequest->client_id ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Booking ID:</strong>
                                        {{ $bookingRequest->booking_id ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Transaction ID:</strong>
                                        {{ !empty($xmlBody['transaction_id']) ? $xmlBody['transaction_id'] : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Total Amount:</strong>
                                        {{ !empty($xmlBody['totalPrice']) ? 'PKR ' . number_format($xmlBody['totalPrice'], 2) : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Booking Reference:</strong>
                                        {{ !empty($xmlBody['order']['orderID']) ? $xmlBody['order']['orderID'] : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Created At:</strong>
                                        {{ !empty($bookingRequest->created_at) ? \Carbon\Carbon::parse($bookingRequest->created_at)->format('d M Y, H:i') : 'N/A' }}
                                    </li>
                                    <li class="list-group-item"><strong>Updated At:</strong>
                                        {{ !empty($bookingRequest->updated_at) ? \Carbon\Carbon::parse($bookingRequest->updated_at)->format('d M Y, H:i') : 'N/A' }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Passenger Information -->
                    @if (!empty($xmlBody['passengers']) && is_array($xmlBody['passengers']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="passengerInfoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#passengerInfo" aria-expanded="false"
                                    aria-controls="passengerInfo">
                                    Passenger Information
                                </button>
                            </h2>
                            <div id="passengerInfo" class="accordion-collapse collapse"
                                aria-labelledby="passengerInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @foreach ($xmlBody['passengers'] as $passenger)
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                Passenger
                                                {{ !empty($passenger['pax_id']) ? $passenger['pax_id'] : 'Unknown' }}
                                                ({{ !empty($passenger['ptc']) ? $passenger['ptc'] : 'N/A' }})
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"><strong>Name:</strong>
                                                        {{ !empty($passenger['title']) ? $passenger['title'] . ' ' : '' }}{{ !empty($passenger['given_name']) ? $passenger['given_name'] : '' }}
                                                        {{ !empty($passenger['surname']) ? $passenger['surname'] : '' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Birthdate:</strong>
                                                        {{ !empty($passenger['birthdate']) ? \Carbon\Carbon::parse($passenger['birthdate'])->format('d M Y') : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Gender:</strong>
                                                        {{ !empty($passenger['gender']) ? $passenger['gender'] : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Citizenship:</strong>
                                                        {{ !empty($passenger['citizenship']) ? $passenger['citizenship'] : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Ticket Number:</strong>
                                                        {{ !empty($passenger['ticket']['ticketNumber']) ? $passenger['ticket']['ticketNumber'] : 'N/A' }}
                                                    </li>
                                                </ul>
                                                <!-- Services -->
                                                @if (!empty($passenger['services']) && is_array($passenger['services']))
                                                    <h6 class="mt-3">Services</h6>
                                                    <ul class="list-group list-group-flush">
                                                        @foreach ($passenger['services'] as $service)
                                                            <li class="list-group-item">
                                                                <strong>Service:</strong>
                                                                {{ !empty($service['service_definition_id']) ? $service['service_definition_id'] : 'N/A' }}
                                                                (Status:
                                                                {{ !empty($service['status_code']) ? $service['status_code'] : 'N/A' }})
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p>No services information available.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No passenger information available.</div>
                    @endif

                    <!-- Flight Segments -->
                    @if (!empty($xmlBody['segments']) && is_array($xmlBody['segments']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="segmentsHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#segments" aria-expanded="false" aria-controls="segments">
                                    Flight Segments
                                </button>
                            </h2>
                            <div id="segments" class="accordion-collapse collapse" aria-labelledby="segmentsHeading"
                                data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @foreach ($xmlBody['segments'] as $segment)
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                Segment: {{ !empty($segment['origin']) ? $segment['origin'] : 'N/A' }}
                                                to
                                                {{ !empty($segment['destination']) ? $segment['destination'] : 'N/A' }}
                                            </div>
                                            <div class="card-body">
                                                <h6>Flight Details</h6>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"><strong>Departure:</strong>
                                                        {{ !empty($segment['origin_name']) ? $segment['origin_name'] : 'N/A' }}
                                                        ({{ !empty($segment['origin']) ? $segment['origin'] : 'N/A' }})
                                                        on
                                                        {{ !empty($segment['departure_time']) ? \Carbon\Carbon::parse($segment['departure_time'])->format('d M Y, H:i') : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Arrival:</strong>
                                                        {{ !empty($segment['destination_name']) ? $segment['destination_name'] : 'N/A' }}
                                                        ({{ !empty($segment['destination']) ? $segment['destination'] : 'N/A' }})
                                                        on
                                                        {{ !empty($segment['arrival_time']) ? \Carbon\Carbon::parse($segment['arrival_time'])->format('d M Y, H:i') : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Duration:</strong>
                                                        {{ !empty($segment['duration']) ? $segment['duration'] : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Flight Number:</strong>
                                                        {{ !empty($segment['flight_number']) ? $segment['flight_number'] : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Carrier:</strong>
                                                        {{ !empty($segment['carrier']) ? $segment['carrier'] : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Aircraft Type:</strong>
                                                        {{ !empty($segment['aircraft_type']) ? $segment['aircraft_type'] : 'N/A' }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No flight segments available.</div>
                    @endif

                    <!-- Pricing Information -->
                    @if (!empty($xmlBody['passengers']) && is_array($xmlBody['passengers']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="pricingInfoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#pricingInfo" aria-expanded="false" aria-controls="pricingInfo">
                                    Pricing Information
                                </button>
                            </h2>
                            <div id="pricingInfo" class="accordion-collapse collapse"
                                aria-labelledby="pricingInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @foreach ($xmlBody['passengers'] as $passenger)
                                        @if (!empty($passenger['fare_details']['fare_price_type']['price']))
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Pricing for Passenger
                                                    {{ !empty($passenger['pax_id']) ? $passenger['pax_id'] : 'Unknown' }}
                                                    ({{ !empty($passenger['ptc']) ? $passenger['ptc'] : 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Base Fare:</strong>
                                                            {{ !empty($passenger['fare_details']['fare_price_type']['price']['base_amount']) ? $passenger['fare_details']['fare_price_type']['price']['currency'] . ' ' . number_format($passenger['fare_details']['fare_price_type']['price']['base_amount'], 2) : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Total Fare:</strong>
                                                            {{ !empty($passenger['fare_details']['fare_price_type']['price']['total_amount']) ? $passenger['fare_details']['fare_price_type']['price']['currency'] . ' ' . number_format($passenger['fare_details']['fare_price_type']['price']['total_amount'], 2) : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Surcharge:</strong>
                                                            {{ !empty($passenger['fare_details']['fare_price_type']['price']['surcharge']) ? $passenger['fare_details']['fare_price_type']['price']['currency'] . ' ' . number_format($passenger['fare_details']['fare_price_type']['price']['surcharge'], 2) : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                    @if (
                                                        !empty($passenger['fare_details']['fare_price_type']['price']['taxes']) &&
                                                            is_array($passenger['fare_details']['fare_price_type']['price']['taxes']))
                                                        <h6 class="mt-3">Tax Breakdown</h6>
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($passenger['fare_details']['fare_price_type']['price']['taxes'] as $tax)
                                                                <li class="list-group-item">
                                                                    <strong>{{ !empty($tax['tax_code']) ? $tax['tax_code'] : 'Unknown Tax' }}:</strong>
                                                                    {{ !empty($tax['amount']) ? $passenger['fare_details']['fare_price_type']['price']['currency'] . ' ' . number_format($tax['amount'], 2) : 'N/A' }}
                                                                    (Refundable:
                                                                    {{ !empty($tax['refund_ind']) ? ($tax['refund_ind'] === 'true' ? 'Yes' : 'No') : 'N/A' }})
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <p>No tax breakdown available.</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            Total Pricing
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>Total Amount:</strong>
                                                    {{ !empty($xmlBody['totalPrice']) ? 'PKR ' . number_format($xmlBody['totalPrice'], 2) : 'N/A' }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No pricing information available.</div>
                    @endif

                    <!-- Baggage Allowances -->
                    @if (!empty($xmlBody['baggage_allowances']) && is_array($xmlBody['baggage_allowances']))
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="baggageInfoHeading">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#baggageInfo" aria-expanded="false" aria-controls="baggageInfo">
                                    Baggage Allowances
                                </button>
                            </h2>
                            <div id="baggageInfo" class="accordion-collapse collapse"
                                aria-labelledby="baggageInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    @foreach ($xmlBody['baggage_allowances'] as $baggage)
                                        <div class="card mb-3">
                                            <div class="card-header">
                                                Baggage Allowance
                                                {{ !empty($baggage['baggage_allowance_id']) ? $baggage['baggage_allowance_id'] : 'Unknown' }}
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"><strong>Type:</strong>
                                                        {{ !empty($baggage['type']) ? $baggage['type'] : 'N/A' }}</li>
                                                    <li class="list-group-item"><strong>Max Weight:</strong>
                                                        {{ !empty($baggage['piece_allowance']['max_weight']['value']) ? $baggage['piece_allowance']['max_weight']['value'] . ' ' . $baggage['piece_allowance']['max_weight']['unit'] : 'N/A' }}
                                                    </li>
                                                    <li class="list-group-item"><strong>Applicable Party:</strong>
                                                        {{ !empty($baggage['piece_allowance']['applicable_party']) ? $baggage['piece_allowance']['applicable_party'] : 'N/A' }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">No baggage allowance information available.</div>
                    @endif

                    <!-- Penalties -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="penaltiesHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#penalties" aria-expanded="false" aria-controls="penalties">
                                Penalties & Cancellation Fees
                            </button>
                        </h2>
                        <div id="penalties" class="accordion-collapse collapse" aria-labelledby="penaltiesHeading"
                            data-bs-parent="#bookingAccordion">
                            <div class="accordion-body">
                                @if (!empty($xmlBody['passengers']) && is_array($xmlBody['passengers']))
                                    <h6>Per Passenger Cancellation Fees</h6>
                                    @foreach ($xmlBody['passengers'] as $passenger)
                                        @if (!empty($passenger['cancel_fee']))
                                            <div class="card mb-2">
                                                <div class="card-header">
                                                    <strong>{{ !empty($passenger['ptc']) ? strtoupper($passenger['ptc']) : 'Unknown' }}
                                                        -
                                                        {{ !empty($passenger['given_name']) ? $passenger['given_name'] : '' }}
                                                        {{ !empty($passenger['surname']) ? $passenger['surname'] : '' }}</strong>
                                                </div>
                                                <div class="card-body p-2">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <span><strong>Penalty ID:</strong>
                                                                {{ !empty($passenger['cancel_fee']['penalty_id']) ? $passenger['cancel_fee']['penalty_id'] : 'N/A' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <span><strong>Type:</strong>
                                                                {{ !empty($passenger['cancel_fee']['type_code']) ? $passenger['cancel_fee']['type_code'] : 'N/A' }}</span>
                                                        </li>
                                                        <li class="list-group-item d-flex justify-content-between">
                                                            <span><strong>Cancellation Fee:</strong></span>
                                                            <span class="fw-bold text-danger">
                                                                {{ !empty($passenger['cancel_fee']['cancel_fee']) ? 'PKR ' . number_format($passenger['cancel_fee']['cancel_fee'], 2) : 'PKR 0.00' }}
                                                            </span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <!-- Summary Table -->
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <strong>Penalty Summary</strong>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>Passenger Type</th>
                                                            <th>Passenger Name</th>
                                                            <th>Penalty Type</th>
                                                            <th>Amount (PKR)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($xmlBody['passengers'] as $passenger)
                                                            @if (!empty($passenger['cancel_fee']))
                                                                <tr>
                                                                    <td>
                                                                        <span
                                                                            class="badge bg-{{ $passenger['ptc'] === 'ADT' ? 'primary' : ($passenger['ptc'] === 'CHD' ? 'warning' : 'info') }}">
                                                                            {{ !empty($passenger['ptc']) ? $passenger['ptc'] : 'N/A' }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ !empty($passenger['given_name']) ? $passenger['given_name'] : '' }}
                                                                        {{ !empty($passenger['surname']) ? $passenger['surname'] : '' }}
                                                                    </td>
                                                                    <td>{{ !empty($passenger['cancel_fee']['type_code']) ? $passenger['cancel_fee']['type_code'] : 'N/A' }}
                                                                    </td>
                                                                    <td class="fw-bold text-end">
                                                                        <span
                                                                            class="text-danger">{{ !empty($passenger['cancel_fee']['cancel_fee']) ? number_format($passenger['cancel_fee']['cancel_fee'], 2) : '0.00' }}</span>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                        <tr class="table-light fw-bold">
                                                            <td colspan="3" class="text-end">Total Penalties:</td>
                                                            <td class="text-end text-danger">
                                                                PKR
                                                                {{ array_sum(array_column(array_filter($xmlBody['passengers'], function ($p) {return !empty($p['cancel_fee']['cancel_fee']);}),'cancel_fee','cancel_fee')) | number_format(0) }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> No passenger data available to
                                        display penalties.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">No booking request data available for PIA.</div>
            @endif
        @elseif ($airline === 'airblue')
            @if ($bookingRequest && is_array($xmlBody) && (!empty($xmlBody['data']) || !empty($xmlBody)))
                @php
                    // Check if it's the new structure
                    $isNewStructure = isset($xmlBody['itinerary']) || isset($xmlBody['fare_breakdown']);
                    
                    // Common Data
                    $data = $xmlBody['data'] ?? $xmlBody ?? [];
                @endphp

                @if($isNewStructure)
                     {{-- NEW STRUCTURE VIEW --}}
                    <div class="accordion" id="bookingAccordion">
                        <!-- General Booking Information -->
                         <div class="accordion-item">
                            <h2 class="accordion-header" id="generalInfoHeading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#generalInfo" aria-expanded="true" aria-controls="generalInfo">
                                    General Booking Information (Airblue)
                                </button>
                            </h2>
                            <div id="generalInfo" class="accordion-collapse collapse show"
                                aria-labelledby="generalInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><strong>Booking ID:</strong> {{ $data['booking']['id'] ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Instance:</strong> {{ $data['booking']['instance'] ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Status:</strong> <span class="badge bg-{{ ($data['status'] ?? '') == 'OK' ? 'success' : 'warning' }}">{{ $data['status'] ?? 'N/A' }}</span></li>
                                        <li class="list-group-item"><strong>Ticket Time Limit:</strong> 
                                            {{ !empty($data['ticket_time_limit']) ? \Carbon\Carbon::parse($data['ticket_time_limit'])->format('d M Y, H:i') : 'N/A' }}
                                        </li>
                                        <li class="list-group-item"><strong>Total Fare:</strong> 
                                            {{ $data['total_fare']['code'] ?? 'PKR' }} {{ number_format($data['total_fare']['amount'] ?? 0, 2) }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Passengers -->
                        @if (!empty($data['passengers']) && is_array($data['passengers']))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="passengersHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#passengers" aria-expanded="false" aria-controls="passengers">
                                        Passengers
                                    </button>
                                </h2>
                                <div id="passengers" class="accordion-collapse collapse" aria-labelledby="passengersHeading"
                                    data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($data['passengers'] as $index => $pax)
                                            <div class="card mb-3">
                                                <div class="card-header d-flex justify-content-between">
                                                    <span>
                                                        Passenger {{ $index + 1 }} 
                                                        ({{ $pax['type'] ?? 'ADT' }})
                                                    </span>
                                                    <span class="badge bg-info">{{ $pax['rph'] ?? '' }}</span>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Name:</strong> 
                                                            {{ $pax['title'] ?? '' }} {{ $pax['first_name'] ?? '' }} {{ $pax['last_name'] ?? '' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Birth Date:</strong> {{ $pax['birth_date'] ?? 'N/A' }}</li>
                                                        
                                                        @if(!empty($pax['phone']))
                                                            <li class="list-group-item"><strong>Phone:</strong> 
                                                                +{{ $pax['phone']['CountryAccessCode'] ?? '' }} {{ $pax['phone']['PhoneNumber'] ?? '' }}
                                                            </li>
                                                        @endif
                                                        
                                                        <li class="list-group-item"><strong>Email:</strong> {{ $pax['email'] ?? 'N/A' }}</li>

                                                        @if(!empty($pax['document']))
                                                            <li class="list-group-item">
                                                                <strong>Document:</strong> <br>
                                                                Type: {{ $pax['document']['DocType'] ?? 'N/A' }} | 
                                                                ID: {{ $pax['document']['DocID'] ?? 'N/A' }} | 
                                                                Issues: {{ $pax['document']['DocIssueCountry'] ?? 'N/A' }} | 
                                                                Exp: {{ $pax['document']['ExpireDate'] ?? 'N/A' }}
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Itinerary -->
                        @if (!empty($data['itinerary']) && is_array($data['itinerary']))
                             <div class="accordion-item">
                                <h2 class="accordion-header" id="itineraryHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#itinerary" aria-expanded="false" aria-controls="itinerary">
                                        Itinerary / Segments
                                    </button>
                                </h2>
                                <div id="itinerary" class="accordion-collapse collapse" aria-labelledby="itineraryHeading"
                                    data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($data['itinerary'] as $legIndex => $leg)
                                            <div class="mb-3">
                                                <h6 class="text-primary">Leg {{ $leg['leg_id'] ?? ($legIndex + 1) }}</h6>
                                                @if(!empty($leg['segments']))
                                                    @foreach($leg['segments'] as $segIndex => $seg)
                                                        <div class="card mb-2">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <strong>{{ $seg['operating_airline'] ?? 'XX' }} {{ $seg['flight_number'] ?? '' }}</strong><br>
                                                                        <small class="text-muted">{{ $seg['aircraft'] ?? '' }} ({{ $seg['cabin'] ?? '' }})</small>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>{{ $seg['from'] ?? '' }}</strong> <i class="fas fa-arrow-right"></i> <strong>{{ $seg['to'] ?? '' }}</strong><br>
                                                                        <small>{{ \Carbon\Carbon::parse($seg['departure'])->format('d M Y H:i') }} - {{ \Carbon\Carbon::parse($seg['arrival'])->format('d M Y H:i') }}</small>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                         Status: {{ $seg['status'] ?? 'N/A' }}<br>
                                                                         Class: {{ $seg['booking_class'] ?? '' }}
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                         RPH: {{ $seg['rph'] ?? '' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Fare Breakdown -->
                        @if (!empty($data['fare_breakdown']) && is_array($data['fare_breakdown']))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="fareBreakdownHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#fareBreakdown" aria-expanded="false" aria-controls="fareBreakdown">
                                        Fare Breakdown
                                    </button>
                                </h2>
                                <div id="fareBreakdown" class="accordion-collapse collapse" aria-labelledby="fareBreakdownHeading"
                                    data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        <table class="table table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Qty</th>
                                                    <th>Base</th>
                                                    <th>Taxes</th>
                                                    <th>Fees</th>
                                                    <th>Total/Pax</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($data['fare_breakdown'] as $fare)
                                                    <tr>
                                                        <td>{{ $fare['type'] ?? 'ADT' }}</td>
                                                        <td>{{ $fare['quantity'] ?? 1 }}</td>
                                                        <td>{{ number_format($fare['base'] ?? 0) }} {{ $fare['currency'] ?? '' }}</td>
                                                        <td>{{ number_format($fare['taxes'] ?? 0) }}</td>
                                                        <td>{{ number_format($fare['fees'] ?? 0) }}</td>
                                                        <td>{{ number_format($fare['total_per_pax'] ?? 0) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                @else
                    {{-- OLD STRUCTURE VIEW (Existing Code) --}}
                    @php
                        $bookingInfo = $data['booking'] ?? [];
                        $flights = $data['flights'] ?? [];
                        $travelers = $data['travelers'] ?? [];
                        $seats = $data['seats'] ?? [];
                        $ancillaries = $data['ancillaries'] ?? [];
                        $priceBreakdown = $data['price_breakdown'] ?? [];
                        $raw = $data['raw'] ?? [];
                    @endphp
                    <div class="accordion" id="bookingAccordion">
                        <!-- General Booking Information -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="generalInfoHeading">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#generalInfo" aria-expanded="true" aria-controls="generalInfo">
                                    General Booking Information Airblue
                                </button>
                            </h2>
                            <div id="generalInfo" class="accordion-collapse collapse show"
                                aria-labelledby="generalInfoHeading" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><strong>ID:</strong>
                                            {{ $bookingRequest->id ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Airline:</strong>
                                            {{ $bookingRequest->airline ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>PNR:</strong>
                                            {{ $bookingInfo['pnr'] ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Instance:</strong>
                                            {{ $bookingInfo['instance'] ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Ticket Time Limit:</strong>
                                            {{ !empty($data['ticket_time_limit']) ? \Carbon\Carbon::parse($data['ticket_time_limit'])->format('d M Y, H:i') : 'N/A' }}
                                        </li>
                                        <li class="list-group-item"><strong>Status:</strong>
                                            {{ !empty($data['success']) ? ($data['success'] === 'true' ? 'Success' : 'Failed') : 'N/A' }}
                                        </li>
                                        <li class="list-group-item"><strong>Total Amount:</strong>
                                            {{ !empty($data['total']['amount']) && !empty($data['total']['currency']) ? $data['total']['currency'] . ' ' . number_format($data['total']['amount'], 2) : 'N/A' }}
                                        </li>
                                        <li class="list-group-item"><strong>Client ID:</strong>
                                            {{ $bookingRequest->client_id ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Booking ID:</strong>
                                            {{ $bookingRequest->booking_id ?? 'N/A' }}</li>
                                        <li class="list-group-item"><strong>Created At:</strong>
                                            {{ !empty($bookingRequest->created_at) ? \Carbon\Carbon::parse($bookingRequest->created_at)->format('d M Y, H:i') : 'N/A' }}
                                        </li>
                                        <li class="list-group-item"><strong>Updated At:</strong>
                                            {{ !empty($bookingRequest->updated_at) ? \Carbon\Carbon::parse($bookingRequest->updated_at)->format('d M Y, H:i') : 'N/A' }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- User Information -->
                        @if (!empty($xmlBody['user']) && is_array($xmlBody['user']))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="userInfoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#userInfo" aria-expanded="false" aria-controls="userInfo">
                                        User Information
                                    </button>
                                </h2>
                                <div id="userInfo" class="accordion-collapse collapse" aria-labelledby="userInfoHeading"
                                    data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><strong>Full Name:</strong>
                                                {{ $xmlBody['user']['userFullName'] ?? 'N/A' }}</li>
                                            <li class="list-group-item"><strong>Email:</strong>
                                                {{ $xmlBody['user']['userEmail'] ?? 'N/A' }}</li>
                                            <li class="list-group-item"><strong>Phone:</strong>
                                                {{ !empty($xmlBody['user']['userPhoneCode']) ? '+' . $xmlBody['user']['userPhoneCode'] . ' ' : '' }}{{ $xmlBody['user']['userPhone'] ?? 'N/A' }}
                                            </li>
                                            <li class="list-group-item"><strong>City:</strong>
                                                {{ $xmlBody['user']['city'] ?? 'N/A' }}</li>
                                            <li class="list-group-item"><strong>Country:</strong>
                                                {{ $xmlBody['user']['country'] ?? 'N/A' }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Passenger Information -->
                        @if (!empty($xmlBody['passengers']) && is_array($xmlBody['passengers']))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="passengerInfoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#passengerInfo" aria-expanded="false"
                                        aria-controls="passengerInfo">
                                        Passenger Information
                                    </button>
                                </h2>
                                <div id="passengerInfo" class="accordion-collapse collapse"
                                    aria-labelledby="passengerInfoHeading" data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($xmlBody['passengers'] as $index => $passenger)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Passenger {{ $index + 1 }} ({{ $passenger['type'] ?? 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Name:</strong>
                                                            {{ !empty($passenger['title']) ? $passenger['title'] . ' ' : '' }}{{ $passenger['name'] ?? '' }}
                                                            {{ $passenger['surname'] ?? '' }}</li>
                                                        <li class="list-group-item"><strong>Date of Birth:</strong>
                                                            {{ !empty($passenger['dob']) ? \Carbon\Carbon::parse($passenger['dob'])->format('d M Y') : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Nationality:</strong>
                                                            {{ $passenger['nationality'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Passport Number:</strong>
                                                            {{ $passenger['passportNumber'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Passport Expiry:</strong>
                                                            {{ !empty($passenger['passportExpiry']) ? \Carbon\Carbon::parse($passenger['passportExpiry'])->format('d M Y') : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Travelers Information -->
                        @if (!empty($travelers) && is_array($travelers))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="travelersInfoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#travelersInfo" aria-expanded="false"
                                        aria-controls="travelersInfo">
                                        Travelers Information
                                    </button>
                                </h2>
                                <div id="travelersInfo" class="accordion-collapse collapse"
                                    aria-labelledby="travelersInfoHeading" data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($travelers as $traveler)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Traveler {{ $traveler['rph'] ?? 'N/A' }}
                                                    ({{ $traveler['type'] ?? 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Full Name:</strong>
                                                            {{ $traveler['full_name'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>First Name:</strong>
                                                            {{ $traveler['first_name'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Last Name:</strong>
                                                            {{ $traveler['last_name'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Title:</strong>
                                                            {{ $traveler['title'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Birth Date:</strong>
                                                            {{ !empty($traveler['birth_date']) ? \Carbon\Carbon::parse($traveler['birth_date'])->format('d M Y') : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Phone:</strong>
                                                            {{ $traveler['phone'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Email:</strong>
                                                            {{ $traveler['email'] ?? 'N/A' }}</li>
                                                        @if (!empty($traveler['document']))
                                                            <li class="list-group-item"><strong>Document ID:</strong>
                                                                {{ $traveler['document']['id'] ?? 'N/A' }}</li>
                                                            <li class="list-group-item"><strong>Document Type:</strong>
                                                                {{ $traveler['document']['type'] ?? 'N/A' }}</li>
                                                            <li class="list-group-item"><strong>Issue Country:</strong>
                                                                {{ $traveler['document']['issue_country'] ?? 'N/A' }}</li>
                                                            <li class="list-group-item"><strong>Nationality:</strong>
                                                                {{ $traveler['document']['nationality'] ?? 'N/A' }}</li>
                                                            <li class="list-group-item"><strong>Expire Date:</strong>
                                                                {{ !empty($traveler['document']['expire_date']) ? \Carbon\Carbon::parse($traveler['document']['expire_date'])->format('d M Y') : 'N/A' }}
                                                            </li>
                                                        @endif
                                                        @if (!empty($traveler['segments']))
                                                            <li class="list-group-item">
                                                                <strong>Flight Segments:</strong>
                                                                {{ is_array($traveler['segments'])
                                                                    ? implode(', ', $traveler['segments'])
                                                                    : $traveler['segments'] }}
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Flight Segments -->
                        @if (!empty($flights) && is_array($flights))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="segmentsHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#segments" aria-expanded="false" aria-controls="segments">
                                        Flight Segments
                                    </button>
                                </h2>
                                <div id="segments" class="accordion-collapse collapse" aria-labelledby="segmentsHeading"
                                    data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($flights as $flight)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Segment {{ $flight['rph'] ?? 'N/A' }}:
                                                    {{ $flight['departure_airport'] ?? 'N/A' }} to
                                                    {{ $flight['arrival_airport'] ?? 'N/A' }}
                                                </div>
                                                <div class="card-body">
                                                    <h6>Flight Details</h6>
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Flight Number:</strong>
                                                            {{ $flight['flight_number'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Departure:</strong>
                                                            {{ $flight['departure_airport'] ?? 'N/A' }}{{ !empty($flight['departure_terminal']) ? ' (Terminal ' . $flight['departure_terminal'] . ')' : '' }}
                                                            on
                                                            {{ !empty($flight['departure_datetime']) ? \Carbon\Carbon::parse($flight['departure_datetime'])->format('d M Y, H:i') : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Arrival:</strong>
                                                            {{ $flight['arrival_airport'] ?? 'N/A' }}{{ !empty($flight['arrival_terminal']) ? ' (Terminal ' . $flight['arrival_terminal'] . ')' : '' }}
                                                            on
                                                            {{ !empty($flight['arrival_datetime']) ? \Carbon\Carbon::parse($flight['arrival_datetime'])->format('d M Y, H:i') : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Duration:</strong>
                                                            {{ !empty($flight['departure_datetime']) && !empty($flight['arrival_datetime']) ? \Carbon\Carbon::parse($flight['departure_datetime'])->diffInMinutes(\Carbon\Carbon::parse($flight['arrival_datetime'])) . ' minutes' : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Operating Airline:</strong>
                                                            {{ $flight['operating_airline'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Marketing Airline:</strong>
                                                            {{ $flight['marketing_airline'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Aircraft:</strong>
                                                            {{ $flight['equipment'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Cabin Class:</strong>
                                                            {{ $flight['cabin'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Fare Type:</strong>
                                                            {{ $flight['fare_type'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Status:</strong>
                                                            {{ $flight['status'] ?? 'N/A' }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Seats Information -->
                        @if (!empty($seats) && is_array($seats))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="seatsInfoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#seatsInfo" aria-expanded="false" aria-controls="seatsInfo">
                                        Seat Selections
                                    </button>
                                </h2>
                                <div id="seatsInfo" class="accordion-collapse collapse"
                                    aria-labelledby="seatsInfoHeading" data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($seats as $seat)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Seat:
                                                    {{ $seat['row_number'] ?? 'N/A' }}{{ $seat['seat_number'] ?? 'N/A' }}
                                                    (Flight RPH: {{ $seat['flight_rph'] ?? 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>Traveler RPH:</strong>
                                                            {{ $seat['traveler_rph'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Seat:</strong> Row
                                                            {{ $seat['row_number'] ?? 'N/A' }}, Seat
                                                            {{ $seat['seat_number'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Status:</strong>
                                                            {{ $seat['status'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Price:</strong>
                                                            {{ !empty($seat['price']) && !empty($seat['currency']) ? $seat['currency'] . ' ' . number_format($seat['price'], 2) : 'Free' }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Ancillaries (Add-ons) -->
                        @if (!empty($ancillaries) && is_array($ancillaries))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="ancillariesInfoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#ancillariesInfo" aria-expanded="false"
                                        aria-controls="ancillariesInfo">
                                        Ancillaries (Add-ons)
                                    </button>
                                </h2>
                                <div id="ancillariesInfo" class="accordion-collapse collapse"
                                    aria-labelledby="ancillariesInfoHeading" data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($ancillaries as $ancillary)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    {{ $ancillary['title'] ?? 'N/A' }} (Flight RPH:
                                                    {{ $ancillary['flight_rph'] ?? 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item"><strong>SSR Code:</strong>
                                                            {{ $ancillary['ssr_code'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Item Code:</strong>
                                                            {{ $ancillary['item_code'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Title:</strong>
                                                            {{ $ancillary['title'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Description:</strong>
                                                            {{ $ancillary['description'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Traveler RPH:</strong>
                                                            {{ $ancillary['traveler_rph'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Price:</strong>
                                                            {{ !empty($ancillary['price']) && !empty($ancillary['currency']) ? $ancillary['currency'] . ' ' . number_format($ancillary['price'], 2) : 'Free' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Status:</strong>
                                                            {{ $ancillary['status'] ?? 'N/A' }}</li>
                                                        <li class="list-group-item"><strong>Refundable:</strong>
                                                            {{ !empty($ancillary['refundable']) ? ($ancillary['refundable'] === 'true' ? 'Yes' : 'No') : 'N/A' }}
                                                        </li>
                                                        <li class="list-group-item"><strong>Expires:</strong>
                                                            {{ !empty($ancillary['expires']) ? \Carbon\Carbon::parse($ancillary['expires'])->format('d M Y, H:i') : 'N/A' }}
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Pricing Information -->
                        @if (!empty($priceBreakdown) && is_array($priceBreakdown))
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="pricingInfoHeading">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#pricingInfo" aria-expanded="false" aria-controls="pricingInfo">
                                        Pricing Information
                                    </button>
                                </h2>
                                <div id="pricingInfo" class="accordion-collapse collapse"
                                    aria-labelledby="pricingInfoHeading" data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($priceBreakdown as $breakdown)
                                            <div class="card mb-3">
                                                <div class="card-header">
                                                    Passenger Type: {{ $breakdown['passenger_type'] ?? 'N/A' }} (Quantity:
                                                    {{ $breakdown['quantity'] ?? 'N/A' }})
                                                </div>
                                                <div class="card-body">
                                                    @if (!empty($breakdown['per_segment_fares']) && is_array($breakdown['per_segment_fares']))
                                                        @foreach ($breakdown['per_segment_fares'] as $segmentFare)
                                                            <div class="card mb-3">
                                                                <div class="card-header">
                                                                    Route: {{ $segmentFare['from'] ?? 'N/A' }} to
                                                                    {{ $segmentFare['to'] ?? 'N/A' }}
                                                                </div>
                                                                <div class="card-body">
                                                                    <ul class="list-group list-group-flush">
                                                                        <li class="list-group-item"><strong>Fare
                                                                                Basis:</strong>
                                                                            {{ $segmentFare['fare_basis'] ?? 'N/A' }}</li>
                                                                        <li class="list-group-item"><strong>Base
                                                                                Fare:</strong> PKR
                                                                            {{ !empty($segmentFare['base_fare']) ? number_format($segmentFare['base_fare'], 2) : '0.00' }}
                                                                        </li>
                                                                        <li class="list-group-item"><strong>Taxes
                                                                                Total:</strong> PKR
                                                                            {{ !empty($segmentFare['taxes_total']) ? number_format($segmentFare['taxes_total'], 2) : '0.00' }}
                                                                        </li>
                                                                        <li class="list-group-item"><strong>Fees
                                                                                Total:</strong> PKR
                                                                            {{ !empty($segmentFare['fees_total']) ? number_format($segmentFare['fees_total'], 2) : '0.00' }}
                                                                        </li>
                                                                        <li class="list-group-item">
                                                                            <strong>Baggage:</strong>
                                                                            {{ !empty($segmentFare['baggage']['quantity']) ? $segmentFare['baggage']['quantity'] . ' ' . ($segmentFare['baggage']['unit'] ?? 'KGS') : 'N/A' }}
                                                                        </li>
                                                                    </ul>
                                                                    @if (!empty($segmentFare['taxes']) && is_array($segmentFare['taxes']))
                                                                        <h6 class="mt-3">Tax Breakdown</h6>
                                                                        <ul class="list-group list-group-flush">
                                                                            @foreach ($segmentFare['taxes'] as $tax)
                                                                                <li class="list-group-item">
                                                                                    <strong>{{ $tax['TaxCode'] ?? 'Tax' }}:</strong>
                                                                                    {{ !empty($tax['CurrencyCode']) && !empty($tax['Amount']) ? $tax['CurrencyCode'] . ' ' . number_format($tax['Amount'], 2) : 'N/A' }}
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                    @if (!empty($segmentFare['fees']) && is_array($segmentFare['fees']))
                                                                        <h6 class="mt-3">Fees Breakdown</h6>
                                                                        <ul class="list-group list-group-flush">
                                                                            @foreach ($segmentFare['fees'] as $fee)
                                                                                <li class="list-group-item">
                                                                                    <strong>{{ $fee['FeeCode'] ?? 'Fee' }}:</strong>
                                                                                    {{ !empty($fee['CurrencyCode']) && !empty($fee['Amount']) ? $fee['CurrencyCode'] . ' ' . number_format($fee['Amount'], 2) : 'N/A' }}
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="card mt-3">
                                            <div class="card-header">
                                                <strong>Total Amount</strong>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item"><strong>Total:</strong>
                                                        {{ !empty($data['total']['amount']) && !empty($data['total']['currency']) ? $data['total']['currency'] . ' ' . number_format($data['total']['amount'], 2) : 'N/A' }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Penalties -->
                        @if (
                            !empty($raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']) &&
                                is_array($raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']))
                            @php
                                $fareBreakdowns =
                                    is_array($raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']) &&
                                    isset($raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown'][0])
                                        ? $raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']
                                        : [$raw['PriceInfo']['PTC_FareBreakdowns']['PTC_FareBreakdown']];
                                $hasPenalties = false;
                                foreach ($fareBreakdowns as $fare) {
                                    if (!empty($fare['FareInfo']) && is_array($fare['FareInfo'])) {
                                        $fareInfos =
                                            is_array($fare['FareInfo']) && isset($fare['FareInfo'][0])
                                                ? $fare['FareInfo']
                                                : [$fare['FareInfo']];
                                        foreach ($fareInfos as $fareInfo) {
                                            if (!empty($fareInfo['RuleInfo']['ChargesRules'])) {
                                                $hasPenalties = true;
                                                break 2;
                                            }
                                        }
                                    }
                                }
                            @endphp
                        @endif
                        @if ($hasPenalties)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="penaltiesHeading">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#penalties" aria-expanded="false"
                                        aria-controls="penalties">
                                        Penalties & Cancellation Fees
                                    </button>
                                </h2>
                                <div id="penalties" class="accordion-collapse collapse"
                                    aria-labelledby="penaltiesHeading" data-bs-parent="#bookingAccordion">
                                    <div class="accordion-body">
                                        @foreach ($fareBreakdowns as $fare)
                                            @if (!empty($fare['FareInfo']) && is_array($fare['FareInfo']))
                                                @php
                                                    $fareInfos = is_array($fare['FareInfo']) && isset($fare['FareInfo'][0]) ? $fare['FareInfo'] : [$fare['FareInfo']];
                                                @endphp
                                                @foreach ($fareInfos as $fareInfo)
                                                    @if (!empty($fareInfo['RuleInfo']['ChargesRules']))
                                                        <div class="card mb-3">
                                                            <div class="card-header">
                                                                Route:
                                                                {{ !empty($fareInfo['DepartureAirport']['@attributes']['LocationCode']) ? $fareInfo['DepartureAirport']['@attributes']['LocationCode'] : 'N/A' }}
                                                                to
                                                                {{ !empty($fareInfo['ArrivalAirport']['@attributes']['LocationCode']) ? $fareInfo['ArrivalAirport']['@attributes']['LocationCode'] : 'N/A' }}
                                                            </div>
                                                            <div class="card-body">
                                                                @if (!empty($fareInfo['RuleInfo']['ChargesRules']['VoluntaryChanges']['Penalty']))
                                                                    <h6>Change Fees</h6>
                                                                    <ul class="list-group list-group-flush">
                                                                        @php
                                                                            $changePenalties = is_array($fareInfo['RuleInfo']['ChargesRules']['VoluntaryChanges']['Penalty']) && isset($fareInfo['RuleInfo']['ChargesRules']['VoluntaryChanges']['Penalty'][0])
                                                                                ? $fareInfo['RuleInfo']['ChargesRules']['VoluntaryChanges']['Penalty']
                                                                                : [$fareInfo['RuleInfo']['ChargesRules']['VoluntaryChanges']['Penalty']];
                                                                        @endphp
                                                                        @foreach ($changePenalties as $penalty)
                                                                            <li class="list-group-item">
                                                                                <strong>{{ !empty($penalty['@attributes']['HoursBeforeDeparture']) ? $penalty['@attributes']['HoursBeforeDeparture'] : 'N/A' }}:</strong>
                                                                                {{ !empty($penalty['@attributes']['CurrencyCode']) && !empty($penalty['@attributes']['Amount']) ? $penalty['@attributes']['CurrencyCode'] . ' ' . number_format($penalty['@attributes']['Amount'], 2) : 'N/A' }}
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                                @if (!empty($fareInfo['RuleInfo']['ChargesRules']['VoluntaryRefunds']['Penalty']))
                                                                    <h6 class="mt-3">Cancellation / Refund Fees</h6>
                                                                    <ul class="list-group list-group-flush">
                                                                        @php
                                                                            $refundPenalties = is_array($fareInfo['RuleInfo']['ChargesRules']['VoluntaryRefunds']['Penalty']) && isset($fareInfo['RuleInfo']['ChargesRules']['VoluntaryRefunds']['Penalty'][0])
                                                                                ? $fareInfo['RuleInfo']['ChargesRules']['VoluntaryRefunds']['Penalty']
                                                                                : [$fareInfo['RuleInfo']['ChargesRules']['VoluntaryRefunds']['Penalty']];
                                                                        @endphp
                                                                        @foreach ($refundPenalties as $penalty)
                                                                            <li class="list-group-item">
                                                                                <strong>{{ !empty($penalty['@attributes']['HoursBeforeDeparture']) ? $penalty['@attributes']['HoursBeforeDeparture'] : 'N/A' }}:</strong>
                                                                                {{ !empty($penalty['@attributes']['CurrencyCode']) && !empty($penalty['@attributes']['Amount']) ? $penalty['@attributes']['CurrencyCode'] . ' ' . number_format($penalty['@attributes']['Amount'], 2) : 'N/A' }}
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <div class="alert alert-warning">No booking request data available for Airblue.</div>
            @endif
        @endif
    @else
        <div class="alert alert-danger">No booking request data available.</div>
    @endif
</div>
