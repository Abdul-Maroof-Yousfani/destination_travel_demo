@if (isset($flightData) && !empty($flightData))
    @php
        $airline = $flightData['airline'] ?? 'unknown';
        $isEmirate = $airline === 'emirate';
        $isFlyJinnah = $airline === 'flyjinnah';
        $isAirblue = $airline === 'airblue';
        $isPIA = $airline === 'pia';

        $logo = $flightData['airline'] ?? 'default';

        // Handle Airblue flights array structure
        if ($isAirblue && isset($flightData['flights']) && is_array($flightData['flights'])) {
            $flights = $flightData['flights'];
            $totalPrice = 0;
            $totalBase = 0;
            $totalTax = 0;
            $totalFees = 0;

            foreach ($flights as $flightItem) {
                if (isset($flightItem['bundle'])) {
                    $totalPrice += (float) ($flightItem['bundle']['total_price'] ?? 0);
                    $totalBase += (float) ($flightItem['bundle']['base_price'] ?? 0);
                    $totalTax += (float) ($flightItem['bundle']['taxes'] ?? 0);
                    $totalFees += (float) ($flightItem['bundle']['fees'] ?? 0);
                }
            }
        } else {
            // Original structure for flyjinnah and emirates
            $departure = $flightData['departure'] ?? null;
            $originCode = $departure['departure']['code'] ?? '';
            $destinationCode = $departure['arrival']['code'] ?? '';
            $depTime = $departure['departure']['time'] ?? '';
            $arrTime = $departure['arrival']['time'] ?? '';
            $depTimeDiff = $departure['duration'] ?? '';
            $depDate = $departure['departure']['date'] ?? '';
            $stopCount = count($departure['segments'] ?? []) - 1;

            $hasReturn = isset($flightData['return']);
            if ($hasReturn) {
                $return = $flightData['return'];
                $returnOriginCode = $return['departure']['code'] ?? '';
                $returnDestinationCode = $return['arrival']['code'] ?? '';
                $returnDepTime = $return['departure']['time'] ?? '';
                $returnArrTime = $return['arrival']['time'] ?? '';
                $returnTimeDiff = $return['duration'] ?? '';
                $returnStopCount = count($return['segments'] ?? []) - 1;
            }

            $outboundBundle = $flightData['firstBundle'] ?? null;
            $returnBundle = $flightData['returnBundle'] ?? null;

            // For PIA, extract price from the total_price_pkr field
            if ($isPIA) {
                $totalPrice = (float) ($flightData['total_price_pkr'] ?? 0);
                $totalBase = $totalPrice; // PIA doesn't provide separate breakdown
                $totalTax = 0;
                $totalFees = 0;
            } else {
                $totalPrice = ($outboundBundle['total_price'] ?? 0) + ($returnBundle['total_price'] ?? 0);
                $totalBase = ($outboundBundle['base_price'] ?? 0) + ($returnBundle['base_price'] ?? 0);
                $totalTax = ($outboundBundle['taxes'] ?? 0) + ($returnBundle['taxes'] ?? 0);
                $totalFees = ($outboundBundle['fees'] ?? 0) + ($returnBundle['fees'] ?? 0);
            }
        }
    @endphp

    {{-- ==================== FLIGHT ITINERARY ==================== --}}
    <div class="bokkings-bar">
        <div class="book-head">
            <div class="youbook">
                <h2>Your Bookings</h2>
            </div>
            @if ($isAirblue && isset($flights) && count($flights) > 2)
                <div class="depar-head">
                    <ul>
                        <li>
                            <p>Multi-City Flight</p>
                        </li>
                        <li>
                            {{-- <p><i class="fa-regular fa-calendar"></i> {{ count($flights) }} {{ Str::plural('Leg', count($flights)) }}</p> --}}
                        </li>
                    </ul>
                </div>
            @else
                <div class="depar-head">
                    <ul>
                        <li>
                            <p>Departing</p>
                        </li>
                        <li>
                            <p><i class="fa-regular fa-calendar"></i> {{ $depDate ?? '' }}</p>
                        </li>
                    </ul>
                </div>
            @endif
        </div>

        <div class="book-flex">
            <div class="emr w-25">
                <img src="{{ asset('assets/images/logos/modal/' . $logo . '.png') }}" alt="{{ strtoupper($logo) }}"
                    onerror="this.src='{{ asset('assets/images/logos/modal/default.png') }}'">
            </div>
        </div>

        <div class="d-flex flex-column">
            @if ($isAirblue && isset($flights))
                {{-- Airblue Multi-City Flights --}}
                @foreach ($flights as $index => $flightItem)
                    @php
                        $flight = $flightItem['departure'] ?? null;
                        if (!$flight) {
                            continue;
                        }

                        $originCode = $flight['departure']['code'] ?? '';
                        $destinationCode = $flight['arrival']['code'] ?? '';
                        $depTime = $flight['departure']['time'] ?? '';
                        $arrTime = $flight['arrival']['time'] ?? '';
                        $depTimeDiff = $flight['duration'] ?? '';
                        $depDate = $flight['departure']['date'] ?? '';
                        $stopCount = count($flight['segments'] ?? []) - 1;
                        $legNumber = $index + 1;
                    @endphp
                    <div class="der-time der-time3 mb-2">
                        <div class="mb-2">
                            {{-- <span class="badge bg-primary">Leg {{ $legNumber }}</span> --}}
                            <small class="text-muted ms-2">{{ $depDate }}</small>
                        </div>
                        <ul>
                            <li>
                                <h2>{{ $depTime }}</h2>
                            </li>
                            <li>
                                <div class="stays">
                                    <p>{{ $depTimeDiff }}</p>
                                </div>
                            </li>
                            <li>
                                <div class="tims">
                                    <h2>{{ $arrTime }}</h2>
                                </div>
                            </li>
                        </ul>
                        <div class="citys citys2">
                            <div class="cit">
                                <ul>
                                    <li>
                                        <p>{{ $originCode }}</p>
                                    </li>
                                    <li>
                                        <p>{{ $stopCount > 0 ? $stopCount . ' ' . Str::plural('Stop', $stopCount) : 'Nonstop' }}
                                        </p>
                                    </li>
                                    <li>
                                        <p>{{ $destinationCode }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Original Structure for FlyJinnah and Emirates --}}
                {{-- Outbound Flight --}}
                @if (isset($departure))
                    <div class="der-time der-time3 mb-2">
                        <ul>
                            <li>
                                <h2>{{ $depTime }}</h2>
                            </li>
                            <li>
                                <div class="stays">
                                    <p>{{ $depTimeDiff }}</p>
                                </div>
                            </li>
                            <li>
                                <div class="tims">
                                    <h2>{{ $arrTime }}</h2>
                                </div>
                            </li>
                        </ul>
                        <div class="citys citys2">
                            <div class="cit">
                                <ul>
                                    <li>
                                        <p>{{ $originCode }}</p>
                                    </li>
                                    <li>
                                        <p>{{ $stopCount > 0 ? $stopCount . ' ' . Str::plural('Stop', $stopCount) : 'Nonstop' }}
                                        </p>
                                    </li>
                                    <li>
                                        <p>{{ $destinationCode }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Return Flight --}}
                @if (isset($hasReturn) && $hasReturn)
                    <div class="der-time der-time3 mb-2">
                        <ul>
                            <li>
                                <h2>{{ $returnDepTime }}</h2>
                            </li>
                            <li>
                                <div class="stays">
                                    <p>{{ $returnTimeDiff }}</p>
                                </div>
                            </li>
                            <li>
                                <div class="tims">
                                    <h2>{{ $returnArrTime }}</h2>
                                </div>
                            </li>
                        </ul>
                        <div class="citys citys2">
                            <div class="cit">
                                <ul>
                                    <li>
                                        <p>{{ $returnOriginCode }}</p>
                                    </li>
                                    <li>
                                        <p>{{ $returnStopCount > 0 ? $returnStopCount . ' ' . Str::plural('Stop', $returnStopCount) : 'Nonstop' }}
                                        </p>
                                    </li>
                                    <li>
                                        <p>{{ $returnDestinationCode }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ==================== PRICE SUMMARY ==================== --}}
    <div class="bokkings-bar bokkings-bar2 priceSummaryContainer">
        <div class="book-head">
            <div class="youbook">
                <h2><span>Price Summary</span></h2>
            </div>
        </div>

        <div class="der-time der-time3">
            @if ($isEmirate)
                {{-- Emirates Special Handling --}}
                @if (!empty($flightData['flightDetails']['bundle']['offerItem'] ?? []))
                    @foreach ($flightData['flightDetails']['bundle']['offerItem'] as $offer)
                        <div class="emr-adul justify-content-between">
                            <p>{{ $offer['fareDetail']['passengers'] ?? 'Passenger' }}</p>
                            <p>PKR {{ number_format($offer['totalPrice']['amount'] ?? 0) }}</p>
                        </div>
                    @endforeach
                @else
                    <div class="emr-adul justify-content-between">
                        <p>Flight Fare</p>
                        <p>PKR {{ number_format($flightData['flightDetails']['bundle']['totalPrice']['amount'] ?? 0) }}
                        </p>
                    </div>
                @endif
                <div class="emr-adul justify-content-between">
                    <p>Taxes & Fees</p>
                    <p>PKR {{ $tax ?? 0 }}</p>
                </div>
            @elseif ($isFlyJinnah)
                {{-- FlyJinnah --}}
                <div class="emr-adul justify-content-between">
                    <p>Flight
                        {{ isset($flightData['isDirectBooking']) && !$flightData['isDirectBooking'] ? 'with Bundle' : 'Price' }}
                    </p>
                    <p>{{ $totalFare['TotalFare']['@attributes']['CurrencyCode'] ?? 'PKR' }}
                        {{ $totalFare['TotalFare']['@attributes']['Amount'] ?? 0 }}</p>
                </div>
                <div class="emr-adul justify-content-between">
                    <p>Tax</p>
                    <p>PKR {{ $tax ?? 0 }}</p>
                </div>
            @elseif ($isPIA)
                {{-- PIA --}}
                <div class="emr-adul justify-content-between">
                    <p>Flight Fare ({{ $flightData['paxCount']['adt'] ?? 1 }} {{ ($flightData['paxCount']['adt'] ?? 1) > 1 ? 'Adults' : 'Adult' }})</p>
                    <p>PKR {{ number_format((float) ($flightData['total_price_pkr'] ?? 0)) }}</p>
                </div>
                @if (($flightData['paxCount']['chd'] ?? 0) > 0)
                    <div class="emr-adul justify-content-between">
                        <p>Children ({{ $flightData['paxCount']['chd'] }})</p>
                        <p>Included</p>
                    </div>
                @endif
                @if (($flightData['paxCount']['inf'] ?? 0) > 0)
                    <div class="emr-adul justify-content-between">
                        <p>Infants ({{ $flightData['paxCount']['inf'] }})</p>
                        <p>Included</p>
                    </div>
                @endif
                <div class="border-top pt-3 mt-2">
                    <div class="emr-adul justify-content-between">
                        <p><strong>Outbound Bundle:</strong> {{ strtoupper($flightData['outbound_bundle'] ?? 'N/A') }}</p>
                    </div>
                    @if (isset($flightData['inbound_bundle']))
                        <div class="emr-adul justify-content-between">
                            <p><strong>Inbound Bundle:</strong> {{ strtoupper($flightData['inbound_bundle']) }}</p>
                        </div>
                    @endif
                </div>
            @else
                {{-- All Other Airlines (Airblue, SereneAir, etc.) --}}
                @if ($isAirblue && isset($flights))
                    {{-- Airblue Multi-City Flights --}}
                    @foreach ($flights as $index => $flightItem)
                        @php
                            $bundle = $flightItem['bundle'] ?? null;
                            if (!$bundle) {
                                continue;
                            }
                            $flight = $flightItem['departure'] ?? null;
                            $originCode = $flight['departure']['code'] ?? '';
                            $destinationCode = $flight['arrival']['code'] ?? '';
                            $routeLabel =
                                $originCode && $destinationCode ? "{$originCode}-{$destinationCode}" : 'Flight';
                        @endphp
                        <div class="align-items-center emr-adul justify-content-between mb-2">
                            <div class="align-items-center gap-2 w-100">
                                <span class="badge px-3 py-2 text-white"
                                    style="background: {{ $bundle['color'] ?? '#6c757d' }}">
                                    {{ $bundle['bundle_name'] ?? 'Standard' }}
                                </span>
                                <small class="text-muted">({{ $routeLabel }})</small>
                                <strong class="float-right">PKR
                                    {{ number_format((float) ($bundle['total_price'] ?? 0)) }}</strong>
                            </div>
                        </div>
                    @endforeach

                    <!-- Breakdown -->
                    <div class="border-top pt-3 mt-2 small text-muted">
                        <div class="d-flex justify-content-between"><span>Base Fare</span><span>PKR
                                {{ number_format($totalBase) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Taxes</span><span>PKR
                                {{ number_format($totalTax) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Fees & Charges</span><span>PKR
                                {{ number_format($totalFees) }}</span></div>
                    </div>
                @else
                    {{-- Original Structure for Other Airlines --}}
                    <!-- Outbound Bundle -->
                    <div class="align-items-center emr-adul justify-content-between mb-2">
                        <div class="align-items-center gap-2 w-100">
                            <span class="badge px-3 py-2 text-white"
                                style="background: {{ $outboundBundle['color'] ?? '#6c757d' }}">
                                {{ $outboundBundle['bundle_name'] ?? 'Standard' }}
                            </span>
                            <small
                                class="text-muted">({{ isset($hasReturn) && $hasReturn ? 'Outbound' : 'Flight' }})</small>
                            <strong class="float-right">PKR
                                {{ number_format($outboundBundle['total_price'] ?? 0) }}</strong>
                        </div>
                    </div>

                    <!-- Return Bundle -->
                    @if (isset($returnBundle) && $returnBundle)
                        <div class="emr-adul justify-content-between align-items-center mb-3">
                            <div class="align-items-center gap-2 w-100">
                                <span class="badge px-3 py-2 text-white"
                                    style="background: {{ $returnBundle['color'] ?? '#6c757d' }}">
                                    {{ $returnBundle['bundle_name'] ?? 'Standard' }}
                                </span>
                                <small class="text-muted">(Return)</small>
                                <strong class="float-right">PKR
                                    {{ number_format($returnBundle['total_price'] ?? 0) }}</strong>
                            </div>
                        </div>
                    @endif

                    <!-- Breakdown -->
                    <div class="border-top pt-3 mt-2 small text-muted">
                        <div class="d-flex justify-content-between"><span>Base Fare</span><span>PKR
                                {{ number_format($totalBase) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Taxes</span><span>PKR
                                {{ number_format($totalTax) }}</span></div>
                        <div class="d-flex justify-content-between"><span>Fees & Charges</span><span>PKR
                                {{ number_format($totalFees) }}</span></div>
                    </div>
                @endif
            @endif

            {{-- Total Price --}}
            <div class="pri-pak mt-4">
                <h2>Total price you pay</h2>
                <p class="Rfs">
                    PKR
                    {{ number_format(
                        $isEmirate
                            ? ($flightData['flightDetails']['bundle']['totalPrice']['amount'] ?? 0) + ($tax ?? 0)
                            : ($isFlyJinnah
                                ? ($totalFare['TotalFare']['@attributes']['Amount'] ?? 0) + ($tax ?? 0)
                                : $totalPrice + ($tax ?? 0)),
                    ) }}
                </p>
            </div>
        </div>
    </div>

    {{-- ==================== PENALTIES (Only for Emirates + All Others, excluding PIA and FlyJinnah) ==================== --}}
    @if ($isEmirate || (!$isEmirate && !$isFlyJinnah && !$isPIA))
        <div class="bokkings-bar bokkings-bar2 penaltiesContainer">
            <div class="book-head">
                <div class="youbook">
                    <h2><span>Penalties</span></h2>
                </div>
                <div class="youbook">
                    <p class="text-info font-weight-bolder toggle-panelties-details pointer">
                        <span class="toggle-text">Show details</span>
                        <i class="fas fa-chevron-down toggle-icon transition-all duration-300"></i>
                    </p>
                </div>
            </div>
            <div class="der-time der-time3 panelties-details" style="display: none;">
                @if ($isEmirate)
                    @foreach ($flightData['flightDetails']['bundle']['offerItem'] ?? [] as $offer)
                        <div class="emr-adul justify-content-between">
                            <h2>{{ $offer['fareDetail']['passengers'] ?? '' }}</h2>
                        </div>
                        @foreach ($offer['fareDetail']['penalties'] ?? [] as $penalty)
                            <div class="emr-adul justify-content-between">
                                <p>{{ $penalty['arrival'] ?? '' }}</p>
                                <p>{{ $penalty['destination'] ?? '' }}</p>
                            </div>
                            @if (!empty($penalty['fareRules']['cancelFee']))
                                <div class="mt-2"><strong class="font-weight-bolder">Cancel Fee</strong></div>
                                @foreach ($penalty['fareRules']['cancelFee'] as $label => $fee)
                                    <div class="emr-adul justify-content-between">
                                        <p>{{ $label }}</p>
                                        @if (isset($fee['price']))
                                            <p>
                                                {{ $fee['price']['amount'] ?? '-' }}
                                                {{ $fee['price']['code'] ?? '' }}
                                                <small class="small">({{ $fee['amountApplication'] ?? '' }})</small>
                                            </p>
                                        @else
                                            <p>{{ $fee }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                            @if (!empty($penalty['fareRules']['changeFee']))
                                <div class="mt-2"><strong class="font-weight-bolder">Change Fee</strong></div>
                                @foreach ($penalty['fareRules']['changeFee'] as $label => $fee)
                                    <div class="emr-adul justify-content-between">
                                        <p>{{ $label }}</p>
                                        @if (isset($fee['price']))
                                            <p>
                                                {{ $fee['price']['amount'] ?? '-' }}
                                                {{ $fee['price']['code'] ?? '' }}
                                                <small class="small">({{ $fee['amountApplication'] ?? '' }})</small>
                                            </p>
                                        @else
                                            <p>{{ $fee }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                            @if (!empty($penalty['fareRules']['refundFee']))
                                <div class="mt-2"><strong class="font-weight-bolder">Refund Fee</strong></div>
                                @foreach ($penalty['fareRules']['refundFee'] as $label => $fee)
                                    <div class="emr-adul justify-content-between">
                                        <p>{{ $label }}</p>
                                        @if (isset($fee['price']))
                                            <p>
                                                {{ $fee['price']['amount'] ?? '-' }}
                                                {{ $fee['price']['code'] ?? '' }}
                                                <small class="small">({{ $fee['amountApplication'] ?? '' }})</small>
                                            </p>
                                        @else
                                            <p>{{ $fee }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                            <br>
                            <hr>
                        @endforeach
                    @endforeach
                @else
                    @php
                        $renderPenalty = function ($bundle, $leg) {
                            if (!$bundle) {
                                return;
                            }
                            echo '<div class="p-3 mb-4 rounded" style="background:#f8f9fa;">';
                            echo '<h6 class="font-weight-bold text-primary">' .
                                ($bundle['bundle_name'] ?? 'Bundle') .
                                ' – ' .
                                $leg .
                                '</h6>';
                            echo '<small class="text-success">Baggage: ' .
                                ($bundle['baggage'] ?? 'Not included') .
                                '</small>';
                            if (!empty($bundle['change_penalty'] ?? [])) {
                                echo '<div class="my-3"><strong class="font-weight-bold">Change Penalty</strong></div>';
                                foreach ($bundle['change_penalty'] as $p) {
                                    echo '<div class="d-flex justify-content-between"><span>' .
                                        e($p['label']) .
                                        '</span><span class="text-danger">PKR ' .
                                        number_format($p['amount']) .
                                        '</span></div>';
                                }
                            }
                            if (!empty($bundle['refund_penalty'] ?? [])) {
                                echo '<div class="my-3"><strong class="font-weight-bold">Cancellation / Refund Penalty</strong></div>';
                                foreach ($bundle['refund_penalty'] as $p) {
                                    echo '<div class="d-flex justify-content-between"><span>' .
                                        e($p['label']) .
                                        '</span><span class="text-danger">PKR ' .
                                        number_format($p['amount']) .
                                        '</span></div>';
                                }
                            }
                            echo '</div>';
                        };
                    @endphp
                    @if ($isAirblue && isset($flights))
                        {{-- Airblue Multi-City Penalties --}}
                        @foreach ($flights as $index => $flightItem)
                            @php
                                $bundle = $flightItem['bundle'] ?? null;
                                if (!$bundle) {
                                    continue;
                                }
                                $flight = $flightItem['departure'] ?? null;
                                $originCode = $flight['departure']['code'] ?? '';
                                $destinationCode = $flight['arrival']['code'] ?? '';
                                $routeLabel =
                                    $originCode && $destinationCode ? "{$originCode}-{$destinationCode}" : 'Journey';
                            @endphp
                            {!! $renderPenalty($bundle, $routeLabel) !!}
                        @endforeach
                    @else
                        {{-- Original Structure for Other Airlines --}}
                        {!! $renderPenalty($outboundBundle ?? null, isset($hasReturn) && $hasReturn ? 'Outbound' : 'Journey') !!}
                        @if (isset($returnBundle) && $returnBundle)
                            {!! $renderPenalty($returnBundle, 'Return') !!}
                        @endif
                    @endif
                @endif
            </div>
        </div>
    @endif
@endif